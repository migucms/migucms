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

use Env;
use migu\Dir;

/**
 * 后台默认首页控制器
 * @package app\system\admin
 */

class Index extends Admin
{
    protected $miguNoAuth =['index', 'welcome'];
    
    /**
     * 首页
     * @author Author: btc
     * @return mixed
     */
    public function index()
    {
        $homepage = $this->modelSystemUser->where('id', '=', ADMIN_ID)->value('homepage');
        if (cookie('migu_iframe')) {
            $this->view->engine->layout(false);
            $homepage = $homepage ?: 'system/index/welcome';
            return $this->assign('homepage', $homepage)->fetch('iframe');
        } else {

            if ($homepage && $homepage != 'system/index/index') {
                $this->redirect($homepage);
                exit;
            }
            return $this->fetch();
        }
    }
    /**
     * 自定义菜单
     * @author Author: btc
     * @return mixed
     */
    public function quickmenu(){
        if($this->request->isPost()){
            $quickmenu = $this->request->post('quickmenu');
            $quickmenu = str_replace(chr(10),'',$quickmenu);
            $menu_arr = explode(chr(13),$quickmenu);
            $menu_arr=var_export($menu_arr,true);
            $file = Env::get('config_path').'quickmenu.php';
            $str = "<?php\nreturn $menu_arr;\n";
            @chmod($file, 0755);
            @file_put_contents($file, $str);
            @chmod($file, 0555);
            return $this->success('保存成功!');
        }else{
            $config_menu = config()['quickmenu'];
            $quickmenu = array_values($config_menu);
            $quickmenu = join(chr(13),$quickmenu);
            $this->assign('quickmenu',$quickmenu);
            return $this->fetch();
        }
    }

    /**
     * 欢迎首页
     * @author Author: btc
     * @return mixed
     */
    public function welcome()
    {
        $this->assign('miguTabType', 0);
        return $this->fetch('index');
    }

    /**
     * 清理缓存
     * @author Author: btc
     * @return mixed
     */
    public function clear()
    {
        $path   = Env::get('runtime_path');
        $cache  = $this->request->param('cache/d', 0);
        $log    = $this->request->param('log/d', 0);
        $temp   = $this->request->param('temp/d', 0);

        if ($cache == 1) {
            Dir::delDir($path.'cache');
        }

        if ($temp == 1) {
            Dir::delDir($path.'temp');
        }

        if ($log == 1) {
            Dir::delDir($path.'log');
        }

        return $this->success('任务执行成功');
    }
}
