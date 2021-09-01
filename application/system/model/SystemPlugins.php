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

use think\facade\Env;
use think\facade\Cache;
use think\Db;
use migu\Dir;
use app\system\model\SystemHook as HookModel;
use app\system\model\SystemHookPlugins as HookPluginsModel;
use app\system\model\SystemMenu as MenuModel;

/**
 * 插件模型
 * @package app\system\model
 */
class SystemPlugins extends SystemBase
{

    // 写入时,转JSON
    public function setConfigAttr($value)
    {
        if (empty($value)) return '';
        return json_encode($value, 1);
    }

    /**
     * 获取插件配置信息
     * 
     * @param  string $name 配置名
     * @param  bool $update 是否更新缓存
     * @author Author: btc
     * @return mixed
     */
    public static function getConfig($name = '', $update = false)
    {
        $result = Cache::get('plugins_config');
        if ($result === false || $update == true) {
            $rows = self::where('status', 2)->column('name,config', 'name');
            $result = [];
            foreach ($rows as $k => $r) {
                if (empty($r)) {
                    continue;
                }
                $config = json_decode($r, 1);
                if (!is_array($config)) {
                    continue;
                }
                foreach ($config as $rr) {
                    switch ($rr['type']) {
                        case 'array':
                        case 'checkbox':
                            $result['plugins_'.$k][$rr['name']] = parse_attr($rr['value']);
                            break;
                        default:
                            $result['plugins_'.$k][$rr['name']] = $rr['value'];
                            break;
                    }
                }
            }
            Cache::tag('hs_plugins')->set('plugins_config', $result);
        }
        return $name != '' ? $result[$name] : $result;
    }
    
    /**
     * 安装插件
     *
     * @param mixed $id 插件标识（支持ID和插件名）
     * @author Author: btc
     * @return mixed 
     */
    public static function install($id)
    {
        $plug = self::where((is_numeric($id) ? 'id' : 'name'), '=', $id)->find();
        if (!$plug) {
            return '插件不存在';
        }

        if ($plug['status'] > 0) {
            return '请勿重复安装此插件';
        }

        $plugPath = Env::get('root_path').'plugins/'.$plug['name'].'/';
        if (!file_exists($plugPath.'info.php')) {
            return '插件文件[info.php]丢失';
        }
        
        $info       = include $plugPath.'info.php';
        $plugClass  = get_plugins_class($plug['name']);
        
        if (!class_exists($plugClass)) {
            return '插件不存在';
        }
        
        $plugObj = new $plugClass;
        
        if(!$plugObj->install()) {
            return '插件安装前的方法执行失败（原因：'. $plugObj->getError().'）';
        }

        // 将插件钩子注入到钩子索引表
        if (isset($plugObj->hooks) && !empty($plugObj->hooks)) {
            if (!HookPluginsModel::storage($plugObj->hooks, $plug['name'])) {
                return '安装插件钩子时出现错误，请重新安装';
            }
        }

        // 导入SQL
        $sqlFile = realpath($plugPath.'sql/install.sql');
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            $sqlList = parse_sql($sql, 0, [$info['db_prefix'] => config('database.prefix')]);
            if ($sqlList) {
                $sqlList = array_filter($sqlList);
                foreach ($sqlList as $v) {
                    // 过滤sql里面的系统表
                    foreach (config('mg_system.tables') as $t) {
                        if (stripos($v, '`'.config('database.prefix').$t.'`') !== false) {
                            return 'install.sql文件含有系统表['.$t.']';
                        }
                    }
                    if (stripos($v, 'DROP TABLE') === false) {
                        try {
                            Db::execute($v);
                        } catch(\Exception $e) {
                            return $e->getMessage();
                        }
                    }
                }
            }
        }

        // 导入菜单
        if ( file_exists($plugPath.'menu.php') ) {
            $menus = include $plugPath.'menu.php';
            // 如果不是数组且不为空就当JSON数据转换
            if (!is_array($menus) && !empty($menus)) {
                $menus = json_decode($menus, 1);
            }
            if (MenuModel::import($menus, 'plugins.'.$plug['name'], 'plugins') == false) {
                // 执行回滚
                MenuModel::where('module', 'plugins.'.$plug['name'])->delete();
                return '插件菜单失败(原因：可能是param参数异常)，请重新安装！';
            }
        }

        // 导入配置信息
        if (isset($info['config']) && !empty($info['config'])) {
            self::where('id', $plug['id'])->setField('config', json_encode($info['config'], 1));
        }

        // 更新插件基础信息
        $sqlmap = [];
        $sqlmap['title'] = $info['title'];
        $sqlmap['identifier'] = $info['identifier'];
        $sqlmap['intro'] = $info['intro'];
        $sqlmap['author'] = $info['author'];
        $sqlmap['url'] = $info['author_url'];
        $sqlmap['version'] = $info['version'];
        $sqlmap['status'] = 2;
        self::where('id', $plug['id'])->update($sqlmap);
        self::getConfig('', true);
        
        if(!$plugObj->installAfter()) {
            return '插件安装前的方法执行失败（原因：'. $plugObj->getError().'）';
        }
        
        self::buildTags();

        return true;
    }
    
    /**
     * 卸载插件
     *
     * @param mixed $id 插件标识（支持ID和插件名）
     * @author Author: btc
     * @return mixed 
     */
    public static function uninstall($id)
    {
        $plug = self::where((is_numeric($id) ? 'id' : 'name'), '=', $id)->find();
        if (!$plug) {
            return '插件不存在';
        }

        if ($plug['status'] == 0) {
            return '插件未安装';
        }

        $plugPath = Env::get('root_path').'plugins/'.$plug['name'].'/';
        
        // 插件基本信息
        if (!file_exists($plugPath.'info.php')) {
            return '插件文件[info.php]丢失';
        }
        $info = include_once $plugPath.'info.php';

        $plugClass = get_plugins_class($plug['name']);
        if (!class_exists($plugClass)) {
            return '插件不存在';
        }

        $plugObj = new $plugClass;

        if(!$plugObj->uninstall()) {
            return '插件卸载前的方法执行失败（原因：'. $plugObj->getError().'）';
        }

        // 查出索引已备生成tags用
        $hookIndexs = HookPluginsModel::where('plugins', '=', $plug['name'])->field('hook,plugins')->select();

        if (!HookPluginsModel::del($plug['name'])) {
            return '插件相关钩子删除失败';
        }
        
        // 导入SQL
        $sqlFile = realpath($plugPath.'sql/uninstall.sql');
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            $sqlList = parse_sql($sql, 0, [$info['db_prefix'] => config('database.prefix')]);
            if ($sqlList) {
                $sqlList = array_filter($sqlList);
                foreach ($sqlList as $v) {
                    // 防止删除整个数据库
                    if (stripos(strtoupper($v), 'DROP DATABASE') !== false) {
                        return 'uninstall.sql文件疑似含有删除数据库的SQL';
                    }
                    // 过滤sql里面的系统表
                    foreach (config('mg_system.tables') as $t) {
                        if (stripos($v, '`'.config('database.prefix').$t.'`') !== false) {
                            return 'uninstall.sql文件含有系统表['.$t.']';
                        }
                    }
                    try {
                        Db::execute($v);
                    } catch(\Exception $e) {
                        return $e->getMessage();
                    }
                }
            }
        }
        
        // 删除插件菜单
        MenuModel::where('module', 'plugins.'.$plug['name'])->delete();

        // 更新插件状态为未安装
        self::where('id', '=', $plug['id'])->update(['status' => 0, 'config' => '']);
        self::getConfig('', true);
        
        if(!$plugObj->uninstallAfter()) {
            return '插件卸载后的方法执行失败（原因：'. $plugObj->getError().'）';
        }
        
        self::buildTags($hookIndexs);

        return true;
    }

    /**
     * 删除插件
     * 
     * @param mixed $id 插件标识（支持ID和插件名）
     * @author Author: btc
     * @return mixed 
     */
    public static function del($id)
    {
        $plug = self::where((is_numeric($id) ? 'id' : 'name'), '=', $id)->find();
        if (!$plug) {
            return '插件不存在';
        }

        if ($plug['status'] != 0) {
            return '请先卸载插件['.$plug['name'].']！';
        }
        
        if (Dir::delDir(Env::get('root_path').'plugins/'.$plug['name']) === false) {
            return '插件目录失败(原因：可能没有权限)！';
        }

        // 删除插件静态资源目录
        Dir::delDir('./static/plugins/'.$plug['name']);

        if (!self::where('id', $plug['id'])->delete()) {
            return '插件数据删除失败';
        }

        return true;
    }

    /**
     * 生成应用行为扩展定义文件
     *
     * @param array $delIndexs 已卸载的插件或已删除的钩子
     * @author Author: btc
     * @return void
     */
    public static function buildTags($delIndexs = [])
    {
        $hooks          = HookModel::where('status', 1)->column('status', 'name');
        $plugins        = self::where('status', 2)->column('status', 'name');
        $hookPlugins    = HookPluginsModel::where('status', 1)->field('hook,plugins')->order('sort')->select();

        if ($hookPlugins) {
            $tags = include Env::get('app_path').'tags.php';
            // 剔除已删除的钩子
            foreach($delIndexs as $v) {
                if (isset($tags[$v['hook']]) && isset($v['plugins'])) {// 卸载关联的插件
                    $plugClass = get_plugins_class($v['plugins']);
                    $key = array_search($plugClass, $tags[$v['hook']]);
                    if ($key !== false) {
                        unset($tags[$v['hook']][$key]);
                    }
                } else if (isset($tags[$v['hook']])) {// 卸载模块的钩子
                    unset($tags[$v['hook']]);
                }
            }
            foreach ($hookPlugins as $v) {
                if (isset($hooks[$v->hook]) && 
                    isset($plugins[$v->plugins])) {
                    $plugClass = get_plugins_class($v->plugins);
                    if (!isset($tags[$v->hook])) {
                        $tags[$v->hook][] = $plugClass;
                    } else if (!in_array($plugClass, $tags[$v->hook])) {
                        $tags[$v->hook][] = $plugClass;
                    }
                }
            }
            $str    = var_export($tags, TRUE);
            $str    = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $str);
            $array  = preg_split("/\r\n|\n|\r/", $str);
            $array  = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [NULL, ']$1', ' => ['], $array);
            $str    = join(PHP_EOL, array_filter(["["] + $array));
            $str    = preg_replace("/[0-9]\ => /", "", $str);
            $str    = "<?php\n\nreturn ".$str.";\n";

            file_put_contents(Env::get('app_path').'tags.php', $str);
        }
    }

    /**
     * 设计并生成标准插件结构
     * 
     * @author Author: btc
     * @return bool
     */
    public function design($data = [])
    {
        $rootPath   = Env::get('root_path');
        $path       = $rootPath.'/plugins/'.$data['name'].'/';
        $dirList    = parse_attr($data['dir']);

        if (in_array('admin', $dirList) !== false) {
            $dirList[] = 'view/admin/index';
        }
        if (in_array('home', $dirList) !== false) {
            $dirList[] = 'view/home/index';
        }
        $dirList[] = 'view/widget';

        unset($data['dir']);
        // 如果存在配置信息，重组数据
        if (isset($data['config']) && !empty($data['config'])) {
            $config = [];
            $inputType = ['array', 'switch', 'radio', 'checkbox', 'select'];
            foreach ($data['config'] as $k => $v) {
                $sort = (int)$v['sort'];
                if (in_array($v['type'], $inputType) != false && empty($v['options'])) {
                    $this->error = '['.$v['title'].']的配置选项不完整！';
                    return false;
                }
                if ($v['options']) {
                    $v['options'] = parse_attr($v['options']);
                }
                $config[$sort] = $v;
            }
            sort($config);
            $data['config'] = json_encode($config, 1);
        } else {
            $config = $data['config'] = '';
        }

        $data['status'] = 0;
        $data['icon'] = ROOT_DIR.'static/plugins/'.$data['name'].'/'.$data['name'].'.png';
        // 验证
        $valid = new \app\system\validate\Plugins;
        if($valid->check($data) !== true) {
            $this->error = $valid->getError();
            return false;
        }

        if (is_dir($path)) {
            $this->error = '插件目录已存在！';
            return false;
        }
        
        if (!self::create($data)) {
            $this->error = '插件生成失败！';
            return false;
        }

        // 生成插件主目录和静态资源目录
        mkdir($path, 0777, true);
        mkdir('./static/plugins/'.$data['name'].'/', 0777, true);
        // 生成插件信息
        $this->mkInfo($path, $data, $config);
        // 生成独立配置文件
        // $this->mkConfig($path, $config);
        // 生成sql文件
        if (in_array('sql', $dirList) !== false) {
            $this->mkSql($path);
        }
        // 生成菜单文件
        $this->mkMenu($path, $data);
        // 生成钩子文件
        $this->mkHook($path, $data);
        // 生成目录结构
        $this->mkDir($path, $dirList);
        // 生成默认示例控制器
        $this->mkExample($path, $data);

        copy('./static/system/image/app.png', './static/plugins/'.$data['name'].'/'.$data['name'].'.png');
        // 生成说明文档
        // $this->mkReadme($path, $data);
        return true;
    }

    /**
     * 生成插件配置
     * 
     * @param string $path 插件完整路径
     * @param string $config 插件配置信息
     * @author Author: btc
     */
    public function mkConfig($path = '', $config = [])
    {
        if (empty($config)) {
            $config = [];
        }
        // 美化数组格式
        $config = var_export($config, true);
        $config = str_replace(['array (', ')'], ['[', ']'], $config);
        $config = preg_replace("/(\s*?\r?\n\s*?)+/", "\n", $config);
        $str = "<?php\n//格式['sort' => '100','title' => '配置标题','name' => '配置名称','type' => '配置类型','options' => '配置选项','value' => '配置默认值', 'tips' => '配置提示'] 各参数设置可参考管理后台->系统->系统功能->配置管理->添加\nreturn ".$config.";\n";
        file_put_contents($path . 'config.php', $str);  
    }

    /**
     * 生成默认示例控制器
     * 
     * @param string $path 插件完整路径
     * @param string $data 插件基本信息
     * @author Author: btc
     */
    public function mkExample($path = '', $data = [])
    {
        if (is_dir($path.'admin')) {
            $admin = "<?php\nnamespace plugins\\".$data["name"]."\\admin;\nuse app\common\controller\Common;\ndefined('IN_SYSTEM') or die('Access Denied');\n\nclass Index extends Common\n{\n    public function index()\n    {\n        return ".'$this->fetch()'.";\n    }\n}";
            file_put_contents($path . 'admin/Index.php', $admin);
            file_put_contents($path.'view/admin/index/index.html', "我是后台模板[".$path."view/admin/index/index.html]\n{include file=\"system@block/layui\" /}");
        }
        if (is_dir($path.'home')) {
            $home = "<?php\nnamespace plugins\\".$data["name"]."\\home;\nuse app\common\controller\Common;\ndefined('IN_SYSTEM') or die('Access Denied');\n\nclass Index extends Common\n{\n    public function index()\n    {\n        return ".'$this->fetch()'.";\n    }\n}";
            file_put_contents($path . 'home/Index.php', $home);
            file_put_contents($path.'view/home/index/index.html', '我是前台模板['.$path.'view/home/index/index.html]');
        }
    }

    /**
     * 生成目录结构
     * 
     * @param string $path 插件完整路径
     * @param array $list 目录列表
     * @author Author: btc
     */
    public function mkDir($path = '', $list = [])
    {
        foreach ($list as $dir) {
            if (!is_dir($path . $dir)) {
                // 创建目录
                mkdir($path . $dir, 0755, true);
            }
        }
    }

    /**
     * 生成SQL文件
     * 
     * @param string $path 插件完整路径
     * @author Author: btc
     */
    public function mkSql($path = '')
    {
        if (!is_dir($path . 'sql')) {
            mkdir($path . 'sql', 0755, true);
        }
        file_put_contents($path . 'sql/install.sql', "/*\n sql安装文件\n*/");
        file_put_contents($path . 'sql/uninstall.sql', "/*\n sql卸载文件\n*/");
        file_put_contents($path . 'sql/demo.sql', "/*\n 演示数据\n*/");
    }

    /**
     * 生成钩子文件
     * 
     * @param string $path 插件完整路径
     * @param string $data 插件基本信息
     * @author Author: btc
     */
    public function mkHook($path = '', $data = [])
    {
        $params = '$params';
        $hooks = '$hooks';
        $code = <<<INFO
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
namespace plugins\\{$data['name']};
use app\common\controller\Plugins;
defined('IN_SYSTEM') or die('Access Denied');
/**
 * {$data['title']}插件
 * @package plugins\\{$data['name']}
 */
class {$data['name']} extends Plugins
{
    /**
     * @var array 插件钩子清单
     */
    public $hooks = [
        // 钩子名称 => 钩子说明【系统钩子，说明不用填写】
        'system_admin_tips',
    ];

    /**
     * system_admin_tips钩子方法
     * @param $params
     */
    public function systemAdminTips($params)
    {
        echo '这是插件[{$data['name']}]的示例！[我在这儿：/plugins/{$data['name']}/{$data['name']}.php]<br>';
    }

    /**
     * 安装前的业务处理，可在此方法实现，默认返回true
     * @return bool
     */
    public function install()
    {
        return true;
    }

    /**
     * 安装后的业务处理，可在此方法实现，默认返回true
     * @return bool
     */
    public function installAfter()
    {
        return true;
    }

    /**
     * 卸载前的业务处理，可在此方法实现，默认返回true
     * @return bool
     */
    public function uninstall()
    {
        return true;
    }

    /**
     * 卸载后的业务处理，可在此方法实现，默认返回true
     * @return bool
     */
    public function uninstallAfter()
    {
        return true;
    }

}
INFO;
        file_put_contents($path.$data['name'].'.php', $code);
    }

    /**
     * 生成菜单文件
     * 
     * @param string $path 插件完整路径
     * @author Author: btc
     */
    public function mkMenu($path = '', $data = [])
    {
        $menus = <<<INFO
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
 * 插件后台菜单
 * 字段说明
 * url【链接地址】如果不是外链请设置为默认：system/plugins/run
 * param【扩展参数】格式：_p={$data['name']}&_c=控制器&_a=方法&aaa=自定义参数
 * 当url值为：system/plugins/run时，param必须录入，且_p={$data['name']}是必传参数
 */
return [
    [
        'pid'           => 5,
        'title'         => '{$data['title']}',
        'icon'          => 'fa fa-cog',
        'url'           => 'system/plugins/run',
        'param'         => '_p={$data['name']}&_c=index&_a=index',
        'target'        => '_self',
        'nav'           => 1,
        'sort'          => 0,
        'childs'        => [
            [
                'title'         => '插件功能1-1',
                'icon'          => 'aicon ai-shezhi',
                'url'           => 'system/plugins/run',
                'param'         => '_p={$data['name']}&_c=index&_a=index',
                'target'        => '_self',
                'sort'          => 0,
                'childs'        => [
                    [
                        'title'         => '插件功能1-1-1',
                        'icon'          => 'aicon ai-shezhi',
                        'url'           => 'system/plugins/run',
                        'param'         => '_p={$data['name']}&_c=index&_a=index',
                        'target'        => '_self',
                        'sort'          => 0,
                    ],
                    [
                        'title'         => '插件功能1-1-2',
                        'icon'          => 'aicon ai-shezhi',
                        'url'           => 'system/plugins/run',
                        'param'         => '_p={$data['name']}&_c=index&_a=index',
                        'target'        => '_self',
                        'sort'          => 0,
                    ],
                    [
                        'title'         => '插件功能1-1-3',
                        'icon'          => 'aicon ai-shezhi',
                        'url'           => 'system/plugins/run',
                        'param'         => '_p={$data['name']}&_c=index&_a=index',
                        'target'        => '_self',
                        'sort'          => 0,
                    ],
                ],
            ],
            [
                'title'         => '插件功能2',
                'icon'          => 'aicon ai-shezhi',
                'url'           => 'system/plugins/run',
                'param'         => '_p={$data['name']}&_c=index&_a=index',
                'target'        => '_self',
                'sort'          => 0,
            ],
            [
                'title'         => '插件功能3',
                'icon'          => 'aicon ai-shezhi',
                'url'           => 'system/plugins/run',
                'param'         => '_p={$data['name']}&_c=index&_a=index',
                'target'        => '_self',
                'sort'          => 0,
            ],
        ],
    ],
];
INFO;
        file_put_contents($path . 'menu.php', $menus);
    }

    /**
     * 生成插件基本信息
     * 
     * @param string $path 插件完整路径
     * @param string $data 插件基本信息
     * @author Author: btc
     */
    public function mkInfo($path = '', $data = [], $config = [])
    {
        if (empty($config)) {
            $config = [];
        }
        // 美化数组格式
        $config = var_export($config, true);
        $config = str_replace(['array (', ')'], ['[', ']'], $config);
        $config = preg_replace("/(\s*?\r?\n\s*?)+/", "\n", $config);
        $code = <<<INFO
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
 * 插件基本信息
 */
return [
    // 核心框架[必填]
    'framework' => 'thinkphp5.1',
    // 插件名[必填]
    'name'        => '{$data['name']}',
    // 插件标题[必填]
    'title'       => '{$data['title']}',
    // 模块唯一标识[必填]，格式：插件名.[应用市场ID].plugins.[应用市场分支ID]
    'identifier'  => '{$data['identifier']}',
    // 插件图标[必填]
    'icon'        => '/static/plugins/{$data['name']}/{$data['name']}.png',
    // 插件描述[选填]
    'intro' => '{$data['intro']}',
    // 插件作者[必填]
    'author'      => '{$data['author']}',
    // 作者主页[选填]
    'author_url'  => '{$data['url']}',
    // 版本[必填],格式采用三段式：主版本号.次版本号.修订版本号
    // 主版本号【位数变化：1-99】：当模块出现大更新或者很大的改动，比如整体架构发生变化。此版本号会变化。
    // 次版本号【位数变化：0-999】：当模块功能有新增或删除，此版本号会变化，如果仅仅是补充原有功能时，此版本号不变化。
    // 修订版本号【位数变化：0-999】：一般是 Bug 修复或是一些小的变动，功能上没有大的变化，修复一个严重的bug即发布一个修订版。
    'version'     => '{$data['version']}',
    // 原始数据库表前缀,插件带sql文件时必须配置
    'db_prefix' => 'db_',
    //格式['sort' => '100','title' => '配置标题','name' => '配置名称','type' => '配置类型','options' => '配置选项','value' => '配置默认值', 'tips' => '配置提示'] 各参数设置可参考管理后台->系统->系统功能->配置管理->添加
    'config'    => {$config},
];
INFO;
        file_put_contents($path.'info.php', $code);
    }
}
