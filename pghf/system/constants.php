<?php
//memcache相关信息
define("MEMCACHE_SERVERNAME","127.0.0.1"); //memcache服务器名
define("MEMCACHE_PORT",11211);             //memcache服务器端口 
define("MEMCACHE_TIME",1500);               //memcache缓存时间 600=10分钟

const DB_SERVERNAME = '192.168.1.81:2433';

const DB_DATABASE = 'yifamob';
const DB_DATABASE_DATA = 'yifamob_data';		// 存放前来源的数据库
const DB_USERNAME = 'yifamobasfjjwqnwj736jh';
const DB_PWD = 'u5gsa18mobdf104bkf5j';
const HF_RATE = 20;
const ANDROID = 1;		// 移动设备类型 安卓
const APPLE = 2;		// 移动设备类型 苹果

const TOP_BANNER = 1;			// 顶部横幅
const BOTTOM_BANNER = 2;			// 底部横幅

define('WX_TZ_DOMAIN', 'http://phd.5177jy.com');// 微信端跳转广告域名
define('TZCODE_DOMAIN', 'http://tz.zjhoudao.com');// 内刷获取跳转代码域名

$ADS_CONFIG = ['num'=>3];

const PICURLURL = 'http://ey.hmj800.com/11/';	// 其他素材地址


const PRE_IP = 6;		// 同一用户在同一网站主同一广告位下的最大pv记数

const DOMEXT = 'com|net|cn|com.cn|net.cn|org.cn|gov.cn|org|asia|tel|tv|cc|co|name|so|biz|info|tw|in|ws|eu|me|us|tv|co.uk|org.uk|ltd.uk|plc.uk|me.uk|pw|sd.cn|ln.cn|bj.cn|yn.cn|gs.cn|gd.cn|zj.cn|he.cn|tw.cn|gz.cn|ha.cn|jl.cn|sh.cn|qh.cn|gx.cn|ah.cn|sx.cn|fj.cn|hk.cn|xz.cn|hb.cn|hl.cn|tj.cn|nx.cn|hi.cn|jx.cn|nm.cn|ac.cn|mo.cn|sn.cn|hn.cn|js.cn|cq.cn|xj.cn|sc.cn|ma|la|top|tk|xyz|xin|wiki|club|ml|cm|gq|win|video|admin|hk|mobi|ren|cf|ga|bid|fm|wang|pub|vc|ltd|gift';
