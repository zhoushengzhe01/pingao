<?php
namespace app\Helpers;

use app\Helpers\Mysql;
use app\Helpers\Mssql;

class Model extends Mssql
{
    //获取广告位数据
    public function getAdposition($pid, $tid, $memcache)
    {
        $cacheKey = 'pgtb_adposition_'.$pid;

        $object = $memcache->get($cacheKey);

        if(empty($object))
        {
            $object = $this->table('ADposition')->where('typeid', '=', $tid)->where('id', '=', $pid)->first();
            
            if($object)
            {
                //进行缓存
                $memcache->set($cacheKey, $object, MEMCACHE_COMPRESSED, config('cache.memcache.time'));
            }
            else
            {
                return false;
            }
            
        }

        return $object;

    }

    //获取站长数据
    public function getWebsite($websiteId, $memcache)
    {


        $cacheKey = 'pgtb_userinfo_'.$websiteId;

        $object = $memcache->get($cacheKey);

        if(empty($object))
        {
            $object = $this->table('user1')->where('userid', '=', $websiteId)->first();
            
            if($object)
            {

                $object->urlstr = $this->getWebsiteUrl($websiteId);

                if($object->urlstr===false)
                {
                    ////////////////
                }

                //进行memcache缓存
                $memcache->set($cacheKey, $object, MEMCACHE_COMPRESSED, config('cache.memcache.time'));

            }
            else
            {
                return false;
            }
            
        }

        return $object; 
    
    }


    public function getWebsiteUrl($websiteId)
    {

        //$cacheKey = 'pghf_userurl_'.$pid;

        //$string = $memcache->get($cacheKey);

        if(empty($string))
        {
            $object = $this->table('user1_url')->where('userid', '=', $websiteId)->where('ok', '=', '1')->get();
            
            if($object)
            {
                $string = '';
                foreach($object as $key=>$value)
                {
                    $string .= '('.$value->url.'){'.$value->id.'}';
                }

                //进行memcache缓存
                //$memcache->set($cacheKey, $string, MEMCACHE_COMPRESSED, config('cache.memcache.time'));

            }
            else
            {
                return false;
            }
            
        }

        return $string;
        
    }

    //获取广告
    public function getAds($tid, $memcache, $update = false)
    {

        $ads = (object)[];

        if(!$memcache->get('pgtb_adslist_isava'.$tid) || $update)
        {
            $memcache->set('pgtb_adslist_isava'.$tid, 1, MEMCACHE_COMPRESSED, config('cache.memcache.time'));

            $hour = (string)date("H");
            $time = (string)date("Y-m-d");
            
            
            $sql='update ads set shenhe=2 from ads,user2 where ads.userid=user2.userid and ads.typeid='.config('app.type_id').' and ads.shenhe=1 and user2.admoney<5';
            $this->query($sql);

            $sql = 'SELECT ISNULL(ads.weight, 1) AS weight1, ads.tznum, ads.unshow_phone, ads.id, ads.istype, ads.okarea, ads.phonetype, ads.userid, ads.picurl0, ads.picurl1, ads.picurl2, ads.gotourl,ads.blacksiteid,ads.limitsiteid,ads.sqms,a.money as \'todaymoney'.$time.'\', ads.maxmoney as \'todaymaxmoney'.$time.'\', ads.adstypeid FROM ads LEFT JOIN (select sum(money) as money,adgid from user2data where user2data.dt = \''.$time.'\' group by adgid ) a ON ads.adgid = a.adgid  WHERE (ads.lmid=0 or ads.lmid=1) and (ads.typeid = '.$tid.') AND (ads.shenhe = 1) AND (ads.toutime LIKE \'%'.$hour.'%\') AND (ads.stime <= \''.$time.'\') AND (ads.etime >= \''.$time.'\' ) AND (ads.maxmoney<=0 or a.money is null or (ads.maxmoney>0 and not a.money is null and ads.maxmoney > a.money)) ORDER BY ads.id';
            $objects = $this->select($sql);
  
            $ads->list = (object)[];
            $ads->info = (object)[];
            
            foreach($objects as $key=>$value)
            {
                if(empty($ads->list->{$value->adstypeid}))
                {
                    $ads->list->{$value->adstypeid} = (object)[];
                }
                
                $ads->list->{$value->adstypeid}->{$key} = $value->id;

                $ads->info->{$value->id} = $value;
            }

            $memcache->set('pgtb_adsinfo', $ads->list, MEMCACHE_COMPRESSED, config('cache.memcache.time'));
            $memcache->set('pgtb_adslist_'.$tid, $ads->info, MEMCACHE_COMPRESSED, config('cache.memcache.time'));

        }else{

            $ads->list = $memcache->get('pgtb_adsinfo');
            $ads->info = $memcache->get('pgtb_adslist_'.$tid);

        }

        return $ads;
    }


    // 广告轮显完或者无广告或者整点 更新 memcache 数据,返回地域数组
    public function uptype($tid, $memcache, $update = false){

        $hour = (string)date('H');
        $time = (string)date("Y-m-d");
        
        // 筛选出符合投放广告类型的广告，且广告本身处于可投放状态
        $sql = 'SELECT ISNULL(ads.weight, 1) AS weight1,ads.id,ads.unshow_phone,ads.istype,ads.okarea,ads.phonetype,ads.userid,ads.picurl0,ads.picurl1,ads.picurl2,ads.picurl3,ads.picurl4,ads.wmap,ads.wapp,ads.gotourl,ads.blacksiteid,ads.limitsiteid,ads.sqms,a.money as \'todaymoney'.$time.'\', ads.maxmoney as \'todaymaxmoney'.$time.'\',ads.adstypeid FROM ads LEFT JOIN (select sum(money) as money,adgid from user2data where user2data.dt = \''.$time.'\' group by adgid ) a ON ads.adgid = a.adgid WHERE (ads.lmid=0 or ads.lmid=1) and (ads.typeid = '.$tid.') AND (ads.shenhe = 1) AND (ads.toutime LIKE \'%'.$hour.'%\') AND (ads.stime <= \''.$time.'\') AND (ads.etime >= \''.$time.'\' ) AND (ads.maxmoney<=0 or a.money is null or (ads.maxmoney>0 and not a.money is null and ads.maxmoney > a.money)) ORDER BY ads.pqid,ads.id';
        
        $objects = $memcache->get(md5($sql));

        if( !$objects || $update )
        {
            $objects = $this->select($sql);

            $sql='update ads set shenhe=2 from ads,user2 where ads.userid=user2.userid and ads.typeid='.config('app.type_id').' and ads.shenhe=1 and user2.admoney<5';
            $this->query($sql);

            $memcache->set(md5($sql), $objects, MEMCACHE_COMPRESSED, config('cache.memcache.time'));
        }

        // print_r($objects);
        
        $ads = (object)[];
        $ads->list = (object)[];
        $ads->info = (object)[];
        
        foreach($objects as $key=>$value)
        {
            if(empty($ads->list->{$value->adstypeid}))
            {
                $ads->list->{$value->adstypeid} = (object)[];
            }
            
            $ads->list->{$value->adstypeid}->{$key} = $value->id;

            $ads->info->{$value->id} = $value;
        }

        return $ads;
    }
}
