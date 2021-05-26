<?php

namespace Bmapi\interfaces;

/**
 * 斑马力方 配置接口
 * Interface ConfigInterface
 *
 * @package App\Libs\Bmapi\interfaces
 */
interface ConfigInterface
{

    /**
     * 获取APP_KEY
     *
     * @return string
     */
    public function getAppKey();

    /**
     * 获取APP_SECRET
     *
     * @return mixed
     */
    public function getAppSecret();

    /**
     * 获取ACCESS_TOKEN
     *
     * @return mixed
     */
    public function getAccessToken();
}
