<?php 
// 收集用户的信息
$gotourl = base64_decode($_GET['gotourl']);
$jafjpp = explode('=', base64_decode($_GET['u'] ? $_GET['u'] : null));

if(count($jafjpp) != 5){
	//outerr('参数错误');
	?>
	    document.writeln("参数错误！");
	<?php
	exit;
}

$rndadspv = mt_rand(1, TZ_RATE);
if($rndadspv < 2){
	$toid = htmlspecialchars(trim($jafjpp[0]));
	$fromid = htmlspecialchars(trim($jafjpp[1]));
	$pid = trim($jafjpp[3]);
	$pin2 = htmlspecialchars(trim($jafjpp[4]));

	$userip = get_ip();								// 获取IP地址
	$userip_num = ip2long($userip);					// 将IP转换为数字
	$useripaid = $userip . $toid;
	$pin1 = iptopwd($useripaid, date('md'));

	$url2 = htmlspecialchars($_GET['url'] ? $_GET['url'] : null);
	$firsturl = htmlspecialchars($_GET['reurl'] ? $_GET['reurl'] : null);	// 获取来源
	$refso = htmlspecialchars(trim($_GET['refso'] ? $_GET['refso'] : null));

	$lailux = '';
	$lailuiis = $_SERVER['HTTP_USER_AGENT'];
	if($lailuiis != '' AND strpos($lailuiis, '(') !== FALSE AND strpos($lailuiis, ')') !== FALSE){

		$list1 = explode('(', $lailuiis)[1];
		$lailux = explode(')', $list1)[0];
	}

	// 导出手机流量
	$myagent = $_SERVER['HTTP_USER_AGENT'];
	if(strpos($myagent, 'Android') !== FALSE 
		OR strpos($myagent, 'iPad') !== FALSE
		OR strpos($myagent, 'iPhone') !== FALSE){

		// 检查url有效性
		if(trim($pin1) == trim($pin2)){

			$conn = openconn();

			$firsturl = substr($firsturl, 0, 450);
			$url2 = substr($url2, 0, 450);

			mssql_select_db(DB_DATABASE_DATA, $conn);

			$sp = mssql_init("vistdata_cpccountinfo", $conn);

			mssql_bind($sp, "@fromid", $fromid, SQLVARCHAR);
			mssql_bind($sp, "@toid", $toid, SQLVARCHAR);
			mssql_bind($sp, "@firsturl", $firsturl, SQLVARCHAR);
			mssql_bind($sp, "@fromurl", $url2, SQLVARCHAR);
			mssql_bind($sp, "@ip", $userip, SQLVARCHAR);
			mssql_bind($sp, "@numberip", $userip_num, SQLVARCHAR);
			mssql_bind($sp, "@tourl", $lailux, SQLVARCHAR);
			mssql_bind($sp, "@pid", $pid, SQLVARCHAR);
			mssql_bind($sp, "@refso", $refso, SQLVARCHAR);

			mssql_execute($sp);

			mssql_free_statement($sp);

			colseconn($conn);
		}
	}
}

if($gotourl)
	die(';(function(){window.location="'.$gotourl.'"})()');
else
	die('failed:'.time());
	
 ?>