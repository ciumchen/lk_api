<?php

namespace App\Http\Controllers\API\Test;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DongController extends Controller
{
    //
    public function orderTest(Request $request)
    {
        $order_id = $request->input('order_id');
        $Order = new Order();
        $orderInfo = $Order->find($order_id);
        dump($orderInfo->mobile);
    }
    
    public function carbon()
    {
        echo Carbon::now();                                                      // 获取当前时间
        echo '<br>';
        echo Carbon::now('Arctic/Longyearbyen');                                 //获取指定时区的时间
        echo '<br>';
        echo Carbon::now(new \DateTimeZone('Europe/London'));                    //获取指定时区的时间
        echo '<br>';
        echo Carbon::today();                                                    //获取今天时间 时分秒是 00-00-00
        echo '<br>';
        echo Carbon::tomorrow('Europe/London');                                  // 获取明天的时间
        echo '<br>';
        echo Carbon::yesterday();                                                // 获取昨天的时间
        echo '<br>';
        echo Carbon::now()->timestamp;                                           // 获取当前的时间戳
        echo '<br>';
        //以上结果输出的是一个Carbon 类型的日期时间对象
        $res = Carbon::now();
        echo $res;
    }
}
