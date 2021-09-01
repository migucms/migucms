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

use app\system\model\SystemLanguage as LanguageModel;

/**
 * 语言包管理控制器
 * @package app\system\admin
 */
class Language extends Admin
{
    // [通用添加、修改专用] 模型名称，格式：模块名/模型名
    protected $miguModel = 'SystemLanguage';
    // [通用添加、修改专用] 验证器类，格式：app\模块\validate\验证器类名
    protected $miguValidate = 'app\system\validate\SystemLanguage';

    /**
     * 语言包管理首页
     * @author Author: btc
     * @return mixed
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            $data           = [];
            $data['data']   = LanguageModel::order('sort asc')->select();
            $data['code']   = 0;
            return json($data);
        }

        $assign = [];
        $assign['buildTable']['toolbar'] = [
            [
                'title' => '新增',
                'url' => url('add'),
                'class' => 'layui-btn layui-btn-normal layui-btn-sm migu-iframe',
                'options' => [
                    'height' => '470px',
                ]
            ],
            [
                'title' => '删除',
                'url' => url('del'),
                'class' => 'layui-btn layui-btn-danger layui-btn-sm migu-table-ajax',
            ],
        ];

        $assign['buildTable']['config'] = [
            'cols' => [
                [
                    'type' => 'checkbox',
                ],
                [
                    'title' => '语言名称',
                    'field' => 'name',
                ],
                [
                    'title' => '语言编码',
                    'field' => 'code',
                ],
                [
                    'title' => '排序',
                    'field' => 'sort',
                ],
                [
                    'title' => '状态',
                    'field' => 'status',
                    'type' => 'switch',
                    'text' => '关闭|正常',
                ],
                [
                    'button' => [
                        [
                            'title' => '编辑',
                            'class' => 'layui-badge layui-bg-blue migu-iframe',
                            'url' => url('edit'),
                            'options' => [
                                'height' => '470px',
                            ]
                        ],
                        [
                            'title' => '删除',
                            'url' => url('del'),
                            'class' => 'layui-badge migu-tr-del',
                        ],
                    ],

                ],
            ]
        ];

        return $this->assign($assign)->fetch();
    }

    public function buildForm()
    {
        $assign['buildForm']['cancelBtn'] = true;
        $assign['buildForm']['items'] = [
            [
                'type' => 'text',
                'title' => '语言名称',
                'name' => 'name',
                'tips' => '长度建议控制在2-5个字符',
            ],
            [
                'type' => 'text',
                'title' => '语言代码',
                'name' => 'code',
                'tips' => '例如：中文，填写 zh-cn',
            ],
            [
                'type' => 'text',
                'title' => '本地化代码',
                'name' => 'locale',
                'tips' => '例如: en_US.UTF-8,en_US',
            ],
            [
                'type' => 'file',
                'title' => '上传语言包',
                'name' => 'pack',
                'exts' => 'zip',
                'tips' => '如不上传，则后台不支持切换到此语言包',
            ],
            [
                'type' => 'radio',
                'title' => '状态设置',
                'name' => 'status',
                'option' => ['禁用', '启用'],
                'value' => 1,
            ],
            [
                'type' => 'text',
                'title' => '排序设置',
                'name' => 'sort',
                'tips' => '数字越小越靠前',
            ],
            [
                'type' => 'hidden',
                'name' => 'id',
            ],
        ];

        $this->assign($assign);
    }
}
