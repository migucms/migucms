layui.define(["element", "form", "table", "md5", "laytpl", "func"], (function(exports) {
	"function" != typeof Object.assign && (Object.assign = function(target) {
		if (null == target) throw new TypeError("Cannot convert undefined or null to object");
		target = Object(target);
		for (var index = 1; index < arguments.length; index++) {
			var source = arguments[index];
			if (null != source) for (var key in source) Object.prototype.hasOwnProperty.call(source, key) && (target[key] = source[key])
		}
		return target
	});
	var $ = layui.jquery,
	element = layui.element,
	layer = layui.layer,
	form = layui.form,
	table = layui.table,
	md5 = layui.md5,
	laytpl = layui.laytpl,
	func = layui.func,
	self = {
		imgPreview: function(e) {
			var dw = $(document).width(),
			st = $(window).scrollTop(),
			wh = $(window).height(),
			dh = $(document).height(),
			top = e.pageY + 5,
			left = e.pageX + 10,
			iw = $("#migu-img-preview").width(),
			ih = $("#migu-img-preview").height();
			iw > dw && (iw = dw / 2, $("#migu-img-preview img").width(iw)),
			ih > wh && (ih = wh / 2, $("#migu-img-preview img").height(ih)),
			left + iw > dw && (left = left - (left + iw - dw) - 50),
			top + ih > wh && (top = top - ih - 10),
			$("#migu-img-preview").css({
				top: top + "px",
				left: left + "px"
			})
		},
		checkDevice: function() {
			var d = layui.device();
			d.ie && d.ie < 9 && layer.alert("IE" + d.ie + "下体验不佳，推荐使用：Chrome/Firefox/Edge/极速模式")
		},
		tabs: {
			body: ".migu-body",
			wrap: ".hs-menu-tabs",
			menu: ".hs-tabs-menu",
			item: ".hs-tabs-item",
			prev: ".hs-tabs-prev",
			next: ".hs-tabs-next",
			home: ".hs-tab-home",
			btn: ".hs-tab-btn",
			step: 200,
			minLeft: null,
			maxLeft: null,
			init: function() {
				if (0 != HSIFRAME) {
					var t = this,
					btn = t.wrap + " " + t.btn;
					$(".migu-tab-close-menu").show(),
					$(t.wrap).prepend($("#hs-menus-tabs-tpl").html()),
					$(document).on("click", ".hs-side-a", (function(e) {
						var url = $(this).attr("href"),
						id = $(this).attr("data-id"),
						title = $(this).attr("data-title");
						return "" != url && (url.indexOf("?") >= 0 ? url += "&migu_iframe=yes": url += "?migu_iframe=yes", $(window).width() <= 768 && $(".migu-body-shade").click(), t.add(title, id, url), !1)
					})),
					$(document).on("click", btn, (function(e) {
						var id = $(this).attr("data-id"),
						i = $(this).index();
						if ($(e.target).hasClass("layui-icon-close")) t.del(id, i);
						else {
							var type = $(this).attr("data-type");
							if ("prev" == type || "next" == type) {
								var m = $(t.menu),
								px = 0;
								if (t.maxLeft = t.maxLeft || $(t.next).offset().left, t.minLeft = t.minLeft || parseInt(m.css("left")), "prev" == type)(px = parseInt(m.css("left")) + t.step) >= t.minLeft && (px = t.minLeft);
								else {
									px = parseInt(m.css("left")) - t.step;
									var last = m.find("li:last");
									if (last.offset().left + last.width() < t.maxLeft) return
								}
								m.css({
									left: px
								})
							} else "drop" == type || t.change(id, i)
						}
					})),
					$(".hs-tabs-close").on("click", (function() {
						var f = function(that) {
							$(".hs-tabs-item" + that.attr("data-id")).remove(),
							that.remove()
						};
						"other" == $(this).attr("data-type") ? $(t.menu + " " + t.btn).each((function(i) {
							$(this).hasClass("active") || $(this).hasClass("hs-tab-home") || f($(this))
						})) : $(t.menu + " " + t.btn).each((function(i) {
							$(this).hasClass("hs-tab-home") || f($(this)),
							$(".hs-tab-home").click()
						})),
						$(t.menu).css({
							left: 30
						})
					}))
				}
			},
			add: function(title, id, url) {
				var t = this;
				if ($(t.btn + id)[0]) {
					$(t.btn + id).click();
					var iframe = $(".hs-tabs-item" + id).children("iframe");
					return iframe.prop("src", iframe.attr("src")),
					!0
				}
				var li = '<li class="hs-tab-btn hs-tab-btn' + id + ' active" data-id="' + id + '" data-type="page"><i></i>' + title + '<b class="layui-icon layui-icon-close"></b></li>';
				$(t.menu).css({
					left: 30
				}).children("li").removeClass("active"),
				$(t.home).after(li),
				$(".hs-tabs-item").hide(),
				$(t.body).append('<div class="hs-tabs-item hs-tabs-item' + id + '"><iframe src="' + url + '" frameborder="0" class="migu-body-iframe"></iframe></div>')
			},
			del: function(id, i) {
				var t = $(this.btn + id);
				t.hasClass("active") && (t.next()[0] ? t.next().click() : t.prev().click()),
				t.remove(),
				$(this.item + id).remove()
			},
			change: function(id, i) {
				var t = this,
				c = $(t.wrap + " li").eq(i),
				m = $(t.menu),
				l = c.offset().left;
				$(t.wrap + " li.active").removeClass("active"),
				c.addClass("active"),
				$(t.item).hide(),
				$(t.item + id).show();
				var r = l - (t.minLeft || parseInt(m.css("left")));
				r - $(t.next).width() < 300 ? $(t.prev).click() : r > .5 * m.width() && $(t.next).click()
			}
		},
		firstMenu: function() {
			if ($(".hs-first-menu")[0]) {
				for (var h = "",
				w = $(".hs-first-menu").width(), l = parseInt(w / 140) - 1, i = 0; i < l; i++) HSMENUS[i] && (h += '<li class="layui-nav-item" id="hs-first-menu' + HSMENUS[i].id + '"><a href="javascript:;" class="hs-first-menu-a" data-id="' + HSMENUS[i].id + '"><i class="' + (HSMENUS[i].icon ? HSMENUS[i].icon: "fa fa-list") + '"></i> ' + HSMENUS[i].title + "</a></li>");
				if (HSMENUS[l]) {
					h += '<li class="layui-nav-item"><a href="javascript:void(0);"><i class="fa fa-th"></i></a>',
					h += '<dl class="layui-nav-child">';
					for (var i = l; i < HSMENUS.length; i++) h += '<dd id="hs-first-menu' + HSMENUS[i].id + '"><a href="javascript:;" class="hs-first-menu-a" data-id="' + HSMENUS[i].id + '"><i class="' + (HSMENUS[i].icon ? HSMENUS[i].icon: "fa fa-list") + '"></i> ' + HSMENUS[i].title + "</a></dd>";
					h += "</dl></li>"
				}
				$(".hs-first-menu").html(h),
				element.render()
			}
		},
		side: {
			cls: "migu-flexible",
			tpl: "#migu-side-menu-tpl",
			wrap: ".migu-side",
			menu: "#migu-side-menu",
			layout: "#migu-layout",
			shrBtn: "#migu-side-shrink",
			shrLeft: "layui-icon-spread-left",
			shrRight: "layui-icon-shrink-right",
			item: "layui-nav-item",
			itemd: "layui-nav-itemed",
			init: function() {
				var t = this;
				$(t.tpl)[0] && (laytpl($(t.tpl).html()).render(HSMENUS, (function(html) {
					var a;
					if ($(t.menu).append(html), element.render(), $(".side-menu-layout")[0]) {
						var p = $(t.menu + ' a[href="' + HSURL + '"]').parent("dd");
						if (p[0]) p.addClass("layui-this").parents("." + t.item).addClass(t.itemd).siblings().removeClass(t.itemd),
						obj = p.parents(".side-menu-layout");
						else var obj = (p = $(t.menu + ' a[href="' + HSURL + '"]').parent("li")).addClass("layui-this").parents(".side-menu-layout");
						obj.show().siblings(".side-menu-layout").hide(),
						$("#hs-first-menu" + obj.attr("data-id")).addClass("layui-this").siblings().removeClass("layui-this")
					} else $(t.menu + ' a[href="' + HSURL + '"]').parent("dd").addClass("layui-this").parents("." + t.item).addClass(t.itemd);
					$(".migu-side ." + t.item).on("click", (function() {
						$(this).siblings("." + t.item).removeClass(t.itemd)
					}))
				})), $(t.shrBtn).on("click", (function() {
					var that = $(this);
					that.hasClass(t.shrRight) ? ($(t.layout).addClass(t.cls), $(t.wrap + " .layui-nav-item").removeClass(t.itemd), that.removeClass(t.shrRight).addClass(t.shrLeft)) : ($(t.layout).removeClass(t.cls), that.removeClass(t.shrLeft).addClass(t.shrRight))
				})), $(".migu-body-shade").on("click", (function() {
					$(t.layout).removeClass(t.cls),
					$(t.shrBtn).removeClass(t.shrLeft).addClass(t.shrRight)
				})), $(document).on("click", ".hs-first-menu-a", (function() {
					var obj = $("#migu-tree-menu" + $(this).attr("data-id"));
					obj.show().children("li").eq(0).children(".layui-nav-child")[0] && obj.show().children("li").eq(0).addClass(t.itemd),
					obj.siblings("ul").hide()
				})), $(t.wrap).hover((function() {
					$(window).width() > 760 && $(t.layout).hasClass(t.cls) && $(this).addClass("float-show")
				}), (function() {
					$(window).width() > 760 && $(t.layout).hasClass(t.cls) && $(this).removeClass("float-show").find("." + t.itemd).removeClass(t.itemd)
				})))
			}
		},
		lockscreen: function() {
			if (!$(".lock-screen", parent.document)[0]) {
				var doc = function(bool) {
					document.oncontextmenu = new Function("event.returnValue=" + bool + ";"),
					document.onselectstart = new Function("event.returnValue=" + bool + ";")
				};
				doc(!1),
				layer.open({
					title: !1,
					type: 1,
					content: '<form action="#" id="migu-unlocked"><div class="lock-screen"><input type="password" class="layui-input" placeholder="请输入登录密码解锁..." autocomplete="on"><button type="submit" class="layui-btn">解锁</button></div></form>',
					closeBtn: 0,
					shade: .95,
					offset: "350px"
				}),
				$("#migu-unlocked").submit((function() {
					var obj = $(this).find("input");
					return !! obj.val() && ($.post(HSROOT + "/system/publics/unlocked", {
						password: md5.exec(obj.val())
					},
					(function(res) {
						1 == res.code ? (window.sessionStorage.setItem("lockscreen", !1), doc(!0), layer.closeAll()) : obj.attr("placeholder", res.msg).val("")
					})), !1)
				}))
			}
		},
		tableTree: {
			close: "layui-icon-triangle-r",
			open: "layui-icon-triangle-d",
			tpl: "#migu-tr-tpl",
			pField: "pid",
			url: "",
			init: function(c) {
				var opt = Object.assign({},
				this, c);
				$(document).on("click", ".migu-tr-tree-btn", (function() {
					var t = $(this),
					p = parseInt(t.attr("data-pid")),
					f = function(data, t) {
						var d = {
							data: data,
							class: t.attr("data-class"),
							level: parseInt(t.attr("data-level")) + 1
						};
						laytpl($(opt.tpl).html()).render(d, (function(html) {
							t.parents("tr").after(html),
							form.render()
						}))
					};
					if (t.hasClass(opt.close)) {
						t.removeClass(opt.close).addClass(opt.open);
						var data = {};
						if (t.attr("data-childs")[0]) {
							var childs = new Function("return " + t.attr("data-childs"))() || {};
							f(childs, t)
						} else opt.url && $.get(opt.url, opt.pField + "=" + p, (function(res) {
							f(res.data, t)
						}), "json")
					} else t.removeClass(opt.open).addClass(opt.close).parents("tr").siblings(".tr-index-" + p).remove()
				}))
			}
		},
		tableEdit: {
			filter: "dataTable",
			id: "id",
			model: "",
			table: "",
			url: HSROOT + "/" + HSMODULE + "/" + HSCONTROLLER + "/setField",
			init: function(c) {
				var t = Object.assign({},
				this, c);
				table.on("edit(" + t.filter + ")", (function(o) {
					$.post(t.url, {
						id: o.data[t.id],
						field: o.field,
						value: o.value,
						miguModel: t.model,
						miguTable: t.table
					},
					(function(res) {
						0 == res.code && layer.msg(res.msg)
					}), "json")
				}))
			}
		},
		tableTool: function(miguTable, toolbars) {
			miguTable.on("toolbar(dataTable)", (function(obj) {
				switch (obj.event) {
				case "MIGUTABLE_SEARCH":
					var s = $(".migu-search");
					s[0] && (s.hasClass("hide") ? s.removeClass("hide") : s.addClass("hide"));
					break;
				case "MIGUTABLE_REFRESH":
					location.reload();
					break;
				case "MIGUTABLE_EXPORT":
					var opt = Object.assign({},
					{
						format: "csv",
						type: "def"
					},
					toolbars[obj.event] || {});
					if ("raw" == opt.type) {
						var title = [],
						field = [],
						data = [];
						layui.each(obj.config.cols[0], (function(k, v) {
							v.field && v.title && (title.push(v.title), field.push(v.field))
						})),
						layui.each(miguTable.cache.dataTable, (function(k, v) {
							var arr = [];
							layui.each(field, (function(kk, vv) {
								arr.push(v[vv] || "")
							})),
							data.push(arr)
						})),
						data.length ? miguTable.exportFile(title, data, opt.format) : notice.info("没有可导出数据")
					} else miguTable.exportFile(obj.config.id, miguTable.cache.dataTable, opt.format);
					break;
				default:
					toolbars[obj.event] && toolbars[obj.event].callback && toolbars[obj.event].callback(obj)
				}
			}))
		},
		init: function() {
			self.firstMenu(),
			self.checkDevice(),
			self.side.init(),
			self.tabs.init(),
			layer.config({
				offset: "20px"
			}),
			$(window).resize((function() {
				self.firstMenu()
			})),
			$(document).on("click", ".hs-side-a", (function() {
				window.localStorage.setItem("ANHL", $(this).attr("href"))
			}));
			var anhl = window.localStorage.getItem("ANHL");
			if (anhl && $(".side-menu-layout")[0] && !$("#migu-side-menu .layui-this")[0]) {
				var a = $('#migu-side-menu a[href="' + anhl + '"]');
				$("#migu-side-menu .layui-nav-item").removeClass("layui-this"),
				a.parent("dd").addClass("layui-this").parents("li.layui-nav-item").addClass("layui-nav-itemed").siblings("li").removeClass(".layui-nav-itemed"),
				a.parents("ul.side-menu-layout").show().siblings("ul").hide()
			}
			var storage = window.sessionStorage;
			$("#migu-lock-screen").click((function() {
				storage.setItem("lockscreen", !0),
				self.lockscreen()
			})),
			"true" == storage.getItem("lockscreen") && self.lockscreen()
		}
	};
	$(".help-tips").click((function() {
		return layer.tips($(this).attr("data-title"), this, {
			tips: [3, "#009688"],
			time: 5e3
		}),
		!1
	})),
	form.on("checkbox(allChoose)", (function(data) {
		var child;
		$(data.elem).parents("table").find("tbody input.checkbox-ids").each((function(index, item) {
			item.checked = data.elem.checked
		})),
		form.render("checkbox")
	})),
	$("#migu-theme-setting").on("click", (function() {
		var that = $(this);
		return layer.open({
			type: 5,
			title: "主题方案",
			shade: .3,
			area: ["200px", "93%"],
			offset: "rb",
			maxmin: !0,
			shadeClose: !0,
			closeBtn: !1,
			anim: 2,
			content: $("#migu-theme-tpl").html(),
			success: function(layero, index) {
				var def = $("#themeLink").attr("theme").substring(11);
				$(".migu-themes").children(".migu-theme-" + def).addClass("active"),
				$(".migu-themes li").on("click", (function() {
					var t = $(this),
					theme = t.attr("data-theme");
					$.get(that.attr("href"), {
						theme: theme
					},
					(function(res) {
						0 == res.code ? layer.msg(res.msg) : ($("#themeLink").prop({
							href: "/static/system/css/theme/" + theme + ".css",
							theme: theme
						}), t.addClass("active").siblings().removeClass("active"))
					}), "json")
				}))
			}
		}),
		!1
	})),
	$("#migu-clear-cache").on("click", (function() {
		var that = $(this);
		return layer.open({
			type: 5,
			title: "删除缓存",
			shade: .3,
			area: ["205px", "93%"],
			offset: "rb",
			maxmin: !0,
			shadeClose: !0,
			closeBtn: !1,
			anim: 2,
			content: $("#migu-clear-cache-tpl").html(),
			success: function(layero, index) {
				form.render("checkbox")
			}
		}),
		!1
	})),
	$(document).on("click", ".j-del-menu,.migu-del-menu", (function() {
		var that = $(this);
		layer.confirm("删除之后无法恢复，您确定要删除吗？", {
			title: !1,
			closeBtn: 0
		},
		(function(index) {
			$.post(that.attr("data-href"), (function(res) {
				layer.msg(res.msg),
				1 == res.code && that.parents("dd").animate({
					left: "-1000px"
				},
				(function() {
					$(this).remove()
				}))
			})),
			layer.close(index)
		}))
	})),
	$(document).on("click", ".j-iframe-pop,.migu-iframe-pop,.migu-iframe", (function() {
		var t = $(this),
		query = "",
		def = {
			width: "60%",
			height: "80%",
			idSync: !1,
			table: "dataTable",
			type: 2,
			offset: "auto",
			url: t.attr("href"),
			title: t.attr("title") || t.text()
		},
		table_id = t.attr("data-table_id") ? t.attr("data-table_id") : "id";
		opt = new Function("return " + t.attr("migu-data"))() || def;
		if (opt = Object.assign({},
		def, opt), $(document).width() < 768 && (opt.width = "90%"), !opt.url) return layer.msg("请设置href参数"),
		!1;
		if (opt.idSync) {
			var checkStatus = table.checkStatus(opt.table);
			if (checkStatus.data.length) for (var i in checkStatus.data) query += "&id[]=" + checkStatus.data[i][table_id];
			if (!query) return layer.msg("请选择要操作的数据"),
			!1
		}
		return opt.url.indexOf("?") >= 0 ? opt.url += "&migu_iframe=yes": opt.url += "?migu_iframe=yes",
		layer.open({
			type: opt.type,
			title: opt.title,
			content: opt.url + query,
			offset: opt.offset,
			area: [opt.width, opt.height]
		}),
		!1
	})),
	form.on("switch(switchStatus)", (function(data) {
		var that = $(this),
		status = 0,
		func = function() {
			$.get(that.attr("data-href"), {
				val: status
			},
			(function(res) {
				layer.msg(res.msg),
				0 == res.code && (that.trigger("click"), form.render("checkbox"))
			}))
		};
		if (void 0 === that.attr("data-href")) return layer.msg("请设置data-href参数"),
		!1;
		this.checked && (status = 1),
		void 0 === that.attr("confirm") ? func() : layer.confirm(that.attr("confirm") || "你确定要执行操作吗？", {
			title: !1,
			closeBtn: 0
		},
		(function(index) {
			func()
		}), (function() {
			that.trigger("click"),
			form.render("checkbox")
		}))
	})),
	form.on("submit(formSubmit)", (function(data) {
		var t = $(this),
		c = {
			pop: !1,
			refresh: !0,
			url: null,
			time: 3e3,
			prompt: 1,
			token: "__token__",
			callback: !1,
			captcha: ""
		},
		f = t.parents("form"),
		txt = t.text(),
		opt = t.attr("migu-data") ? func.jsonParse(t.attr("migu-data")) : {},
		submit = function(fdata) {
			if ("undefined" != typeof CKEDITOR) for (instance in CKEDITOR.instances) CKEDITOR.instances[instance].updateElement();
			t.text("提交中...").removeClass("layui-btn-normal").addClass("layui-btn-disabled").prop("disabled", !0),
			$.ajax({
				type: "POST",
				url: f.attr("action"),
				data: fdata || f.serialize(),
				success: function(res) {
					t.removeClass("layui-btn-disabled").addClass("layui-btn-normal"),
					0 == res.code ? (res.data.token && $('input[name="' + opt.token + '"]')[0] && $('input[name="' + opt.token + '"]').val(res.data.token), res.data.captcha && opt.captcha && $(opt.captcha).attr("src", res.data.captcha + "#rand=" + Math.random()), 1 == opt.prompt ? layer.msg(res.msg) : t.text(res.msg).removeClass("layui-btn-normal").addClass("layui-btn-danger"), setTimeout((function() {
						t.prop("disabled", !1).removeClass("layui-btn-danger").addClass("layui-btn-normal").text(txt)
					}), 3e3)) : (t.prop("disabled", !1), cb = opt.callback, "function" == typeof cb ? opt.callback(t, res) : (1 == opt.prompt ? (t.text(txt), layer.msg(res.msg, {
						time: opt.time
					})) : t.text(res.msg), opt.refresh && setTimeout((function() {
						opt.pop ? (parent.layer.closeAll(), parent.miguTableIns ? parent.miguTableIns.reload({},
						!0) : opt.url ? parent.location.href = opt.url: res.url && res.url != window.location.href ? parent.location.href = res.url: parent.location.reload()) : opt.url ? location.href = opt.url: res.url && res.url != window.location.href ? location.href = res.url: location.reload()
					}), opt.time)))
				}
			})
		};
		return "function" == typeof(opt = Object.assign({},
		c, opt)).before ? opt.before(data, submit) : submit(),
		!1
	})),
	$(document).on("click", ".j-tr-del,.migu-tr-del", (function() {
		var t = $(this),
		h = t.attr("href") || "";
		return layer.confirm("删除之后无法恢复，您确定要删除吗？", {
			title: !1,
			closeBtn: 0
		},
		(function(index) {
			if ("" == h || "javascript:;" == h) return t.parents("tr").remove(),
			layer.close(index),
			!1;
			$.post(h, (function(res) {
				0 == res.code ? layer.msg(res.msg) : t.parents("tr").remove()
			})),
			layer.close(index)
		})),
		!1
	})),
	$(document).on("click", ".j-ajax,.migu-ajax", (function() {
		var that = $(this),
		href = that.attr("data-href") ? that.attr("data-href") : that.attr("href"),
		refresh = that.attr("refresh") ? that.attr("refresh") : "true";
		return href ? (that.attr("confirm") ? layer.confirm(that.attr("confirm"), {
			title: !1,
			closeBtn: 0
		},
		(function(index) {
			layer.msg("数据提交中...", {
				time: 5e5
			}),
			$.get(href, {},
			(function(res) {
				layer.msg(res.msg, {},
				(function() {
					"true" != refresh && "yes" != refresh || (void 0 !== res.url && null != res.url && "" != res.url ? location.href = res.url: location.reload())
				}))
			})),
			layer.close(index)
		})) : (layer.msg("数据提交中...", {
			time: 5e5
		}), $.get(href, {},
		(function(res) {
			layer.msg(res.msg, {},
			(function() {
				"true" != refresh && "yes" != refresh || (void 0 !== res.url && null != res.url && "" != res.url ? location.href = res.url: location.reload())
			}))
		})), layer.close()), !1) : (layer.msg("请设置data-href参数"), !1)
	})),
	$(".j-auto-checked,migu-auto-checked").blur((function() {
		var that = $(this);
		that.attr("data-value") != that.val() ? that.parents("tr").find('input[name="ids[]"]').attr("checked", !0) : that.parents("tr").find('input[name="ids[]"]').attr("checked", !1),
		form.render("checkbox")
	})),
	$(document).on("focusout", ".j-ajax-input,.migu-ajax-input", (function() {
		var that = $(this),
		_val = that.val();
		return "" != _val && that.attr("data-value") != _val && (that.attr("data-href") ? void $.post(that.attr("data-href"), {
			val: _val
		},
		(function(res) {
			1 == res.code && that.attr("data-value", _val),
			layer.msg(res.msg)
		})) : (layer.msg("请设置data-href参数"), !1))
	})),
	$(".tooltip").hover((function() {
		var that;
		$(this).find("i").show()
	}), (function() {
		var that;
		$(this).find("i").hide()
	})),
	$(document).on("click", ".j-page-btns,.migu-page-btns,.migu-table-ajax", (function() {
		var that = $(this),
		query = "",
		code = function(that) {
			var href = that.attr("href") ? that.attr("href") : that.attr("data-href"),
			table_id = that.attr("data-table_id") ? that.attr("data-table_id") : "id";
			tableObj = that.attr("data-table") ? that.attr("data-table") : "dataTable";
			if (!href) return layer.msg("请设置data-href参数"),
			!1;
			if ($(".checkbox-ids:checked").length <= 0) {
				var checkStatus = table.checkStatus(tableObj);
				if (checkStatus.data.length <= 0) return layer.msg("请选择要操作的数据"),
				!1;
				for (var i in checkStatus.data) i > 0 && (query += "&"),
				query += "id[]=" + checkStatus.data[i][table_id]
			} else query = that.parents("form")[0] ? that.parents("form").serialize() : $("#pageListForm").serialize();
			layer.msg("数据提交中...", {
				time: 5e5
			}),
			$.post(href, query, (function(res) {
				layer.msg(res.msg, {},
				(function() {
					0 != res.code && location.reload()
				}))
			}))
		};
		if (that.hasClass("confirm")) {
			var tips = that.attr("tips") ? that.attr("tips") : "操作不可逆，您确定要执行吗？";
			layer.confirm(tips, {
				title: !1,
				closeBtn: 0
			},
			(function(index) {
				code(that),
				layer.close(index)
			}))
		} else code(that);
		return ! 1
	})),
	$(document).on("submit", "#miguSearch,#migu-table-search", (function() {
		var t = $(this),
		a = t.serializeArray(),
		w = new Array,
		obj = t.attr("data-table") ? t.attr("data-table") : "dataTable",
		o = new Function("return " + t.attr("migu-data"))() || {
			page: {
				curr: 1
			}
		};
		for (var i in a) w[a[i].name] = a[i].value;
		return o.url = t.attr("action"),
		o.where = w,
		table.reload(obj, o),
		!1
	})),
	$(document).on("click", ".migu-table-a-filter", (function() {
		var t = $(this),
		obj = t.attr("data-table") ? t.attr("data-table") : "dataTable",
		o = new Function("return " + t.attr("migu-data"))() || {
			page: {
				curr: 1
			}
		};
		return o.url = t.attr("href"),
		table.reload(obj, o),
		!1
	})),
	$(".migu-quote-close").on("click", (function() {
		$(this).parent("blockquote").fadeOut()
	})),
	$(document).on("mouseover", ".migu-img-preview", (function(e) {
		var t = $(this),
		opt = t.attr("migu-options") ? func.jsonParse(t.attr("migu-options")) : {
			width: "",
			height: ""
		},
		d = "<div id='migu-img-preview' style='position:absolute'><img src='" + this.src + "' width='" + (opt.width || "") + "' height='" + (opt.height || "") + "' /></div>";
		$("body").append(d),
		self.imgPreview(e)
	})).on("mouseout", ".migu-img-preview", (function() {
		$("#migu-img-preview").remove()
	})).on("mousemove", ".migu-img-preview", (function(e) {
		self.imgPreview(e)
	})),
	self.init(),
	exports("global", self)
}));