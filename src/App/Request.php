<?php

namespace Wizard\Src\App;

class Request 
{
    public $route_parameters = array();

    public $POST;

    public $GET;

    public $models;

    function __construct()
    {
        $this->POST = $_POST;
        $this->GET = $_GET;
    }

    /**
     * @param string $value
     * @return mixed|null
     *
     * Get a param of the route params array.
     */
    public function getParam(string $value)
    {
        return $this->route_parameters[$value] ?? null;
    }
    
}