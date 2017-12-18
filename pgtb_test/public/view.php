<?php
date_default_timezone_set('PRC');

#ini_set("display_errors", "On");

#error_reporting(E_ALL | E_STRICT);

header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');

header('Access-Control-Allow-Origin:*');

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
*/

require_once __DIR__.'/../vendor/autoload.php';

define('__ROOT__', __DIR__);
/*
|--------------------------------------------------------------------------
| According to the routing controller
|--------------------------------------------------------------------------
*/

$route = [
	'get'=>[
		'/url'=>'ClickController@clickAction',
		'/se'=>'PvController@pvAction',
		'/config'=>'ConfigController@getAction',
		'/[0-9]+/[0-9]+'=>'AdsController@adsAction',
	],
	'post'=>[
		'/config'=>'ConfigController@postAction',
	],
];

$request = explode("?", trim($_SERVER['REQUEST_URI']));
$request_url = empty($request[0]) ? '/' : $request[0];

$method = strtolower( trim( $_SERVER['REQUEST_METHOD'] ) );

if(!is_array($route[$method]))
{
	die("没有路由");
}

//匹配URl
foreach($route[$method] as $k=>$v)
{
    if (preg_match("#^".$k."#", $request_url))
    {
    	$value = $v;
    }
}

preg_match_all('/[0-9]+/i', $request_url, $paramet);

implode(", ", $paramet[0]);

$array = explode('@', $value);

$file = trim($array[0]);

$function = trim($array[1]);

if(isset($file) && isset($function))
{
	eval('$obj = new \app\\'.$file.'();$obj->'.$function.'('.implode(", ", $paramet[0]).');');
};