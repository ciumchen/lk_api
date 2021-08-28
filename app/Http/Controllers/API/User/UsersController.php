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
        $ip!=''?:$ip='183.14.29.143';
        if ($user->member_status != 0) {
            throw new LogicException('该用户已是来客会员，无需购买!');
        }

        //生成两条录单记录//给用户录单
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
            'description'=>"KTHY",
            );
        $orderData[] = Order::create($data)->toArray();
        //给邀请人录单
        $data = array(
            'uid'=>$user->invite_uid,
            'business_uid'=>2,
            'profit_ratio'=>20,
            'price'=>5,
            'profit_price'=>1,
            'status'=>1,
            'name'=>'开通会员',
            'member_gl_oid'=>$orderData[0]['id'],
            'order_no'=>$order_no,
            'description'=>"KTHY",
        );
        $orderData[] = Order::create($data)->toArray();

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
                //更新用户会员身份，member_gl_oid
                $gmUser = Users::find($orderInfo->uid);
                $gmUser->member_status = 1;
                $gmUser->save();//修改用户来客会员身份状态

                //验证通过修改订单支付状态
                $orderInfo->status = 2;
                $orderInfo->pay_status = 'succeeded';
                $orderInfo->save();

                $yqrOrder = $Order::where('member_gl_oid',$orderInfo->id)->first();//邀请人录单记录
                $yqrOrder->status = 2;
                $yqrOrder->pay_status = 'succeeded';
                $yqrOrder->save();

                $OrderModel = new OrderService();
                $OrderModel->MemberUserOrder($orderInfo->id);
                $OrderModel->MemberUserOrder($yqrOrder->id);

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

    }

    //购买会员支付完成添加积分测试
    public function addJfTestOrder(Request $request){
        $oid1 = $request->input('oid1');
        $oid2 = $request->input('oid2');
//        var_dump($oid1,$oid2);
        $OrderService = new OrderService();
        $OrderService->MemberUserOrder($oid1);
        $OrderService->MemberUserOrder($oid2);

    }

}
