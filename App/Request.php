<?php

namespace Wizard\App;

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

    /**
     * @return bool
     * Continues the request.
     */
    public function continue()
    {
        return true;
    }

    public function request(string $uri)
    {
        return array('type' => 'request', 'uri' => $uri);
    }

    public function page(string $page, array $params = array())
    {
        return array('type' => 'page', 'page' => $page, 'params' => $params);
    }

    public function text(string $text)
    {
        return array('type' => 'text', 'text' => $text);
    }
    
}