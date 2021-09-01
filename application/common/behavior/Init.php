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

use Request;
use Route;
use think\Container;
use think\facade\Lang;
use app\system\model\SystemModule as SystemModule;

/**
 * 应用初始化行为
 */
class Init
{
    public function run()
    {
        if (defined('INSTALL_ENTRANCE')) {
            return;
        }

        $path = trim(Request::instance()->pathinfo(), '/');
        $bind = empty(Route::getBind()) ? false : true;
        
        if (!empty($path) && !defined('ENTRANCE') && $bind === false) {
            if (strtolower($path) != 'index') {
                $path       = explode('/', $path);
                $module     = $path[0];
                $controller = parse_name((isset($path[1]) ? $path[1] : 'index'), 1);
                $action     = lcfirst(parse_name((isset($path[2]) ? $path[2] : 'index'), 1));
                $action     = str_replace('.'.config('app.url_html_suffix'), '', $action);

                if (method_exists("app\\{$module}\\home\\{$controller}", $action)) {
                    $bind = true;
                } elseif (method_exists("app\\{$module}\\home\\{$controller}", parse_name($action))) {
                    $bind = true;
                } elseif (method_exists("app\\{$module}\\home\\{$controller}", '_empty')) {
                    $bind = true;
                }
            }
        }

        // 设置前台默认模块
        if (!defined('ENTRANCE') && $bind === false) {
            $map    = [];
            $map[]  = ['is_default', '=', 1];
            $map[]  = ['status', '=', 2];
            if ($name = SystemModule::where($map)->cache(true)->value('name')) {
                Container::get('app')->bind($name);
            }
        }
    }
}
