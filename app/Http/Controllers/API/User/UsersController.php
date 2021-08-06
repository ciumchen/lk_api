<?php

namespace App\Http\Controllers\API\User;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyBusinessRequest;
use App\Http\Requests\NewApplyBusinessRequest;
use App\Http\Requests\RealNameRequest;
use App\Http\Resources\IntegralLogsResources;
use App\Http\Resources\UserResources;
use App\Libs\Yuntong\YuntongPay;
use App\Models\AuthLog;
use App\Models\BusinessApply;
use App\Models\IntegralLogs;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Models\Users;
use App\Models\UserUpdatePhoneLog;
use App\Models\VerifyCode;
use App\Services\BusinessService;
use App\Services\OrderService;
use App\Services\OrderTwoService;
use App\Services\OssService;
use Illuminate\Database\Eloquent\Model;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use PDOException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\API\Payment\YuntongPayController;
class UsersController extends Controller
{

    //购买来客会员
    public function PurchaseLkMember(Request $request){
        $user = $request->user();
        $ip = $request->input('ip');
//        dd($user->toArray());
        $ip!=''?:$ip='183.14.29.143';
        if ($user->member_status != 0) {
            throw new LogicException('该用户已是来客会员，无需购买!');
        }

        //生成两条录单记录
        //给用户录单
//        $order_no1 = createOrderNo();
//        $order_no2 = createOrderNo();
        $order_no = createOrderNo();

        $data = array(
            'uid'=>$user->id,
            'business_uid'=>2,
            'profit_ratio'=>20,
            'price'=>10,
            'profit_price'=>2,
            'status'=>1,
            'name'=>'开通会员',
            'order_no'=>$order_no,
            );
        $orderData[] = Order::create($data)->toArray();
        //给邀请人录单
        if ($user->invite_uid != 2){
            $data = array(
                'uid'=>$user->invite_uid,
                'business_uid'=>2,
                'profit_ratio'=>5,
                'price'=>20,
                'profit_price'=>1,
                'status'=>1,
                'name'=>'开通会员',
                'member_gl_oid'=>$orderData[0]['id'],
                'order_no'=>$order_no,
            );
            $orderData[] = Order::create($data)->toArray();
        }

        //调用支付宝支付
        $payModel = new YuntongPayController();
        $data = [
            'goodsTitle' => '开通会员',
            'goodsDesc'  => '开通会员支付',//商品描述
            'need_fee'   => 1,//消费金额
            'order_no'   => $order_no,//订单号
            'order_from' => 'alipay',//支付渠道 固定值：alipay|wx|unionpay
            'ip'         => $ip,//ip
//            'return_url' => "http://ning.catspawvideo.com/api/getLkMemberPayHd",
            'return_url' => "",
        ];
        return $payModel->payRequest($data, createNotifyUrl('api/getLkMemberPayHd'));

    }

    //购买会员支付回调
    public function getLkMemberPayHd(Request $request){
        $Pay = new YuntongPay();
        $json = $request->getContent();
        DB::beginTransaction();
        try {
            $data = json_decode($json, true);
            $res = $Pay->Notify($data);

//            Log::info("=======打印购买会员支付回调数据====1======",$data);

            if (!empty($res)) {
                $Order = new Order();
                $orderInfo = $Order->getOrderByOrderNo($data[ 'order_id' ]);//用户录单记录
                if ($orderInfo->price/10 != $data[ 'amount' ]) {
                    throw new Exception('付款金额与应付金额不一致');
                }
//                Log::info("=======打印购买会员支付回调数据====2======",$data);
                //验证通过修改订单支付状态
                $orderInfo->status = 2;
                $orderInfo->pay_status = 'succeeded';
                $orderInfo->member_status = 1;//修改用户来客会员身份状态
                $orderInfo->save();

                $yqrOrder = $Order::where('member_gl_oid',$orderInfo->id)->first();//邀请人录单记录
                $yqrOrder->status = 2;
                $yqrOrder->pay_status = 'succeeded';
                $yqrOrder->save();

                $OrderTwoModel = new OrderTwoService();
                $OrderTwoModel->MemberUserOrder($orderInfo->id);
                $OrderTwoModel->MemberUserOrder($yqrOrder->id);

            } else {
                Log::info("=======打印购买会员支付回调数据=====解析为空=====");
                throw new Exception('解析为空');
            }
            DB::commit();
            $Pay->Notify_success();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('YuntongNotify-购买会员支付回调-验证不通过-getLkMemberPayHd-'.$e->getMessage(), [$json.'---------'.json_encode($e)]);
            $Pay->Notify_failed();
        }


//***************************************************************


    }

    public function addJfTestOrder(Request $request){
        $oid1 = $request->input('oid1');
        $oid2 = $request->input('oid2');
//        var_dump($oid1,$oid2);
        $OrderService = new OrderService();
        $OrderService->MemberUserOrder($oid1);
        $OrderService->MemberUserOrder($oid2);

    }

//    //购买会员支付回调
//    public function getLkMemberPayHd(Request $request){
//        $Pay = new YuntongPay();
//        $json = $request->getContent();
//        DB::beginTransaction();
//        try {
//            $data = json_decode($json, true);
//            $res = $Pay->Notify($data);
//
////            Log::info("=======打印购买会员支付回调数据====1======",$data);
//
//            if (!empty($res)) {
//                $Order = new Order();
//                $orderInfo = $Order->getOrderByOrderNo($data[ 'order_id' ]);//用户录单记录
//                if ($orderInfo->price/10 != $data[ 'amount' ]) {
//                    throw new Exception('付款金额与应付金额不一致');
//                }
////                Log::info("=======打印购买会员支付回调数据====2======",$data);
//                //验证通过修改订单支付状态
//                $orderInfo->status = 2;
//                $orderInfo->pay_status = 'succeeded';
//                $orderInfo->save();
//
//                $yqrOrder = $Order::where('member_gl_oid',$orderInfo->id)->first();//邀请人录单记录
//                $yqrOrder->status = 2;
//                $yqrOrder->pay_status = 'succeeded';
//                $yqrOrder->save();
//
//                //给用户添加积分和积分记录
//                $userData1 = Users::where('id',$orderInfo->uid)->first();//+10
//                $userData2 = Users::where('id',$orderInfo->business_uid)->first();//+2
//                //用户变动前积分
//                $oldamount1 = $userData1->integral;
//                $oldamount2 = $userData2->integral;
//                //给用户添加积分
//                $userData1->integral = $oldamount1+10;
//                $userData1->member_status = 1;//修改用户来客会员身份状态
//                $userData1->save();
//                $userData2->integral = $oldamount2+2;
//                $userData2->save();
//                //添加用户积分记录
//                IntegralLogs::addLog($userData1->id,10,'开通会员',$oldamount1,$userData1->role,'开通来客会员',$data[ 'order_id' ],0,$userData1->id,'KTHY');
//                IntegralLogs::addLog($userData2->id,2,'开通会员',$oldamount2,$userData2->role,'用户开通会员',$data[ 'order_id' ],0,$userData1->id,'KTHY');
//
//
//                //给邀请人添加积分和积分记录
//                $userData3 = Users::where('id',$yqrOrder->uid)->first();//+5
//                $userData4 = Users::where('id',$yqrOrder->business_uid)->first();//+1
//
//                //邀请人变动前积分
//                $oldamount3 = $userData3->integral;
//                $oldamount4 = $userData4->integral;
//
//                //给邀请人添加积分
//                $userData3->integral = $oldamount3+5;
//                $userData3->save();
//                $userData4->integral = $oldamount4+1;
//                $userData4->save();
//
//                //给邀请人添加积分记录
//                IntegralLogs::addLog($userData3->id,5,'开通会员',$oldamount3,$userData3->role,'邀请用户开通会员',$data[ 'order_id' ],0,$userData3->id,'KTHY');
//                IntegralLogs::addLog($userData4->id,1,'开通会员',$oldamount4,$userData4->role,'用户开通会员',$data[ 'order_id' ],0,$userData3->id,'KTHY');
//
//            } else {
//                Log::info("=======打印购买会员支付回调数据=====解析为空=====");
//                throw new Exception('解析为空');
//            }
//            DB::commit();
//            $Pay->Notify_success();
//        } catch (Exception $e) {
//            DB::rollBack();
//            Log::debug('YuntongNotify-购买会员支付回调-验证不通过-getLkMemberPayHd-'.$e->getMessage(), [$json.'---------'.json_encode($e)]);
//            $Pay->Notify_failed();
//        }
//
//
////***************************************************************
//
//
//    }



}
