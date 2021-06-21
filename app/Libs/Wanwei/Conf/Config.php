<?php

namespace Wanwei\Conf;

/**
 * Description:万维易源接口配置
 *
 * Class Config
 *
 * @package Wanwei\Conf
 * @author  lidong<947714443@qq.com>
 * @date    2021/6/21 0021
 */
class Config
{
    
    const APP_ID   = '';
    
    const APP_SIGN = '';
    
    const API_URL  = 'https://route.showapi.com';
    
    public function getAppId()
    {
        return self::APP_ID;
    }
    
    public function getAppSign()
    {
        return self::APP_SIGN;
    }
    
    public function getApiUrl()
    {
        return self::API_URL;
    }
}
