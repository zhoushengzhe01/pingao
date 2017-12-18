<?php
namespace app;

use Memcache;

use App\Helpers\Model;
use app\Helpers\Mssql;


class ClickController
{
    protected $memcache_obj;    //数据库
    
    function __construct()
    {
        //memcache缓存处理
        $this->memcache_obj = new Memcache;
        
        $this->memcache_obj->pconnect(config('app.memcache.host'),config('app.memcache.port'));
        
    }

    /**
     * 点击计费
     */
    public function clickAction(){
  
        //获取参数
        $str = base64_decode(request('s') ? request('s') : null);
        $refso  = request('refso') ? request('refso') : 0;
        $url    = substr(request('url') ? request('url') : 0, 0, 450);
        $reurl  = substr(request('reurl') ? request('reurl') : '', 0, 450);
        $type  = request('type') ? request('type') : 1;
        
        parse_str($str, $data);

        if( empty($data['adsid']) || empty($data['website_id']) || empty($data['position_id']) || empty($data['type_id']) || empty($data['gotourl']) || empty($data['time']) || empty($data['access_token']))
        {
            response(['start'=>false, 'msg'=>'Parameter error'], 310);
        }

        $adsid = trim($data['adsid']);
        $userid = trim($data['website_id']);
        $pid = trim($data['position_id']);
        $tid = trim($data['type_id']);

        $token = $data['access_token'];
        unset($data['access_token']);

        //访问密钥验证
        if(getAccessToken($data) != $token)
        {
            response(['start'=>false, 'msg'=>'access_token error'], 310);
        }

        $data['gotourl'] = UrlDecode($data['gotourl']);

        $model = new Model;
        $ads = $model->uptype($tid, $this->memcache_obj);

        $ads_list = $ads->list;
        $ads_info = $ads->info;
        
        @$ads = $ads_info->$adsid;
        @$gotourl = $ads->gotourl;


        $is_skip_domain = false;
        if(strpos(server('http_user_agent'), 'Android')==true && isWechat()==true)
        {
            $config = getConfig();
            if(!empty($config['wx_ad_ids']) && !empty($config['wx_skip']))
            {
                $adsid_array = explode(',', $config['wx_ad_ids']);
                if(in_array($adsid, $adsid_array))
                {
                    $is_skip_domain = true;
                }
            }
        }

        if(empty($gotourl))
        {
            // 判断是否跳转域名
            if($is_skip_domain && !empty($config) && !empty($config['wx_skip']))
            {
                $gotourl = str_replace("##", $data['gotourl'], $config['wx_skip']);
            }
            else
            {
                $gotourl = $data['gotourl'];
            }

            header('Location: ' . $gotourl);
            exit;
        }

        // 判断是否跳转域名
        if($is_skip_domain && !empty($config) && !empty($config['wx_skip']))
        {
            $gotourl = str_replace("##", $gotourl, $config['wx_skip']);
        }

        // 导出手机流量
        $user_agent = server('http_user_agent');
        if(strpos($user_agent, 'Mobile') === FALSE)
        {
            header('Location: ' . $gotourl);
            exit;
        }

        $jilu = true;
        $userip = getClientIp();            // 获取IP地址
        $ip_number = ip2long($userip);	    // 将IP转换为数字
        $useripaid = $userip . $adsid;


        if(strpos($refso, 'Mac') === false && strpos($refso, 'Win') === false){
            
            $cpc = get_cookie( md5('pg_icon_cpc' . $ip_number . $adsid) );

            if($cpc)
            {
                $cpc = json_decode($cpc, true);
                $cpc_count = empty($cpc['cpc_count']) ? 0 : intval($cpc['cpc_count']);
                $is_count = empty($cpc['is_count']) ? 0 : trim($cpc['is_count']);
            }
            else
            {
                $cpc_count = 0;
                $is_count = 0;
                
            }

            //获取假关闭计费率
            $is_record = true;
            if($type==2)
            {
                $fault_l = config('app.fault.default');
                foreach(config('app.fault') as $key=>$val)
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

            if((config('app.pre_ip') > $cpc_count) && !$is_count && $is_record){
             
                $mssql = new Mssql;

                $bind_data = [
                    ['adsid', $adsid, SQLVARCHAR],
                    ['uip', $ip_number, SQLVARCHAR]
                ];

                //cpc查询
                $result = $mssql->init('xyz69_cpc')->bindArr($bind_data)->execute();
                $cpc_ads = $mssql->fetchRow($result);
                $mssql->freeStatement();

                if(empty($result))
                {
                    errorlog('The reservoir process xyz69_cpc failed.', __FILE__);
                }
                
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

                    if(empty($result))
                    {
                        errorlog('The reservoir process vistdata69_cpc_count failed.', __FILE__);
                    }

                    $cookieData = [
                        'cpc_count'=>(intval($cpc_count)+1),
                        'is_count'=>1
                    ];

                    set_cookie( md5('pg_icon_cpc' . $ip_number . $adsid), json_encode($cookieData, true) );

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
                    ['tourl', server('http_user_agent'), SQLVARCHAR],
                    ['pid', $pid, SQLVARCHAR],
                    ['refso', $refso, SQLVARCHAR],
                    ['tid', $tid, SQLVARCHAR]
                ];

                $result = $mssql->init('vistdata_cpccountinfos')->bindArr($bind_data)->execute();
                $mssql->freeStatement();

                if(empty($result))
                {
                    errorlog('The reservoir process vistdata_cpccountinfos failed.', __FILE__);
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

                $object = $model->table('user2')->where('userid', '=', $ads->userid)->where('admoney', '<', '5')->get();

                if(!$object)
                {
                    //超标更新缓存
                    $model = new Model;
                    $ads = $model->uptype($tid, $this->memcache_obj, true);
                }
            }
        }
        else
        {
            // 判断每日限量是否超标
            $todaymoney = $ads->{'todaymoney'.date('Y-m-d')};

            $todaynowpop = $ads->{'todaymaxmoney'.date('Y-m-d')};
            
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