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

namespace app\system\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Build as AppBuild;
use think\facade\Env;
use think\facade\Config;
use migu\Dir;
use app\system\model\SystemModule as ModuleModel;
use app\system\model\SystemPlugins as PluginsModel;
use app\system\model\SystemMenu as MenuModel;

class Build extends Command
{
    const DS = DIRECTORY_SEPARATOR;

    /**
     * 应用类型（module,plugins）
     *
     * @var string
     */
    public $appType = 'module';

    /**
     * 应用名称
     *
     * @var string
     */
    public $app = '';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('build')
            ->addOption('title', 't', Option::VALUE_OPTIONAL, 'Title name')
            ->addOption('module', 'm', Option::VALUE_OPTIONAL, 'Module name', null)
            ->addOption('plugins', 'p', Option::VALUE_OPTIONAL, 'Plugins name', null)
            ->addOption('force', 'f', Option::VALUE_OPTIONAL, 'Forced coverage', null)
            ->addOption('dir', 'd', Option::VALUE_OPTIONAL, 'File Directory. Multiple directories separated by commas')
            ->addOption('author', 'a', Option::VALUE_OPTIONAL, 'Author', null)
            ->addOption('url', 'u', Option::VALUE_OPTIONAL, 'Developer URL', null)
            ->addOption('identifier', 'i', Option::VALUE_OPTIONAL, 'Uniquely identifies', null)
            ->addOption('prefix', null, Option::VALUE_OPTIONAL, 'Data table prefix', Config::get('database.prefix', 'db_'))
            ->addOption('intro', null, Option::VALUE_OPTIONAL, 'Application intro', null)
            ->addOption('delete', null, Option::VALUE_OPTIONAL, 'Delete application', null)
            ->addOption('install', null, Option::VALUE_OPTIONAL, 'Install application', null)
            ->addOption('uninstall', null, Option::VALUE_OPTIONAL, 'Uninstall application', null)
            ->setDescription('Build Application Dirs');
    }

    protected function execute(Input $input, Output $output)
    {
        !defined('DS') && define('DS', DIRECTORY_SEPARATOR);
        define('ADMIN_ID', 1);
        define('IN_SYSTEM', true);
        
        if ($input->hasOption('module')) {
            $this->app = $input->getOption('module');
        } else if ($input->hasOption('plugins')) {
            $this->app = $input->getOption('plugins');
            $this->appType = 'plugins';
        } else {
            $output->error('Module and plugin cannot be empty at the same time');
            return;
        }

        if ($input->getOption('install')) {
            $result = $this->installApp();
        } else if ($input->getOption('uninstall')) {
            $result = $this->uninstallApp();
        } else if ($input->getOption('delete')) {
            $result = $this->deleteApp();
        } else {
            if (!$input->hasOption('title')) {
                $output->error('title must');
                return;
            }
            
            $dir = [];
            if ($input->hasOption('dir')) {
                $dir = explode(',', $input->getOption('dir'));
            }
            
            if ($this->appType == 'plugins') {
                $result = $this->buildPlugins($dir);
            } else {
                $result = $this->buildModule($dir);
            }
        }

        if ($result === true) {
            $output->info('Successed');
        } else {
            $output->error($result);
        }
    }

    /**
     * 卸载应用
     *
     * @return bool
     */
    protected function uninstallApp()
    {
        if ($this->appType == 'plugins') {
            $model = new PluginsModel;
        } else {
            $model = new ModuleModel;
        }

        $row = $model->where('name', '=', $this->app)->find();
        if ($row->status != 0) {
            return $model::uninstall($this->app);
        }
        
        return true;
    }

    /**
     * 删除应用
     *
     * @return bool
     */
    protected function deleteApp()
    {
        if ($this->appType == 'plugins') {
            $model = new PluginsModel;
        } else {
            $model = new ModuleModel;
        }

        $row = $model->where('name', '=', $this->app)->find();
        if ($row->status != 0) {
            $result = $model::uninstall($this->app);
            if ($result !== true) {
                return $result;
            }
        }

        return $model::del($this->app);
    }

    /**
     * 安装应用
     *
     * @return void
     */
    protected function installApp()
    {
        if ($this->appType == 'plugins') {
            $model = new PluginsModel;
        } else {
            $model = new ModuleModel;
        }

        return $model::install($this->app, 1);
    }

    /**
     * 生成应用
     *
     * @param array $dir 生成的目录
     * @return void
     */
    protected function buildModule($dir = [])
    {
        $path = $this->getRootPath();
        if (is_dir($path) && !$this->input->getOption('force')) {
            return 'module already exists';
        }

        $defDir = ['admin', 'home', 'model', 'service', 'logic', 'lang', 'sql', 'validate', 'view'];
        $dir    = array_merge($defDir, $dir);
        $dir    = array_unique($dir);

        // 生成模块基础结构
        $build                      = [];
        $build[$this->app]['__file__']    = ['common.php'];
        $build[$this->app]['__dir__']     = $dir;

        AppBuild::run($build);

        // 删除默认控制器目录和文件
        @unlink($path.'controller'.DS.'Index.php');
        @rmdir($path.'controller');
        
        $this->buildInfo();
        $this->buildMenu();


        $info = include_once $path.'info.php';

        $sql                = [];
        $sql['name']        = $info['name'];
        $sql['identifier']  = $info['identifier'];
        $sql['theme']       = $info['theme'];
        $sql['title']       = $info['title'];
        $sql['intro']       = $info['intro'];
        $sql['author']      = $info['author'];
        $sql['icon']        = $info['icon'];
        $sql['version']     = $info['version'];
        $sql['url']         = $info['author_url'];
        $sql['config']      = '';
        $sql['status']      = 0;
        $sql['default']     = 0;
        $sql['system']      = 0;
        $sql['app_keys']    = '';

        $result = ModuleModel::create($sql);
        if ($result) {
            $res = ModuleModel::install($result->id, 1);

            if ($res !== true) {
                $this->output->info($res);
            }
        }

        return true;
    }

    /**
     * 生成插件
     *
     * @param array $dir 生成的目录
     * @return void
     */
    protected function buildPlugins($dir = [])
    {
        $path = $this->getRootPath();
        if (is_dir($path) && !$this->input->getOption('force')) {
            return 'plugins already exists';
        }
        
        $defDir = ['admin', 'home', 'model', 'service', 'logic', 'lang', 'sql', 'validate', 'view'];
        $dir    = array_merge($defDir, $dir);
        $dir    = array_unique($dir);

        $dir[] = 'view'.DS.'admin';
        $dir[] = 'view'.DS.'home';
        $dir[] = 'view'.DS.'widget';

        foreach ($dir as $d) {
            if (!is_dir($path . $d)) {
                // 创建目录
                mkdir($path . $d, 0755, true);
            }
        }
        
        $this->buildInfo();
        $this->buildMenu();
        $this->buildHook();

        $info = include_once $path.'info.php';
        
        $sql                = [];
        $sql['name']        = $info['name'];
        $sql['identifier']  = $info['identifier'];
        $sql['title']       = $info['title'];
        $sql['intro']       = $info['intro'];
        $sql['author']      = $info['author'];
        $sql['icon']        = $info['icon'];
        $sql['version']     = $info['version'];
        $sql['url']         = $info['author_url'];
        $sql['config']      = '';
        $sql['status']      = 0;
        $sql['system']      = 0;
        $sql['app_keys']    = '';
        
        $result = PluginsModel::create($sql);
        if ($result) {
            $res = PluginsModel::install($result->id);
            if ($res !== true) {
                $this->output->info($res);
            }
        }

        return true;
    }
    
    /**
     * 生成基础信息文件（info.php）
     *
     * @return true
     */
    protected function buildInfo()
    {
        $options = $this->input->getOptions();
        $options['app'] = $this->app;
        
        if (!$options['identifier']) {
            $options['identifier'] = $this->app.'.'.$this->appType;
        }

        $keys = array_keys($options);
        $stub = $this->getStub('info');
        $content = str_replace(array_map(function($v) {
                        return '{%'.$v.'%}';
                    }, $keys), array_values($options), $stub);
    
        file_put_contents($this->getRootPath() . 'info.php', $content);

        return true;
    }

    /**
     * 生成菜单文件（menu.php）
     *
     * @return true
     */
    protected function buildMenu()
    {
        $options = $this->input->getOptions();
        $options['app'] = $this->app;
        
        $keys = array_keys($options);
        $stub = $this->getStub('menu');
        $content = str_replace(array_map(function($v) {
                        return '{%'.$v.'%}';
                    }, $keys), array_values($options), $stub);
    
        file_put_contents($this->getRootPath() . 'menu.php', $content);

        return true;
    }

    /**
     * 生成插件钩子文件
     *
     * @return true
     */
    protected function buildHook()
    {
        $options = $this->input->getOptions();
        $options['app'] = $this->app;
        
        $keys = array_keys($options);
        $stub = $this->getStub('hook');
        $content = str_replace(array_map(function($v) {
                        return '{%'.$v.'%}';
                    }, $keys), array_values($options), $stub);
    
        file_put_contents($this->getRootPath() . $this->app . '.php', $content);

        return true;
    }

    /**
     * 获取模块或插件的根目录
     *
     * @return string
     */
    protected function getRootPath()
    {
        if ($this->appType == 'plugins') {
            return Env::get('root_path') . 'plugins' . DS . $this->app . DS;
        } else {
            return Env::get('app_path') . $this->app . DS;
        }
    }

    /**
     * 获取基础模板
     *
     * @param string $name 模板名
     * @return string
     */
    protected function getStub($name)
    {
        return file_get_contents(__DIR__ . DS . 'Build' . DS . 'stubs' . DS . $this->appType . DS . $name . '.stub');
    }
}
