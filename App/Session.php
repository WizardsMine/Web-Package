<?php

namespace Wizard\App;

class Session
{
    /**
     * @var array
     *
     * The global session data array.
     */
    static $data = array();

    /**
     * @var array
     *
     * The current flash data available. This data wont be available at the next request.
     */
    static $flash = array();

    /**
     * @var array
     *
     * The data that will be stored in the database and will be available for the next request.
     */
    static $next_flash = array();

    /**
     * @param string $key
     * @return mixed|null
     *
     * Get data from the current session.
     */
    public static function get(string $key)
    {
        return self::$data[$key] ?? null;
    }

    /**
     * @param string $key
     * @param $value
     *
     * Put data to the current session
     */
    public static function put(string $key, $value)
    {
        self::$data[$key] = $value;
    }

    /**
     * @param string $key
     *
     * Remove data from the current session.
     */
    public static function unset(string $key)
    {
        unset(self::$data[$key]);
    }

    /**
     * @param string $key
     * @return mixed|null
     *
     * Get data for the previous request.
     */
    public static function getFlash(string $key)
    {
        return self::$flash[$key] ?? null;
    }

    /**
     * @param string $key
     * @param $value
     *
     * Put data for the next request.
     */
    public static function putFlash(string $key, $value)
    {
        self::$next_flash[$key] = $value;
    }
}
