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

}
