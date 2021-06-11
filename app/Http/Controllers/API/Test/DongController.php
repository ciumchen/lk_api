<?php

namespace App\Http\Controllers\API\Test;

use App\Http\Controllers\Controller;
use App\Models\Order;
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
}
