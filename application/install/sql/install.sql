
# Dump of table mgcms_jobs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `mgcms_jobs`;

CREATE TABLE `mgcms_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table mgcms_system_annex
# ------------------------------------------------------------

DROP TABLE IF EXISTS `mgcms_system_annex`;

CREATE TABLE `mgcms_system_annex` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `data_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联的数据ID',
  `type` varchar(20) NOT NULL DEFAULT '' COMMENT '类型',
  `group` varchar(100) NOT NULL DEFAULT 'sys' COMMENT '文件分组',
  `file` varchar(255) NOT NULL COMMENT '上传文件',
  `hash` varchar(64) NOT NULL COMMENT '文件hash值',
  `size` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '附件大小KB',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '使用状态(0未使用，1已使用)',
  `ctime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='[系统] 上传附件';



# Dump of table mgcms_system_annex_group
# ------------------------------------------------------------

DROP TABLE IF EXISTS `mgcms_system_annex_group`;

CREATE TABLE `mgcms_system_annex_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '附件分组',
  `count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '附件数量',
  `size` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '附件大小kb',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='[系统] 附件分组';



# Dump of table mgcms_system_config
# ------------------------------------------------------------

DROP TABLE IF EXISTS `mgcms_system_config`;

CREATE TABLE `mgcms_system_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为系统配置(1是，0否)',
  `group` varchar(20) NOT NULL DEFAULT 'base' COMMENT '分组',
  `title` varchar(20) NOT NULL COMMENT '配置标题',
  `name` varchar(50) NOT NULL COMMENT '配置名称，由英文字母和下划线组成',
  `value` text NOT NULL COMMENT '配置值',
  `type` varchar(20) NOT NULL DEFAULT 'input' COMMENT '配置类型()',
  `options` text NOT NULL COMMENT '配置项(选项名:选项值)',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '文件上传接口',
  `tips` varchar(255) NOT NULL COMMENT '配置提示',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) unsigned NOT NULL COMMENT '状态',
  `ctime` int(10) unsigned NOT NULL DEFAULT '0',
  `mtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8 COMMENT='[系统] 系统配置';

INSERT INTO `mgcms_system_config` (`id`, `system`, `group`, `title`, `name`, `value`, `type`, `options`, `url`, `tips`, `sort`, `status`, `ctime`, `mtime`)
VALUES
	(1,1,'sys','配置分组','config_group','','array',' ','','请按如下格式填写：&lt;br&gt;键值:键名&lt;br&gt;键值:键名&lt;br&gt;&lt;span style=&quot;color:#f00&quot;&gt;键值只能为英文、数字、下划线&lt;/span&gt;',2,1,1492140215,1554647091),
	(13,1,'base','网站域名','site_domain','http://www.test.com','input','','','',2,1,1492140215,1492140215),
	(14,1,'upload','图片限制','upload_image_size','0','input','','','单位：KB，0表示不限制上传大小',3,1,1490841797,1491040778),
	(15,1,'upload','图片格式','upload_image_ext','jpg,png,gif,jpeg,ico','input','','','多个格式请用英文逗号（,）隔开',4,1,1490842130,1491040778),
	(16,1,'upload','缩略图裁剪','thumb_type','2','select','1:等比例缩放\r\n2:缩放后填充\r\n3:居中裁剪\r\n4:左上角裁剪\r\n5:右下角裁剪\r\n6:固定尺寸缩放\r\n','','',5,1,1490842450,1491040778),
	(17,1,'upload','图片水印','image_watermark','1','switch','0:关闭\r\n1:开启','','',6,1,1490842583,1491040778),
	(18,1,'upload','图片水印图','image_watermark_pic','','image','','','',7,1,1490842679,1491040778),
	(19,1,'upload','水印透明度','image_watermark_opacity','50','input','','','可设置值为0~100，数字越小，透明度越高',8,1,1490857704,1491040778),
	(20,1,'upload','水印图位置','image_watermark_location','9','select','7:左下角\r\n1:左上角\r\n4:左居中\r\n9:右下角\r\n3:右上角\r\n6:右居中\r\n2:上居中\r\n8:下居中\r\n5:居中','','',9,1,1490858228,1491040778),
	(21,1,'upload','文件限制','upload_file_size','0','input','','','单位：KB，0表示不限制上传大小',1,1,1490859167,1491040778),
	(22,1,'upload','文件格式','upload_file_ext','doc,docx,xls,xlsx,ppt,pptx,pdf,wps,txt,rar,zip','input','','','多个格式请用英文逗号（,）隔开',2,1,1490859246,1491040778),
	(23,1,'upload','文字水印','text_watermark','0','switch','0:关闭\r\n1:开启','','',10,1,1490860872,1491040778),
	(24,1,'upload','水印内容','text_watermark_content','','input','','','',11,1,1490861005,1491040778),
	(25,1,'upload','水印字体','text_watermark_font','','file','','','不上传将使用系统默认字体',12,1,1490861117,1491040778),
	(26,1,'upload','水印大小','text_watermark_size','20','input','','','单位：px(像素)',13,1,1490861204,1491040778),
	(27,1,'upload','水印颜色','text_watermark_color','#000000','input','','','文字水印颜色，格式:#000000',14,1,1490861482,1491040778),
	(28,1,'upload','水印位置','text_watermark_location','7','select','7:左下角\r\n1:左上角\r\n4:左居中\r\n9:右下角\r\n3:右上角\r\n6:右居中\r\n2:上居中\r\n8:下居中\r\n5:居中','','',11,1,1490861718,1491040778),
	(29,1,'upload','缩略图尺寸','thumb_size','300x300;500x500','input','','','[选填] 单规格示例500x500，多规格示例300x300;500x500',4,1,1490947834,1491040778),
	(30,1,'sys','开发模式','app_debug','0','switch','0:关闭\r\n1:开启','','&lt;strong class=&quot;red&quot;&gt;生产环境下一定要关闭此配置&lt;/strong&gt;',3,1,1491005004,1492093874),
	(31,1,'sys','页面Trace','app_trace','0','switch','0:关闭\r\n1:开启','','&lt;strong class=&quot;red&quot;&gt;生产环境下一定要关闭此配置&lt;/strong&gt;',4,1,1491005081,1492093874),
	(33,1,'sys','编辑器','editor','ueditor','select','ueditor:UEditor\r\numeditor:UMEditor\r\nkindeditor:KindEditor\r\nckeditor:CKEditor','','',0,1,1491142648,1492140215),
	(35,1,'databases','备份目录','backup_path','./backup/database/','input','','','数据库备份路径,路径必须以 / 结尾',0,1,1491881854,1491965974),
	(36,1,'databases','分卷大小','part_size','20971520','input','','','用于限制压缩后的分卷最大长度。单位：B；建议设置20M',0,1,1491881975,1491965974),
	(37,1,'databases','压缩开关','compress','1','switch','0:关闭\r\n1:开启','','压缩备份文件需要PHP环境支持gzopen,gzwrite函数',0,1,1491882038,1491965974),
	(38,1,'databases','压缩级别','compress_level','4','radio','1:最低\r\n4:一般\r\n9:最高','','数据库备份文件的压缩级别，该配置在开启压缩时生效',0,1,1491882154,1491965974),
	(39,1,'base','网站状态','site_status','1','switch','0:关闭\r\n1:开启','','站点关闭后将不能访问，后台可正常登录',1,1,1492049460,1554634212),
	(40,1,'sys','后台路径','admin_path','admin.php','input','','','必须以.php为后缀',1,1,1492139196,1492140215),
	(41,1,'base','网站标题','site_title','','input','','','网站首页标题，建议不超过28个字',8,1,1492502354,1494695131),
	(42,1,'base','网站关键词','site_keywords','','input','','','网站首页关键词，多个关键字请用英文逗号&quot;,&quot;分隔',9,1,1494690508,1494690780),
	(43,1,'base','网站描述','site_description','','textarea','','','网站首页描述信息，建议不超过80个字符',10,1,1494690669,1494691075),
	(44,1,'base','ICP备案号','site_icp','','input','','','请填写ICP备案号',11,1,1494691721,1494692046),
	(45,1,'base','统计代码','site_statis','','textarea','','','前台调用时请先用 htmlspecialchars_decode函数转义输出',12,1,1494691959,1494694797),
	(46,1,'base','网站名称','site_name','咪咕CMS','input','','','将显示在浏览器窗口标题等位置',5,1,1494692103,1494694680),
	(47,1,'base','网站LOGO','site_logo','/static/system/image/logo.png','image','','','网站LOGO图片',6,1,1494692345,1494693235),
	(48,1,'base','网站图标','site_favicon','','image','','/system/annex/favicon','用于浏览器的地址栏展示，&lt;strong class=&quot;red&quot;&gt;.ico格式&lt;/strong&gt;，&lt;a href=&quot;https://www.baidu.com/s?ie=UTF-8&amp;wd=favicon&quot; target=&quot;_blank&quot;&gt;点此了解&lt;/a&gt;',7,1,1494692781,1494693966),
	(49,1,'base','手机网站','wap_site_status','0','switch','0:关闭\r\n1:开启','','如果有手机网站，请设置为开启状态',3,1,1498405436,1498405436),
	(50,1,'sys','云端推送','cloud_push','0','switch','0:关闭\r\n1:开启','','关闭之后，无法通过云端推送安装扩展',5,1,1504250320,1504250320),
	(51,1,'base','手机域名','wap_domain','http://m.test.com','input','','','手机访问将自动跳转至此域名',4,1,1504304776,1504304837),
	(52,1,'sys','多语言支持','multi_language','0','switch','0:关闭\r\n1:开启','','开启后你可以自由上传多种语言包',6,1,1506532211,1506532211),
	(53,1,'sys','后台白名单','admin_whitelist_verify','0','switch','0:禁用\r\n1:启用','','禁用后不存在的菜单节点将不在提示',7,1,1542012232,1542012321),
	(54,1,'sys','日志保留天数','system_log_retention','30','input','','','单位天，系统将自动清除 ? 天前的日志',8,1,1542013958,1542014158),
	(55,1,'upload','上传驱动','upload_driver','local','select','local:本地上传','','资源上传驱动设置',0,1,1558599270,1558618703),
	(56,1,'sys','后台全局JS','admin_js','','input','','','填写文件名即可(不含.js)，多个文件用逗号(,)分隔。&lt;br&gt;请确保文件在/public/static/system/js/下。',9,1,1570695206,1570696415),
	(57,1,'sys','后台全局CSS','admin_css','','input','','','请填写完整的文件路径，多个文件用逗号(,)分隔。',10,1,1575950033,1575950085),
	(58,1,'sys','系统日志记录','system_log_switch','0','switch','0:关闭\r\n1:开启','','关闭后，不记录后台操作日志',7,1,1578660542,1578660801),
	(59,0,'sys','后台登录错误次数限制','admin_login_error_limit','5','input','','','指登录时的试错次数',11,1,1589160246,1589160432),
	(60,0,'sys','后台登录错误锁定时间','admin_login_locked_time','5','input','','','指达到试错次数后锁定的时间（单位分钟）',12,1,1589160318,1589160491),
	(61,1,'databases','SQL开关','sqlswitch','0','switch','0:关闭\r\n1:开启','','执行sql语句开关',0,1,1491882038,1491965974);


# Dump of table mgcms_system_hook
# ------------------------------------------------------------

DROP TABLE IF EXISTS `mgcms_system_hook`;

CREATE TABLE `mgcms_system_hook` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '系统插件',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '钩子名称',
  `source` varchar(50) NOT NULL DEFAULT '' COMMENT '钩子来源[plugins.插件名，module.模块名]',
  `intro` varchar(200) NOT NULL DEFAULT '' COMMENT '钩子简介',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `ctime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `mtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='[系统] 钩子表';


INSERT INTO `mgcms_system_hook` (`id`, `system`, `name`, `source`, `intro`, `status`, `ctime`, `mtime`)
VALUES
	(1,1,'system_admin_index','','后台首页',1,1490885108,1490885108),
	(2,1,'system_admin_tips','','后台所有页面提示',1,1490713165,1490885137),
	(3,1,'system_annex_upload','','附件上传钩子，可扩展上传到第三方存储',1,1490884242,1490885121);

# Dump of table mgcms_system_hook_plugins
# ------------------------------------------------------------

DROP TABLE IF EXISTS `mgcms_system_hook_plugins`;

CREATE TABLE `mgcms_system_hook_plugins` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `hook` varchar(32) NOT NULL COMMENT '钩子id',
  `plugins` varchar(32) NOT NULL COMMENT '插件标识',
  `ctime` int(11) unsigned NOT NULL DEFAULT '0',
  `mtime` int(11) unsigned NOT NULL DEFAULT '0',
  `sort` int(11) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='[系统] 钩子-插件对应表';


INSERT INTO `mgcms_system_hook_plugins` (`id`, `hook`, `plugins`, `ctime`, `mtime`, `sort`, `status`)
VALUES
	(1,'system_admin_index','mgcms',1509380301,1509380301,0,1);

# Dump of table mgcms_system_language
# ------------------------------------------------------------

DROP TABLE IF EXISTS `mgcms_system_language`;

CREATE TABLE `mgcms_system_language` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '语言包名称',
  `code` varchar(20) NOT NULL DEFAULT '' COMMENT '编码',
  `locale` varchar(255) NOT NULL DEFAULT '' COMMENT '本地浏览器语言编码',
  `icon` varchar(30) NOT NULL DEFAULT '' COMMENT '图标',
  `pack` varchar(100) NOT NULL DEFAULT '' COMMENT '上传的语言包',
  `sort` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='[系统] 语言包';


INSERT INTO `mgcms_system_language` (`id`, `name`, `code`, `locale`, `icon`, `pack`, `sort`, `status`)
VALUES
	(1,'简体中文','zh-cn','zh-CN,zh-CN.UTF-8,zh-cn','','1',1,1);

# Dump of table mgcms_system_log
# ------------------------------------------------------------

DROP TABLE IF EXISTS `mgcms_system_log`;

CREATE TABLE `mgcms_system_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(100) DEFAULT '',
  `url` text,
  `param` text,
  `remark` varchar(255) DEFAULT '',
  `count` int(10) unsigned NOT NULL DEFAULT '1',
  `ip` varchar(128) DEFAULT '',
  `ctime` int(10) unsigned NOT NULL DEFAULT '0',
  `mtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='[系统] 操作日志';


# Dump of table mgcms_system_menu
# ------------------------------------------------------------

DROP TABLE IF EXISTS `mgcms_system_menu`;

CREATE TABLE `mgcms_system_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID(快捷菜单专用)',
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `module` varchar(50) NOT NULL DEFAULT '' COMMENT '模块名或插件名，插件名格式:plugins.插件名',
  `title` varchar(20) NOT NULL COMMENT '菜单标题',
  `icon` varchar(80) NOT NULL DEFAULT 'layui-icon layui-icon-next' COMMENT '菜单图标',
  `url` varchar(200) NOT NULL COMMENT '链接地址(模块/控制器/方法)',
  `param` varchar(200) NOT NULL DEFAULT '' COMMENT '扩展参数',
  `target` varchar(20) NOT NULL DEFAULT '_self' COMMENT '打开方式(_blank,_self)',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `debug` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '开发模式可见',
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为系统菜单，系统菜单不可删除',
  `nav` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为菜单显示，1显示0不显示',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态1显示，0隐藏',
  `ctime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=141 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='[系统] 管理菜单';


INSERT INTO `mgcms_system_menu` (`id`, `uid`, `pid`, `module`, `title`, `icon`, `url`, `param`, `target`, `sort`, `debug`, `system`, `nav`, `status`, `ctime`)
VALUES
	(1,0,0,'system','','','','','_self',0,0,1,0,0,1490315067),
	(2,0,0,'system','系统管理','layui-icon layui-icon-set','system/system','','_self',1,0,1,1,1,1490315067),
	(3,0,0,'system','','','','','_self',0,0,1,0,0,1490315067),
	(4,0,0,'system','其他','','','','_self',0,0,1,0,1,1490315067),
	(5,0,0,'system','插件列表','layui-icon layui-icon-app','','','_self',2,0,1,1,1,1490315067),
	(6,0,2,'system','系统基础','hs-icon hs-icon-gongneng','system/system','','_self',1,0,1,1,1,1490315067),
	(7,0,17,'system','导入主题SQL','','system/module/exeSql','','_self',10,0,1,0,1,1490315067),
	(8,0,2,'admin','系统扩展','hs-icon hs-icon-sys','system/extend','','_self',3,0,1,1,1,1490315067),
	(9,0,4,'system','','','','','_self',4,0,1,0,0,1490315067),
	(10,0,6,'system','系统设置','hs-icon hs-icon-sys-set','system/system/index','','_self',1,0,1,1,1,1490315067),
	(11,0,6,'system','配置管理','hs-icon hs-icon-set','system/config/index','','_self',2,1,1,1,1,1490315067),
	(12,0,6,'system','系统菜单','hs-icon hs-icon-menu','system/menu/index','','_self',3,1,1,1,1,1490315067),
	(13,0,6,'system','角色权限','hs-icon hs-icon-admin','system/role/index','','_self',4,0,1,1,1,1490315067),
	(14,0,6,'system','系统管理员','hs-icon hs-icon-admin','system/user/index','','_self',5,0,1,1,1,1490315067),
	(15,0,6,'system','系统日志','hs-icon hs-icon-syslog','system/log/index','','_self',7,0,1,1,1,1490315067),
	(16,0,6,'system','附件管理','','system/annex/index','','_self',8,0,1,0,1,1490315067),
	(17,0,8,'system','本地模块','hs-icon hs-icon-module','system/module/index','','_self',1,0,1,1,1,1490315067),
	(18,0,8,'system','本地插件','hs-icon hs-icon-plugins','system/plugins/index','','_self',2,0,1,1,1,1490315067),
	(19,0,8,'system','插件钩子','hs-icon hs-icon-hook','system/hook/index','','_self',3,0,1,1,1,1490315067),
	(20,0,4,'system','','','','','_self',1,0,1,0,0,1490315067),
	(21,0,4,'system','','','','','_self',2,0,1,0,0,1490315067),
	(22,0,4,'system','','','','','_self',1,0,1,0,0,1490315067),
	(23,0,4,'system','','','','','_self',2,0,1,0,0,1490315067),
	(24,0,4,'system','','','','','_self',100,0,1,0,0,1490315067),
	(25,0,6,'system','清空缓存','','system/index/clear','','_self',2,0,1,0,1,1490315067),
	(26,0,12,'system','添加菜单','','system/menu/add','','_self',1,0,1,0,1,1490315067),
	(27,0,12,'system','修改菜单','','system/menu/edit','','_self',2,0,1,0,1,1490315067),
	(28,0,12,'system','删除菜单','','system/menu/del','','_self',3,0,1,0,1,1490315067),
	(29,0,12,'system','状态设置','','system/menu/status','','_self',4,0,1,0,1,1490315067),
	(30,0,12,'admin','排序设置','','system/menu/setField','field=sort','_self',5,0,1,0,1,1490315067),
	(31,0,12,'system','添加快捷菜单','','system/menu/quick','','_self',6,0,1,0,1,1490315067),
	(32,0,12,'system','导出菜单','','system/menu/export','','_self',7,0,1,0,1,1490315067),
	(33,0,13,'system','添加角色','','system/role/add','','_self',1,0,1,0,1,1490315067),
	(34,0,13,'system','修改角色','','system/role/edit','','_self',2,0,1,0,1,1490315067),
	(35,0,13,'system','删除角色','','system/role/del','','_self',3,0,1,0,1,1490315067),
	(36,0,13,'system','状态设置','','system/role/status','','_self',4,0,1,0,1,1490315067),
	(37,0,14,'system','添加管理员','','system/user/add','','_self',1,0,1,0,1,1490315067),
	(38,0,14,'system','修改管理员','','system/user/edit','','_self',2,0,1,0,1,1490315067),
	(39,0,14,'system','删除管理员','','system/user/del','','_self',3,0,1,0,1,1490315067),
	(40,0,14,'system','状态设置','','system/user/status','','_self',4,0,1,0,1,1490315067),
	(41,0,4,'system','个人信息设置','','system/user/info','','_self',5,0,1,0,1,1490315067),
	(42,0,18,'system','安装插件','','system/plugins/install','','_self',1,0,1,0,1,1490315067),
	(43,0,18,'system','卸载插件','','system/plugins/uninstall','','_self',2,0,1,0,1,1490315067),
	(44,0,18,'system','删除插件','','system/plugins/del','','_self',3,0,1,0,1,1490315067),
	(45,0,18,'system','状态设置','','system/plugins/status','','_self',4,0,1,0,1,1490315067),
	(46,0,18,'system','生成插件','','system/plugins/design','','_self',5,0,1,0,1,1490315067),
	(47,0,18,'system','运行插件','','system/plugins/run','','_self',6,0,1,0,1,1490315067),
	(48,0,18,'system','更新插件','','system/plugins/update','','_self',7,0,1,0,1,1490315067),
	(49,0,18,'system','插件配置','','system/plugins/setting','','_self',8,0,1,0,1,1490315067),
	(50,0,19,'system','添加钩子','','system/hook/add','','_self',1,0,1,0,1,1490315067),
	(51,0,19,'system','修改钩子','','system/hook/edit','','_self',2,0,1,0,1,1490315067),
	(52,0,19,'system','删除钩子','','system/hook/del','','_self',3,0,1,0,1,1490315067),
	(53,0,19,'system','状态设置','','system/hook/status','','_self',4,0,1,0,1,1490315067),
	(54,0,19,'system','插件排序','','system/hook/sort','','_self',5,0,1,0,1,1490315067),
	(55,0,11,'system','添加配置','','system/config/add','','_self',1,0,1,0,1,1490315067),
	(56,0,11,'system','修改配置','','system/config/edit','','_self',2,0,1,0,1,1490315067),
	(57,0,11,'system','删除配置','','system/config/del','','_self',3,0,1,0,1,1490315067),
	(58,0,11,'system','状态设置','','system/config/status','','_self',4,0,1,0,1,1490315067),
	(59,0,11,'admin','排序设置','','system/config/setField','field=sort','_self',5,0,1,0,1,1490315067),
	(60,0,10,'admin','基础配置','','system/system/index','group=base','_self',1,0,0,0,1,1490315067),
	(61,0,10,'system','系统配置','','system/system/index','group=sys','_self',2,0,1,0,1,1490315067),
	(62,0,10,'system','上传配置','','system/system/index','group=upload','_self',3,0,1,0,1,1490315067),
	(63,0,10,'system','开发配置','','system/system/index','group=develop','_self',4,0,1,0,1,1490315067),
	(64,0,17,'system','生成模块','','system/module/design','','_self',6,1,1,0,1,1490315067),
	(65,0,17,'system','安装模块','','system/module/install','','_self',1,0,1,0,1,1490315067),
	(66,0,17,'system','卸载模块','','system/module/uninstall','','_self',2,0,1,0,1,1490315067),
	(67,0,17,'system','状态设置','','system/module/status','','_self',3,0,1,0,1,1490315067),
	(68,0,17,'system','设置默认模块','','system/module/setdefault','','_self',4,0,1,0,1,1490315067),
	(69,0,17,'system','删除模块','','system/module/del','','_self',5,0,1,0,1,1490315067),
	(70,0,4,'system','','','','','_self',1,0,1,0,0,1490315067),
	(71,0,4,'system','','','','','_self',2,0,1,0,0,1490315067),
	(72,0,4,'system','','','','','_self',3,0,1,0,0,1490315067),
	(73,0,4,'system','','','','','_self',4,0,1,0,0,1490315067),
	(74,0,4,'system','','','','','_self',5,0,1,0,0,1490315067),
	(75,0,4,'system','','','','','_self',0,0,1,0,0,1490315067),
	(76,0,4,'system','','','','','_self',0,0,1,0,0,1490315067),
	(77,0,4,'system','','','','','_self',0,0,1,0,0,1490315067),
	(78,0,16,'system','附件上传','','system/annex/upload','','_self',1,0,1,0,1,1490315067),
	(79,0,16,'system','删除附件','','system/annex/del','','_self',2,0,1,0,1,1490315067),
	(80,0,8,'system','框架升级','hs-icon hs-icon-upgrade','system/upgrade/index','','_self',4,0,1,1,1,1491352728),
	(81,0,80,'system','获取升级列表','','system/upgrade/lists','','_self',0,0,1,0,1,1491353504),
	(82,0,80,'system','安装升级包','','system/upgrade/install','','_self',0,0,1,0,1,1491353568),
	(83,0,80,'system','下载升级包','','system/upgrade/download','','_self',0,0,1,0,1,1491395830),
	(84,0,6,'system','数据库管理','hs-icon hs-icon-database','system/database/index','','_self',6,0,1,1,1,1491461136),
	(85,0,84,'system','备份数据库','','system/database/export','','_self',0,0,1,0,1,1491461250),
	(86,0,84,'system','恢复数据库','','system/database/import','','_self',0,0,1,0,1,1491461315),
	(87,0,84,'system','优化数据库','','system/database/optimize','','_self',0,0,1,0,1,1491467000),
	(88,0,84,'system','删除备份','','system/database/del','','_self',0,0,1,0,1,1491467058),
	(89,0,84,'system','修复数据库','','system/database/repair','','_self',0,0,1,0,1,1491880879),
	(90,0,21,'system','','','','','_self',0,0,1,0,1,1491966585),
	(91,0,10,'system','数据库配置','','system/system/index','group=databases','_self',5,0,1,0,1,1492072213),
	(92,0,17,'system','模块打包','','system/module/package','','_self',7,0,1,0,1,1492134693),
	(93,0,18,'system','插件打包','','system/plugins/package','','_self',0,0,1,0,1,1492134743),
	(94,0,17,'system','主题管理','','system/module/theme','','_self',8,0,1,0,1,1492433470),
	(95,0,17,'system','设置默认主题','','system/module/setdefaulttheme','','_self',9,0,1,0,1,1492433618),
	(96,0,17,'system','删除主题','','system/module/deltheme','','_self',10,0,1,0,1,1490315067),
	(97,0,6,'system','语言包管理','','system/language/index','','_self',9,0,1,0,1,1490315067),
	(98,0,97,'system','添加语言包','','system/language/add','','_self',100,0,1,0,1,1490315067),
	(99,0,97,'system','修改语言包','','system/language/edit','','_self',100,0,1,0,1,1490315067),
	(100,0,97,'system','删除语言包','','system/language/del','','_self',100,0,1,0,1,1490315067),
	(101,0,97,'system','排序设置','','system/language/sort','','_self',100,0,1,0,1,1490315067),
	(102,0,97,'system','状态设置','','system/language/status','','_self',100,0,1,0,1,1490315067),
	(103,0,16,'system','收藏夹图标上传','','system/annex/favicon','','_self',3,0,1,0,1,1490315067),
	(104,0,17,'system','导入模块','','system/module/import','','_self',11,0,1,0,1,1490315067),
	(105,0,4,'system','','','','','_self',100,0,1,0,0,1490315067),
	(106,0,4,'system','布局切换','','system/user/iframe','','_self',100,0,1,0,1,1490315067),
	(107,0,15,'system','删除日志','','system/log/del','table=admin_log','_self',100,0,1,0,1,1490315067),
	(108,0,15,'system','清空日志','','system/log/clear','','_self',100,0,1,0,1,1490315067),
	(109,0,17,'system','编辑模块','','system/module/edit','','_self',100,0,1,0,1,1490315067),
	(110,0,17,'system','模块图标上传','','system/module/icon','','_self',100,0,1,0,1,1490315067),
	(111,0,18,'system','导入插件','','system/plugins/import','','_self',100,0,1,0,1,1490315067),
	(112,0,19,'system','钩子插件状态','','system/hook/hookPluginsStatus','','_self',100,0,1,0,1,1490315067),
	(113,0,4,'system','设置主题','','system/user/setTheme','','_self',100,0,1,0,1,1490315067),
	(114,0,8,'system','应用市场','hs-icon hs-icon-store','system/store/index','','_self',0,0,1,1,1,1490315067),
	(115,0,114,'system','安装应用','','system/store/install','','_self',0,0,1,0,1,1490315067),
	(116,0,21,'system','','','','','_self',0,0,1,0,1,1490315067),
	(117,0,4,'system','','','','','_self',100,0,1,0,0,1490315067),
	(118,0,4,'system','','','','','_self',100,0,1,0,0,1490315067),
	(119,0,4,'system','','','','','_self',100,0,1,0,0,1490315067),
	(120,0,4,'system','','','','','_self',100,0,1,0,0,1490315067),
	(121,0,4,'system','','','','','_self',100,0,1,0,0,1490315067),
	(122,0,4,'system','','','','','_self',100,0,1,0,0,1490315067),
	(123,0,4,'system','','','','','_self',100,0,1,0,0,1490315067),
	(124,0,4,'system','','','','','_self',100,0,1,0,0,1490315067),
	(125,0,4,'system','','','','','_self',100,0,1,0,0,1490315067),
	(126,0,4,'system','','','','','_self',100,0,1,0,0,1490315067),
	(127,0,4,'system','','','','','_self',100,0,1,0,0,1490315067),
	(128,0,4,'system','','','','','_self',100,0,1,0,0,1490315067),
	(129,0,4,'system','','','','','_self',100,0,1,0,0,1490315067),
	(130,0,4,'system','','','','','_self',100,0,1,0,0,1490315067),
	(131,0,4,'system','','','','','_self',100,0,1,0,0,1490315067),
	(132,0,4,'system','','','','','_self',100,0,1,0,0,1490315067),
	(133,0,4,'system','','','','','_self',100,0,1,0,0,1490315067),
	(134,0,4,'system','','','','','_self',100,0,1,0,0,1490315067),
	(135,0,4,'system','','','','','_self',100,0,1,0,0,1490315067),
	(136,0,4,'system','','','','','_self',100,0,1,0,0,1490315067),
	(137,0,4,'system','','','','','_self',100,0,1,0,0,1490315067),
	(138,0,4,'system','','','','','_self',100,0,1,0,0,1490315067),
	(139,0,4,'system','','','','','_self',100,0,1,0,0,1490315067),
	(140,0,84,'system','数据库管理','hs-icon hs-icon-database','system/database/index','','_self',100,0,1,1,1,1490315067),
	(141,0,84,'system','执行SQL','hs-icon hs-icon-gongneng','system/database/sql','','_self',100,0,1,1,1,1490315067),
	(142,0,84,'system','批量替换','hs-icon hs-icon-theme','system/database/rep','','_self',100,0,1,1,1,1490315067),
	(143,0,4,'system','','','','','_self',100,0,1,0,0,1490315067);

# Dump of table mgcms_system_menu_lang
# ------------------------------------------------------------

DROP TABLE IF EXISTS `mgcms_system_menu_lang`;

CREATE TABLE `mgcms_system_menu_lang` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `menu_id` int(11) unsigned NOT NULL DEFAULT '0',
  `title` varchar(120) NOT NULL DEFAULT '' COMMENT '标题',
  `lang` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '语言包',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='[系统] 管理菜单语言包';



# Dump of table mgcms_system_module
# ------------------------------------------------------------

DROP TABLE IF EXISTS `mgcms_system_module`;

CREATE TABLE `mgcms_system_module` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '系统模块',
  `name` varchar(50) NOT NULL COMMENT '模块名(英文)',
  `identifier` varchar(100) NOT NULL COMMENT '模块标识(模块名(字母).开发者标识.module)',
  `title` varchar(50) NOT NULL COMMENT '模块标题',
  `intro` varchar(255) NOT NULL COMMENT '模块简介',
  `author` varchar(100) NOT NULL COMMENT '作者',
  `icon` varchar(80) NOT NULL DEFAULT 'aicon ai-mokuaiguanli' COMMENT '图标',
  `version` varchar(20) NOT NULL COMMENT '版本号',
  `url` varchar(255) NOT NULL COMMENT '链接',
  `sort` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0未安装，1未启用，2已启用',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '默认模块(只能有一个)',
  `config` text NOT NULL COMMENT '配置',
  `app_id` varchar(30) NOT NULL DEFAULT '0' COMMENT '应用市场ID(0本地)',
  `app_keys` varchar(200) DEFAULT '' COMMENT '应用秘钥',
  `theme` varchar(50) NOT NULL DEFAULT 'default' COMMENT '主题模板',
  `ctime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `mtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `identifier` (`identifier`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='[系统] 模块';


INSERT INTO `mgcms_system_module` (`id`, `system`, `name`, `identifier`, `title`, `intro`, `author`, `icon`, `version`, `url`, `sort`, `status`, `is_default`, `config`, `app_id`, `app_keys`, `theme`, `ctime`, `mtime`)
VALUES
	(1,1,'system','system.mgcms.module','系统管理模块','系统核心模块，用于后台各项管理功能模块及功能拓展','咪咕CMS官方出品','','1.0.0','http://www.migucms.com',0,2,0,'','0','','default',1489998096,1489998096),
	(2,1,'index','index.mgcms.module','默认模块','推荐使用扩展模块作为默认首页。','咪咕CMS官方出品','','1.0.0','http://www.migucms.com',0,2,0,'','0','','default',1489998096,1489998096),
	(3,1,'install','install.mgcms.module','系统安装模块','系统安装模块，勿动。','咪咕CMS官方出品','','1.0.0','http://www.migucms.com',0,2,0,'','0','','default',1489998096,1489998096);

# Dump of table mgcms_system_plugins
# ------------------------------------------------------------

DROP TABLE IF EXISTS `mgcms_system_plugins`;

CREATE TABLE `mgcms_system_plugins` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `name` varchar(32) NOT NULL COMMENT '插件名称(英文)',
  `title` varchar(32) NOT NULL COMMENT '插件标题',
  `icon` varchar(64) NOT NULL COMMENT '图标',
  `intro` text NOT NULL COMMENT '插件简介',
  `author` varchar(32) NOT NULL COMMENT '作者',
  `url` varchar(255) NOT NULL COMMENT '作者主页',
  `version` varchar(16) NOT NULL DEFAULT '' COMMENT '版本号',
  `identifier` varchar(64) NOT NULL DEFAULT '' COMMENT '插件唯一标识符',
  `config` text NOT NULL COMMENT '插件配置',
  `app_id` varchar(30) NOT NULL DEFAULT '0' COMMENT '来源(0本地)',
  `app_keys` varchar(200) DEFAULT '' COMMENT '应用秘钥',
  `ctime` int(10) unsigned NOT NULL DEFAULT '0',
  `mtime` int(10) unsigned NOT NULL DEFAULT '0',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='[系统] 插件表';


INSERT INTO `mgcms_system_plugins` (`id`, `system`, `name`, `title`, `icon`, `intro`, `author`, `url`, `version`, `identifier`, `config`, `app_id`, `app_keys`, `ctime`, `mtime`, `sort`, `status`)
VALUES
	(1,1,'mgcms','系统基础信息','/static/plugins/mgcms/mgcms.png','后台首页展示系统基础信息和开发团队信息','咪咕CMS','http://www.migucms.com','1.0.0','mgcms.mgcms.plugins','','0','',1509379331,1509379331,0,2);


# Dump of table mgcms_system_role
# ------------------------------------------------------------

DROP TABLE IF EXISTS `mgcms_system_role`;

CREATE TABLE `mgcms_system_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `name` varchar(50) NOT NULL COMMENT '角色名称',
  `intro` varchar(200) NOT NULL COMMENT '角色简介',
  `auth` text NOT NULL COMMENT '角色权限',
  `childs` text COMMENT '子ID',
  `ctime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `mtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='[系统] 管理角色';


INSERT INTO `mgcms_system_role` (`id`, `pid`, `name`, `intro`, `auth`, `childs`, `ctime`, `mtime`, `status`)
VALUES
	(1,0,'超级管理员','拥有系统最高权限','[]','1',1489411760,0,1),
	(2,0,'系统管理员','拥有系统管理员权限','[]','2',1489411760,1554483958,1);


# Dump of table mgcms_system_user
# ------------------------------------------------------------

DROP TABLE IF EXISTS `mgcms_system_user`;

CREATE TABLE `mgcms_system_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(64) NOT NULL,
  `nick` varchar(50) NOT NULL COMMENT '昵称',
  `mobile` varchar(11) DEFAULT '',
  `email` varchar(50) DEFAULT '' COMMENT '邮箱',
  `auth` text COMMENT '权限',
  `iframe` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0默认，1框架',
  `theme` varchar(50) NOT NULL DEFAULT 'default' COMMENT '主题',
  `menu_layout` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '菜单布局（0左侧，1上左）',
  `homepage` varchar(100) DEFAULT '' COMMENT '默认主页',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `last_login_ip` varchar(128) DEFAULT '' COMMENT '最后登陆IP',
  `last_login_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登陆时间',
  `login_error_count` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '登录错误次数',
  `login_locked_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登录锁定时间',
  `ctime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `mtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='[系统] 管理用户';


# Dump of table mgcms_system_user_role
# ------------------------------------------------------------

DROP TABLE IF EXISTS `mgcms_system_user_role`;

CREATE TABLE `mgcms_system_user_role` (
  `user_id` int(11) unsigned DEFAULT '0',
  `role_id` int(10) unsigned DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理员角色索引';



# Dump of table mgcms_token
# ------------------------------------------------------------

DROP TABLE IF EXISTS `mgcms_token`;

CREATE TABLE `mgcms_token` (
  `token` varchar(128) NOT NULL DEFAULT '' COMMENT 'Token',
  `tag` varchar(50) DEFAULT '' COMMENT '标签',
  `value` varchar(30) NOT NULL DEFAULT '' COMMENT '映射的值',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `expire_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '过期时间',
  PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='token表';