<?php
namespace app;

use app\Config\AppConfig;
use app\Helpers\Model;
use app\Helpers\Mssql;
use app\Helpers\Helper;

class PvController extends CommonController
{
    function __construct()
    {
        
        parent::__construct();
    }

    public function pvAction(){

        parse_str(base64_decode(Helper::request('u') ? Helper::request('u') : null), $data);
        if( empty($data['adsid']) || empty($data['website_id']) || empty($data['position_id']) || empty($data['type_id']))
            Helper::response(['start'=>false, 'msg'=>'Parameter error'], 310);
        if( empty($data['gotourl']) || empty($data['time']) || empty($data['access_token']) )
            Helper::response(['start'=>false, 'msg'=>'Parameter error'], 310);

        $is_iframe = Helper::request('is_iframe') ? Helper::request('is_iframe') : '';
        
        $adsid = trim($data['adsid']);
        $userid = trim($data['website_id']);
        $pid = trim($data['position_id']);
        $tid = trim($data['type_id']);
        $token = $data['access_token'];
        
        //验证密钥
        unset($data['access_token']);
        if(Helper::getAccessToken($data) != $token)
            Helper::response(['start'=>false, 'msg'=>'access_token error'], 310);


        $mssql = new Mssql;

        //插入PV
        $bind_data = [
            ['pid', $data['position_id'], SQLINT2],
            ['adsid', $data['adsid'], SQLVARCHAR],
            ['userid', $data['website_id'], SQLINT2]
        ];
        $result = $mssql->init('vistdata69_cpv_count')->bindArr($bind_data)->execute();
        $mssql->freeStatement();

        //失败返回
        if(empty($result))
            die("var error;");
        else
            die("var success;");
    }
}