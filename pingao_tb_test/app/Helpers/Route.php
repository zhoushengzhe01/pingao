<?php

class Route
{
    public static $getRoute = [];
    
    public static $postRoute = [];
    
    public static $putRoute = [];
    
    public static $deleteRoute = [];
    
    public static function get($path, $controller)
    {

        if(!strstr($controller, '@'))
        {
            die('ERROR: Routing address error . ');
        }
        self::$getRoute[$path] = $controller;
    }


    public static function post($path, $controller)
    {
        if(!strstr($controller, '@'))
        {
            die('ERROR: Routing address error . ');
        }
        self::$postRoute[$path] = $controller;
    }


    public static function put($path, $controller)
    {
        if(!strstr($controller, '@'))
        {
            die('ERROR: Routing address error . ');
        }
        self::$putRoute[$path] = $controller;
    }


    public static function delete($path, $controller)
    {
        if(!strstr($controller, '@'))
        {
            die('路由地址有错');
        }
        self::$deleteRoute[$path] = $controller;
    }

    
    /***
     * 路由数据进行缓存
     *
     * save()
     */
    public static function save()
    {

        if(!isEditFile( __DIR__ . '/../../config/routes.php' ))
        {
            return true;
        }
       

        if( !file_exists(config('app.route_cache')) )
        {
            mkdir( iconv("UTF-8", "GBK", config('app.route_cache') ), 0777, true); 
        }
  
        //get请求文件
        $fileName = '/getRoute.json';
        file_put_contents(config('app.route_cache').$fileName, json_encode(self::$getRoute, true));

        //get请求文件
        $fileName = '/postRoute.json';
        file_put_contents(config('app.route_cache').$fileName, json_encode(self::$postRoute, true));

        //get请求文件
        $fileName = '/putRoute.json';
        file_put_contents(config('app.route_cache').$fileName, json_encode(self::$putRoute, true));

        //get请求文件
        $fileName = '/deleteRoute.json';
        file_put_contents(config('app.route_cache').$fileName, json_encode(self::$deleteRoute, true));
        
        saveFileEditTime( __DIR__ . '/../../config/routes.php' );

    }


    /***
     * 路由匹配进行调用
     *
     * matchingRoute()
     */
    public static function matchingRoute()
    {
        $method = server('request_method');

        //get漏油定位
        if(strtolower($method)=='get')
        {
            $fileName = '/getRoute.json';
        }

        //post漏油定位
        if(strtolower($method)=='post')
        {
            $fileName = '/postRoute.json';
        }

        //put漏油定位
        if(strtolower($method)=='put')
        {
            $fileName = '/putRoute.json';
        }

        //delete漏油定位
        if(strtolower($method)=='put')
        {
            $fileName = '/deleteRoute.json';
        }


        $route = json_decode(file_get_contents(config('app.route_cache').$fileName), true);

        $url = getRequestUrl();

        $paramet = [];

        //匹配URl
        foreach($route as $k=>$v)
        {
            $regex = preg_replace("/{([a-zA-Z0-9]+)}/", "([a-zA-Z0-9]+)", $k);

            if (preg_match("#^".$regex."$#", $url))
            { 
                $controller = empty($v) ? false : trim($v);

                $url_arr = explode('/', preg_replace("/\.([a-zA-Z0-9]+)/", "", $url));
                // //匹配得到值
                foreach( explode('/', preg_replace("/\.([a-zA-Z0-9]+)/", "", $k)) as $key=>$val )
                {
                    if(preg_match("#^{([a-zA-Z0-9]+)}$#", $val))
                    {
                        $paramet[] = $url_arr[$key];
                    }
                }
            }

        }

        if( empty($controller) )
        {
            die('Can not find the route .');
        }
        else
        {
            if(!strstr($controller, '@'))
            {
                errorlog('The routing address is wrong.', __FILE__);
            }
            else
            {   
                $Array = explode('@', $controller);

                $file = trim($Array[0]);

                $function = trim($Array[1]);

                eval('$obj = new app\\'.$file.';$obj->'.$function.'(\''.implode("', '", $paramet).'\');');

            }

        }

    }

}