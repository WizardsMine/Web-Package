<?php

namespace Wizard\Kernel\Http\Controller;

use Wizard\App\Controller;
use Wizard\App\Request;

class ControllerHandler
{
    /**
     * @var $controllerObject
     * Holds the class of the executed controller.
     */
    static $controllerObject;

    /**
     * @const string
     * The path where the controllers can be found.
     */
    const CONTROLLER_PATH = '\\App\\Http\\Controllers\\';

    /**
     * @param $route
     * @return array
     * @throws ControllerException
     *
     * Checks if the controller is valid and if it exists.
     * Returning an array with the file, class and method.
     */
    public function getController($route)
    {
        if (!is_array($route)) {
            throw new ControllerException('Route is not an array');
        }

        $location = $this->getControllerLocation($route['controller']);

        if (!class_exists(str_replace('/', '\\', self::CONTROLLER_PATH . $location['class']))) {
            throw new ControllerException('Controller class not found');
        }
        return $location;
    }

    /**
     * @param array $location
     * @param Request $request
     * @return mixed
     * @throws ControllerException
     *
     * Execute controller and checks if the controller class extends Controller.
     * Returning what the controller method returned and throws an error when
     * the controller method didn't return anything or null.
     */
    public function executeController(array $location, Request $request)
    {
        $class = str_replace('/', '\\', self::CONTROLLER_PATH.$location['class']);

        $controller_object = new $class();

        if (!$controller_object instanceof Controller) {
            throw new ControllerException($location['class']. " doesn't extend the Controller class");
        }
        if (!method_exists($controller_object, $location['function'])) {
            throw new ControllerException("Controller method doesn't exist");
        }

        $controller = call_user_func(array($controller_object, $location['function']), $request);

        if ($controller === null) {
            throw new ControllerException('Controller method didnt return');
        }
        self::$controllerObject = $controller_object;

        return $controller;
    }

    /**
     * @param string $controller
     * @return array
     * @throws ControllerException
     *
     * Split the controller string up to the path of the controller
     * method that needs to be executed.
     * Returning an array with file, class and function keys.
     */
    private function getControllerLocation(string $controller)
    {
        $exploded = explode('@', $controller);

        $numbers = count($exploded);

        if ($numbers == 2){
            return [
                'file' => $exploded[0],
                'class' => $exploded[0],
                'function' => $exploded[1]
            ];
        } else {
            throw new ControllerException('Invalid controller syntax');
        }
    }
}
