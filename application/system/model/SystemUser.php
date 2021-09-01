<?php
// +----------------------------------------------------------------------
// | 咪咕CMS[基于ThinkPHP5.1开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2021 http://www.migucms.com
// +----------------------------------------------------------------------
// | 咪咕CMS承诺基础框架永久免费开源，您可用于学习，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: btc
// +----------------------------------------------------------------------
namespace app\system\model;

use app\system\model\SystemMenu as MenuModel;
use app\system\model\SystemRole as RoleModel;
use app\system\model\SystemLog as LogModel;
use migu\Dir;
use think\facade\Cache;

/**
 * 后台用户模型
 * @package app\system\model
 */
class SystemUser extends SystemBase
{
    
    public function setAuthAttr($value)
    {
        if (is_array($value)) {
            return json_encode($value, 1);
        } elseif ($value) {
            return json_encode(explode(',', $value), 1);
        } else {
            return '';
        }
    }

    public function getAuthAttr($value)
    {
        if (empty($value)) return [];
        return json_decode($value, 1);
    }

    // 获取最后登陆ip
    public function setLastLoginIpAttr($value)
    {
        return get_client_ip();
    }

    // 格式化最后登录时间
    public function getLastLoginTimeAttr($value)
    {
        if ($value) {
            return date('Y-m-d H:i', $value);
        } else {
            return '---';
        }
    }

    // 关联角色
    public function hasRoles()
    {
        return $this->belongsToMany('SystemRole', 'SystemUserRole', 'role_id', 'user_id');
    }

    // 关联索引
    public function hasIndexs()
    {
        return $this->hasMany('SystemUserRole', 'user_id');
    }

    // 模型事件
    public static function init()
    {
        // 新增前
        self::event('before_insert', function ($obj) {
            $data = $obj->getData();
            $data['password'] = password_hash(md5($data['password']), PASSWORD_DEFAULT);
            
            unset($data['password_confirm']);
            
            $obj->data($data);

            return true;
        });

        // 新增后
        self::event('after_insert', function($obj) {
            if (isset($obj->role_id) && $obj->role_id) {
                $obj->hasRoles()->saveAll(explode(',', $obj->role_id));
            }
        });

        // 更新前
        self::event('before_update', function ($obj) {
            $data = $obj->getData();
            
            if ($data['id'] == 1 && ADMIN_ID != 1) {
                $obj->error = '禁止修改超级管理员';
                return false;
            }
            
            if (isset($obj->role_id) && $obj->role_id) {
                $obj->hasRoles()->detach();
            }
            
            if (isset($data['password']) && 
                $data['password'] && 
                isset($data['password_confirm']) &&
                $data['password_confirm']) {
                $data['password'] = password_hash(md5($obj['password']), PASSWORD_DEFAULT);
                unset($data['password_confirm']);
            } else {
                unset($data['password']);
            }
            
            $obj->data($data);
            
            return true;
        });

        // 更新后
        self::event('after_update', function($obj) {
            if (isset($obj->role_id) && $obj->role_id) {
                $obj->hasRoles()->saveAll(explode(',', $obj->role_id));
            }
        });

        // 删除前
        self::event('before_delete', function ($obj) {

            if ($obj['id'] == ADMIN_ID) {
                $obj->error = '不能删除当前登陆的用户';
                return false;
            }

            if ($obj['id'] == 1) {
                $obj->error = '不能删除超级管理员';
                return false;
            }
            
            // 删除角色索引表
            $obj->hasRoles()->detach();

            // 删除用户收藏的菜单
            (new MenuModel)->delUser($obj['id']);
        });
    }

    /**
     * 用户登录
     * @param string $username 用户名
     * @param string $password 密码
     * @param bool $remember 记住登录 TODO
     * @author Author: btc
     * @return bool|mixed
     */
    public function login($username = '', $password = '', $remember = false)
    {
        $username = trim($username);
        $password = trim($password);
        $map = [];
        $map[] = ['status', '=', 1];
        $map[] = ['username', '=', $username];

        $validate = new \app\system\validate\SystemUser;
        
        if ($validate->scene('login')->check(input('post.')) !== true) {
            $this->error = $validate->getError();
            return false;
        }
        
        $user = self::with('hasRoles')->where($map)->find();
        if (!$user) {
            $this->error = '用户不存在或被禁用！';
            return false;
        }

        $loginErrorCount = $user->login_error_count;
        $loginLockedTime = $user->login_locked_time;
        if ($loginLockedTime) {
            if ($loginLockedTime > time()) {
                $minute = ceil(($loginLockedTime - time())%86400/60);
                $this->error = '账号被锁定，请稍等'.$minute.'分后再登录';
                return false;
            } else {
                $sqlmap = [];
                $sqlmap['login_error_count'] = 0;
                $sqlmap['login_locked_time'] = 0;
                $loginErrorCount = 0;
                self::where('id', '=', $user->id)->update($sqlmap);
            }
        }

        // 密码校验
        if (!password_verify($password, $user->password)) {
            $errorLimit = (int)config('sys.admin_login_error_limit');
            $errorLimit = $errorLimit ?: 5;
            $lockedTime = (int)config('sys.admin_login_locked_time');
            $lockedTime = $lockedTime ?: 5;
            $loginErrorCount++;

            $sqlmap = [];
            $sqlmap['login_error_count'] = $loginErrorCount;

            if ($loginErrorCount >= $errorLimit) {
                $sqlmap['login_locked_time'] = strtotime('+ '.$lockedTime.' minutes');
            }
            
            self::where('id', '=', $user->id)->update($sqlmap);

            $this->error = '登陆密码错误！';
            return false;
        }

        $roleIds = [];
        if ($user['id'] != 1) {
            // 非超级管理员，提取关联角色
            $roles = $user->hasRoles->toArray();
            if (empty($roles)) {
                $this->error = '未绑定角色';
                return false;
            }
            
            foreach($roles as $k => $v) {
                $v['status'] == 1 && $roleIds[] = $v['id'];
            }

            if (empty($roleIds)) {
                $this->error = '绑定的角色不可用';
                return false;
            }
        }

        // 自动清除过期的系统日志
        LogModel::where('ctime', '<', strtotime('-'.(int)config('sys.system_log_retention').' days'))->delete();

        // 更新登录信息
        $sqlmap                     = [];
        $sqlmap['last_login_time']  = time();
        $sqlmap['last_login_ip']    = get_client_ip();
        $sqlmap['login_error_count'] = 0;
        $sqlmap['login_locked_time'] = 0;

        if (self::where('id', $user->id)->update($sqlmap)) {
            
            // 执行登陆
            $login              = [];
            $login['uid']       = $user->id;
            $login['role_id']   = implode(',', $roleIds);
            $login['username']  = $user->username;
            $login['mobile']    = $user->mobile;
            $login['email']     = $user->email;
            $login['nick']      = $user->nick;
            
            cookie('migu_iframe', (int)$user->iframe);
            cookie('migu_menu_layout', (int)$user->menu_layout);

            // 主题设置
            self::setTheme(isset($user->theme) ? $user->theme : 0);
            self::getThemes(true);

            // 缓存登录信息
            session('admin_user', $login);
            session('admin_user_sign', $this->dataSign($login));

            runhook('admin_login', $login);

            return $user->id;
        }

        return false;
    }

    /**
     * 获取主题列表
     * @author Author: btc
     * @return bool
     */
    public static function getThemes($cache = false)
    {
        $files = (new Dir)->listFile('./static/system/css/theme/', '*.css');
        $data = array_column($files, 'basename');
        $data = array_map(function($value) {
            return trim($value, '.css');
        }, $data);

        empty($data) && $data = [0, 1, 2, 3, 4];

        if ($cache) {
            session('migu_admin_themes', $data);
        }
        
        return $data;
    }

    /**
     * 设置主题
     * @author Author: btc
     * @return bool
     */
    public static function setTheme($name = 'default', $update = false)
    {
        cookie('migu_admin_theme', $name);
        $result = true;
        if ($update && defined('ADMIN_ID')) {
            $result = self::where('id', ADMIN_ID)->setField('theme', $name);
        }
        return $result;
    }

    /**
     * 判断是否登录
     * @author Author: btc
     * @return bool|array
     */
    public function isLogin() 
    {
        $user = session('admin_user');
        if (isset($user['uid'])) {
            // if (!self::where('id', $user['uid'])->find()) {
            //     return false;
            // }
            return session('admin_user_sign') == $this->dataSign($user) ? $user : false;
        }
        return false;
    }

    /**
     * 退出登陆
     * @author Author: btc
     * @return bool
     */
    public function logout() 
    {
        $user = session('admin_user');
        $uid = $user['uid'] ?? 0;
        session('role_auth_'.($user['uid'] ?? 0), null);
        session('admin_user', null);
        session('admin_user_sign', null);
        runhook('admin_logout', $user);
        Cache::rm('admin_menu_'.$uid.'_'.dblang('admin').'_'.config('sys.app_debug'));
    }

    /**
     * 数据签名认证
     * @param array $data 被认证的数据
     * @author Author: btc
     * @return string 签名
     */
    public function dataSign($data = [])
    {
        if (!is_array($data)) {
            $data = (array) $data;
        }
        ksort($data);
        $code = http_build_query($data);
        $sign = sha1($code);
        return $sign;
    }
}
