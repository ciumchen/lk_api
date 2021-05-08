<?php
/**
 * 云通
 */

namespace App\Libs\Yuntong;
/**
 * @TODO:把账号抽象一个类，然后定义接口,pay类接收账户接口
 * 把账号抽象一个类，然后定义接口,pay类接收账户接口
 */
class Config
{

    const APP_ID = 'app_d5d748e6d3e04dfe8a';
    const APP_SECRET = 'sk_live_8B06DB8F29F94B20B50F9DCFA4DF904A';
    const API_DOMIAN = 'http://api.foceplay.com/';

    protected string $appID;
    protected string $appSecret;

    public function __construct($appID = '', $appSecret = '')
    {
        $this->appID = $appID ?: self::APP_ID;
        $this->appSecret = $appSecret ?: self::APP_SECRET;

    }

}
