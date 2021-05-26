<?php

namespace Bmapi\conf;

use Bmapi\interfaces\ConfigInterface;

/**
 * 斑马力方 接口配置文档
 * Class Config
 *
 * @package App\Libs\Bmapi\conf
 */
class Config implements ConfigInterface
{

    const APP_KEY      = '10002911';

    const APP_SECRET   = 'oBfoIUjgyTREH5c70qeAueUXgAoZT0AW';

    const ACCESS_TOKEN = '2dd520ba581a4db5a3fcbd074e19d618';

    public function getAppKey()
    {
        return self::APP_KEY;
    }

    public function getAccessToken()
    {
        return self::ACCESS_TOKEN;
    }

    public function getAppSecret()
    {
        return self::APP_SECRET;
    }
}
