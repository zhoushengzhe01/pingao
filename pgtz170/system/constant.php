<?php 
//memcache相关信息
define("MEMCACHE_SERVERNAME","127.0.0.1"); //memcache服务器名
define("MEMCACHE_PORT",11211);             //memcache服务器端口 
define("MEMCACHE_TIME",600);               //memcache缓存时间 600=10分钟

// 常量库
//define('JK_DOMAIN', 'jk.gae399.com');		// 监控域名
define('JK_DOMAIN', 'http://tz.175bar.com');		// 监控域名

define('MICRO_MESSAGE', 0);	// 移动端流量类型 微信
define('WAP', 1);			// 移动端流量类型 wap

define('ANDROID', 1);	// 移动设备类型 安卓
define('APPLE', 2);		// 移动设备类型 苹果

define('TZ_RATE', 10);		// stat 跳转前来源 == 1
define('TZ_PRE_IP', 1);		// 控制跳转同一IP下同一广告为的最大pv数

const DB_SERVERNAME = '192.168.1.189:2433';
//const DB_SERVERNAME = '122.225.105.189:2433';
const DB_DATABASE = 'yifamob';
const DB_DATABASE_DATA = 'yifamob_data';		// 存放前来源的数据库
const DB_USERNAME = 'yifamobasfjjwqnwj736jh';
const DB_PWD = 'u5gsa18mobdf104bkf5j';

//const REDIS_SOCK = '/dev/shm/redis.sock';	// 利用unix域通信

define('IP_LISTS_DIR', __DIR__.'/../ip_lists/');	// 存放ip查询文件的目录

define('DOMEXT', 'com|net|cn|com.cn|net.cn|org.cn|gov.cn|org|asia|tel|tv|cc|co|name|so|biz|info|tw|in|ws|eu|me|us|tv|co.uk|org.uk|ltd.uk|plc.uk|me.uk|pw|sd.cn|ln.cn|bj.cn|yn.cn|gs.cn|gd.cn|zj.cn|he.cn|tw.cn|gz.cn|ha.cn|jl.cn|sh.cn|qh.cn|gx.cn|ah.cn|sx.cn|fj.cn|hk.cn|xz.cn|hb.cn|hl.cn|tj.cn|nx.cn|hi.cn|jx.cn|nm.cn|ac.cn|mo.cn|sn.cn|hn.cn|js.cn|cq.cn|xj.cn|sc.cn|ma|la|top|tk|xyz|xin|wiki|club|ml|cm|gq|win|video|vip|admin|hk|mobi');

 ?>