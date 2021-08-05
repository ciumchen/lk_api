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

//        echo "购买来客会员";

        //生成两条录单记录
//
//        'uid',
//        'business_uid',
//        'profit_ratio',
//        'price',
//        'profit_price',
//        'status',
//        'name',
        //给用户录单
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
                'order_no'=>$order_no,
            );
            $orderData[] = Order::create($data)->toArray();
        }

//        dd($orderData);

        //调用支付宝支付
        $payModel = new YuntongPayController();
//        $payModel->bmPay();

        $data = [
            'goodsTitle' => '开通会员',
            'goodsDesc'  => '开通会员支付',//商品描述
            'need_fee'   => 1,//消费金额
            'order_no'   => $order_no,//订单号
            'order_from' => 'alipay',//支付渠道 固定值：alipay|wx|unionpay
            'ip'         => $ip,//ip
            'return_url' => "http://ning.catspawvideo.com/api/getLkMemberPayHd",
        ];
        return $payModel->payRequest($data, createNotifyUrl('api/bm-pay-notify'));

    }

    //购买会员支付回调
    public function getLkMemberPayHd(Request $request){
        $allData = $request->all();
        Log::info("=======打印购买会员支付回调数据=====1=====",$allData);
        Log::debug("=======打印购买会员支付回调数据=====1=====",$allData);
//***************************************************************
        $Pay = new YuntongPay();
        $json = $request->getContent();
        DB::beginTransaction();
        try {
            $data = json_decode($json, true);
            $res = $Pay->Notify($data);

            Log::info("=======打印购买会员支付回调数据====2======",$data);
            Log::debug("=======打印购买会员支付回调数据====2======",$data);

            if (!empty($res)) {
                $Order = new Order();
                $orderInfo = $Order->getOrderByOrderNo($data[ 'order_id' ]);
                if ($orderInfo->price != $data[ 'amount' ]) {
                    throw new Exception('付款金额与应付金额不一致');
                }
                Log::info("=======打印购买会员支付回调数据====3======",$data);
                Log::debug("=======打印购买会员支付回调数据====3======",$data);
            } else {
                DB::rollBack();
                Log::info("=======打印购买会员支付回调数据=====解析为空=====");
                Log::debug("=======打印购买会员支付回调数据=====解析为空=====");
                throw new Exception('解析为空');
            }
            DB::commit();
            $Pay->Notify_success();
        } catch (Exception $e) {
            Log::debug('YuntongNotify-验证不通过-bmCallback-'.$e->getMessage(), [$json.'---------'.json_encode($e)]);
            $Pay->Notify_failed();
        }


//***************************************************************


    }

    public function bmPayCallback(Request $request)
    {
        $Pay = new YuntongPay();
        $json = $request->getContent();
        DB::beginTransaction();
        try {
            $data = json_decode($json, true);
            $res = $Pay->Notify($data);
            if (!empty($res)) {
                $Order = new Order();
                $orderInfo = $Order->getOrderByOrderNo($data[ 'order_id' ]);
                if ($orderInfo->price != $data[ 'amount' ]) {
                    throw new Exception('付款金额与应付金额不一致');
                }
                /* 更新订单表以及积分 */
                $OrderService = new OrderService();
                $description = $OrderService->getDescription($orderInfo->id);
                $OrderService->completeOrderTable($orderInfo->id, $orderInfo->uid, $description, $orderInfo->order_no);
                /* 更新对应斑马订单表 */
                $OrderService->updateSubOrder($orderInfo->id, $res, $description);
            } else {
                DB::rollBack();
                throw new Exception('解析为空');
            }
            DB::commit();
            /* 订单完成后续充值 */
            $OrderService->afterCompletedOrder($orderInfo->id, $res, $description, $orderInfo);
            $Pay->Notify_success();
        } catch (Exception $e) {
            Log::debug('YuntongNotify-验证不通过-bmCallback-'.$e->getMessage(), [$json.'---------'.json_encode($e)]);
            $Pay->Notify_failed();
        }
    }



}
