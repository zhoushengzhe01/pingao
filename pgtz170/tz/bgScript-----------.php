<?php 
// 内刷代码
$OK_PID = [2001,2034,2031,2036,2038];	// 允许内嵌的pid
const AD_TYPE = 68;

// 判断模拟器
if(strpos($get['f'], 'Win') !== FALSE OR strpos($get['f'], 'Mac') !== FALSE){
	exit;
}

// 判断前来源是否为空
if($_SERVER['HTTP_REFERER'] == ''){
	exit;
}

$pid = $get['p'] ? $get['p'] : null;
// 判断机型
if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') === FALSE 
	AND strpos($_SERVER['HTTP_USER_AGENT'], 'iPad') === FALSE){
	exit;
}


/********************接收传入参数.***********/
// 广告位置

if(!$pid || !in_array($pid, $OK_PID)){
	?>
	document.writeln("参数错误！");
	<?php
	exit;
}

$userip = get_ip();
if(!$userip){
	?>
	document.writeln("无效ip！");
	<?php
	exit;
}

$memcache=new Memcache;
$result=$memcache->pconnect(MEMCACHE_SERVERNAME,MEMCACHE_PORT);

/**************************初始化参数******************************/
// 有广告位ID传入,则使用对应网站主ID,不管userid是否传入,以广告位ID为准
$data_adp = upADPosition($pid, AD_TYPE, $memcache);
$userid = $data_adp['userid'];

$data_user1 = upUser1Info($userid, AD_TYPE, $memcache);	// 载入网站主信息


/*********************状态判断****************************/
if($data_user1['zhuangtai'] != 1){
	?>
	    document.writeln("您的帐号未开通或被冻结");
	<?php
	exit;
}

if(strpos($data_user1['openty'], (string)AD_TYPE) === FALSE){
	?>
	    document.writeln("您的帐号未开通跳转广告");
	<?php
	exit;
}

/***********************过滤可弹广告*****************************/
$ads_list = uptype(AD_TYPE, $memcache);


if(!is_array($ads_list)){

	?>
		document.writeln("没有广告！");
	<?php
	exit;
}
$ads_info = $memcache->get('pgtz_adsinfo');
// 获取指定分类的广告信息
$ads = [];

$adsclass = ($ptype == 1) ? $data_adp['wadsclass'] : $data_adp['iosclass'];
$adsclass = trim($adsclass);

if($adsclass == '0'){
	// 广告全选
	foreach($ads_list as $val){
		foreach($val as $v){
			$ads[$v] = $ads_info[$v];
		}
	}
}else{
	// 选择了指定类型的广告
	foreach(explode(',', $adsclass) as $class){
		foreach($ads_list[$class] as $val){
			$ads[$val] = $ads_info[$val];
		}
	}
}


$userip_num = ip2long($userip);
$areaname = get_my_region($userip_num);			// 获取ip所在城市

$nowdic = array();
// 如果系统有定向广告,则与定向广告进行重合性判断,条件(站点广告与地域广告重合, 访者地域与广告地域相符)
foreach($ads as $ad_id => $ad){

	if($ad['istype'] == $istype
		AND strpos($ad['phonetype'], (string)$ptype) !== FALSE
		AND strpos($ad['blacksiteid'], ','.$userid.',') === FALSE
		AND (empty($ad['okarea']) OR mb_strpos($ad['okarea'], $areaname, 0, 'UTF-8') !== FALSE)){
        $unshow = false;
		if($ad['unshow_phone'] != '0'){
			foreach (explode(',',$ad['unshow_phone']) as $value) {
            	if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']),(string)$value) !== false){
            		$unshow = true;
            		break;
            	}
            }
		}
		if(!$unshow){
			if($ad['sqms'] == 1){
				// 广告定投站点
				if(strpos($ad['limitsiteid'], ','.$userid.',') !== FALSE){
					$nowdic[] = $ad_id;
				}
			}else{
				$nowdic[] = $ad_id;
			}
		}
	}
}


if(empty($nowdic)){

	?>
	document.writeln("没有符合条件的广告！");
	<?php
	exit;
}

$ads_arr = $nowdic;

$ads_cpa = [];
$ads_cpm = [];

foreach($nowdic as $v){
	if($ads[$v]['adstypeid'] == 9){
		$ads_cpa[] = $v;  
	}else{
		$ads_cpm[] = $v;
	}
}

$un_ads_ids = ltrim($un_ads_ids,',');
$arr = explode(',',$un_ads_ids);
if($un_ads_ids!=''){
	/*
	1: un_ads_ids 将变成这样: adsid1t20170527,adsid2t20170528,adsid3t20170529 "t"后面的日期代表的是这个广告上次刷的日期,结合该广告的pvweight值, >=该日期N天就可以再刷了, 这样设置日期为的是改变pvweight值时,一些已经置过的cookie会立马正确间隔;
	2: 在arr里去掉已经过了间隔的adsid,并在arr里只保留adsid,并重新将未过失效日期的adsid重新拼凑赋值un_ads_ids  by ywl 2017-5-28 21:00
	*/
	$arr_new=[];
	$un_ads_ids_new='';
	$tmparr=[];
	foreach ($arr as $v) {
		$tmparr = explode('t',$v);
		if($tmparr){
	      $startdate=strtotime($tmparr[1]);
	      $nowdate=strtotime(date('Ymd'));
	      $days=round(($nowdate-$startdate)/3600/24) ;	
		  $gapday=$ads[$tmparr[0]]['pv_weight'];
		  if($gapday==''){$gapday=1;}
		  if($days<$gapday){ //间隔天数<pvweight设置的天数;当天不可刷
			  $arr_new[]=$tmparr[0];
			  $un_ads_ids_new = $un_ads_ids_new.','.$v;
		  } 
		}
	}
	$un_ads_ids = $un_ads_ids_new;
	
	if($un_ads_ids!=''){	//通过日期间隔判断后,还是有需要过滤的不可刷的adsid,则继续广告过滤
		$nowdic = [];

		foreach ($ads_cpm as $v) {

			if(!in_array($v, $arr_new)){

				$nowdic[] = $v;
			}
		}

		if(!$nowdic){
			foreach ($ads_cpa as $v) {
				if(!in_array($v, $arr_new)){

					$nowdic[] = $v;
				}
			}
		}
	}else{
		$nowdic=$ads_cpm;
	}
}else{
	$nowdic=$ads_cpm;
}

$tz_flag = false;
if(!empty($nowdic)){

    $dicstr = '';
	$totalweight = 0;
	$weightdic_now = '';

	shuffle($nowdic);
	foreach($nowdic as $val){
		$dicstr .= ',' . $val;
	    $weightdic_now .= ',' . $ads[$val]['weight1'];
	    $totalweight += $ads[$val]['weight1'];
	}

	if(strlen($dicstr)){
		$nowdic = explode(',', substr($dicstr, 1));
		$weightdic = explode(',', substr($weightdic_now, 1));
	}

	// >>>>>>>>广告过滤完成，根据随机数字获取广告池内的广告信息<<<<<<<
	$rnd_ad = mt_rand(1, $totalweight);
	$curAdid = 0;		// 选中的广告编号
	// 利用权重的上下界数值比较确定选中哪个广告
	foreach($weightdic as $k => $v){
		if($rnd_ad <= $v){
			$curAdid = (int)$nowdic[$k];
			break;
		}else{
			$rnd_ad -= $v;
			continue;
		}
	}

    $ad_cookie = $_COOKIE['pgbg_'.$userip_num.$curAdid] ?: 0;

    if(!$ad_cookie){
    	$link=new swoole_client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_SYNC);//TCP方式、同步
		$link->connect('127.0.0.1',9508);//连接
		$link->send($pid.'|'.$userid.'|'.$web_id.'|'.$curAdid.'|'.$userip_num);//执行查询
		$res=unserialize($link->recv());
		if(!$res){
		    echo "connect failed. Error: {$link->errCode}\n";
		    $link->close();
		    exit;
		}else{
		    if(!$res['count']){
                
			    setcookie('pgbg_'.$userip_num.$curAdid,1,strtotime('23:59:59'));
			    $tz_flag = true;

			}else{

				if(!$res['error']){

				    if($ads[$curAdid]['pv_weight']>0){//如果是同IP不同UV进来,选了已经被这个IP上跳过的广告(而且该广告不要UV),撞死了,则给他选一个CPA里pv_weight=0的没跳过的广告跳,不计费,利用一下.
					    $uv_dic = [];
						foreach ($ads_cpa as $v) {
							$uv_cookie = $_COOKIE['pgbg_'.$userip_num.$v] ?: 0;
							if($ads[$v]['pv_weight']==0 and !$uv_cookie){
								$uv_dic[] = $v;
							}
						}
						if(!empty($uv_dic)){
							$curAdid = $uv_dic[array_rand($uv_dic,1)];
						}
					}
					
				}else{

					echo $res['error'];
					$link->close();
					exit;
				}
			}

		}

		$link->close();
	
	}

	$gotourl = $ads[$curAdid]['gotourl'];

	if($tz_flag||$ads[$curAdid]['pv_weight']==0){
        $isWifi = true; //判断是否wifi状态,不在基站IP库内
		if($curAdid == 1120 || $curAdid == 952 || $curAdid == 1019){
           
	        $ip_list = include __DIR__.'/../ip_lists/ip_listjz/ip_listjz.php';
            $is_loop = false;
            $file_name = '';
	        foreach($ip_list as $k => $v){
	        	if($userip_num >= $k && $userip_num <= $v){
                    $is_loop = true;
                    $file_name = $k.'-'.$v.'.php';
                    break;
	        	}
	        }

	        if($is_loop && $file_name){
	        	$ip_arr = include __DIR__.'/../ip_lists/ip_listjz/'.$file_name;
	        	foreach ($ip_arr as $k => $v) {
	        		if($userip_num >= $k && $userip_num <= $v){
	        			$isWifi = false;
	        			break;
	        		}
	        	}
	        }

	    }

		$un_ads_ids = $un_ads_ids.','.$curAdid.'t'.date('Ymd');  
	    if($curAdid == 1120){
	    	if($isWifi){
	        	echo 'var a,i,h;""==WckgetCookie("ispgs'.$userip_num.$ads[$curAdid]['id'].date('Ymd').'")&&(a=document.createElement("iframe"),a.src="',$gotourl,'",a.width="0",a.height="0",a.style="display:none;",h=document.getElementsByTagName("html")[0],h.appendChild(a),WcksetCookie("ispgs'.$userip_num.$ads[$curAdid]['id'].date('Ymd').'",3));WcksetCookie("'.$userip_num.'bg","'.$un_ads_ids.'");';
	        }
	    }else{
	    	if($isWifi){
	    		if($curAdid == 952 || $curAdid == 1203){

	    			$ch = curl_init();
				    $header = array( 
				        'CLIENT-IP:'.$userip, 
				        'X-FORWARDED-FOR:'.$userip, 
				    ); 

				    if(strpos($gotourl,"https://")!==FALSE){
				        curl_setopt($ch, CURLOPT_SSLVERSION, 4);//获取https服务器资源时，要加上ssl的相关参数，最新版本是4
				        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
				    }

				    curl_setopt ($ch, CURLOPT_URL, $gotourl);
				    curl_setopt($ch, CURLOPT_HEADER, 1);
				    curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
				    curl_setopt ($ch, CURLOPT_REFERER, $_SERVER['HTTP_REFERER']);
				    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

				    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);

				    curl_exec($ch);
				    curl_close ($ch);
					setcookie('pgbg_'.$userip_num.$curAdid,1,strtotime('23:59:59'));
					echo 'WcksetCookie("ispgs'.$userip_num.$ads[$curAdid]['id'].date('Ymd').'",3));WcksetCookie("'.$userip_num.'bg","'.$un_ads_ids.'");';

	    		}else{
	    			echo 'var a,i,h;""==WckgetCookie("ispgs'.$userip_num.$ads[$curAdid]['id'].date('Ymd').'")&&(a=document.createElement("img"),a.src="',$gotourl,'",a.width="0",a.height="0",a.style="display:none;",h=document.getElementsByTagName("html")[0],h.appendChild(a),WcksetCookie("ispgs'.$userip_num.$ads[$curAdid]['id'].date('Ymd').'",3));WcksetCookie("'.$userip_num.'bg","'.$un_ads_ids.'");';
	    		}

	    	}
	    }
	}
}else{
	echo 'WcksetCookie("'.$userip_num.date('Ymd').'stat",1);'; //表示这个用户今天刷完了一圈的广告,在入口处可以控制今天不请求,减少请求量
}






