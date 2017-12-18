<?php
namespace app\Helpers;

use app\Helpers\Helper;
use app\Config\AppConfig;

class Helper
{
    //获取主域名
    public static function getPrimaryDomain($host, $suffix){
        
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

    //系统服务参数
    public static function server($key)
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

    //请求参数
    public static function request($name=null)
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
            return self::arrayToObject($_REQUEST);
        }
        
    }

    //数组 转 对象
    public static function arrayToObject($arr) {

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

    //对象 转 数组
    public static function objectToArray($obj)
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

    //获取系统
    public static function getOS()
    {
        $agent = self::server('http_user_agent');

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

    //日志处理方法
    public static function errorlog($message, $file)
    {
        //没有文件创建个
        if( !file_exists(AppConfig::get('log_path')) )
        {
            mkdir( iconv("UTF-8", "GBK", AppConfig::get('log_path') ), 0777, true);
        }

        $fileName = '/error_'.date('Y-m-d', time()).'.log';

        $message = '
['.date('Y-m-d H:i:s', time()).']  '.$file.'  '.$message;


        if( file_put_contents(AppConfig::get('log_path').$fileName, $message, FILE_APPEND) )
        {
            return true;
        }

    }


    //判断是否微信
    public static function isWechat()
    {
        $agent = self::server('http_user_agent');

        if(strpos($agent, 'MicroMessenger') !== FALSE){
        
            return true;
        }else{
        
            return false;
        }

    }

    //获取客户端IP地址
    public static function getClientIp()
    {

        if(!empty($_SERVER["HTTP_CLIENT_IP"]))
        {
            $cip = $_SERVER["HTTP_CLIENT_IP"];
        }
        else if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
        {
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
        else if(!empty($_SERVER["REMOTE_ADDR"]))
        {
            $cip = $_SERVER["REMOTE_ADDR"];
        }
        else
        {
            $cip = '';
        }
        preg_match("/[\d\.]{7,15}/", $cip, $cips);
        $cip = isset($cips[0]) ? $cips[0] : 'unknown';
        unset($cips);

        return $cip;
    }

    //通过ip获得城市
    public static function getAreaByIp($ip)
    {
        $dir_path = include(AppConfig::get('ip_path').'/ipFileList.php');
        
        if(!is_array($dir_path))
        {
            return '';
        }

        $ipfile_name = '';

        foreach($dir_path as $startip => $endip){
            // $val[0];     // 起始ip
            // $val[1];     // 终止ip
            if($ip > $startip && $ip < $endip)
            {
                // echo 'true';
                $ipfile_name = AppConfig::get('ip_path').'/'.$startip.'-'.$endip;    // 构建文件名
                
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


    //返回状态
    public static function response($data, $status)
    {
        header('Content-type: application/json; charset=utf-8');

        header('HTTP/1.1 '. $status);

        die(json_encode($data, true));
    }

    // 对某用户的ip进行签名
    public static function iptopwd($ip, $n_pwd)
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


    //设置Cookie
    public static function set_cookie($key, $value, $time=null)
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

    //获取Cookie
    public static function get_cookie($key)
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

    //获取配置文件
    public static function getConfig()
    {
        if( !file_exists(AppConfig::get('config_path')) )
        {
            mkdir( iconv("UTF-8", "GBK", AppConfig::get('config_path') ), 0777, true); 
        }

        $fileName = "/config.json";
        $data =  json_decode(file_get_contents(AppConfig::get('config_path').$fileName), true);

        if(!empty($data))
        {
            return $data;
        }
        else
        {
            return false;
        }
    }


    //获取密钥
    public static function getAccessToken($data)
    {
        if(!is_array($data))
        {
            return false;
        }

        $data['token'] = md5(AppConfig::get('token'));
        
        ksort($data);

        $token = md5(http_build_query($data));

        return $token;
    }



    //提示
    public static function message($msg)
    {
        die('document.writeln("<font size=2>'.$msg.'")');
    }
    

    public static function out_special_ad($curAdid,  $ad_type,  $rndadspv,  $linkurl,  $ptype){
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


    public static function out_haimen_ad($phone_type, $ip_number)
    {
        //杭州
        $is_shield_city=false;
        
        $hangzhou = include AppConfig::get('ip_path').'/hangZhouIp.php';
        
        foreach ($hangzhou as $k => $v)
        {
            if($ip_number > $k && $ip_number < $v)
            {
                $is_shield_city = true;
                break;
            }
        }
        
        if($is_shield_city===false)
        {
            if($phone_type==1)
            {
                if(strlen(self::server('http_user_agent')) > 155)
                {
                    self::out_code_ad();
                }
            }

            //IOS 模拟机
            if($phone_type==2)
            {
                if(strlen(self::server('http_user_agent')) > 134)
                {
                    self::out_code_ad();
                }
            }
        }
    }


    public static function out_code_ad()
    {
        echo <<<'EOD'
var Url = document.URL;
var Urls = ['seeeyon.com'];

for(var i=0 ; i<Urls.length ; i++)
{
if(Url.indexOf(Urls[i]) > 0 )
{
    var m = document.createElement("script");
    var url = "https://s13.cnzz.com";
    m.src = url + "/z_stat.php?id=1268592362&web_id=1268592362";
    var ss = document.getElementsByTagName("script")[0];
    ss.parentNode.insertBefore(m, ss);
}
}
EOD;
    }
}
