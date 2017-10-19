<?php
namespace app;

use Memcache;

use App\Helpers\Model;
use app\Helpers\Mssql;


class CsstypeController
{
    /**
     * 点击计费
     */
    public function cssAction(){
        
        header("Content-type: text/css");
        
        require __ROOT__.'/app/style/css.css';

    }

}