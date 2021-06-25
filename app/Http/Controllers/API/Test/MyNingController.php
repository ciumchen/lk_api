<?php

namespace App\Http\Controllers\Api\Test;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\OrderService;
use App\Services\OssService;
use App\Models\Order;
use App\Services\OrderService_test;
use Illuminate\Support\Facades\DB;
use App\Exceptions\LogicException;
use App\Models\Address;
use App\Models\Assets;
use App\Models\AssetsLogs;
use App\Models\AssetsType;
use App\Models\BanList;
use App\Models\Setting;
use App\Models\WithdrawLogs;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\VerifyCode;
use App\Services\AddressService;
use App\Services\AssetsService;
use App\Services\TransferService;
use App\Services\AssetConversionService;
class MyNingController extends Controller
{
    //test测试
    public function test()
    {
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
    public function test2(Request $request)
    {
//        echo 'test22222';
//        var_dump($request->img);
//        var_dump($request->file('img'));
        $imgUrl = OssService::base64Upload($request->img);
        var_dump($imgUrl);

//        $path = $request->file('img')->store('avatars');
//
//        return $path;


    }

    //订单回调测试
    public function orderTest(Request $request)
    {
//        echo "测试积分添加";
        //更新 order 表审核状态
        $orderOn = $request->input('orderOn');
        (new OrderService())->completeOrder($orderOn);
    }

    //自动审核测试
//https://ceshi.catspawvideo.com/api/pushOrder
//http://localhost:8081/api/pushOrder
    public function pushOrder2()
    {
        set_time_limit(0);
        ini_set('max_execution_time', '0');
//        $count = Order::where('status',"!=",2)->where('id','>',23314)->where('pay_status',"!=","ddyc")->count();
        $count = Order::where('status', "!=", 2)->where('pay_status', "!=", "ddyc")->count();
//        $count = Order::where('status',"!=",2)->where('pay_status',"!=","ddyc")->count();
//dd($count);
        if ($count) {
            $orderInfo = DB::table('order')
//                ->where('order.id','>','23314')
//            $orderInfo = DB::table('order')
                ->where('order.status', "!=", 2)
                ->where('order.pay_status', "!=", "ddyc")
                ->leftJoin('trade_order', 'order.id', '=', 'trade_order.oid')
                ->limit(20)->get()->toArray();
//                ->limit(1)->get()->toArray();

//            dd($orderInfo);
            foreach ($orderInfo as $k => $v) {
//                dd($v->order_no);
                if ($v->order_no) {
                    (new OrderService_test())->completeOrder($v->order_no);
                }

            }

            return "<h4>今次自动完成审核20条记录，总共还有<font color='red'>" . ($count - 20) . "</font>条订单还需要审核</h4>";
//            return "<h4>今次自动完成审核1条记录，总共还有<font color='red'>".($count-1)."</font>条订单还需要审核</h4>";

        } else {
            return '<h4>所有订单审核完成</h4>';
        }


    }


    //自动审核测试
//https://ceshi.catspawvideo.com/api/pushOrder
//http://localhost:8081/api/pushOrder
    public function pushOrder()
    {
        set_time_limit(0);
        ini_set('max_execution_time', '0');
//        $count = Order::where('status',"!=",2)->where('id','>',23314)->where('pay_status',"!=","ddyc")->count();
        $count = Order::where('status', "!=", 2)->where('pay_status', "!=", "ddyc")->count();
//        $count = Order::where('status',"!=",2)->where('pay_status',"!=","ddyc")->count();
//dd($count);
        if ($count) {
            $orderInfo = DB::table('order')
                ->where('status', "!=", 2)
                ->where('pay_status', "!=", "ddyc")
                ->limit(20)->get()->toArray();

//            dd($orderInfo);
            foreach ($orderInfo as $k => $v) {
//dd($v->id);
                if ($v->id) {
                    (new OrderService_test())->completeOrder($v->id);
                }

            }

            return "<h4>今次自动完成审核20条记录，总共还有<font color='red'>" . ($count - 20) . "</font>条订单还需要审核</h4>";
//            return "<h4>今次自动完成审核1条记录，总共还有<font color='red'>".($count-1)."</font>条订单还需要审核</h4>";

        } else {
            return '<h4>所有订单审核完成</h4>';
        }


    }

    //修改用户手机号
    public function updateUserPhone(Request $request)
    {
        $uid = $request->input('uid');
        $phone = $request->input('phone');
        $userInfo = User::where('id', $uid)->first();
        $phoneUser = User::where('phone', $phone)->first();

        if ($phoneUser){
            return "该手机号已被uid=".$phoneUser->id." 的用户使用，请更换其他手机号";
        }
        if ($userInfo) {
            $userInfo->phone = $phone;
            $re = $userInfo->save();
            if ($re) {
                return "<h4>用户uid=" . $uid . "的手机号修改成功</h4>";
            } else {
                return "<h4>用户uid=" . $uid . "的手机号修改失败</h4>";
            }
        } else {
            return "<h4>这个uid=" . $uid . "的用户不存在</h4>";
        }

    }


    //对比用户资产和记录
    public function getUserAssetInfo(Request $request)
    {
        $uid = $request->input('uid');
        $user = User::where('id',$uid)->first();
//        dd($user);
        $iets_asset = AssetsType::where('assets_name', 'iets')->first();
        $usdt_asset = AssetsType::where('assets_name', AssetsType::DEFAULT_ASSETS_NAME)->first();
        $userBalance_iets = AssetsService::getBalanceData($user, $iets_asset);//获取资产
        $userBalance_usdt = AssetsService::getBalanceData($user, $usdt_asset);//获取资产

        $data['iets'] = $userBalance_iets->amount;
        $data['iets_log'] = AssetsLogs::where('assets_type_id', $iets_asset->id)->where('uid', $user->id)->sum('amount');

        $data['usdt'] = $userBalance_usdt->amount;//错
        $data['usdt_log'] = AssetsLogs::where('assets_type_id', $usdt_asset->id)->where('uid', $user->id)->sum('amount');//对
        dd($data);


    }

    //解封用户资产账号
    public function xfUserAssetFH(Request $request){
        $uid = $request->input('uid');
        $amount = $request->input('amount');
        $assType = $request->input('assType');
        $assData = Assets::where('uid',$uid)->where('assets_name',$assType)->first();
        $assData->amount = $amount;
        if($assData->save()){
            return "账号解封成功";
        }else{
            return "账号解封失败";
        }

    }


}







