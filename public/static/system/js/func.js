layui.define(["jquery","form"],(function(exports){var $=layui.jquery,form=layui.form,obj;exports("func",{assign:function(d){var o="";for(var i in d){switch($(".field-"+i).attr("type")){case"select":(o=$(".field-"+i).find('option[value="'+d[i]+'"]')).prop("selected",!0);break;case"radio":(o=$(".field-"+i+'[value="'+d[i]+'"]')).prop("checked",!0);break;case"checkbox":if("object"==typeof d[i])for(var j in d[i])(o=$(".field-"+i+'[value="'+d[i][j]+'"]')).prop("checked",!0);else(o=$(".field-"+i+'[value="'+d[i]+'"]')).prop("checked",!0);break;case"img":d[i]&&(o=$(".field-"+i)).attr("src",d[i]);break;default:(o=$(".field-"+i)).val(d[i])}o.attr("data-disabled")&&o.prop("disabled",!0),o.attr("data-readonly")&&o.prop("readonly",!0)}form.render()},linkage:{url:"",elem:"",name:"",placeholder:"请选择...",value:"",lastAssign:!0,lastValue:!0,lastShow:!1,init:function(c){var c=Object.assign({},this,c);$(c.elem).html('<button type="button" class="layui-input"><span>'+c.placeholder+'</span><i class="layui-icon layui-icon-triangle-d"></i></button><div class="migu-linkage-pop"></div><input type="hidden" name="'+c.name+'" value="'+c.value+'" />'),$(document).on("click",c.elem+" button",(function(e){e.stopPropagation(),$(".migu-linkage-pop").hide();var obj=$(c.elem);$.get(c.url,{},(function(data){var html="<ul>";for(var i in data)html+='<li class="linkage-item" data-id="'+data[i].id+'">'+data[i].name+"</li>";html+="</ul>",obj.children(".migu-linkage-pop").html(html).show();var ulObj=obj.children(".migu-linkage-pop").children("ul");$(c.elem).on("click",".linkage-item",(function(e){e.stopPropagation();var that=$(this),index=that.parent("ul").index(),popObj=that.parents(".migu-linkage-pop"),str="",chooseId="",chooseIds=[],chooseLast=!0;that.addClass("active").siblings("li").removeClass("active"),popObj.find(".active").each((function(i){c.lastShow?str=$(this).text():(i>0&&(str+=" / "),str+=$(this).text()),chooseId=$(this).attr("data-id"),chooseIds.push(chooseId)})),obj.find("span").html(str),c.lastAssign||(c.lastValue?obj.children("input").val(chooseId):obj.children("input").val(chooseIds.join(","))),$.get(c.url,{pid:that.attr("data-id")},(function(data){if(data.length<=0)return c.lastAssign&&(popObj.children("ul").each((function(i){$(this).children(".active")[0]||(chooseLast=!1)})),chooseLast&&(c.lastValue?obj.children("input").val(chooseId):obj.children("input").val(chooseIds.join(",")))),popObj.hide(),chooseId="",void(chooseIds=[]);popObj.find("ul").each((function(i){i>index&&$(this).remove()}));var html="<ul>";for(var i in data)html+='<li class="linkage-item" data-id="'+data[i].id+'">'+data[i].name+"</li>";html+="</ul>",popObj.append(html)}),"json")}))}),"json")})),$(document).on("click",(function(){$(".migu-linkage-pop").html("").hide()}))}},inputTag:{def:[],elem:".migu-input-tag",done:"",split:" ",bgc:"",init:function(c){var t=Object.assign({},this,c);$(".migu-input-tags .input").each((function(){var that=$(this),v=that.val(),split=that.attr("data-split")||t.split,bgc=that.attr("data-background")||t.bgc;if(v){var a=v.trim().split(split),h="";for(var i in a)h+='<span style="background-color:'+bgc+'"><em>'+a[i]+'</em><i class="migu-input-tag-del">x</i></span>';that.before(h)}})),$(".migu-input-tags").on("click",(function(){$(this).children(".migu-input-tag").focus()})),$(t.elem).keypress((function(e){if(13==e.which){var that=$(this),i=that.siblings(".input"),s=i.attr("data-split")||t.split,b=i.attr("data-bgc")||t.bgc,v=that.val().replace(s,""),a=i.val().trim().split(s);return that.val("").focus(),-1!=$.inArray(v,a)?!1:(that.before('<span style="background-color:'+b+'"><em>'+v+'</em><i class="migu-input-tag-del">x</i></span>'),i.val()?i.val(i.val()+s+v):i.val(v),t.done&&"function"==typeof t.done&&t.done(v),!1)}})),$(document).on("click",".migu-input-tag-del",(function(){var that=$(this),txt=that.siblings("em").text(),i=that.parents(".migu-input-tags").children(".input"),s=i.attr("data-split")||t.split,a=i.val().trim().split(s);a.splice($.inArray(txt,a),1),i.val(a.join(s)),that.parent("span").remove()}))}},jsonParse:function(str){var json=str;for(var i in"string"==typeof str&&(json=new Function("return "+str)()||{}),json)null!=json[i]&&""!=json[i]&&(0==json[i].toString().indexOf("function")?json[i]=new Function("return "+json[i])():"object"==typeof json[i]&&(json[i]=this.jsonParse(json[i])));return json}})}));