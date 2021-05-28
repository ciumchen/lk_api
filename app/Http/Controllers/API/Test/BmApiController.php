<?php

namespace App\Http\Controllers\api\Test;

use App\Http\Controllers\Controller;
use Bmapi\Conf\Config;
use Bmapi\Core\Aes;
use Bmapi\Core\Sign;
use Bmapi\Core\ApiRequest;
use Illuminate\Http\Request;

/**
 * 斑马力方接口测试
 * Class BmApiController
 *
 * @package App\Http\Controllers\api\Test
 */
class BmApiController extends Controller
{

    /**
     * @throws \Exception
     */
    public function index()
    {
        $BmConfig = new Config();
        $res = $BmConfig->getAccessToken();
        $res1 = $BmConfig->getAppKey();
        $res2 = $BmConfig->getAppSecret();
        dump($res);
        dump($res1);
        dump($res2);
        $ApiRequest = new ApiRequest();
        dump($ApiRequest);
        $AES = new Aes();
        $key = '111111111111111111111111111';
        $str = $AES::encrypt('222', $key);
        dump($str);
        $str2 = $AES::decrypt($str, $key);
        dump($str2);
        $Sign = Sign::generateSign(['123', '123', '123', '123', '123', '123', '123'], 'sdasdd');
        dump($Sign);
        $check = (new Sign())->checkSign(['123',
                                          '123',
                                          '123',
                                          '123',
                                          '123',
                                          '123',
                                          '123',
                                          'sign' => '3119C5B37A09C5666615EF85912370262D33FD01',
        ], 'sdasdd');
        dump($check);
        dump(date_default_timezone_get());
        dump(date('Y-m-d H:m:i'));
    }

}
