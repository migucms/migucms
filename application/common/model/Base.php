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

namespace app\common\model;

use think\Model;
use think\Db;

class Base extends Model
{
    /**
     * 自动获取模型层、验证器层、服务层、数据库实例（支持跨模块）
     */
    public function __get($name)
    {
        $class  = $name;
        if (in_array(substr($name, 0, 3), ['set', 'get'])) {
            return parent::__get($class);
        }

        $layer  = get_layer($name);
        if ($layer) {
            $name = ltrim($name, $layer); 
            if ($layer == 'db') {
               return $this->$class = Db::name($name);
            }
            $use = 'app\\'.request()->module().'\\'.$layer.'\\'.$name;
        } else if (substr($name, 0, 7) == 'plugins') {
            $name   = ltrim($name, 'plugins');
            $layer  = get_layer(strtolower($name));
            $use    = 'plugins\\'.request()->param('_p').'\\'.$layer.'\\'.ltrim($name, ucfirst($layer));
        } else {
            $parseName  = parse_name($name);
            $exp        = explode('_', $parseName);
            $name       = lcfirst(ltrim($name, $exp[0]));
            $layer      = get_layer($name);
            if ($layer) {
                $name   = ltrim($name, $layer); 
                $use    = 'app\\'.$exp[0].'\\'.$layer.'\\'.$name;
            } else {
                return parent::__get($class);
            }
        }
        
        return $this->$class = (new $use);
    }

    public function __set($name, $value)
    {
        $layer  = get_layer($name);
        if (!$layer) {
            parent::__set($name, $value);
        }

        $this->$name = $value;
    }
}