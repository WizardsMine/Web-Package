<?php

namespace Wizard\Src\Http\App;

class Request 
{
    public $route_parameters = array();

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