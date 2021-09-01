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

namespace app\system\admin;

use app\common\model\SystemAnnex as AnnexModel;

/**
 * 附件控制器
 * @package app\system\admin
 */

class Annex extends Admin
{

    /**
     * 附件管理
     * @return mixed
     */
    public function index() 
    {
        return $this->fetch();
    }

    /**
     * 附件上传
     */
    public function upload()
    {
        return json(AnnexModel::fileUpload());
    }

    /**
     * favicon 图标上传
     * @return json
     */
    public function favicon()
    {
        return json(AnnexModel::favicon());
    }

    /**
     * 上传保护文件
     * @return json
     */
    public function protect()
    {
        return json(AnnexModel::protect());
    }
}
