<?php
define('__ROOT__', __DIR__);

$loading = require __DIR__.'/config/autoload.php';

/***
 * 载入自动加载文件
 *
 * paramet: dir(目录)  noLoad(不加载的文件)  yesLoad(已经加载的文件)
 *
 * config/autoload.php 文件
 */
function loadFile($dir, $no_load=[], $yes_load=[])
{
    $handle = opendir($dir);

    if ( $handle )
    {
        while ( ( $file = readdir ( $handle ) ) !== false )
        {

            if ( $file != '.' && $file != '..')
            {
                if(!in_array($file, $yes_load) && !in_array($file, $no_load))
                {
                    $cur_path = $dir . DIRECTORY_SEPARATOR . $file;
                    if ( is_dir ( $cur_path ) )
                    {
                        //子目录加载
                        //loadFile ( $cur_path, $no_load );
                    }
                    else
                    {
                        require $cur_path;
                    }
                }
                
            }
        }
        closedir($handle);
    }

    return true;
}


if( is_array($loading) && count($loading)>0 )
{
    if( empty($loading['path']) || empty($loading['load']) )
    {
        die("config/autoload.php 文件出错！");
    }

    if( is_array($loading['path']) && is_array($loading['load']) )
    {

        foreach($loading['load'] as $key=>$value)
        {

            
            $load = $loading['path'][$value];
            
            //选加载文件
            if( !empty($load['file']) && is_array($load['file']) && count($load['file'])>0 )
            {
                foreach($load['file'] as $key=>$value)
                {

                    if(!empty($load['path']))
                    {   
                        require __DIR__ . $load['path'] . '/' . $value;
                    }
                    
                }

            }

            //其次加载文件
            if(!empty($load['path']))
            {
                if( !empty($load['file']) && is_array($load['file']) && count($load['file'])>0 )
                {

                    loadFile( __DIR__ . $load['path'], $loading['no_load'], $load['file']);
                }
                else
                {
                    
                    loadFile( __DIR__ . $load['path'], $loading['no_load'] );
                }
            }

        }
    }
    else
    {
        die("config/autoload.php 文件出错！");
    }
}