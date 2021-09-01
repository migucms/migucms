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
 * 插件验证器
 * @package app\system\validate
 */
class Plugins extends Validate
{
    //定义验证规则
    protected $rule = [
		'name|插件名'			=> 'require|alphaDash|unique:system_plugins',
		'title|插件标题'			=> 'require|chsDash',
		'identifier|插件标识'		=> 'require|unique:system_plugins',
    ];
}
