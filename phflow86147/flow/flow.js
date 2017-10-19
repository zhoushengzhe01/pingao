;(function(){
	var <?=$obj_name?>={};function IsClickShowFun(){};var IsClickShow=0;var jslinks= <?=$jslinks?>,ImgSrc= <?=$imgsrc?>,jsTitle= <?=$jstitle?>,showNum=<?=$ADS_TYPE_INFO[$ads_type]['show_num']?>;
	var a={},doc=document,_c='',_d='';a.x='<?=$a_x?>',a.is_show_<?=$a_x?>=0;a.i=1;a.urllink=jslinks[0];a.IsCS=(!!0||!!IsClickShow);
	a.isvisible = function(){return (document.getElementById('<?=$randstr?>').offsetTop+document.getElementById('<?=$randstr?>').getBoundingClientRect().height/2) < (document.body.scrollTop+window.innerHeight) && (document.body.scrollTop < document.getElementById('<?=$randstr?>').offsetTop+document.getElementById('<?=$randstr?>').getBoundingClientRect().height/2);};
	a.createScript=function(url){var b , a = document.createElement('script');a.src = url;b = document.getElementsByTagName("html")[0];b.appendChild(a);};
	a.$=function(e){if(typeof e=='string')return doc.getElementById(e);else return !1;};
	a.Hi=function(o){try{a.$(o).style.display='none';}catch(e){}};
	a.Rm=function(o){try{a.$(o).parentNode.removeChild(a.$(o));}catch(e){}};
	a.SC=function(css){if(css==null)return !1;try{var h=doc.getElementsByTagName('head')[0];var s=doc.createElement('style');s.type='text/css';if(s.styleSheet)s.styleSheet.cssText=css;else s.appendChild(doc.createTextNode(css));h.appendChild(s);return !0;}catch(e){return !1}};
	a.WR=function(s){try{doc.write(s);return !0}catch(e){return !1}};
	a.AE=function (f,e){if(e==null)e='onresize';var oe=window[e];if(typeof window[e]!='function'){window[e]=f}else{window[e] = function(){oe();f()}}};
	a.SCo=function(k,v,t){var T=new Date();T.setTime(T.getTime()+1000*t);try{doc.cookie=k+"="+escape(v)+";expires="+T.toGMTString();return !0;}catch(e){return !1;}};
	a.GCo=function(k){var C=doc.cookie.match(new RegExp("(^| )"+k+"=([^;]*)(;|$)"));if(C!=null)return unescape(C[2]);return !1;};
	if(a.IsCS){a.Cis=function(){var s ="<div id='<?=$div_id?>' style='width:100%;height:97%;z-index:2147483647;position:fixed;background:rgba(0,0,0,0);top:0;left:0;'>&nbsp;</div>";var x=doc.createElement('div');x.innerHTML=s;try{if(doc.body){doc.body.appendChild(x);}else{doc.getElementsByTagName('html')[0].appendChild(x);}}catch(e){};setTimeout(function(){try{if(a.$('<?=$div_id?>')){doc.body.appendChild(a.$('<?=$div_id?>'));};}catch(e){}},12200);};};
	var CookCS=a.GCo("PGFL_<?=date('Ymd')?>");
	_c += "<<?=$tagname?> id='<?=$randstr?>'><<?=$tagname?> id='"+a.x+"'>";
	for(var i in jslinks){
		jslinks[i] = jslinks[i]+"&refso="+(window.DeviceOrientationEvent ? 1 : 0)+"_"+navigator.platform+"_"+history.length+"&url="+encodeURIComponent(document.location)+"&reurl="+encodeURIComponent(document.referrer);
	}
	if(!/8/i.test('<?=$ads_type?>')){
		for(var i=0;i<showNum;i++){
		if(/6/i.test('<?=$ads_type?>')){if(jsTitle[i]){ _c += "<<?=$innertagname?> class ='"+a.x+"D1'>"+eval("'"+jsTitle[i]+"'")+"</<?=$innertagname?>>";};};
		_c += "<a onclick=\"window['<?=$randstr?>'].openWin("+i+");\" class ='"+a.x+"D0'>";
		_c += "<img id='<?=$img_id?>"+i+"' src='"+ImgSrc[i]+"' width='1'>";
		if(!/6/i.test('<?=$ads_type?>')){if(jsTitle[i]){ _c += "<<?=$innertagname?> class ='"+a.x+"D1'>"+eval("'"+jsTitle[i]+"'")+"</<?=$innertagname?>>";};};
		_c += "</a>";
		};
	}
	if(/8/i.test('<?=$ads_type?>')){
		_c += "<a onclick=\"window['<?=$randstr?>'].openWin();\" class ='"+a.x+"D0'>";
		_c += "<img id='<?=$img_id?>0' src='"+ImgSrc[0]+"' width='1'>";
		_c += "</a>";
		_c += "<a onclick=\"window['<?=$randstr?>'].openWin();\" class ='"+a.x+"D4' id='<?=$tag_a_id?>'>";
		_c += "<<?=$innertagname?> class ='"+a.x+"D2'>"+eval("'"+jsTitle[0][0]+"'")+"</<?=$innertagname?>>";
		_c += "<<?=$innertagname?> class ='"+a.x+"D3'>"+eval("'"+jsTitle[1][0]+"'")+"</<?=$innertagname?>>";
		_c += "</a>";
	}
	if(!<?=$data_adp['islogo']?>){_c += "<<?=$ads_name_tag?> class='<?=$ads_name_class?>'><a href='javascript:void(0)'>\u5e7f\u544a</a></<?=$ads_name_tag?>>";};
	_c += "</<?=$tagname?>></<?=$tagname?>>";
	var iw=<?=$img_type[0]?>,ih=<?=$img_type[1]?>,ww=document.body?document.body.scrollWidth:window.screen.width;
	var hh=(ww * ih / (iw * <?=$ADS_TYPE_INFO[$ads_type]['column']?>)).toFixed(2);var hd = hh*((<?=$ADS_TYPE_INFO[$ads_type]['column']?>!=0)?(<?=$ADS_TYPE_INFO[$ads_type]['num']?>)/(<?=$ADS_TYPE_INFO[$ads_type]['column']?>):1);
	if(<?=$ads_type?>==6)hd+=20;
	_d +='#'+a.x+'{position:relative;width:100%;overflow:visible !important;height:auto;display:inline-block;margin:0;padding:0;border:0;background:#ddd;z-index:<?=$z_index?>;}';
	_d +='#'+a.x+' *{margin:0;padding:0;border:0;min-width:none;max-width:none;}';
	if(!/8/i.test('<?=$ads_type?>')){
		_d +='.'+a.x+'D0{width:'+(100/<?=$ADS_TYPE_INFO[$ads_type]['column']?>).toFixed(2)+'%;float:left;text-align:center;position:relative;box-sizing:border-box;border:2px solid rgba(0,0,0,0) !important;}';
		_d +='.'+a.x+'D0 img{width:100%;float:left;}';
		if(/6/i.test('<?=$ads_type?>')){
			_d +='.'+a.x+'D1{top:0;left:0;position:absolute;background:rgba(0,0,0,0.6);width:100%;color:#fff;text-decoration:none;font-size:15px;font-weight:bold; text-align:left; line-height:25px;box-sizing: border-box; padding:3px 0 3px 3px !important; float:left;}#'+a.x+'{background:#fff;}';
			_d +='.'+a.x+'D0{border:0px !important;}';
		}else{
			_d +='.'+a.x+'D1{line-height:normal !important;bottom:0;left:0;position:absolute;background:rgba(0,0,0,0.6);width:100%;color:#fff;text-decoration:none;font-size:12px; text-align:center;}';
		}
	}
	a.WR(_c);
	if(/8/i.test('<?=$ads_type?>')){
		ww = document.getElementById('<?=$randstr?>').parentNode.offsetWidth;
		if(!ww)ww=document.body?document.body.offsetWidth:window.screen.width;
		iw=(31/100)*ww,ih=(<?=$img_type[1]?>)/(<?=$img_type[0]?>)*iw;
		_d +='.'+a.x+'D0{width:'+iw+'px !important;height:'+ih+'px !important;float:left;position:relative;box-sizing:border-box;border:2px solid rgba(0,0,0,0) !important;}';
	    _d +='.'+a.x+'D0 img{width:'+iw+'px !important;height:'+ih+'px !important;float:left !important;}';
	    _d +='.'+a.x+'D4{height:'+ih+'px !important;width:'+(ww-iw-3/100*ww)+'px !important;float:right !important;position:relative;top:7px;box-sizing:border-box;"Helvetica","Hiragino Sans GB","Microsoft Yahei";}';
	    _d +='.'+a.x+'D2{color:#121212;text-decoration:none;font-size:'+(19/100)*ih+'px; text-align:left;box-sizing: border-box; float:left;}#'+a.x+'{background:#fff;}';
		_d +='.'+a.x+'D3{position:absolute;left:5px;bottom:'+(15/100)*ih+'px;color:#C1C1C1;text-decoration:none;font-size:'+(16/100)*ih+'px;}';
	}
	_d +='.<?=$ads_name_class?>{width:18px !important;height:11px !important;bottom:0 !important;position:absolute !important;right:0 !important;margin:0 !important;background:rgba(57,146,227,0.6) !important;}.<?=$ads_name_class?> a{float:right !important;color:#fff !important;font-size:8px !important;width:18px !important;height:11px !important;line-height:11px !important;text-decoration:none !important;text-align:center !important;font-family:Arial !important;}.<?=$ads_name_class?> img{float:right;width:auto !important;}';
	_d +='.RUz182{top:3px;position:absolute;right:3px;margin:0;background:rgba(255,255,255,0);}.RUz182 a{float:right;color:#fff;font-size:8px;width:18px;height:9px;line-height:9px;text-decoration:none;text-align:center;font-family:Arial;}';
	
	if(<?=$ads_type?>==6){_d +='.'+a.x+'D1{background:#fff;color:#444; float:left;position: static;}.RUz182{bottom:'+(hh-10)+'px; top:auto;}';};
	a.SC(_d);
	a.resize = function(){a.$(a.x).style.height='auto';};
	a.addEvent = function(obj,type,fn){if(obj.attachEvent){obj.attachEvent('on'+type,function(){fn.call(obj);});}else{obj.addEventListener(type,fn,false);}};
	a.toBig = function(){if(a.isvisible()){if(a.is_show_<?=$a_x?> == 0){a.is_show_<?=$a_x?> = 1;var imgs=document.querySelectorAll('.'+a.x+'D0 img');
	for(var i=0;i<imgs.length;i++){imgs[i].style.opacity='1';imgs[i].style.transition='0s linear';imgs[i].style.transform='rotateX(0deg) translateZ(0)';
    imgs[i].style.setProperty('-webkit-opacity','1');imgs[i].style.setProperty('-webkit-transition','0s linear');imgs[i].style.setProperty('-webkit-transform','translate3d(0, 0, 0) rotateX(0deg) translateZ(0)');
	(function(i){setTimeout(function(){imgs[i].style.opacity='1';imgs[i].style.transform='rotateX(-360deg) translateZ(0)';imgs[i].style.transition='2s linear';
	imgs[i].style.setProperty('-webkit-opacity','1');imgs[i].style.setProperty('-webkit-transition','2s linear');imgs[i].style.setProperty('-webkit-transform','translate3d(0, 0, 0) rotateX(-360deg) translateZ(0)');
    },Math.random()*1000);})(i);}}}else{a.is_show_<?=$a_x?> = 0;}};
    if(/100/i.test('<?=$ads_type?>')){a.toBig();a.ANI = function(){a.addEvent(window,'scroll',function(){a.toBig();});};a.ANI();};
    a.openWin=function(i){var hrefLink = '';if(!/5|6|7|8/i.test('<?=$ads_type?>')){hrefLink = jslinks[i];}else{hrefLink = a.urllink;};if(navigator.platform.indexOf("Win") > -1 || navigator.platform.indexOf("Mac") > -1){window.location = hrefLink;return;};window.open(hrefLink, '_blank');};
	if(/5|6|7|8/i.test('<?=$ads_type?>')){var len=jslinks.length;setInterval(function(){if(a.i>=len)a.i=0;document.getElementById('<?=$img_id?>0').src=ImgSrc[a.i];if(<?=$ads_type?>==8){document.getElementsByClassName(a.x+'D2')[0].innerText=jsTitle[0][a.i];document.getElementsByClassName(a.x+'D3')[0].innerText=jsTitle[1][a.i];}else{document.getElementsByClassName(a.x+'D1')[0].innerText=jsTitle[a.i];};a.urllink=jslinks[a.i];a.i++;},12e3);};
	window['<?=$randstr?>'] = a; 
})();