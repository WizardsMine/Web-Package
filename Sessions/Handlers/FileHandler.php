<?php

namespace Wizard\Sessions\Handlers;

use Wizard\Sessions\BaseSessionHandler;
use Wizard\Sessions\SessionException;

class FileHandler extends BaseSessionHandler
{

    function __construct(array $config)
    {
        parent::__construct($config);

        $this->validateConfig($config);
    }

    public function loadData(string $key)
    {

    }

    public function setId(string $key)
    {

    }

    public function garbageCollection()
    {

    }

    public function updateTime(string $key)
    {

    }

    public function hasId()
    {

    }

    public function validateConfig($config)
    {
        if (!array_key_exists('file', $config) || !is_array($config['file'])) {
            throw new SessionException('Session file config not found or value is not an array');
        }
        if (!array_key_exists('save_path', $config['file'])) {
            throw new SessionException('Session file save path key not found');
        }
        if (!is_string($config['file']['save_path'])) {
            throw new SessionException('Session save path must be a string');
        }
        if (!file_exists($config['file']['save_path'])) {
            throw new SessionException('Session file save path doesnt exists');
        }
    }
}