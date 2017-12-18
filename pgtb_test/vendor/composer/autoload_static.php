<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit7e52adf01015bb630546046ccf717107
{
    public static $prefixLengthsPsr4 = array (
        'a' => 
        array (
            'app\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'app\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
    );

    public static $prefixesPsr0 = array (
        'M' => 
        array (
            'Monolog' => 
            array (
                0 => __DIR__ . '/..' . '/monolog/monolog/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit7e52adf01015bb630546046ccf717107::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit7e52adf01015bb630546046ccf717107::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit7e52adf01015bb630546046ccf717107::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}