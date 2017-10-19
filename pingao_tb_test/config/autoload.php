<?php
return [

    //配置加载文件夹
    'path' => [

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
            ],
        ]
    ],

    //设置加载文件
    'load' => ['app', 'helpers'],
];