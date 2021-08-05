<?php

namespace App\Http\Controllers\API\User;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyBusinessRequest;
use App\Http\Requests\NewApplyBusinessRequest;
use App\Http\Requests\RealNameRequest;
use App\Http\Resources\IntegralLogsResources;
use App\Http\Resources\UserResources;
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
        $data = $request->all();

        Log::info("=======打印购买会员支付回调数据==========",$data);

    }



}
