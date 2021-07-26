<?php

namespace App\Http\Controllers\API\Order;

use App\Http\Controllers\Controller;
use App\Models\AssetsLogs;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Exceptions\LogicException;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrdersResources;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Exception;
use Illuminate\Pagination\Paginator;
use PDOException;

//我的分享
class MyShareController extends Controller
{
    //获取消费者和商户的lk
    public function getLkCount(Request $request){
        $uid = $request->input('uid');
        $userInfo = DB::table('users')->where('id',$uid)->first();

        $lkNum['lkNum']=0;
        //消费者lk
        if ($userInfo->role==1){
            $data = DB::table('settings')->where('key','lk_per')->first();
            $lkNum['lkNum'] = round($userInfo->integral/($data->value),2);
        }
        //商家lk
        if ($userInfo->role==2){
            $data = DB::table('settings')->where('key','business_Lk_per')->first();
            $lkNum['lkNum'] = round(($userInfo->business_integral)/($data->value),2);
        }
        return $lkNum;
    }



    //分享消费者分享 Consumer
    //累计消费者奖励
    //接口返回当前用户下每个用户的所有累积实际让利金额*3%
    public function Consumer(Request $request){
        $this->validate($request, [
            'userId' => 'bail|required|integer',
        ]);

        $userId = $request->input('userId');//获取当前用户的id
        //查询当前用户所有分享的用户
        $userList = DB::table('users')->where('invite_uid',$userId)->get(['id','phone'])->toArray();
        $userData = array();
        $totalXfMoney = 0;//总消费累积金额
        foreach ($userList as $k=>$v){
            $re = DB::select("SELECT SUM(profit_price) AS nums FROM `order` WHERE uid=$v->id and status=2");
            if(!$re[0]->nums){
                $re[0]->nums = 0;
            }
            $userData["$v->phone"] = round($re[0]->nums*0.03,2);//每个用户所有累积实际让利金额*3%
            $totalXfMoney+=$userData["$v->phone"];
        }
        arsort($userData);
        //组装数据
        $fxData['countUser'] = count($userList);//分享用户总人数
        $fxData['totalXfMoney'] = round($totalXfMoney,2);//总消费累积金额
        //每个用户累积实际让利金额
        $i = 1;
        foreach ($userData as $k=>$v){
            $fxData['oneUser'][$i]['phone']=$k;
            $fxData['oneUser'][$i]['oneMoney']=$v;
            $i++;
        }

        return $fxData;

    }

    //Merchant
    //分享商家
    //累计商家让利奖励
    //接口返回当前用户下每个商户的所有累积实际让利金额*2%
    public function Merchant(Request $request){
        $this->validate($request, [
            'userId' => 'bail|required|integer',
        ]);

        $userId = $request->input('userId');//获取当前用户的id
        //查询当前用户所有分享的商家
        $userList = DB::table('users')->where('invite_uid',$userId)->where('role',2)->get(['id','phone','member_head'])->toArray();
        $userData = array();
        $totalXfMoney = 0;//总消费累积金额
        foreach ($userList as $k=>$v){
            //查询当前用户的邀请人是团员的所有订单并统计实际让利金额求和，审核通过、支付成功
//            $tuanYuanOrder = DB::select("SELECT SUM(profit_price) AS nums FROM `order` WHERE uid=$v->id and state=1 and status=2 and pay_status='succeeded'");//判断了非盟主

            //不判断是否是非盟主，查询当前id商家的所有录单,business_uid
            $tuanYuanOrder = DB::select("SELECT SUM(profit_price) AS nums FROM `order` WHERE business_uid=$v->id and status=2");
            if(!$tuanYuanOrder[0]->nums){//团员累计奖励为空时等于0
                $tuanYuanOrder[0]->nums = 0;
            }
            //判断当前用户是盟主就乘0.035，非盟主就乘0.02
            $userInfo = DB::table('users')->where('id',$userId)->first();
            if ($userInfo->member_head==2){//盟主
                $userData["$v->phone"]['tuanyuan'] = round($tuanYuanOrder[0]->nums*0.035,2);
            }else{//非盟主
                $userData["$v->phone"]['tuanyuan'] = round($tuanYuanOrder[0]->nums*0.02,2);
            }


//            //查询当前用户的邀请人是盟主的所有订单并统计实际让利金额求和
//            $mengZhuOrder = DB::select("SELECT SUM(profit_price) AS nums FROM `order` WHERE uid=$v->id and state=2 and status=2 and pay_status='succeeded'");
//            if(!$mengZhuOrder[0]->nums){//盟主累计奖励为空时等于0
//                $mengZhuOrder[0]->nums = 0;
//            }
            //每个用户的所有盟主订单让利额都乘3.5%
//            $userData["$v->phone"]['mengzhu'] = round($mengZhuOrder[0]->nums*0.035,2);

            //所有盟主订单让利额+所有团员订单让利额=总让利额
//            $totalXfMoney+=$userData["$v->phone"]['tuanyuan']+$userData["$v->phone"]['mengzhu'];

            //统计每个分享商家录单的让利金额总数，不区分商家是否是盟主还是团员
            $totalXfMoney+=$userData["$v->phone"]['tuanyuan'];

        }
        arsort($userData);
        //组装数据
        $fxData['countUser'] = count($userList);//分享用户总人数
        $fxData['totalXfMoney'] = round($totalXfMoney,2);//总消费累积金额
        //每个用户累积实际让利金额
        $i = 1;
        foreach ($userData as $k=>$v){
            $fxData['oneUser'][$i]['phone']=$k;
            $fxData['oneUser'][$i]['tuanyuan']=$v['tuanyuan'];
//            $fxData['oneUser'][$i]['mengzhu']=$v['mengzhu'];
            $i++;
        }

        return $fxData;

    }

    //Team
    //分享团队
    //判断当前用户uid是否是盟主
    //在所有的分享用户中挑选2盟主
    //2盟主自己的所有商家的让利*0.5%和人数
    //2盟主下级的所有人分享的商家的让利*0.5%和人数
    public function Team(Request $request){
        $this->validate($request, [
            'userId' => 'bail|required|integer',
        ]);

        $userId = $request->input('userId');//获取当前用户的id
        //查询当前用户所有分享的商家
        $userList = DB::table('users')->where('invite_uid',$userId)->where('role',2)->get(['id','phone'])->toArray();
        $userData = array();
        $totalXfMoney = 0;//总消费累积金额
        foreach ($userList as $k=>$v){
            $re = DB::select("SELECT SUM(profit_price) AS nums FROM `order` WHERE uid=$v->id and status=2 and pay_status='succeeded'");
            if(!$re[0]->nums){
                $re[0]->nums = 0;
            }
            $userData["$v->phone"] = round($re[0]->nums*0.02,2);//每个用户所有累积实际让利金额*2%
            $totalXfMoney+=$userData["$v->phone"];
        }
        arsort($userData);
        //组装数据
        $fxData['countUser'] = count($userList);//分享用户总人数
        $fxData['totalXfMoney'] = round($totalXfMoney,2);//总消费累积金额
        $fxData['oneUser'] = $userData;//每个用户累积实际让利金额

        return $fxData;

    }

    //获取当前用户今日录单金额总数和账号限额
    public function getTodayLkCount(Request $request){
        $uid = $request->input('uid');
        //查询当前商户今日限额总数
        $hfData = DB::table('business_data')->where('uid',$uid)->first();
        if ($hfData->state==1){
            //单独设置商户每日限额
            $data['todayHfQuota'] = $hfData->limit_price;

        }else{
            //没有单独设置商户每日限额
            $setData = DB::table('settings')->where('key','limit_price')->first();
            $data['todayHfQuota'] = $setData->value;

        }

        //统计当前商户今日所有录单金额
        $today = date('Y-m-d',time());
        $data['priceCount'] = DB::table('order')->where('business_uid',$uid)->where('created_at','>=',$today)->where('pay_status','succeeded')->sum('price');
        //判断今日剩余录单额度
        if($data['todayHfQuota']>$data['priceCount']){
            //没有超出限额
            $data['todaySyHfQuota'] = $data['todayHfQuota']-$data['priceCount'];//今日剩余录单限额
        }else{
            //超出限额
            $data['todaySyHfQuota'] = 0;
        }

        return $data;

    }


    //Merchant
    //新分享商家
    //累计商家让利奖励
    //接口返回当前用户下每个商户的所有累积实际让利金额*2%
    public function newFxMerchant(Request $request){
        $this->validate($request, [
            'uid' => 'bail|required|integer',
        ]);

        $userId = $request->input('uid');//获取当前用户的id
        $userInfo = Users::where('id',$userId)->first();
        //查询当前用户所有分享的商家
        $userList = DB::table('users')->where('invite_uid',$userId)->where('role',2)->get(['id','phone','member_head'])->toArray();
        foreach ($userList as $k=>$v){
            //今日让利奖励
            $today = date('Y-m-d',time());
            //不判断是否是非盟主，查询当前id商家的所有录单,business_uid
            $tuanYuanData[$k]['uid'] = $v->id;
            $tuanYuanData[$k]['phone'] = $v->phone;

            //统计每个商户的今日录单的实际让利金额，判断当前用户是盟主就乘0.035，非盟主就乘0.02
            $profit_price = Order::where('business_uid',$v->id)->where('status',2)->where('created_at','>=',$today)->sum('profit_price');//实际让利金额
            if ($userInfo->member_head==2){//盟主
                $tuanYuanData[$k]['all_profit_price'] = round($profit_price*0.035,2);
            }else{//非盟主
                $tuanYuanData[$k]['all_profit_price'] = round($profit_price*0.02,2);
            }

            //统计每个商户录单的消费金额总数
//            $tuanYuanData[$k]['price'] = Order::where('business_uid',$v->id)->where('status',2)->where('created_at','>=',$today)->sum('price');//消费金额
            $tuanYuanData[$k]['price'] = Order::where('business_uid',$v->id)->where('status',2)->sum('price');//录单总消费金额

        }

        if (!empty($tuanYuanData)){
            array_multisort(array_column($tuanYuanData, 'price'), SORT_DESC, $tuanYuanData);
        }

        //累计让利奖励
        $tuanYuanData['assetsLj']['allAmountRljl'] = AssetsLogs::where('uid',$userId)->where('remark','邀请商家，获得盈利返佣')->sum('amount');

        //邀请分红资产记录变动数量统计
        $tuanYuanData['assetsLj']['allYqfhAmountRljl'] = AssetsLogs::where('uid',$userId)->where('operate_type','invite_rebate')->sum('amount');

        //累计总奖励
        $tuanYuanData['assetsLj']['allAmountZjl'] = AssetsLogs::where('uid',$userId)->where(function ($query){
            $query->where('operate_type','invite_rebate')->orWhere('operate_type','share_b_rebate');
        })->sum('amount');

        return response()->json(['code'=>1, 'msg'=>'获取成功', 'data' => $tuanYuanData]);

    }

    //查询用户分享商家累计总奖励记录
    public function getUserFxshjl(Request $request){
        $userId = $request->input('uid');
        $page = $request->input('page');
        $page!=''?:$page=1;
        $data = AssetsLogs::where('uid',$userId)->where(function ($query){
            $query->where('operate_type','invite_rebate')->orWhere('operate_type','share_b_rebate');
        })->orderBy('updated_at','desc')->forPage($page, 10)->get(['remark','updated_at','amount']);
        if ($data!='[]'){
            return response()->json(['code'=>1, 'msg'=>'获取成功', 'data' => $data]);
        }else{
            return response()->json(['code'=>0, 'msg'=>'获取失败', 'data' => '']);
        }

    }

    //查询二级和三级消费
    public function getTowXfUser(Request $request){
        $uid = $request->input('uid');
        $grade = $request->input('grade');//2/3
        if ($grade==2){//二级
            $grade = 0.02;
        }elseif($grade==3){//三级
            $grade = 0.01;
        }else{
            return response()->json(['code'=>0, 'msg'=>'参数grade错误', 'data' => '']);
        }
        $userList = Users::where('invite_uid',$uid)->get(['id','phone','member_head']);
        if ($userList!='[]'){
            $data = array();
            foreach ($userList->toArray() as $k=>$v){
                $data[$k]['uid'] = $v['id'];
                $data[$k]['phone'] = $v['phone'];
                //消费总额
                $data[$k]['total_consumption'] = Order::where('uid',$v['id'])->where('status',2)->where('id','>=',40136)->sum('price');
//                $data[$k]['total_consumption'] = Order::where('uid',$v['id'])->where('status',2)->sum('price');
                //消费奖励
                $data[$k]['consumption_reward'] = Order::where('uid',$v['id'])->where('status',2)->where('id','>=',40136)->sum('profit_price')*$grade;
//                $data[$k]['consumption_reward'] = Order::where('uid',$v['id'])->where('status',2)->sum('profit_price')*$grade;


            }
            if (!empty($data)){
                array_multisort(array_column($data, 'total_consumption'), SORT_DESC, $data);
            }
            return response()->json(['code'=>1, 'msg'=>'获取成功', 'data' => $data]);
        }else{
            return response()->json(['code'=>0, 'msg'=>'获取失败', 'data' => 0]);
        }


    }



}
