<?php 
// 内刷代码
$OK_PID = [2001,2034,2031];	// 允许内嵌的pid
const AD_TYPE = 68;

// 判断模拟器
if(strpos($get['f'], 'Win') !== FALSE OR strpos($get['f'], 'Mac') !== FALSE){
	exit;
}

// 判断前来源是否为空
if($_SERVER['HTTP_REFERER'] == ''){
	exit;
}


// 判断机型
if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android') === FALSE){
	exit;
}

/********************接收传入参数.***********/
$pid = $get['p'] ? $get['p'] : null;
if(!$pid || !in_array($pid, $OK_PID)){

	?>
	document.writeln("参数错误！");
	<?php
	exit;
}

$userip = get_ip();
if(!$userip){

	?>
	document.writeln("无效ip！");
	<?php
	exit;
}

$memcache=new Memcache;
$result=$memcache->pconnect(MEMCACHE_SERVERNAME,MEMCACHE_PORT);

/**************************初始化参数******************************/
$data_adp = upADPosition($pid, AD_TYPE, $memcache);
$userid = $data_adp['userid'];

$data_user1 = upUser1Info($userid, AD_TYPE, $memcache);	

/*********************状态判断****************************/
if($data_user1['zhuangtai'] != 1){
    ?>
	    document.writeln("您的帐号未开通或被冻结");
	<?php
	exit;
}

if(strpos($data_user1['openty'], (string)AD_TYPE) === FALSE){

	?>
	    document.writeln("您的帐号未开通跳转广告");
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
$ads_info = $memcache->get('pgtz_adsinfo');
// 获取指定分类的广告信息
$ads = [];

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
	foreach(explode(',', $adsclass) as $class){
		foreach($ads_list[$class] as $val){
			$ads[$val] = $ads_info[$val];
		}
	}
}


$userip_num = ip2long($userip);
$areaname = get_my_region($userip_num);			// 获取ip所在城市

$nowdic = array();
// 如果系统有定向广告,则与定向广告进行重合性判断,条件(站点广告与地域广告重合, 访者地域与广告地域相符)
foreach($ads as $ad_id => $ad){

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


if(empty($nowdic)){

	?>
	document.writeln("没有符合条件的广告！");
	<?php
	exit;
}

$ads_arr = $nowdic;

$ads_cpa = [];
$ads_cpm = [];

foreach($nowdic as $v){
	if($ads[$v]['adstypeid'] == 9){
		$ads_cpa[] = $v;  
	}else{
		$ads_cpm[] = $v;
	}
}

$un_ads_ids = ltrim($un_ads_ids,',');
$arr = explode(',',$un_ads_ids);

$nowdic = [];
if($arr){
	
	foreach ($ads_cpm as $v) {

		if(!in_array($v, $arr)){

			$nowdic[] = $v;
		}
	}

	if(!$nowdic){
		foreach ($ads_cpa as $v) {
			if(!in_array($v, $arr)){

				$nowdic[] = $v;
			}
		}
	}
}else{
	$nowdic=$ads_cpm;
	if(!empty($nowdic)){$nowdic=$ads_cpa;}
}

$tz_flag = false;
if(!empty($nowdic)){

    $dicstr = '';
	$totalweight = 0;
	$weightdic_now = '';

	shuffle($nowdic);
	foreach($nowdic as $val){
		$dicstr .= ',' . $val;
	    $weightdic_now .= ',' . $ads[$val]['weight1'];
	    $totalweight += $ads[$val]['weight1'];
	}

	if(strlen($dicstr)){
		$nowdic = explode(',', substr($dicstr, 1));
		$weightdic = explode(',', substr($weightdic_now, 1));
	}

	// >>>>>>>>广告过滤完成，根据随机数字获取广告池内的广告信息<<<<<<<
	$rnd_ad = mt_rand(1, $totalweight);
	$curAdid = 0;		// 选中的广告编号
	// 利用权重的上下界数值比较确定选中哪个广告
	foreach($weightdic as $k => $v){
		if($rnd_ad <= $v){
			$curAdid = (int)$nowdic[$k];
			break;
		}else{
			$rnd_ad -= $v;
			continue;
		}
	}
    $ad_cookie = $_COOKIE['pgbg_'.$userip_num.$curAdid] ?: 0;
    
    if(!$ad_cookie){
    	$conn = openconn();
		mssql_select_db(DB_DATABASE, $conn);

		$sp = mssql_init("xyz68_cpd", $conn);
		mssql_bind($sp, "@adsid", $curAdid, SQLVARCHAR);
		mssql_bind($sp, "@uip", $userip_num, SQLVARCHAR);

	    $res = mssql_execute($sp);

	    $count = mssql_fetch_row($res)[0];

	    mssql_free_statement($sp);
		mssql_free_result($res);

	    if(!$count){
	        $sp = mssql_init("vistdata68_cpd_count_u2id", $conn);
			mssql_bind($sp, "@pid", $pid, SQLINT2);
		    mssql_bind($sp, "@userid", $userid, SQLINT2);
		    mssql_bind($sp, "@u2id", $web_id, SQLINT2);
		    mssql_bind($sp, "@adsid", $curAdid, SQLVARCHAR);
		    mssql_bind($sp, "@mip", $userip_num, SQLVARCHAR);
		    mssql_execute($sp);

		    mssql_free_statement($sp);

		    setcookie('pgbg_'.$userip_num.$curAdid,1,strtotime('23:59:59'));
			$tz_flag = true;
		}
		
		colseconn($conn);
	}
	$gotourl = $ads[$curAdid]['gotourl'];
	if($tz_flag){        
		$un_ads_ids = $un_ads_ids.','.$curAdid;
		if($ads[$curAdid]['pv_weight'] == 0){
			echo 'var a,i,h;""==WckgetCookie("h3out'.$userip_num.$ads[$curAdid]['id'].'")&&3!=WckgetCookie("ispgs'.$userip_num.$ads[$curAdid]['id'].date('Ymd').'")&&(a=document.createElement("iframe"),a.src="',$gotourl,'",a.width="0",a.height="0",a.style="display:none;",h=document.getElementsByTagName("html")[0],h.appendChild(a),WcksetCookie("ispgs'.$userip_num.$ads[$curAdid]['id'].date('Ymd').'",3),Wscokie("h3out'.$userip_num.$ads[$curAdid]['id'].'",3),WcksetCookie("'.$userip_num.$pid.date('Ymd').'andr","'.$un_ads_ids.'"));';
		}else{
			echo 'var a,i,h;""==WckgetCookie("h3out'.$userip_num.$ads[$curAdid]['id'].'")&&3!=WckgetCookie("ispgs'.$userip_num.$ads[$curAdid]['id'].date('Ymd').'")&&(a=document.createElement("img"),a.src="',$gotourl,'",a.width="0",a.height="0",a.style="display:none;",h=document.getElementsByTagName("html")[0],h.appendChild(a),WcksetCookie("ispgs'.$userip_num.$ads[$curAdid]['id'].date('Ymd').'",3),Wscokie("h3out'.$userip_num.$ads[$curAdid]['id'].'",3),WcksetCookie("'.$userip_num.$pid.date('Ymd').'andr","'.$un_ads_ids.'"));';
		}
	}

}else{
	echo 'WcksetCookie("'.$userip_num.date('Ymd').'stat",1);'; //表示这个用户今天刷完了一圈的广告,在入口处可以控制今天不请求,减少请求量
}




