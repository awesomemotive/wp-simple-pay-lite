<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit38333d11ecc7afbc8bdea63441179c55
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'SimplePay\\Vendor\\Stripe\\' => 24,
            'SimplePay\\Vendor\\' => 17,
            'SimplePay\\Core\\' => 15,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'SimplePay\\Vendor\\Stripe\\' => 
        array (
            0 => __DIR__ . '/../..' . '/lib/Stripe/lib',
        ),
        'SimplePay\\Vendor\\' => 
        array (
            0 => __DIR__ . '/../..' . '/lib',
        ),
        'SimplePay\\Core\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit38333d11ecc7afbc8bdea63441179c55::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit38333d11ecc7afbc8bdea63441179c55::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit38333d11ecc7afbc8bdea63441179c55::$classMap;

        }, null, ClassLoader::class);
    }
}
