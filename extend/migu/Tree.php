<?php
// +----------------------------------------------------------------------
// | 咪咕CMS承诺基础框架永久免费开源，您可用于学习，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 http://www.migucms.com
// +----------------------------------------------------------------------
// | 咪咕CMS承诺基础框架永久免费开源，您可用于学习，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: btc
// +----------------------------------------------------------------------
namespace migu;

/**
* 通用树型类
*/
class Tree {
    
    /**
     * 配置参数
     * @var array
     */
    protected static $config = [
        'id'                => 'id',        // id名称
        'pid'               => 'pid',       // pid名称
        'child'             => 'childs',    // 子元素键名
        'name'              => 'name',      // 下拉列表的选项名
        'icon'              => '├',         // 下拉列表的图标
        'placeholder'       => '&nbsp;',    // 下拉列表的占位符
        'placeholder_number'=> 3,           // 下拉列表的占位符数量
    ];

    /**
     * 架构函数
     * @param array $config
     */
    public function __construct($config = [])
    {
        self::$config = array_merge(self::$config, $config);
    }

    /**
     * 配置参数
     * @param  array $config
     * @return object
     */
    public static function config($config = [])
    {
        if (!empty($config)) {

            return self::$config = array_merge(self::$config, $config);
            
        }
    }

    /**
     * 将数据集格式化成树形结构
     * @param array $data 原始数据
     * @param int $pid 父级id
     * @param int $limitLevel  限制返回几层，0为不限制
     * @param int $currentLevel 当前层数
     * @return array
     */
    public static function toTree($data = [], $pid = 0, $limitLevel = 0, $currentLevel = 0)
    {
        $trees = [];
        $data = array_values($data);
        
        foreach ($data as $k => $v) {
            
            if ($v[self::$config['pid']] == $pid) {

                if ($limitLevel > 0 && $limitLevel == $currentLevel) {

                    return $trees;
                
                }
                
                unset($data[$k]);

                $childs = self::toTree($data, $v[self::$config['id']], $limitLevel, ($currentLevel+1));

                if (!empty($childs)) {

                    $v[self::$config['child']] = $childs;

                }

                $trees[] = $v;
            }
        }
        
        return $trees;
    }

    /**
     * 将树形结构数据转为单层结构
     * @param array $tree 树形结构数据
     * @param array $data 结果集
     * @param int $level  当前层级
     * @return array
     */
    public static function treeToList($tree, &$data = [], $level = 0)
    {
        foreach($tree as $k => $v) {
            $childs = '';
            $v['level'] = $level;
            $v['icon'] = self::buildIcon($level);
            if (isset($v[self::$config['child']])) {
                $childs = $v[self::$config['child']];
                unset($v[self::$config['child']]);
            }
            $data[] = $v;
            if ($childs) {
                self::treeToList($childs, $data, $level+1);
            }
        }

        return $data;
    }

    private static function buildIcon($level) {

        $icon   = '';

        if ($level > 0) {

            for ($i=0; $i < $level; $i++) {

                for($j = 0; $j < self::$config['placeholder_number']; $j++) {

                    $icon .= self::$config['placeholder'];

                }

            }

            $icon .= self::$config['icon'].'&nbsp;';
        }

        return $icon;
    }

    /**
     * 将树形结构的数据转成下拉选择
     * @param array|object $data 原始数据
     * @param int $sid 选中ID
     * @param array $did 禁止选择
     * @param int $level 当前层数
     * @return array
     */
    public static function toOptions($data = [], $sid = 0, $did = [], $level = 0)
    {

        if (empty($data)) {

            return '';

        }

        $id     = self::$config['id'];
        $name   = self::$config['name'];
        $str    = '';
        $icon   = self::buildIcon($level);

        foreach ($data as $k => $v) {
            $data = json_encode($v);
            if ($sid == $v[$id]) {

                $str .= '<option value="'.$v[$id].'" migu-data=\''.$data.'\' selected>'.$icon.$v[$name].'</option>';

            } else if ($did && in_array($v[$id], $did)) {

                $str .= '<option value="'.$v[$id].'" disabled>'.$icon.$v[$name].'</option>';

            } else {

                $str .= '<option value="'.$v[$id].'" migu-data=\''.$data.'\'>'.$icon.$v[$name].'</option>';

            }

            if (isset($v['childs'])) {

                $str.= self::toOptions($v['childs'], $sid, $did, $level+1);

            }
        }

        return $str;
    }
}
