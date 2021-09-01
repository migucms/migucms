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

use app\system\model\SystemHook as HookModel;
use app\system\model\SystemHookPlugins as HookPluginsModel;
use app\system\model\SystemPlugins as PluginsModel;
use think\facade\Cache;
use think\facade\Env;

/**
 * 注册钩子
 * @package app\common\behavior
 */
class Hook
{
    public function run()
    {
        // 安装操作直接return
        if (defined('INSTALL_ENTRANCE')) return;
        
        $tags = include Env::get('app_path'). 'tags.php';
        if (isset($tags['system_admin_index'])) {
            return;
        }
        
        $hookPlugins    = Cache::get('hook_plugins');
        $hooks          = Cache::get('hooks');
        $plugins        = Cache::get('plugins');
        if (!$hookPlugins) {
            $hooks          = HookModel::where('status', 1)->column('status', 'name');
            $plugins        = PluginsModel::where('status', 2)->column('status', 'name');
            $hookPlugins    = HookPluginsModel::where('status', 1)
                                                ->field('hook,plugins')
                                                ->order('sort')
                                                ->select();
            // 非开发模式，缓存数据
            if (config('app_debug') === false) {
                Cache::tag('hs_plugins')->set('hook_plugins', $hookPlugins);
                Cache::tag('hs_plugins')->set('hooks', $hooks);
                Cache::tag('hs_plugins')->set('plugins', $plugins);
            }
        }
        // 全局插件
        if ($hookPlugins) {
            foreach ($hookPlugins as $value) {
                if (isset($hooks[$value->hook]) && isset($plugins[$value->plugins])) {
                    \Hook::add($value->hook, get_plugins_class($value->plugins));
                }
            }
        }
    }
}
