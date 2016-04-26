<?php

namespace Wizard\Src\Kernel;

use Wizard\Src\Exception\WizardRuntimeException;
use Wizard\Src\Http\HttpKernel;
use Wizard\Src\Modules\Config\Config;
use Wizard\Src\Modules\Database\Database;
use Wizard\Src\Modules\Exception\DatabaseException;
use Wizard\Src\Modules\Exception\SessionException;
use Wizard\Src\Modules\Sessions\WizardSessionHandler;
use Wizard\Src\Templating\TemplateLoader;

class App
{
    /**
     * @var static mixed
     * Keeps the response.
     */
    static $Response;

    /**
     * @var static mixed
     * Holds the path to the Response that is send to the user.
     * Null if it is plain html.
     */
    static $ResponsePath = null;

    /**
     * @var static mixed
     * Everything that gets echoed while running the application
     */
    static $Echoed = array();

    /**
     * @var static string
     * The root directory of this project
     */
    static $Root;

    /**
     * @var static array
     * Keeps all the debug messages to output at the end of the response
     */
    static $Debug = array();

    /**
     * @var string
     * The requested uri by the user
     */
    private $uri;

    /**
     * @var string
     * The request method
     */
    private $method;

    /**
     * App constructor.
     * @param string $uri
     * @param string $method
     *
     * This is the base of the app, triggering the database connection
     * and starting session handler.
     */
    public function __construct(string $uri, string $method)
    {
        $this->uri = $uri;
        $this->method = $method;

        try {
            $this->DBConnect();
        } catch (DatabaseException $e) {
            $e->showErrorPage();
        } catch (\PDOException $e) {
            WizardRuntimeException::showStaticErrorPage($e);
        }
        $this->startSession();
    }

    /**
     * @param HttpKernel $Kernel
     *
     * Here the app get forged and handled.
     * Also catching all errors and exceptions
     */
    public function make(HttpKernel $Kernel)
    {
        try {
//            ob_start();
            $Kernel->handleRequest($this->uri, $this->method);
        } catch (WizardRuntimeException $e) {
            $e->showErrorPage();
        } catch (\PDOException $e) {
            WizardRuntimeException::showStaticErrorPage($e);
        } catch (\Error $e) {
            WizardRuntimeException::showStaticErrorPage($e);
        } catch (\Throwable $e) {
            WizardRuntimeException::showStaticErrorPage($e);
        }
    }

    /**
     * @param string $absolute_path
     * @param array $parameters
     * @return bool
     * @throws WizardRuntimeException
     *
     * Sets the response that is soon to be send to the user.
     * Also collecting the output that is echo'ed before this method
     */
    public static function setResponse(string $absolute_path, array $parameters = array())
    {
        $path = str_replace('/', '\\', $absolute_path);
        if (file_exists($path) === false) {
            throw new WizardRuntimeException($path. ' response file not found');
        }
        self::$ResponsePath = $path;

        $loader = new TemplateLoader(App::$Root.'/Storage/Cache/template.php');
        $content = $loader->loadTemplate($path);
        
        $asset_name = HttpKernel::$Route['assets'];
        $params = $loader->addAssets($content, $asset_name);
        $parameters['links'] = $params['links'];
        $parameters['images'] = $params['images'];

        $cache = self::$Root.'/Storage/Cache/template.php';
        App::$Response = self::loadResponseFile($cache, $parameters);
        
        return true;
    }

    /**
     * @param string $path
     * @param array $parameters
     * @return mixed
     *
     * Load the App::$ResponsePath and return its contents.
     * Make sure that the path exists before using this method
     */
    public static function loadResponseFile(string $path, array $parameters)
    {
        ob_start();
        extract($parameters, EXTR_SKIP);

        require $path;

        $content = ob_get_clean();
        return $content;
    }

    /**
     * Sending the fresh forged app to the users browser
     */
    public static function send()
    {
        echo html_entity_decode(self::$Response);
    }

    /**
     * @param $message
     * Closing the app and shutting down database connections and sessions.
     */
    public static function terminate($message = '')
    {
        die($message);
    }

    /**
     * Starting the session with the custom made session handler
     */
    private function startSession()
    {
        try {
            $handler = new WizardSessionHandler();
            session_set_save_handler($handler, true);
            session_start();
        } catch (SessionException $e) {
            $e->showErrorPage();
        }
    }

    /**
     * @throws DatabaseException
     * Checking the database config file and when the connect_on_load key is set
     * to true it will automatically connect to the database.
     */
    private function DBConnect()
    {
        $config = Config::getFile('database');
        if ($config === null) {
            throw new DatabaseException("Couldn't find database config file");
        }
        if (!is_array($config)) {
            throw new DatabaseException("Database config file didn't return an array");
        }
        if ($config['connect_on_load'] ?? false === true) {
            $database = new Database();
            Database::$DBConnection = $database->connect();
        }
    }
}
