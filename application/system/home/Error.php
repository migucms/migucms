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

namespace app\system\home;

use app\common\controller\Common;
use app\system\model\SystemUser as UserModel;

class Error extends Common
{
    public function _empty()
    {
        $contr = strtolower($this->request->controller());
        if ($contr == 'jump') {//【后台专用】跳转至目标URL
            $url = urldecode($this->request->param('url'));
    
            if (!(new UserModel)->isLogin()) {
                return $this->error('无操作权限');
            }

            if (stripos($url, 'http') === false) {
                return $this->error('URL地址不合法');
            }
    
            return $this->success('正在跳转至目标网站', $url);
        }

        return $this->error('禁止访问');
    }
}
