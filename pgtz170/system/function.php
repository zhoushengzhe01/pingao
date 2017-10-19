<?php 
// 工具函数库

function openconn()
{
	
    $msconnect = mssql_connect(DB_SERVERNAME, DB_USERNAME, DB_PWD);
	
    if (!$msconnect) {

        die("something is wrong,please try again");
    }
    
    return $msconnect;
}

function colseconn($msconnect)
{
    mssql_close($msconnect);
}

function get_ip(){
    $realip = NULL;

    if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

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

// 对某用户某一小时的ip进行签名
function iptopwd($ip, $n_pwd){
    if(!is_numeric($n_pwd)){
        return FALSE;
    }

    $s_ip = explode('.', $ip);
    $iptopwd = md5('pg'.(((int)$s_ip[0] + $n_pwd) >> 2).'.'.((int)$s_ip[2] + $n_pwd + 8).'.'.((int)$s_ip[1] + $n_pwd * 3).'.'.((int)$s_ip[3] + ($n_pwd << 2)));

    return $iptopwd;
}

// 生成制定长度的随机字符串
function randomStr($length){
	$str = 'abcdefghijklmnopqrstuvwxyz1234567890';
	$rand_arr = str_split(str_shuffle($str), 1);
	$out_str = '';

	for(; $length > -1; $length--){
		$out_str .= $rand_arr[mt_rand(0, 35)];
	}
	return $out_str;
}

function upADPosition($pid,$tid,$memcache){
    if(empty($pid) OR empty($tid)){
        return FALSE;
    }

    $memcache_adp = 'pg_pid_' . $pid;  

    if($data_adp = $memcache->get($memcache_adp)){
    	return $data_adp;
    }

    // 没有获取到数据，执行数据库查询
    $conn = openconn();
    mssql_select_db(DB_DATABASE, $conn);
    //获取广告位信息
    $sql_adpos = "SELECT top 1 userid,noadok,islogo,ispic,wadsclass,iosclass FROM dbo.ADposition WHERE [ID] = {$pid} AND [typeid] = {$tid}";

    $stmt_adpos = mssql_query($sql_adpos);

    if($stmt_adpos === FALSE){
        ?>
            document.writeln("没有指定类型的广告位");
        <?php 
        exit;
    }
    
    $data_adp = mssql_fetch_array($stmt_adpos);

    mssql_free_result($stmt_adpos);
        
    if($data_adp){

        $sql_adssign = "SELECT adsid,adxg FROM adsSign WHERE pid = {$pid} AND userid = {$data_adp['userid']} AND isok = 1";
        $stmt_adssign = mssql_query($sql_adssign);
        if($stmt_adssign === FALSE) {
            ?>
                document.writeln("Something went wrong,please try again");
            <?php 
            exit;
        }
        
        $adxgstr_arr = array();
        while($row = mssql_fetch_array($stmt_adssign)){
            $adxgstr_arr[$row['adsid']] = $row['adxg'];
        }
        mssql_free_result($stmt_adssign);

        colseconn($conn);

        $data_adp['adxgstr_arr'] = $adxgstr_arr;

        $memcache->set($memcache_adp, $data_adp, MEMCACHE_COMPRESSED, MEMCACHE_TIME); // 网站主的广告位信息
        return $data_adp;
    }
    
    //关闭
    colseconn($conn);
    ?>
        document.writeln("没有获取到广告位数据");
    <?php 
    exit;
}

//通过网站主userid查找网站主信息    返回存放在Redis上的key
// 此处的网站主数据Redis上可能需要2008左右的chunk
function upUser1Info($user1_id, $tid, $memcache){
    if(!$user1_id OR !$tid){
        ?>
            document.writeln("用户信息参数错误");
        <?php
        exit;
    }

    $memcache_user1 = 'pg_user1_' . $user1_id;  

    if($data_user1 = $memcache->get($memcache_user1)){
        return $data_user1;
    }

    $conn = openconn();
    mssql_select_db(DB_DATABASE, $conn);

    $data_user1 = null; 

    $sql_user1 = "SELECT top 1 username,openty,ifdomain,zhuangtai,iftype FROM user1 WHERE userid={$user1_id} AND lmid=1";
    $stmt_user1 = mssql_query($sql_user1);
    if($stmt_user1 === FALSE) {
        exit;
    }
    $data_user1 = mssql_fetch_array($stmt_user1);
    mssql_free_result($stmt_user1);
    if(!$data_user1){
        exit;
    }
    
    $sql_url = "SELECT id,url FROM user1_url WHERE userid={$user1_id} AND ok=1";
    $stmt_url = mssql_query($sql_url);
    if($stmt_url === FALSE) {
        exit;
    }
    $data_url = '';  
    while($row = mssql_fetch_array($stmt_url)) {
        $data_url .= '('.$row['url'].'){'.$row['id'].'}';
    }
    mssql_free_result($stmt_url);
    $data_user1['urlstr'] = $data_url;

    colseconn($conn);

    $memcache->set($memcache_user1, $data_user1, MEMCACHE_COMPRESSED, MEMCACHE_TIME);

    return $data_user1;
}

// 广告轮显完或者无广告或者整点 更新Redis数据,返回地域数组
function uptype($tid,$memcache){
    $memcache_type_id = 'pg_type_' . $tid;
    $t_time = (string)(date('H'));

    if($data_ads_list = $memcache->get($memcache_type_id.'_list')){
        return $data_ads_list;
    }
    
    $conn = openconn();
    mssql_select_db(DB_DATABASE, $conn);

    $sql='update ads set shenhe=2 from ads,user2 where ads.userid=user2.userid and ads.typeid=68 and ads.shenhe=1 and user2.admoney<1';

    mssql_query($sql);

    $time_Ymd = date('Y-m-d');

    $sql_select_ads = 'SELECT ISNULL(ads.weight, 1) AS weight1,ads.id,ads.pv_weight,ads.unshow_phone,ads.istype,ads.okarea,ads.phonetype,ads.userid,ads.picurl0,ads.picurl1,ads.picurl2,ads.gotourl,ads.blacksiteid,ads.limitsiteid,ads.sqms,a.money AS todaymoney,ads.maxmoney,ads.adstypeid 
        FROM ads 
        LEFT JOIN (select sum(money) as money,adgid from user2data where user2data.dt = "'.date("Y-m-d").'" group by adgid ) a ON ads.adgid = a.adgid 
        WHERE (ads.lmid=0 or ads.lmid=1) and (ads.typeid = '.$tid.') AND (ads.shenhe = 1) AND (ads.toutime LIKE "%'.$t_time.'%") AND (ads.stime <= "'.date("Y-m-d ").'") AND (ads.etime >= "'.date("Y-m-d").'" ) AND (ads.maxmoney<=0 or a.money is null or (ads.maxmoney>0 and not a.money is null and ads.maxmoney > a.money)) 
        ORDER BY ads.pqid,ads.id';

    $stmt_select_ads = mssql_query($sql_select_ads);

    if($stmt_select_ads === FALSE){
        ?>
            document.writeln("没有对应广告数据");
        <?php 
        exit;

    }
    
    $data_ads_list = array();   
    $ads_info = [];
    while($row = mssql_fetch_array($stmt_select_ads)){

        $data_ads_list[$row['adstypeid']][] = $row['id'];
        $row['todaymoney'.$time_Ymd] = $row['todaymoney'];
        $row['todaymaxmoney'.$time_Ymd] = $row['maxmoney'];
        unset($row['todaymoney'], $row['maxmoney']);
        $ads_info[$row['id']] = $row;
    }
    mssql_free_result($stmt_select_ads);

    colseconn($conn);

    $memcache->set($memcache_type_id.'_list', $data_ads_list, MEMCACHE_COMPRESSED, MEMCACHE_TIME); 
    $memcache->set('pgtz_adsinfo',$ads_info,MEMCACHE_COMPRESSED,MEMCACHE_TIME);

    return $data_ads_list;
}

// 根据本地ip地址对应文件，查询ip所对应的省份。
function get_my_region($ip){
    $dir_path = include IP_LISTS_DIR.'ipfile_list.php';
    if(!is_array($dir_path)){
        return '';
    }

    $ipfile_name = '';
    foreach($dir_path as $startip => $endip){

        if($ip > $startip AND $ip < $endip){

            $ipfile_name = IP_LISTS_DIR.$startip.'-'.$endip; 
            break;
        }
    }

    $region = '';
    $fh = null;

    if(is_file($ipfile_name)){
        $fh = fopen($ipfile_name, 'rb');
    }
    if($fh){
        while(($iprecode_arr = explode("\t", fgets($fh))) !== FALSE){

            if($ip <= (int)$iprecode_arr[1]){
                if($ip >= (int)$iprecode_arr[0]){

                    $region = trim($iprecode_arr[2]);
                }
                break;
            }
        }
        fclose($fh);
    }

    return $region;
}


