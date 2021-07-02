<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class getIntegralController extends Controller
{
    //获取用户排队订单记录
    public function getUserIntegral(Request $request){
        $uid = $request->input('uid');
        $role = $request->input('role');
        $role==''?$role=1:$role;

        //排队积分统计
        $data['countJf'] = Order::where('uid',$uid)->where('status',2)->where('line_up',1)->sum('to_be_added_integral');
        $data['integralJl'] = array();
//dd($data['countJf']);dd(111);
        $allOrderData = Order::where('status',2)->where('line_up',1)->get();
        if ($allOrderData!='[]'){
            foreach ($allOrderData->toArray() as $k=>$v){
                $pxData['uid'.$v['uid']][$k]['id'] = $v['id'];
                $pxData['uid'.$v['uid']][$k]['name'] = $v['name'];
                $pxData['uid'.$v['uid']][$k]['to_be_added_integral'] = $v['to_be_added_integral'];
                $pxData['uid'.$v['uid']][$k]['updated_at'] = $v['updated_at'];
                $pxData['uid'.$v['uid']][$k]['line_up_num'] = $k+1;

                $pxData['business_uid'.$v['business_uid']][$k]['id'] = $v['id'];
                $pxData['business_uid'.$v['business_uid']][$k]['name'] = $v['name'];
                $pxData['business_uid'.$v['business_uid']][$k]['to_be_added_integral'] = $v['profit_price'];
                $pxData['business_uid'.$v['business_uid']][$k]['updated_at'] = $v['updated_at'];
                $pxData['business_uid'.$v['business_uid']][$k]['line_up_num'] = $k+1;

            }

            if ($role == 1){
                $userOrder = Order::where('uid',$uid)->where('status',2)->where('line_up',1)->count();
                if ($userOrder){
                    foreach ($pxData['uid'.$uid] as $v){
                        $data['integralJl'][] = $v;
                    }
                    return response()->json(['code'=>1, 'msg'=>'获取成功', 'data' => $data]);
                }else{
                    return response()->json(['code'=>0, 'msg'=>'获取失败', 'data' => $data]);
                }

            }elseif ($role == 2){
                $userOrder = Order::where('business_uid',$uid)->where('status',2)->where('line_up',1)->count();
                if ($userOrder){
                    foreach ($pxData['business_uid'.$uid] as $v){
                        $data['integralJl'][] = $v;
                    }
                    return response()->json(['code'=>1, 'msg'=>'获取成功', 'data' => $data]);
                }else{
                    return response()->json(['code'=>0, 'msg'=>'获取失败', 'data' => $data]);
                }

            }else{
                return response()->json(['code'=>0, 'msg'=>'获取失败', 'data' => $data]);
            }

        }else{
            return response()->json(['code'=>0, 'msg'=>'获取失败', 'data' => $data]);
        }


    }



}
