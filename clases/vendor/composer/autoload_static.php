<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc143a2a3b3f4c8bb71f64d367de74db3
{
    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'FlowrouteMessagingLib\\' => 22,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'FlowrouteMessagingLib\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'U' => 
        array (
            'Unirest' => 
            array (
                0 => __DIR__ . '/..' . '/apimatic/unirest-php/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitc143a2a3b3f4c8bb71f64d367de74db3::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitc143a2a3b3f4c8bb71f64d367de74db3::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitc143a2a3b3f4c8bb71f64d367de74db3::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
