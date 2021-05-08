<?php

namespace App\Http\Controllers\api\Test;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Libs\Yuntong\YuntongPay;

class YuntongController extends Controller
{
    //
    public function index()
    {
        $aa = new YuntongPay();
        try {
            $bb = $aa
                ->setGoodsTitle('商品标题')
                ->setGoodsDesc('商品描述')
                ->setAmount(0.6)
                ->setOrderId('order_no')
                ->setNotifyUrl('return_url')
                ->setType('alipay')
                ->setMethod('qr')
                ->pay();
            dd($aa->pay());
            echo 'echegn';
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

    }

    public function pay()
    {

    }
}
