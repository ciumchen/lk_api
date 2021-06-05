<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class getIntegralController extends Controller
{
    //获取用户排队订单记录
    public function getUserIntegral(Request $request){
        $uid = $request->input('uid');
        //待添加积分总数
        $data['countJf'] = Order::where('uid',$uid)->where('status',2)->where('line_up',1)->sum('to_be_added_integral');
        $oneOrder = Order::where('uid',$uid)->where('status',2)->where('line_up',1)->first();

        $orderData = Order::where('uid',$uid)->where('status',2)->where('line_up',1)->get()->append(['updated_date'])->toArray();
//dd($orderData);
        foreach ($orderData as $k=>$v){
            $data['integralJl'][$k]['id'] = $v['id']-($oneOrder->id-1);
            $data['integralJl'][$k]['name'] = $v['name'];
            $data['integralJl'][$k]['to_be_added_integral'] = $v['to_be_added_integral'];
            $data['integralJl'][$k]['updated_date'] = $v['updated_date'];

        }

        return response()->json(['code'=>1, 'msg'=>'获取成功', 'data' => $data]);

    }



}
