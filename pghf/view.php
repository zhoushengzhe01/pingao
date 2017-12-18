<?php
/*ini_set("display_errors", "On");
error_reporting(E_ALL);*/
ini_set('date.timezone','Asia/Shanghai');//设置服务器时间
header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
header("Content-Type: text/html;charset=utf-8");

header('Access-Control-Allow-Origin:*');

isset($_SERVER['HTTP_REFERER']) || exit;

$istype = null;	// 是否是微信
$agent_type = $_SERVER['HTTP_USER_AGENT'];
if(strpos($agent_type, 'MicroMessenger') !== FALSE){
	$istype = 0;
}else{
	$istype = 1;
}

$ptype = null;	// 移动端类型
if(strpos($agent_type, 'Android') !== FALSE){
	$ptype = 1;
}else if(strpos($agent_type, 'iPad') !== FALSE 
	OR strpos($agent_type, 'iPhone') !== FALSE){
	$ptype = 2;
}else{
	exit;
}

$path_info = substr($_SERVER['PATH_INFO'],1);// 去掉开头的 斜线 /

$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;

if(($pid == 2324 || $pid == 2564) && $istype == 0){
	exit;
}

// 路由+参数格式化
if($path_info){
    
	require 'system/constants.php';
	require 'system/function.php';

    switch(strtolower($path_info)){
		case 'v.js':	// yfScript.php 顶部横幅
			$ad_pos=1;
			
			require 'info.php';
			break;
		case 'y.js':
		    $ad_pos = 2;
		  
		    require 'info.php';
		    break;
		case 'or':
            require 'error.php';
            break;
        case 're':
            require 'refer.php';
            break;
        case 'se':
            require 'statspv.php';
            break;

		default :
			die('您一定是搞错了什么！');
	}
}
