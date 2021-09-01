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

namespace app\system\validate;

use think\Validate;

/**
 * 角色验证器
 * @package app\system\validate
 */
class SystemLanguage extends Validate
{
    //定义验证规则
    protected $rule = [
        'name|语言名称' => 'require|unique:system_language',
        'code|语言代码'  => 'require|unique:system_language',
    ];

    //定义验证提示
    protected $message = [
        'name.require' => '语言名称不允许为空',
        'name.unique' => '语言名称已存在',
        'code.require' => '语言代码不允许为空',
        'code.unique' => '语言代码已存在',
    ];
}
