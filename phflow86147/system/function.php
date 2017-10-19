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

// 获得用户的真实IP地址
/*
HTTP_X_FORWARDED_FOR    该值是在HTTP头部中设置的，可由随意构造的，容易伪造
HTTP_VIA                http请求经过的代理服务器，通常会在最后添加自己的相关信息
HTTP_CLIENT_IP          该值是客户端发送的HTTP头。超级匿名代理可以隐藏掉。
REMOTE_ADDR             该值是访问客户端的IP，不能隐藏。只会是最后一级代理的IP。
*/
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
// 在Redis上的存取比本地session的代价大15%左右
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

//网站主的广告位 以及广告位下的广告信息
/*
1.根据广告位获取网站主的广告信息
2.根据广告位获取该广告位上的有效广告
*/
function upADPosition($pid,$tid,$memcache){
    if(empty($pid) OR empty($tid)){
        return FALSE;
    }

    $memcache_adp = 'pg_flow_pid_' . $pid;    // pid_2028

    // 从Redis获取到数据,有缓存，则返回数据
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
        //关闭
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

    $memcache_user1 = 'pg_flow_user1_' . $user1_id;  

    if($data_user1 = $memcache->get($memcache_user1)){
        return $data_user1;
    }

    $conn = openconn();
    mssql_select_db(DB_DATABASE, $conn);

    $data_user1 = null;   // 存放网站主相关信息
    //获取网网站主相关信息
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
    
    // 获取网站主旗下的所有可用网站
    $sql_url = "SELECT id,url FROM user1_url WHERE userid={$user1_id} AND ok=1 AND isdel=0";
    $stmt_url = mssql_query($sql_url);
    if($stmt_url === FALSE) {
        exit;
    }
    $data_url = '';   // 存放网站主的所有正常网站
    while($row = mssql_fetch_array($stmt_url)) {
        $data_url .= $row['url'].',';
    }
    mssql_free_result($stmt_url);
    $data_user1['urlstr'] = $data_url;

    colseconn($conn);
    // 缓存到Redis上
    $memcache->set($memcache_user1, $data_user1, MEMCACHE_COMPRESSED, MEMCACHE_TIME);

    return $data_user1;
}

// 广告轮显完或者无广告或者整点 更新Redis数据,返回地域数组
function uptype($tid,$memcache,$update = false){
    $t_time = (string)(date('H'));
    $memcache_type_id = 'pg_flow_type_' . $tid.'_'.$t_time;

    // 判断数据是否存在以及数据的有效性
    $data_ads_list = $memcache->get($memcache_type_id.'_list');
    if($data_ads_list && !$update){
        return $data_ads_list;
    }
    
    $conn = openconn();
    mssql_select_db(DB_DATABASE, $conn);

    $sql='update ads set shenhe=2 from ads,user2 where ads.userid=user2.userid and ads.typeid=57 and ads.shenhe=1 and user2.admoney<5';

    mssql_query($sql);

    $time_Ymd = date('Y-m-d');
    // 数据不存在，则重新获取
    // 筛选出符合投放广告类型的广告，且广告本身处于可投放状态
    $sql_select_ads = 'SELECT ISNULL(ads.weight, 1) AS weight1,ads.id,ads.unshow_phone,ads.istype,ads.okarea,ads.phonetype,ads.userid,ads.picurl0,ads.picurl1,ads.picurl2,ads.picurl3,ads.picurl4,ads.wmap,ads.wapp,ads.gotourl,ads.blacksiteid,ads.limitsiteid,ads.sqms,a.money AS todaymoney,ads.maxmoney,ads.adstypeid FROM ads LEFT JOIN (select sum(money) as money,adgid from user2data where user2data.dt = "'.date("Y-m-d").'" group by adgid ) a ON ads.adgid = a.adgid WHERE (ads.lmid=0 or ads.lmid=1) and (ads.typeid = '.$tid.') AND (ads.shenhe = 1) AND (ads.toutime LIKE "%'.$t_time.'%") AND (ads.stime <= "'.date("Y-m-d").'") AND (ads.etime >= "'.date("Y-m-d").'" ) AND (ads.maxmoney<=0 or a.money is null or (ads.maxmoney>0 and not a.money is null and ads.maxmoney > a.money)) ORDER BY ads.pqid,ads.id';

    $stmt_select_ads = mssql_query($sql_select_ads);

    if($stmt_select_ads === FALSE){
        ?>
            document.writeln("没有对应广告数据");
        <?php 
        exit;

    }
    
    $data_ads_list = array();       // 存放该类型广告的所有列表
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

    $memcache->set($memcache_type_id.'_list', $data_ads_list, MEMCACHE_COMPRESSED, MEMCACHE_TIME); // 单条广告一个位置
    $memcache->set('pgflow_adsinfo',$ads_info,MEMCACHE_COMPRESSED,MEMCACHE_TIME);

    return $data_ads_list;
}

// 根据本地ip地址对应文件，查询ip所对应的省份。
function get_my_region($ip){
    $dir_path = include IP_LISTS_DIR.'ipfile_list.php';
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
            $ipfile_name = IP_LISTS_DIR.$startip.'-'.$endip;    // 构建文件名
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

// 记录用户pv数据
function count_pv($values, $conn)
{
    
    mssql_select_db(DB_DATABASE, $conn);

    $sp = mssql_init("vistdata57_cpv_count", $conn);

    mssql_bind($sp, "@values", $values, SQLVARCHAR);
    mssql_execute($sp);

    mssql_free_statement($sp);

}

//筛选广告,返回js相关参数
//$round_num 循环次数 ； $ads_unique 筛选广告时是否去重  ； $pic_unique 筛选图片时是否去重
function get_js_info_by_filter_ads($round_num, $nowdic, $ads_unique, $pic_unique){
    global $ads_info, $ads_type, $data_adp, $userip, $pid, $userid, $istype;
    $jslinks = [];
    $imgsrc = [];
    $jstitle = [];
    $img_type = '';
    $pv_url = [];
    $tz_domain = $istype ? WAP_TZ_DOMAIN : WX_TZ_DOMAIN;

    for($i = 0; $i < $round_num; $i++){
        
        $totalweight = 0;
        $weightdic = [];

        shuffle($nowdic);
        foreach($nowdic as $k => $val){
            $weightdic[$k] = $ads_info[$val]['weight1'];
            $totalweight += $ads_info[$val]['weight1'];
        }

        // >>>>>>>>广告过滤完成，根据随机数字获取广告池内的广告信息<<<<<<<
        $rnd_ad = mt_rand(1, $totalweight);
        $curAdid = 0;       // 选中的广告编号
        // 利用权重的上下界数值比较确定选中哪个广告
        foreach($weightdic as $k => $v){
            if($rnd_ad <= $v){
                $curAdid = (int)$nowdic[$k];
                $ads_key = $k;
                break;
            }else{
                $rnd_ad -= $v;
                continue;
            }
        }

        $useripaid = $userip . $curAdid;

        /********************正常广告输出***************************/
        if($ads_type == 5 || $ads_type == 6){
      
            $nowpic = explode(',', str_replace(' ', '', $ads_info[$curAdid]['picurl0']));
            $img_type = '640x200';
            $picurl_num = 0;
        }else if($ads_type == 1 || $ads_type == 3 || $ads_type == 8){

            $nowpic = explode(',', str_replace(' ', '', $ads_info[$curAdid]['picurl4']));
            $picurl_num = 4;
        }else if($ads_type == 2 || $ads_type == 4){
   
            $nowpic = explode(',', str_replace(' ', '', $ads_info[$curAdid]['picurl3']));
            $picurl_num = 3;
        }else{

            $nowpic = explode(',', str_replace(' ', '', $ads_info[$curAdid]['picurl1']));
            $img_type = '640x150';
            $picurl_num = 1;
        }
        
        $img_key = array_rand($nowpic, 1);
 
        $nowpico = $nowpic[$img_key];

        if(!$nowpico){
            ?>
                document.writeln("图片资源不足。");
            <?php
            exit;
        }
       
        $linkurl = base64_encode(explode('.', $nowpico)[0].'&'.$curAdid.'&'.$userid.'&'.time().'&'.$pid.'&'.AD_TYPE.'&'.iptopwd($useripaid, date('md')).'&'.base64_encode($ads_info[$curAdid]['gotourl']));

        $jslinks[] = $tz_domain.'/url?s='.$linkurl;
        $imgsrc[] = PICURLURL . $nowpico;
        $pv_url[] = $linkurl;
        $ads_url[] = $ads_info[$curAdid]['gotourl'];

        if($ads_type == 6 || $ads_type == 7 || $ads_type == 5){

            $jstitle[] = trim($ads_info[$curAdid]['wmap']);
        }else if($ads_type == 8){

            $jstitle[0][] = trim($ads_info[$curAdid]['wmap']);
            $jstitle[1][] = trim($ads_info[$curAdid]['wapp']);
        }else{
            
            $jstitle[] = trim($ads_info[$curAdid]['wapp']);
        }
        
        //去掉已筛选的
        if($pic_unique){
            unset($nowpic[$img_key]);
            $ads_info[$curAdid]['picurl'.$picurl_num] = implode(',', $nowpic);  
        }
        
        if($ads_unique){
            unset($nowdic[$ads_key]);
        }

    }

    return ['jslinks' => $jslinks, 'imgsrc' => $imgsrc, 'jstitle' => $jstitle, 'img_type' => $img_type, 'pv_url' => $pv_url, 'ads_url' => $ads_url, 'nowpic' => $nowpic];

}


function getConfig($userid, $memcache, $areaname, $ptype){

    $js_info = $memcache->get('pg_flow_user1_'.$userid.'_jsinfo');

    if(empty($js_info)){

        $conn=openconn();
        $sql = 'SELECT js_flow_config,js_flow_okarea,js_flow_ptype,js_flow_time FROM user1 WHERE userid = '.$userid;

        $stmt = mssql_query($sql);
        $js_info = mssql_fetch_array($stmt);

        colseconn($conn);
        $memcache->set('pg_flow_user1_'.$userid.'_jsinfo', $js_info, MEMCACHE_COMPRESSED, MEMCACHE_TIME);
    }
  
    $js_config = $js_info['js_flow_config'] ? explode(':', $js_info['js_flow_config']) : ['0','0'];

    // 检查参数配置的默认值
    $config = [
        'isredirect' => 0,
        'uc_qq_tz' => 0,
    ];
    
    if(strpos($js_info['js_flow_time'], (string)date('H'))){//检测时间段

        // 检查设备类型 0 表示所有机型，1 表示安卓， 2表示ios
        $mob = (!$js_info['js_flow_ptype'] OR $js_info['js_flow_ptype'] === $ptype);

        if($mob){
            $js_okarea = $js_info['js_flow_okarea'] ?: '';

            $area = (mb_strpos($js_okarea, $areaname, 0, 'UTF-8') === FALSE);
          
            if($area){
                $config['isredirect'] = $js_config[0];
                $config['uc_qq_tz'] = $js_config[1];
            }
        }
    }
    
    return $config;
}
