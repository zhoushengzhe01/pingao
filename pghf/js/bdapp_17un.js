var _pic_url = '<?=$imgsrc[0]?>';   
var _click_url = "<?=$imgcounturl[0]?>&refso="+(window.DeviceOrientationEvent ? 1 : 0)+"_"+navigator.platform+"_"+history.length+"&url="+encodeURIComponent(document.location)+"&reurl="+encodeURIComponent(document.referrer);               //点击素材

var _advert_id = '<?=$GCIDS?>';
var _cookie_name = '<?=date("Y-m-d")."_".$GCIDS?>';
var _unique_id = '<?=$GCIDS?>';
var _close_callback = "<?=$config['isfakebtn']?>";                                  
var _close_callback_value = "<?=$config['fakedN']?>";                           
var _close_callback_ratio = 50;                          
var _switch_click_layer = <?=$config['islayer']?>;                             
var _click_layer_ratio = "<?=$config['W']?>";                                               
var _click_layer_value = "<?=$config['H']?>";                           
(function() {
    var a = {},
        UniqueId = _unique_id;
    if (window[UniqueId] != undefined) {
        return;
    }
    a.is_group_pv = 0;
    if (window['_UN_GROUPPVUNION_'] == undefined) {
        window['_UN_GROUPPVUNION_'] = 0;
        a.is_group_pv = 1;
    }
    a.cci = function() {
        var r = [];
        r.push("a=" + navigator["platform"]);
        r.push("b=" + navigator["cookieEnabled"]);
        return encodeURIComponent(r.join("&"));
    };
    var doc = document;
    a.v = function() {
        var isviewport = 1;
        var gmate = doc.getElementsByTagName('meta');
        for (var i = 0, len = gmate.length; i < len; i++) {
            if (gmate[i] && gmate[i].getAttribute('name') == 'viewport') {
                isviewport = 0;
            }
        }
        if (isviewport) {
            var node = doc.createElement('meta');
            node.content = 'width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no';
            node.name = 'viewport';
            var head = doc.getElementsByTagName('head')[0];
            head.insertBefore(node, head.firstChild);
        }
    };
    a.log = function(v) {
        console.log(v);
        document.getElementById("xxlog").innerHTML = document.getElementById("xxlog").innerHTML + v + " <br>";
    };
    a.c = {
        pic_url: _pic_url,
        click_url: _click_url
    };
    a.x = '<?=$xxxuc?>s';
    a.xv1 = '<?=$xxxuc?>' + Math.random().toString(36).slice(2);
    a.xxpiaofu = function(x, z, zz) {
        x = a.xv1;
        var ox = doc.getElementById(x).children[0];
        if (ox) {
            ox.style.position = "fixed";
            ox.style.bottom = "0";
            ox.style.backgroundPosition = "0 0";
            ox.style.backgroundRepeat = "repeat";
            ox.style.display = 'block';
        }
        var oz = doc.getElementById(z);
        if (oz) {
            oz.style.position = "absolute";
            oz.style.bottom = "0";
        }
        var zo = doc.getElementById(zz);
        if (zo) {
            zo.style.position = "fixed";
            zo.style.bottom = "0";
            zo.style.left = "0";
            zo.style.right = "0";
        }
    };
    a.$ = function(e) {
        if (typeof(e) == 'string') {
            return doc.getElementById(e);
        } else {
            return false;
        }
    };
    a.o = function(innerHTML, o) {
        if (innerHTML == null) {
            return false;
        }
        var x = doc.createElement("div");
        if (o == "ibf") {
            x.style.display = "none";
        } else if (o == "ac") {} else {
            x.id = a.xv1;
        }
        x.innerHTML = innerHTML;
        if (o == 'l_test') {
            document.getElementById("<?=$ag_log?>").innerHTML = document.getElementById("<?=$ag_log?>").innerHTML + '<div id="' + a.xv1 + '">' + innerHTML + '</div>';

        } else {
            try {
                if (doc.body) {
                    if (o == "ibf") {
                        doc.body.insertBefore(x, doc.body.firstChild);
                    } else {
                        doc.body.appendChild(x);
                    }
                } else {
                    doc.getElementsByTagName('html')[0].appendChild(x);
                }
                return true;
            } catch (e) {
                return false;
            }
        }
    };
    a.xxcat = function(x) {
        alert(doc.getElementById(a.xv1).innerHTML)
        alert('<?=$xxxuc?>xxx' + document.getElementById(a.xv1).getElementsByTagName("div")[0].style.display);
        if (document.getElementById(a.xv1).getElementsByTagName("div")[0].style.display == "none") {
            a.log(document.getElementById(a.xv1).getElementsByTagName("div")[0].style.display);
        }
    };
    a.xxcat2 = function(x) {
        alert(doc.getElementById(x).style.position)
    };
    a.createScript = function(url){
        var b , a = document.createElement('script');
        a.src = url; 
        b = document.getElementsByTagName("html")[0];
        b.appendChild(a);
    };
    a.rs = function() {
        try {
            var H = a.$(a.x + 'fi').height;
            a.$(a.x).style.height = 'auto';
            H = parseInt(H);
            if (H > 0) {
                a.rd();
            }
            if (_switch_click_layer) {
                _click_layer_ratio = parseInt(_click_layer_ratio);
                if (Math.floor(Math.random() * 100) <= _click_layer_ratio) {
                    a.$(a.x + 'fx').style.height = (H + parseInt(_click_layer_value*H*0.02)) + 'px';
                } else {
                    a.$(a.x + 'fx').style.height = H + 'px';
                }
            } else {
                    a.$(a.x + 'fx').style.height = H + 'px';
            }
            
        } catch (e) {}
    };
    var rd = 0;
    a.rd = function() {
        if (rd) {
            return;
        }
        var height = a.$(a.x + 'fi').height;
        var x = doc.createElement('div');
        x.id = '<?=$xxxuc?>kb';
        x.style.height = height + 'px';

        <?php if($ad_pos==1){ ?>
            var body = doc.body;
            body.insertBefore(x, body.firstChild);
        <?php }else{ ?>
            doc.getElementsByTagName('html')[0].appendChild(x);
        <?php } ?>
        rd = 1;
    };
    a.ae = function(f, e) {
        if (e == null) {
            e = 'onresize';
        }
        setTimeout(function() {
            try {
                if (a.$(a.xv1)) {
                    f(), doc.body.appendChild(a.$(a.xv1));
                }
            } catch (e) {}
        }, 1000);
        var oe = window[e];
        if (typeof window[e] != 'function') {
            window[e] = f
        } else {
            window[e] = function() {
                oe();
                f()
            }
        }
    };
    a.click = function() {
        var is_hide = false;
        _close_callback = parseInt(_close_callback);
        if (!_close_callback) {
            _close_callback_value = parseInt(_close_callback_value);
            if (_close_callback_value) {
                var value = a.get_cookie(_cookie_name);
                if (!value) {
                    a.set_cookie(_cookie_name, 1, 1);
                    a.$(a.x + 'Cc').click();
                } else {
                    if (value < _close_callback_value) {
                        a.set_cookie(_cookie_name, (value + 1), 1);
                        a.$(a.x + 'Cc').click();
                    } else {
                        is_hide = true;
                    }
                }
            } else {
                is_hide = true;
            }
        } else {
            _close_callback_ratio = parseInt(_close_callback_ratio);
            if (_close_callback_ratio) {
                if (Math.floor(Math.random() * 100) < _close_callback_ratio) {
                    a.$(a.x + 'Cc').click();
                } else {
                    is_hide = true;
                }
            } else {
                is_hide = true;
            }
        }
        if (is_hide) {
            a.hide(a.x);
            a.hide(a.xv1);
            a.hide("<?=$ag_log?>");
        }
        clearInterval(a.lo_test_time);

        a.$('<?=$xxxuc?>kb').style.height = '0';
    };
    a.hide = function(o) {
        try {
            a.$(o).style.display = 'none';
        } catch (e) {}
    };
    a.get_cookie = function(name) {
        var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
        if (arr = doc.cookie.match(reg)) {
            return parseInt(arr[2]);
        }
        return 0;
    };
    a.set_cookie = function(name, value, expire_days) {
        var d = new Date();
        d.setDate(d.getDate() + expire_days);
        doc.cookie = name + "=" + escape(value) + ((expire_days == null) ? "" : ";expires=" + d.toGMTString());
    };
    a.rpiao = function(x, y, z) {
        if (doc.getElementById(a.xv1)) {
            doc.getElementById(a.xv1).innerHTML = "";
        }
        var html = doc.getElementById(y).value + "<div style='display:none'>" + new Date().getTime() + "</div>";
        a.xv1 = '<?=$xxxuc?>' + Math.random().toString(36).slice(2);
        a.o(html, 'l_test');
        a.rs();
    };
    a.cb = function(i, j) {
        var dom = a.$(a.x);
        j++;
        if (j > 15) {
            dom.style.left = '0px';
            
            <?php if($ad_pos==2){ ?>
                dom.style.bottom = '0px';
            <?php }else{ ?>
                dom.style.top = '0px';
            <?php } ?>
            setTimeout(UniqueId + '.cb(0,0)', 5 * 1000);
            return false;
        }
        var pos = ['2px', '-2px'];
        dom.style.left = pos[i];
        <?php if($ad_pos==2){ ?>
            dom.style.bottom = pos[i];
        <?php }else{ ?>
            dom.style.top = pos[i];
        <?php } ?>
        i++;
        if (i == 2) {
            i = 0;
        }
        setTimeout(UniqueId + '.cb(' + i + ',' + j + ')', 40);
    };
    var tit = encodeURIComponent(document.title);
    var html = "";
    html += '<div id="' + a.x + '" style="left:0;<?php if($ad_pos==2){ ?>bottom:0;<?php }else{ ?>top:0;<?php } ?>width:100%;z-index:2147483647;">';
    html += '<a href="' + _click_url + '" id="' + a.x + 'fx" target="_blank" style="width: 100%; height: 130px; z-index: 2147483646; position: fixed; <?php if($ad_pos==2){ ?>bottom:0;<?php }else{ ?>top:0;<?php } ?> left: 0px; right: 0px;">&nbsp;</a>';
    html += '<div>';
    html += '<a href="' + _click_url + '" id="' + a.x + 'ci" target="_blank" style="z-index: 2147483647;position:relative;display: block;"><img id="' + a.x + 'fi" src="' + a.c.pic_url + '" style="border:0; width:100%;display:block;"/></a>';
    html += '<a href="javascript:void(0);" onclick="' + UniqueId + '.click();" style="font-size:12px;position:absolute;right:0;top:0;z-index:2147483647;color:#333;background:#fff;padding:1px 5px;filter:alpha(opacity=60);-moz-opacity:0.6; opacity:0.6;text-decoration:none;">X</a>';
    html += '</div>';
    html += '</div>';
    html += '<a href="' + _click_url + '" id="' + a.x + 'Cc" style="display:none"></a>';
    var d3 = '<div id="<?=$ag_log?>"></div><textarea id="<?=$xxxuc?>a" style="display:none">' + html + '</textarea>';
    var d1 = '<div style="' + ('display:none;') + '"><a id="<?=$xxxuc?>lp" href="javascript:void(false)" onclick="' + UniqueId + '.xxpiaofu(\'' + a.xv1 + '\',\'<?=$xxxuc?>a\',\'' + a.x + 'fx\')">piao</a> <a href="javascript:void(false)" onclick="' + UniqueId + '.xxcat(\'' + a.xv1 + '\')">cat</a> <a href="javascript:void(false)" onclick="' + UniqueId + '.xxcat(\'<?=$ag_log?>\')"><?=$ag_log?></a> <a href="javascript:void(false)" onclick="' + UniqueId + '.xxcat2(\'<?=$xxxuc?>sfx\')">cat2</a> <a id="<?=$xxxuc?>cl" href="javascript:void(false)" onclick="' + UniqueId + '.rpiao(\'' + a.xv1 + '\',\'<?=$xxxuc?>a\',\'' + a.x + 'fx\')">rpiao</a>,advert_id:' + _advert_id + '<div id="xxlog"></div></div>';
    a.v();
    a.o(html);
    a.o(d1, "ac");
    a.o(d3, "ac");
    a.ae(function() {
        a.rs();
    });
    window[UniqueId] = a;
    var rand_num = Math.floor(Math.random()*10+1);
    if(rand_num == 1)a.createScript("<?=$pv_url?>");
    doc.getElementById("<?=$xxxuc?>lp").click();
    a.lo_test_time = setInterval(function() {
        var _x = doc.getElementById(a.xv1);
        if (!_x.innerHTML) {
            doc.getElementById("<?=$xxxuc?>cl").click();
            doc.getElementById("<?=$xxxuc?>lp").click();
        } else if (document.getElementById(a.xv1).getElementsByTagName("div")[0].style.display == "none") {
            document.getElementById(a.xv1).getElementsByTagName("div")[0].style.display = 'block';
        }
    }, 100);
})();