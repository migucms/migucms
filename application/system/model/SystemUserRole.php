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

use app\system\model\SystemRole as RoleModel;
use think\facade\Cache;

/**
 * 管理员角色索引模型
 * @package app\system\model
 */
class SystemUserRole extends SystemBase
{
    protected $autoWriteTimestamp = false;

    // 缓存标签名
    const CACHE_TAG = 'system@user_role';

    protected static function init()
    {
        // 新增后
        self::event('after_insert', function ($obj) {
            Cache::rm(self::CACHE_TAG);
        });

        // 更新后
        self::event('after_update', function ($obj) {
            Cache::rm(self::CACHE_TAG);
        });

        // 删除后
        self::event('after_delete', function ($obj) {
            Cache::rm(self::CACHE_TAG);
        });
    }

    /**
     * 获取同组织下的所有管理员ID
     *
     * @param string|array $roleIds
     * @return array
     */
    public static function getOrgUserId($roleIds)
    {
        $cacheName = 'org_user_id_'.$roleIds;
        $ids = Cache::get($cacheName);
        if (!$ids) {
            $roles  = RoleModel::where('id', 'in', $roleIds)->field('childs')->select()->toArray();
            $roles  = array_column($roles, 'childs');
            $roles  = implode(',', $roles);
            $roles  = explode(',', $roles);
            $roles  = array_unique($roles);
            $ids    = self::where('role_id', 'in', $roles)->distinct(true)->column('user_id');

            Cache::tag(self::CACHE_TAG)->set($cacheName, $ids);
        }
        
        return $ids;
    }
}
