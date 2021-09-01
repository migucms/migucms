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
 * 插件基本信息
 */
return [
    // 插件名[必填]
    'name'        => 'mgcms',
    // 插件标题[必填]
    'title'       => '系统基础信息',
    // 模块唯一标识[必填]，格式：插件名.[应用市场ID].plugins.[应用市场分支ID]
    'identifier'  => 'mgcms.mgcms.plugins',
    // 插件图标[必填]
    'icon'        => '/plugins/mgcms/mgcms.png',
    // 插件描述[选填]
    'intro' => '后台首页展示系统基础信息和开发团队信息',
    // 插件作者[必填]
    'author'      => '咪咕CMS',
    // 作者主页[选填]
    'author_url'  => 'http://www.migucms.com',
    // 版本[必填],格式采用三段式：主版本号.次版本号.修订版本号
    // 主版本号【位数变化：1-99】：当模块出现大更新或者很大的改动，比如整体架构发生变化。此版本号会变化。
    // 次版本号【位数变化：0-999】：当模块功能有新增或删除，此版本号会变化，如果仅仅是补充原有功能时，此版本号不变化。
    // 修订版本号【位数变化：0-999】：一般是 Bug 修复或是一些小的变动，功能上没有大的变化，修复一个严重的bug即发布一个修订版。
    'version'     => '1.0.0',
    // 原始数据库表前缀,插件带sql文件时必须配置
    'db_prefix' => 'db_',
    //格式['sort' => '100','title' => '配置标题','name' => '配置名称','type' => '配置类型','options' => '配置选项','value' => '配置默认值', 'tips' => '配置提示'] 各参数设置可参考管理后台->系统->系统功能->配置管理->添加
    'config'    => [
],
];