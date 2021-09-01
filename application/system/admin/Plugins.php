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

use app\system\model\SystemPlugins as PluginsModel;
use app\system\model\SystemHookPlugins as HookPluginsModel;
use app\system\model\SystemMenu as MenuModel;
use app\system\model\SystemHook as HookModel;
use think\Db;
use migu\Dir;
use migu\PclZip;
use think\facade\Log;
use think\facade\Env;

/**
 * 插件管理控制器
 * @package app\system\admin
 */
class Plugins extends Admin
{
    public $tabData = [];
    protected $miguModel = 'SystemPlugins';

    /**
     * 初始化方法
     */
    protected function initialize()
    {
        parent::initialize();

        $tabData['menu'] = [
            [
                'title' => '已启用',
                'url' => 'system/plugins/index?status=2',
            ],
            [
                'title' => '已停用',
                'url' => 'system/plugins/index?status=1',
            ],
            [
                'title' => '待安装',
                'url' => 'system/plugins/index?status=0',
            ],
            [
                'title' => '导入插件',
                'url' => 'system/plugins/import',
            ],
        ];

        // if (config('sys.app_debug') == 1) {
        //     array_push($tabData['menu'], ['title' => '生成插件', 'url' => 'system/plugins/design',]);
        // }
        
        $this->tabData = $tabData;
    }

    /**
     * 插件管理首页
     * @author Author: btc
     * @return mixed
     */
    public function index()
    {
        $status = $this->request->param('status/d', 2);
        $tabData = $this->tabData;
        $tabData['current'] = url('?status='.$status);
        $map            = [];
        $map['status']  = $status;
        $map['system']  = 0;
        $plugins = PluginsModel::where($map)->order('sort,id')->column('id,title,author,intro,icon,system,app_keys,identifier,name,version,config,status');
        if ($status == 0) {
            $pluginsPath = Env::get('root_path').'plugins/';
            // 自动将本地未入库的插件导入数据库
            $allPlugins = PluginsModel::order('sort,id')->column('id,name', 'name');
            $files = Dir::getList($pluginsPath);
            foreach ($files as $k => $f) {
                // 排除已存在数据库的插件
                if (array_key_exists($f, $allPlugins) || !is_dir($pluginsPath.$f)) {
                    continue;
                }
                if (file_exists($pluginsPath.$f.'/info.php')) {
                    $info = include_once $pluginsPath.$f.'/info.php';
                    $sql                = [];
                    $sql['name']        = $info['name'];
                    $sql['identifier']  = $info['identifier'];
                    $sql['title']       = $info['title'];
                    $sql['intro']       = $info['intro'];
                    $sql['author']      = $info['author'];
                    $sql['icon']        = ROOT_DIR.substr($info['icon'], 1);
                    $sql['version']     = $info['version'];
                    $sql['url']         = $info['author_url'];
                    $sql['config']      = '';
                    $sql['status']      = 0;
                    $sql['system']      = 0;
                    $sql['app_keys']    = '';
                    $db = PluginsModel::create($sql);
                    $sql['id'] = $db->id;
                    $plugins = array_merge($plugins, [$sql]);
                }
            }
        }

        $this->assign('plugins', array_values($plugins));
        $this->assign('miguTabData', $tabData);
        $this->assign('miguTabType', 3);
        return $this->fetch();
    }

    /**
     * 插件设计
     * @author Author: btc
     * @return mixed
     */
    public function design()
    {
        if (config('sys.app_debug') == 0) {
            return $this->error('非开发模式禁止使用此功能');
        }
        if ($this->request->isPost()) {
            $model = new PluginsModel();
            if (!$model->design($this->request->post())) {
                return $this->error($model->getError());
            }
            return $this->success('插件已生成完毕', url('index?status=0'));
        }
        $tabData = [];
        $tabData['menu'] = [
            ['title' => '插件设计'],
            ['title' => '插件配置'],
            // ['title' => '插件菜单'],
        ];

        $this->assign('miguTabData', $tabData);
        $this->assign('miguTabType', 2);
        return $this->fetch();
    }

    /**
     * 插件配置
     * @author Author: btc
     * @return mixed
     */
    public function setting($id = 0)
    {
        $where = [];
        if (is_numeric($id)) {
            $where[] = ['id', '=', $id];
        } else {
            $where[] = ['name', '=', $id];
        }

        $row = PluginsModel::where($where)->field('id,name,config,title')->find()->toArray();
        $pluginsInfo = plugins_info($row['name']);
        if (!$row['config'] && !$pluginsInfo['config']) {
            return $this->error('此插件无需配置');
        }

        if (!$row['config'] && $pluginsInfo['config']) {
            $config = $pluginsInfo['config'];
        } else {
            $config = json_decode($row['config'], 1);
        }
        
        foreach ($config as &$v) {
            if (isset($v['options']) && $v['options']) {
                $v['options'] = array_filter(parse_attr($v['options']));
            }
            if ($v['type'] == 'checkbox' && isset($v['value']) && $v['value']) {
                if (!is_array($v['value'])) {
                    $v['value'] = explode(',', $v['value']);
                }
            }
        }
        $row['config'] = $config;

        if ($this->request->isPost()) {
            $postData = $this->request->post();
            foreach ($row['config'] as &$conf) {
                $conf['value'] = isset($postData[$conf['name']]) ? $postData[$conf['name']] : '';
            }
            if (PluginsModel::where('id', $id)->setField('config', json_encode($row['config'], 1)) === false) {
                return $this->error('配置保存失败');
            }
            PluginsModel::getConfig('', true);
            return $this->success('配置保存成功');
        }
        $this->assign('formData', $row);
        return $this->fetch();
    }

    /**
     * 安装插件
     * @author Author: btc
     */
    public function install()
    {
        $id = get_num();
        $result = PluginsModel::install($id);
        if ($result !== true) {
            return $this->error($result);
        }
        return $this->success('插件已安装成功', url('index?status=2'));
    }

    /**
     * 执行模块安装(为了兼容开发助手)
     * 
     * @param mixed $id
     * @param integer $clear
     * @return bool|string 
     */
    public function execInstall($id)
    {
        return PluginsModel::install($id);
    }
    
    /**
     * 卸载插件
     * @author Author: btc
     */
    public function uninstall($id = 0)
    {
        $result = PluginsModel::uninstall($id);
        if ($result !== true) {
            return $this->error($result);
        }
        return $this->success('插件已卸载成功', url('index?status=0'));
    }

    /**
     * 导入插件
     * @author Author: btc
     * @return mixed
     */
    public function import()
    {
        if ($this->request->isPost()) {
            $_file = $this->request->param('file');
            if (empty($_file)) {
                return $this->error('请上传模块安装包');
            }

            $file = realpath('.'.$_file);
            if (ROOT_DIR != '/') {// 针对子目录处理
                $file = realpath('.'.str_replace(ROOT_DIR, '/', $_file));
            }
            
            if (!file_exists($file)) {
                return $this->error('上传文件无效');
            }
            
            $decomPath = '.'.trim(str_replace(ROOT_DIR, '/', $_file), '.zip');
            if (!is_dir($decomPath)) {
                Dir::create($decomPath, 0777);
            }
            // 解压安装包到$decomPath
            $archive = new PclZip();
            $archive->PclZip($file);
            if(!$archive->extract(PCLZIP_OPT_PATH, $decomPath, PCLZIP_OPT_REPLACE_NEWER)) {
                Dir::delDir($decomPath);
                @unlink($file);
                return $this->error('导入失败('.$archive->error_string.')');
            }

            // 获取插件名
            $files = Dir::getList($decomPath.'/upload/plugins/');

            if (!isset($files[0])) {
                Dir::delDir($decomPath);
                @unlink($file);
                return $this->error('导入失败，安装包不完整');
            }

            $appName = $files[0];

            // 防止重复导入插件
            if (is_dir(Env::get('root_path').'plugins/'.$appName)) {
                Dir::delDir($decomPath);
                @unlink($file);
                return $this->error('插件已存在');
            } else {
                Dir::create(Env::get('root_path').'plugins/'.$appName, 0777);
            }

            // 复制插件
            Dir::copyDir($decomPath.'/upload/plugins/'.$appName.'/', Env::get('root_path').'plugins/'.$appName);
            
            // 文件安全检查
            $safeCheck = Dir::safeCheck(Env::get('root_path').'plugins/'.$appName);
            if ($safeCheck) {
                foreach($safeCheck as $v) {
                    Log::warning('文件 '. $v['file'].' 含有危险函数：'.str_replace('(', '', implode(',', $v['function'])));
                }
            }

            // 复制静态资源
            Dir::copyDir($decomPath.'/upload/public/static/'.$appName, './static/plugins/'.$appName);

            // 删除临时目录和安装包
            Dir::delDir($decomPath);
            @unlink($file);

            $this->success($safeCheck ? '插件导入成功，部分文件可能存在安全风险，请查看系统日志' : '插件导入成功', url('index?status=0'));
        }

        $tabData = $this->tabData;
        $tabData['current'] = 'system/plugins/import';
        $this->assign('miguTabData', $tabData);
        $this->assign('miguTabType', 3);
        return $this->fetch();
    }

    /**
     * 删除插件
     * @author Author: btc
     */
    public function del()
    {
        $id = get_num();
        $result = PluginsModel::del($id);
        if ($result !== true) {
            return $this->error($result);
        }

        return $this->success('插件删除成功');
    }

    /**
     * 执行内部插件
     * @author Author: btc
     * @return mixed
     */
    public function run() {
        $plugin     = $_GET['_p'] = $this->request->param('_p');
        $controller = $_GET['_c'] = ucfirst($this->request->param('_c', 'Index'));
        $action     = $_GET['_a'] = $this->request->param('_a', 'index');
        $params     = $this->request->except(['_p', '_c', '_a'], 'param');

        if (empty($plugin)) {
            return $this->error('插件名必须传入[_p]');
        }
            
        if (!PluginsModel::where(['name' => $plugin, 'status' => 2])->find() ) {
            return $this->error("插件可能不存在或者未安装");
        }

        if (!plugins_action_exist($plugin.'/'.$controller.'/'.$action)) {
            return $this->error("找不到插件方法：{$plugin}/{$controller}/{$action}");
        }
        return plugins_run($plugin.'/'.$controller.'/'.$action, $params);
    }
}
