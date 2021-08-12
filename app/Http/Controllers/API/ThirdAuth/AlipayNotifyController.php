<?php

namespace App\Http\Controllers\API\ThirdAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AlipayNotifyController extends Controller
{
    //支付宝授权回调
    public function authNotify(Request $request)
    {
        $data_all = $request->all();
        Log::debug('Alipay-$data_all', [json_encode($data_all)]);
        $post = $_POST;
        $get = $_GET;
        $json = file_get_contents("php://input");
        Log::debug('Alipay-$post', [json_encode($post)]);
        Log::debug('Alipay-$get', [json_encode($get)]);
        Log::debug('Alipay-$json', [json_encode($json)]);
    }
}
