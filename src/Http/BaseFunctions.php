<?php

namespace Wizard\Src\Http;

trait BaseFunctions
{
    public function checkAndIncludeFile($root, $path)
    {
        $file_path = str_replace('/', '\\', $path);
        $path = $root.$file_path.'.php';
        if (file_exists($path)) {
            require_once $path;
            return true;
        }
        return false;
    }

    public function checkClassExist($path)
    {
        $class_path = str_replace('/', '\\', $path);
        if (class_exists($class_path, false)) {
            return true;
        }
        return false;
    }

    /**
     * @param array $multidimensional_array
     * @return array
     *
     * Loops through an array that is returned by the controller and
     * convert every array found to one array to be used as variables
     * for the template.
     */
    public function getParams(array $multidimensional_array)
    {
        $params = array();
        foreach ($multidimensional_array as $value) {
            if (is_array($value)) {
                foreach ($value as $key => $param) {
                    $params[$key] = $param;
                }
            }
        }
        return $params;
    }
}