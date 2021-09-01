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

/**
 * 钩子模型
 * @package app\system\model
 */
class SystemHook extends SystemBase
{

    /**
     * 钩子入库
     * @param array $data 入库数据
     * @author Author: btc
     * @return bool
     */  
    public function storage($data = [])
    {
        if (empty($data)) {
            $data = request()->post();
        }

        // 如果钩子名称存在直接返回true
        if (self::where('name', $data['name'])->find()) {
            return true;
        }

        // 验证
        $validate = new \app\system\validate\Hook;;
        if($validate->check($data) !== true) {
            $this->error = $validate->getError();
            return false;
        }

        if (isset($data['id']) && !empty($data['id'])) {
            $res = $this->update($data);
        } else {
            $res = $this->create($data);
        }
        if (!$res) {
            $this->error = '保存失败！';
            return false;
        }
        
        return $res;
    }

    /**
     * 删除钩子
     * @param string $source 来源名称
     * @author Author: btc
     * @return bool
     */    
    public static function delHook($source = '')
    {
        if (empty($source)) {
            return false;
        }

        if (self::where('source', $source)->delete() === false) {
            return false;
        }
        return true;
    }
}
