<?php

namespace Util;

spl_autoload_register("\Util\Autoloader::autoload");

class Autoloader
{
    private static $autoloadPathArray = [
        "Constant",
        "Http",
        "Util",
    ];
    
    public static function autoload($className)
    {
        foreach (self::$autoloadPathArray as $path) {
            $file = dirname(__DIR__).DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.$className.".php";
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
            if (is_file($file)) {
                include_once $file;
                break;
            }
        }
    }
    
    public static function addAutoloadPath($path)
    {
        array_push(self::$autoloadPathArray, $path);
    }
}
