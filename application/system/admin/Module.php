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

use app\system\model\SystemModule as ModuleModel;
use app\system\model\SystemConfig as ConfigModel;
use app\system\model\SystemMenu as MenuModel;
use app\system\model\SystemHook as HookModel;
use migu\Dir;
use migu\PclZip;
use think\Db;
use think\Xml;
use think\facade\Log;
use think\facade\Env;

/**
 * 模块管理控制器
 * @package app\system\admin
 */
class Module extends Admin
{
    public $tabData = [];
    protected $miguModel = 'SystemModule';

    /**
     * 初始化方法
     */
    protected function initialize()
    {
        parent::initialize();

        $tabData['menu'] = [
            [
                'title' => '已启用',
                'url' => 'system/module/index?status=2',
            ],
            [
                'title' => '已停用',
                'url' => 'system/module/index?status=1',
            ],
            [
                'title' => '待安装',
                'url' => 'system/module/index?status=0',
            ],
            [
                'title' => '导入模块',
                'url' => 'system/module/import',
            ],
        ];
        
        $this->tabData = $tabData;
        $this->appPath = Env::get('app_path');
    }

    /**
     * 模块管理首页
     * @author Author: btc
     * @return mixed
     */
    public function index()
    {
        $status             = $this->request->param('status/d', 2);
        $tabData            = $this->tabData;
        $tabData['current'] = url('?status='.$status);
        $map                = [];
        $map['status']      = $status;
        $map['system']      = 0;
        $modules = ModuleModel::where($map)
                    ->order('sort,id')
                    ->column('id,title,author,intro,icon,is_default,system,app_keys,identifier,config,name,version,status');

        if ($status == 0) {

            // 自动将本地未入库的模块导入数据库
            $allModule  = ModuleModel::order('sort,id')->column('id,name', 'name');
            $files      = Dir::getList($this->appPath);
            $sysDir     = config('mg_system.modules');
            array_push($sysDir, 'extra');

            foreach ($files as $k => $f) {

                // 排除系统模块和已存在数据库的模块
                if (array_search($f, $sysDir) !== false ||
                    array_key_exists($f, $allModule) ||
                    !is_dir($this->appPath.$f)) {
                    continue;
                }

                if (file_exists($this->appPath.$f.'/info.php')) {
                    $info = include_once $this->appPath.$f.'/info.php';
                    $sql                = [];
                    $sql['name']        = $info['name'];
                    $sql['identifier']  = $info['identifier'];
                    $sql['theme']       = $info['theme'];
                    $sql['title']       = $info['title'];
                    $sql['intro']       = $info['intro'];
                    $sql['author']      = $info['author'];
                    $sql['icon']        = ROOT_DIR.substr($info['icon'], 1);
                    $sql['version']     = $info['version'];
                    $sql['url']         = $info['author_url'];
                    $sql['config']      = '';
                    $sql['status']      = 0;
                    $sql['is_default']     = 0;
                    $sql['system']      = 0;
                    $sql['app_keys']    = '';
                    $db = ModuleModel::create($sql);
                    $sql['id'] = $db->id;
                    $modules = array_merge($modules, [$sql]);
                }
            }
        }
        
        $this->assign('modules', array_values($modules));
        $this->assign('miguTabData', $tabData);
        $this->assign('miguTabType', 3);
        
        return $this->fetch();
    }

    /**
     * 模块设计
     * @author Author: btc
     * @return mixed
     */
    public function design()
    {
        if (config('sys.app_debug') == 0) {
            return $this->error('非开发模式禁止使用此功能');
        }

        if ($this->request->isPost()) {
            $model = new ModuleModel();

            if (!$model->design($this->request->post())) {
                return $this->error($model->getError());
            }

            return $this->success('模块已自动生成完毕', url('index?status=0'));
        }

        return $this->fetch();
    }

    /**
     * 安装模块
     * @author Author: btc
     * @return mixed
     */
    public function install($id = 0)
    {
        if ($this->request->isPost()) {
            $postData = $this->request->post();
            $result = ModuleModel::install($id, $postData['clear']);
            if ($result !== true) {
                return $this->error($result);
            }
            return $this->success('模块已安装成功', url('index?status=2'));
        }

        $mod = ModuleModel::where('id', $id)->find();
        if (!$mod) {
            return $this->error('模块不存在');
        }

        if ($mod['status'] > 0) {
            return $this->error('请勿重复安装此模块');
        }

        $modPath = $this->appPath.$mod['name'].'/';
        // 模块自定义配置
        if (!file_exists($modPath.'info.php')) {
            return $this->error('模块配置文件不存在[info.php]');
        }

        $info = include_once $modPath.'info.php';

        // 模块依赖检查
        foreach ($info['module_depend'] as $k => $v) {
            if (!isset($v[3])) {
                $v[3] = '=';
            }
            $v[4] = '✔︎';
            $v[5] = '';
            // 判断模块是否存在
            if (!is_dir($this->appPath.$v[0])) {
                $v[4] = '<span class="red">✘ 模块不存在</span>';
                $info['module_depend'][$k] = $v;
                continue;
            }
            if (!file_exists($this->appPath.$v[0].'/info.php')) {
                $v[4] = '<span class="red">✘ 模块配置文件不存在</span>';
                $info['module_depend'][$k] = $v;
                continue;
            }
            $dinfo = include $this->appPath.$v[0].'/info.php';
            $v[5] = $dinfo['version'];
            // 判断依赖的模块标识是否一致
            if ($dinfo['identifier'] != $v[1]) {
                $v[4] = '<span class="red">✘ 模块标识不匹配</span>';
                $info['module_depend'][$k] = $v;
                continue;
            }
            // 版本对比
            if (version_compare($dinfo['version'], $v[2], $v[3]) === false) {
                $v[4] = '<span class="red">✘ 需要的版本必须'.$v[3].$v[2].'</span>';
                $info['module_depend'][$k] = $v;
                continue;
            }
            $info['module_depend'][$k] = $v;
        }

        // 插件依赖检查 TODO
        $info['id'] = $mod['id'];
        $info['demo_data'] = file_exists($modPath.'sql/demo.sql') ? true : false;

        $this->assign('tables', $this->checkTable($info['tables']));
        $this->assign('formData', $info);

        return $this->fetch();
    }

    /**
     * 模块图标上传
     * @author Author: btc
     * @return mixed
     */
    public function icon()
    {
        $_id = get_num();

        $module = ModuleModel::where('id', $_id)->find();
        if (!$module) {
            return $this->error('参数传递错误');
        }

        $file = request()->file('file');
        if (!$file->checkExt('png')) {
            return $this->error('只允许上传PNG图标');
        }
        
        if (!$file->checkSize(102400)) {
            return $this->error('图标大小超过系统限制(100KB)');
        }

        $imagePath = './upload/temp/';
        $file->rule('')->move($imagePath, $module['name'] . '.png');
        $image = getimagesize($imagePath.$module['name'] . '.png');
        if ($image[0] !== 200 || $image[1] !== 200) {
            unlink($imagePath.$module['name'] . '.png');
            return $this->error('图标尺寸不符合要求(200px * 200px)');
        }

        // 将图标移动到模块目录下面
        copy($imagePath.$module['name'] . '.png', $this->appPath.$module['name'].'.png');
        copy($imagePath.$module['name'] . '.png', './static/'.$module['name'].'/'.$module['name'].'.png');
        return $this->success('/static/'.$module['name'].'/'.$module['name'].'.png?v='.time());
    }

    /**
     * 导入模块
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
            
            $decomPath = '.'.trim($_file, '.zip');

            if (!is_dir($decomPath)) {
                Dir::create($decomPath, 0777);
            }

            // 解压安装包到$decomPath
            $archive = new PclZip();
            $archive->PclZip($file);
            if (!$archive->extract(PCLZIP_OPT_PATH, $decomPath, PCLZIP_OPT_REPLACE_NEWER)) {
                Dir::delDir($decomPath);
                @unlink($file);
                return $this->error('导入失败('.$archive->error_string.')');
            }

            if (!is_dir($decomPath.'/upload/application')) {
                Dir::delDir($decomPath);
                @unlink($file);
                return $this->error('导入失败，安装包不完整(-1)');
            }

            // 获取模块名
            $files = Dir::getList($decomPath.'/upload/application/');
            if (!isset($files[0])) {
                Dir::delDir($decomPath);
                @unlink($file);
                return $this->error('导入失败，安装包不完整(-2)');
            }

            $appName = $files[0];

            // 防止重复导入模块
            if (is_dir($this->appPath.$appName)) {
                Dir::delDir($decomPath);
                @unlink($file);
                return $this->error('模块已存在');
            }

            // 应用目录
            $appPath = $decomPath.'/upload/application/'.$appName.'/';

            // 获取安装包基本信息
            if (!file_exists($appPath.'info.php')) {
                Dir::delDir($decomPath);
                @unlink($file);
                return $this->error('安装包缺少[info.php]文件');
            }

            $info = include_once $appPath.'info.php';

            // 安装模块路由
            if (file_exists($appPath.$appName.'.php')) {
                Dir::copyDir($appPath.$appName.'.php', './route');
            }

            // 复制app目录
            if (!is_dir(Env::get('root_path').'application/'.$appName)) {
                Dir::create(Env::get('root_path').'application/'.$appName, 0777);
            }

            Dir::copyDir($appPath, Env::get('app_path').$appName);

            // 文件安全检查
            $safeTips = false;
            $safeCheck = Dir::safeCheck(Env::get('app_path').$appName);
            if ($safeCheck) {
                $safeTips = true;
                foreach ($safeCheck as $v) {
                    Log::warning('文件 '. $v['file'].' 含有危险函数：'.str_replace('(', '', implode(',', $v['function'])));
                }
            }

            if (!is_dir('./static/'.$appName.'/')) {
                Dir::create('./static/'.$appName.'/', 0755);
            }

            // 复制static目录
            if (is_dir($decomPath.'/upload/public/static')) {
                Dir::copyDir($decomPath.'/upload/public/static', './static');
            }

            // 复制theme目录
            if (is_dir($decomPath.'/upload/public/theme')) {
                Dir::copyDir($decomPath.'/upload/public/theme', './theme');

                // 文件安全检查
                $safeCheck = Dir::safeCheck('./theme/'.$appName);
                if ($safeCheck) {
                    $safeTips = true;
                    foreach ($safeCheck as $v) {
                        Log::warning('文件 '. $v['file'].' 含有危险函数：'.str_replace('(', '', implode(',', $v['function'])));
                    }
                }
            }

            // 删除临时目录和安装包
            Dir::delDir($decomPath);
            @unlink($file);
            $this->success($safeTips ? '模块导入成功，部分文件可能存在安全风险，请查看系统日志' : '模块导入成功', url('index?status=0'));
        }

        $tabData = $this->tabData;
        $tabData['current'] = 'system/module/import';
        $this->assign('miguTabData', $tabData);
        $this->assign('miguTabType', 3);
        
        return $this->fetch();
    }

    /**
     * 卸载模块
     * @author Author: btc
     * @return mixed
     */
    public function uninstall($id = 0)
    {
        $mod = ModuleModel::where('id', $id)->find();
        if (!$mod) {
            return $this->error('模块不存在');
        }
        if ($mod['status'] == 0) {
            return $this->error('模块未安装');
        }
        
        if ($this->request->isPost()) {
            $result = ModuleModel::uninstall($id);
            if (!$result) {
                $this->error($result);
            }

            $this->success('模块已卸载成功', url('index?status=0'));
        }

        $this->assign('formData', $mod);
        return $this->fetch();
    }

    /**
     * 删除模块
     * @author Author: btc
     * @return mixed
     */
    public function del()
    {
        $id = get_num();
        $result = Modulemodel::del($id);
        if ($result !== true) {
            $this->error($result);
        }

        $this->success('模块删除成功');
    }

    /**
     * 执行主题SQL安装
     * @author Author: btc
     * @return mixed
     */
    public function exeSql()
    {
        $app    = $this->request->param('app_name');
        $theme  = $this->request->param('theme');
        $path   = './theme/'.$app.'/'.$theme.'/';
        if (!is_file($path.'install.sql')) {
            return $this->error('SQL文件不存在');
        }

        if (is_file($path.'config.json')) {
            $json = file_get_contents($path.'config.json');
            $config = json_decode($json, 1);
        } elseif (is_file($path.'config.xml')) {
            $xml = file_get_contents($path.'config.xml');
            $config = xml2array($xml);
        } else {
            return $this->error('缺少配置文件');
        }

        if (!isset($config['db_prefix'])) {
            return $this->error('配置文件缺少db_prefix配置');
        }
        
        $sql        = file_get_contents($path.'install.sql');
        $sqlList    = parse_sql($sql, 0, [$config['db_prefix'] => config('database.prefix')]);
        if ($sqlList) {
            $sqlList = array_filter($sqlList);
            foreach ($sqlList as $v) {
                // 防止删除整个数据库
                if (stripos(strtoupper($v), 'DROP DATABASE') !== false) {
                    return $this->error('install.sql文件疑似含有删除数据库的SQL');
                }

                // 过滤sql里面的系统表
                foreach (config('mg_system.tables') as $t) {
                    if (stripos($v, '`'.config('database.prefix').$t.'`') !== false) {
                        return $this->error('install.sql文件含有系统表['.$t.']');
                    }
                }
                
                try {
                    Db::execute($v);
                } catch (\Exception $e) {
                    return $this->error($e->getMessage());
                }
            }
        }

        return $this->success('导入成功');
    }

    /**
     * 设置默认模块
     * @author Author: btc
     * @return mixed
     */
    public function setDefault()
    {
        $id     = $this->request->param('id/d');
        $val    = $this->request->param('val/d');
        if ($val == 1) {
            $res = ModuleModel::where('id', $id)->find();
            if ($res['system'] == 1) {
                return $this->error('禁止设置系统模块');
            }
            if ($res['status'] != 2) {
                return $this->error('禁止设置未启用或未安装的模块');
            }

            ModuleModel::where('id > 0')->setField('is_default', 0);
            ModuleModel::where('id', $id)->setField('is_default', 1);
        } else {
            ModuleModel::where('id', $id)->setField('is_default', 0);
        }
        return $this->success('操作成功');
    }

    /**
     * 主题管理
     * @author Author: btc
     * @return mixed
     */
    public function theme($id = 0)
    {
        $where = [];
        $where[] = ['status', '=', 2];

        if (is_numeric($id)) {
            $where[] = ['id', '=', $id];
        } else {
            $where[] = ['name', '=', $id];
        }

        $module = ModuleModel::where($where)->find();
        if (!$module) {
            return $this->error('模块不存在或未安装');
        }
        $path = './theme/'.$module['name'].'/';
        if (!is_dir($path)) {
            return $this->error('模块主题不存在');
        }
        $theme = Dir::getList($path);
        $themes = [];
        
        foreach ($theme as $k => $v) {
            if (is_file($path.$v.'/config.xml')) {
                $xml = file_get_contents($path.$v.'/config.xml');
                $themes[$k] = xml2array($xml);
            } elseif (is_file($path.$v.'/config.json')) {
                $json = file_get_contents($path.$v.'/config.json');
                $themes[$k] = json_decode($json, 1);
            } else {
                continue;
            }

            $themes[$k]['sql'] = 0;
            if (is_file($path.$v.'/install.sql')) {
                $themes[$k]['sql'] = 1;
            }

            $themes[$k]['name'] = $v;
            $themes[$k]['thumb'] = ROOT_DIR.'theme/'.$module['name'].'/'.$v.'/thumb.png';
            if (!is_file($themes[$k]['thumb'])) {
                $themes[$k]['thumb'] = ROOT_DIR.'static/system/image/theme.png';
            }
        }

        $this->assign('formData', $module);
        $this->assign('data_list', $themes);
        return $this->fetch();
    }

    /**
     * 设置默认主题
     * @author Author: btc
     * @return mixed
     */
    public function setDefaultTheme($id = 0, $theme = '')
    {
        if (empty($theme)) {
            return $this->error('参数传递错误');
        }

        $module = ModuleModel::where(['id' => $id, 'status' => 2])->find();
        if (!$module) {
            return $this->error('模块不存在或未安装');
        }

        $res = ModuleModel::where('id', $id)->setField('theme', $theme);
        if (!$res) {
            return $this->error('设置默认主题失败');
        }
        return $this->success('设置成功');
    }

    /**
     * 删除主题
     * @author Author: btc
     * @return mixed
     */
    public function delTheme($id = 0, $theme = '')
    {
        if (empty($theme)) {
            return $this->error('参数传递错误');
        }

        $module = ModuleModel::where(['id' => $id, 'status' => 2])->find();
        if (!$module) {
            return $this->error('模块不存在或未安装');
        }
        $path = './theme/'.$module['name'].'/';
        Dir::delDir($path.$theme);
        return $this->success('删除成功');
    }

    /**
     * 执行模块安装(为了兼容开发助手)
     *
     * @param mixed $id
     * @param integer $clear
     * @return bool|string
     */
    public function execInstall($id, $clear = 1)
    {
        return ModuleModel::install($id, $clear);
    }

    /**
     * 添加模型菜单
     * @param array $data 菜单数据
     * @param string $mod 模型名称
     * @param int $pid 父ID
     * @author Author: btc
     * @return bool
     */
    private function addMenu($data = [], $mod = '', $pid = 0)
    {
        if (empty($data)) {
            return false;
        }
        foreach ($data as $v) {
            $v['pid'] = $pid;
            $childs = $v['childs'];
            unset($v['childs']);
            $res = model('SystemMenu')->storage($v);
            if (!$res) {
                return false;
            }
            if (!empty($childs)) {
                $this->addMenu($childs, $mod, $res['id']);
            }
        }
        return true;
    }

    /**
     * 检查表是否存在
     * @param array $list 目录列表
     * @author Author: btc
     * @return array
     */
    private function checkTable($tables = [])
    {
        $res = [];
        foreach ($tables as $k => $v) {
            $res[$k]['table'] = config('database.prefix').$v;
            $res[$k]['exist'] = '<span style="color:green">✔︎</span>';
            if (Db::query("SHOW TABLES LIKE '".config('database.prefix').$v."'")) {
                $res[$k]['exist'] = '<strong style="color:red">表名已存在</strong>';
            }
        }
        return $res;
    }

    /**
     * 生成模块信息文件
     * @author Author: btc
     */
    private function mkInfo($data = [])
    {
        // 配置内容
        $config = <<<INFO
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
/**
 * 模块基本信息
 */
return [
    // 模块名[必填]
    'name'        => '{$data['name']}',
    // 模块标题[必填]
    'title'       => '{$data['title']}',
    // 模块唯一标识[必填]，格式：模块名.[应用市场ID].module.[应用市场分支ID]
    'identifier'  => '{$data['identifier']}',
    // 主题模板[必填]，默认default
    'theme'        => 'default',
    // 模块图标[选填]
    'icon'        => '{$data['icon']}',
    // 模块简介[选填]
    'intro' => '{$data['intro']}',
    // 开发者[必填]
    'author'      => '{$data['author']}',
    // 开发者网址[选填]
    'author_url'  => '{$data['url']}',
    // 版本[必填],格式采用三段式：主版本号.次版本号.修订版本号
    // 主版本号【位数变化：1-99】：当模块出现大更新或者很大的改动，比如整体架构发生变化。此版本号会变化。
    // 次版本号【位数变化：0-999】：当模块功能有新增或删除，此版本号会变化，如果仅仅是补充原有功能时，此版本号不变化。
    // 修订版本号【位数变化：0-999】：一般是 Bug 修复或是一些小的变动，功能上没有大的变化，修复一个严重的bug即发布一个修订版。
    'version'     => '{$data['version']}',
    // 模块依赖[可选]，格式[[模块名, 模块唯一标识, 依赖版本, 对比方式]]
    'module_depend' => {$data['module_depend']},
    // 插件依赖[可选]，格式[[插件名, 插件唯一标识, 依赖版本, 对比方式]]
    'plugin_depend' => {$data['plugin_depend']},
    // 模块数据表[有数据库表时必填,不包含表前缀]
    'tables' => {$data['tables']},
    // 原始数据库表前缀,模块带sql文件时必须配置
    'db_prefix' => '{$data['db_prefix']}',
    // 模块预埋钩子[非系统钩子，必须填写]
    'hooks' => {$data['hooks']},
    // 模块配置，格式['sort' => '100','title' => '配置标题','name' => '配置名称','type' => '配置类型','options' => '配置选项','value' => '配置默认值', 'tips' => '配置提示'],各参数设置可参考管理后台->系统->系统功能->配置管理->添加
    'config' => {$data['config']},
];
INFO;
        return file_put_contents($this->appPath. $data['name'] . '/info.php', $config);
    }
}
