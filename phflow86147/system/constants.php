<?php
// 常量库	
//memcache相关信息
define("MEMCACHE_SERVERNAME","127.0.0.1"); //memcache服务器名
define("MEMCACHE_PORT",11211);             //memcache服务器端口 
define("MEMCACHE_TIME",1500);               //memcache缓存时间 600=10分钟
	
define('MICRO_MESSAGE', 0);	// 移动端流量类型 微信
define('WAP', 1);			// 移动端流量类型 wap

define('ANDROID', 1);	// 移动设备类型 安卓
define('APPLE', 2);		// 移动设备类型 苹果

define('WX_TZ_DOMAIN', 'http://fd.yide69.com');//微信端计费域名
define('TZCODE_DOMAIN', 'http://tz.huayi65.com');// 内刷获取跳转代码域名
define('PICURLURL', 'http://im1.56zzw.com/7/'); // 其他素材地址

define('DB_DATABASE', 'yifamob');
const DB_DATABASE_DATA = 'yifamob_data';		// 存放前来源的数据库

define('DB_USERNAME', 'yifamobasfjjwqnwj736jh');
define('DB_PWD', 'u5gsa18mobdf104bkf5j');

const DB_SERVERNAME = '192.168.1.189:2433';

const IS_SHOW_LOGO = 0;//是否显示公司名称

define('PRE_IP', 4);    // 同一用户在同一网站主同一广告位下的最大pv记数

define('IP_LISTS_DIR', __DIR__.'/../ip_lists/');	// 存放ip查询文件的目录

$ADS_TYPE_INFO = [//展示的广告类型相关信息num广告数量，column列数，img_type图片尺寸,show_num展示的广告数量
                    '1'=>['num'=>2, 'column'=>2, 'img_type'=>'300x240','show_num'=>2], 
                    '2'=>['num'=>3, 'column'=>3, 'img_type'=>'200x240', 'show_num'=>3],
                    '3'=>['num'=>4, 'column'=>2, 'img_type'=>'300x240', 'show_num'=>4],
                    '4'=>['num'=>6, 'column'=>3, 'img_type'=>'200x240' ,'show_num'=>6],
                    '5'=>['num'=>2, 'column'=>1, 'img_type'=>'', 'show_num'=>1],
                    '6'=>['num'=>2, 'column'=>1, 'img_type'=>'', 'show_num'=>1],
                    '7'=>['num'=>2, 'column'=>1, 'img_type'=>'', 'show_num'=>1],
                    '8'=>['num'=>2, 'column'=>1, 'img_type'=>'300x240', 'show_num'=>1]
                ];

const DOMEXT = 'com|net|cn|com.cn|net.cn|org.cn|gov.cn|org|asia|tel|tv|cc|co|name|so|biz|info|tw|in|ws|eu|me|us|tv|co.uk|org.uk|ltd.uk|plc.uk|me.uk|pw|sd.cn|ln.cn|bj.cn|yn.cn|gs.cn|gd.cn|zj.cn|he.cn|tw.cn|gz.cn|ha.cn|jl.cn|sh.cn|qh.cn|gx.cn|ah.cn|sx.cn|fj.cn|hk.cn|xz.cn|hb.cn|hl.cn|tj.cn|nx.cn|hi.cn|jx.cn|nm.cn|ac.cn|mo.cn|sn.cn|hn.cn|js.cn|cq.cn|xj.cn|sc.cn|ma|la|top|tk|xyz|xin|wiki|club|ml|cm|gq|win|video|vip|admin|hk|mobi|ren|cf|ga|bid|fm|wang';

