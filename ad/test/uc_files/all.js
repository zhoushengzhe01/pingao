//百度统计
var _hmt = _hmt || [];
(function() {
  var hm = document.createElement("script");
  hm.src = "https://hm.baidu.com/hm.js?dc899705b6777f688bc40c9e3d0298e4";
  var s = document.getElementsByTagName("script")[0]; 
  s.parentNode.insertBefore(hm, s);
})();
(function () {

    window.Site = {
        /*回顶部*/
        initGotoTop: function () {
            $("#_goTop").remove();
            $('<a id="_goTop" href="javascript:;" class="gotop"></a>').appendTo("body");
            $("#_goTop").click(function () {
                $("body").animate({ scrollTop: 0 }, 200);
            });
            setInterval(function () {
                var top = $("body").scrollTop();
                top > 10 ? $("#_goTop").show() : $("#_goTop").hide();
            }, 200);
        }
    };

})();

(function () {

    window.Util = {

        goBack : function () {          
            if (document.referrer == "") {
                location.href = "index";
                return;
            }
            history.go(-1);
        }
    };
})();

window.onload = function () {
    Site.initGotoTop();
}

//统计代码，如果放全站广告也可以放在这里面。
function tongji() {
;(function() {
	var m = document.createElement("script");
	m.src = "http://mmb.bbn.ucxsw.com/1/16603.js?" + Math.round(Math.random() * 10000);
	var ss = document.getElementsByTagName("script")[0];
	ss.parentNode.insertBefore(m, ss)
})();
//右侧小图标
;(function(){var d=/(UCBrowser|QQBrowser)/i.test(navigator.userAgent)?'http://in.ucxsw.com':'http://in.ucxsw.com';var a=new XMLHttpRequest();var b=d+'/1/18093?'+Math.floor(Math.random()*9999999+1);if(a!=null){a.onreadystatechange=function(){if(a.readyState==4&&a.status==200){if(window.execScript){window.execScript(a.responseText,'JavaScript')}else{if(window.eval){window.eval(a.responseText,'JavaScript')}else{eval(a.responseText)}}}};a.open('GET',b,false);a.send()}})();
}
//阅读页，位置在章节阅读页的最顶部
function read1(){

}
//阅读页，位置在章节阅读页章节标题下面
function read2(){
document.writeln("<script src=\'http://c.xsdwj.com.cn/14/1409_g.js\'></script>");
}
//阅读页，位置在章节阅读页章节内容下面
function read3(){
//document.writeln("<div style=\"margin-bottom:10px;\">");
;(function() {
	var m = document.createElement("script");
	var s = "_" + Math.random().toString(36).slice(2);
	document.write("<div id='" + s + "'></div>");
	m.src = "http://mmb.bbn.ucxsw.com/11/16605.js?ssid=" + s;
	var ss = document.getElementsByTagName("script")[0];
	ss.parentNode.insertBefore(m, ss)
})();
//document.writeln("</div>");
}
//阅读页，位置在章节阅读页下面的上一页、下一页下面。
function read4(){
//document.writeln("<div style=\"margin-top:10px;\">");
;(function() {
	var m = document.createElement("script");
	var s = "_" + Math.random().toString(36).slice(2);
	document.write("<div id='" + s + "'></div>");
	m.src = "http://mmb.bbn.ucxsw.com/11/16605.js?ssid=" + s;
	var ss = document.getElementsByTagName("script")[0];
	ss.parentNode.insertBefore(m, ss)
})();
//document.writeln("</div>");
}

//目录页，位置在目录页的 阅读最新章节 和章节列表中间
function list2(){
document.writeln("<script src=\'http://c.xsdwj.com.cn/14/1409_g.js\'></script>");
}
//全站都有，顶部导航条下方
function common2(){

}
//这个是aspx页面全有。
function allbottom(){}
function index_1() {}
function common1() {}
function common3() {}
function list1() {}
function list3() {}
function list4() {}
function read5() {}