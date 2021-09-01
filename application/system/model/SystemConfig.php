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

namespace app\system\model;
use think\facade\Cache;

/**
 * 系统配置模型
 * @package app\system\model
 */
class SystemConfig extends SystemBase
{
    /**
     * 获取系统配置信息
     * @param  string $name 配置名
     * @param  bool $update 是否更新缓存
     * @author Author: btc
     * @return mixed
     */
    public static function getConfig($name = '', $update = false)
    {
        $result = Cache::get('sys_config');
        if ($result === false || $update == true) {
            $configs = self::column('value,type,group', 'name');
            $result = [];
            foreach ($configs as $config) {
                switch ($config['type']) {
                    case 'array':
                    case 'checkbox':
                        if ($config['name'] == 'config_group') {
                            $v = parse_attr($config['value']);
                            if (!empty($config['value'])) {
                                $result[$config['group']][$config['name']] = array_merge(config('mg_system.config_group'), $v);
                            } else {
                                $result[$config['group']][$config['name']] = config('mg_system.config_group');
                            }
                        } else {
                            $result[$config['group']][$config['name']] = parse_attr($config['value']);
                        }
                        break;
                    default:
                        $result[$config['group']][$config['name']] = $config['value'];
                        break;
                }
            }
            Cache::tag('hs_config')->set('sys_config', $result);
        }
        return $name != '' ? $result[$name] : $result;
    }

    /**
     * 删除配置
     * @param string|array $id 节点ID
     * @author Author: btc
     * @return bool
     */
    public function del($ids = '') {
        if (is_array($ids)) {
            $error = '';
            foreach ($ids as $k => $v) {
                $map = [];
                $map['id'] = $v;
                $row = self::where($map)->find();
                if ($row['system'] == 1) {
                    $error .= '['.$row['title'].']为系统配置，禁止删除！<br>';
                    continue;
                }
                self::where($map)->delete();
            }
            if ($error) {
                $this->error = $error;
                return false;
            }
            return true;
        }
        $this->error = '参数传递错误';
        return false;
    }
}
