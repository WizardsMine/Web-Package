<?php

namespace Wizard\Kernel\Http\Middleware;

use Wizard\App\Middleware;
use Wizard\App\Request;

class MiddlewareHandler
{
    
    /**
     * @param $route
     * @return array
     *
     * Checks if the route has middleware and creates instances for each middleware that is found.
     */
    public function getMiddlewareInstances(array $route)
    {
        if (!array_key_exists('middleware', $route) || empty($route['middleware'])) {
            return array();
        }
        $middleware = $route['middleware'];
        $instances = array();
        foreach ($middleware as $item) {
            $instances[] = new $item();
        }
        return $instances;
    }

    /**
     * @param $middleware
     * @return mixed
     * @throws MiddlewareException
     *
     * This will execute the middleware that has been found.
     * The handle function will get executed and returned and an error will be thrown 
     * if the handle function didn't return.
     */
    public function executeMiddleware(Middleware $middleware, Request $request)
    {
        if (!method_exists($middleware, 'handle')) {
            throw new MiddlewareException('Couldnt fine the handle function in the middleware class');
        }
        $handle = call_user_func(array($middleware, 'handle'), $request);

        if ($handle === null) {
            throw new MiddlewareException('Middleware handler didnt return');
        }
        return $handle;
    }
}
