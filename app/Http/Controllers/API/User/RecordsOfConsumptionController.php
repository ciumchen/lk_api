<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\AssetsLogs;
use App\Models\FreezeLogs;
use App\Models\IntegralLog;
use App\Models\IntegralLogs;
use App\Models\Order;
use App\Models\RecordsOfConsumption;
use App\Models\Setting;
use App\Models\User;
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
            ->get(['operate_type','amount','updated_at'])->append(['updated_date']);

        return response()->json(['code'=>1, 'msg'=>'获取成功', 'data' => $data]);

    }

    //获取用户分享积分
    public function getUserAssetsFxJf(Request $request){
        $uid = $request->input('uid');
        $page = $request->input('page');
        $pageSize = $request->input('pageSize',10);
        $data['amount_count'] = 0;
        if($uid!=''){
//            $count1 = AssetsLogs::where('uid',$uid)->where('assets_name', 'encourage')->where('operate_type','share_b_rebate')->sum('amount');
//            $count2 = AssetsLogs::where('uid',$uid)->where('assets_name', 'encourage')->where('operate_type','invite_rebate')->sum('amount');
//            $data['amount_count'] = $count1+$count2;

            $count1 = AssetsLogs::where('uid',$uid)->where('remark','邀请商家，获得盈利返佣')->sum('amount');
            $count2 = AssetsLogs::where('uid',$uid)->where('remark','下级消费返佣')->sum('amount');
            $data['amount_count'] = $count1+$count2;
//            $data1 = (new AssetsLogs())
//                ->where("uid", $uid)
//                ->where('assets_name', 'encourage')
//                ->where('remark','邀请商家，获得盈利返佣')
//                ->where('operate_type','share_b_rebate')
//                ->orderBy('id', 'desc')
//                ->latest('id')
//                ->forPage($page, $pageSize)
//                ->get(['operate_type','amount','updated_at']);
//
//            $data2 = (new AssetsLogs())
//                ->where("uid", $uid)
//                ->where('assets_name', 'encourage')
//                ->where('remark','邀请商家，获得盈利返佣')
//                ->where('operate_type','invite_rebate')
//                ->orderBy('id', 'desc')
//                ->latest('id')
//                ->forPage($page, $pageSize)
//                ->get(['operate_type','amount','updated_at']);

//            if(!empty($data1)){
//                foreach ($data1 as $k=>$v){
//                    $data['jls'][] = $v;
//                }
//            }
//            if(!empty($data2)){
//                foreach ($data2 as $k=>$v){
//                    $data['jls'][] = $v;
//                }
//            }
//            $data['countjl'] = (new AssetsLogs())
//                ->where("uid", $uid)
//                ->where(function ($query){
//                    $query->orwhere('remark','邀请商家，获得盈利返佣')
//                        ->orwhere('remark','下级消费返佣');
//                })
//                ->orderBy('id', 'desc')
//                ->latest('id')
//                ->count();

            $data['jls'] = (new AssetsLogs())
                ->where("uid", $uid)
                ->where(function ($query){
                    $query->orwhere('remark','邀请商家，获得盈利返佣')
                        ->orwhere('remark','下级消费返佣');
                })
                ->orderBy('id', 'desc')
                ->latest('id')
                ->forPage($page, $pageSize)
                ->get(['operate_type','amount','updated_at','remark'])->append(['updated_date']);

            return response()->json(['code'=>1, 'msg'=>'获取成功', 'data' => $data]);
        }else{
            $data['jls'] = 0;
            return response()->json(['code'=>0, 'msg'=>'获取失败', 'data' => $data]);
        }

    }

    //用户的公益贡献接口
    public function getUoserGYGX(Request $request){
        $uid = $request->input('uid');
        $count = Order::where('uid',$uid)->where('status',2)->sum('profit_price')*0.04;

        return response()->json(['code'=>1, 'msg'=>'获取成功', 'data' => array('num',$count)]);

    }

    //查询当前用户是邀请人所获得的商家积分记录
    public function getInvitePoints(Request $request){
        $uid = $request->input('uid');
        $page = $request->input('page');
        $pageSize = $request->input('pageSize',10);

        //商家积分总数
        $data['business_integral'] = User::where('id',$uid)->value('business_integral');


        $InvitePointsArr = [
            'HF' => 'Invite_points_hf',
            'YK' => 'Invite_points_yk',
            'MT' => 'Invite_points_mt',
            'ZL' => 'Invite_points_zl',
        ];

        foreach ($InvitePointsArr as $k=>$v){
            $value = Setting::where('key', $v)->value('value');
                if ($value != 0 && strstr($value,'|') != false) {
                    $dateArr = explode('|', $value);
                    $integralData[$k]=RecordsOfConsumption::where('updated_at','>',$dateArr[0])
                        ->where('updated_at','<',$dateArr[1])
                        ->where("uid", $uid)
                        ->where('description',$k)
                        ->with(['user'])
                        ->orderBy('id', 'desc')
                        ->latest('id')
                        ->forPage($page, $pageSize)
                        ->get()
                        ->append(['updated_date'])
                        ->toArray();

                }

        }

//dd($integralData);
        $operate_type = array(
            'spend'=>'消费订单完成',
            'rebate'=>'分红扣除积分',
        );
        $descriptionArr = array(
            'HF'=>'话费',
            'YK'=>'油卡',
            'MT'=>'美团',
            'ZL'=>'代充',
        );
        $data1 = array();
        foreach ($integralData as $k=>$v){
            if (!empty($v)){
                foreach ($v as $k2=>$v2){
                    $data1[strtotime($v2['updated_date'])]['operate_type'] = $operate_type[$v2['operate_type']];
                    $data1[strtotime($v2['updated_date'])]['amount'] = $v2['amount'];
                    $data1[strtotime($v2['updated_date'])]['description'] = $descriptionArr[$v2['description']];
                    $data1[strtotime($v2['updated_date'])]['phone'] = $v2['user']['phone'];
                    $data1[strtotime($v2['updated_date'])]['updated_date'] = $v2['updated_date'];
                    $data1[strtotime($v2['updated_date'])]['item'] = strtotime($v2['updated_date']);

                }

            }
        }
        if (!empty($data1)){
            array_multisort(array_column($data1, 'item'), SORT_DESC, $data1);
        }
        $data['jls'] =$data1;
//            dd($data['jls']);
        return response()->json(['code'=>1, 'msg'=>'获取成功', 'data' => $data]);
    }



}





