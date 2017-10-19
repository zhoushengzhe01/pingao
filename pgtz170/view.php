<?php 
// 作用：
// 1.做好脚本参数配置，http头输出。
// 2.判断流量设备类型和流量类型。
// 3.路由到具体脚本，并传入格式化好的参数 $get 。
// 调试模式
// ini_set('display_errors', '1');
// error_reporting(E_ALL);
// ini_set('opcache.enable', '0');

// 生产模式
/*ini_set('display_errors', '0');
error_reporting(null);*/
header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
date_default_timezone_set('asia/shanghai');
header("Content-Type: text/html;charset=utf-8"); 
header('Access-Control-Allow-Origin: *');


require __DIR__.'/system/constant.php';
require __DIR__.'/system/function.php';

// 导出手机流量
$istype = null;	// 是否是微信
$ptype = null;	// 移动端类型
$agent_type = $_SERVER['HTTP_USER_AGENT'];
if(strpos($agent_type, 'MicroMessenger') !== FALSE){
	$istype = MICRO_MESSAGE;
	exit;
}else{
	$istype = WAP;
}

if(strpos($agent_type, 'Android') !== FALSE){
	$ptype = ANDROID;
}else if(strpos($agent_type, 'iPad') !== FALSE 
	OR strpos($agent_type, 'iPhone') !== FALSE){
	$ptype = APPLE;
}else{
	exit;
}

$path_info = substr($_SERVER['PATH_INFO'], 1);	// 去掉开头的 斜线 /

// 路由+参数格式化
// var_dump($params);
$params = explode('-', $path_info);

switch(strtolower($params[0])){

	case 'tz':	// 跳转广告
		ob_start();
		$get['p'] = $params[1];
		require __DIR__ . '/tz/tzScript.php';
		ob_end_flush();
		break;
	case 'url':		// 收集前来源脚本
		require __DIR__.'/tz/url.php';
		break;
	case 'zl':

	    $get['p'] = (int)$params[1];
	    $web_id = isset($params[3]) ? intval($params[3]) : 0;
	    $un_ads_ids = isset($params[4]) ? trim($params[4]) : '';
		require __DIR__ . '/tz/zlScript.php';

 	    break;
	case 'bg':
		$get['p'] = $params[1];
		$get['f'] = $params[2];
		$web_id = isset($params[3]) ? intval($params[3]) : 0;
		$un_ads_ids = isset($params[4]) ? trim($params[4]) : '';
		require __DIR__ . '/tz/bgScript.php';
		break;
	case 'andr':
		$get['p'] = $params[1];
		$get['f'] = $params[2];
		$web_id = isset($params[3]) ? intval($params[3]) : 0;
		$un_ads_ids = isset($params[4]) ? trim($params[4]) : '';
		require __DIR__ . '/tz/andrScript.php';
		break;
	case 'statpv':
		$get['p'] = $params[1];
		$get['f'] = $params[2];
		$web_id = isset($params[3]) ? intval($params[3]) : 0;
		$un_ads_ids = isset($params[4]) ? trim($params[4]) : '';
		require __DIR__ . '/tz/bgScript.php';
		break;
	case 'statspv':
		$get['p'] = $params[1];
		$get['f'] = $params[2];
		$web_id = isset($params[3]) ? intval($params[3]) : 0;
		$un_ads_ids = isset($params[4]) ? trim($params[4]) : '';
		require __DIR__ . '/tz/andrScript.php';
		break;
	default :
		die('您一定是搞错了什么！');
}


?>