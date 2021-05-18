<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\AssetsLogs;
use App\Models\FreezeLogs;
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

        return response()->json(['code'=>1, 'msg'=>'获取成功', 'data' => $data]);
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

        return response()->json(['code'=>1, 'msg'=>'获取成功', 'data' => $data]);

    }

    //获取用户的冻结资产usdt的记录
    public function getUserFreeze(Request $request){
        $uid = $request->input('uid');
        $page = $request->input('page');
        $pageSize = $request->input('pageSize',10);
        $data = (new FreezeLogs())
            ->where("uid", $uid)
            ->where('assets_type_id', 3)
            ->orderBy('id', 'desc')
            ->latest('id')
            ->forPage($page, $pageSize)
            ->get(['operate_type','amount','updated_at']);

        return response()->json(['code'=>1, 'msg'=>'获取成功', 'data' => $data]);

    }

    //获取用户分享积分
    public function getUserAssetsFxJf(Request $request){
        $uid = $request->input('uid');
        $page = $request->input('page');
        $pageSize = $request->input('pageSize',10);
        $data['amount_count'] = AssetsLogs::where('uid',$uid)->sum('amount');
        $data['jls'] = (new AssetsLogs())
            ->where("uid", $uid)
            ->where('assets_name', 'encourage')
            ->orwhere('assets_type_id','share_b_rebate')
            ->orwhere('operate_type','invite_rebate')
            ->orderBy('id', 'desc')
            ->latest('id')
            ->forPage($page, $pageSize)
            ->get(['operate_type','amount','updated_at']);

        return response()->json(['code'=>1, 'msg'=>'获取成功', 'data' => $data]);

    }

    //用户的公益贡献接口
    public function getUoserGYGX(Request $request){
        $uid = $request->input('uid');
        $count = Order::where('uid',$uid)->where('status',2)->sum('profit_price')*0.04;

        return response()->json(['code'=>1, 'msg'=>'获取成功', 'data' => array('num',$count)]);

    }

}





