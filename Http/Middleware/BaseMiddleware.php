<?php

namespace Wizard\Http\Middleware;

use Wizard\Exception\WizardRuntimeException;
use Wizard\App\Middleware;
use Wizard\Http\BaseFunctions;
use Wizard\Http\Exception\MiddlewareException;
use Wizard\Http\Interfaces\MiddlewareInterface;
use Wizard\Kernel\App;

class BaseMiddleware
{
    use BaseFunctions;

    /**
     * @var static
     * Holds the root of the project.
     */
    private $root;

    /**
     * @var array|mixed
     * The route array that is generated in the Routing class.
     */
    public $route;

    /**
     * @const string
     * The path from the project root to the middleware folder.
     */
    const MIDDLEWARE_PATH = '\\App\\Http\\Middleware\\';

    /**
     * BaseMiddleware constructor.
     * @throws WizardRuntimeException
     */
    function __construct()
    {
        $this->root = App::$Root;
    }

    /**
     * @param $route
     * @return bool|mixed
     * @throws MiddlewareException
     *
     * Checks if the route has middleware and checks if it exists.
     * If there is no middleware found it will return false.
     * When there is middleware but the destined class or file is not found it will
     * throw exceptions.
     * When everything is ok it will return the name of the middleware.
     */
    public function getMiddleware($route)
    {
        if (!is_array($route)) {
            throw new MiddlewareException('Incoming route is not an array');
        }

        $this->route = $route;

        if (!array_key_exists('middleware', $route) || $route['middleware'] === null) {
            return false;
        }
        $middleware = $route['middleware'];
        if (!is_string($middleware)) {
            throw new MiddlewareException('Value of middleware is not a string');
        }
        if (!$this->checkAndIncludeFile($this->root, self::MIDDLEWARE_PATH.$middleware)){
            throw new MiddlewareException('Middleware file not found');
        }
        if (!$this->checkClassExist(self::MIDDLEWARE_PATH.$middleware)) {
            throw new MiddlewareException('Middleware class not found');
        }
        return $middleware;
    }

    /**
     * @param $handler
     * @return bool|string
     * @throws MiddlewareException
     *
     * Checks what type the middleware handle method has returned.
     * When the handle method returned true it has passed the middleware
     * and may continue with the request handling.
     * Otherwise it will return the type of what needs to happen next.
     */
    public function processHandler($handler)
    {
        if (!is_array($handler)) {
            throw new MiddlewareException('Handler must return an array');
        }
        if (!is_bool(current($handler))) {
            throw new MiddlewareException('First value of the returned array must be an boolean');
        }
        if (current($handler)) {
            return true;
        }
        if (array_key_exists('error', $handler)) {
            return 'error';
        } elseif (array_key_exists('request', $handler)) {
            return 'request';
        } elseif (array_key_exists('page', $handler)) {
            return 'page';
        } else {
            return 'access_denied';
        }
    }

    /**
     * @param $middleware
     * @return mixed
     * @throws MiddlewareException
     *
     * This will execute the middleware that has been found.
     * Before it gets executed it will check if the class extends Middleware class
     * and implements the MiddlewareInterface interface and when that is all fine
     * the handle function will get executed and returned.
     */
    public function executeMiddleware($middleware)
    {
        $middlewareClass = str_replace('/', '\\', self::MIDDLEWARE_PATH.$middleware);

        $middleware_object = new $middlewareClass($this->root);

        if (!$middleware_object instanceof Middleware) {
            throw new MiddlewareException('Middleware object doesnt extend Middleware');
        }

        if (!$middleware_object instanceof MiddlewareInterface) {
            throw new MiddlewareException('Middleware object doesnt implements MiddlewareInterface');
        }

        if (!method_exists($middleware_object, 'handle')) {
            throw new MiddlewareException('Couldnt fine the handle function in the middleware class');
        }

        $handle = call_user_func(array($middleware_object, 'handle'));

        //print_r($handle);
        if ($handle === null) {
            throw new MiddlewareException('Middleware handler didnt return');
        }
        return $handle;
    }
}











