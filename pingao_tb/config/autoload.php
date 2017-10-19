<?php
return [

    //配置加载文件夹
    'path' => [

        'config' => [
            'path' => '/config',
        ],

        'app' => [
            'path' => '/app',
            
            //选加载的文件
            'file' => [
                'CommonController.php',
            ],
        ],

        'helpers' => [
            'path' => '/app/Helpers',

            //选加载的文件
            'file' => [
                'Mssql.php',
                'Mysql.php'
            ],

        ]
    ],

    //设置加载文件
    'load' => ['config', 'app', 'helpers'],

    //不自动加载的文件
    'no_load' => ['routes.php']

];