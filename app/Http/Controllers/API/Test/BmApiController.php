<?php

namespace App\Http\Controllers\api\Test;

use App\Http\Controllers\Controller;
use Bmapi\conf\Config;
use Illuminate\Http\Request;

/**
 * 斑马力方接口测试
 * Class BmApiController
 *
 * @package App\Http\Controllers\api\Test
 */
class BmApiController extends Controller
{

    public function index()
    {
        $BmConfig = new Config();
        $res = $BmConfig->getAccessToken();
        $res1 = $BmConfig->getAppKey();
        $res2 = $BmConfig->getAppSecret();
        dump($res);
        dump($res1);
        dump($res2);
        $ApiRequest = new \Bmapi\core\ApiRequest();
        dump($ApiRequest);
    }
}
