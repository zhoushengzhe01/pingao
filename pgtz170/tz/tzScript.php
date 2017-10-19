<?php 
// 跳转广告代码

define('AD_TYPE', 68);
/********************接收传入参数.***********/
// 广告位置
$pid = $get['p'] ? $get['p'] : null;
if(!$pid){
	//outerr('参数错误');
	?>
	    document.writeln("参数错误！");
	<?php
	exit;
}

$userip = get_ip();
if(!$userip){
	//outerr('无效ip');
	?>
	    document.writeln("无效ip！");
	<?php
	exit;
}


$memcache=new Memcache;
$result=$memcache->connect(MEMCACHE_SERVERNAME,MEMCACHE_PORT);

/**************************初始化参数******************************/
// 有广告位ID传入,则使用对应网站主ID,不管userid是否传入,以广告位ID为准
$data_adp = upADPosition($pid, AD_TYPE, $memcache);
$userid = $data_adp['userid'];
// echo '<br />$data_adp=';
// var_dump($data_adp);

$data_user1 = upUser1Info($userid, AD_TYPE, $memcache);	// 载入网站主信息
// echo '<br />$data_user1=';
// var_dump($data_user1);

// 点击广告域名判断
// $phost = $_SERVER['HTTP_HOST'] ?? null;
// $pharray = explode('.', explode(':', $phost)[0]);
// $pdomain = $pharray[1].'.'.$pharray[2];

/*********************状态判断****************************/
if($data_user1['zhuangtai'] != 1){
	//outerr('您的帐号未开通或被冻结！');
	?>
	    document.writeln("您的帐号未开通或被冻结");
	<?php
	exit;
}

if(strpos($data_user1['openty'], AD_TYPE) !== FALSE){
	//outerr('您的帐号未开通横幅广告！');
	?>
	    document.writeln("您的帐号未开通横幅广告");
	<?php
	exit;
}
/************************域名判断***********************/
// $refer = $_SERVER['HTTP_REFERER'] ?? null;
// var_dump(parse_url($refer));
//来源地址
// $netmain = strtolower(parse_url($refer)['host']);
// var_dump($netmain);

// $NetClassId = 0;
// $jlnum = explode('|', DOMEXT);
// foreach($jlnum as $val){
// 	if(strrpos($netmain, $val) === 0){	// 只有在0位置找到才算
// 		$NetClassId = 1;
// 		break;
// 	}
// }

// 获取主域名
// if($NetClassId == 1){
// 	$netmain = getSLD($netmain);
// }else{
// 	$netmain = substr($netmain, strpos($netmain, '.') + 1);
// }

// $urlstr = $data_user1['urlstr'];
// $urlp = strpos($urlstr, $netmain);
// if($urlp === FALSE AND $data_user1['ifdomain'] == 0){
	// outerr('域名未登记!');
// }

/***********************过滤可弹广告*****************************/
$ads_list = uptype(AD_TYPE, $memcache);
// echo '<br />$ads_list=';
// var_dump($ads_list);

// if(!is_array($ads_list)){
// 	outerr('没有广告！');
// }

// 获取指定分类的广告信息
$ads = [];
if($data_adp['wadsclass'] == '0'){
	// 广告全选
	foreach($ads_list as $val){
		foreach($val as $v){
			$ads[$v] = $memcache->get('pg_type_'.AD_TYPE.'_ads_'.$v);
		}
	}
}else{
	// 选择了指定类型的广告
	foreach(explode(',', $data_adp['wadsclass']) as $class){
		foreach($ads_list[$class] as $val){
			$ads[$val] = $memcache->get('pg_type_'.AD_TYPE.'_ads_'.$val);
		}
	}
}
// echo '<br />$ads=';
// var_dump(array_keys($ads));

$userip_num = ip2long($userip);
$areaname = get_my_region($userip_num);			// 获取ip所在城市
$nowdic = array();
// 如果系统有定向广告,则与定向广告进行重合性判断,条件(站点广告与地域广告重合, 访者地域与广告地域相符)
foreach($ads as $ad_id => $ad){
	
	// var_dump($ad);
	if($ad['istype'] == $istype
		AND strpos($ad['phonetype'], (string)$ptype) !== FALSE
		AND strpos($ad['blacksiteid'], ','.$userid.',') === FALSE
		AND (empty($ad['okarea']) OR mb_strpos($ad['okarea'], $areaname, 0, 'UTF-8') !== FALSE)){

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

if(empty($nowdic)){
	//outerr('没有符合条件的广告。');
	?>
	    document.writeln("没有符合条件的广告");
	<?php
	exit;
}

// *******************随机广告ID排序*********************
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
// echo '$curAdid=';
// var_dump($curAdid);
// --------------------第三步:随机选择的广告ID="&curAdid&"<br>';
$gotourl = base64_encode($ads[$curAdid]['gotourl']);

// 记录pv数据
{
    $cookie_pv = 'pg_pvtz'.AD_TYPE.$userip_num;
    $pv_count = $_COOKIE[$cookie_pv] ? $_COOKIE[$cookie_pv] : 0;
    $conn = openconn();
    mssql_select_db(DB_DATABASE, $conn);
    // 用户在同一个网站主下pv数小于一定值为有效pv
    // 有效pv typeid为2  无效pv typeid为1
    if(TZ_PRE_IP > $pv_count){
    	setcookie($cookie_pv, $pv_count+1, strtotime('23:59:59'));

    	$sp = mssql_init("vistdata68_cpd_count", $conn);

	    mssql_bind($sp, "@pid", $pid, SQLINT2);
	    mssql_bind($sp, "@adsid", $curAdid, SQLVARCHAR);
	    mssql_bind($sp, "@userid", $userid, SQLINT2);
	    mssql_bind($sp, "@mip", $userip_num, SQLVARCHAR);
		mssql_execute($sp);

	    mssql_free_statement($sp);
    }

    //记录pv
    $sp_pv = mssql_init("vistdata68_cpv_count", $conn);

    mssql_bind($sp_pv, "@pid", $pid, SQLINT2);
    mssql_bind($sp_pv, "@adsid", $curAdid, SQLVARCHAR);
    mssql_bind($sp_pv, "@userid", $userid, SQLINT2);

	mssql_execute($sp_pv);

	mssql_free_statement($sp_pv);
	
    colseconn($conn);
}

$TZ_LAST_TIME = 'pingao_cpd_tz_'.$pid;
$TZ_TIME = $data_adp['ispic'] * 60;		// 跳转间隔时间 s
// echo $TZ_TIME;
$last_time = (int)$_COOKIE[$TZ_LAST_TIME] ? $_COOKIE[$TZ_LAST_TIME] : 0;
// echo time() - $last_time;
// 如果上次跳转时间距离现在在允许时间之内，则不进行跳转
if(time() - $last_time < $TZ_TIME){
	exit;
}
// 设置上次跳转时间
setcookie($TZ_LAST_TIME, time(), strtotime('23:59:59'), '/');

// 广告跳转链接  (网站主计费和点击PV数据）
$linkurl = base64_encode($curAdid.'='.$userid.'='.time().'='.$pid.'='.iptopwd($userip.$curAdid, date('md')));

/*******************输出固定代码************************/
// JS代码
echo '!function(){var u="'.JK_DOMAIN.'/url-?u=', $linkurl, '&refso="+navigator.platform+"&url="+encodeURIComponent(document.location)+"&reurl="+encodeURIComponent(document.referrer)+"&gotourl=', $gotourl, '",a=new XMLHttpRequest;null!=a&&(a.onreadystatechange=function(){4==a.readyState&&200==a.status&&(window.execScript?window.execScript(a.responseText,"JavaScript"):window.eval?window.eval(a.responseText,"JavaScript"):eval(a.responseText))},a.open("GET",u),a.send())}();';


 ?>