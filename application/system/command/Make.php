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

namespace app\system\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\input\Argument;
use think\console\Output;
use think\facade\App;
use think\facade\Config;
use think\facade\Env;

abstract class Make extends Command
{
    protected $type;

    protected $appType = 'module';

    protected $namespace = 'app';

    protected $appPath = '';

    abstract protected function getStub();

    protected function configure()
    {
        $this->addArgument('name', Argument::REQUIRED, "The name of the class")
            ->addOption('type', 't', Option::VALUE_OPTIONAL, 'Application Type, optional values: module, plugins', 'module')
            ->addOption('force', 'f', Option::VALUE_OPTIONAL, 'Application type, optional values: module, plugins');
    }
    
    protected function execute(Input $input, Output $output)
    {
        
        $name = trim($input->getArgument('name'));

        if (strpos($name, '/')) {
            $exp = explode('/', $name);
            $last = parse_name(array_pop($exp), 1);
            foreach($exp as &$v) {
                $v = parse_name($v);
            }
            $name = implode('/', $exp).'/'.$last;
        } else {
            $name = parse_name($name, 1);
        }
        
        $this->appType = $input->getOption('type');

        if ($this->appType == 'plugins') {
            $this->namespace = 'plugins';
            $this->appPath = Env::get('root_path').'plugins'.DIRECTORY_SEPARATOR;
        } else {
            $this->namespace = App::getNamespace();
            $this->appPath = Env::get('app_path');
        }
        
        $classname = $this->getClassName($name);
        $pathname = $this->getPathName($classname);

        if (is_file($pathname) && !$input->hasOption('force')) {
            $output->writeln('<error>' . $this->type . ' already exists! to override, set the --force parameter to true</error>');
            return false;
        }

        if (!is_dir(dirname($pathname))) {
            mkdir(dirname($pathname), 0755, true);
        }

        file_put_contents($pathname, $this->buildClass($classname));

        $output->writeln('<info>' . $this->type . ' created successfully.</info>');

    }

    protected function buildClass($name)
    {
        $stub = file_get_contents($this->getStub());

        $namespace = trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');

        $class = str_replace($namespace . '\\', '', $name);

        return str_replace(['{%className%}', '{%actionSuffix%}', '{%namespace%}', '{%app_namespace%}'], [
            $class,
            Config::get('action_suffix'),
            $namespace,
            $this->namespace,
        ], $stub);
    }

    protected function getPathName($name)
    {
        
        $name = str_replace($this->namespace . '\\', '', $name);

        return $this->appPath . ltrim(str_replace('\\', '/', $name), '/') . '.php';
    }

    protected function getClassName($name)
    {

        $appNamespace = $this->namespace;

        if (strpos($name, $appNamespace . '\\') !== false) {
            return $name;
        }

        if (Config::get('app_multi_module')) {
            if (strpos($name, '/')) {
                list($module, $name) = explode('/', $name, 2);
            } else {
                $module = 'common';
            }
        } else {
            $module = null;
        }

        if (strpos($name, '/') !== false) {
            $name = str_replace('/', '\\', $name);
        }
        
        return $this->getNamespace($appNamespace, $module) . '\\' . $name;
    }

    protected function getNamespace($appNamespace, $module)
    {
        return $module ? ($appNamespace . '\\' . $module) : $appNamespace;
    }

}
