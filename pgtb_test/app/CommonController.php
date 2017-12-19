<?php
namespace app;

use Memcache;

use app\Config\AppConfig;
use app\Helpers\Helper;

class CommonController 
{

    protected $memcache_obj;    //数据库

    protected $client_data;     //客户端信息


    public function __construct()
    {

        $this->init();
    }

    /***
     * 调用从这里开始
     * 
     * init()
     */
    public function init()
    {

        // 拦截不可访问的客户端
        if( !in_array(Helper::getOS(), AppConfig::get('permit_sys')) )
        {
            die('The system can not be accessed.');
        }


        // 禁止微信访问
        if( Helper::isWechat() !== FALSE )
        {
            $this->client_data->is_wechat = 0;
        }
        else
        {
            $this->client_data->is_wechat = 1;
        }


        //判断客户端类型
        if(strpos(Helper::server('http_user_agent'), 'Android') !== false)
        {
	        $this->client_data->phone_type = 1;
        }
        elseif(strpos(Helper::server('http_user_agent'), 'iPad') !== false or strpos(Helper::server('http_user_agent'), 'iPhone') !== false)
        {
	        $this->client_data->phone_type = 2;
        }
        else
        {
            die('Not through client');
        }

        //验证来访者
        if(!Helper::server('http_referer') || !AppConfig::get('type_id'))
        {
            die('Illegal entrance');
        }

        //来源地址判断
        $referer = parse_url(Helper::server('http_referer'));
        $host = empty($referer['host']) ? '' : $referer['host'];
        if(!$host)
        {
            Helper::message('The wrong entrance');
        }
        
        //检测域名后缀
        foreach(AppConfig::get('domain_suffix') as $key=>$val)
        {
            if(strrpos($host, $val) !== FALSE)
            {
                $this->client_data->domain = Helper::getPrimaryDomain($host, $val);
                break;
            }
        }
        if(empty($this->client_data->domain))
        {
            $this->client_data->domain = substr($host, strpos($host, '.') + 1);
        }
        
        //memcache缓存处理
        $this->memcache_obj = new Memcache;
        
        $this->memcache_obj->pconnect(AppConfig::get('memcache.host'), AppConfig::get('memcache.port'));   
    }
}