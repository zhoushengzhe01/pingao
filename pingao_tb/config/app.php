<?php

return [

    // 系统名称
    'name' => '品告代码统计',

    //系统缓存地址
    'cache' => __ROOT__ . '/app/cache',

    // 漏油缓存地址
    'route_cache' => __ROOT__ . '/app/cache/routes',

    // ip缓存地址
    'ip_path' => __ROOT__ . '/app/cache/ipLists',

    // 日志文件地址
    'log_path'=> __ROOT__ . '/app/cache/logs',

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
    'pic_url' => 'http://ey.hmj800.com/7/',

    //特殊广告输出
    'special_ads_id' => [152],

    //同一用户在同一网站主同一广告位下的最大pv记数
    'pre_ip' => 5,

    //允许的后缀域名
    'domain_suffix' => ['com', 'net', 'cn', 'com.cn', 'net.cn', 'org.cn', 'gov.cn', 'org', 'asia', 'tel', 'tv', 'cc', 'co', 'name', 'so', 'biz', 'info', 'tw', 'in', 'ws', 'eu', 'me', 'us', 'tv', 'co.uk', 'org.uk', 'ltd.uk', 'plc.uk', 'me.uk', 'pw', 'sd.cn', 'ln.cn', 'bj.cn', 'yn.cn', 'gs.cn', 'gd.cn', 'zj.cn', 'he.cn', 'tw.cn', 'gz.cn', 'ha.cn', 'jl.cn', 'sh.cn', 'qh.cn', 'gx.cn', 'ah.cn', 'sx.cn', 'fj.cn', 'hk.cn', 'xz.cn', 'hb.cn', 'hl.cn', 'tj.cn', 'nx.cn', 'hi.cn', 'jx.cn', 'nm.cn', 'ac.cn', 'mo.cn', 'sn.cn', 'hn.cn', 'js.cn', 'cq.cn', 'xj.cn', 'sc.cn', 'ma', 'la', 'top', 'tk', 'xyz', 'xin', 'wiki', 'club', 'ml', 'cm', 'gq', 'win', 'video', 'admin', 'hk', 'mobi', 'ren', 'cf', 'ga', 'bid', 'fm', 'wang']

    //'domext' => 'com|net|cn|com.cn|net.cn|org.cn|gov.cn|org|asia|tel|tv|cc|co|name|so|biz|info|tw|in|ws|eu|me|us|tv|co.uk|org.uk|ltd.uk|plc.uk|me.uk|pw|sd.cn|ln.cn|bj.cn|yn.cn|gs.cn|gd.cn|zj.cn|he.cn|tw.cn|gz.cn|ha.cn|jl.cn|sh.cn|qh.cn|gx.cn|ah.cn|sx.cn|fj.cn|hk.cn|xz.cn|hb.cn|hl.cn|tj.cn|nx.cn|hi.cn|jx.cn|nm.cn|ac.cn|mo.cn|sn.cn|hn.cn|js.cn|cq.cn|xj.cn|sc.cn|ma|la|top|tk|xyz|xin|wiki|club|ml|cm|gq|win|video|admin|hk|mobi|ren|cf|ga|bid|fm|wang'
    
];