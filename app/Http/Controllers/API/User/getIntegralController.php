<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class getIntegralController extends Controller
{
    public function getUserIntegral(Request $request){
        $uid = $request->input('uid');
        $data['countJf'] = Order::where('status',2)->where('line_up',1)->sum('to_be_added_integral');

        return response()->json(['code'=>1, 'msg'=>'获取成功', 'data' => $data]);

    }
}
