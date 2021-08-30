<?php

namespace App\Http\Controllers\API\Test;

use App\Http\Controllers\Controller;
use App\Models\BusinessApply;
use App\Models\BusinessData;
use App\Models\IntegralLogs;
use App\Models\LkshopOrder;
use App\Models\LkshopOrderLog;
use App\Models\OrderAirTrade;
use App\Models\OrderIntegralLkDistribution;
use App\Models\OrderMobileRecharge;
use App\Models\OrderUtilityBill;
use App\Models\OrderVideo;
use App\Models\RebateData;
use App\Models\TradeOrder;
use App\Models\User;
use App\Models\UserIdImg;
use App\Models\Users;
use App\Models\UserUpdatePhoneLog;
use App\Models\UserUpdatePhoneLogSd;
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
use App\Models\TtshopUser;

class MyNingModifyController extends Controller
{

    /**
     * 测试启用
     */
    public function __construct()
    {
        //die('测试接口');
    }

    //扣除用户积分
    public function setSdkcUserJf(Request $request){
        set_time_limit(0);
        ini_set('max_execution_time', '0');

        $oid = $request->input('oid');
        $order_no = $request->input('order_no');
        $num = $request->input('num');

        //通过订单号查询查消费者uid和商家uid
        $orderData = Order::find($oid);
        $xfzUid = $orderData->uid;//消费者uid
        $sjUid = $orderData->business_uid;//商家uid

        //通过订单号查询积分记录表所有的记录，并统计记录数
        $xfzJfLogCount = IntegralLogs::where(['order_no'=>$order_no,'uid'=>$xfzUid])->count();
        $sjJfLogCount = IntegralLogs::where(['order_no'=>$order_no,'uid'=>$sjUid])->count();

        $xfzJfLog = IntegralLogs::where(['order_no'=>$order_no,'uid'=>$xfzUid])->limit($xfzJfLogCount-1)->get();
        $sjJfLog = IntegralLogs::where(['order_no'=>$order_no,'uid'=>$sjUid])->limit($sjJfLogCount-1)->get();


        //dd($xfzJfLog->toArray(),$sjJfLog->toArray());

        //删除消费者用户和商家用户的积分 users
        if ($xfzJfLog!=null){//删除消费者积分
            $xfzJfLogArr = $xfzJfLog->toArray();
            $countXfzJf = 0;
            foreach ($xfzJfLogArr as $k=>$v){
                $countXfzJf+=$v['amount'];
            }

            $xfzUserData = Users::find($xfzUid);
            $xfzUserData->integral = $xfzUserData->integral-$countXfzJf;
            $xfzUserData->save();

        }
        if ($sjJfLog!=null){//删除商家积分
            $sjJfLogArr = $sjJfLog->toArray();
            $countsjJf = 0;
            foreach ($sjJfLogArr as $k=>$v){
                $countsjJf+=$v['amount'];
            }

            $sjUserData = Users::find($sjUid);
            $sjUserData->integral = $sjUserData->integral-$countsjJf;
            $sjUserData->save();

        }

        //删除消费者用户和商家用户的积分记录 integral_log

        //




        $jfData = IntegralLogs::where('id','>',152087)->get();
//        $jfData = IntegralLogs::where('id','=',332)->get();
        $i = 0;
//        dd($jfData->toArray());

        foreach ($jfData->toArray() as $k=>$v){

//            dd($v['description'],$v['order_no']);
            $oid = $this->getOderIdByDescription($v['description'],$v['order_no']);

//            if ($v['description']=='LR'){
//                dump($oid.'--'.$v['description']);
//            }
//        dump($oid.'--'.$v['description']);

            $userInfo = Users::where('id',$v['uid'])->first();
            if ($v['role']==1){//扣除消费者积分
                $userInfo->integral = $userInfo->integral-$v['amount'];
                $userInfo->save();

            }elseif ($v['role']==2){//扣除商家积分
                $userInfo->business_integral = $userInfo->business_integral-$v['amount'];
                $userInfo->save();
            }
            //改变订单排队状态
            $orderInfo = Order::where('id',$oid)->first();
            $orderInfo->line_up = 1;
            $orderInfo->save();

            //删除用户积分记录
            IntegralLogs::where('id',$v['id'])->delete();

            $i++;

        }

        var_dump($i);

    }


    public function getOderIdByDescription($desc, $order_no)
    {
        $Order = new Order();
        try {
            $oid = '';
            switch ($desc) {
                case 'MZL':
                    $oid = (new OrderMobileRecharge())->where('order_no', $order_no)->value('order_id');
                    break;
                case 'SHOP':
                    $oid = (new LkshopOrder())->where('order_no', $order_no)->value('oid');
                    break;
                case 'UB':
                    $oid = (new OrderUtilityBill())->where('order_no', $order_no)->value('order_id');
                    break;
                case 'AT':
                    $oid = (new OrderAirTrade())->where('order_no', $order_no)->value('oid');
                    break;
                case 'VC':
                    $oid = (new OrderVideo())->where('order_no', $order_no)->value('order_id');
                    break;
                default:
                    $oid = (new TradeOrder())->where('order_no', $order_no)->value('oid');
                    break;

            }
            return $oid;

        } catch (Exception $e) {
            return '错误';
//            throw $e;
        }


//
//
//        if (empty($Order)) {
//            $Order = Order::find($order_id);
//        }
//        try {
//            if (empty($Order)) {
//                throw new Exception('订单数据为空');
//            }
//            if (!empty($Order->trade)) { /* 兼容trade_order */
//                $description = $Order->trade->description;
//            }
//            if (!empty($Order->mobile)) {
//                switch ($Order->mobile->create_type) {
//                    case OrderMobileRecharge::CREATE_TYPE_ZL:
//                        $description = 'ZL';
//                        break;
//                    case OrderMobileRecharge::CREATE_TYPE_MZL:
//                        $description = 'MZL';
//                        break;
//                    default:
//                        ;
////                        $description = 'HF';
//                }
//            }
//            if (!empty($Order->video)) { /* 视频会员订单 */
//                $description = 'VC';
//            }
//            if (!empty($Order->air)) { /* 机票订单 */
//                $description = 'AT';
//            }
//            if (!empty($Order->utility)) { /* 生活缴费 */
//                $description = 'UB';
//            }
//            if (!empty($Order->lkshopOrder)) { /* 生活缴费 */
//                $description = 'SHOP';
//            }
//            /* 判断 是否已经获取到对应类型的订单*/
//            if (empty($description)) {
//                throw new Exception('没有对应类型的订单');
//            }
//        } catch (Exception $e) {
//            throw $e;
//        }
//
//

    }

//    //扣除用户来客
//    public function del_kcuserLk(){
////        echo floor(3.2232323233);exit;
//        $orderData = Order::where('status',2)
//            ->where('id','>=',38680)->where('id','<=',38810)
////            ->where('id','>=',1566)->where('id','<=',1566)
////            ->count();
//            ->get()->toArray();
//
////dd($orderData);
////dd($orderData);
//$i = 0;
//        foreach ($orderData as $k=>$v){
//            //扣除消费者lk
//            $userInfo = Users::where('id',$v['uid'])->first();
//            $userInfo->lk = floor($userInfo->integral/300);
//            $userInfo->save();
//
//            $i++;
//            //扣除商家lk
//
//        }
//
//dd($i);
//    }
//
//    //扣除s商家lk和邀请人lk
//    public function del_sh_kcuserLk(){
//        $orderData = Order::where('status',2)
//            ->where('id','>=',38680)->where('id','<=',38810)
////            ->where('id','>=',1566)->where('id','<=',1566)
////            ->count();
//            ->get()->toArray();
//
////dd($orderData);
////dd($orderData);
//$i = 0;
//        foreach ($orderData as $k=>$v){
//            //消费者uid的邀请人
//            $userInfo = Users::where('id',$v['uid'])->first();//消费者用户信息
//            $userInfoYQR = Users::where('id',$userInfo->invite_uid)->first();//邀请人的用户信息
//            $userInfoYQR->business_lk = floor($userInfoYQR->business_integral/60);
//            $userInfoYQR->save();
//
////            dd($userInfo->id,$userInfoYQR->id);
//
////dd($userInfo->business_integral);
//            //扣除商家uid的商家lk
//            $shInfo = Users::where('id',$v['business_uid'])->first();//消费者用户信息
//            $shInfo->business_lk = floor($shInfo->business_integral/60);
//            $shInfo->save();
//
//            $i++;
//        }
//
//dd($i);
//    }

//************************************************************************************


}







