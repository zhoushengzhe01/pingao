<?php

// 获取 二级域名+一级域名   Second-level domain
function getSLD($domain){
    if(trim($domain) == ''){
        return FALSE;
    }
    
    $pattern = '/(?:[a-z0-9-]){1,63}\.(?:' . DOMEXT . ')(?:\:\d+)?$/';
    if(preg_match($pattern, $domain, $matches)){
        return $matches[0];
    }
}

//连接数据库
function openconn()
{
    
    $msconnect = mssql_connect(DB_SERVERNAME, DB_USERNAME, DB_PWD);
    
    
    if (!$msconnect) {

        die("connect188:something is wrong,please try again！");
    }
    
    return $msconnect;
}

function colseconn($msconnect)
{
    mssql_close($msconnect);
}

//获取用户ip
function get_ip(){
    $realip = NULL;

    if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

        /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
        foreach($arr AS $ip){
            $ip = trim($ip);

            if($ip != 'unknown'){
                $realip = $ip;
                break;
            }
        }
    }elseif(isset($_SERVER['HTTP_CLIENT_IP'])){
        $realip = $_SERVER['HTTP_CLIENT_IP'];
    }else{
        $realip = $_SERVER['REMOTE_ADDR'];
    }
    
    return $realip;
}

//获取广告位信息
function upADPosition($pid,$tid,$memcache){
	$cache_name = 'pghf_adposition_'.$pid;
    $user_adp = $memcache->get($cache_name);
    if(!$user_adp){
        $conn=openconn();
        mssql_select_db(DB_DATABASE, $conn);
        //获取广告位信息
        $sql_adpos="SELECT top 1 userid,noadok,islogo,ispic,wadsclass,iosclass,istype FROM ADposition WHERE  typeid=".$tid." AND ID =".$pid;

        $stmt = mssql_query(  $sql_adpos);
        if( $stmt === false) {
            ?>
              document.writeln("Something went wrong,please try again");
           <?php 
           exit;
        }

        $result_adpos=mssql_fetch_array($stmt);
		//var_dump($result_adpos);

        if($result_adpos){
            //var_dump($result_adpos);
            //把相关信息写入到缓存中
            //广告位基础信息
            $memcache->set($cache_name,$result_adpos,MEMCACHE_COMPRESSED,MEMCACHE_TIME); 
        }
        
        mssql_free_result($stmt);
        
        //关闭
        colseconn($conn);

        return $result_adpos;
        
    }else{
        return $user_adp;
    }
}

//通过网站主userid查找网站主信息
function upuser1($uid,$tid,$memcache)
{
    $cache_name = 'pghf_userinfo_'.$uid;
    $userinfo = $memcache->get($cache_name);

    if(!$userinfo){
        $conn=openconn();
        mssql_select_db(DB_DATABASE, $conn);
        //获取网网站主相关信息
		
        $sql="SELECT username,openty,ifdomain,zhuangtai,iftype,webtypeid FROM dbo.user1 WHERE userid=".$uid." AND lmid=1";
        $stmt = mssql_query($sql);
        if($stmt === false) {
            ?>
                document.writeln("Something went wrong,please try again2");
            <?php
            exit;
        }
        $result=mssql_fetch_array($stmt);
        if($result){

            //获取网站主的网站信息
            $sql_url=" SELECT id,url FROM dbo.user1_url WHERE userid=".$uid." AND ok=1";
            $stmt_url = mssql_query($sql_url);

            if($stmt_url === false){
                ?>
                    document.writeln("Something went wrong,please try again");
                <?php 
                exit;
            }
            
            $urlstr="";
            while($res=mssql_fetch_array($stmt_url)) {
                $urlstr .= '('.$res['url'].'){'.$res['id'].'}';
            }
            $result['urlstr'] = $urlstr;
            $memcache->set($cache_name,$result,MEMCACHE_COMPRESSED,MEMCACHE_TIME);
			//echo $urlstr;
            mssql_free_result($stmt_url);
            
        }
        mssql_free_result($stmt);
        
        colseconn($conn);

        return $result;

    }else{

        return $userinfo;
    }
}

function durlck($netmain)
{
    if($netmain=="" || strlen($netmain)==0)
    {
        return false;
    }
    
    $pattern= "/([a-z0-9-]){1,63}\.(".DOMEXT. ")(\:\d+)?$/";
    if(preg_match($pattern, $netmain,$matches))
    {
        return $matches[0];
    }
}

//广告轮显完或者无广告或者整点 更开memcache数据
function uptype($tid,$memcache,$update = false)
{   
    $t_time=(string)(date("H"));
    if(!$memcache->get('pghf_adslist_isava'.$tid.'_'.$t_time) || $update)
    {
        $memcache->set('pghf_adslist_isava'.$tid.'_'.$t_time,1,MEMCACHE_COMPRESSED,MEMCACHE_TIME);
        $conn=openconn();
        mssql_select_db(DB_DATABASE, $conn);

        $sql='update ads set shenhe=2 from ads,user2 where ads.userid=user2.userid and ads.typeid=67 and ads.shenhe=1 and user2.admoney<5';

        mssql_query($sql);
        
        $sqlt = 'SELECT ISNULL(ads.weight, 1) AS weight1,ads.tznum,ads.unshow_phone,ads.id,ads.istype,ads.okarea,ads.phonetype,ads.userid,ads.picurl0,ads.picurl1,ads.picurl2,ads.gotourl,ads.blacksiteid,ads.limitsiteid,ads.sqms,a.money AS todaymoney,ads.maxmoney,ads.adstypeid FROM ads LEFT JOIN (select sum(money) as money,adgid from user2data where user2data.dt = "'.date("Y-m-d").'" group by adgid ) a ON ads.adgid = a.adgid  WHERE (ads.lmid=0 or ads.lmid=1) and (ads.typeid = '.$tid.') AND (ads.shenhe = 1) AND (ads.toutime LIKE "%'.$t_time.'%") AND (ads.stime <= "'.date("Y-m-d ").'") AND (ads.etime >= "'.date("Y-m-d").'" ) AND (ads.maxmoney<=0 or a.money is null or (ads.maxmoney>0 and not a.money is null and ads.maxmoney > a.money)) ORDER BY ads.id';
        
        $stmt = mssql_query($sqlt);
        //var_dump($stmt);
        if($stmt === false){
            ?>
                document.write("something went wrong,please try again3");
            <?php 
            exit;
        }
        $time_Ymd = date('Y-m-d');
        $ads_list = [];
        $ads_info = [];
        while($row=mssql_fetch_array($stmt)) 
        {
            $ads_list[$row['adstypeid']][] = $row['id'];
            $row['todaymoney'.$time_Ymd] = $row['todaymoney'];
            $row['todaymaxmoney'.$time_Ymd] = $row['maxmoney'];
            unset($row['todaymoney'], $row['maxmoney']);
            $ads_info[$row['id']] = $row;
        }
        
        mssql_free_result($stmt);
        colseconn($conn);

        $memcache->set('pghf_adsinfo',$ads_info,MEMCACHE_COMPRESSED,MEMCACHE_TIME);

        $memcache->set('pghf_adslist_'.$tid,$ads_list,MEMCACHE_COMPRESSED,MEMCACHE_TIME);

        return $ads_list;
        
    }else{

        return $memcache->get('pghf_adslist_'.$tid);
    }
}

// 根据本地ip地址对应文件，查询ip所对应的省份。
function get_my_region($ip){
    $dir_path = include 'ip_lists/ipfile_list.php';
	
    if(!is_array($dir_path)){
        // echo '获取ip文件列表出错！';
        return '';
    }

    $ipfile_name = '';
    foreach($dir_path as $startip => $endip){
        // $val[0];     // 起始ip
        // $val[1];     // 终止ip
        if($ip > $startip AND $ip < $endip){
            // echo 'true';
            $ipfile_name = 'ip_lists/'.$startip.'-'.$endip;    // 构建文件名
            break;
        }
    }

    $region = '';
    $fh = null;
    // echo 'ipfile_name=', $ipfile_name;
    if(is_file($ipfile_name)){
        $fh = fopen($ipfile_name, 'rb');
    }
    if($fh){
        while(($iprecode_arr = explode("\t", fgets($fh))) !== FALSE){
            // var_dump($iprecode_arr);
            // 首次匹配到小于区间
            if($ip <= (int)$iprecode_arr[1]){
                if($ip >= (int)$iprecode_arr[0]){
                    // echo '<br />match<br />';
                    $region = trim($iprecode_arr[2]);
                }
                break;
            }
        }
        fclose($fh);
    }

    return $region;
}
// 对某用户的ip进行签名
function iptopwd($ip, $n_pwd){
    if(!is_numeric($n_pwd)){
        return FALSE;
    }

    $s_ip = explode('.', $ip);
    if(isset($s_ip[3])){
        $iptopwd = md5('pg'.(((int)$s_ip[0] + $n_pwd) >> 2).'.'.((int)$s_ip[2] + $n_pwd + 8).'.'.((int)$s_ip[1] + $n_pwd * 3).'.'.((int)$s_ip[3] + ($n_pwd << 2)));
    }else{
        $iptopwd = md5($ip.$n_pwd);
    }

    return $iptopwd;
}
// 记录用户pv数据
function count_pv($s)
{
    
    $jafjpp = explode('&', base64_decode($s));

    $conn=openconn();
    mssql_select_db(DB_DATABASE, $conn);

    $sp = mssql_init("vistdata67_cpv_count", $conn);

    mssql_bind($sp, "@pid", $jafjpp[4], SQLINT2);
    mssql_bind($sp, "@adsid", $jafjpp[1], SQLVARCHAR);
    mssql_bind($sp, "@userid", $jafjpp[2], SQLINT2);

    mssql_execute($sp);

    mssql_free_statement($sp);

    colseconn($conn);

}


// 获取网站主广告显示配置参数
function getJSConfig($userid, $ptype, $memcache, $areaname = null){
    // 范例配置
    // 存储顺序说明
    // $config = [
    //     'ishark=1:shakecycle=3', // 抖动配置
    //     'iclosebtn=1:fakedclose=1:N=5:M=5',  // 关闭按钮配置
    //     'ifakebtn=1',    // 假关闭配置
    //     'islayer=0:H=50:W=100',   // 半浮层配置
    //     'ifulllayer=0:N=10:M=10',    // 全浮层配置
    //     'ijump=0',  // 自动跳转配置
    // ];
    
    $js_info = $memcache->get('pg_user1_'.$userid.'_jsinfo');
    if(empty($js_info)){
        $conn=openconn();
        $sql = 'SELECT js_config,js_okarea,js_ptype FROM user1 WHERE userid = '.$userid;
        $stmt = mssql_query($sql);
        $js_info = mssql_fetch_array($stmt);
        colseconn($conn);
        $memcache->set('pg_user1_'.$userid.'_jsinfo', $js_info, MEMCACHE_COMPRESSED, MEMCACHE_TIME);
    }

    // $js_info['js_config'] = '1:3|1:1:5:5|1|0:50:100|0:10:10|0';
    $shift = explode('|', $js_info['js_config']);
    $ishark = explode(':', $shift[0]);
    $iclosebtn = explode(':', $shift[1]);
    $islayer = explode(':', $shift[3]);
    $ifulllayer = explode(':', $shift[4]);
    $redirect = explode(':', $shift[5]);

    // 检查参数配置的默认值
    $config = [
        'isshake' => $ishark[0] !== '' ? $ishark[0] : 1,
        'shakecycle' => $ishark[1] ? ($ishark[1] + 3) : 7,

        'closebtn' => $iclosebtn[0] ? $iclosebtn[0] : 0,
        'fakedclose' => $iclosebtn[1] ? $iclosebtn[1] : 0,
        'fakedN' => $iclosebtn[2] ? $iclosebtn[2] : 5,
        'fakedM' => $iclosebtn[3] ? $iclosebtn[3] : 5,

        'isfakebtn' => $shift[2] !== '' ? $shift[2] : 1,

        'islayer' => $islayer[0] ? $islayer[0] : 0,
        'H' => $islayer[1] ? $islayer[1] : 40,
        'W' => $islayer[2] ? $islayer[2] : 100,

        'fulllayer' => $ifulllayer[0] ? $ifulllayer[0] : 0,
        'fullN' => $ifulllayer[1] ? $ifulllayer[1] : 5,
        'fullM' => $ifulllayer[2] ? $ifulllayer[2] : 5,

        'isredirect' => 0,
        'uc_qq_tz' => 0,

        'refreshN' => 0,
    ];

    // 检查设备类型 0 表示所有机型，1 表示安卓， 2表示ios
    $mob = ($js_info['js_ptype'] === 0 OR $js_info['js_ptype'] === $ptype);
    if($mob){
        $js_okarea = $js_info['js_okarea'] ? $js_info['js_okarea'] : 'all';
        if($areaname == ''){$js_okarea = 'all';}
        $area = ($js_okarea == 'all' OR mb_strpos($js_info['js_okarea'], $areaname, 0, 'UTF-8') !== FALSE);
        if($area){
         
            $config['isredirect'] = $redirect[0] ?: 0;
            $config['uc_qq_tz'] = $redirect[1] ?: 0;
        }
    }
    
    $rand = mt_rand(1, 100);
    if($rand > $config['W']){
        $config['H'] = 0;
    }

    return $config;
}

// 特殊广告输出
function out_special_ad($curAdid,  $ad_type,  $rndadspv,  $linkurl,  $ptype){
    switch($curAdid){
        // 巨鲨广告
        case 152:
            if($ad_type == TOP_BANNER){
                echo <<<'EOD'
document.writeln('<script src="https://s.051352.com/yrc_pg002.js" type="text/javascript"></script>');
EOD;
            }else{
                echo <<<'EOD'
document.writeln('<script src="https://s.051352.com/yrc_pg001.js" type="text/javascript"></script>');
EOD;
            }
            break;
        
    }
}
