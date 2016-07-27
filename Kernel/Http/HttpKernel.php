<?php

namespace Wizard\Kernel\Http;

use Wizard\Kernel\App;
use Wizard\Http\Exception\ControllerException;
use Wizard\Http\Exception\MiddlewareException;
use Wizard\Kernel\Http\Controller\ControllerHandler;
use Wizard\Kernel\Http\Middleware\MiddlewareHandler;
use Wizard\Kernel\Http\Routing\RouteHandler;

class HttpKernel
{
    /**
     * @var array
     * The route that is loaded.
     */
    static $route;
    
    /**
     * @var RouteHandler
     * Holds the route handler class.
     */
    public $route_handler;

    /**
     * @var MiddlewareHandler
     * Holds the BaseMiddleware class
     */
    public $middleware_handler;

    /**
     * @var ControllerHandler
     * Holds the BaseController class
     */
    public $controller_handler;

    /**
     * HttpKernel constructor.
     * Set the base classes needed to process the whole http process
     * Http stands for hypertext transfer protocol if you didn't know yet :)
     */
    function __construct()
    {
        $this->route_handler = new RouteHandler();

        $this->middleware_handler = new MiddlewareHandler();

        $this->controller_handler = new ControllerHandler();
    }

    /**
     * @param string $uri
     * @param string $method
     *
     * This is where a new request handler begins and where it triggers the
     * other methods to further process the http request
     */
    public function handleRequest(string $uri, string $method = 'GET')
    {
        $route = $this->handleRouting($uri, $method);

        HttpKernel::$route = $route;

        $middleware_handler = $this->handleMiddleware($route);
        if ($middleware_handler[0] === false) {
            App::setResponse($middleware_handler['path'], $middleware_handler['parameters']);
            return;
        } elseif ($middleware_handler[0] == null) {
            return;
        }
        switch ($route['type']) {
            case 'controller':
                $controller_handler = $this->handleController($route);
                if ($controller_handler === true) {
                    return;
                } elseif (is_array($controller_handler)) {
                    $path = $controller_handler['path'];
                    $params = $controller_handler['params'];
                    App::setResponse($path, $params);
                    return;
                } else {
                    App::$Response = htmlentities($controller_handler);
                }
                break;
            case 'page':
                $path = App::$Root.'/Resources/Views/'.$route['page'].'.php';
                App::setResponse($path);
                return;
                break;
            case 'text':
                App::$Response = htmlentities($route['text']);
                break;
        }
    }

    /**
     * @param string $uri
     * @param string $method
     * @return array
     *
     * Handles the routing and checks if there is an matching route
     * and returns the route that need to be handled including
     * middleware, controller, assets.
     */
    private function handleRouting(string $uri, string $method = 'GET')
    {
        $routing = $this->route_handler;
        $routing->routeValidator($uri, $method);

        return $routing->matching_route;
    }

    /**
     * @param $route
     * @return bool
     *
     * Handles the middleware and if it passes middleware (middleware returning true)
     * it will return true.
     *
     * true = passed middleware
     * null = new request
     * false = load a page
     */
    private function handleMiddleware($route)
    {
        try {
            $middleware = $this->middleware_handler->getMiddleware($route);

            if ($middleware === false) {
                return [true];
            }
            $executed = $this->middleware_handler->executeMiddleware($middleware);
            
            $type = $this->middleware_handler->processHandler($executed);

            if ($type === true) {
                return [true];
            } elseif ($type == 'request') {
                $request = $executed['request'];
                if (!is_string($request)) {
                    throw new MiddlewareException('Request returned by middleware needs to be a string');
                }
                $request_method = $executed['method'] ?? 'GET';
                $this->handleRequest($request, $request_method);
                return [null];
            } elseif ($type == 'page') {
                $page = $executed['page'];
                if (!is_string($page)) {
                    throw new MiddlewareException('Page returned by middleware needs to be a string');
                }
                $path = App::$root.'/Resources/Views/'.$page.'.php';
            } else {
                $page = $executed['error'] ?? 'AccessDenied';
                $path = App::$root.'/Resources/ErrorPages/'. $page. '.php';
            }
            return [false, 'path' => $path, 'parameters' => $this->controller_handler->getParams($executed) ?? array()];
        } catch (MiddlewareException $e) {
            $e->showErrorPage();
        }
    }

    /**
     * @param $route
     * @return array|bool|mixed
     *
     * Handles the controller and returns true if there is new request
     */
    private function handleController($route)
    {
        try {
            $base_controller = $this->controller_handler;

            $location = $base_controller->getController($route);
            $controller = $base_controller->executeController($location);

            if (is_array($controller) && array_key_exists('request', $controller) && is_string($controller['request'])) {
                $this->handleRequest($controller['request'], $controller['method'] ?? 'GET');
                return true;
            } elseif (is_array($controller) && array_key_exists('page', $controller) && is_string($controller['page'])) {
                $params = $base_controller->getParams($controller);
                $path = App::$root.'/Resources/Views/'.$controller['page'].'.php';
                return ['path' => $path, 'params' => $params];
            } elseif (is_string($controller)) {
                return $controller;
            } else {
                throw new ControllerException('Invalid controller return syntax');
            }
        } catch (ControllerException $e) {
            $e->showErrorPage();
        }
    }
}











