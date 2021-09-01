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

use app\common\controller\Common;
use app\system\model\SystemUser as UserModel;
use think\captcha\Captcha;

/**
 * 后台公共控制器
 * @package app\system\admin
 */
class Publics extends Common
{
    /**
     * 登陆页面
     * @author Author: btc
     * @return mixed
     */
    public function index()
    {
        $model = new UserModel;
        $loginError = (int)session('admin_login_error');
        
        if ($this->request->isPost()) {
            $captchaObj = new Captcha();
            $username   = $this->request->post('username/s');
            $password   = $this->request->post('password/s');
            $captcha    = $this->request->post('captcha/s');
            $data       = [];

            if ($loginError >= 3) {

                if (empty($captcha)) {
                    return $this->error('请输入验证码');
                }

                if (!captcha_check($captcha)) {
                    return $this->error('验证码错误');
                }
            }
            
            if (!$model->login($username, $password)) {

                $loginError = ($loginError+1);
                session('admin_login_error', $loginError);

                $data['token'] = $this->request->token();
                $data['captcha'] = $loginError >= 3 ? captcha_src() : '';

                return $this->error($model->getError(), url('index'), $data);

            }

            session('admin_login_error', 0);
            cookie('migu_iframe', 0);
            cookie('migu_menu_layout', 1);
            return $this->success('登陆成功，页面跳转中...', url('index/index'));

        }

        if ($model->isLogin()) {
            $this->redirect(url('index/index', '', true, true));
        }

        $this->view->engine->layout(false);
        
        $this->assign('loginError', $loginError);

        return $this->fetch();
    }

    /**
     * 退出登陆
     * @author Author: btc
     * @return mixed
     */
    public function logout(){
        model('SystemUser')->logout();
        $this->redirect(ROOT_DIR);
    }


    /**
     * 图标选择
     * @author Author: btc
     * @return mixed
     */
    public function icon() {
        return $this->fetch();
    }

    /**
     * 解锁屏幕
     * @author Author: btc
     * @return mixed
     */
    public function unlocked()
    {
        $_pwd = $this->request->post('password/s');
        $model = model('SystemUser');
        $login = $model->isLogin();
        
        if (!$login) {
            return $this->error('登录信息失效，请重新登录！');
        }

        $password = $model->where('id', $login['uid'])->value('password');
        if (!$password) {
            return $this->error('登录异常，请重新登录！');
        }

        if (!password_verify($_pwd, $password)) {
            return $this->error('密码错误，请重新输入！');
        }

        return $this->success('解锁成功');
    }

}
