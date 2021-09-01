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

use app\system\model\SystemMenu as MenuModel;
use Env;
use migu\Tree;

/**
 * 菜单控制器
 * @package app\system\admin
 */
class Menu extends Admin
{
    protected $miguTable = 'SystemMenu';
    protected $miguNoAuth = ['getMenu'];

    /**
     * 菜单管理
     * @author Author: btc
     * @return mixed
     */
    public function index()
    {
        
        $menus = MenuModel::where('title', '<>', '')->order('sort asc')->field('id,title,module,pid,icon,url,sort,status')->select()->toArray();


        $assign['menus'] = Tree::toTree($menus);

        $this->assign($assign);
        return $this->fetch();
    }

    /**
     * 添加菜单
     * @author Author: btc
     * @return mixed
     */
    public function add($pid = '', $mod = '')
    {
        if ($this->request->isPost()) {
            $url = $this->request->post('url');
            $model = new MenuModel();

            $result = $model->storage();
            if (!$result) {
                return $this->error($model->getError());
            }

            // 将最新的菜单保存到模块下面
            if (strtolower($url) != 'system/plugins/run') {
                $pid = $model->getParents($result->id);
                $model->export($pid);
            } else {
                $model->export($pid);
            }

            return $this->success('保存成功', '');

        }

        $this->assign('menuOptions', self::menuOption($pid));
        $this->assign('miguTabType', 0);
        return $this->fetch('form');
    }

    /**
     * 修改菜单
     * @author Author: btc
     * @return mixed
     */
    public function edit()
    {
        $id = get_num();

        $row = MenuModel::where('id', $id)->find();

        if ($this->request->isPost()) {

            $url = $this->request->post('url');
            $model = new MenuModel();

            if (!$model->storage()) {
                return $this->error($model->getError());
            }

            if (strtolower($url) != 'system/plugins/run') {
                $pid = $model->getParents($id);
            } else {
                $pid = $model->getPluginsParents($row['module'], $id);
            }

            $model->export($pid);

            return $this->success('保存成功', '');
        }

        // admin模块 只允许超级管理员在开发模式下修改
        if ($row['module'] == 'admin' && (ADMIN_ID != 1 || config('sys.app_debug') == 0)) {
            return $this->error('禁止修改系统模块！');
        }

        // 多语言
        if (config('sys.multi_language') == 1) {
            $row['title'] = $row['lang']['title'];
        }
        
        $this->assign('formData', $row);
        $this->assign('menuOptions', self::menuOption($row['pid']));

        return $this->fetch('form');
    }

    /**
     * 下拉菜单
     * @author Author: btc
     * @return mixed
     */
    private function menuOption($id = '', $str = '')
    {
        $menus = MenuModel::getAllChild();

        foreach ($menus as $v) {

            if ($id == $v['id']) {
                $str .= '<option level="1" value="'.$v['id'].'" selected>['.$v['module'].']'.$v['title'].'</option>';
            } else {
                $str .= '<option level="1" value="'.$v['id'].'">['.$v['module'].']'.$v['title'].'</option>';
            }

            if ($v['childs']) {

                foreach ($v['childs'] as $vv) {

                    if ($id == $vv['id']) {
                        $str .= '<option level="2" value="'.$vv['id'].'" selected>&nbsp;&nbsp;['.$vv['module'].']'.$vv['title'].'</option>';
                    } else {
                        $str .= '<option level="2" value="'.$vv['id'].'">&nbsp;&nbsp;['.$vv['module'].']'.$vv['title'].'</option>';
                    }

                    if ($vv['childs']) {

                        foreach ($vv['childs'] as $vvv) {
                            if ($id == $vvv['id']) {
                                $str .= '<option level="3" value="'.$vvv['id'].'" selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;['.$vvv['module'].']'.$vvv['title'].'</option>';
                            } else {
                                $str .= '<option level="3" value="'.$vvv['id'].'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;['.$vvv['module'].']'.$vvv['title'].'</option>';
                            }
                        }

                    }

                }

            }
        }
        return $str;
    }

    /**
     * 删除菜单
     * @author Author: btc
     * @return mixed
     */
    public function del()
    {
        $id = $this->request->param('id/a');
        $model = new MenuModel();

        if ($model->del($id)) {
            return $this->success('删除成功');
        }

        return $this->error($model->getError());
    }
    
    /**
     * 添加快捷菜单
     * @author Author: btc
     * @return string
     */
    public function quick()
    {
        $id = $this->request->param('id/d');
        if (!$id) {
            return $this->error('参数传递错误');
        }

        $map        = [];
        $map['id']  = $id;
        
        $row = MenuModel::where($map)->find()->toArray();
        if (!$row) {
            return $this->error('您添加的菜单不存在');
        }
        
        unset($row['id'], $map['id']);

        $map['url']     = $row['url'];
        $map['param']   = $row['param'];
        $map['uid']     = ADMIN_ID;
        $row['pid']     = $map['pid'] = 4;

        if (MenuModel::where($map)->find()) {
            return $this->error('您已添加过此快捷菜单');
        }

        $row['uid']     = ADMIN_ID;
        $row['debug']   = 0;
        $row['system']  = 0;
        $row['ctime']   = time();

        $model = new MenuModel();

        if ($model->storage($row) === false) {
            return $this->error('快捷菜单添加失败');
        }

        return $this->success('快捷菜单添加成功');
    }

    /**
     * 获取管理菜单
     *
     * @return void
     */
    public function getMenu()
    {
        return json(MenuModel::getAdminMenu());
    }
}
