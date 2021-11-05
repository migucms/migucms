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

namespace app\common\behavior;

use app\system\model\SystemConfig as ConfigModel;
use app\system\model\SystemModule as ModuleModel;
use app\system\model\SystemPlugins as PluginsModel;
use think\facade\Env;
use think\facade\Request;
use think\facade\Route;
use think\facade\Lang;
use think\facade\View;
use think\facade\Config;

/**
 * 初始化基础配置行为
 * 将扩展的全局配置本地化
 */
class Base
{
    public function run()
    {
        define('MIGU_VERSION', '2.0.41');
        define('ROOT_PATH', Env::get('root_path'));
        define('IN_SYSTEM', true);
        define('ROOT_DIR', '/');
        
        // 系统版本
        $version = include_once(Env::get('root_path').'version.php');
    
        Config::set($version);
        
        // 获取当前模块名称
        $module = strtolower(Request::module());

        
        // 安装操作直接return
        if (defined('INSTALL_ENTRANCE')) {
            return;
        }

        // 插件配置
        $pluginsConf = PluginsModel::getConfig();
        // 模块配置
        $moduleConf = ModuleModel::getConfig();
        // 系统配置
        $config = ConfigModel::getConfig();
        Config::set(array_merge($pluginsConf, $moduleConf, $config));
        // 判断模块是否存在且已安装
        $theme = 'default';
        if (in_array($module, ['index', 'system']) === false) {
            $modInfo = ModuleModel::where(['name' => $module, 'status' => 2])
                                    ->cache(Env::get('app_debug') ? false : true)
                                    ->find();
            if (!$modInfo) {
                exit($module.' 模块可能未启用或者未安装！');
            }

            // 设置模块的默认主题
            $theme = $modInfo['theme'] ? $modInfo['theme'] : 'default';
        }
        
        //静态目录扩展配置
        $themePath = '/theme/'.$module.'/'.$theme.'/';
        $viewReplaceStr = [
            // 站点根目录
            '__ROOT_DIR__'      => '/',
            // 静态资源根目录
            '__STATIC__'        => '/static',
            // 文件上传目录
            '__UPLOAD__'        => '/upload',
            // 插件目录
            '__PLUGINS__'       => '/plugins',
            // 后台公共静态目录
            '__ADMIN_CSS__'     => '/static/system/css',
            '__ADMIN_JS__'      => '/static/system/js',
            '__ADMIN_IMG__'     => '/static/system/image',
            // 后台模块静态目录
            '__ADMIN_MOD_CSS__' => '/static/'.$module.'/css',
            '__ADMIN_MOD_JS__'  => '/static/'.$module.'/js',
            '__ADMIN_MOD_IMG__' => '/static/'.$module.'/image',
            // 前台公共静态目录
            '__PUBLIC_CSS__'    => '/static/css',
            '__PUBLIC_JS__'     => '/static/js',
            '__PUBLIC_IMG__'    => '/static/image',
            // 前台模块静态目录
            '__CSS__'           => $themePath.'static/css',
            '__JS__'            => $themePath.'static/js',
            '__IMG__'           => $themePath.'static/image',
            '__STATIC_MOD__'    =>'/static/'.$module,
            // WAP前台模块静态目录
            '__WAP_CSS__'       => $themePath.'wap/static/css',
            '__WAP_JS__'        => $themePath.'wap/static/js',
            '__WAP_IMG__'       => $themePath.'wap/static/image',
        ];
        
        if ($pName = Request::param('_p')) {
            $static = '/static/plugins/'.$pName.'/static/';
            $viewReplaceStr['__PLUGINS_CSS__']  = $static.'css';
            $viewReplaceStr['__PLUGINS_JS__']   = $static.'js';
            $viewReplaceStr['__PLUGINS_IMG__']  = $static.'image';
        }

        View::config(['tpl_replace_string' => $viewReplaceStr]);

        if (defined('ENTRANCE') && ENTRANCE == 'admin') {
            if ($module == 'index') {
                header('Location: '.url('system/publics/index'));
                exit;
            }
            
            self::setLang('admin');
        } else {
            if (config('base.site_status') != 1) {
                exit('站点已关闭！');
            }

            $domain = Request::domain();
            $wap    = config('base.wap_domain');
            $viewPath = 'theme/'.$module.'/'.$theme.'/';
            // 定义前台模板路径[分手机和PC]
            
            if (Request::isMobile() === true &&
                config('base.wap_site_status') &&
                file_exists('./'.$viewPath.'wap/')) {
                if (!Request::isAjax() && $wap && $wap != $domain) {
                    header('Location: '.$wap.Request::url());
                    exit();
                }

                $viewPath .= 'wap/';
            } elseif (config('base.wap_site_status') && $domain == $wap) {
                $viewPath .= 'wap/';
            }
            
            View::config(['view_path' => $viewPath]);
                
            self::setLang();
        }

        define('MIGU_LANG', Lang::range());
    }

    // 设置前台默认语言到cookie
    private function setLang($admin = '')
    {
        $cookieName = $admin.'_language';
        $lang = cookie($cookieName);
        if (isset($_GET['lang']) && !empty($_GET['lang'])) {
            cookie($cookieName, $_GET['lang']);
        } elseif (!$lang) {
            $lang = Lang::range();
            cookie($cookieName, $lang);
        }
    }
}
