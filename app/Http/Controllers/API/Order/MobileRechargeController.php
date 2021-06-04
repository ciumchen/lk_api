<?php

namespace App\Http\Controllers\api\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MobileRechargeController extends Controller
{
    
    public function setOrder(Request $request)
    {
        $mobile = $request->input('mobile');
        $money = $request->input('money');
        
    }
}
