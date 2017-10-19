<?php

/***
 * 调用配置文件参数
 *  
 * 格式：app.name
 */
if(!function_exists("config"))
{

    function config( $position )
    {
        $array = explode('.', $position);

        if( !is_array($array) && count($array)<=0 )
        {
            return false;    
        }

        $fileValue = require  __DIR__.'/../../config/'.$array[0].'.php';
        
        $value = '';

        foreach($array as $key=>$value)
        {
            if( $key > 0 )
            {
                if( empty($fileValue[$value]) )
                {
                    return false;
                }
                else
                {
                    $fileValue = $fileValue[$value];
                }
                
            }
            
        }

        return $fileValue;
	}

}

//获取主域名
function getPrimaryDomain($host, $suffix){
    
    if(trim($host) == '')
    {
        return FALSE;
    }
    
    $pattern = '/(?:[a-z0-9-]){1,63}\.(?:'.$suffix.')(?:\:\d+)?$/';

    if(preg_match($pattern, $host, $matches))
    {
        if(empty($matches[0]))
        {
            return false;
        }
        else
        {
            return trim($matches[0]);
        }
        
    }
}


/***
 * 系统服务参数
 *
 * $_SERVICE 里面的参数 
 */

if(!function_exists('server'))
{
    function server($key)
    {
        if(!empty($_SERVER[strtoupper($key)]))
        {
            return trim($_SERVER[strtoupper($key)]);
        }
        else
        {
            return false;
        }

    }
}


/***
 * 过滤URL地址不带参数
 *
 * 样式：/user/info?uid=123 获取 /user/info
 */
if(!function_exists('getRequestUrl'))
{
    function getRequestUrl()
    {
        
        $request_url = explode("?", substr(server('request_uri'), 1));

        if(empty($request_url[0]))
        {
            return '/';
        }
        else
        {
            return $request_url[0]; 
        }

    }

}

/***
 * 
 *  
 * 格式：app.name
 */
if(!function_exists("view"))
{

    function view($place)
    {

        $array = explode('.', $place);

        if( !is_array($array) && count($array)<=0 )
        {
            return false;    
        }

        $path =  __DIR__.'/../../view';

        foreach($array as $key=>$value)
        {

            if($key>=count($array)-1)
            {
                $path .= '/'.$value.'.php';
            }
            else
            {
                $path .= '/'.$value;
            }

            
        }

        
        if(file_exists($path))
        {
            
            require $path;
        }
        else
        {
            die('Nonexistent file:'.$path);
        }


	}

}


/***
 * 请求参数
 *
 * Request
 */
if(!function_exists('request'))
{
    function request($name=null)
    {
        if($name)
        {
            if(empty($_REQUEST[$name]))
            {
                return false;
            }
            else
            {
                return trim($_REQUEST[$name]);
            }
        }
        else
        {
            return arrayToObject($_REQUEST);
        }
        
    }
}



/**
 * 数组 转 对象
 *
 * @param array $arr 数组
 * @return object
 */
if(!function_exists('arrayToObject'))
{
    function arrayToObject($arr) {

        if (gettype($arr) != 'array') {
        
            return;
        
        }
        
        foreach ($arr as $k => $v) {
        
            if (gettype($v) == 'array' || getType($v) == 'object') {
        
                $arr[$k] = (object)array_to_object($v);
        
            }
        
        }
    
        return (object)$arr;
    }
}


/**
 * 对象 转 数组
 *
 * @param object $obj 对象
 * @return array
 */
if(!function_exists("objectToArray"))
{
    function objectToArray($obj)
    {
        $obj = (array)$obj;
        
        foreach ($obj as $k => $v) {
        
            if (gettype($v) == 'resource') {
        
                return;
        
            }
        
            if (gettype($v) == 'object' || gettype($v) == 'array') {
        
                $obj[$k] = (array)object_to_array($v);
        
            }
        
        }
    
        return $obj;
    }
}


/***
 * 获取手机型号
 *  
 * getPhoneType
 */
if(!function_exists("getPhoneType"))
{

    function getPhoneType()
    {

        $agent = server('http_user_agent');

        if (stripos($agent, "iPhone")!==false) {
            $brand = 'iPhone';

        } else if (stripos($agent, "SAMSUNG")!==false || stripos($agent, "Galaxy")!==false || strpos($agent, "GT-")!==false || strpos($agent, "SCH-")!==false) {
            $brand = 'SAMSUNG';

        } else if (strpos($agent, "SM-")!==false) {
            $brand = 'SM';

        } else if (stripos($agent, "Huawei")!==false ||  stripos($agent, "H60-")!==false || stripos($agent, "H30-")!==false) {
            $brand = 'Huawei';

        } else if (stripos($agent, "Honor")!==false) {
            $brand = 'Honor';

        } else if (stripos($agent, "Lenovo")!==false) {
            $brand = 'Lenovo';

        } else if (strpos($agent, "MI-ONE")!==false || strpos($agent, "MI 1S")!==false || strpos($agent, "MI 2")!==false || strpos($agent, "MI 3")!==false || strpos($agent, "MI 4")!==false || strpos($agent, "MI-4")!==false) {
            $brand = 'MI';

        } else if (strpos($agent, "HM NOTE")!==false || strpos($agent, "HM201")!==false) {
            $brand = 'HM NOTE';

        } else if (stripos($agent, "Coolpad")!==false || strpos($agent, "8190Q")!==false || strpos($agent, "5910")!==false) {
            $brand = 'Coolpad';

        } else if (stripos($agent, "ZTE")!==false || stripos($agent, "X9180")!==false || stripos($agent, "N9180")!==false || stripos($agent, "U9180")!==false) {
            $brand = 'ZTE';

        } else if (stripos($agent, "OPPO")!==false || strpos($agent, "X9007")!==false || strpos($agent, "X907")!==false || strpos($agent, "X909")!==false || strpos($agent, "R831S")!==false || strpos($agent, "R827T")!==false || strpos($agent, "R821T")!==false || strpos($agent, "R811")!==false || strpos($agent, "R2017")!==false) {
            $brand = 'OPPO';
        
        } else if (strpos($agent, "HTC")!==false || stripos($agent, "Desire")!==false) {
            $brand = 'HTC';
        
        } else if (stripos($agent, "vivo")!==false) {
            $brand = 'vivo';
        
        } else if (stripos($agent, "K-Touch")!==false) {
            $brand = 'K-Touch';
        
        } else if (stripos($agent, "Nubia")!==false || stripos($agent, "NX50")!==false || stripos($agent, "NX40")!==false) {
            $brand = 'Nubia';
        
        } else if (strpos($agent, "M045")!==false || strpos($agent, "M032")!==false || strpos($agent, "M355")!==false) {
            $brand = 'M045';
        
        } else if (stripos($agent, "DOOV")!==false) {
            $brand = 'DOOV';
        
        } else if (stripos($agent, "GFIVE")!==false) {
            $brand = 'GFIVE';
        
        } else if (stripos($agent, "Gionee")!==false || strpos($agent, "GN")!==false) {
            $brand = 'GN';
        
        } else if (stripos($agent, "HS-U")!==false || stripos($agent, "HS-E")!==false) {
            $brand = 'HS-U';
        
        } else if (stripos($agent, "Nokia")!==false) {
            $brand = 'Nokia';
        
        } else {
            $brand = 'ON';
        
        }

        return $brand;
	}

}


/***
 * 获取系统
 *
 * getOS 
 */
if(!function_exists('getOS'))
{
    function getOS()
    {
        $agent = server('http_user_agent');

        if (strpos($agent, 'Android') !== false) {
            $os = 'Android';

        } elseif (strpos($agent, 'iPhone') !== false) {
            $os = 'iPhone';

        } elseif (strpos($agent, 'iPad') !== false) {
            $os = 'iPad';
        
        } elseif (strpos($agent, 'Windows') !== false) {
            $os = 'Windows';
        
        } elseif (strpos($agent, 'Linux') !== false) {
            $os = 'Linux';
        
        } elseif (strpos($agent, 'unix') !== false) {
            $os = 'unix';
        
        } elseif (strpos($agent, 'sun') !== false) {
            $os = 'sun';
        
        } elseif (strpos($agent, 'ibm') !== false) {
            $os = 'ibm';

        } elseif (strpos($agent, 'Mac') !== false) {
            $os = 'Mac';

        } elseif (strpos($agent, 'PowerPC') !== false) {
            $os = 'PowerPC';

        } elseif (strpos($agent, 'AIX') !== false) {
            $os = 'AIX';

        } elseif (strpos($agent, 'HPUX') !== false) {
            $os = 'HPUX';
        
        } elseif (strpos($agent, 'NetBSD') !== false) {
            $os = 'NetBSD';
        
        } elseif (strpos($agent, 'BSD') !== false) {
            $os = 'BSD';
        
        } elseif (strpos($agent, 'OSF1') !== false) {
            $os = 'OSF1';
        
        } elseif (strpos($agent, 'IRIX') !== false) {
            $os = 'IRIX';
        
        } elseif (strpos($agent, 'FreeBSD') !== false) {
            $os = 'FreeBSD';
        
        } else{
            $os = 'Unknown';

        }

        return $os;

    }
}


/***
 * 日志处理方法
 * 
 * 
 */
if(!function_exists('errorlog'))
{
    function errorlog($message, $file)
    {
        //没有文件创建个
        if( !file_exists(config('app.log_path')) )
        {
            mkdir( iconv("UTF-8", "GBK", config('app.log_path') ), 0777, true);
        }

        $fileName = '/error_'.date('Y-m-d', time()).'.log';

        $message = '
['.date('Y-m-d H:i:s', time()).']  '.$file.'  '.$message;


        if( file_put_contents(config('app.log_path').$fileName, $message, FILE_APPEND) )
        {
            return true;
        }

    }

}


/***
 * 判断文件是否被修改
 *
 * isEditFile
 */
if(!function_exists('isEditFile'))
{
    function isEditFile($name)
    {
        
        if( !file_exists(config('app.cache')) )
        {
            mkdir( iconv("UTF-8", "GBK", config('app.cache') ), 0777, true); 
        }

        if( !is_file(config('app.cache').'/fileEditTime.json') )
        {
            return true;
        }

        $time = filectime($name);
        
        $route = json_decode(file_get_contents(config('app.cache').'/fileEditTime.json'), true);

        $isExist = false;

        foreach($route as $key=>$val)
        {   
            if($val['name']==$name)
            {
                $isExist = true;
                if($val['time']==$time)
                {
                    return false;
                }
                else
                {
                    return true;
                }
                break;
            }
        }

        if($isExist==false)
        {
            return true;
        }

    }
}

/***
 * 储存文件修改时间
 *
 * saveFileEditTime
 */
if(!function_exists('saveFileEditTime'))
{
    function saveFileEditTime($name)
    {
        if( !file_exists(config('app.cache')) )
        {
            mkdir( iconv("UTF-8", "GBK", config('app.cache') ), 0777, true); 
        }

        $time = filectime($name);

        if( !is_file(config('app.cache').'/fileEditTime.json') )
        {
            $route = [];
        }
        else
        {
            $route = json_decode(file_get_contents(config('app.cache').'/fileEditTime.json'), true);
        }

        $isExist = false;
        foreach($route as $key=>$val)
        {   
            if($val['name']==$name)
            {
                $isExist = true;

                if($val['time']==$time)
                {
                    return false;
                }
                else
                {
                    $route[$key]['time'] = $time;
                }

                break;
                
            }
        }

        if($isExist===false)
        {
            $route[] = ['time'=>$time, 'name'=>$name];
        }
        
        file_put_contents(config('app.cache').'/fileEditTime.json', json_encode($route, true));
    }
}

/***
 * 判断是否微信
 *
 * isWechat
 */
if(!function_exists('isWechat'))
{
    function isWechat()
    {
        $agent = server('http_user_agent');

        if(strpos($agent, 'MicroMessenger') !== FALSE){
        
            return true;
        }else{
        
            return false;
        }

    }
}

/***
 * 获取客户端IP地址
 * 
 * 
 */
if(!function_exists('getClientIp'))
{
    function getClientIp()
    {   
        return server('remote_addr');
    }
}

/***
 * 通过ip获得城市
 *
 *
 */
if(!function_exists('getIpArea'))
{

    function getAreaByIp($ip)
    {
        $dir_path = include config('app.ip_path').'/ipFileList.php';
        
        if(!is_array($dir_path))
        {
            // echo '获取ip文件列表出错！';
            return '';
        }

        $ipfile_name = '';

        foreach($dir_path as $startip => $endip){
            // $val[0];     // 起始ip
            // $val[1];     // 终止ip
            if($ip > $startip && $ip < $endip)
            {
                // echo 'true';
                $ipfile_name = config('app.ip_path').'/'.$startip.'-'.$endip;    // 构建文件名
                
                break;
            }
        }

        $region = '';
        $fh = null;

        if(is_file($ipfile_name))
        {

            $fh = fopen($ipfile_name, 'rb');
        }

        if($fh){

            while(($iprecode_arr = explode("\t", fgets($fh))) !== FALSE)
            {
                
                if($ip <= (int)$iprecode_arr[1])
                {
                    if($ip >= (int)$iprecode_arr[0])
                    {
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
}

//返回状态
if(!function_exists('response'))
{
    function response($data, $status)
    {

        header('Content-type: application/json');

        header('HTTP/1.1 '. $status);

        die(json_encode($data, true));
    }
}

// 对某用户的ip进行签名
if(!function_exists('iptopwd'))
{
    function iptopwd($ip, $n_pwd)
    {
        if(!is_numeric($n_pwd)){
            return FALSE;
        }

        $s_ip = explode('.', $ip);
        if(isset($s_ip[3]))
        {
            $iptopwd = md5('pg'.(((int)$s_ip[0] + $n_pwd) >> 2).'.'.((int)$s_ip[2] + $n_pwd + 8).'.'.((int)$s_ip[1] + $n_pwd * 3).'.'.((int)$s_ip[3] + ($n_pwd << 2)));
        }
        else
        {
            $iptopwd = md5($ip.$n_pwd);
        }

        return $iptopwd;
    }
}


//设置Cookie
if(!function_exists('set_cookie'))
{
    function set_cookie($key, $value, $time=null)
    {   
        if(empty($time))
        {
            $time = strtotime(date('Y-m-d').' 23:59:59');
        }
        
        if(empty($key) || empty($value))
        {
            return false;
        }

        setcookie($key, $value, $time);
        
        return true;
    }
}

//获取Cookie
if(!function_exists('get_cookie'))
{
    function get_cookie($key)
    {        
        if(empty($key))
        {
            return false;
        }

        $value = empty($_COOKIE[$key]) ? null : trim($_COOKIE[$key]);

        if(empty($value))
        {
            return false;
        }
        else
        {
            return $value;
        }
    }
}


//获取密钥
if(!function_exists('access_token'))
{
    function getAccessToken($data)
    {
        if(!is_array($data))
        {
            return false;
        }

        $data['token'] = md5(config('app.token'));
        
        ksort($data);

        $token = md5(http_build_query($data));

        return $token;
    }
}

//提示
if(!function_exists('message'))
{
    function message($msg)
    {
        die('document.writeln("<font size=2>'.$msg.'")');
    }
}

if(!function_exists('is_work_memcache'))
{
    function is_work_memcache($memcache)
    {
        if($memcache->get('testicon'))
        {
            echo $memcache->get('testicon');
            die('工作中，缓存了10秒');
        }
        else
        {
            $memcache->set('testicon', 1, MEMCACHE_COMPRESSED, 10);
            die('停止缓存了。');
        }
    }
}


if(!function_exists('out_special_ad'))
{
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
}