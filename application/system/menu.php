<?php
return [
  [
    'title' => '系统管理',
    'icon' => 'layui-icon layui-icon-set',
    'module' => 'system',
    'url' => 'system/system',
    'param' => '',
    'target' => '_self',
    'debug' => 0,
    'system' => 1,
    'nav' => 1,
    'sort' => 1,
    'childs' => [
      [
        'title' => '系统基础',
        'icon' => 'hs-icon hs-icon-gongneng',
        'module' => 'system',
        'url' => 'system/system',
        'param' => '',
        'target' => '_self',
        'debug' => 0,
        'system' => 1,
        'nav' => 1,
        'sort' => 1,
        'childs' => [
          [
            'title' => '系统设置',
            'icon' => 'hs-icon hs-icon-sys-set',
            'module' => 'system',
            'url' => 'system/system/index',
            'param' => '',
            'target' => '_self',
            'debug' => 0,
            'system' => 1,
            'nav' => 1,
            'sort' => 1,
            'childs' => [
              [
                'title' => '基础配置',
                'icon' => '',
                'module' => 'admin',
                'url' => 'system/system/index',
                'param' => 'group=base',
                'target' => '_self',
                'debug' => 0,
                'system' => 0,
                'nav' => 0,
                'sort' => 1,
              ],
              [
                'title' => '系统配置',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/system/index',
                'param' => 'group=sys',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 2,
              ],
              [
                'title' => '上传配置',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/system/index',
                'param' => 'group=upload',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 3,
              ],
              [
                'title' => '开发配置',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/system/index',
                'param' => 'group=develop',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 4,
              ],
              [
                'title' => '数据库配置',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/system/index',
                'param' => 'group=databases',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 5,
              ],
              [
                'title' => '开发示例配置',
                'icon' => 'layui-icon layui-icon-next',
                'module' => 'example',
                'url' => 'system/system/index',
                'param' => 'group=example',
                'target' => '_self',
                'debug' => 0,
                'system' => 0,
                'nav' => 0,
                'sort' => 100,
              ],
              [
                'title' => '云平台配置',
                'icon' => 'layui-icon layui-icon-next',
                'module' => 'cloud',
                'url' => 'system/system/index',
                'param' => 'group=cloud',
                'target' => '_self',
                'debug' => 0,
                'system' => 0,
                'nav' => 0,
                'sort' => 100,
              ],
            ],
          ],
          [
            'title' => '配置管理',
            'icon' => 'hs-icon hs-icon-set',
            'module' => 'system',
            'url' => 'system/config/index',
            'param' => '',
            'target' => '_self',
            'debug' => 1,
            'system' => 1,
            'nav' => 1,
            'sort' => 2,
            'childs' => [
              [
                'title' => '添加配置',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/config/add',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 1,
              ],
              [
                'title' => '修改配置',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/config/edit',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 2,
              ],
              [
                'title' => '删除配置',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/config/del',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 3,
              ],
              [
                'title' => '状态设置',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/config/status',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 4,
              ],
              [
                'title' => '排序设置',
                'icon' => '',
                'module' => 'admin',
                'url' => 'system/config/setField',
                'param' => 'field=sort',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 5,
              ],
            ],
          ],
          [
            'title' => '清空缓存',
            'icon' => '',
            'module' => 'system',
            'url' => 'system/index/clear',
            'param' => '',
            'target' => '_self',
            'debug' => 0,
            'system' => 1,
            'nav' => 0,
            'sort' => 2,
          ],
          [
            'title' => '系统菜单',
            'icon' => 'hs-icon hs-icon-menu',
            'module' => 'system',
            'url' => 'system/menu/index',
            'param' => '',
            'target' => '_self',
            'debug' => 1,
            'system' => 1,
            'nav' => 1,
            'sort' => 3,
            'childs' => [
              [
                'title' => '添加菜单',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/menu/add',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 1,
              ],
              [
                'title' => '修改菜单',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/menu/edit',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 2,
              ],
              [
                'title' => '删除菜单',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/menu/del',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 3,
              ],
              [
                'title' => '状态设置',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/menu/status',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 4,
              ],
              [
                'title' => '排序设置',
                'icon' => '',
                'module' => 'admin',
                'url' => 'system/menu/setField',
                'param' => 'field=sort',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 5,
              ],
              [
                'title' => '添加快捷菜单',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/menu/quick',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 6,
              ],
              [
                'title' => '导出菜单',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/menu/export',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 7,
              ],
            ],
          ],
          [
            'title' => '角色权限',
            'icon' => 'hs-icon hs-icon-admin',
            'module' => 'system',
            'url' => 'system/role/index',
            'param' => '',
            'target' => '_self',
            'debug' => 0,
            'system' => 1,
            'nav' => 1,
            'sort' => 4,
            'childs' => [
              [
                'title' => '添加角色',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/role/add',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 1,
              ],
              [
                'title' => '修改角色',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/role/edit',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 2,
              ],
              [
                'title' => '删除角色',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/role/del',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 3,
              ],
              [
                'title' => '状态设置',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/role/status',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 4,
              ],
            ],
          ],
          [
            'title' => '系统管理员',
            'icon' => 'hs-icon hs-icon-admin',
            'module' => 'system',
            'url' => 'system/user/index',
            'param' => '',
            'target' => '_self',
            'debug' => 0,
            'system' => 1,
            'nav' => 1,
            'sort' => 5,
            'childs' => [
              [
                'title' => '添加管理员',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/user/add',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 1,
              ],
              [
                'title' => '修改管理员',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/user/edit',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 2,
              ],
              [
                'title' => '删除管理员',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/user/del',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 3,
              ],
              [
                'title' => '状态设置',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/user/status',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 4,
              ],
            ],
          ],
          [
            'title' => '数据库管理',
            'icon' => 'hs-icon hs-icon-database',
            'module' => 'system',
            'url' => 'system/database/index',
            'param' => '',
            'target' => '_self',
            'debug' => 0,
            'system' => 1,
            'nav' => 1,
            'sort' => 6,
            'childs' => [
              [
                'title' => '备份数据库',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/database/export',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 0,
              ],
              [
                'title' => '恢复数据库',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/database/import',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 0,
              ],
              [
                'title' => '优化数据库',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/database/optimize',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 0,
              ],
              [
                'title' => '删除备份',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/database/del',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 0,
              ],
              [
                'title' => '修复数据库',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/database/repair',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 0,
              ],
              [
                'title' => '数据库管理',
                'icon' => 'hs-icon hs-icon-database',
                'module' => 'system',
                'url' => 'system/database/index',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 1,
                'sort' => 0,
              ],
              [
                'title' => '执行SQL',
                'icon' => 'hs-icon hs-icon-gongneng',
                'module' => 'system',
                'url' => 'system/database/sql',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 1,
                'sort' => 0,
              ],
              [
                'title' => '批量替换',
                'icon' => 'hs-icon hs-icon-theme',
                'module' => 'system',
                'url' => 'system/database/rep',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 1,
                'sort' => 0,
              ],
            ],
          ],
          [
            'title' => '系统日志',
            'icon' => 'hs-icon hs-icon-syslog',
            'module' => 'system',
            'url' => 'system/log/index',
            'param' => '',
            'target' => '_self',
            'debug' => 0,
            'system' => 1,
            'nav' => 1,
            'sort' => 7,
            'childs' => [
              [
                'title' => '删除日志',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/log/del',
                'param' => 'table=admin_log',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 100,
              ],
              [
                'title' => '清空日志',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/log/clear',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 100,
              ],
            ],
          ],
          [
            'title' => '附件管理',
            'icon' => '',
            'module' => 'system',
            'url' => 'system/annex/index',
            'param' => '',
            'target' => '_self',
            'debug' => 0,
            'system' => 1,
            'nav' => 0,
            'sort' => 8,
            'childs' => [
              [
                'title' => '附件上传',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/annex/upload',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 1,
              ],
              [
                'title' => '删除附件',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/annex/del',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 2,
              ],
              [
                'title' => '收藏夹图标上传',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/annex/favicon',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 3,
              ],
            ],
          ],
          [
            'title' => '语言包管理',
            'icon' => '',
            'module' => 'system',
            'url' => 'system/language/index',
            'param' => '',
            'target' => '_self',
            'debug' => 0,
            'system' => 1,
            'nav' => 0,
            'sort' => 9,
            'childs' => [
              [
                'title' => '添加语言包',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/language/add',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 100,
              ],
              [
                'title' => '修改语言包',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/language/edit',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 100,
              ],
              [
                'title' => '删除语言包',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/language/del',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 100,
              ],
              [
                'title' => '排序设置',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/language/sort',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 100,
              ],
              [
                'title' => '状态设置',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/language/status',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 100,
              ],
            ],
          ],
        ],
      ],
      [
        'title' => '系统扩展',
        'icon' => 'hs-icon hs-icon-sys',
        'module' => 'admin',
        'url' => 'system/extend',
        'param' => '',
        'target' => '_self',
        'debug' => 0,
        'system' => 1,
        'nav' => 1,
        'sort' => 3,
        'childs' => [
          [
            'title' => '本地模块',
            'icon' => 'hs-icon hs-icon-module',
            'module' => 'system',
            'url' => 'system/module/index',
            'param' => '',
            'target' => '_self',
            'debug' => 0,
            'system' => 1,
            'nav' => 1,
            'sort' => 1,
            'childs' => [
              [
                'title' => '安装模块',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/module/install',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 1,
              ],
              [
                'title' => '卸载模块',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/module/uninstall',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 2,
              ],
              [
                'title' => '状态设置',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/module/status',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 3,
              ],
              [
                'title' => '设置默认模块',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/module/setdefault',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 4,
              ],
              [
                'title' => '删除模块',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/module/del',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 5,
              ],
              [
                'title' => '生成模块',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/module/design',
                'param' => '',
                'target' => '_self',
                'debug' => 1,
                'system' => 1,
                'nav' => 0,
                'sort' => 6,
              ],
              [
                'title' => '模块打包',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/module/package',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 7,
              ],
              [
                'title' => '主题管理',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/module/theme',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 8,
              ],
              [
                'title' => '设置默认主题',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/module/setdefaulttheme',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 9,
              ],
              [
                'title' => '导入主题SQL',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/module/exeSql',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 10,
              ],
              [
                'title' => '删除主题',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/module/deltheme',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 10,
              ],
              [
                'title' => '导入模块',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/module/import',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 11,
              ],
              [
                'title' => '编辑模块',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/module/edit',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 100,
              ],
              [
                'title' => '模块图标上传',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/module/icon',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 100,
              ],
            ],
          ],
          [
            'title' => '本地插件',
            'icon' => 'hs-icon hs-icon-plugins',
            'module' => 'system',
            'url' => 'system/plugins/index',
            'param' => '',
            'target' => '_self',
            'debug' => 0,
            'system' => 1,
            'nav' => 1,
            'sort' => 2,
            'childs' => [
              [
                'title' => '插件打包',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/plugins/package',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 0,
              ],
              [
                'title' => '安装插件',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/plugins/install',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 1,
              ],
              [
                'title' => '卸载插件',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/plugins/uninstall',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 2,
              ],
              [
                'title' => '删除插件',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/plugins/del',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 3,
              ],
              [
                'title' => '状态设置',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/plugins/status',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 4,
              ],
              [
                'title' => '生成插件',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/plugins/design',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 5,
              ],
              [
                'title' => '运行插件',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/plugins/run',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 6,
              ],
              [
                'title' => '更新插件',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/plugins/update',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 7,
              ],
              [
                'title' => '插件配置',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/plugins/setting',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 8,
              ],
              [
                'title' => '导入插件',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/plugins/import',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 100,
              ],
            ],
          ],
          [
            'title' => '插件钩子',
            'icon' => 'hs-icon hs-icon-hook',
            'module' => 'system',
            'url' => 'system/hook/index',
            'param' => '',
            'target' => '_self',
            'debug' => 0,
            'system' => 1,
            'nav' => 1,
            'sort' => 3,
            'childs' => [
              [
                'title' => '添加钩子',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/hook/add',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 1,
              ],
              [
                'title' => '修改钩子',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/hook/edit',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 2,
              ],
              [
                'title' => '删除钩子',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/hook/del',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 3,
              ],
              [
                'title' => '状态设置',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/hook/status',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 4,
              ],
              [
                'title' => '插件排序',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/hook/sort',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 5,
              ],
              [
                'title' => '钩子插件状态',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/hook/hookPluginsStatus',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 100,
              ],
            ],
          ],
          [
            'title' => '框架升级',
            'icon' => 'hs-icon hs-icon-upgrade',
            'module' => 'system',
            'url' => 'system/upgrade/index',
            'param' => '',
            'target' => '_self',
            'debug' => 0,
            'system' => 1,
            'nav' => 1,
            'sort' => 4,
            'childs' => [
              [
                'title' => '获取升级列表',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/upgrade/lists',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 0,
              ],
              [
                'title' => '安装升级包',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/upgrade/install',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 0,
              ],
              [
                'title' => '下载升级包',
                'icon' => '',
                'module' => 'system',
                'url' => 'system/upgrade/download',
                'param' => '',
                'target' => '_self',
                'debug' => 0,
                'system' => 1,
                'nav' => 0,
                'sort' => 0,
              ],
            ],
          ],
        ],
      ],
    ],
  ],
];
