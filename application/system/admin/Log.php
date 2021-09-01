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

use app\system\model\SystemLog as LogModel;
use Env;
/**
 * 日志管理控制器
 * @package app\system\admin
 */
class Log extends Admin
{
    protected $miguTable = 'SystemLog';

    /**
     * 日志首页
     * @author Author: btc
     * @return mixed
     */
    public function index()
    {
        if ($this->request->isAjax()) {

            $where  = $data = [];
            $page   = $this->request->param('page/d', 1);
            $limit  = $this->request->param('limit/d', 15);
            $uid    = $this->request->param('uid/d');
            
            if ($uid) {
                $where['uid'] = $uid;
            }

            $data['data']   = LogModel::with(['hasUser' => function($query) {
                                                $query->field('id,nick');
                                            }])->where($where)
                                                ->page($page)
                                                ->order('count desc,mtime desc')
                                                ->limit($limit)
                                                ->select();

            $data['count']  = LogModel::where($where)->count('id');
            $data['code']   = 0;

            return json($data);

        }
        $assign = [];
        $assign['buildTable'] = [
            'config' => [
                'page' => true,
                'limit' => 20,
                'cols' => [
                    [
                        'type' => 'checkbox',
                    ],
                    [
                        'field' => 'uid',
                        'title' => '管理员',
                        'templet' => '<div><a href="'.url().'?uid={{ d.uid }}" class="migu-table-a-filter">{{ d.has_user.nick }}</a></div>',
                        'width' => 120,
                    ],
                    [
                        'field' => 'title',
                        'title' => '访问标题',
                        'width' => 150,
                    ],
                    [
                        'field' => 'url',
                        'title' => '访问地址',
                    ],
                    [
                        'field' => 'remark',
                        'title' => '备注说明',
                        'width' => 90,
                    ],
                    [
                        'field' => 'count',
                        'title' => '访问次数',
                        'width' => 90,
                    ],
                    [
                        'field' => 'ip',
                        'title' => '最近访问',
                        'width' => 120,
                    ],
                    [
                        'field' => 'mtime',
                        'title' => '最近访问',
                        'width' => 180,
                    ],
                ],
            ],
            'toolbar' => [
                [
                    'title' => '删除',
                    'url' => url('del'),
                    'class' => 'layui-btn layui-btn-warm layui-btn-sm migu-table-ajax',
                ],
                [
                    'title' => '清空',
                    'url' => url('clear'),
                    'class' => 'layui-btn layui-btn-danger layui-btn-sm migu-ajax',
                ],
            ],
        ];
        
        return $this->assign($assign)->fetch();
    }
    /**
     * 清空日志
     * @author Author: btc
     * @return mixed
     */
    public function clear()
    {
        if (!LogModel::where('id > 0')->delete()) {
            return $this->error('日志清空失败');
        }
        return $this->success('日志清空成功');
    }
}
