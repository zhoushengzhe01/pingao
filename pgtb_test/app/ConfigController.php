<?php
namespace app;

use app\Config\AppConfig;
use app\Helpers\Model;
use app\Helpers\Mssql;
use app\Helpers\Helper;


class ConfigController
{

    // 获取配置
    public function getAction(){

        header('Content-type: application/json; charset=utf-8');
        
        if( !file_exists( AppConfig::get('config_path') ) )
        {
            mkdir( iconv("UTF-8", "GBK", AppConfig::get('config_path') ), 0777, true); 
        }

        $fileName = "/config.json";
        $data =  json_decode(file_get_contents(AppConfig::get('config_path').$fileName), true);

        if(!empty($data))
        {
            echo json_encode(['start'=>true, 'data'=>$data], true);
            die;
        }
        else
        {
            echo json_encode(['start'=>false, 'msg'=>'获取失败'], true);
            die;
        }

    }

    // 提交配置
    public function postAction(){

        header('Content-type: application/json; charset=utf-8');

        $wx_skip  = Helper::request('wx_skip') ? Helper::request('wx_skip') : 0;
        
        $wx_ad_ids  = Helper::request('wx_ad_ids') ? Helper::request('wx_ad_ids') : 0;

        if(empty($wx_skip))
        {
            echo json_encode(['start'=>false, 'msg'=>'微信量跳转地址']);
            die;
        }

        $data = [
            "wx_skip" => $wx_skip,
            "wx_ad_ids" => $wx_ad_ids,
        ];

        $fileName = "/config.json";
        file_put_contents(AppConfig::get('config_path').$fileName, json_encode($data, true));

        echo json_encode(['start'=>true, 'msg'=>'修改成功'], true);
        die;
    }

}