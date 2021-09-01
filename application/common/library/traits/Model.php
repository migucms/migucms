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

namespace app\common\library\traits;

trait Model
{
    /**
     * 递归添加子ID
     * @param int $id 子ID
     * @param int $pid 父ID
     * @param string $field 存放子级ID的字段名
     * @return string
     */
    public function addChilds($id, $pid = 0, $field = 'childs')
    {

        if ($pid == 0) {

            $row = self::where('id', $id)->find();
            self::where('id', $id)->setField($field, $id);

        } else {

            $row = self::where('id', $pid)->find();
            self::where('id', $pid)->setField($field, $row[$field].','.$id);

        }

        if ($row['pid'] > 0) {

            self::addChilds($id, $row['pid'], $field);

        }

    }

    /**
     * 递归删除子ID
     * @param int $id 子ID
     * @param int $pid 父ID
     * @param string $field 存放子级ID的字段名
     * @return string
     */
    public function delChilds($id, $pid = 0, $field = 'childs')
    {

        if (!$pid) return true;

        $row = self::where('id', $pid)->find();

        $childs = explode(',', $row[$field]);
        $childs = implode(',', array_diff($childs, [$id]));

        self::where('id', $pid)->setField($field, $childs);

        if ($row['pid'] > 0) {

            self::delChilds($id, $row['pid'], $field);

        }

    }
}