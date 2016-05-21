<?php

namespace Wizard\Modules\Config;

class Config
{

    public static function getFile($path)
    {
        $path = __DIR__.'/../../../../../../config/'.$path.'.php';
        //echo $path;
        if (!file_exists($path)) {
            return null;
        }
        $file = include $path;

        return $file;
    }


}