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

namespace app\system\admin;

use app\common\controller\Common;
use app\system\model\SystemMenu as MenuModel;
use app\system\model\SystemRole as RoleModel;
use app\system\model\SystemUser as UserModel;
use app\system\model\SystemLog as LogModel;
use app\system\model\SystemLanguage as LangModel;
use app\system\model\SystemUserRole as IndexModel;
use think\Db;
use think\facade\Env;
use think\facade\Cache;

/**
 * 后台公共控制器
 * @package app\system\admin
 */
class Admin extends Common
{
    // [通用添加、修改] 模型名称，格式：模块名/模型名
    protected $miguModel = '';
    // [通用添加、修改] 表名(不含表前缀) 
    protected $miguTable = '';
    // [通用添加、修改] 验证器类，格式：app\模块\validate\验证器类名
    protected $miguValidate = false;
    // [通用添加] 添加数据验证场景名
    protected $miguAddScene = false;
    // [通用更新] 更新数据验证场景名
    protected $miguEditScene = false;
    // [通用更新] 只读字段定义
    protected $miguReadonly = [];
    // 当前控制器无需鉴权的白名单，但要登录（写方法名即可）
    protected $miguNoAuth = [];
    // 表单赋值
    protected $formData = [];
    // 数据权限设置，可选值：own 个人，org 组织，false 不启用
    protected $dataRight = false;
    // 数据权限字段名
    protected $dataRightField = 'admin_id';
    
    /**
     * 初始化方法
     */
    protected function initialize()
    {
        parent::initialize();
        
        // 判断登陆
        $this->loginInfo = (new UserModel)->isLogin();
        if (!isset($this->loginInfo['uid'])) {
            return $this->error('请登陆之后在操作', ROOT_DIR.config('sys.admin_path'));
        }
        
        if (!defined('ADMIN_ID')) {
            define('ADMIN_ID', $this->loginInfo['uid']);
            define('ADMIN_ROLE', $this->loginInfo['role_id']);

            $curMenu    = MenuModel::getInfo();
            $action     = $this->request->action();
            $curUrl     = strtolower(self::getActUrl());
            
            if ($curMenu) {
                if (empty($this->miguNoAuth) || !in_array($action, $this->miguNoAuth)) {
                    if (!RoleModel::checkAuth($curMenu['id'])) {
                        return $this->error('['.$curMenu['title'].'] 无访问权限');
                    }
                }
            } else if (config('sys.admin_whitelist_verify')) {
                if (empty($this->miguNoAuth) || !in_array($action, $this->miguNoAuth)) {
                    return $this->error('节点不存在或者已禁用！');
                }

                $curMenu = ['title' => '', 'url' => $curUrl, 'id' => 0];
            } else {
                $curMenu = ['title' => '', 'url' => $curUrl, 'id' => 0];
            }

            config('sys.system_log_switch') && $this->_systemLog($curMenu['title']);

            // 如果不是ajax请求，则读取菜单
            if (!$this->request->isAjax()) {
                $languages = (new LangModel)->lists();
                $menus = MenuModel::getAdminMenu();

                $assign = [
                    'miguCurMenu' => $curMenu,
                    'miguMenus' => $menus,
                    'miguTabType' => 0,
                    'miguTabData' => [],
                    'formData' => [],
                    'languages' => $languages,
                    'login' => $this->loginInfo,
                    'miguHead' => '',
                ];
                
                $this->view->engine->layout('system@layout');
                $this->assign($assign);
            }
        }
    }

    /**
     * 系统日志记录
     * @author Author: btc
     * @return string
     */
    private function _systemLog($title)
    {
        // 系统日志记录
        $log            = [];
        $log['uid']     = ADMIN_ID;
        $log['title']   = $title ? $title : '未加入系统菜单';
        $log['url']     = $this->request->url();
        $log['remark']  = '浏览数据';

        if ($this->request->isPost()) {
            $log['remark'] = '保存数据';
        }

        $result = LogModel::where($log)->cache(true)->find();

        $log['param']   = json_encode($this->request->param());
        $log['ip']      = $this->request->ip();

        if (!$result) {
            LogModel::create($log);
        } else {
            $log['id'] = $result->id;
            $log['count'] = $result->count+1;
            LogModel::update($log);
        }
    }

    /**
     * 获取当前方法URL
     * @author Author: btc
     * @return string
     */
    protected function getActUrl() 
    {
        $model      = request()->module();
        $controller = request()->controller();
        $action     = request()->action();
        return $model.'/'.$controller.'/'.$action;
    }
    
    /**
     * [通用方法]添加页面展示和保存
     * @author Author: btc
     * @return mixed
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $postData = $this->request->post();
            
            if ($this->miguValidate) {// 数据验证
                if (strpos($this->miguValidate, '\\') === false ) {
                    if (defined('IS_PLUGINS')) {
                        $this->miguValidate = 'plugins\\'.$this->request->param('_p').'\\validate\\'.$this->miguValidate;
                    } else {
                        $this->miguValidate = 'app\\'.$this->request->module().'\\validate\\'.$this->miguValidate;
                    }
                    
                }

                if ($this->miguAddScene) {
                    $this->miguValidate = $this->miguValidate.'.'.$this->miguAddScene;
                }

                $result = $this->validate($postData, $this->miguValidate);
                if ($result !== true) {
                    return $this->error($result);
                }
            }

            if ($this->miguModel) {// 通过Model添加
                $model = $this->model();
                if (!$model->save($postData)) {
                    return $this->error($model->getError());
                }
            } else if ($this->miguTable) {// 通过Db添加
                if (!Db::name($this->miguTable)->insert($postData)) {
                    return $this->error('保存失败');
                }
            } else {
                return $this->error('当前控制器缺少属性（miguModel、miguTable至少定义一个）');
            }

            return $this->success('保存成功', '');
        }

        method_exists($this, 'buildForm') && $this->buildForm();

        $template = $this->request->param('template', 'form');

        return $this->fetch($template);
    }

    /**
     * [通用方法]编辑页面展示和保存
     * @author Author: btc
     * @return mixed
     */
    public function edit()
    {
        if ($this->request->isPost()) {// 数据验证
            $postData = $this->request->post();
            
            if ($this->miguReadonly) {
                foreach($this->miguReadonly as $v) {
                    if (isset($postData[$v])) {
                        unset($postData[$v]);
                    }
                }
            }

            if ($this->miguValidate) {
                if (strpos($this->miguValidate, '\\') === false ) {
                    if (defined('IS_PLUGINS')) {
                        $this->miguValidate = 'plugins\\'.$this->request->param('_p').'\\validate\\'.$this->miguValidate;
                    } else {
                        $this->miguValidate = 'app\\'.$this->request->module().'\\validate\\'.$this->miguValidate;
                    }
                }

                if ($this->miguEditScene) {
                    $this->miguValidate = $this->miguValidate.'.'.$this->miguEditScene;
                }

                $result = $this->validate($postData, $this->miguValidate);
                if ($result !== true) {
                    return $this->error($result);
                }

            }
        }

        $where = [];
        if ($this->miguModel) {// 通过Model更新
            $model = $this->model();
            $pk = $model->getPk();
            $id = $this->request->param($pk);

            $where[]= [$pk, '=', $id];
            $where  = $this->getRightWhere($where);
            
            if ($this->request->isPost()) {
                if ($model->save($postData, $where) === false) {
                    return $this->error($model->getError());
                }
                return $this->success('保存成功', '');
            }
            
            if (empty($this->formData)) {
                $this->formData = $model->where($where)->find();
            }
        } else if ($this->miguTable) {// 通过Db更新
            $db = Db::name($this->miguTable);
            $pk = $db->getPk();
            $id = $this->request->param($pk);

            $where[]= [$pk, '=', $id];
            $where  = $this->getRightWhere($where);

            if ($this->request->isPost()) {
                if (!$db->where($where)->update($postData)) {
                    return $this->error('保存失败');
                }

                return $this->success('保存成功', '');
            }

            if (empty($this->formData)) {
                $this->formData = $db->where($where)->find();
            }
        } else {
            return $this->error('当前控制器缺少属性（miguModel、miguTable至少定义一个）');
        }
        
        $this->assign('formData', $this->formData);

        method_exists($this, 'buildForm') && $this->buildForm();

        $template = $this->request->param('template', 'form');

        return $this->fetch($template);
    }

    /**
     * [通用方法]状态设置
     * 禁用、启用都是调用这个内部方法
     * @author Author: btc
     * @return mixed
     */
    public function status()
    {
        $val    = $this->request->param('val/d');
        $id     = $this->request->param('id/a');
        $field  = $this->request->param('field/s', 'status');
        
        if (empty($id)) {
            return $this->error('缺少id参数');
        }

        // 以下表操作需排除值为1的数据
        if ($this->miguModel == 'SystemMenu') {
            if (in_array('1', $id) || in_array('2', $id) || in_array('3', $id)) {
                return $this->error('系统限制操作');
            }
        }
        
        if ($this->miguModel) {
            $obj = $this->model();
        } else if ($this->miguTable) {
            $obj = db($this->miguTable);
        } else {
            return $this->error('当前控制器缺少属性（miguModel、miguTable至少定义一个）');
        }
        
        $pk = $obj->getPk();

        $where  = [];
        $where[]= [$pk, 'in', $id];
        $where  = $this->getRightWhere($where);

        $result = $obj->where($where)->setField($field, $val);
        if ($result === false) {
            return $this->error('状态设置失败');
        }

        return $this->success('状态设置成功', '');
    }

    /**
     * [通用方法]删除单条记录
     * @author Author: btc
     * @return mixed
     */
    public function del()
    {
        $id = $this->request->param('id/a');
        
        if (empty($id)) {
            return $this->error('缺少id参数');
        }
        
        if ($this->miguModel) {
            $model = $this->model();
            $pk = $model->getPk();
            $where[] = [$pk, 'in', $id];
            $where = $this->getRightWhere($where);
            if (method_exists($model, 'withTrashed')) {
                $rows = $model->withTrashed()->where($where)->select();
                foreach($rows as $v) {
                    if ($v->trashed()) {
                        $result = $v->delete(true);
                    } else {
                        $result = $v->delete();
                    }

                    if (!$result) {
                        return $this->error($v->getError());
                    }
                }
            } else {
                $row = $model->where($where)->delete();
            }
        } else if ($this->miguTable) {
            $db = db($this->miguTable);
            $pk = $db->getPk();

            $where  = [];
            $where[]= [$pk, 'in', $id];
            $where  = $this->getRightWhere($where);

            $db->where($where)->delete();
        } else {
            return $this->error('当前控制器缺少属性（miguModel、miguTable至少定义一个）');
        }

        return $this->success('删除成功', '');
    }

    /**
     * [通用方法]排序
     * @author Author: btc
     * @return mixed
     */
    public function sort()
    {
        $id     = $this->request->param('id/a');
        $field  = $this->request->param('field/s', 'sort');
        $val    = $this->request->param('val/d');
        
        if (empty($id)) {
            return $this->error('缺少id参数');
        }

        if ($this->miguModel) {
            $obj = $this->model();
        } else if ($this->miguTable) {
            $obj = db($this->miguTable);
        } else {
            return $this->error('当前控制器缺少属性（miguModel、miguTable至少定义一个）');
        }
        
        $pk     = $obj->getPk();

        $where  = [];
        $where[]= [$pk, 'in', $id];
        $where  = $this->getRightWhere($where);

        $result = $obj->where($where)->setField($field, $val);
        if ($result === false) {
            return $this->error('排序设置失败');
        }

        return $this->success('排序设置成功', '');
    }

    /**
     * [通用方法]更新某个字段
     * @author Author: btc
     * @return mixed
     */
    public function setField()
    {
        $id     = $this->request->param('id/a');
        $field  = $this->request->param('field/s');
        $value  = $this->request->param('value');
        
        if (empty($id)) {
            return $this->error('缺少id参数');
        }

        if ($this->miguModel) {
            $obj = $this->model();
        } else if ($this->miguTable) {
            $obj = Db::name($this->miguTable);
        } else {
            return $this->error('当前控制器缺少属性（miguModel、miguTable至少定义一个）');
        }

        $pk     = $obj->getPk();

        $where  = [];
        $where[]= [$pk, 'in', $id];
        $where  = $this->getRightWhere($where);
        
        $result = $obj->where($where)->setField($field, $value);
        if ($result === false) {
            return $this->error('更新失败');
        }

        return $this->success('更新成功', '');
    }

    /**
     * [通用方法]默认设置  
     */
    public function setDefault()
    {
        $id     = $this->request->param('id/d');
        $field  = $this->request->param('field/s', 'is_default');
        $val    = $this->request->param('val');
        
        if (empty($id)) {
            return $this->error('缺少id参数');
        }

        if (!in_array($val, [0, 1])) {
            return $this->error('参数值异常');
        }

        if ($this->miguModel) {
            $obj = $this->model();
        } else {
            $obj = Db::name($this->miguTable);
        }

        if ($val == 1) {
            $obj->where('id', '<>', $id)->setField($field, 0);
        }

        $obj->where('id', '=', $id)->setField($field, $val);

        return $this->success('设置成功');
    }

    /**
     * [通用方法]上传附件
     * @author Author: btc
     * @return mixed
     */
    public function upload()
    {
        $model = new \app\common\model\SystemAnnex;
        
        return json($model::fileUpload());
    }

    /** 
     * 实例化模型类($miguModel)
    */
    protected function model()
    {
        if (property_exists(__CLASS__, 'model')) {
            return $this->model;
        }

        if (!$this->miguModel) {
            $this->error('miguModel属性未定义');
        }

        if (defined('IS_PLUGINS')) {
            if (strpos($this->miguModel, '\\') === false ) {
                $this->miguModel = 'plugins\\'.$this->request->param('_p').'\\model\\'.$this->miguModel;
            }
            $this->model = new $this->miguModel;
        } else {
            if (strpos($this->miguModel, '/') === false ) {
                $this->miguModel = $this->request->module().'/'.$this->miguModel;
            }
            $this->model = model($this->miguModel);
        }

        return $this->model;
    }

    /** 
     * 实例化数据库类
    */
    protected function db($name = '')
    {
        $name = $name ?: $this->miguTable;
        if (!$name) {
            $this->error('miguTable属性未定义');
        }

        $this->db = Db::name($name);

        return $this->db;
    }

    /**
     * 获取同组织下的所有管理员ID
     * @return array
     */
    protected function getAdminIds()
    {
        
        if (ADMIN_ID == 1 || !$this->dataRight) {
            return [];
        }

        $ids = [ADMIN_ID];

        if ($this->dataRight == 'org') {// 组织
            $ids = IndexModel::getOrgUserId(ADMIN_ROLE);
        }

        return $ids;
    }

    /**
     * 获取数据权限 where
     * @param array $where
     * @return array
     */
    protected function getRightWhere($where = [])
    {
        $ids = $this->getAdminIds();
        
        if ($ids) {
            $ids[] = 0;
            $where[] = [$this->dataRightField, 'in', $ids];
        }

        return $where;
    }

    /**
     * 输出layui的json数据
     *
     * @param array $data
     * @param integer $count
     * @return void
     */
    protected function layuiJson($data, $count = 0)
    {
        return json(['data' => $data, 'count' => $count, 'code' => 0]);
    }

}
