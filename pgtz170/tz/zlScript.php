<?php 
// 直链代码

$OK_PID = [2001,2034,2138,2040,2052];		// 允许直链的pid
define('AD_TYPE', 68);
/********************接收传入参数.***********/
// 广告位置
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
// 有广告位ID传入,则使用对应网站主ID,不管userid是否传入,以广告位ID为准
$data_adp = upADPosition($pid, AD_TYPE, $memcache);
$userid = $data_adp['userid'];

$data_user1 = upUser1Info($userid, AD_TYPE, $memcache);	// 载入网站主信息

/*********************状态判断****************************/
if($data_user1['zhuangtai'] != 1){
	?>
	    document.writeln("您的帐号未开通或被冻结");
	<?php
	exit;
}

if(strpos($data_user1['openty'], (string)AD_TYPE) === FALSE){
	?>
	    document.writeln("您的帐号未开通横幅广告");
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

if(empty($nowdic)){
	?>
	    document.writeln("没有符合条件的广告");
	<?php
	exit;
}

// *******************排除是否展示********************
$ads_array=$nowdic;
$newads=array();
$xianshiads=array();
$adscycle=1;

if($_COOKIE['pg_zl_adscycle'])
{
	$adscycle=$_COOKIE['pg_zl_adscycle'];
}

foreach($ads_array as $ads_one)
{
	if($_COOKIE['pg_zl_'.$ads_one]!=$adscycle)
	{
		$newads[]=$ads_one;
	}else{
		$xianshiads[]=$ads_one;
	}
	
}
if(!$newads)
{
	$newads=$xianshiads;
	$adscycle=$adscycle+1;
	
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

//设置COOKies,广告ID
if($curAdid)
{
	$time_over=mktime(date("H"),59,59,date("n"),date("d"),date("Y"));
	$cookiename="pg_zl_".$curAdid;
	setcookie($cookiename,$adscycle,$time_over);
	setcookie('pg_zl_adscycle',$adscycle,$time_over);
	
} 


// --------------------第三步:随机选择的广告ID="&curAdid&"<br>';
// 记录pv数据
// 只有指定pid的广告位能够计费

$cookie_pv = 'pg_pvzl'.AD_TYPE.$userip_num.'_adsid_'.$curAdid;
$pv_count = $_COOKIE[$cookie_pv] ? $_COOKIE[$cookie_pv] : 0;


$tz_cookie = $_COOKIE['pg_tzzl'.AD_TYPE.$userip_num.'_adsid_'.$curAdid] ?: 0;
$tz_flag = false;
if(!$tz_cookie){
	$tz_flag = true;
}

// 用户在同一个网站主下pv数小于一定值为有效pv  

if(TZ_PRE_IP > $pv_count){
    $conn = openconn();
	mssql_select_db(DB_DATABASE, $conn);
	$sp = mssql_init("xyz68_cpd_insert", $conn);
	mssql_bind($sp, "@adsid", $curAdid, SQLVARCHAR);
	mssql_bind($sp, "@uip", $userip_num, SQLVARCHAR);
	mssql_bind($sp, "@pid", $pid, SQLINT2);
    mssql_bind($sp, "@userid", $userid, SQLINT2);
    mssql_bind($sp, "@u2id", $web_id, SQLINT2);
    mssql_bind($sp, "RETVAL", $res, SQLINT4, TRUE); 
    mssql_execute($sp,false);

    mssql_free_statement($sp);

    if(!$res){

		setcookie('pg_pvzl'.AD_TYPE.$userip_num.'_adsid_'.$curAdid, $pv_count+1, time()+24*60*60);
        $tz_flag = true;

    }else{

    	$nowdic = [];

		$sp_mip = mssql_init("xyz68_cpd_mip_unique", $conn);

		mssql_bind($sp_mip, "@uip", $userip_num, SQLVARCHAR);

		$mip_res = mssql_execute($sp_mip);
	    while($row = mssql_fetch_assoc($mip_res)){
	    	$arr[] = $row['adsid'];
	    }

		mssql_free_statement($sp_mip);

		foreach ($ads_array as $v) {

			if(!in_array($v, $arr)){

				$nowdic[] = $v;
			}
		}
		
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

			$sp = mssql_init("xyz68_cpd_insert", $conn);
			mssql_bind($sp, "@adsid", $curAdid, SQLVARCHAR);
			mssql_bind($sp, "@uip", $userip_num, SQLVARCHAR);
			mssql_bind($sp, "@pid", $pid, SQLINT2);
		    mssql_bind($sp, "@userid", $userid, SQLINT2);
		    mssql_bind($sp, "@u2id", $web_id, SQLINT2);
		    mssql_bind($sp, "RETVAL", $res, SQLINT4, TRUE); 
		    mssql_execute($sp,false);

		    mssql_free_statement($sp);

		    if(!$res){

		    	setcookie('pg_pvzl'.AD_TYPE.$userip_num.'_adsid_'.$curAdid, $pv_count+1, time()+24*60*60);
		    }

            $tz_flag = true;
		}else{
	        
		    $nowdic = $ads_array;
		    $dicstr = '';
			$totalweight = 0;
			$weightdic_now = '';

			shuffle($nowdic);
			foreach($nowdic as $val){
				$dicstr .= ',' . $val;
			    $weightdic_now .= ',' . $ads[$val]['pv_weight'];
			    $totalweight += $ads[$val]['pv_weight'];
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

			$tz_flag = true; 
		}
	}
	colseconn($conn);  
}

if($tz_flag){
	setcookie('pg_tzzl'.AD_TYPE.$userip_num.'_adsid_'.$curAdid, 1, time()+10*60);
	header('Location: '. $ads[$curAdid]['gotourl']);
}


