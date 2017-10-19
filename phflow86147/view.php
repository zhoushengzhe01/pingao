<?php
// 调试模式
/*ini_set('display_errors', '1');
error_reporting(E_ALL);*/
header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
header('Access-Control-Allow-Origin: *');
header('Content-Type: text/html; Charset=utf-8;');

date_default_timezone_set('Asia/ShangHai');

require __DIR__.'/system/constants.php';
require __DIR__.'/system/function.php';

$agent_type = $_SERVER['HTTP_USER_AGENT'] ? $_SERVER['HTTP_USER_AGENT'] : '';

if(strpos($agent_type, 'Mobile') === FALSE){
	exit;
}

// 导出手机流量
$istype = null;	// 是否是微信
if(strpos($agent_type, 'MicroMessenger') !== FALSE){
	$istype = MICRO_MESSAGE;
	exit;
}else{
	$istype = WAP;
}

$ptype = null;	// 移动端类型
if(strpos($agent_type, 'Android') !== FALSE){
	$ptype = ANDROID;
}else if(strpos($agent_type, 'iPad') !== FALSE 
	OR strpos($agent_type, 'iPhone') !== FALSE){
	$ptype = APPLE;
}else{
	exit;
}

// 用pathinfo形式替换 get查询参数  兼容原来的写法
$path_info = substr($_SERVER['PATH_INFO'], 1);	// 去掉开头的 斜线 /

// 路由+参数格式化
if($path_info){
	$params = explode('/', $path_info);

	switch(strtolower($params[0])){

		case 'fw.html':
		    $pid = $params[1] ? intval($params[1]) : 0;
		    $ads_type = $params[2] ? intval($params[2]) : 0;
		    require __DIR__.'/flow/flow.php';
		    break;

		case 'url':
            require __DIR__.'/flow/url.php';
            break;
            
        case 'se':
            require __DIR__.'/flow/pv.php';
            break;

		default :
			die('您一定是搞错了什么！');
	}
	
}
