<?php

namespace Wizard\Kernel\Http\Routing;

use Wizard\Kernel\App;
use Wizard\Modules\Database\Model;

class RouteHandler
{

    /**
     * @var string
     * Holds the incoming uri.
     */
    private $uri;

    /**
     * @var string
     * Holds the request method.
     */
    private $method;

    /**
     * @var array
     * Holds the array that the routes.php file returned.
     */
    private $routes;

    /**
     * @var array
     * This stores all information about the matching route
     * eg route, controller, middleware, assets.
     */
    public $matching_route = array();


    /**
     * @param string|null $uri
     * @param string $method
     *
     * This is where the routing process begins
     * it first checks if there is an uri and method.
     * After that it will get the route file
     */
    public function routeValidator(string $uri = null, string $method = 'GET')
    {
        if ($uri === null) {
            $this->uri = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['PHP_SELF'], '/index.php'));
        } else {
            $this->uri = $uri;
        }
        $this->method = $method;

        try {
            if ($this->getRouteFile() === false) {
                throw new RouteException('The routing file didnt return an array');
            }
            $this->scanRoutes($this->routes);
        } catch (RouteException $e) {
            $e->showErrorPage();
        }
    }

    /**
     * @param $routes
     * @throws RouteException
     * @return void
     */
    private function scanRoutes(array $routes)
    {
        foreach ($routes as $id => $route_params) {
            if ($this->isGroup($route_params)) {
                if ($this->groupHasRoute($route_params)) {
                    return;
                }
            } elseif (is_string($id)) {
                $method = $route_params['method'] ?? 'GET';
                if ($this->checkRoute($id, $method)) {
                    $this->setMatchingRoute($id, $route_params, $method);
                    return;
                }
            }
        }
        throw new RouteException("No route found error 404", "Add a page");
    }

    /**
     * @param array $route_params
     * @return null|string
     * @throws RouteException
     *
     * Checks an array if it contains one of these keys: page, controller or text.
     * Only one of them is allowed and it will throw exceptions if there is more then one
     * or if there are none.
     * Returning the type (page, controller, text) that is found.
     */
    private function validateRouteArray(array $route_params)
    {
        $type = null;
        if (array_key_exists('page', $route_params)) {
            $type = 'page';
        }
        if (array_key_exists('controller', $route_params)) {
            if ($type === null) {
                $type = 'controller';
            } else {
                throw new RouteException('Route array can only contain one of these keys: page, controller or text');
            }
        }
        if (array_key_exists('text', $route_params)) {
            if ($type === null) {
                $type = 'text';
            } else {
                throw new RouteException('Route array can only contain one of these keys: page, controller or text');
            }
        }
        if ($type === null) {
            throw new RouteException('Route array must contain one of these keys: page, controller or text');
        }
        switch ($type) {
            case 'controller':
                $controller = $route_params['controller'];
                if (!is_string($controller) || !$this->isController($controller)) {
                    throw new RouteException('Invalid controller syntax');
                }
                break;
            case 'page':
                if (!is_string($route_params['page'])) {
                    throw new RouteException('Value of page must be a string');
                }
                break;
            case 'text':
                if (!is_string($route_params['text'])) {
                    throw new RouteException('Value of text must be a string');
                }
                break;
        }
        return $type;
    }

    /**
     * @return bool
     * @throws RouteException
     *
     * Checks if the /App/Http/routes.php exist and if it exists it will be put
     * in the class property routes.
     * Returning true if routes.php is an array and false otherwise.
     */
    private function getRouteFile()
    {
        $path = App::$root. '/App/Http/routes.php';
        if (!file_exists($path)) {
            throw new RouteException('App/Http/routes.php not found', 'Make sure you have the routes.php under the App/Http directory');
        }
        $this->routes = require $path;
        if (is_array($this->routes)) {
            return true;
        }
        return false;
    }

    /**
     * @param $route
     * @param $method
     * @return bool
     *
     * Check if the route has parameters and after that it will check if the uri
     * matches with the route and the request method.
     */
    private function checkRoute($route, $method)
    {
        if (is_string($route) && is_string($method) && $this->method == $method ) {

            $param = $this->routeHasParam($route);
            if ($param === false) {
                if ($this->uri == $route) {
                    return true;
                }
            } else {
                $route = str_replace('/', '\/', $route);

                $route = substr_replace($route, '(', 0, 0);

                $positions = array();
                $loop = 0;

                for ($i = 0; $loop < count($param[0]); $i++) {
                    if ($i > 0) {
                        $positions[$i]['first'] = strpos($route, '{', $positions[$i - 1]['second']);
                    } else {
                        $positions[$i]['first'] = strpos($route, '{');
                    }
                    $positions[$i]['second'] = strpos($route, '}', $positions[$i]['first']);
                    $loop++;
                }

                foreach ($positions as $id => $position) {
                    $route = substr_replace($route, ')', $position['first'] + ($id * 2), 0);
                    $route = substr_replace($route, '(', $position['second'] + 2 + ($id * 2), 0);
                }

                $route = substr_replace($route, ')', strlen($route), 0);

                $route = preg_replace('/(\{)[\w]+(\})/', '[\w]+', $route);

                $route = substr_replace($route, '~', 0, 0);
                $route = substr_replace($route, '~', strlen($route), 0);

                if (preg_match($route, $this->uri, $match, PREG_OFFSET_CAPTURE)) {

                    $param_names = array();
                    foreach ($param[0] as $value) {
                        $param_names[] = str_replace('}', '', str_replace('{', '', $value));
                    }

                    $start_pos = 0;

                    foreach ($match as $id => $match_part) {
                        if ($id === 0 || count($match) === $id+1) {
                            continue;
                        }
                        $length = $match[$id+1][1] - (strlen($match_part[0]) + $start_pos);

                        $value = substr($this->uri, strlen($match_part[0]) + $start_pos, $length);

                        $this->matching_route['params'][$param_names[$id-1]] = $value;

                        $start_pos = $match[$id+1][1];
                    }
                    return true;
                }
            }
        }
        return false;
    }

    public function routeHasParam($route)
    {
        $regex = preg_match_all('/(\{)[\w]+(\})/', $route, $matches);
        if ($regex >= 1) {
            return $matches;
        }
        return false;
    }

    /**
     * @param $value
     * @return bool
     *
     * Checks if there is an @ symbol in a string to check if its
     * a valid controller string.
     */
    private function isController(string $value)
    {
        if (stripos($value, '@')) {
            return true;
        }
        return false;
    }

    /**
     * @param $value
     * @return bool
     * @throws RouteException
     *
     * Checks if an value is a valid group by checking if its an array with
     * the group key.
     */
    private function isGroup($value)
    {
        if (is_array($value)) {
            if (array_key_exists('group', $value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $group
     * @return array
     * @throws RouteException
     *
     * Checks if the group array has the route with specific request type that
     * is matching with the incomming request.
     */
    private function groupHasRoute(array $group)
    {
        if (!is_array($group) || !array_key_exists('routes', $group) || !is_array($group['routes'])) {
            return false;
        }
        $routes = $group['routes'];
        $request_method = $group['method'] ?? 'GET';
        foreach ($routes as $route => $route_params) {
            if (!is_string($route)) {
                continue;
            }
            if (is_array($route_params) && array_key_exists('method', $route_params) && is_string($route_params['method'])) {
                $request_method = $route_params['method'];
            }
            if (!$this->checkRoute($route, $request_method)) {
                continue;
            }
            $this->setMatchingRoute($route, $route_params, $request_method, $group['middleware'] ?? null, $group['assets'] ?? null, $group['models'] ?? null);
            return true;
        }
        return false;
    }

    /**
     * @param $route
     * @param $route_params
     * @param string $request_method
     * @param string|null $middleware
     * @param null $assets
     * @param null $models
     * @throws RouteException
     *
     * Checks if the route and the route parameters are valid and adding them to the matching_route
     * property to be used further in the http process.
     */
    private function setMatchingRoute($route, $route_params, string $request_method, string $middleware = null, $assets = null, $models = null)
    {
        if (is_array($route_params)) {
            $type = $this->validateRouteArray($route_params);
            switch ($type) {
                case 'controller':
                    $this->matching_route['controller'] = $route_params['controller'];
                    break;
                case 'page':
                    $this->matching_route['page'] = $route_params['page'];
                    break;
                case 'text':
                    $this->matching_route['text'] = $route_params['text'];
                    break;
            }
            $this->matching_route['type'] = $type;
        } elseif (is_string($route_params)) {
            if ($this->isController($route_params)) {
                $this->matching_route['controller'] = $route_params;
                $this->matching_route['type'] = 'controller';
            } else {
                $this->matching_route['text'] = $route_params;
                $this->matching_route['type'] = 'text';
            }
        } else {
            throw new RouteException('Value of route must be an array or string');
        }
        $this->matching_route['params'] = $this->matching_route['params'] ?? array();
        $this->matching_route['route'] = $route;
        $this->matching_route['method'] = $request_method;
        $this->matching_route['middleware'] = $route_params['middleware'] ?? $middleware ?? null;
        $this->matching_route['assets'] = $route_params['assets'] ?? $assets ?? null;

        if ($models !== null && is_array($route_params) && array_key_exists('models', $route_params)) {
            if (!is_array($models) || !is_array($route_params['models'])) {
                throw new RouteException('Value of models key in route must be an array');
            }
            $models = array_merge($models, $route_params['models']);
        }

        $models = $models ?? $route_params['models'] ?? array();

        foreach ($models as $key => $model) {
            if (!is_string($key) || !is_string($model)) {
                throw new RouteException('Every key and value in the models array must be a string');
            }
            if (!class_exists($model)) {
                throw new RouteException($model . ' class not found');
            }
            $model_classes[$key] = new $model();
            if (!$model_classes[$key] instanceof Model) {
                throw new RouteException($model . ' is not instance of Wizard\Src\Modules\Database\Model');
            }
        }
        $this->matching_route['models'] = $model_classes ?? array();
    }
}

