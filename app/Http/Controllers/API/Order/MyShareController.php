<?php

namespace App\Http\Controllers\API\Order;

use App\Http\Controllers\Controller;
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
    //test测试
    public function test(){
//        $re = DB::select("select * form users");
        //查询当前用户的邀请人
//        $invite_uid = DB::table("users")->where('id',1)->pluck('invite_uid')->toArray();
//        if($invite_uid[0]!=0){
//            //有邀请人
//            $member_head = DB::table("users")->where('id',$invite_uid[0])->pluck('member_head')->toArray();
//            if ($member_head[0]!=2){
//                //邀请人是非盟主按2%计算
//            }else{
//                //邀请人是盟主按3.5%计算
//            }
//        }else{
//            //没有邀请人按2%计算
//        }
//
//
//
//        echo "<pre>";
//        print_r($invite_uid);
//        print_r($member_head);

        $re = Order::create([
            'state' => 1,
            'uid' => 2,
            'business_uid' => 3,
            'name' => '张三',
            'profit_ratio' => '5',
            'price' => '100',
            'profit_price' => '200',
        ])->toArray();

var_dump($re['id']);





        echo 'test1112021年4月22日 13:39:29';
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
            $re = DB::select("SELECT SUM(profit_price) AS nums FROM `order` WHERE uid=$v->id");
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
            //查询当前用户的邀请人是团员的所有订单并统计实际让利金额求和
            $tuanYuanOrder = DB::select("SELECT SUM(profit_price) AS nums FROM `order` WHERE uid=$v->id and state=1");
            if(!$tuanYuanOrder[0]->nums){//团员累计奖励为空时等于0
                $tuanYuanOrder[0]->nums = 0;
            }
            //每个用户的所有团员订单让利额都乘2%
            $userData["$v->phone"]['tuanyuan'] = round($tuanYuanOrder[0]->nums*0.02,2);

            //查询当前用户的邀请人是盟主的所有订单并统计实际让利金额求和
            $mengZhuOrder = DB::select("SELECT SUM(profit_price) AS nums FROM `order` WHERE uid=$v->id and state=2");
            if(!$mengZhuOrder[0]->nums){//盟主累计奖励为空时等于0
                $mengZhuOrder[0]->nums = 0;
            }
            //每个用户的所有盟主订单让利额都乘3.5%
            $userData["$v->phone"]['mengzhu'] = round($mengZhuOrder[0]->nums*0.035,2);

            //所有盟主订单让利额+所有团员订单让利额=总让利额
            $totalXfMoney+=$userData["$v->phone"]['tuanyuan']+$userData["$v->phone"]['mengzhu'];

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
            $fxData['oneUser'][$i]['mengzhu']=$v['mengzhu'];
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
            $re = DB::select("SELECT SUM(profit_price) AS nums FROM `order` WHERE uid=$v->id");
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



}
