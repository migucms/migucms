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

namespace app\common\controller;
use View;
use think\facade\Env;
use think\Controller;
use think\Db;

/**
 * 通用控制器基类
 * @package app\common\Base
 */
class Base extends Controller
{

    /**
     * 解析和获取模板内容 用于输出
     * @param string    $template 模板文件名或者内容
     * @param array     $vars     模板输出变量
     * @param array     $replace 替换内容
     * @param array     $config     模板参数
     * @param bool      $renderContent     是否渲染内容
     * @return string
     * @throws Exception
     * @author Author: btc
     */
    final protected function fetch($template = '', $vars = [], $replace = [], $config = [], $renderContent = false)
    {

        if (defined('IS_PLUGINS')) {
            return self::pluginsFetch($template, $vars, $replace, $config, $renderContent);
        }

        if (defined('ENTRANCE') && ENTRANCE == 'admin') {
            if (isset($this->view->buildForm)) {
                isset($this->view->buildForm['filterField']) && self::buildFormFilterField();
                $template = $template ?: 'form';
                $template = Env::get('app_path').'/system/view/builder/'.$template.'.html';
            } else if (isset($this->view->buildTable)) {
                $template = $template ?: 'table';
                $template = Env::get('app_path').'/system/view/builder/'.$template.'.html';
            } else if (isset($this->view->buildTreeTable)) {
                $template = $template ?: 'tree_table';
                $template = Env::get('app_path').'/system/view/builder/'.$template.'.html';
            }
        }
        
        return parent::fetch($template, $vars, $replace, $config, $renderContent);
    }
    
    /**
     * 渲染插件模板
     * @param string    $template 模板文件名或者内容
     * @param array     $vars     模板输出变量
     * @param array     $replace 替换内容
     * @param array     $config     模板参数
     * @param bool      $renderContent     是否渲染内容
     * @return string
     * @throws Exception
     * @author Author: btc
     */
    final protected function pluginsFetch($template = '', $vars = [], $replace = [], $config = [], $renderContent = false)
    {
        $bool = true;
        if (defined('ENTRANCE') && ENTRANCE == 'admin') {
            if (isset($this->view->buildForm)) {
                isset($this->view->buildForm['filterField']) && self::buildFormFilterField();
                $template = $template ?: 'form';
                $template = Env::get('app_path').'/system/view/builder/'.$template.'.html';
                $bool = false;
            } else if (isset($this->view->buildTable)) {
                $template = $template ?: 'table';
                $template = Env::get('app_path').'/system/view/builder/'.$template.'.html';
                $bool = false;
            } else if (isset($this->view->buildTreeTable)) {
                $template = $template ?: 'tree_table';
                $template = Env::get('app_path').'/system/view/builder/'.$template.'.html';
                $bool = false;
            }
        }

        if ($bool === true && stripos($template, config('template.view_suffix')) === false) {
            $plugin     = $_GET['_p'];
            $controller = $_GET['_c'];
            $action     = $_GET['_a'];
            if (!$template) {
                $template = $controller.'/'.parse_name($action);
            } elseif (strpos($template, '/') == false) {
                $template = $controller.'/'.$template;
            }
            
            if(defined('ENTRANCE') && ENTRANCE == 'admin') {
                $template = 'admin/'.$template;
            } else {
                $template = 'home/'.$template;
            }
            
            $template = Env::get('root_path').strtolower("plugins/{$plugin}/view/{$template}.".config('template.view_suffix'));
        }

        return parent::fetch($template, $vars, $replace, $config, $renderContent);
    }
    
    /**
     * 根据构建器的表单字段过滤不需要的字段
     * @author Author: btc
     */
    private function buildFormFilterField()
    {
        if (isset($this->view->formData) && $this->view->formData) {
            $fields = [];
            if (isset($this->view->buildForm['group'])) {
                foreach($this->view->buildForm['group'] as $v) {
                    foreach ($v['items'] as $vv) {
                        if (isset($vv['name'])) array_push($fields, $vv['name']);
                    }
                }
            } else {
                foreach ($this->view->buildForm['items'] as $v) {
                    if (isset($v['name'])) array_push($fields, $v['name']);
                }
            }

            $formData = $this->view->formData;
            if (!is_array($formData)) {
                $formData = $formData->toArray();
            }
            
            foreach($formData as $k => $v) {
                if (array_search($k, $fields) === false) {
                    unset($formData[$k]);
                }
            }

            $this->assign('formData', $formData);
        }
    }
    
    /**
     * 自动获取模型层、逻辑层、验证器层、服务层、数据库实例（支持跨模块）
     */
    public function __get($name)
    {
        $class = $name;
        $layer  = get_layer($name);
        if ($layer) {
            $name = ltrim($name, $layer); 
            if ($layer == 'db') {
               return $this->$class = Db::name($name);
            }
            $use = 'app\\'.$this->request->module().'\\'.$layer.'\\'.$name;
        } else if (substr($name, 0, 7) == 'plugins') {
            $name   = ltrim($name, 'plugins');
            $layer  = get_layer(strtolower($name));
            $use    = 'plugins\\'.$this->request->param('_p').'\\'.$layer.'\\'.ltrim($name, ucfirst($layer));
        } else {
            $parseName  = parse_name($name);
            $exp        = explode('_', $parseName);
            $name       = lcfirst(ltrim($name, $exp[0]));
            $layer      = get_layer($name);
            if ($layer) {
                $name   = ltrim($name, $layer); 
                $use    = 'app\\'.$exp[0].'\\'.$layer.'\\'.$name;
            } else {
                return;
            }
        }
        
        return $this->$class = (new $use);
    }
}
