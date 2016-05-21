<?php

namespace Wizard\Exception;

use Wizard\Kernel\App;

class WizardRuntimeException extends \Exception implements WizardExceptionInterface
{
    /**
     * @var string
     * The solution to solve the exception if specified.
     */
    public $solution;

    /**
     * @var string
     * The type of the exception
     */
    public $type;

    /**
     * WizardRuntimeException constructor.
     * @param string $message
     * @param string $solution
     * @param string $type
     * 
     * Sets the solution property and the type property
     */
    function __construct(string $message = '' , $solution = '', $type = 'WizardRuntimeException')
    {
        parent::__construct($message);

        $this->solution = $solution;
        $this->type = $type;
    }

    /**
     * Show the main error page
     */
    public function showErrorPage()
    {
        $path = App::$Root.'/Resources/ErrorPages/main.php';
        $parameters = [
            'message' => $this->getMessage(),
            'line' => $this->getLine(),
            'file' => $this->getFile(),
            'solution' => $this->solution,
            'class' => get_class($this),
            'type' => $this->type
        ];
        if (!file_exists($path)) {
            App::terminate('Something went horribly wrong');
        }
        App::$ResponsePath = $path;
        $content = App::loadResponseFile($path, $parameters);
        App::$Response = htmlentities($content);
//        ob_end_clean();
        App::send();
        App::terminate();
    }

    /**
     * @param \Throwable $exception
     * Same as the non static method in this class except this needs to have
     * an instance of the interface Throwable as one and only parameter.
     */
    public static function showStaticErrorPage(\Throwable $exception)
    {
        $path = App::$Root.'/Resources/ErrorPages/main.php';
        $parameters = [
            'message' => $exception->getMessage(),
            'line' => $exception->getLine(),
            'file' => $exception->getFile(),
            'solution' => $exception->solution ?? 'No solution specified',
            'class' => get_class($exception),
            'type' => $exception->type ?? 'Undefined type'
        ];
        if (!file_exists($path)) {
            App::terminate('Something went horribly wrong');
        }
        App::$ResponsePath = $path;
        $content = App::loadResponseFile($path, $parameters);
        App::$Response = htmlentities($content);
//        ob_end_clean();
        App::send();
        App::terminate();
    }
}