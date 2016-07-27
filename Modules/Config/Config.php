<?php

namespace Wizard\Modules\Config;

use Wizard\Kernel\App;

class Config
{

    public static function getFile($path)
    {
        $path = App::$root.'/Config/'.$path.'.php';
        //echo $path;
        if (!file_exists($path)) {
            return null;
        }
        $file = include $path;

        return $file;
    }


}