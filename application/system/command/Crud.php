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
use think\facade\App;
use think\facade\Env;
use think\facade\Config;
use think\Db;
use migu\Dir;
use app\system\model\SystemMenu as MenuModel;

class Crud extends Command
{
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
    public $appName = '';

    /**
     * 表名称
     *
     * @var string
     */
    public $tableName = '';

    /**
     * 表基础信息
     *
     * @var string
     */
    public $tableInfo = [];

    /**
     * 表字段信息
     *
     * @var string
     */
    public $tableFields = [];

    /**
     * 模型名称
     *
     * @var string
     */
    public $modelName = '';

    /**
     * 控制器名称
     *
     * @var string
     */
    public $controllerName = '';

    /**
     * url前缀
     *
     * @var string
     */
    public $urlPrefix = '';

    /**
     * 数据表主键
     *
     * @var string
     */
    public $PK = '';

    /**
     * 待生成的关联模型
     *
     * @var array
     */
    public $relationModel = [];

    /**
     * 待生成的关联模型方法
     *
     * @var array
     */
    public $relationModelMethod = [];
    
    /**
     * 关联模型外键列表显示清单
     *
     * @var array
     * ['关联外键' => '关联模型名']
     */
    public $relationModelFKColsDisplay = [];

    /**
     * 模型JSON字段定义
     *
     * @var array
     */
    public $jsonFields = [];

    /**
     * 记录复选框类型的字段，用于生成修改器和获取器
     *
     * @var array
     */
    public $checkboxAttr = [];

    /**
     * 记录时间戳字段，用于生成修改器和获取器
     *
     * @var array
     */
    public $timestampAttr = [];

    /**
     * 过滤字段
     *
     * @var array
     */
    public $filterFields = [];
    
    /**
     * 默认字段类型映射
     *
     * @var array
     */
    public $defaultFieldTypeMap = [
        'varchar' => 'text',
        'int' => 'text',
        'date' => 'date',
        'datetime' => 'datetime',
        'timestamp' => 'datetime',
        'time' => 'time',
        'year' => 'year',
    ];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('crud')
            ->addArgument('table', Argument::OPTIONAL, "Table name")
            ->addOption('table', 't', Option::VALUE_REQUIRED, 'Table name without prefix', null)
            ->addOption('controller', 'c', Option::VALUE_OPTIONAL, 'Controller name', null)
            ->addOption('model', 'm', Option::VALUE_OPTIONAL, 'Model name', null)
            ->addOption('force', 'f', Option::VALUE_OPTIONAL, 'Forced coverage', null)
            ->addOption('relation', 'r', Option::VALUE_OPTIONAL | Option::VALUE_IS_ARRAY, 'Association model table name', null)
            ->addOption('module', null, Option::VALUE_REQUIRED, 'Module name', null)
            ->addOption('plugins', null, Option::VALUE_REQUIRED, 'Plugins name', null)
            ->addOption('logic', null, Option::VALUE_OPTIONAL, 'Logic name', null)
            ->addOption('service', null, Option::VALUE_OPTIONAL, 'Service name', null)
            ->addOption('validate', null, Option::VALUE_OPTIONAL, 'Validate name', null)
            ->addOption('addscene', null, Option::VALUE_OPTIONAL, 'Validator add scene', null)
            ->addOption('editscene', null, Option::VALUE_OPTIONAL, 'Validator edit scene', null)
            ->addOption('deletetime', null, Option::VALUE_OPTIONAL, 'Soft Delete field name', null)
            ->addOption('page', null, Option::VALUE_OPTIONAL, 'Table pageing', null)
            ->addOption('dataright', null, Option::VALUE_OPTIONAL, 'Data permissions, optional values: own, org', null)
            ->addOption('datarightfield', null, Option::VALUE_OPTIONAL, 'Data permission field definition', null)
            ->addOption('menuid', null, Option::VALUE_OPTIONAL, 'Specify the parent menu id', null)
            ->setDescription('Build controller and model from table');
    }

    protected function execute(Input $input, Output $output)
    {
        !defined('DS') && define('DS', DIRECTORY_SEPARATOR);
        define('ADMIN_ID', 1);
        
        $table = $input->getArgument('table');
        !$table && $table = $input->getOption('table');
        if (!$table) {
            $output->error("Table must");
            return;
        }
        
        if ($input->hasOption('module')) {
            $this->appName = $input->getOption('module'); 
        } else if ($input->hasOption('plugins')) {
            $this->appName = $input->getOption('plugins');
            $this->appType = 'plugins';
        } else {
            $output->error('Module and plugin cannot be empty at the same time');
            return;
        }

        if (!is_dir($this->getRootPath())) {
            $output->error($this->appType." folder does not exist,Please check --module parameters.\nFolder path: ".$this->getRootPath());
            return;
        }
        
        $prefix         = Config::get('database.prefix');
        $database       = Config::get('database.database');
        $this->fullTableName  = stripos($table, $prefix) === 0 ? $table : $prefix.$table;// 原始表名（有前缀）

        $this->tableName        = substr($this->fullTableName, strlen($prefix));// 原始表名（无前缀）
        $this->controllerName   = parse_name(($input->getOption('controller') ?: $this->tableName), 1);
        $this->modelName        = parse_name(($input->getOption('model') ?: $this->tableName), 1);
        $this->urlPrefix        = '/admin.php/'.$this->appName.'/'.$this->controllerName.'/';
        $this->tableInfo        = Db::query("SHOW TABLE STATUS LIKE '{$this->fullTableName}'");
        $this->controllerFile   = $this->getRootPath() . 'admin' .DS . $this->controllerName . '.php';
        $this->modelFile        = $this->getRootPath() . 'model' .DS . $this->modelName . '.php';

        if (!$this->tableInfo) {
            $output->error('Table does not exist');
            return;
        }

        $this->tableInfo = $this->tableInfo[0];
        
        if(!$this->tableInfo['Comment']) {
            $output->error('Table comment does not exist');
            return;
        }
        
        $sql = "SELECT * FROM `information_schema`.`columns` WHERE TABLE_SCHEMA = ? AND table_name = ? ORDER BY ORDINAL_POSITION";

        $columns = Db::query($sql, [$database, $this->fullTableName]);

        $this->tableFields = array_column($columns, 'COLUMN_NAME');
        $this->relationModelHandle();

        // 组装表单项
        $formItems = [
            [
                'type' => 'hidden',
                'name' => '',
            ],
        ];

        // 组装表格列
        $tableCols = [
            [
                'type' => 'checkbox',
            ],
        ];

        // 组装表格筛选
        $filterItems = [];

        foreach($columns as $v) {
            $comment = $v['COLUMN_COMMENT'];
            $fieldName = $v['COLUMN_NAME'];
            $option = [];

            if (!$comment && $v['COLUMN_KEY'] != 'PRI') {
                $output->error('Field name `'.$fieldName.'` is missing a comment');
                return;
            }

            $comment = explode('|', $comment);
            
            $title = $comment[0];

            if ($v['COLUMN_KEY'] == 'PRI') {
                $formItems[0]['name'] = $this->PK = $fieldName;
                continue;
            }

            if (isset($comment[1]) && is_numeric($comment[1])) {
                $type = $this->defaultFieldTypeMap[$v['DATA_TYPE']] ?? 'input';
                $display = str_split($comment[1]);
            } else {
                $type = $comment[1] ?? $this->defaultFieldTypeMap[$v['DATA_TYPE']] ?? 'input';
                $display = str_split($comment[2] ?? '110');
            }

            if (isset($this->relationModelMethod[$fieldName])) {
                $option = '%}model("'.$this->relationModelMethod[$fieldName]['model'].'")->column("'.$this->relationModelMethod[$fieldName]['pk'].','.$this->relationModelMethod[$fieldName]['field'].'"){%';
                in_array($type, ['select', 'radio', 'checkbox']) == false && $type = 'select';
            } else if (isset($comment[3])) {
                $option = parse_attr($comment[3]);
            }

            $colsDisplay    = $display[0] ?? 1;// 数据列
            $formDisplay    = $display[1] ?? 1;// 表单
            $filterDisplay  = $display[2] ?? 0;// 表头筛选

            
            // 时间字段类型矫正
            if (in_array($fieldName, ['create_time', 'update_time', 'delete_time'])) {
                $type = 'datetime';
                if ($fieldName == 'delete_time') {
                    $formDisplay = $colsDisplay = $filterDisplay = 0;
                }
            }

            if (in_array($type, ['editor'])) {// 编辑器禁止在数据列表显示
                $colsDisplay = 0;
            } else if (in_array($type, ['files', 'images'])) {// 识别json字段
                $this->jsonFields[] = $fieldName;
            } else if ($type == 'checkbox') {// 复选框强制生成checkbox获取器和修改器
                $this->checkboxAttr[] = $fieldName;
            } else if (in_array($type, ['datetime', 'time', 'date']) && $v['DATA_TYPE'] == 'int') {
                $this->timestampAttr[$fieldName] = $type;
            }

            // 提取表单字段
            if ($formDisplay) {
                $formItems[] = [
                    'type'  => $type,
                    'title' => $title,
                    'name'  => $fieldName,
                    'option' => $option,
                    'value' => in_array($type, ['input', 'datetime', 'date', 'time', 'switch']) && $v['COLUMN_DEFAULT'] == 0 ? '' : $v['COLUMN_DEFAULT'],
                ];
            }
            
            // 提取过滤字段
            if ($filterDisplay) {
                $filterItems[] = [
                    'type'  => $type,
                    'title' => $title,
                    'name'  => $fieldName,
                    'value' => $v['COLUMN_DEFAULT'],
                    'option' => $option,
                ];
                $this->filterFields[] = $fieldName;
            }

            // 提取表格字段
            if ($colsDisplay) {
                $colsArr = ['field' => $fieldName, 'title' => $title];
                if (isset($this->relationModelMethod[$fieldName])) {
                    $this->relationModelFKColsDisplay[$fieldName] = $this->relationModelMethod[$fieldName];
                    $colsArr['templet'] = '<div>{{ d.'.parse_name($this->relationModelMethod[$fieldName]['method']).'.'.$this->relationModelMethod[$fieldName]['field'].' }}</div>';
                } else {
                    // 特殊类型处理
                    switch($type) {
                        case 'checkbox':
                            $colsArr['templet'] = 'function(d) {
            var obj = '.json_encode(isset($comment[3]) ? $option : '').', str = "";
            for(var i in d.'.$fieldName.') {
                i > 0 ? str += \'、\' : \'\';
                str += obj[i];
            }
            return str;
        }';
                            break;
                        case 'radio':
                        case 'select':
                            $colsArr['templet'] = 'function(d) {
            var obj = '.json_encode(isset($comment[3]) ? $option : '').';
            return obj[d.'.$fieldName.'] ? obj[d.'.$fieldName.'] : "---";
        }';
                            break;
                        case 'tag':
                            $colsArr['templet'] = 'function(d) {
            if (d.'.$fieldName.') {
                var str = "", data = d.'.$fieldName.';
                if (typeof(data) != "object" && data.toString().indexOf(",") != -1) {
                    data = data.split(",");
                }
                
                if (typeof(data) == "object") {
                    for(var i in data) {
                        str += "<span class=\'layui-badge layui-bg-blue\'>"+data[i]+"</span> ";
                    }
                }

                return str;
            } else {
                return "";
            }
        }';
                            break;

                        case 'image':
                            $colsArr['templet'] = 'function(d) {
            if (d.'.$fieldName.') {
                return "<img src="+d.'.$fieldName.'+" width=\"30\" height=\"30\" />";
            } else {
                return "";
            }
        }';
                            break;
                    }
                }

                $type == 'switch' && $colsArr['type'] = 'switch';
                $tableCols[] = $colsArr;
            }
        }

        // 生成菜单
        $this->buildMenu();
        // 生成关联模型文件
        $this->buildRelationModel();
        // 生成控制器文件
        $this->buildController($formItems, $tableCols, $filterItems);
        // 生成模型文件
        $this->buildModel();

        $output->info('Successed');
    }

    // 生成权限菜单
    protected function buildMenu()
    {
        $menuid = $this->input->getOption('menuid');
        if (!$menuid) {
            $where      = [];
            if ($this->appType == 'plugins') {
                $info = include_once $this->getRootPath().'info.php';
                $where[] = ['title', '=', $info['title']];
                $where[] = ['module', '=', 'plugins.'.$this->appName];
            } else {
                $where[]    = ['pid', '=', 0];
                $where[] = ['url', '=', $this->appName];
                $where[] = ['module', '=', $this->appName];
            }
            
            $row = MenuModel::where($where)->find();
            if (!$row) {
                $this->output->error('Module is not installed');
            }

            $menuid = $row['id'];
        } else {
            if (!MenuModel::where('id', '=', $menuid)->find()) {
                $this->output->error('--menuid value does not exist');
                exit;
            }
        }

        if ($this->appType == 'plugins') {
            $controller = lcfirst($this->controllerName);
            $module = 'plugins.'.$this->appName;
            if (MenuModel::where('module', '=', $module)->where('param', '=', '_a=index&_c='.$controller.'&_p='.$this->appName)->find()) {
                return;
            }
            $pid = MenuModel::getPluginsParents($module, $menuid);
            $data = [
                [
                    'pid'   => $menuid,
                    'title' => $this->tableInfo['Comment'],
                    'icon' => '',
                    'module' => $module,
                    'url' => 'system/plugins/run',
                    'param' => '_a=index&_c='.$controller.'&_p='.$this->appName,
                    'nav' => 1,
                    'target' => '_self',
                    'sort' => 100,
                    'childs' => [
                        [
                            'title' => '添加',
                            'icon' => '',
                            'module' => $module,
                            'url' => 'system/plugins/run',
                            'param' => '_a=add&_c='.$controller.'&_p='.$this->appName,
                            'nav' => 0,
                            'target' => '_self',
                            'sort' => 0,
                            'childs' => '',
                        ],
                        [
                            'title' => '修改',
                            'icon' => '',
                            'module' => $module,
                            'url' => 'system/plugins/run',
                            'param' => '_a=edit&_c='.$controller.'&_p='.$this->appName,
                            'nav' => 0,
                            'target' => '_self',
                            'sort' => 0,
                            'childs' => '',
                        ],
                        [
                            'title' => '删除',
                            'icon' => '',
                            'module' => $module,
                            'url' => 'system/plugins/run',
                            'param' => '_a=del&_c='.$controller.'&_p='.$this->appName,
                            'nav' => 0,
                            'target' => '_self',
                            'sort' => 0,
                            'childs' => '',
                        ],
                    ],
                ],
            ];
        } else {
            $contr = lcfirst($this->controllerName);
            $contrUrl = $this->appName.'/'.$contr.'/index';
            if (MenuModel::where('module', '=', $this->appName)->where('url', '=', $contrUrl)->find()) {
                return;
            }
            
            $pid = MenuModel::getParents($menuid);

            $data = [
                [
                    'pid'   => $menuid,
                    'title' => $this->tableInfo['Comment'],
                    'icon' => '',
                    'module' => $this->appName,
                    'url' => $contrUrl,
                    'param' => '',
                    'nav' => 1,
                    'target' => '_self',
                    'sort' => 100,
                    'childs' => [
                        [
                            'title' => '添加',
                            'icon' => '',
                            'module' => $this->appName,
                            'url' => $this->appName.'/'.$contr.'/add',
                            'param' => '',
                            'nav' => 0,
                            'target' => '_self',
                            'sort' => 0,
                            'childs' => '',
                        ],
                        [
                            'title' => '修改',
                            'icon' => '',
                            'module' => $this->appName,
                            'url' => $this->appName.'/'.$contr.'/edit',
                            'param' => '',
                            'nav' => 0,
                            'target' => '_self',
                            'sort' => 0,
                            'childs' => '',
                        ],
                        [
                            'title' => '删除',
                            'icon' => '',
                            'module' => $this->appName,
                            'url' => $this->appName.'/'.$contr.'/del',
                            'param' => '',
                            'nav' => 0,
                            'target' => '_self',
                            'sort' => 0,
                            'childs' => '',
                        ],
                    ],
                ],
            ];
        }

        MenuModel::import($data, $this->appName, $this->appType, $menuid);
        MenuModel::export($pid);
    }

    /**
     * 关联模型处理
     *
     * @return void
     */
    protected function relationModelHandle()
    {
        $relations = $this->input->getOption('relation');
        if (!$relations) {
            return;
        }

        foreach($relations as $r) {
            $exp = explode(',', $r);
            if (count($exp) <= 0) {
                $this->output->error('--relation parameter format error');
                exit();
            }

            $exp = explode(',', $r);

            $model  = $method = $exp[0];// 关联模型名称，支持跨模块，格式：[模块]/模型名称
            $fk     = $exp[1];// 外键名
            $field  = $exp[2] ?? '';// 显示的字段，不允许为空
            if (!$field) {
                $this->output->error('--relation parameter format error');
                exit();
            }

            $mode   = $exp[3] ?? 'belongsTo';// 关联模式 belongsTo/hasOne
            $pk     = $exp[4] ?? 'id';// 主键名
            $type   = $exp[5] ?? 'module';// 应用类型：模块(module)/插件(plugins)
            $type   = $type == 'plugins' ?: 'app';

            if (strpos($model, '/')) {
                $exp    = explode('/', $model);
                $method = parse_name($exp[1], 1);
                if (!$method) {
                    $this->output->error('--relation parameter format error');
                    exit();
                }
                
                $model = '\\'.$type.'\\'.$exp[0].'\\model\\'.$method;
            
                $this->relationModel[] = ['type' => $type, 'app' => $exp[0], 'model' => $method];
            } else {
                $method = parse_name($method, 1);
                $model = parse_name($model, 1);
            
                $this->relationModel[] = ['type' => $type, 'app' => $this->appName, 'model' => $model];
            }
            
            $this->relationModelMethod[$fk] = [
                'method'    => lcfirst($method),
                'mode'      => $mode,
                'model'     => $model,
                'fk'        => $fk,
                'pk'        => $pk,
                'field'     => str_replace('/', ',', $field),
            ];
        }
    }

    /**
     * 生成关联模型文件
     *
     * @return void
     */
    protected function buildRelationModel()
    {
        if (!$this->relationModel) {
            return;
        }
        
        $stub       = $this->getStub('model'.DS.'relation_model');
        $prefix     = Config::get('database.prefix');
        $database   = Config::get('database.database');
        $pluginsPath = Env::get('root_path') . 'plugins' . DS;
        $modulePath = Env::get('app_path');

        foreach ($this->relationModel as $v) {
            if ($v['type'] == 'plugins') {
                $filePath = $pluginsPath . $v['app'] . DS . 'model' .DS . $v['model'] . '.php';
            } else {
                $filePath = $modulePath . $v['app'] . DS . 'model' .DS . $v['model'] . '.php';
            }

            // 关联模型文件已存在的就跳过生成
            if (is_file($filePath)) continue;

            $tableName = parse_name($v['model']);
            $fullTable = $prefix.$tableName;
            $tableInfo = Db::query("SHOW TABLE STATUS LIKE '{$fullTable}'");
            if (!$tableInfo) {
                $this->output->error('The associated table does not exist');
                exit;
            }

            $tableInfo = $tableInfo[0];
            
            if(!$tableInfo['Comment']) {
                $this->output->error('Correlation table is missing comments');
                exit;
            }
            
            $columns = Db::query("SELECT * FROM `information_schema`.`columns` WHERE TABLE_SCHEMA = ? AND table_name = ? ORDER BY ORDINAL_POSITION", [$database, $fullTable]);
            
            // 获取表所有字段
            $tableFields = array_column($columns, 'COLUMN_NAME');
            
            // 查找主键
            $tableKey = array_column($columns, 'COLUMN_KEY');
            $seachKey = array_search('PRI', $tableKey, true);
            if ($seachKey === false) {
                $this->output->error('Missing primary key in associated table');
                exit;
            }

            $pk = $columns[$seachKey]['COLUMN_NAME'];

            $softDelete = '';
            $deleteTime = in_array('delete_time', $tableFields) ? 'delete_time' : '';
            if ($deleteTime) {
                $softDelete = 'use SoftDelete;'."\n";
                $softDelete .= '    protected $deleteTime = \''.$deleteTime.'\';'."\n";
                $softDelete .= '    protected $defaultSoftDelete = 0;';
            }
            
            $assign = [
                '{%namespace%}'         => $v['type'].'\\'.$v['app'],
                '{%classTitle%}'        => $tableInfo['Comment'],
                '{%className%}'         => $v['model'],
                '{%definePK%}'          => $pk != 'id' ? 'protected $pk = \''.$pk.'\';' : '',
                '{%autoWriteTimestamp%}'=> in_array('create_time', $tableFields) || in_array('update_time', $tableFields) ? 'true' : 'false',
                '{%softDelete%}'        => $softDelete,
                '{%useSoftDelete%}'     => $softDelete ? 'use think\model\concern\SoftDelete;' : '',
            ];
    
            $content = str_replace(array_keys($assign), array_values($assign), $stub);
        
            file_put_contents($filePath, $content);
        }
        return true;

    }

    /**
     * 生成模型文件
     *
     * @return void
     */
    protected function buildModel()
    {
        if (!$this->input->hasOption('force')) {
            // 检查模型文件是否存在
            if (is_file($this->modelFile)) {
                $this->output->info("Are you sure you want to override the model file? Type 'yes' to override(default 'no'):");
                $stdin = fgets(defined('STDIN') ? STDIN : fopen('php://stdin', 'r'));
                if (trim($stdin) != 'yes') {
                    return;
                }
            }
        }

        $stub = $this->getStub('model');
        $softDelete = $defineTable = '';
        $deleteTime = $this->input->getOption('deletetime') ?: in_array('delete_time', $this->tableFields) ? 'delete_time' : '';
        if ($deleteTime) {
            $softDelete = 'use SoftDelete;'."\n";
            $softDelete .= '    protected $deleteTime = \''.$deleteTime.'\';'."\n";
            $softDelete .= '    protected $defaultSoftDelete = 0;';
        }

        if ($this->modelName != parse_name($this->tableName, 1)) {
            $defineTable = 'protected $table = "'.$this->fullTableName.'";';
        }

        // 生成关联模型方法
        $relationModelMethod = '';
        if ($this->relationModelMethod) {
            $relationModelMethodStub = $this->getStub('model'.DS.'relation_method');
            
            foreach ($this->relationModelMethod as $v) {
                $assign = [
                    '{%relationMethod%}'=> $v['method'],
                    '{%relationMode%}'  => $v['mode'],
                    '{%relationModel%}' => $v['model'],
                    '{%ForeignKey%}'    => $v['fk'],
                    '{%PrimaryKey%}'    => $v['pk'],
                ];

                $relationModelMethod .= str_replace(array_keys($assign), array_values($assign), $relationModelMethodStub);
            }
        }

        // 生成复选框专用的修改器和获取器
        $checkboxAttr = '';
        if ($this->checkboxAttr) {
            $checkboxAttrSub = $this->getStub('model'.DS.'checkbox_attr');
            foreach($this->checkboxAttr as $v) {
                $checkboxAttr .= str_replace('{%fieldName%}', parse_name($v, 1), $checkboxAttrSub);
            }
        }

        // 生成时间戳专用的修改器和获取器
        $timestampAttr = '';
        if ($this->timestampAttr) {
            $timestampAttrSub = $this->getStub('model'.DS.'timestamp_attr');
            foreach($this->timestampAttr as $k => $v) {
                $format = ['datetime' => 'Y-m-d H:i:s', 'date' => 'Y-m-d', 'time' => 'H:i:s'];
                $timestampAttr .= str_replace(['{%fieldName%}', '{%format%}'], [parse_name($k, 1), $format[$v]], $timestampAttrSub);
            }
        }
        
        $assign = [
            '{%namespace%}'         => $this->getNamespace(),
            '{%classTitle%}'        => $this->tableInfo['Comment'],
            '{%className%}'         => $this->modelName,
            '{%tableName%}'         => $this->tableName,
            '{%definePK%}'          => $this->PK != 'id' ? 'protected $pk = \''.$this->PK.'\';' : '',
            '{%autoWriteTimestamp%}'=> in_array('create_time', $this->tableFields) || in_array('update_time', $this->tableFields) ? 'true' : 'false',
            '{%defineTable%}'       => $defineTable,
            '{%softDelete%}'        => $softDelete,
            '{%useSoftDelete%}'     => $softDelete ? 'use think\model\concern\SoftDelete;' : '',
            '{%relationMethod%}'    => $relationModelMethod,
            '{%jsonFields%}'        => $this->jsonFields ? $this->arrayToString($this->jsonFields) : '[]',
            '{%checkboxAttr%}'      => $checkboxAttr,
            '{%timestampAttr%}'     => $timestampAttr,
        ];

        $content = str_replace(array_keys($assign), array_values($assign), $stub);
    
        file_put_contents($this->getRootPath() . 'model' .DS . $this->modelName . '.php', $content);
        
        return true;
    }

    /**
     * 生成控制器文件
     *
     * @param array $formItems 表单项
     * @param array $tableCols 表头参数
     * @param array $filterItems 筛选表单
     * @return void
     */
    protected function buildController($formItems, $tableCols, $filterItems)
    {
        if (!$this->input->hasOption('force')) {
            // 检查控制器是否存在
            if (is_file($this->controllerFile)) {
                $this->output->info("Are you sure you want to override the controller file? Type 'yes' to override(default 'no'):");
                $stdin = fgets(defined('STDIN') ? STDIN : fopen('php://stdin', 'r'));
                if (trim($stdin) != 'yes') {
                    return;
                }
            }
        }

        $buildForm  = $this->buildForm($formItems);
        $buildTable = $this->buildTable($tableCols, $filterItems);
        $dataRight  = $this->input->getOption('dataright');
        $dataRightField = $this->input->getOption('datarightfield');
        $stub       = $this->getStub('controller');
        $with       = [];

        if ($this->relationModelFKColsDisplay) {
            foreach($this->relationModelFKColsDisplay as $k => $v) {
                $with[$v['method']] = '%}function($query) {$query->field("'.$v['pk'].','.$v['field'].'");}{%';
            }
        }
        
        $assign = [
            '{%namespace%}'         => $this->getNamespace(),
            '{%classTitle%}'        => $this->tableInfo['Comment'],
            '{%className%}'         => $this->controllerName,
            '{%modelName%}'         => $this->modelName,
            '{%buildForm%}'         => $this->arrayToString($buildForm),
            '{%buildTable%}'        => $this->arrayToString($buildTable),
            '{%tableName%}'         => '',
            '{%validateClass%}'     => $this->input->getOption('validate'),
            '{%addScene%}'          => $this->input->getOption('addscene'),
            '{%editScene%}'         => $this->input->getOption('editscene'),
            '{%dataRight%}'         => $dataRight ? "'{$dataRight}'" : 'false',
            '{%dataRightField%}'    => $dataRightField ? "'{$dataRightField}'" : 'admin_id',
            '{%pk%}'                => $this->PK ?: '*',
            '{%filterField%}'       => $this->filterFields ? implode(',', $this->filterFields) : '',
            '{%with%}'              => $with ? '->with('.$this->arrayToString($with).')' : '',
        ];

        $content = str_replace(array_keys($assign), array_values($assign), $stub);
        $content = str_replace(["'%}", "{%'"], ['', ''], $content);

        file_put_contents($this->controllerFile, $content);
        
        return true;
    }

    /**
     * 生成逻辑文件
     *
     * @return true
     */
    protected function buildLogic()
    {
        $name = $this->input->getOption('logic');
        $stub = $this->getStub('logic');
        $file = $this->getRootPath() . 'logic' .DS . $name . '.php';

        if (!$this->input->hasOption('force')) {
            if (is_file($file)) {
                $this->output->info("Are you sure you want to override the logic file? Type 'yes' to override(default 'no'):");
                $stdin = fgets(defined('STDIN') ? STDIN : fopen('php://stdin', 'r'));
                if (trim($stdin) != 'yes') {
                    return;
                }
            }
        }

        $assign = [
            '{%namespace%}'     => $this->getNamespace(),
            '{%classTitle%}'    => $this->tableInfo['Comment'],
            '{%className%}'     => $name,
        ];

        $content = str_replace(array_keys($assign), array_values($assign), $stub);
    
        file_put_contents($file, $content);
        
        return true;
    }

    /**
     * 生成服务文件
     *
     * @return true
     */
    protected function buildService()
    {
        $name = $this->input->getOption('service');
        $stub = $this->getStub('service');
        $file = $this->getRootPath() . 'service' .DS . $name . '.php';
        
        if (!$this->input->hasOption('force')) {
            if (is_file($file)) {
                $this->output->info("Are you sure you want to override the service file? Type 'yes' to override(default 'no'):");
                $stdin = fgets(defined('STDIN') ? STDIN : fopen('php://stdin', 'r'));
                if (trim($stdin) != 'yes') {
                    return;
                }
            }
        }

        $assign = [
            '{%namespace%}'     => $this->getNamespace(),
            '{%classTitle%}'    => $this->tableInfo['Comment'],
            '{%className%}'     => $name,
        ];

        $content = str_replace(array_keys($assign), array_values($assign), $stub);
    
        file_put_contents($file, $content);
        
        return true;
    }

    /**
     * 生成验证器文件
     *
     * @return true
     */
    protected function buildValidate()
    {
        $name = $this->input->getOption('validate');
        $stub = $this->getStub('validate');
        $file = $this->getRootPath() . 'validate' .DS . $name . '.php';
        
        if (!$this->input->hasOption('force')) {
            if (is_file($file)) {
                $this->output->info("Are you sure you want to override the validate file? Type 'yes' to override(default 'no'):");
                $stdin = fgets(defined('STDIN') ? STDIN : fopen('php://stdin', 'r'));
                if (trim($stdin) != 'yes') {
                    return;
                }
            }
        }

        $assign = [
            '{%namespace%}'     => $this->getNamespace(),
            '{%classTitle%}'    => $this->tableInfo['Comment'],
            '{%className%}'     => $name,
        ];

        $content = str_replace(array_keys($assign), array_values($assign), $stub);
    
        file_put_contents($file, $content);
        
        return true;
    }

    /**
     * 生成表格
     *
     * @param array $items 数据列
     * @param array $filter 筛选
     * @param array $toolbar 工具栏
     * @return array
     */
    protected function buildTable($cols, $filter)
    {
        $func = $this->appType == 'plugins' ? 'plugins_url' : 'url';
        $cols[] = [
            'button' => [
                [
                    'title' => '编辑',
                    'url' => '%}'.$func.'("edit"){%',
                    'class' => 'layui-badge layui-bg-blue migu-iframe',
                ],
                [
                    'title' => '删除',
                    'url' => '%}'.$func.'("del"){%',
                    'class' => 'layui-badge layui-bg-danger migu-tr-del',
                ],
            ],
        ];
        
        $data['config']['page'] = $this->input->getOption('page') ? true : false;
        $data['config']['cols'] = $cols;
        $data['toolbar'] = [
            [
                'title' => '新增',
                'url' => '%}'.$func.'("add"){%',
                'class' => 'layui-btn layui-btn-normal layui-btn-sm migu-iframe',
            ],
            [
                'title' => '批量删除',
                'url' => '%}'.$func.'("del"){%',
                'class' => 'layui-btn layui-btn-danger layui-btn-sm migu-table-ajax',
            ],
        ];
        
        if ($filter) {
            $data['filter']['action'] = '%}'.$func.'(){%';
            $data['filter']['items'] = $filter;
        }

        return $data;
    }

    /**
     * 生成表单
     *
     * @param array $items 表单项
     * @return array
     */
    protected function buildForm($items)
    {
        $data['cancelBtn'] = true;
        $data['items'] = $items;
        $data['action'] = $this->appType == 'plugins' ? '%}plugins_url(){%' : '%}url(){%';
        return $data;
    }

    /**
     * 数组格式化为字符串
     *
     * @param array $arr 需要格式化的数组
     * @return void
     */
    protected function arrayToString($arr)
    {
        $str    = var_export($arr, TRUE);
        $str    = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $str);
        $array  = preg_split("/\r\n|\n|\r/", $str);
        $array  = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [NULL, ']$1', ' => ['], $array);
        $str    = join(PHP_EOL, array_filter(["["] + $array));
        $str    = preg_replace("/[0-9]+\ => /", "", $str);
        return $str;
    }
    
    // 获取模块或插件的根目录
    protected function getRootPath()
    {
        if ($this->appType == 'plugins') {
            return Env::get('root_path') . 'plugins' . DS . $this->appName . DS;
        } else {
            return Env::get('app_path') . $this->appName . DS;
        }
    }

    // 获取命名空间
    protected function getNamespace()
    {
        return ($this->appType == 'module' ? 'app' : 'plugins').'\\'.$this->appName;
    }

    // 获取母版
    protected function getStub($name)
    {
        return file_get_contents(__DIR__ . DS . 'Crud' . DS . 'stubs' . DS . $name . '.stub');
    }
}
