<?php
namespace app;

use App\Helpers\Model;
use app\Helpers\Mssql;

class PvController extends CommonController
{
    function __construct()
    {
        
        parent::__construct();
    }

    public function pvAction(){
        
        //计数pv
        $str = base64_decode(request('u') ? request('u') : null);

        parse_str($str, $data);

        if( empty($data['adsid']) || empty($data['website_id']) || empty($data['position_id']) || empty($data['type_id']) || empty($data['gotourl']) || empty($data['time']) || empty($data['access_token']))
        {
            response(['start'=>false, 'msg'=>'Parameter error'], 310);
        }

        $token = $data['access_token'];
        
        unset($data['access_token']);

        //访问密钥验证
        if(getAccessToken($data) != $token)
        {
            response(['start'=>false, 'msg'=>'access_token error'], 310);
        }

        $mssql = new Mssql;

        $bind_data = [
            ['pid', $data['position_id'], SQLINT2],
            ['adsid', $data['adsid'], SQLVARCHAR],
            ['userid', $data['website_id'], SQLINT2]
        ];

        $result = $mssql->init('vistdata69_cpv_count')->bindArr($bind_data)->execute();

        $mssql->freeStatement();

        if(empty($result))
        {
            errorlog('The reservoir process vistdata69_cpv_count failed.', __FILE__);

            response(['start'=>false], 310);
        }
        else
        {
            response(['start'=>true, 'msg'=>'success'], 200);
        }

    }
}