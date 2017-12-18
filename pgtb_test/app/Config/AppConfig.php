<?php
namespace app\Config;


class AppConfig 
{
    public static function init()
    {
        return [

            // 系统名称
            'name' => '品告代码统计',

            //系统缓存地址
            'cache' => __ROOT__ . '/../cache',

            // 漏油缓存地址
            'route_cache' => __ROOT__ . '/../cache/routes',

            // 缓存配置文件路径
            'config_path' => __ROOT__ . '/../cache/config',

            // ip缓存地址
            'ip_path' => __ROOT__ . '/../cache/ipLists',

            // 日志文件地址
            'log_path'=> __ROOT__ . '/../cache/logs',

            // 允许的设备
            'permit_sys' => ['Android', 'iPad', 'iPhone', 'Windows'],

            //广告类型ID
            'type_id' => 69,

            //密钥
            'token' => 'hsD565dd65s65sd8654684646',

            //广告取出数量
            'number' => 3,

            //切换速度
            'rate' => 20,

            //其他素材地址
            'pic_url' => 'http://ey.hmj800.com/11/',

            //特殊广告输出
            'special_ads_id' => [152],

            //同一用户在同一网站主同一广告位下的最大pv记数
            'pre_ip' => 5,

            //图标的边框，大小，位置，pv记录，切换速度
            'status' => [
                'default' => [ 'border'=>2, 'width'=>25, 'top'=>40, 'recordpv'=>10, 'rate'=>12000 ],
                '4007' => [ 'border'=>2, 'width'=>25, 'top'=>40, 'recordpv'=>80, 'rate'=>12000 ],
                '4963' => [ 'border'=>2, 'width'=>25, 'top'=>20, 'recordpv'=>10, 'rate'=>12000 ],
                '5099' => [ 'border'=>2, 'width'=>20, 'top'=>32, 'recordpv'=>10, 'rate'=>12000 ],
                '5122' => [ 'border'=>2, 'width'=>22, 'top'=>30, 'recordpv'=>10, 'rate'=>12000 ],
                '4860' => [ 'border'=>2, 'width'=>25, 'top'=>32, 'recordpv'=>10, 'rate'=>12000 ],
            ],

            // memcache
            'memcache' => [
                'host'=>'127.0.0.1',    //连接地址
                'port'=>'11211',       //端口
                'time'=>1200,        //缓存时间
                'isP'=>false,       //是否常连接
            ],
            
            // mssql
            'mssql' => [
                'host' => '192.168.1.115',  
                'port' => '2433',
                'database' => 'yifamob',
                'username' => 'yifamobasfjjwqnwj736jh',
                'password' => 'u5gsa18mobdf104bkf5j',
                'isP' => true,
            ],

            // mssql_date
            'mssql_date' => [
                'host' => '192.168.1.115',
                'port' => '2433',
                'database' => 'yifamob_data',
                'username' => 'yifamobasfjjwqnwj736jh',
                'password' => 'u5gsa18mobdf104bkf5j',
                'isP' => true,
            ],

            //配置误点计费机率
            'fault' => ['default'=>70, '4007' => 78],

            //允许的后缀域名
            'domain_suffix' => ['com', 'net', 'cn', 'com.cn', 'net.cn', 'org.cn', 'gov.cn', 'org', 'asia', 'tel', 'tv', 'cc', 'co', 'name', 'so', 'biz', 'info', 'tw', 'in', 'ws', 'eu', 'me', 'us', 'tv', 'co.uk', 'org.uk', 'ltd.uk', 'plc.uk', 'me.uk', 'pw', 'sd.cn', 'ln.cn', 'bj.cn', 'yn.cn', 'gs.cn', 'gd.cn', 'zj.cn', 'he.cn', 'tw.cn', 'gz.cn', 'ha.cn', 'jl.cn', 'sh.cn', 'qh.cn', 'gx.cn', 'ah.cn', 'sx.cn', 'fj.cn', 'hk.cn', 'xz.cn', 'hb.cn', 'hl.cn', 'tj.cn', 'nx.cn', 'hi.cn', 'jx.cn', 'nm.cn', 'ac.cn', 'mo.cn', 'sn.cn', 'hn.cn', 'js.cn', 'cq.cn', 'xj.cn', 'sc.cn', 'ma', 'la', 'top', 'tk', 'xyz', 'xin', 'wiki', 'club', 'ml', 'cm', 'gq', 'win', 'video', 'admin', 'hk', 'mobi', 'ren', 'cf', 'ga', 'bid', 'fm', 'wang']
        ];
    }


    // 调用用的
    public static function get($position)
    {
        //初始
        $value = self::init();

        $array = explode('.', $position);

        foreach($array as $k=>$v)
        {
            if( empty($value[$v]) )
            {
                return false;
            }
            else
            {
                $value = $value[$v];
            }
        }

        return $value;
    }
}

