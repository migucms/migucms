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
// [ 应用入口文件 ]
namespace think;

header('Content-Type:text/html;charset=utf-8');

// 定义应用目录
define('APP_PATH', __DIR__ . '/application/');
// 加载基础文件
require __DIR__ . '/../thinkphp/base.php';
if(!is_file('./../install.lock')) {
	define('INSTALL_ENTRANCE', true);
    Container::get('app')->bind('install')->run()->send();

} else {

    Container::get('app')->run()->send();
    
}