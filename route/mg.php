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

/**
 * 咪咕CMS框架默认路由，升级会被覆盖，请勿修改
 */

// 插件路由
Route::rule('plugins/:_p/:_c/:_a', 'system/plugins/index?_p=:_p&_c=:_c&_a=:_a');
Route::rule('plugins/:_p/:_c', 'system/plugins/index?_p=:_p&_c=:_c&_a=index');

// 应用商店推送
Route::rule('push/module', 'system/push/module');
Route::rule('push/plugins', 'system/push/plugins');
Route::rule('push/theme', 'system/push/theme');