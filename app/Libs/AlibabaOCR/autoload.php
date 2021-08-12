<?php

namespace AlibabaOCR;

class Autoloader
{

    public static function autoload($className)
    {
        $path = str_replace('\\', DIRECTORY_SEPARATOR, $className);
        $file = __DIR__.DIRECTORY_SEPARATOR.'ocr'.DIRECTORY_SEPARATOR.$path.'.php';
//        dd($file);
        if (file_exists($file)) {
            require_once $file;
        }
    }
}

spl_autoload_register("\AlibabaOCR\Autoloader::autoload");
