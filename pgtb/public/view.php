<?php
date_default_timezone_set('PRC');

// ini_set("display_errors", "On");

// error_reporting(E_ALL | E_STRICT);

header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');

header('Access-Control-Allow-Origin:*');

/**
 * 程序文件
 * 
 * 这里加载是必要文件
 */

require __DIR__.'/../bootstrap.php';

/***
 * 这里出发应用程序入口
 *
 * 调用触发应用程序
 */
 
$loading = require __DIR__.'/../config/routes.php';

Route::save();

Route::matchingRoute();