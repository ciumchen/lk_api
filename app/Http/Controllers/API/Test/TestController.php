<?php


namespace App\Http\Controllers\API\Test;


use App\Http\Controllers\API\Test\OpenClient;
use App\Models\Order;
use App\Models\Users;
use App\Services\GatherOrderService;
use App\Services\GatherService;
use App\Services\OrderService;
use App\Services\OssService;
use Illuminate\Http\Request;
use GuzzleHttp;

class TestController
{
    /**
     * 测试启用
     */
    /*public function __construct()
    {
        die('测试接口');
    }*/

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

    //图片上传oss测试
    public function test2(Request $request){
//        echo 'test22222';
//        var_dump($request->img);
//        var_dump($request->file('img'));
        $imgUrl = OssService::base64Upload($request->img);
        var_dump($imgUrl);

//        $path = $request->file('img')->store('avatars');
//
//        return $path;


    }

    //图片上传oss测试
    public function gatherTest(Request $request)
    {
        //$gid = 16;
        for ($i = 101; $i <= 201; $i ++)
        {
            (new GatherService())->addGatherUser($request->gid, $i);
        }
    }

    //扣除来拼金和购物卡
    /*public function updGold()
    {
        $ids = [15873,7772,439,18735,2422,2561,2561,11507,18054,9941,1086,20257,602,602,610,2850,10961,18822,280,145,19827,20555,11200,10317,17718,287];
        foreach ($ids as $id)
        {
            $users = Users::find($id);
            $users->balance_tuan = $users->balance_tuan - 100;
            $users->save();
        }

        $idDict = [56,10295,11339,19708,7772,18822];
        foreach ($idDict as $val)
        {
            $users = Users::find($val);
            $users->gather_card = $users->gather_card - 100;
            $users->save();
        }
    }*/

    //扣除来拼金和购物卡
    public function updOrderStatus(Request $request)
    {
        $orderList = Order::where(['description' => 'PT', 'status' => 1])
            ->where('created_at', '>=', $request->strat)
            ->where('created_at', '<=', $request->endat)
            ->get(['id', 'uid', 'order_no']);

        $orderArr = json_decode($orderList, 1);
        foreach ($orderArr as $list)
        {
            (new GatherOrderService())->completeOrderGatger($list['id'], $list['uid'], 'PT', $list['order_no']);
        }
    }
}
