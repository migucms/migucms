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

use app\system\model\SystemConfig as ConfigModel;
use think\facade\Env;

/**
 * 配置管理控制器
 * @package app\system\admin
 */
class Config extends Admin
{
    protected $miguTable = 'SystemConfig';

    protected function initialize()
    {
        parent::initialize();
        !Env::get('app_debug') && $this->error('非开发模式禁止访问！');
    }

    public function index($group = 'base')
    {
        if ($this->request->isAjax()) {
            $where  = $data = [];
            $page   = $this->request->param('page/d', 1);
            $limit  = $this->request->param('limit/d', 15);
            $keyword= $this->request->param('keyword/s');

            if ($keyword) {
                if (preg_match("/^[A-Za-z0-9\-\_]+$/", $keyword)) {
                    $where[] = ['name', 'like', "%{$keyword}%"];
                } else {
                    $where[] = ['title', 'like', "%{$keyword}%"];
                }
            }

            $where[] = ['group', '=', $group];

            $data['data']   = ConfigModel::where($where)->page($page)->limit($limit)->order('sort,id')->select();
            $data['count']  = ConfigModel::where($where)->count('id');
            $data['code']   = 0;
            return json($data);
        }

        $tabData = [];

        foreach (config('sys.config_group') as $key => $value) {
            $arr                = [];
            $arr['title']       = $value;
            $arr['url']         = '?group='.$key;
            $tabData['menu'][]  = $arr;
        }
        
        $tabData['menu'][] = [
            'title' => '添加分组',
            'url'   => '#',
            'id'    => 'miguAddGroup',
        ];

        $tabData['current'] = url('?group='.$group);

        $this->assign('miguTabData', $tabData);
        $this->assign('miguTabType', 3);
        return $this->fetch();
    }

    /**
     * 添加配置
     * @author Author: btc
     * @return mixed
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            switch ($data['type']) {
                case 'switch':
                case 'radio':
                case 'checkbox':
                case 'select':
                    if (!$data['options']) {
                        return $this->error('请填写配置选项');
                    }
                    break;
                default:
                    break;
            }

            // 验证
            $result = $this->validate($data, 'SystemConfig');
            if($result !== true) {
                return $this->error($result);
            }

            if (!ConfigModel::create($data)) {
                return $this->error('添加失败');
            }

            // 更新配置缓存
            ConfigModel::getConfig('', true);
            return $this->success('添加成功');
        }
        return $this->fetch('form');
    }

    /**
     * 修改配置
     * @author Author: btc
     * @return mixed
     */
    public function edit($id = 0)
    {
        $row = ConfigModel::where('id', $id)->field('id,group,title,name,value,type,options,tips,status,system')->find();

        if ($row['system'] == 1) {
            return $this->error('禁止编辑此配置');
        }

        if ($this->request->isPost()) {
            $data = $this->request->post();

            // 验证
            $result = $this->validate($data, 'SystemConfig');

            if($result !== true) {
                return $this->error($result);
            }

            if (!ConfigModel::update($data)) {
                return $this->error('保存失败');
            }

            // 更新配置缓存
            ConfigModel::getConfig('', true);
            return $this->success('保存成功');
        }

        $row['tips'] = htmlspecialchars_decode($row['tips']);
        $row['value'] = htmlspecialchars_decode($row['value']);
        $this->assign('formData', $row);
        return $this->fetch('form');
    }

    /**
     * 删除配置
     * @author Author: btc
     * @return mixed
     */
    public function del()
    {
        $id = $this->request->param('id/a');
        $model = new ConfigModel();
        
        if ($model->del($id)) {
            return $this->success('删除成功');
        }
        // 更新配置缓存
        ConfigModel::getConfig('', true);
        return $this->error($model->getError());
    }

    /**
     * 删除配置分组
     * @author Author: btc
     * @return mixed
     */
    public function delGroup()
    {
        
        $group = $this->request->param('group/s');
        $sysGroup = config('mg_system.config_group');
        if (isset($sysGroup[$group])) {
            return $this->error('禁止删除系统分组');
        }

        $row = ConfigModel::where('name', 'config_group')->find();

        $arr = parse_attr($row['value']);

        if (isset($arr[$group])) {
            unset($arr[$group]);
        }

        $str = '';
        foreach($arr as $k => $v) {
            if (is_number($k)) continue;
            $str .= $k.':'.$v."\n";
        }
        
        $row->value = $str;
        $row->save();

        ConfigModel::where('group', $group)->where('system', 0)->delete();

        return $this->success('删除成功', url('index', ['group' => 'base']));
    }

    /**
     * 添加分组
     * @date   2019-01-24
     * @access public
     * @author Author: btc
     */
    public function addGroup()
    {
        if (!$this->request->isPost()) {
            return $this->error('请求异常');
        }

        $name = $this->request->param('name', '', 'strip_tags');

        $exp = explode(':', $name);

        if (count($exp) != 2) {
            return $this->error('格式错误（示例：user:用户配置）');
        }

        if (empty($exp[0]) || empty($exp[1])) {
            return $this->error('格式错误（示例：user:用户配置）');
        }

        $defConfig = config('sys.config_group');
        $disable = config('mg_system.config');

        if (isset($defConfig[$exp[0]]) || isset($disable[$exp[0]])) {
            return $this->error('别名已存在');
        }

        if (in_array($exp[1], $defConfig)) {
            return $this->error('标题已存在');
        }

        $result = ConfigModel::where('name', 'config_group')->where('group', 'sys')->find();

        $config = $result['value'];

        if (!empty($config)) {

            $config .= "\n".$name;

        } else {

            $config = $name;

        }

        $result->value = $config;

        if ($result->save() === false) {
            return $this->error('添加失败');
        }

        ConfigModel::getConfig('', true);

        return $this->success('添加成功', url('index', ['group' => $exp[0]]));
    
    }
}
