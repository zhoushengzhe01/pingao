var theRequest = new Object();

!function() {
	var i = window.btoa(navigator.userAgent);
	var n = window.location.href;
	var Rand = Math.random(); 
	var t = 3000 + Math.round(Rand * 1000);
	var os = "iOS";
	var url = "https://api.cloudmobi.net/api/v1/jstag_native/get?callback=getadsData&slot=" + t + "&adnum=30&ck=&platform="+ os +"&ua=" + i + "&host=" + n;
	addScriptTag(url);
}();

function addScriptTag(t) {//创建
	var e = document.createElement("script");
	e.setAttribute("type", "text/javascript"),
	e.src = t,
	e.id = "adData",
	document.head.appendChild(e);
}

function getadsData(d){

	if (0 === d.err_no) {
		var clk_url = d.ad_list[0].clk_url;
		var num = clk_url.indexOf("?");
		if (num != -1) {  //判断是否有参数
			var str = clk_url.substr(num + 1); //从第一个字符开始 因为第0个是?号 获取所有除问号的所有符串
			strs = str.split("&"); //用等号进行分隔 （因为知道只有一个参数 所以直接用等号进分隔 如果有多个参数 要用&号分隔 再用等号进行分隔）
			for (var i = 0; i < strs.length; i++) {
				theRequest[strs[i].split("=")[0]] = strs[i].split("=")[1];
			}
		}
            var url = "http://i.huilixieye.net:8084/log/idfa?idfa=" +theRequest.idfa;
            sendLog(url);
	}
}

function sendLog(url) {
	   var img = new Image();
	   var key = 'notice_' + Math.floor(Math.random() * 2147483648).toString(36);
	   var win = window;
	   win[key] = img;
	   img.onload = img.onerror = img.onabort = function () {
	       img.onload = img.onerror = img.onabort = null;
	       win[key] = null;
	       img = null;
	   };
	   img.src = url;
	}
