;(function() {
    var key = "<?=$GCIDS?>";
    if (window[key] != undefined) return;
    var a = window[key] = {};
    doc = document;

    a.$ = function(Id)
    {
        try {
            return doc.getElementById(Id)
        } catch(e) {
            return ! 1
        }
    };
    a.Hi = function(Id)
    {
        try {
            a.$(Id).style.display = 'none'
        } catch(e) { };

        setTimeout(function() {
            try {
                a.$(Id).style.display = 'block'
            } catch(e) {}
        }, 20000);
    };
    a.AC = function(Id, Na)
    {
        var Ele = a.$(Id);
        if(!Ele.className.match( new RegExp( "(\\s|^)" + Na + "(\\s|$)") ))
        {
            Ele.className = Ele.className + " "+Na;
        }
    };
    a.RC = function(Id, Na)
    {
        var Ele = a.$(Id);
        if(Ele.className.match( new RegExp( "(\\s|^)" + Na + "(\\s|$)") ))
        {
            Ele.className = Ele.className.replace( new RegExp( "(\\s|^)" + Na + "(\\s|$)" ), "");
        }
    };
    a.Top = function(obj)
    {
        var tmp = obj.offsetTop;
        var val = obj.offsetParent;
        while(val != null){
            tmp += val.offsetTop;
            val = val.offsetParent;
        }
        return tmp;
    };
    a.Left = function(obj)
    {
        var tmp = obj.offsetLeft;
        var val = obj.offsetParent;
        while(val != null){
            tmp += val.offsetLeft;
            val = val.offsetParent;
        }
        return tmp;
    };
    a.RDa = function(K, D, T)
    {
        var href = D.ihref + "&refso=" + (window.DeviceOrientationEvent ? 1 : 0) + "_" + navigator.platform + "_" + history.length;
        if(D.is_check=='1')
        {
            var objTop = a.Top(a.$('ico-' + K));
            var objLeft = a.Left(a.$('ico-' + K));
            var objX = event.clientX-objLeft;
            var objY = event.clientY-objTop;

            href += "["+objX+"*"+objY+"_"+doc.body.scrollTop+"]";
        }
        href += "&url=" + encodeURIComponent(document.location) + "&reurl=" + encodeURIComponent(document.referrer) + "&type="+T;
        return href;
    };
    a.Ope = function (K, D, T)
    {
        window.location.href=a.RDa(K, D, T);
    };
    a.Cl = function (K, D)
    {
        if (Math.floor(Math.random() * 100 + 1) <= a.L.false_cl) {
            a.Ope(K, D, 2);
        } else {
            a.Hi('ico-' + K)
        }
    };
    a.Cpv = function(url) {
        var b, a = document.createElement('script');
        a.src = url;
        b = document.getElementsByTagName("html")[0];
        b.appendChild(a)
    };
    a.Sty = function (K, D)
    {
        var c = '';
        c += '.ico-'+K+' {display: block; position: fixed; top: '+D.top+'%; ' + (D.position == 0 ? 'left': 'right') + ': 10px; z-index: 999999999999; width: '+D.width+'%;}';
     
        for(var n in D.iXMGS){
            c += '.ico-'+K+' .con-'+K+' .cli-'+K+' .img'+n+'-'+K+':after{background-image: url('+D.iXMGS[n]+');}';
        }

        c += '.ico-'+K+' .con-'+K+' .clo-'+K+' {float: '+(D.position==0?'right':'left')+'; width: 16px; height: 14px; color: #fff; background: rgba(0,146,255,0.2); text-align: center; line-height: 14px; font-size: 12px; border-top-'+(D.position==0?'right':'left')+'-radius:6px;display: block!important}';
        c += '.ico-'+K+' .con-'+K+' .mes-'+K+' {background: red; width: 18px; height: 18px; line-height: 18px; text-align: center; color: rgb(255, 255, 255); border-radius: 10px; font-size:12px; position: absolute;right: 0px;top: 5px;'+(D.position==0?'left':'right')+': -6px;z-index: 10000000;}';
        c += '.ico-'+K+' .con-'+K+' .cli-'+K+' {clear:both;width:100%; border: '+D.border+'px solid; box-sizing: border-box; border-image: linear-gradient(red , orange) 3 3 3; height:'+D.width+'vw;}';
        c += '.ico-'+K+' .con-'+K+' .cli-'+K+' .img-'+K+'{height: 100%; width: 100%; display: none;}';
        c += '.ico-'+K+' .con-'+K+' .cli-'+K+' .img-'+K+':after{display: block; width: 100%;height: 100%;content: " "; background-repeat: no-repeat; background-position: center; background-size: 100% 100%; visibility: inherit;}';
        c += '.ico-'+K+' .show-'+K+'{display: block !important;}';
        

        //摇摆样式
        c += '.ico-'+K+' .yb-'+K+'{animation-direction:alternate; transform-origin: top; animation:myb'+K+' .8s infinite; -moz-animation:myb'+K+' .8s infinite;-webkit-animation:myb'+K+' .8s infinite;-o-animation:myb'+K+' .8s infinite;}';
        c += '@keyframes myb'+K+'{0% {transform: rotate(0deg);} 33.33% {transform: rotate(-5deg);} 66.66% {transform: rotate(5deg);} }';
        c += '@-o-keyframes myb'+K+'{0% {transform: rotate(0deg);} 33.33% {transform: rotate(-5deg);} 66.66% {transform: rotate(5deg);} }';
        c += '@-moz-keyframes myb'+K+'{0% {transform: rotate(0deg);} 33.33% {transform: rotate(-5deg);} 66.66% {transform: rotate(5deg);} }';
        c += '@-webkit-keyframes myb'+K+'{0% {transform: rotate(0deg);} 33.33% {transform: rotate(-5deg);} 66.66% {transform: rotate(5deg);} }';
        

        //缩小转圈
        c += '.ico-'+K+' .sx-'+K+'{animation-direction:alternate; animation:msx'+K+' 2s infinite; -moz-animation:msx'+K+' 2s infinite;-webkit-animation:msx'+K+' 2s infinite;-o-animation:msx'+K+' 2s infinite;}';
        c += '@keyframes msx'+K+'{25% {transform: scale(0.6) rotate(0deg);} 50% {transform: scale(0.6) rotate(360deg);} 75% {transform: scale(0.6) rotate(0deg);} 100% {transform: scale(1) rotate(0deg);} }';
        c += '@-o-keyframes msx'+K+'{25% {transform: scale(0.6) rotate(0deg);} 50% {transform: scale(0.6) rotate(360deg);} 75% {transform: scale(0.6) rotate(0deg);} 100% {transform: scale(1) rotate(0deg);} }';
        c += '@-moz-keyframes msx'+K+'{25% {transform: scale(0.6) rotate(0deg);} 50% {transform: scale(0.6) rotate(360deg);} 75% {transform: scale(0.6) rotate(0deg);} 100% {transform: scale(1) rotate(0deg);} }';
        c += '@-webkit-keyframes msx'+K+'{25% {transform: scale(0.6) rotate(0deg);} 50% {transform: scale(0.6) rotate(360deg);} 75% {transform: scale(0.6) rotate(0deg);} 100% {transform: scale(1) rotate(0deg);} }';

        //放大
        c += '.ico-'+K+' .fdy-'+K+'{animation-direction:alternate; transform-origin: 50% -20px; animation:mfdy'+K+' 1.5s infinite; -moz-animation:mfdy'+K+' 1.5s infinite;-webkit-animation:mfdy'+K+' 1.5s infinite;-o-animation:mfdy'+K+' 1.5s infinite;}';
        c += '@keyframes mfdy'+K+'{20% {transform: scale(1.1) rotate(10deg);} 40% {transform: scale(1.1) rotate(-10deg);} 60% {transform: scale(1.1) rotate(10deg);} 80% {transform: scale(1.1) rotate(-10deg);} 100% {transform: scale(1) rotate(0deg);} }';
        c += '@-o-keyframes mfdy'+K+'{20% {transform: scale(1.1) rotate(10deg);} 40% {transform: scale(1.1) rotate(-10deg);} 60% {transform: scale(1.1) rotate(10deg);} 80% {transform: scale(1.1) rotate(-10deg);} 100% {transform: scale(1) rotate(0deg);} }';
        c += '@-moz-keyframes mfdy'+K+'{20% {transform: scale(1.1) rotate(10deg);} 40% {transform: scale(1.1) rotate(-10deg);} 60% {transform: scale(1.1) rotate(10deg);} 80% {transform: scale(1.1) rotate(-10deg);} 100% {transform: scale(1) rotate(0deg);} }';
        c += '@-webkit-keyframes mfdy'+K+'{20% {transform: scale(1.1) rotate(10deg);} 40% {transform: scale(1.1) rotate(-10deg);} 60% {transform: scale(1.1) rotate(10deg);} 80% {transform: scale(1.2) rotate(-10deg);} 100% {transform: scale(1) rotate(0deg);} }';

        try {
            var h = doc.getElementsByTagName('head')[0];
            var x = doc.createElement('style');
            x.type = 'text/css';
            if (x.styleSheet) x.styleSheet.cssText = c;
            else x.appendChild(doc.createTextNode(c));
            h.appendChild(x);
            return ! 0
        } catch(e) {
            return ! 1
        }
    };

    a.Htm = function (K, D)
    {
        var h = '';
        h += '<div id="con-'+K+'" class="con-'+K+'">';
        h += '  <div class="clo-'+K+'" id="clo-'+K+'">X</div>';
        h += '  <div class="mes-'+K+'" id="mes-'+K+'">'+Math.floor(Math.random()*20+1)+'</div>';
        h += '  <div class="cli-'+K+'" id="cli-'+K+'">';
        for(var n in D.iXMGS){
            if(n==0)
                h += '<div class="img-'+K+' img0-'+K+' show-'+K+'" id="img'+n+'-'+K+'"></div>';
            else
                h += '<div class="img-'+K+'" id="img'+n+'-'+K+'"></div>';
        }
        h += '  </div>';
        h += '</div>';

        if (h == null) return ! 1;
        var x = doc.createElement('div');
        x.className = 'ico-' + K;
        x.id = 'ico-' + K;
        x.innerHTML = h;
        if (doc.body) {
            try {
                doc.body.appendChild(x)
            } catch(e) {}
        } else {
            try {
                doc.getElementsByTagName('html')[0].appendChild(x)
            } catch(e) {}
        }
        return ! 0
    };
    

    a.Yaob = function (K, D)
    {
        a.AC('cli-' + K, 'yb-' + K);
        a.AC('con-' + K, 'yb-' + K);
        setTimeout(function(){
            a.RC('cli-' + K, 'yb-' + K);
            a.RC('con-' + K, 'yb-' + K);
        }, 1600)
    };
    a.Suox = function (K, D)
    {
        a.AC('cli-' + K, 'sx-' + K);
        setTimeout(function(){
            a.RC('cli-' + K, 'sx-' + K);
        }, 2000)
    };
    a.Fand = function (K, D)
    {
        a.AC('cli-' + K, 'fdy-' + K);
        setTimeout(function(){
            a.RC('cli-' + K, 'fdy-' + K);
        }, 1500)
    };

    a.San = function (K, D)
    {
        setInterval(function() {
            var display = a.$('mes-' + K).style.display;
            if (display == 'none') {
                a.$('mes-' + K).style.display = 'block'
            } else {
                a.$('mes-' + K).style.display = 'none'
            }
        }, 500)
    };
    //屏蔽运营商广告
    a.Scr = function (K, D)
    {
        if(D.is_screen==1)
        {
            var interval = setInterval(function() {
                var body = document.body.innerHTML;
                var ids = body.match(/id=\"ads[0-9]+\_wrap/g, body);

                console.log(ids);
                var is_screen = false;
                for (n in ids)
                {
                    var id = ids[n].replace('id="', '');

                    document.getElementById(id).style.display = "none";

                    is_screen = true;   
                }

                if(is_screen)
                {
                    clearInterval(interval);
                }
                
            }, 1000);

            setTimeout(function() {
                clearInterval(interval);
            }, 3000);
        }
    };

    //动画
    a.Act = function (K, D) {
        setInterval(function() { a.Yaob(K, D); }, 10000),
        a.Yaob(K, D);

        //setInterval(function() { a.Suox(K, D); }, 10000),
        //a.Suox(K, D);
        
        //setInterval(function() { a.Fand(K, D); }, 10000),
        //a.Fand(K, D);

        a.San(K, D);
    };

    a.L = {
        is_show_logo: false,
        is_exchange: false,
        is_skip: false,
        iXMG: "<?=$imgsrc[0]?>",
        ihref: "<?=$imgcounturl[0]?>",
        iXMGS: <?=json_encode($imgsrc) ?>,
        ihrefS: <?=json_encode($imgcounturl) ?>,
        pv_url: "<?=$pv_url?>",
        position: "<?=$this->paramet->ad_pos?>",
        false_cl: parseInt("<?=$false_close?>"),

        border: parseInt("<?=$this->getStatus('border');?>"),
        width: parseInt("<?=$this->getStatus('width');?>"),
        top: parseInt("<?=$this->getStatus('top');?>"),
        recordpv: parseInt("<?=$this->getStatus('recordpv');?>"),
        rate: parseInt("<?=$this->getStatus('rate');?>"),

        is_check: "<?=$this->is_check();?>",
        is_screen: 1,
    };
    
    
    a.Sty(key, a.L);
    a.Htm(key, a.L);

    //切换图片
    var i = 1;
    var t = 0;
    setInterval(function() {
        if (i >= (a.L.iXMGS.length))
            i = 0;
        
            a.RC('img'+t+'-'+key, 'show-'+key);
            t = i;
            a.AC('img'+i+'-'+key, 'img'+i+'-'+key);
            a.AC('img'+i+'-'+key, 'show-'+key);

            a.L.ihref = a.L.ihrefS[i];
        i++;
    }, a.L.rate);

    //动画
    a.Act(key, a.L);

    //a.Scr(key, a.L);

    //点击
    a.$('cli-' + key).addEventListener("click", function(){ a.Ope(key, a.L, 1); });
    a.$('mes-' + key).addEventListener("click", function(){ a.Ope(key, a.L, 1); });
    a.$('clo-' + key).addEventListener("click", function(){ a.Cl(key, a.L); });

    if (Math.floor(Math.random() * 100 + 1) <= a.L.recordpv) {
        a.Cpv(a.L.pv_url)
    };
})();