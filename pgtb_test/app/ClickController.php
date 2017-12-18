<?php
namespace app;

use Memcache;

use app\Config\AppConfig;
use app\Helpers\Model;
use app\Helpers\Mssql;
use app\Helpers\Helper;


class ClickController
{
    protected $memcache_obj;    //数据库
    
    function __construct()
    {
        //memcache缓存处理
        $this->memcache_obj = new Memcache;
        
        $this->memcache_obj->pconnect( AppConfig::get('memcache.host'), AppConfig::get('memcache.port'));
    }

    /**
     * 点击计费
     */
    public function clickAction(){
        
        parse_str(base64_decode( Helper::request('s') ? Helper::request('s') : null ), $data);
        //判断为空
        if( empty($data['adsid']) || empty($data['website_id']) || empty($data['position_id']) || empty($data['type_id']))
            Helper::response(['start'=>false, 'msg'=>'Parameter error'], 310);
        if(empty($data['gotourl']) || empty($data['time']) || empty($data['access_token']) )
            Helper::response(['start'=>false, 'msg'=>'Parameter error'], 310);


        //获取参数
        $url    = substr( Helper::request('url') ? Helper::request('url') : 0, 0, 450);
        $reurl  = substr( Helper::request('reurl') ? Helper::request('reurl') : '', 0, 450);
        $type   = Helper::request('type') ? Helper::request('type') : 1;
        $refso  = Helper::request('refso') ? Helper::request('refso') : 0;
        

        $adsid = trim($data['adsid']);
        $userid = trim($data['website_id']);
        $pid = trim($data['position_id']);
        $tid = trim($data['type_id']);
        $gotourl = UrlDecode($data['gotourl']);
        $token = trim($data['access_token']);

        //密钥验证
        unset($data['access_token']);
        if(Helper::getAccessToken($data) != $token)
            Helper::response(['start'=>false, 'msg'=>'access_token error'], 310);


        //获取广告
        $model = new Model;
        $ads = $model->uptype($tid, $this->memcache_obj);
        
        if(!empty($ads->info->$adsid))
            $myads = $ads->info->$adsid;
        else
            Helper::response(['start'=>false, 'msg'=>'No ads found'], 310);


        //安卓微信跳转域名
        $is_skip_domain = false;
        if(strpos(Helper::server('http_user_agent'), 'Android')==true && Helper::isWechat()==true)
        {
            $config = Helper::getConfig();
            if(!empty($config['wx_ad_ids']) && !empty($config['wx_skip']))
            {
                $adsid_array = explode(',', $config['wx_ad_ids']);
                if(in_array($adsid, $adsid_array))
                {
                    $is_skip_domain = true;
                }
            }
        }

        // 判断是否跳转域名
        if($is_skip_domain && !empty($config) && !empty($config['wx_skip']))
            $gotourl = str_replace("##", $gotourl, $config['wx_skip']);

        //广告里面没有gotourl 直接跳转
        if(empty($myads->gotourl)){
            header('Location: ' . $gotourl);die;
        }

        // 导出PC流量
        if(strpos(Helper::server('http_user_agent'), 'Mobile') === FALSE){
            header('Location: ' . $gotourl);exit;
        }

        $jilu = true;
        $userip = Helper::getClientIp();    // 获取IP地址
        $ip_number = ip2long($userip);      // 将IP转换为数字
        $useripaid = $userip . $adsid;


        if(strpos($refso, 'Mac') === false && strpos($refso, 'Win') === false){
            
            $cpc = Helper::get_cookie( md5('pg_icon_cpc' . $ip_number . $adsid) );

            if($cpc){
                $cpc = json_decode($cpc, true);
                $cpc_count = empty($cpc['cpc_count']) ? 0 : intval($cpc['cpc_count']);
                $is_count = empty($cpc['is_count']) ? 0 : trim($cpc['is_count']);
            }else{
                $cpc_count = 0;
                $is_count = 0;    
            }

            //假关闭计费率
            $is_record = true;
            if($type==2)
            {
                $fault_l = AppConfig::get('fault.default');
                foreach(AppConfig::get('fault') as $key=>$val)
                {
                    if($key==$userid)
                    {
                        $fault_l = $val;
                    }
                }
                if( rand(0, 100) > $fault_l  )
                {
                    $is_record = false;
                }
            }

            if((AppConfig::get('pre_ip') > $cpc_count) && !$is_count && $is_record){
             
                $mssql = new Mssql;

                //cpc查询
                $bind_data = [
                    ['adsid', $adsid, SQLVARCHAR],
                    ['uip', $ip_number, SQLVARCHAR]
                ];
                $result = $mssql->init('xyz69_cpc')->bindArr($bind_data)->execute();
                $cpc_ads = $mssql->fetchRow($result);
                $mssql->freeStatement();

                //错误日志
                if(empty($result))
                    Helper::errorlog('The reservoir process xyz69_cpc failed.', __FILE__);

                //没有则插入cpc
                if(!$cpc_ads){

                    $bind_data = [
                        ['userid', $userid, SQLINT2],
                        ['adsid', $adsid, SQLVARCHAR],
                        ['mip', $ip_number, SQLVARCHAR],
                        ['pid', $pid, SQLINT2]
                    ];
                    $result = $mssql->init('vistdata69_cpc_count')->bindArr($bind_data)->execute();
                    $mssql->freeStatement();

                    //记录错误日志
                    if(empty($result))
                        Helper::errorlog('The reservoir process vistdata69_cpc_count failed.', __FILE__);

                    //储存cookie
                    $cookieData = ['cpc_count'=>(intval($cpc_count)+1), 'is_count'=>1];
                    Helper::set_cookie( md5('pg_icon_cpc' . $ip_number . $adsid), json_encode($cookieData, true) );

                    $jilu = false;
                }
                
            }
        }


        //记录前来源
        if(mt_rand(1,3) == 1){

            if($url || $reurl || $refso){

                //点击总统计
                $mssql = new Mssql('mssql_date');

                $bind_data = [
                    ['fromid', $userid, SQLVARCHAR],
                    ['toid', $adsid, SQLVARCHAR],
                    ['firsturl', $reurl, SQLVARCHAR],
                    ['fromurl', $url, SQLVARCHAR],
                    ['ip', $userip, SQLVARCHAR],
                    ['numberip', $ip_number, SQLVARCHAR],
                    ['tourl', Helper::server('http_user_agent'), SQLVARCHAR],
                    ['pid', $pid, SQLVARCHAR],
                    ['refso', $refso, SQLVARCHAR],
                    ['tid', $tid, SQLVARCHAR]
                ];

                $result = $mssql->init('vistdata_cpccountinfos')->bindArr($bind_data)->execute();
                $mssql->freeStatement();

                if(empty($result))
                {
                    Helper::errorlog('The reservoir process vistdata_cpccountinfos failed.', __FILE__);
                }

            }
        }

        /***
         * 检测广告是否超标
         * 
         * 超标更新缓存数据
         */
        if(!$jilu)
        {
            if(mt_rand(1,40) == 1)
            {
                // 判断总量是否完毕
                $model = new model;

                $object = $model->table('user2')->where('userid', '=', $myads->userid)->where('admoney', '<', '5')->get();

                if(!$object)
                {
                    //超标更新缓存
                    $model = new Model;
                    $model->uptype($tid, $this->memcache_obj, true);
                }
            }
        }
        else
        {
            // 判断每日限量是否超标
            $todaymoney = $myads->{'todaymoney'.date('Y-m-d')};

            $todaynowpop = $myads->{'todaymaxmoney'.date('Y-m-d')};
            
            if($todaynowpop > 0 && ($todaynowpop <= $todaymoney))
            {
                //超标更新缓存
                //$model = new Model;
                //$ads = $model->uptype($tid, $this->memcache_obj, true);
            }
            
        }
        
        header('Location: '.$gotourl);
    }

}