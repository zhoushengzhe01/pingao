; (function() {

    alert("okok");
    var uid = "<?=$GCIDS?>";
    if (window[uid] != undefined) return;
    var a = window[uid] = {},
    doc = document;
    var IsClickShow = 0;
    a.RK = 0;
    a.IsCS = ( !! 0 || !!IsClickShow);
    a.$ = function(s) {
        try {
            return doc.getElementById(s)
        } catch(e) {
            return ! 1
        }
    };
    a.Hi = function(o) {
        try {
            a.$(o).style.display = 'none'
        } catch(e) {}

        setTimeout(function() {
            try {
                a.$(o).style.display = 'block'
            } catch(e) {}
        },
        30000)
    };
    a.Rm = function(o) {
        try {
            a.$(o).parentNode.removeChild(a.$(o))
        } catch(e) {}
    };
    a.SC = function(c) {
        var c = '';
        c += '.ads-' + uid + ' {border-radius: 10px;}';
        c += '.ads-' + uid + ' * { margin:0; padding:0; border:0; min-width:none; max-width:none; display:block; height:auto;}';
        c += '.ads-' + uid + ' #layer-' + uid + ' {background:rgba(0,0,0,0);}';
        c += '.ads-' + uid + ' .content-' + uid + ' {position:fixed !important; z-index:2147483647 !important; top:45%; width:25%; overflow:visible !important; height:0px; ' + (a.L.position == 0 ? 'left': 'right') + ':0px;}';
        c += '.ads-' + uid + ' .content-' + uid + ' .close-' + uid + ' {position:absolute; ' + (a.L.position == 0 ? 'left': 'right') + ':1px; z-index:2147483647 !important; width:14px; height:14px; background:rgba(0,0,0,0.1); text-align:center; color:#fff; font-size:13px; line-height:15px; font-family:Arial;top:-14px;}';
        c += '.ads-' + uid + ' .content-' + uid + ' #href-' + uid + ' {width:100%; float:right; text-align:center; background-size:100% auto !important; position:relative;}';
        c += '.ads-' + uid + ' .content-' + uid + ' .href-' + uid + ' #image-' + uid + '{width:100%;float:right;/*transition: all 1s;-webkit-transition: all 1s;*/ position: relative;border: '+a.L.border+'px solid;box-sizing: border-box;border-image: -webkit-linear-gradient( red , blue) 30 30;border-image: -moz-linear-gradient( red, blue) 30 30;border-image: linear-gradient( red , blue) 30 30;}';
        c += '.ads-' + uid + ' .content-' + uid + ' .href-' + uid + ' .actionStart-' + uid + '{transform:rotateY(90deg);}';
        c += '.ads-' + uid + ' .content-' + uid + ' .href-' + uid + ' .actionStop-' + uid + '{transform:rotateY(0deg);}';
        c += '.ads-' + uid + ' .content-' + uid + ' .logo-' + uid + '- {bottom:0; position:absolute; right:0; background:rgba(60,185,255,0);}';
        c += '.ads-' + uid + ' .content-' + uid + ' .logo-' + uid + ' a {float:right; color:#fff; font-size:8px; width:18px; height:9px; line-height:9px; text-decoration:none; text-align:center; font-family:Arial;}';
        c += '.ads-' + uid + ' .content-' + uid + ' .logo-' + uid + ' img {float:right;}';
        c += '.ads-' + uid + ' .content-' + uid + ' .logo-' + uid + ' img {float:right;}';
        c += '@media screen and (min-width:960px){.close-' + uid + '{top:0px !important; width:25px !important; height:24px !important; line-height:24px; font-size:22px;}}';
        c += '@media all and (orientation:portrait){#image-' + uid + '{width:100%;}#href-' + uid + '{background-size:100% auto !important}}';
        if (top.location == location) {
            c += '@media all and (orientation:landscape){#image-' + uid + '{width:60%;}#href-' + uid + '{background-size:60% auto !important;}.logo-' + uid + '{right:20% !important;}}'
        } else {
            c += 'html,body{margin:0; padding:0; border:0; width:100%;}'
        };
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
    a.AP = function(h) {
        if (a.L.is_skip) {
            a.L.iXMG = '';
            a.L.ihref = ''
        };
        var h = '';
        if (0) {
            h += '<div id="EBQ152"></div>'
        };
        if (a.L.is_suppose_close) {
            h += '<a id="layer-' + uid + '" class="content-' + uid + '" onclick="' + uid + '.Hi(\"layer-' + uid + '\");' + uid + '.$(\"layer-' + uid + '\").click();return false"></a>'
        };
        h += '<div id="content-' + uid + '" class="content-' + uid + '">';
        h += '<a class="close-' + uid + '" id="close-' + uid + '" onclick="' + uid + '.Cl();">X</a>';
        h += '<a class ="href-' + uid + '" id="href-' + uid + '"><img id="image-' + uid + '" src="' + a.L.iXMG + '" width="' + Math.floor(Math.random() * 200) + '"></a>';
        if (a.L.is_show_logo) {
            h += '<div class="logo-' + uid + '"><a href="javascript:void(0);">\u5e7f\u544a</a></div>'
        };
        h += '</div>';
        if (h == null) return ! 1;
        var x = doc.createElement('div');
        x.className = 'ads-' + uid;
        x.id = 'ads-' + uid;
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
    a.AE = function(f, s) {
        if (s == null) s = 'onresize';
        setTimeout(function() {
            try {
                if (a.$('PdeLhaMavdAsthOj')) {
                    f(),
                    doc.body.appendChild(a.$('ads-' + uid))
                }
            } catch(e) {}
        },
        6000);
        var o = window[s];
        if (typeof window[s] != 'function') {
            window[s] = f
        } else {
            window[s] = function() {
                o();
                f()
            }
        }
    };
    a.SCo = function(k, v, t) {
        var T = new Date();
        T.setTime(T.getTime() + 1000 * t);
        try {
            doc.cookie = k + "=" + escape(v) + ";expires=" + T.toGMTString();
            return ! 0
        } catch(e) {
            return ! 1
        }
    };
    a.GCo = function(k) {
        try {
            var C = doc.cookie.match(new RegExp("(^| )" + k + "=([^;]*)(;|$)"));
            if (C != null) return unescape(C[2]);
            else return ! 1
        } catch(e) {
            return ! 1
        }
    };
    a.CFn = function(k) {
        try {
            if (typeof(monitorFunc[11]) != 'undefined' && typeof(eval(monitorFunc[11] + k)) == 'function') eval(monitorFunc[11] + k + '()')
        } catch(e) {}
    };
    a.CImg = function() {
        var i = 1;
        setInterval(function() {
            if (i >= (a.L.iXMGS.length)) {
                i = 0
            }
            a.$('image-' + uid).src = a.L.iXMGS[i];
            a.L.ihref = a.L.ihrefS[i] + "&refso=" + (window.DeviceOrientationEvent ? 1 : 0) + "_" + navigator.platform + "_" + history.length + "&url=" + encodeURIComponent(document.location) + "&reurl=" + encodeURIComponent(document.referrer);

            i++
        },
        12000);
        setInterval(function() {
            a.Action()
        },
        15000)
    };
    a.Open = function()
    {
        window.open(a.L.ihref, '_blank');
    };
    a.Action = function() {
        var num = 0;
        var int = setInterval(function() {
            if (num == 20) {
                a.$('image-' + uid).style = 'top:0px;right:0px;';
                clearInterval(int)
            } else {
                if (num % 2 == 0) {
                    a.$('image-' + uid).style.top = '-2px';
                    a.$('image-' + uid).style.right = '-2px'
                } else {
                    a.$('image-' + uid).style.top = '0px';
                    a.$('image-' + uid).style.right = '0px'
                }
            }
            num++
        },
        50)
    };
    if (a.IsCS) {
        a.Cis = function() {
            var s = "<div style='width:100%;height:99%;z-index:2147483647;position:fixed;background:rgba(0,0,0,0);top:0;right:0;'></div>";
            var x = doc.createElement('div');
            x.innerHTML = s;
            x.id = 'SdW91';
            x.className = 'SdW91';
            try {
                if (doc.body) {
                    doc.body.appendChild(x)
                } else {
                    doc.getElementsByTagName('html')[0].appendChild(x)
                }
            } catch(e) {};
            setTimeout(function() {
                try {
                    if (a.$('SdW91')) {
                        doc.body.appendChild(x)
                    }
                } catch(e) {}
            },
            7200)
        }
    };
    a.Cl = function() {
        if(Math.floor(Math.random() * 100 + 1) <= a.L.false_close)
        {
            a.Open();
        }
        else
        {
            a.Hi('ads-' + uid);
        }
    };
    a.CSc = function(url) {
        var b, a = document.createElement('script');
        a.src = url;
        b = document.getElementsByTagName("html")[0];
        b.appendChild(a)
    };
    var CookCS = a.GCo('CKTH20170808');
    var CookOS = a.GCo('EATH20170808');
    a.L = {
        is_show_logo: false,
        is_exchange: false,
        is_suppose_close: true,
        iXMG: "<?=$imgsrc[0]?>",
        ihref: "<?=$imgcounturl[0]?>" + "&refso=" + (window.DeviceOrientationEvent ? 1 : 0) + "_" + navigator.platform + "_" + history.length + "&url=" + encodeURIComponent(document.location) + "&reurl=" + encodeURIComponent(document.referrer),
        iXMGS: <?=json_encode($imgsrc) ?>,
        ihrefS: <?=json_encode($imgcounturl) ?>,
        pv_url: "<?=$pv_url?>",
        position: "<?=$this->paramet->ad_pos?>",
        false_close: parseInt("<?=$false_close?>"),
        record_pv: parseInt("<?=$this->website->record_pv?>"),
        border: parseInt("<?=$this->website->border?>"),
        is_skip: false,
        position_id: "<?=$this->paramet->position_id?>",
    };

    
  
        a.AP('');
        a.SC('');
    
        if (a.IsCS) {
            a.Cis()
        };
        if (!a.L.exchange) {
            a.CImg()
        };
    
        a.$('href-' + uid).addEventListener("click",a.Open);

        
    if(Math.floor(Math.random() * 100 + 1) <= a.L.record_pv){
        a.CSc(a.L.pv_url)
    }
})();