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


use app\system\model\SystemUser as UserModel;
use app\system\model\SystemUserRole as IndexModel;

/**
 * 后台角色模型
 * @package app\system\model
 */
class SystemRole extends SystemBase
{

    use \app\common\library\traits\Model;

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

    // 模型事件
    public static function init()
    {

        // 新增后
        self::event('after_insert', function ($obj) {

            if (!isset($obj->id)) {
                return false;
            }
            $obj->addChilds($obj->id);
            return true;
        });

        // 更新前
        self::event('before_update', function ($obj) {

            if ($obj->pid == $obj->id) {
                $obj->error = '禁止将父级角色设置为自己';
                return false;
            }
            
            $roles = explode(',', ADMIN_ROLE);
            if (in_array($obj->id, $roles)) {
                $obj->error = '禁止修改当前角色';
                return false;
            }

            $row = self::where('id', $obj->id)->find();
            $obj->delChilds($obj->id, $row->pid);

            return true;

        });

        // 更新后
        self::event('after_update', function ($obj) {
            
            if ($obj->pid == 0) {
                return true;
            }

            $obj->addChilds($obj->id, $obj->pid);
            
            return true;
        
        });


        // 删除前
        self::event('before_delete', function ($obj) {
            if (strpos($obj->childs, ',')) {
                $obj->error = '请先删除当前角色下的所有子角色';
                return false;
            }

            if (IndexModel::where('role_id', 'in', $obj->id)->find()) {
                $obj->error = '已有管理员绑定此角色（请先取消绑定）';
                return false;
            }

            return true;
        });

        // 删除后
        self::event('after_delete', function ($obj) {
            $obj->delChilds($obj->id, $obj->pid);
            return true;
        });
    }

    /**
     * 获取所有角色(下拉列)
     * @param int $id 选中的ID
     * @author Author: btc
     * @return string
     */
    public static function getOption($id = 0)
    {
        $rows = self::column('id,name');
        $str = '';
        foreach ($rows as $k => $v) {
            if ($k == 1) {// 过滤超级管理员角色
                continue;
            }
            if ($id == $k) {
                $str .= '<option value="'.$k.'" selected>'.$v.'</option>';
            } else {
                $str .= '<option value="'.$k.'">'.$v.'</option>';
            }
        }
        return $str;
    }

    /**
     * 检查访问权限
     * @param int $id 需要检查的节点ID
     * @author Author: btc
     * @return bool
     */
    public static function checkAuth($id = 0)
    {
        // 超级管理员直接返回true
        if (ADMIN_ID == 1) {
            return true;
        }

        // 获取当前角色的权限明细
        $auths = (array)session('role_auth_'.ADMIN_ID);
        if (!$auths) {
            $auths = self::getRoleAuth(ADMIN_ROLE);

            // 非开发模式，缓存数据
            config('sys.app_debug') == 0 && session('role_auth_'.ADMIN_ID, $auths);
        }

        if (!$auths) return false;

        return in_array($id, $auths);
    }

    /**
     * 获取角色权限ID集
     */
    public static function getRoleAuth($id)
    {
        $childs = self::where('id', 'in', $id)->column('childs');
        $childs = implode(',', $childs);
        $rows   = self::where('id', 'in', $childs)->where('status', '=', 1)->field('auth')->select();
        $auths  = [];

        foreach($rows as $k => $v) {
            $auths = array_merge($auths, $v['auth']);
        }
        
        return array_unique($auths);
    }
}
