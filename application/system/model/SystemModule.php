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

use migu\Dir;
use think\facade\Build;
use think\facade\Cache;
use think\facade\Env;
use think\Db;
use app\system\model\SystemMenu as MenuModel;
use app\system\model\SystemHook as HookModel;
use app\system\model\SystemPlugins as PluginsModel;

/**
 * 模块模型
 * @package app\system\model
 */
class SystemModule extends SystemBase
{
    
    /**
     * 获取模块配置信息
     * @param  string $name 配置名
     * @param  bool $update 是否更新缓存
     * @author Author: btc
     * @return mixed
     */
    public static function getConfig($name = '', $update = false)
    {
        $result = Cache::get('module_config');
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
                            $result['module_'.$k][$rr['name']] = parse_attr($rr['value']);
                            break;
                        default:
                            $result['module_'.$k][$rr['name']] = $rr['value'];
                            break;
                    }
                }
            }
            Cache::tag('hs_module')->set('module_config', $result);
        }
        return $name != '' ? $result[$name] : $result;
    }

    /**
     * 将已安装模块添加到路由配置文件
     * @param  bool $update 是否更新缓存
     * @author Author: btc
     * @return array
     */
    public static function moduleRoute($update = false)
    {
        $result = cache('module_route');
        if (!$result || $update == true) {
            $map = [];
            $map['status'] = 2;
            $map['name'] =  ['neq', 'admin'];
            $result = self::where($map)->column('name');
            if (!$result) {
                $result = ['route'];
            } else {
                foreach ($result as &$v) {
                    $v = $v.'Route';
                }
            }
            array_push($result, 'route');
            cache('module_route', $result);
        }
        return $result;
    }

    /**
     * 获取所有已安装模块(下拉列)
     * @param string $select 选中的值
     * @author Author: btc
     * @return string
     */
    public static function getOption($select = '', $field='name,title')
    {
        $rows = self::column($field);
        $str = '';
        foreach ($rows as $k => $v) {
            if ($k == 1) {// 过滤超级管理员角色
                continue;
            }
            if ($select == $k) {
                $str .= '<option value="'.$k.'" selected>['.$k.']'.$v.'</option>';
            } else {
                $str .= '<option value="'.$k.'">['.$k.']'.$v.'</option>';
            }
        }
        return $str;
    }
    
    /**
     * 安装模块
     *
     * @param mixed $id
     * @param integer $clear
     * @return bool|string
     */
    public static function install($id, $clear = 1)
    {
        $mod = self::where((is_numeric($id) ? 'id' : 'name'), '=', $id)->find();
        if (!$mod) {
            return '模块不存在';
        }

        $id = $mod['id'];

        if ($mod['status'] > 0) {
            return '请勿重复安装此模块';
        }

        $modPath = Env::get('app_path').$mod['name'].'/';
        if (!file_exists($modPath.'info.php')) {
            return '模块配置文件不存在[info.php]';
        }

        $info = include $modPath.'info.php';

        // 过滤系统表
        foreach ($info['tables'] as $t) {
            if (in_array($t, config('mg_system.tables'))) {
                return '模块数据表与系统表重复['.$t.']';
            }
        }
        // 导入安装SQL
        $sqlFile = realpath($modPath.'sql/install.sql');
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            $sqlList = parse_sql($sql, 0, [$info['db_prefix'] => config('database.prefix')]);
            if ($sqlList) {
                if ($clear == 1) {// 清空所有数据
                    foreach ($info['tables'] as $table) {
                        if (Db::query("SHOW TABLES LIKE '".config('database.prefix').$table."'")) {
                            Db::execute('DROP TABLE IF EXISTS `'.config('database.prefix').$table.'`;');
                        }
                    }
                }
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
                        } catch (\Exception $e) {
                            return $e->getMessage();
                        }
                    }
                }
            }
        }

        // 导入演示SQL
        $sqlFile = realpath($modPath.'sql/demo.sql');
        if (file_exists($sqlFile) && request()->param('demo_data/d', 0) === 1) {
            $sql = file_get_contents($sqlFile);
            $sqlList = parse_sql($sql, 0, [$info['db_prefix'] => config('database.prefix')]);
            if ($sqlList) {
                $sqlList = array_filter($sqlList);
                foreach ($sqlList as $v) {
                    // 过滤sql里面的系统表
                    foreach (config('mg_system.tables') as $t) {
                        if (stripos($v, '`'.config('database.prefix').$t.'`') !== false) {
                            return 'demo.sql文件含有系统表['.$t.']';
                        }
                    }

                    if (stripos($v, 'DROP TABLE') === false) {
                        try {
                            Db::execute($v);
                        } catch (\Exception $e) {
                            return $e->getMessage();
                        }
                    }
                }
            }
        }

        // 导入路由
        if (file_exists($modPath.'route.php')) {
            copy($modPath.'route.php', Env::get('route_path').$mod['name'].'.php');
        }

        // 导入菜单
        if (file_exists($modPath.'menu.php')) {
            $menus = include $modPath.'menu.php';
            // 如果不是数组且不为空就当JSON数据转换
            if (!is_array($menus) && !empty($menus)) {
                $menus = json_decode($menus, 1);
            }
            if (MenuModel::import($menus, $mod['name']) == false) {
                // 执行回滚
                MenuModel::where('module', $mod['name'])->delete();
                return '添加菜单失败，请重新安装';
            }
        }
        
        // 导入模块钩子
        if (!empty($info['hooks'])) {
            $hookModel = new HookModel;
            foreach ($info['hooks'] as $k => $v) {
                $map            = [];
                $map['name']    = $k;
                $map['intro']   = $v;
                $map['source']  = 'module.'.$mod['name'];
                $hookModel->storage($map);
            }
        }
        
        // 导入模块配置
        if (isset($info['config']) && !empty($info['config'])) {
            $menu           = [];
            $menu['pid']    = 10;
            $menu['module'] = $mod['name'];
            $menu['title']  = $mod['title'].'配置';
            $menu['url']    = 'system/system/index';
            $menu['param']  = 'group='.$mod['name'];
            $menu['system'] = 0;
            $menu['debug']  = 0;
            $menu['nav']    = 0;
            $menu['sort']   = 100;
            $menu['status'] = 1;
            
            (new MenuModel)->storage($menu);

            self::where('id', $id)->setField('config', json_encode($info['config'], 1));
        }

        // 更新模块基础信息
        $sqlmap                 = [];
        $sqlmap['title']        = $info['title'];
        $sqlmap['identifier']   = $info['identifier'];
        $sqlmap['intro']        = $info['intro'];
        $sqlmap['author']       = $info['author'];
        $sqlmap['url']          = $info['author_url'];
        $sqlmap['version']      = $info['version'];
        $sqlmap['status']       = 2;

        self::where('id', $id)->update($sqlmap);
        self::getConfig('', true);
        return true;
    }

    /**
     * 卸载模块
     *
     * @param mixed $id 模块标识（支持ID和模块名）
     * @author Author: btc
     * @return mixed
     */
    public static function uninstall($id)
    {
        $mod = self::where((is_numeric($id) ? 'id' : 'name'), '=', $id)->find();
        if (!$mod) {
            return '模块不存在';
        }

        $modPath = Env::get('app_path').$mod['name'].'/';

        // 模块自定义配置
        if (!file_exists($modPath.'info.php')) {
            return '模块配置文件不存在[info.php]';
        }

        $info = include_once $modPath.'info.php';

        // 过滤系统表
        foreach ($info['tables'] as $t) {
            if (in_array($t, config('mg_system.tables'))) {
                return '模块数据表与系统表重复['.$t.']';
            }
        }

        $post = request()->post();
        // 导入SQL
        $sqlFile = realpath($modPath.'sql/uninstall.sql');
        if (file_exists($sqlFile) && $post['clear'] == 1) {
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
                    } catch (\Exception $e) {
                        return $e->getMessage();
                    }
                }
            }
        }

        // 删除路由
        if (file_exists(Env::get('route_path').$mod['name'].'.php')) {
            unlink(Env::get('route_path').$mod['name'].'.php');
        }

        // 删除当前模块菜单
        MenuModel::where('module', $mod['name'])->delete();

        // 删除模块钩子
        $hooks = HookModel::where('source', 'module.'.$mod['name'])->column('id,name as hook,source', 'id');
        $hooks && PluginsModel::buildTags($hooks);

        // 更新模块状态为未安装
        self::where('id', $mod['id'])->update(['status' => 0, 'is_default' => 0, 'config' => '']);
        self::getConfig('', true);
        
        return true;
    }

    /**
     * 删除模块
     *
     * @param mixed $id 模块标识（支持ID和模块名）
     * @author Author: btc
     * @return mixed
     */
    public static function del($id)
    {
        $mod = self::where((is_numeric($id) ? 'id' : 'name'), '=', $id)->find();
        if (!$mod) {
            return '模块不存在';
        }

        if ($mod['name'] == 'system') {
            return '禁止删除系统模块';
        }

        if ($mod['status'] != 0) {
            return '已安装的模块禁止删除';
        }
        
        // 删除模块文件
        $path = Env::get('app_path').$mod['name'];
        if (is_dir($path) && Dir::delDir($path) === false) {
            return '模块删除失败['.$path.']';
        }

        // 删除模块路由
        $path = Env::get('app_path').$mod['name'].'.php';
        if (is_file($path)) {
            @unlink($path);
        }

        // 删除模块记录
        self::where('id', $mod['id'])->delete();

        // 删除菜单记录
        MenuModel::where('module', $mod['name'])->delete();

        // 删除模块模板
        $path = './theme/'.$mod['name'];
        if (is_dir($path) && Dir::delDir($path) === false) {
            return '模块模板删除失败['.$path.']';
        }

        // 删除模块相关附件
        $path = './static/'.$mod['name'];
        if (is_dir($path) && Dir::delDir($path) === false) {
            return '模块资源删除失败['.$path.']';
        }
        
        return true;
    }

    /**
     * 设计并生成标准模块结构
     * @author Author: btc
     * @return bool
     */
    public function design($data = [])
    {
        $app_path = Env::get('app_path');
        if (empty($data)) {
            $data = input('post.');
        }
        
        $icon = 'static/'.$data['name'].'/'.$data['name'].'.png';
        $data['icon'] = ROOT_DIR.$icon;
        $mod_path = $app_path.$data['name'] . '/';
        if (is_dir($mod_path) || self::where('name', $data['name'])->find() || in_array($data['name'], config('mg_system.modules')) !== false) {
            $this->error = '模块已存在！';
            return false;
        }

        if (!is_writable(Env::get('root_path').'application')) {
            $this->error = '[application]目录不可写！';
            return false;
        }

        if (!is_writable('./theme')) {
            $this->error = '[theme]目录不可写！';
            return false;
        }

        if (!is_writable('./static')) {
            $this->error = '[static]目录不可写！';
            return false;
        }

        // 生成模块目录结构
        $build = [];
        if ($data['file']) {
            $build[$data['name']]['__file__'] = explode(',', $data['file']);
        }
        $build[$data['name']]['__dir__'] = parse_attr($data['dir']);
        $build[$data['name']]['model'] = ['example'];
        $build[$data['name']]['view'] = ['index/index'];
        Build::run($build);
        if (!is_dir($mod_path)) {
            $this->error = '模块目录生成失败[application/'.$data['name'].']！';
            return false;
        }

        // 删除默认的应用配置目录
        Dir::delDir(Env::get('config_path').$data['name']);

        // 生成对应的前台主题模板目录、静态资源目录、后台静态资源目录
        $dir_list = [
            'public/theme/'.$data['name'].'/default/static/css',
            'public/theme/'.$data['name'].'/default/static/js',
            'public/theme/'.$data['name'].'/default/static/image',
            'public/theme/'.$data['name'].'/default/index',
            'public/static/'.$data['name'].'/css',
            'public/static/'.$data['name'].'/js',
            'public/static/'.$data['name'].'/image',
        ];
        self::mkDir($dir_list);
        self::mkThemeConfig('./theme/'.$data['name'].'/default/', $data);
        self::mkSql($mod_path, $data);
        self::mkMenu($mod_path, $data);
        self::mkInfo($mod_path, $data);
        self::mkControl($mod_path, $data);

        // 将生成的模块信息添加到模块管理表
        $sql = [];
        $sql['name'] = $data['name'];
        $sql['identifier'] = $data['identifier'];
        $sql['title'] = $data['title'];
        $sql['intro'] = $data['intro'];
        $sql['author'] = $data['author'];
        $sql['icon'] = $data['icon'];
        $sql['version'] = $data['version'];
        $sql['url'] = $data['url'];
        $sql['config'] = '';
        $sql['status'] = 0;
        self::create($sql);

        // 复制默认应用图标
        copy('./static/system/image/app.png', './'.$icon);
        // 复制system布局模板到当前模块
        copy($app_path.'system/view/layout.html', $mod_path.'view/layout.html');
        return true;
    }

    /**
     * 生成目录
     * @param array $list 目录列表
     * @author Author: btc
     */
    public static function mkDir($list)
    {
        $root_path = Env::get('root_path');
        foreach ($list as $dir) {
            if (!is_dir($root_path . $dir)) {
                // 创建目录
                mkdir($root_path . $dir, 0755, true);
            }
        }
    }

    /**
     * 生成模块控制器
     * @author Author: btc
     */
    public static function mkControl($path = '', $data = [])
    {
        // 删除默认控制器目录和文件
        unlink($path.'controller/Index.php');
        rmdir($path.'controller');
        // 生成后台默认控制器
        if (is_dir($path.'admin')) {
            $admin_contro = "<?php\nnamespace app\\".$data["name"]."\\admin;\nuse app\system\admin\Admin;\n\nclass Index extends Admin\n{\n    protected ".'$miguModel'." = '';//模型名称[通用添加、修改专用]\n    protected ".'$miguTable'." = '';//表名称[通用添加、修改专用]\n    protected ".'$miguAddScene'." = '';//添加数据验证场景名\n    protected ".'$miguEditScene'." = '';//更新数据验证场景名\n\n    public function index()\n    {\n        return ".'$this->fetch()'.";\n    }\n}";
            // 删除框架生成的html文件
            @unlink($path . 'view/index/index.html');
            file_put_contents($path . 'admin/Index.php', $admin_contro);
            file_put_contents($path . 'view/index/index.html', "我是后台模板[".$path."view/index/index.html]\n{include file=\"system@block/layui\" /}");
        }

        // 生成前台默认控制器
        if (is_dir($path.'home')) {
            $home_contro = "<?php\nnamespace app\\".$data["name"]."\\home;\nuse app\common\controller\Common;\n\nclass Index extends Common\n{\n    public function index()\n    {\n        return ".'$this->fetch()'.";\n    }\n}";
            file_put_contents($path . 'home/Index.php', $home_contro);
            file_put_contents('./theme/'.$data['name'].'/default/index/index.html', '我是前台模板[/theme/'.$data['name'].'/default/index/index.html]');
        }
    }

    /**
     * 生成SQL文件
     * @author Author: btc
     */
    public static function mkSql($path = '')
    {
        if (!is_dir($path . 'sql')) {
            mkdir($path . 'sql', 0755, true);
        }
        file_put_contents($path . 'sql/install.sql', "/*\n sql安装文件\n*/");
        file_put_contents($path . 'sql/uninstall.sql', "/*\n sql卸载文件\n*/");
        file_put_contents($path . 'sql/demo.sql', "/*\n 演示数据\n*/");
    }

    /**
     * 生成模块菜单文件
     */
    public static function mkMenu($path = '', $data = [])
    {
        // 菜单示例代码
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
 * 模块菜单
 * 字段说明
 * url 【链接地址】格式：{$data['name']}/控制器/方法，可填写完整外链[必须以http开头]
 * param 【扩展参数】格式：a=123&b=234555
 */
return [
    [
        'pid'           => 0,
        'title'         => '{$data['title']}',
        'icon'          => 'fa fa-cog',
        'module'        => '{$data['name']}',
        'url'           => '{$data['name']}',
        'param'         => '',
        'target'        => '_self',
        'nav'           => '',
        'sort'          => 100,
    ],
];
INFO;
        file_put_contents($path . 'menu.php', $menus);
    }

    /**
     * 生成模块信息文件
     * @author Author: btc
     */
    public static function mkInfo($path = '', $data = [])
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
    // 核心框架[必填]
    'framework' => 'thinkphp5.1',
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
    'module_depend' => [],
    // 插件依赖[可选]，格式[[插件名, 插件唯一标识, 依赖版本, 对比方式]]
    'plugin_depend' => [],
    // 模块数据表[有数据库表时必填,不包含表前缀]
    'tables' => [
        // 'table_name',
    ],
    // 原始数据库表前缀,模块带sql文件时必须配置
    'db_prefix' => 'db_',
    // 模块预埋钩子[非系统钩子，必须填写]
    'hooks' => [
        // '钩子名称' => '钩子描述'
    ],
    // 模块配置，格式['sort' => '100','title' => '配置标题','name' => '配置名称','type' => '配置类型','options' => '配置选项','value' => '配置默认值', 'tips' => '配置提示'],各参数设置可参考管理后台->系统->系统功能->配置管理->添加
    'config' => [],
];
INFO;
        file_put_contents($path . 'info.php', $config);
    }

    public static function mkThemeConfig($path, $data = [])
    {
        $str = '<?xml version="1.0" encoding="ISO-8859-1"?>
<root>
    <item id="title"><![CDATA[默认模板]]></item>
    <item id="version"><![CDATA[v1.0.0]]></item>
    <item id="time"><![CDATA['.date('Y-m-d H:i').']]></item>
    <item id="author"><![CDATA[咪咕CMS]]></item>
    <item id="copyright"><![CDATA[咪咕CMS]]></item>
    <item id="db_prefix"><![CDATA[db_]]></item>
    <item id="identifier" title="默认模板必须留空，非默认模板必须填写对应的应用标识"><![CDATA[]]></item>
    <item id="depend" title="请填写当前对应的模块标识"><![CDATA['.$data['identifier'].']]></item>
</root>';
        file_put_contents($path.'config.xml', $str);
    }
}
