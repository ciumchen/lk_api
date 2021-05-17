<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\AssetsLogs;
use App\Models\Order;
use Illuminate\Http\Request;

class RecordsOfConsumptionController extends Controller
{
    //获取用户的消费记录
    public function getUserOrderJl(Request $request){
        $uid = $request->input('uid');
        $page = $request->input('page');
        $pageSize = $request->input('pageSize',10);
        $data = (new Order())
            ->where("uid", $uid)
            ->where('status', 2)
            ->orderBy('id', 'desc')
            ->latest('id')
            ->forPage($page, $pageSize)
            ->get(['id','price','name','updated_at']);

        return response()->json(['code'=>0, 'msg'=>'获取成功', 'data' => $data]);
    }


    //获取用户的资产usdt的记录
    public function getUserAssets(Request $request){
        $uid = $request->input('uid');
        $page = $request->input('page');
        $pageSize = $request->input('pageSize',10);
        $data = (new AssetsLogs())
            ->where("uid", $uid)
            ->where('assets_type_id', 3)
            ->orderBy('id', 'desc')
            ->latest('id')
            ->forPage($page, $pageSize)
            ->get(['operate_type','amount','updated_at']);

        return response()->json(['code'=>0, 'msg'=>'获取成功', 'data' => $data]);





    }

}
