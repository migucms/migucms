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

namespace app\system\model;

use Env;
use migu\Dir;
use migu\PclZip;
use think\facade\Cache;

/**
 * 钩子模型
 * @package app\system\model
 */
class SystemLanguage extends SystemBase
{
    protected $autoWriteTimestamp = false;

    // 缓存标签名
    const CACHE_TAG = 'system@language';

    public static function init()
    {
        // 新增前
        self::event('before_insert', function ($obj) {
            return true;
        });

        // 新增后
        self::event('after_insert', function ($obj) {
            Cache::clear(self::CACHE_TAG);
            // 安装语言包
            return $obj->install($obj);
        });

        // 更新前
        self::event('before_update', function ($obj) {
            if ($obj['id'] == 1) {// 禁止修改ID为1的语言包
                return false;
            }
            
            $row = self::where('id', $obj['id'])->find();
            if (empty($row['pack']) && !empty($obj['pack'])) {
                return $obj->install($obj);
            }

            return true;
        });

        // 删除后
        self::event('after_update', function($obj) {
            Cache::clear(self::CACHE_TAG);
        });

        // 删除前
        self::event('before_delete', function ($obj) {
            return $obj->deleteLang($obj);
        });

        // 删除后
        self::event('after_delete', function($obj) {
            Cache::clear(self::CACHE_TAG);
        });
    }

    /**
     * 获取语言包列表
     * @param  string $code  获取指定语言
     * @author Author: btc
     * @return mixed
     */
    public static function lists($code = '')
    {
        $result = Cache::get('system_language');
        if (!$result) {
            $result = self::where('status', '=', 1)->order('sort asc')->column('id,code,name,icon,pack', 'code');
            Cache::tag(self::CACHE_TAG)->set('system_language', $result);
        }

        if ($code) {
            if (isset($result[$code])) {
                return $result[$code]['id'];
            } else if ($result) {
                $lang = current($result);
                return $lang['id'];
            } else {
                return 0;
            }
        }

        return $result;
    }

    /**
     * 安装语言包
     * @param  object $obj 当前的模型对象实例
     * @author Author: btc
     * @return mixed
     */
    public function install($obj) 
    {
        if (empty($obj['pack'])) {
            $obj->error = '语言包不存在';
            return false;
        }

        $pack = '.'.$obj['pack'];
        if (file_exists($pack)) {

            $decomPath = Env::get('runtime_path').'lang/';
            if (!is_dir($decomPath)) {
                Dir::create($decomPath, 0777);
            }

            // 解压升级包
            $archive = new PclZip();
            $archive->PclZip($pack);
            if(!$archive->extract(PCLZIP_OPT_PATH, $decomPath, PCLZIP_OPT_REPLACE_NEWER)) {
                $obj->error = '语言包解压失败！';
                return false;
            }

            // 导入语言包到admin
            $adminLang = $decomPath.'system/'.$obj['code'].'.php';
            if (file_exists($adminLang)) {
                copy($adminLang, Env::get('app_path').'system/lang/'.$obj['code'].'.php');
            }

            // 导入语言包到common
            $commonLang = $decomPath.'common/'.$obj['code'].'.php';
            if (file_exists($commonLang)) {
                if (!is_dir(Env::get('app_path').'common/lang/')) {
                    Dir::create(Env::get('app_path').'common/lang/');
                }
                copy($commonLang, Env::get('app_path').'common/lang/'.$obj['code'].'.php');
            }

            // 导入后台菜单
            if (file_exists($decomPath.'menu.php')) {
                $menu = include_once $decomPath.'menu.php';
                $menuData = [];
                foreach ($menu as $key => $v) {
                    $menuData[$key]['menu_id'] = $v['menu_id'];
                    $menuData[$key]['title'] = $v['title'];
                    $menuData[$key]['lang'] = $obj['id'];
                }

                if ($menuData) {
                    db('system_menu_lang')->insertAll($menuData);
                }
            }

            Dir::delDir($decomPath);
        }

        return true;
    }

    /**
     * 删除语言包
     * @param  object $obj 当前的模型对象实例
     * @author Author: btc
     * @return mixed
     */
    public function deleteLang($obj)
    {
        if ($obj['id'] == 1) {
            return false;
        }
        
        // 删除语言包相关文件
        $admin_lang = Env::get('app_path').'system/lang/'.$obj['code'].'.php';
        if (file_exists($admin_lang)) {
            @unlink($admin_lang);
        }
        
        $common_lang = Env::get('app_path').'common/lang/'.$obj['code'].'.php';
        if (file_exists($common_lang)) {
            @unlink($common_lang);
        }

        if (file_exists('.'.$obj['pack'])) {
            @unlink('.'.$obj['pack']);
        }

        // 删除管理菜单
        db('system_menu_lang')->where('lang', $obj['id'])->delete();

        return true;
    }
}
