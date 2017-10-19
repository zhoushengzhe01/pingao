<?php 
/*ini_set("display_errors", "On");
error_reporting(E_ALL);*/
ini_set('date.timezone','Asia/Shanghai');
header("Content-Type: text/html;charset=utf-8");

// 记录点击PV数据 和 点击UV数据
$jafjpp = explode('&', base64_decode($_GET['s'] ? $_GET['s'] : null));

if(count($jafjpp) != 8){
    ?>
         document.writeln("参数错误！");
    <?php
    exit;
}

$jkpic = trim($jafjpp[0]);
$adsid = htmlspecialchars(trim($jafjpp[1]));
$userid = htmlspecialchars(trim($jafjpp[2]));

$pid = trim($jafjpp[4]);
$tid = htmlspecialchars(trim($jafjpp[5]));

$secret_key = htmlspecialchars(trim($jafjpp[6]));

$memcache=new Memcache;
$result=$memcache->pconnect(MEMCACHE_SERVERNAME,MEMCACHE_PORT);

$ads_info = $memcache->get('pgflow_adsinfo');
$ads = $ads_info[$adsid];
$gotourl = $ads['gotourl'];  //？？？

if(!$gotourl){
	uptype($tid, $memcache);
	$ads_info = $memcache->get('pgflow_adsinfo');
    $ads = $ads_info[$adsid];
    $gotourl = $ads['gotourl'];
}

if(!$gotourl){
    $gotourl = base64_decode($jafjpp[7]);
    $gotourl = str_replace('pguid', $userid, $gotourl);
    header('Location: ' . $gotourl);
    exit;
}

$gotourl = str_replace('pguid', $userid, $gotourl);

if(!$_SERVER['HTTP_REFERER']){
    header('Location: ' . $gotourl);
    exit;
}

// 导出手机流量
$myagent = $_SERVER['HTTP_USER_AGENT'];
if(strpos($myagent, 'Mobile') === FALSE){
	header('Location: ' . $gotourl);
	exit;
}

// 如果不是有效设备来源，则退出。
if(!(strpos($myagent, 'Android') !== FALSE 
	OR strpos($myagent, 'iPad') !== FALSE 
	OR strpos($myagent, 'iPhone') !== FALSE)){

	header('Location: '.$gotourl);
    exit;
}

$jilu = true;

$userip = get_ip();					// 获取IP地址
$userip_num = ip2long($userip);		// 将IP转换为数字
$useripaid = $userip . $adsid;

if(iptopwd($userip.$adsid, date('md')) != $secret_key){
	header('Location: ' . $gotourl);
    exit;
}

/////////////////////记录点击数据开始//////////////////////
$conn = openconn();
mssql_select_db(DB_DATABASE, $conn);

$refso = htmlspecialchars(trim($_GET['refso'] ? $_GET['refso'] : null));

if(strpos($refso, 'Mac') === false && strpos($refso, 'Win') === false){

	$is_cpc_count = $_COOKIE['pg_flow_cpc_'.$userip_num.$adsid] ? $_COOKIE['pg_flow_cpc_'.$userip_num.$adsid] : 0;
	$cpc_count = $_COOKIE['pg_flow_cpc_count'.$userip_num] ? $_COOKIE['pg_flow_cpc_count'.$userip_num] : 0;

	if((PRE_IP > $cpc_count) && !$is_cpc_count){
		
		$sp_cpc_count = mssql_init("xyz57_cpc", $conn);
		mssql_bind($sp_cpc_count, "@adsid", $adsid, SQLVARCHAR);
		mssql_bind($sp_cpc_count, "@uip", $userip_num, SQLVARCHAR);

	    $cpc_count_res = mssql_execute($sp_cpc_count,false);
	    $cpc_count_ads = mssql_fetch_row($cpc_count_res)[0];

	    mssql_free_statement($sp_cpc_count);
	    mssql_free_result($cpc_count_res);
	    
	    if(!$cpc_count_ads){
	    	$sp = mssql_init("vistdata57_cpc_count", $conn);

			mssql_bind($sp, "@userid", $userid, SQLINT2);
			mssql_bind($sp, "@adsid", $adsid, SQLVARCHAR);
			mssql_bind($sp, "@mip", $userip_num, SQLVARCHAR);
			mssql_bind($sp, "@pid", $pid, SQLINT2);

			mssql_execute($sp);
		    mssql_free_statement($sp);
		    setcookie('pg_flow_cpc_count'.$userip_num, $cpc_count+1, strtotime('23:59:59'));
			setcookie('pg_flow_cpc_'.$userip_num.$adsid, 1, strtotime('23:59:59'));
			$jilu = false;
	    }
		
	}
}

/****************************记录前来源***************************/
$refer_rand = mt_rand(1, 2);
if($refer_rand == 1){
	mssql_select_db(DB_DATABASE_DATA, $conn);

	$url2 = htmlspecialchars($_GET['url'] ? $_GET['url'] : null);
	$firsturl = htmlspecialchars($_GET['reurl'] ? $_GET['reurl'] : null);	// 获取来源

	$refso = htmlspecialchars(trim($_GET['refso'] ? $_GET['refso'] : null));

	$lailux = $_SERVER['HTTP_USER_AGENT'];

	$firsturl = substr($firsturl, 0, 450);
	$url2 = substr($url2, 0, 450);

	if($url2 || $firsturl || $refso){

		$sp_refer = mssql_init("vistdata_cpccountinfos", $conn);

		mssql_bind($sp_refer, "@fromid", $userid, SQLVARCHAR);
		mssql_bind($sp_refer, "@toid", $adsid, SQLVARCHAR);
		mssql_bind($sp_refer, "@firsturl", $firsturl, SQLVARCHAR);
		mssql_bind($sp_refer, "@fromurl", $url2, SQLVARCHAR);
		mssql_bind($sp_refer, "@ip", $userip, SQLVARCHAR);
		mssql_bind($sp_refer, "@numberip", $userip_num, SQLVARCHAR);
		mssql_bind($sp_refer, "@tourl", $lailux, SQLVARCHAR);
		mssql_bind($sp_refer, "@pid", $pid, SQLVARCHAR);
		mssql_bind($sp_refer, "@refso", $refso, SQLVARCHAR);
		mssql_bind($sp_refer, "@tid", $tid, SQLVARCHAR);

		mssql_execute($sp_refer);

		mssql_free_statement($sp_refer);
	}
}

$reloadok = null;
if(!$jilu){
	$rand = mt_rand(1,2);
	if($rand == 1){
		// 判断总量是否完毕
		$sql = 'select userid from user2  where userid='.$ads['userid'].' and admoney<5';
		$stmt = mssql_query($sql);
		if($stmt !== false){
			$reloadok = TRUE;
			mssql_free_result($stmt);
		}
	}
}else{
	// 判断每日限量是否超标
	$todaymoney = $ads['todaymoney'.date('Y-m-d')];
	$todaynowpop = $ads['todaymaxmoney'.date('Y-m-d')];
	
	if($todaynowpop > 0 AND ($todaynowpop <= $todaymoney)){
		$reloadok = TRUE; 
	}
	
}
colseconn($conn);
// 总量 或 每日量 或 小时峰值到则更新广告池
if($reloadok){
	uptype($tid, $memcache,true);
}

header('Location: '.$gotourl);