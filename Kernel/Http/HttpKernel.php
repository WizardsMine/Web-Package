<?php

namespace Wizard\Kernel\Http;

use Wizard\App\Request;
use Wizard\Kernel\App;
use Wizard\Kernel\Http\Controller\ControllerException;
use Wizard\Kernel\Http\Controller\ControllerHandler;
use Wizard\Kernel\Http\Middleware\MiddlewareException;
use Wizard\Kernel\Http\Middleware\MiddlewareHandler;
use Wizard\Kernel\Http\Routing\RouteException;
use Wizard\Kernel\Http\Routing\RouteHandler;

class HttpKernel
{
    /**
     * @var array
     * The route that is loaded.
     */
    static $route;

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

        $this->handleMiddleware($route);

        switch ($route['type']) {
            case 'controller':
                $this->handleController($route);
                break;
            case 'page':
                $path = App::$root.'/Resources/Views/'.$route['page'].'.php';
                App::setResponse($path);
                return;
                break;
            case 'text':
                App::$response = htmlentities($route['text']);
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
        try {
            $routing = new RouteHandler();
            $routing->routeValidator($uri, $method);
    
            return $routing->matching_route;
        } catch (RouteException $e) {
            $e->showErrorPage();
        }
    }

    /**
     * @param $route
     * @return bool
     *
     * Handles the middleware and returns nothing because if the middleware returns a page or new request
     * this request will be terminated.
     */
    private function handleMiddleware($route)
    {
        try {
            $middleware_handler = new MiddlewareHandler();
            $middleware_instances = $middleware_handler->getMiddlewareInstances($route);

            $request = $this->prepareRequest();

            foreach ($middleware_instances as $middleware_instance) {
                $handle = $middleware_handler->executeMiddleware($middleware_instance, $request);

                if ($handle === true) {
                    continue;
                } elseif (is_array($handle)) {
                    $this->processHandler($handle, 'middleware');
                } else {
                    throw new MiddlewareException('Invalid return value from middleware');
                }
            }
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
            $controller_handler = new ControllerHandler();

            $location = $controller_handler->getController($route);
            $controller = $controller_handler->executeController($location, $this->prepareRequest());
            $this->processHandler($controller, 'controller');

        } catch (ControllerException $e) {
            $e->showErrorPage();
        }
    }

    /**
     * @param $handler
     * @param $type
     * @return bool|string
     * @throws HttpKernelException
     *
     * Checks what type the middleware/controller method has returned.
     * The only options are page request and text.
     */
    private function processHandler(array $handler, string $type)
    {
        if (array_key_exists('type', $handler)) {
            switch ($handler['type']) {
                case 'page':
                    if (!array_key_exists('page', $handler)) {
                        throw new HttpKernelException($type .' handler return type is page but page key is not found');
                    }
                    if (!is_string($handler['page'])) {
                        throw new HttpKernelException('Page returned by '. $type .' handler needs to be a string');
                    }
                    $path = App::$root.'/Resources/Views/'.$handler['page'].'.php';
                    App::setResponse($path, $handler['params'] ?? array());
                    App::send();
                    App::terminate();
                    break;

                case 'request':
                    if (!array_key_exists('uri', $handler)) {
                        throw new HttpKernelException($type .' handler return type is request but uri key is not found');
                    }
                    if (!is_string($handler['uri'])) {
                        throw new HttpKernelException('Uri returned by '. $type .' handler needs to be a string');
                    }
                    App::sendRequest($handler['uri']);
                    App::terminate();
                    break;
                
                case 'text':
                    if (!array_key_exists('text', $handler)) {
                        throw new HttpKernelException($type .' handler return type is text but text key is not found');
                    }
                    if (!is_string($handler['text'])) {
                        throw new HttpKernelException('Text returned by '. $type .' handler needs to be a string');
                    }
                    App::$response = htmlentities($handler['text']);
                    App::send();
                    App::terminate();
                    break;
            }
        }
        throw new HttpKernelException('Return value from '. $type .' handler is incorrect');
    }

    /**
     * @return Request
     * Prepares the request that is send to the middleware handle method.
     */
    private function prepareRequest()
    {
        $request = new Request();
        $request->route_parameters = HttpKernel::$route['params'];

        $request->models = HttpKernel::$route['models'];

        return $request;
    }
}
