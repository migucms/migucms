<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 刘志淳 <chun@engineer.com>
// +----------------------------------------------------------------------

namespace app\system\command\make;

use app\system\command\Make;
use think\console\input\Option;
use think\facade\Config;

class Controller extends Make
{
    protected $type = "Controller";

    protected function configure()
    {
        parent::configure();
        $this->setName('make:controller')
            ->addOption('layer', null, Option::VALUE_OPTIONAL, 'Controller layering, optional values: admin、home、api', 'home')
            ->setDescription('Create a new resource controller class');
    }

    protected function getStub()
    {
        $stubPath = __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR;
        $layer = $this->input->getOption('layer');

        if (in_array($layer, ['admin', 'home'])) {
            return $stubPath . 'controller_'.$this->input->getOption('layer').'.stub';
        }

        return $stubPath . 'controller_home.stub';
    }

    protected function getClassName($name)
    {
        return parent::getClassName($name) . (Config::get('controller_suffix') ? ucfirst(Config::get('url_controller_layer')) : '');
    }

    protected function getNamespace($appNamespace, $module)
    {
        $layer = $this->input->getOption('layer');
        return parent::getNamespace($appNamespace, $module) . '\\'.$layer;
    }

}
