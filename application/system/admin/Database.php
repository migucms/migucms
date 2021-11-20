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

use migu\Dir;
use migu\Database as dbOper;
use think\Db;
use Env;

/**
 * 数据库管理控制器
 * @package app\system\admin
 */
class Database extends Admin
{
    /**
     * 初始化方法
     */
    protected function initialize()
    {
        parent::initialize();
        $this->backupPath = Env::get('root_path').'backup/'.trim(config('databases.backup_path'), '/').'/';
        $tabData['menu'] = [
            [
                'title' => '备份数据库',
                'url'   => 'system/database/index?group=export',
            ],
            [
                'title' => '恢复数据库',
                'url'   => 'system/database/index?group=import',
            ],
        ];

        $this->miguTabData = $tabData;
    }

    /**
     * 数据库管理
     * @author Author: btc
     * @return mixed
     */
    public function index($group = 'export')
    {
        if ($this->request->isAjax()) {

            $group = $this->request->param('group');
            $data = [];

            if ($group == 'export') {
                $tables = Db::query("SHOW TABLE STATUS");

                foreach ($tables as $k => &$v) {
                    $v['id'] = $v['Name'];
                    $v['Data_length'] = ($v['Data_length'] / 1024).' KB';
                    $v['Data_free'] = ($v['Data_free'] / 1024).' KB';
                }

                $data['data'] = $tables;
                $data['code'] = 0;

            } else {

                //列出备份文件列表
                if (!is_dir($this->backupPath)) {
                    Dir::create($this->backupPath);
                }

                $flag = \FilesystemIterator::KEY_AS_FILENAME;
                $glob = new \FilesystemIterator($this->backupPath,  $flag);

                $dataList = [];

                foreach ($glob as $name => $file) {

                    if(preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql(?:\.gz)?$/', $name)) {
                        $name = sscanf($name, '%4s%2s%2s-%2s%2s%2s-%d');
                        $date = "{$name[0]}-{$name[1]}-{$name[2]}";
                        $time = "{$name[3]}:{$name[4]}:{$name[5]}";
                        $part = $name[6];

                        if(isset($dataList["{$date} {$time}"])) {

                            $info           = $dataList["{$date} {$time}"];
                            $info['part']   = max($info['part'], $part);
                            $info['size']   = ceil(($info['size'] + $file->getSize()) / 1024 ) . ' KB';

                        } else {

                            $info['part']   = $part;
                            $info['size']   = ceil($file->getSize()/1024) . ' KB';

                        }

                        $info['time']       = "{$date} {$time}";
                        $time               = strtotime("{$date} {$time}");
                        $extension          = strtoupper($file->getExtension());
                        $info['compress']   = ($extension === 'SQL') ? '无' : $extension;
                        $info['name']       = date('Ymd-His', $time);
                        $info['id']         = $time;

                        $dataList["{$date} {$time}"] = $info;

                    }
                    sort($dataList);
                }

                $data['data'] = $dataList;
                $data['code'] = 0;
            }

            return json($data);
        }

        $tabData            = $this->miguTabData;
        $tabData['current'] = url('?group='.$group);
        
        $assign = [];
        $assign['miguTabType'] = 3;
        $assign['miguTabData'] = $tabData;
        $assign['buildTable']['config']['url'] = url('?group='.$group);
        $assign['buildTable']['config']['page'] = false;

        if ($group == 'export') {

            // 工具栏
            $assign['buildTable']['toolbar'] = [
                [
                    'title' => '优化',
                    'url' => url('optimize'),
                    'class' => 'layui-btn layui-btn-sm migu-table-ajax',
                ],
                [
                    'title' => '备份',
                    'url' => url('export'),
                    'class' => 'layui-btn layui-btn-normal layui-btn-sm migu-table-ajax',
                ],
                [
                    'title' => '修复',
                    'url' => url('repair'),
                    'class' => 'layui-btn layui-btn-warm layui-btn-sm migu-table-ajax',
                ],
            ];

            // 表格头
            $assign['buildTable']['config']['cols'] = [
                [
                    'type' => 'checkbox',
                ],
                [
                    'field' => 'Name',
                    'title' => '表名',
                ],
                [
                    'field' => 'Rows',
                    'title' => '数据量',
                    'width' => 90,
                ],
                [
                    'field' => 'Data_length',
                    'title' => '大小',
                    'width' => 100,
                ],
                [
                    'field' => 'Data_free',
                    'title' => '冗余',
                    'width' => 100,
                ],
                [
                    'field' => 'Comment',
                    'title' => '备注',
                ],
                [
                    'title' => '操作',
                    'width' => 180,
                    'button' => [
                        [
                            'title' => '优化',
                            'url' => url('optimize'),
                            'class' => 'layui-btn layui-btn-xs migu-ajax',
                        ],
                        [
                            'title' => '备份',
                            'url' => url('export'),
                            'class' => 'layui-btn layui-btn-normal layui-btn-xs migu-ajax',
                        ],
                        [
                            'title' => '修复',
                            'url' => url('repair'),
                            'class' => 'layui-btn layui-btn-warm layui-btn-xs migu-ajax',
                        ],
                    ],
                ],
            ];
        } else {

            // 工具栏
            $assign['buildTable']['toolbar'] = [
                [
                    'title' => '恢复',
                    'url' => url('import'),
                    'class' => 'layui-btn layui-btn-normal layui-btn-sm migu-table-ajax',
                ],
                [
                    'title' => '删除',
                    'url' => url('del'),
                    'class' => 'layui-btn layui-btn-danger layui-btn-sm migu-table-ajax',
                ],
            ];

            // 表格头
            $assign['buildTable']['config']['cols'] = [
                [
                    'type' => 'checkbox',
                ],
                [
                    'field' => 'name',
                    'title' => '备份名称',
                ],
                [
                    'field' => 'part',
                    'title' => '备份卷数',
                ],
                [
                    'field' => 'compress',
                    'title' => '备份压缩',
                ],
                [
                    'field' => 'size',
                    'title' => '备份大小',
                ],
                [
                    'field' => 'time',
                    'title' => '备份时间',
                ],
                [
                    'title' => '操作',
                    'width' => 180,
                    'button' => [
                        [
                            'title' => '恢复',
                            'url' => url('import'),
                            'class' => 'layui-btn layui-btn-normal layui-btn-xs migu-ajax',
                        ],
                        [
                            'title' => '删除',
                            'url' => url('del'),
                            'class' => 'layui-btn layui-btn-danger layui-btn-xs migu-tr-del',
                        ],
                    ],
                ],
            ];
        }

        return $this->assign($assign)->fetch();
    }

    /**
     * 备份数据库 [参考原作者 麦当苗儿 <zuojiazi@vip.qq.com>]
     * @param string|array $id 表名
     * @param integer $start 起始行数
     * @author Author: btc
     * @return mixed
     */
    public function export($id = '', $start = 0)
    {

        if ($this->request->isAjax()) {

            if (empty($id)) {
                return $this->error('请选择您要备份的数据表');
            }

            if (!is_array($id)) {
                $tables[] = $id;
            } else {
                $tables = $id;
            }
            
            //读取备份配置
            $config = array(
                'path'     => $this->backupPath,
                'part'     => config('databases.part_size'),
                'compress' => config('databases.compress'),
                'level'    => config('databases.compress_level'),
            );

            //检查是否有正在执行的任务
            $lock = "{$config['path']}backup.lock";

            if(is_file($lock)){
                return $this->error('检测到有一个备份任务正在执行，请稍后再试');
            } else {

                if (!is_dir($config['path'])) {
                    Dir::create($config['path'], 0755, true);
                }

                //创建锁文件
                file_put_contents($lock, $this->request->time());
            }

            //生成备份文件信息
            $file = [
                'name' => date('Ymd-His', $this->request->time()),
                'part' => 1,
            ];

            // 创建备份文件
            $database = new dbOper($file, $config);

            if($database->create() !== false) {

                // 备份指定表
                foreach ($tables as $table) {
                    $start = $database->backup($table, $start);
                    while (0 !== $start) {
                        if (false === $start) {
                            return $this->error('备份出错');
                        }
                        $start = $database->backup($table, $start[0]);
                    }
                }

                // 备份完成，删除锁定文件
                unlink($lock);
            }

            return $this->success('备份完成');
        }
        return $this->error('备份出错');
    }

    /**
     * 恢复数据库 [参考原作者 麦当苗儿 <zuojiazi@vip.qq.com>]
     * @param string|array $ids 表名
     * @param integer $start 起始行数
     * @author Author: btc
     * @return mixed
     */
    public function import($id = '')
    {
        if (empty($id)) {
            return $this->error('请选择您要恢复的备份文件');
        }

        $name  = date('Ymd-His', $id) . '-*.sql*';
        $path  = $this->backupPath.$name;
        $files = glob($path);
        $list  = array();

        foreach($files as $name){
            $basename = basename($name);
            $match    = sscanf($basename, '%4s%2s%2s-%2s%2s%2s-%d');
            $gz       = preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql.gz$/', $basename);
            $list[$match[6]] = array($match[6], $name, $gz);
        }

        ksort($list);

        // 检测文件正确性
        $last = end($list);

        if(count($list) === $last[0]) {

            foreach ($list as $item) {

                $config = [
                    'path'     => $this->backupPath,
                    'compress' => $item[2]
                ];

                $database = new dbOper($item, $config);
                $start = $database->import(0);

                // 导入所有数据
                while (0 !== $start) {

                    if (false === $start) {
                        return $this->error('数据恢复出错');
                    }

                    $start = $database->import($start[0]);
                }
            }

            return $this->success('数据恢复完成');
        }

        return $this->error('备份文件可能已经损坏，请检查');
    }

    /**
     * 优化数据表
     * @author Author: btc
     * @return mixed
     */
    public function optimize($id = '')
    {
        if (empty($id)) {
            return $this->error('请选择您要优化的数据表');
        }

        if (!is_array($id)) {
            $table[] = $id;
        } else {
            $table = $id;
        }

        $tables = implode('`,`', $table);
        $res = Db::query("OPTIMIZE TABLE `{$tables}`");
        if ($res) {
            return $this->success('数据表优化完成');
        }

        return $this->error('数据表优化失败');
    }

    /**
     * 修复数据表
     * @author Author: btc
     * @return mixed
     */
    public function repair($id = '')
    {
        if (empty($id)) {
            return $this->error('请选择您要修复的数据表');
        }

        if (!is_array($id)) {
            $table[] = $id;
        } else {
            $table = $id;
        }

        $tables = implode('`,`', $table);
        $res = Db::query("REPAIR TABLE `{$tables}`");

        if ($res) {
            return $this->success('数据表修复完成');
        }

        return $this->error('数据表修复失败');
    }

    /**
     * 删除备份
     * @author Author: btc
     * @return mixed
     */
    public function del($id = '')
    {
        if (empty($id)) {
            return $this->error('请选择您要删除的备份文件');
        }

        if(!is_array($id)) {
            $ids[] = $id;
        } else {
            $ids = $id;
        }

        foreach($ids as $v) {
            $name  = date('Ymd-His', $v) . '-*.sql*';
            $path = $this->backupPath.$name;
            array_map("unlink", glob($path));
    
            if(count(glob($path)) && glob($path)){
                return $this->error('备份文件删除失败，请检查权限');
            }
        }
        
        return $this->success('备份文件删除成功');
    }
    /**
     * 执行sql
     * @author Author: btc
     * @return mixed
     */
    public function sql(){
        if (!config('databases.sqlswitch')) {
            return $this->error('执行SQL关闭');
        }
        if ($this->request->isPost()) {
            $sql = $this->request->param('sql');
            $sql = trim($sql);

            if(!empty($sql)){
                $sql = str_replace('{pre}',config('database.prefix'),$sql);
                //查询语句返回结果集
                if(strtolower(substr($sql,0,6))=="select"){
                    
                }else{
                    Db::execute($sql);
                }
            }
            return $this->success('执行成功');
        }
        
        $assign['buildForm']['cancelBtn'] = true;
        $assign['buildForm']['items'] = [
            [
                'type'  => 'txt',
                'title' => '文本框',
                'value' => "常用语句对照：</br>
1.查询数据 SELECT * FROM {pre}video_vod 查询所有数据
SELECT * FROM {pre}video_vod WHERE vod_id=1000 查询指定ID数据</br>
2.删除数据 DELETE FROM {pre}video_vod 删除所有数据
DELETE FROM {pre}video_vod WHERE vod_id=1000 删除指定的第几条数据
DELETE FROM {pre}video_vod WHERE vod_actor LIKE '%刘德华%' vod_actor\"刘德华\"的数据</br>
3.修改数据 UPDATE {pre}video_vod SET vod_hits=1 将所有vod_hits字段里的值修改成\"1\"
UPDATE {pre}video_vod SET vod_hits=1 WHERE vod_id=1000 指定的第几条数据把vod_hits字段里的值修改成\"1\"</br>
4.替换图片地址 UPDATE {pre}video_vod SET vod_pic=REPLACE(vod_pic, '原始字符串', '替换成其他字符串')</br>
5.清空数据ID重新从1开始 TRUNCATE {pre}video_vod",
            ],
            [
                'type' => 'textarea',
                'title' => '执行语句',
                'name' => 'sql',
            ],
        ];
        return $this->fetch();
        
    }
    public function rep()
    {
        if (!config('databases.sqlswitch')) {
            return $this->error('执行SQL关闭');
        }
        if ($this->request->isPost()) {
            $param = request()->post();
            $table = $param['table'];
            $field = $param['field'];
            $findstr = $param['findstr'];
            $tostr = $param['tostr'];
            $where = $param['where'];
            if(!empty($table) && !empty($field) && !empty($findstr) && !empty($tostr) && !empty($where)){
                $sql = "UPDATE ".$table." set ".$field."=Replace(".$field.",'".$findstr."','".$tostr."') where ".$where;
                Db::execute($sql);
                return $this->success('执行成功');
            }
            if(!empty($table) && !empty($field) && !empty($findstr) && !empty($tostr)){
                $sql = "UPDATE ".$table." set ".$field."=Replace(".$field.",'".$findstr."','".$tostr."') where 1=1";
                Db::execute($sql);
                return $this->success('执行成功');
            }

            return $this->error('参数错误');
        }
        $list = Db::query("SHOW TABLE STATUS");
        $this->assign('list',$list);
        return $this->fetch();
    }
    public function columns()
    {
        $param = input();
        $table = $param['table'];
        if(!empty($table)){
            $list = Db::query('SHOW COLUMNS FROM '.$table);
            $this->success('获取成功',null, $list);
        }
        $this->error('参数错误');
    }
}
