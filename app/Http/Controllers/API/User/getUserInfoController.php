<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class getUserInfoController extends Controller
{

    //获取用户积分和lk
    public function getUserLkIntegral(Request $request){
        $shop_uid = $request->input('shop_uid');
        $userData = Users::where('shop_uid',$shop_uid)->first();
//        dd($userData);
        if ($userData!=null){
            $data['id']= $userData->id;
            $data['id']= $userData->id;



            return response()->json(['code' => 1, 'msg' => $userData->toArray()]);
        }else{
            return response()->json(['code' => 0, 'msg' => '该帐号没有注册来客app']);
        }
    }

    //获取用户消费积分lk百分百比
    public function getUserIntegralbfb(Request $request){
        $uid = $request->input('uid');
        $userInfo = Users::where('id',$uid)->first();
        if ($userInfo){
            $data['sx_integral'] = round(300-($userInfo->integral-$userInfo->lk*300),2);
            $data['sx_100'] = (round(($userInfo->integral-$userInfo->lk*300)/300*100,1)).'%';

            return response()->json(['code' => 0, 'msg' => $data]);
        }else{
            return response()->json(['code' => 0, 'msg' => '该帐号没有注册来客app']);
        }
    }

}
