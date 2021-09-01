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

use app\system\model\SystemRole as RoleModel;
use app\system\model\SystemMenu as MenuModel;
use migu\Tree;

/**
 * 角色控制器
 * @package app\system\admin
 */

class Role extends Admin
{
    public $tabData = [];
    protected $miguModel = 'SystemRole';

    /**
     * 角色列表
     * @author Author: btc
     * @return mixed
     */
    public function index()
    {
        $rows = RoleModel::where('id', '>', 1)->field('id,pid,name,ctime,intro,status')->select();
        $data = [];
        if ($rows) {
            $tree = Tree::toTree($rows->toArray());
            $data = Tree::treeToList($tree);
        }
        
        $assign['buildTable']['config'] = [
            'page' => false,
            'data' => $data,
            'cols' => [
                [
                    'type' => 'checkbox',
                ],
                [
                    'field' => 'name',
                    'title' => '角色名称',
                    'templet' => 'function(d) {
                        return "<span style=\'padding-left:"+(d.level * 15)+"px;\'> "+(d.level > 0 ? \'├ \' : \'\')+d.name+"</span>";
                    }',
                ],
                [
                    'field' => 'intro',
                    'title' => '角色简介',
                ],
                [
                    'field' => 'status',
                    'title' => '状态',
                    'width' => 90,
                    'type'  => 'switch',
                ],
                [
                    'title' => '操作',
                    'width' => 200,
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

        return $this->assign($assign)->fetch();
    }
    
    // 表单构建器
    protected function buildForm()
    {
        $action = $this->request->action();
        $menus = MenuModel::where('uid', '=', 0)
                            ->where('title', '<>', '')
                            ->where('status', '=', 1)
                            ->column([
                                'pid',
                                'title',
                                'id' => 'spread',
                                "IF(id > 0,'auth[]', 'auth[]')" => 'field'
                            ], 'id');
                            
        if ($this->formData) {// 这个是为layui写的，待layui的tree组件完善后就可以删除此段代码
            $checked = MenuModel::where('id', 'in', $this->formData['auth'])->column('id');
            foreach ($checked as $k => $v) {
                if (MenuModel::where('pid', '=', $v)->find()) {// 删除父级节点
                    unset($checked[$k]);
                }
            }
            $this->formData['auth'] = $checked;
            $this->assign('formData', $this->formData);
        }
        
        $roles = RoleModel::where('id', '>', 1)->column('id,pid,name');
        $roleTree = Tree::toTree($roles);

        $tree = new Tree(['child' => 'children']);
        $id = $this->request->param('id/d', 0);
        
        $assign = [];
        $assign['buildForm'] = [
            'cancelBtn' => true,
            'group' => [
                [
                    'title' => '基本信息',
                    'items' => [
                        [
                            'type'  => 'select',
                            'title' => '父级角色',
                            'name'  => 'pid',
                            'option' => Tree::toOptions($roleTree, '', [$id]),
                            'tips' => '父级将拥有子级所有权限',
                        ],
                        [
                            'type'  => 'text',
                            'title' => '角色名称',
                            'name'  => 'name',
                            'verify' => 'required',
                            'placeholder' => '请输入角色名称',
                        ],
                        [
                            'type'  => 'textarea',
                            'title' => '角色简介',
                            'name'  => 'intro',
                            'placeholder' => '[选填] 角色简介',
                        ],
                        [
                            'type'  => 'radio',
                            'title' => '角色状态',
                            'name'  => 'status',
                            'value' => 1,
                            'option' => [
                                1 => '启用',
                                0 => '禁用',
                            ],
                        ],
                        [
                            'type'  => 'hidden',
                            'name'  => 'id',
                        ],
                    ],
                ],
                [
                    'title' => '设置权限',
                    'items' => [
                        [
                            'type' => 'tree',
                            'name' => 'auth',
                            'title' => '请勾选权限',
                            'options' => [
                                'showCheckbox' => true,
                                'data' => $tree::toTree($menus),
                            ],
                        ]
                    ],
                ],
            ],
        ];
        $this->assign($assign);
    }
}
