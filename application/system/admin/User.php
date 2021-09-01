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

use app\system\model\SystemUser as UserModel;
use app\system\model\SystemRole as RoleModel;
use app\system\model\SystemMenu as MenuModel;
use migu\Tree;

/**
 * 后台用户、角色控制器
 * @package app\system\admin
 */
class User extends Admin
{
    protected $miguModel = 'SystemUser';
    protected $miguValidate = 'SystemUser';
    protected $miguEditScene = 'update';
    protected $miguNoAuth = ['iframe', 'theme', 'menuLayout', 'info', 'getRoleMenu'];

    /**
     * 用户管理
     * @author Author: btc
     * @return mixed
     */
    public function index($q = '')
    {
        if ($this->request->isAjax()) {
            $page   = $this->request->param('page/d', 1);
            $limit  = $this->request->param('limit/d', 20);
            $where[]= ['id', '>', 1];
            $where[]= ['id', '<>', ADMIN_ID];

            $data['data'] = UserModel::with('hasRoles')
                                ->where($where)
                                ->field('id,username,nick,mobile,status,last_login_time')
                                ->page($page)
                                ->limit($limit)
                                ->select();

            $data['count'] = UserModel::where($where)->count('id');
            $data['code'] = 0;

            return json($data);
        }

        $assign['buildTable']['config'] = [
            'page' => true,
            'cols' => [
                [
                    'type' => 'checkbox',
                ],
                [
                    'field' => 'nick',
                    'title' => '昵称',
                    'width' => 150,
                ],
                [
                    'field' => 'username',
                    'title' => '用户名',
                    'width' => 120,
                ],
                [
                    'field' => 'role',
                    'title' => '角色',
                    'templet' => 'function(d) {
                        var str = "";
                        for(var i in d.has_roles) {
                            str += "<span class=\"layui-badge layui-bg-blue\" style=\"margin-right:5px;\">"+d.has_roles[i].name+"</span>";
                        }
                        return str;
                    }',
                ],
                [
                    'field' => 'mobile',
                    'title' => '手机号',
                    'width' => 120,
                ],
                [
                    'field' => 'last_login_time',
                    'title' => '最后登录',
                    'width' => 150,
                ],
                [
                    'field' => 'status',
                    'title' => '状态',
                    'width' => 90,
                    'type'  => 'switch',
                ],
                [
                    'title' => '操作',
                    'width' => 120,
                    'button' => [
                        [
                            'title' => '编辑',
                            'url' => url('edit'),
                            'class' => 'layui-btn layui-btn-xs migu-iframe',
                        ],
                        [
                            'title' => '删除',
                            'url' => url('del'),
                            'class' => 'layui-btn layui-btn-xs layui-btn-danger migu-tr-del',
                        ],
                    ],
                ],
            ],
        ];

        $assign['buildTable']['toolbar'] = [
            [
                'title' => '添加',
                'url' => url('add'),
                'class' => 'migu-iframe',
                'data' => [
                    'title' => '添加内容',
                ],
            ],
            [
                'title' => '删除',
                'url' => url('del'),
                'class' => 'migu-table-ajax layui-btn-danger migu-ajax',
            ],
        ];
        
        $roles = json_encode(RoleModel::column('id,name'), 1);
        $assign['jsCode'] = "var roles = {$roles};";

        return $this->assign($assign)->fetch();
    }

    // 角色下拉生成占位符
    private function buildStr($level)
    {
        $str = '|';
        for ($i=0; $i < $level; $i++) {
            $str .= '--';
        }

        return $level ? $str.' ' : '';
    }

    // 表单构建器
    public function buildForm()
    {
        $roles = RoleModel::where('id', '>', 1)->column('id,pid,name');

        $tree = new Tree(['child' => 'children']);
        $roles = $tree::toTree($roles);
        // $roleList = Tree::treeToList($roleTree);
        $initValue = [];
        $homepage = '';

        if ($this->formData) {
            unset($this->formData['password']);
            $initValue = array_column($this->formData->hasIndexs->toArray(), 'role_id');
            $homepage = $this->formData['homepage'];
        }

        $assign = [];
        $assign['buildForm'] = [
            'cancelBtn' => true,
            'token' => true,
            'items' => [
                [
                    'type'  => 'text',
                    'title' => '昵称/姓名',
                    'name'  => 'nick',
                    'verify' => 'required',
                    'placeholder' => '请输入昵称/姓名',
                ],
                [
                    'type'  => 'text',
                    'title' => '登录账号',
                    'name'  => 'username',
                    'verify' => 'required',
                    'placeholder' => '请输入登录账号',
                ],
                [
                    'type'  => 'password',
                    'title' => '登录密码',
                    'name'  => 'password',
                    'placeholder' => '长度6-18位',
                ],
                [
                    'type'  => 'password',
                    'title' => '确认密码',
                    'name'  => 'password_confirm',
                    'placeholder' => '长度6-18位',
                ],
                [
                    'type'  => 'text',
                    'title' => '联系邮箱',
                    'name'  => 'email',
                    'placeholder' => '[选填]',
                ],
                [
                    'type'  => 'text',
                    'title' => '联系手机',
                    'name'  => 'mobile',
                    'placeholder' => '[选填]',
                ],
                [
                    'type'  => 'radio',
                    'title' => '状态设置',
                    'name'  => 'status',
                    'value' => 1,
                    'option' => [
                        1 => '启用',
                        0 => '禁用',
                    ],
                ],
                [
                    'type' => 'select+',
                    'name' => 'role_ids',
                    'title' => '设置角色',
                    'options' => [
                        'name' => 'role_id',
                        'autoRow' => true,
                        'filterable' => false,
                        'prop' => [
                            'value' => 'id',
                        ],
                        'tree' => [
                            'show' => true,
                            'strict' => false,
                        ],
                        'initValue' => $initValue,
                        'data' => $roles,
                        'on' => 'function(data) {
                            var roleIds = "";
                            for(var i in data.arr) {
                                if (i > 0) roleIds += ",";
                                roleIds += data.arr[i].id;
                            }
                            if (!roleIds) {
                                xmSelectArr["homepage"].update({data:[]});
                                return;
                            }
                            layui.jquery.get("'.url('getRoleMenu').'", {role_id: roleIds}, function(res) {
                                xmSelectArr["homepage"].update({data:res})
                            }, "json");
                        }',
                    ],
                ],
                [
                    'type'  => 'select+',
                    'title' => '默认首页',
                    'name'  => 'homepage',
                    'options' => [
                        'name' => 'homepage',
                        'autoRow' => true,
                        'radio' => true,
                        'clickClose' => true,
                        'filterable' => false,
                        'prop' => [
                            'value' => 'url',
                            'name' => 'title',
                        ],
                        'tree' => [
                            'show' => true,
                            'strict' => false,
                        ],
                        'initValue' => [$homepage],
                    ],
                ],
                [
                    'type'  => 'hidden',
                    'name'  => 'id',
                ],
            ],
        ];

        $ajaxUrl = url('getRoleMenu');
        $roleIds = implode(',', $initValue);
        if ($initValue) {
            $assign['jsCode'] = <<<EOF
        layui.use(['jquery'], function(){
            var $ = layui.jquery;
            setTimeout(function(){
                $.get("{$ajaxUrl}", { role_id: '{$roleIds}' }, function(res) {
                    xmSelectArr["homepage"].update({data:res})
                }, "json");
            }, 600);
        });
EOF;
        }

        $this->assign($assign);
    }

    /**
     * 修改个人信息
     * @author Author: btc
     * @return mixed
     */
    public function info()
    {
        if ($this->request->isPost()) {
            $data = $this->request->only(['nick','email','mobile', 'password', 'password_confirm', '__token__']);
            $data['id'] = ADMIN_ID;
            
            $result = $this->validate($data, 'SystemUser.info');
            if ($result !== true) {
                return $this->error($result);
            }
            
            $model = new UserModel;
            if (!$model->save($data, ['id' => ADMIN_ID])) {
                return $this->error($model->getError() ?? '修改失败');
            }
            return $this->success('修改成功');
        }

        $row = UserModel::where('id', ADMIN_ID)->field('username,nick,email,mobile')->find();
        
        $assign = [];
        $assign['formData'] = $row;
        $assign['buildForm'] = [
            'cancelBtn' => true,
            'token' => true,
            'items' => [
                [
                    'type'  => 'text',
                    'title' => '昵称/姓名',
                    'name'  => 'nick',
                    'verify' => 'required',
                    'placeholder' => '请输入昵称/姓名',
                ],
                [
                    'type'  => 'text',
                    'title' => '登录账号',
                    'name'  => 'username',
                    'readonly' => true,
                ],
                [
                    'type'  => 'password',
                    'title' => '登录密码',
                    'name'  => 'password',
                    'placeholder' => '长度6-18位',
                ],
                [
                    'type'  => 'password',
                    'title' => '确认密码',
                    'name'  => 'password_confirm',
                    'placeholder' => '长度6-18位',
                ],
                [
                    'type'  => 'text',
                    'title' => '联系邮箱',
                    'name'  => 'email',
                    'placeholder' => '[选填]',
                ],
                [
                    'type'  => 'text',
                    'title' => '联系手机',
                    'name'  => 'mobile',
                    'placeholder' => '[选填]',
                ],
            ],
        ];
    
        return $this->assign($assign)->fetch();
    }

    /**
     * 获取指定角色菜单
     *
     * @return void
     */
    public function getRoleMenu($role_id = '')
    {
        if (!$role_id) {
            return json([]);
        }
        $auths = $this->modelSystemRole->where('id', 'in', $role_id)->field('auth')->select()->toArray();
        $menuIds = [];
        foreach ($auths as $k => $v) {
            $menuIds = array_merge($menuIds, $v['auth']);
        }
        $menuIds = array_unique($menuIds);
        
        $menus = $this->modelSystemMenu->where('id', 'in', $menuIds)->where('nav', '=', 1)->column('id,pid,title,url,param');
        foreach ($menus as $k => &$v) {
            $v['param'] && $v['url'] = $v['url'].'?'.$v['param'];
        }

        $tree = new Tree(['child' => 'children', 'name' => 'title']);

        return json($tree::toTree($menus));
    }

    public function edit()
    {
        $id = $this->request->param('id');

        if ($id == ADMIN_ID) {
            $this->error('禁止修改');
        }
 
        return parent::edit();
    }

    /**
     * 布局切换
     * @author Author: btc
     * @return mixed
     */
    public function iframe()
    {
        $val = UserModel::where('id', ADMIN_ID)->value('iframe');
        $val = $val == 1 ? 0 : 1;

        if (!UserModel::where('id', ADMIN_ID)->setField('iframe', $val)) {
            return $this->error('切换失败');
        }

        cookie('migu_iframe', $val);

        return $this->success('请稍等，页面切换中...', url('system/index/index'));
    }

    /**
     * 菜单布局切换
     * @author Author: btc
     * @return mixed
     */
    public function menuLayout()
    {
        $val = UserModel::where('id', ADMIN_ID)->value('menu_layout');
        $val = $val == 1 ? 0 : 1;

        if (!UserModel::where('id', ADMIN_ID)->setField('menu_layout', $val)) {
            return $this->error('切换失败');
        }

        cookie('migu_menu_layout', $val);

        return $this->success('请稍等，页面切换中...', url('system/index/index'));
    }

    /**
     * 主题设置
     * @author Author: btc
     * @return mixed
     */
    public function setTheme()
    {
        $theme = $this->request->param('theme');
        if (UserModel::setTheme($theme, true) === false) {
            return $this->error('设置失败');
        }
        return $this->success('设置成功');
    }
}
