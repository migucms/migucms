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

use app\system\model\SystemUser as UserModel;

/**
 * 后台日志模型
 * @package app\system\model
 */
class SystemLog extends SystemBase
{
    
    public function hasUser()
    {
        return $this->hasOne('SystemUser', 'id', 'uid');
    }
}
