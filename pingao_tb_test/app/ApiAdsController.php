<?php
namespace app;

use App\Helpers\Model;
use app\Helpers\Mssql;

class ApiAdsController extends CommonController
{

    protected $website;     //站长用户信息

    protected $paramet;    //广告位 0左边 1右边
    
    protected $jk_domain;   //计算PV域名
    protected $wap_tz_domain;   //点击域名

    function __construct()
    {
        parent::__construct();

        //PV和点击域名
        if(strpos(server('http_referer'), 'https://') !== false){
            
            $this->jk_domain = 'https://tp.ningyizs.com';
            $this->wap_tz_domain = 'https://tt.ningyizs.com';
            
        }else{

            $this->jk_domain = 'http://tp.moecz.com';
            $this->wap_tz_domain = 'http://td.biyao365.com';
        }

    }

    public function adsAction($ad_pos, $position_id){

        $this->paramet->ad_pos = trim($ad_pos);
        $this->paramet->position_id = trim($position_id);

        //验证参数
        if( empty($this->paramet->ad_pos) && $this->paramet->ad_pos!=0 )
        {
            json_encode(['state'=>false, 'msg'=>'parameter error']);die;
        }
        if(empty($this->paramet->position_id))
        {
            json_encode(['state'=>false, 'msg'=>'parameter error']);die;
        }

        //获取广告位信息
        $model = new Model;
        $adposition = $model->getAdposition($this->paramet->position_id, config('app.type_id'), $this->memcache_obj);
        if($adposition===false)
        {
            json_encode(['state'=>false, 'msg'=>'Advertising does not exist']);die;
        }

        //查找站长信息
        $model = new Model;
        $website = $model->getWebsite($adposition->userid, $this->memcache_obj);
        if(empty($website))
        {
            json_encode(['state'=>false, 'msg'=>'The account does not exist']);die;
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
            json_encode(['state'=>false, 'msg'=>'Your account is not open']);die;
        }

        //是否开通此广告类型
        if(strpos($this->website->openty, (string)config('app.type_id')) === false)
        { 
            json_encode(['state'=>false, 'msg'=>'No advertisement type is available']);die;
        }

        //域名是否登记
        if(!strpos($this->website->urlstr, $this->client_data->domain) && $this->website->ifdomain == 0)
        {
            json_encode(['state'=>false, 'msg'=>'Domain name registration.']);die;
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
                    if($ad->istype==$this->isWechat   //是否支持微信
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
 
            $imgsrc[] = config('app.pic_url').$nowpic[array_rand($nowpic, 1)];


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
                json_encode(['state'=>false, 'msg'=>'AccessToken error']);die;
            }

            //$linkurl = base64_encode('0&'.$adsid.'&'.$this->website->userid.'&'.time().'&'.$position_id.'&'.config('app.type_id').'&'.iptopwd($useripaid, date('md')).'&'.base64_encode($ads->$adsid->gotourl));

            if($i == 0)
            {
                $pv_url = $this->jk_domain.'/se?u='.base64_encode(http_build_query($data));
                $anim_adid = $adsid;
            }
            
            $imgcounturl[] = ($this->client_data->is_wechat ? $this->wap_tz_domain : $this->wap_tz_domain)."/url?s=".base64_encode(http_build_query($data));
            
            $gotourls[] = $ads->$adsid->gotourl;

        }

        $GCIDS = 'p'.$this->paramet->ad_pos.$this->website->userid.substr(str_shuffle('abcdefghjkmnpqrstuvwxyz'), 0, 5);
        
        $data = [
            'html' => '
                <div class=\'icon\' id=\'icon\'>
                    <div class=\'close\' id=\'close\'>X</div>
                    <a class=\'href\' href=\'javascript:void(0)\' id=\'href\'>
                        <img id=\'image\' src=\''.$imgsrc[0].'\' class=\'\'/>
                    </a>
                </div>
                <style>
                    .hide{
                        display: none;
                    }
                    .icon{
                        position: fixed;
                        right: 20px;
                        width: 100px;
                        height: 100px;
                        top: 40%;
                    }
                    .icon .close{
                        width: 9px;
                        font-size: 15px;
                        color: #fff;
                        background: #ccc;
                        padding: 4px;
                        position: absolute;
                        text-align: center;
                        line-height: 8px;
                    }
                    .icon .href{
                        display: block;
                    }
                    .icon .href img{
                        width: 100%;
                        top: 16px;
                        position: absolute;
                    }
                    .icon .href .topright{
                        top: 14px;
                        right:-2px;
                    }
                    .icon .href .buttonleft{
                        top: 16px;
                        right:0px;
                    }
                </style>
            ',
            'image' =>$imgsrc[0],
            'url' => $imgcounturl[0],
            'imageArr' => $imgsrc,
            'urlArr' => $imgcounturl,
            'pvUrl' => $pv_url,
            'position' => $this->paramet->ad_pos,
            'falseClose' => $false_close,
            'recordPv' => $this->website->record_pv,
            'isSkip' => false,
            'changeTime' => 12000,
            'actionTime' => 3000,
            
        ];
    
        header('Content-type: application/json');
      
        $json = json_encode(['state'=>true, 'data'=>$data], true);
        $callback = $_GET['callback'];
        exit($callback."($json)");        
        
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

}