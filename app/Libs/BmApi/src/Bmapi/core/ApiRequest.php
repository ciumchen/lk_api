<?php

namespace Bmapi\core;

use Bmapi\conf\Config;
use Bmapi\interfaces\ConfigInterface;

class ApiRequest
{

    /**
     * @var
     */
    protected $app_key;
    protected $app_secret;
    protected $access_token;
    /**
     * @var \Bmapi\conf\Config
     */
    protected $config;

    public function __construct(ConfigInterface $config = null)
    {
        if ($config == null) {
            $config = new Config();
        }
        $this->config = $config;
        $this->app_key = $config->getAppKey();
    }

}
