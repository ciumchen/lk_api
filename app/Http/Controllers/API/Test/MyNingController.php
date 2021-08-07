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

class MyNingController extends Controller
{

    /**
     * 测试启用
     */
    public function __construct()
    {
//        die('测试接口');
    }

    //test测试
    public function UpdateBusinessApply(Request $request)
    {
        $uid = $request->input('uid');
        $status = $request->input('status');
        $re = BusinessApply::where('uid',$uid)->update(array('status'=>$status));
        if ($re){
            echo "修改成功";
        }else{
            echo "修改失败";
        }
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
    public function pushOrder()
    {
        set_time_limit(0);
        ini_set('max_execution_time', '0');
//        $count = Order::where('status', "!=", 2)->where('pay_status', "!=", "ddyc")->count();
        $count = Order::where('status', "!=", 2)->count();
//dd($count);
        if ($count) {
            $orderInfo = DB::table('order')
                ->where('status', "!=", 2)
//                ->where('pay_status', "!=", "ddyc")
                ->limit(20)->get()->toArray();

//            dd($orderInfo);
            foreach ($orderInfo as $k => $v) {
                $orderNo = $this->getOrderInfoOrderNo($v->id);
                //获取订单类型
                $orderService = new OrderService_test();
                $orderType = $orderService->getDescription($v->id);//订单类型
                (new OrderService_test())->pushCompleteOrder($v->id,$orderNo,$v->uid,$orderType);
            }

            return "<h4>今次自动完成审核20条记录，总共还有<font color='red'>" . ($count - 20) . "</font>条订单还需要审核</h4>";
//            return "<h4>今次自动完成审核1条记录，总共还有<font color='red'>".($count-1)."</font>条订单还需要审核</h4>";

        } else {
            return '<h4>所有订单审核完成</h4>';
        }


    }

    //获取订单号
    public function getOrderInfoOrderNo(string $orderId,Order $orderData=null)
    {
        try {
            if (empty($orderData)) {
                $orderData = Order::find($orderId);
            }
//            $orderData = Order::find($orderId);
            $orderService = new OrderService_test();
//            log::debug("=================打印订单信息01==================================".$orderId);
            $orderType = $orderService->getDescription($orderId, $orderData);//订单类型
//            log::debug("=================打印订单信息02==================================".$orderType);
//            log::debug("=================打印订单信息03==================================",$orderData->toArray());
//        dd($orderInfo,$orderData);
            if ($orderType == 'LR' || $orderType == 'HF' || $orderType == 'YK' || $orderType == 'MT' || $orderType == 'ZL') {
                $dataInfo = $orderData->trade;
            } elseif ($orderType == 'VC') {
                $dataInfo = $orderData->video;
            } elseif ($orderType == 'AT') {
                $dataInfo = $orderData->air;
            } elseif ($orderType == 'UB') {
                $dataInfo = $orderData->utility;
            } elseif ($orderType == 'SHOP') {
                $dataInfo = $orderData->lkshopOrder;
            } elseif ($orderType == 'MZL') {
                $dataInfo = $orderData->mobile;
            } elseif ($orderType == 'CLP' || $orderType == 'CLM') {
                $dataInfo = $orderData->convertLogs;
            } else {
//                log::debug("=================打印订单信息3-000000==================================".$orderType);
                return false;
            }
        } catch (\Exception $e) {
            report($e);
            throw new LogicException('类型错误：' . $e);
//            log::debug("=================打印订单信息3-111111==================================".$e);
        }
//
//        $consumer_uid = $dataInfo->user_id ?? $dataInfo->uid;
//        $description = $orderType;
//        $orderNo = $dataInfo->order_no;
        if (empty($dataInfo->order_no)){
            $orderNo = TradeOrder::where('oid',$orderId)->value('order_no');
        }else{
            $orderNo = $dataInfo->order_no;
        }
        return $orderNo;
    }

        //修改用户手机号
    public function updateUserPhone(Request $request)
    {
        $uid = $request->input('uid');
        $phone = $request->input('phone');
        $userInfo = User::where('id', $uid)->first();
        $phoneUser = User::where('phone', $phone)->first();

        if ($phoneUser) {
            return "该手机号已被uid=" . $phoneUser->id . " 的用户使用，请更换其他手机号";
        }
        if ($userInfo) {
            DB::beginTransaction();
            try {
                $userDataLogModel = new UserUpdatePhoneLogSd();
                $userDataLogModel->user_id = $uid;
                $userDataLogModel->time = time();
                $userDataLogModel->edit_to_phone = $userInfo->phone . '=>' . $phone;
                $userDataLogModel->save();

                $userInfo->phone = $phone;
                $userInfo->save();
                DB::commit();
            } catch (Exception $exception) {
                DB::rollBack();
//                throw $exception;
                return response()->json(['code' => 0, 'msg' => '修改失败']);
            }
            return response()->json(['code' => 1, 'msg' => '修改成功']);

        } else {
            return "<h4>这个uid=" . $uid . "的用户不存在</h4>";
        }

    }


    //对比用户资产和记录
    public function getUserAssetInfoAndLog(Request $request)
    {
        $uid = $request->input('uid');
        $user = User::where('id', $uid)->first();
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
    public function xfUserAssetFH(Request $request)
    {
        $uid = $request->input('uid');
        $amount = $request->input('amount');//资产数量
        $assType = $request->input('assType');//资产类型：usdt/iets

        $assData = Assets::where('uid', $uid)->where('assets_name', $assType)->first();
        if ($assData==''){
            dd($uid."用户资产记录不存在");
        }
        $assData->amount = $amount;
        if ($assData->save()) {
            return "账号解封成功";
        } else {
            return "账号解封失败";
        }

    }

    //初始化导入记录
    public function initDrOrderLog(Request $request)
    {
        $type = $request->input('type');// mch_order
        $order_id = $request->input('order_id');
        if ($order_id == '') {
            $order_id = 0;
        }
        echo "初始化导入记录";
        $logData = LkshopOrderLog::where('type', $type)->first();
        $logData->order_id = $order_id;
        if ($logData->save()) {
            echo "初始化成功，order_id=" . $order_id;
        } else {
            echo "初始化失败";
        }

    }

    //查看导入时间
    public function getAddOrderTime()
    {
        $data[] = LkshopOrderLog::where('type', 'mch_order')->value('order_id');
        $data[] = LkshopOrderLog::where('type', '1688_order')->value('order_id');

        dd($data);
    }

    //修改订单名
//    public function updateShopOrderName(Request $request){
//        $logData = DB::table('lkshop_order')->update(['name'=>'商户订单']);
//
//        dd($logData);
//
//    }

    //修改已导入订单的类型
//    public function updateShopDrLog(Request $request){
//        $description = $request->input('description');//lkshop_sh
//        $updat_description = $request->input('updat_description');
//        $logData = DB::table('lkshop_order')->where('description',$description)->update(['description'=>$updat_description]);
//
//        dd($logData);
//    }


//    public function clearShopOrderLog(){
//        $re1 = DB::table('lkshop_order')->truncate();
//        $re2 = DB::table('lkshop_order_log')->truncate();
//        dd($re1,$re2);
//    }

//初始化修改用户手机号记录
    public function clearUserPhoneUpdateLog()
    {
        $re1 = DB::table('user_update_phone_log')->truncate();
        dd($re1);
    }

//扣除用户商城积分
    public function kcUserShopJf(Request $request)
    {
        $userId = $request->input('uid');//用户uid
        $role = $request->input('role');
        $num = $request->input('num');

        if($userId && $role && $num ){
            //        var_dump($userId,$role,$num);
//        echo '扣除用户积分接口<br/><br/>参数：uid用户的uid<br/>role=1表示删除消费者积分，role=2表示删除商家积分<br/>num=要删除的积分<br/><br/>操作结果：<br/><br/>';
            $userInfo = Users::where('id', $userId)->first();

            if ($userInfo != '') {
//            echo "当前用户的消费积分：" . $userInfo->integral . "<br/>";
//            echo "当前用户的商家积分：" . $userInfo->business_integral . "<br/>";
                if ($role == 1) {
                    $userInfo->integral = $userInfo->integral - $num;
                    if ($userInfo->save()) {
                        $data[] = "扣除成功<br/>扣除uid=" . $userId . " 的用户消费者积分，" . $num . "积分<br/>";
                    }
                } elseif ($role == 2) {
                    $userInfo->business_integral = $userInfo->business_integral - $num;
                    if ($userInfo->save()) {
                        $data[] =  "扣除成功<br/>扣除uid=" . $userId . " 的用户商家积分，" . $num . "积分<br/>";
                    }
                } else {
                    $data[] =  '扣除积分失败<br/>';
                }
            } else {
                $data[] =  '该uid用户不存在<br/>';
            }
        }else{
            $data[] =  "参数错误<br/>";
        }

        $this->returnView($data,'myning-test');


    }

    //清空商城卡单处理
    public function setShopKdOrderId(Request $request)
    {
        $orderId = $request->input('orderId');
        if ($orderId) {
            echo '修改OrderId' . $orderId . '的记录<br/>';
            $orderInfo = Order::where('id', $orderId)->first();
            $orderInfo->line_up = 0;
            if ($orderInfo->save()) {
                echo '修改成功';
            } else {
                echo '修改失败';
            }

        } else {
            echo 'orderId不能为空';
        }


    }

    //批量修改商家信息表审核状态
    public function plUpdateBussStutas()
    {
        $data = array('is_status' => 2);
        $re = DB::table('business_data')->update($data);

        var_dump($re);
    }


    //同商城用户的uid
    public function updateLkShopUserId(Request $request)
    {
        $start = $request->input('start');
        $end = $request->input('end');
        ini_set("max_execution_time", 0);
        set_time_limit(0);
        $shopUserData = TtshopUser::get(['id','binding']);
        $i = 0;
        foreach ($shopUserData->toArray() as $v) {
            $userInfo = Users::where('phone', $v['binding'])->first();
            if ($userInfo != '') {
                $userInfo->shop_uid = $v['id'];
                $re = $userInfo->save();
                if ($re){
                    $i++;
                }

            }

        }

        dd($i);
        exit;
//
//
//        dump($start,$end);
//        if ($start=='' || $end=='') {
//            dd('没有传limit范围');
//        } else {
//            $shopUserData = TtshopUser::offset($start)->limit($end)->get(['id', 'binding']);
////            dd($shopUserData->toArray());
//            dd(count($shopUserData->toArray()));
//            $i = 0;
//            foreach ($shopUserData->toArray() as $v) {
//                $userInfo = Users::where('phone', $v['binding'])->first();
//                if ($userInfo != '') {
//                    $userInfo->shop_uid = $v['id'];
//                    $userInfo->save();
//                    $i++;
//                }
//
//            }
//
//            var_dump($i);
//        }



    }

    //修改用户商家身份
    public function updateUserInfoRole(Request $request){
        $phone = $request->input('phone');
        $role = $request->input('role');
        $userInfo = Users::where('phone',$phone)->first();
        if ($userInfo!=''){
            $userInfo->role = $role;
            if($userInfo->save()){
                dd('修改成功');
            }else{
                dd('修改失败');
            }
        }else{
            dd('用户不存在');
        }

    }

    //修改商家申请后没有插入商家表的记录
    public function insertUserBuinssData(Request $request){
        $uid = $request->input('uid');
        $business_apply_id = $request->input('business_apply_id');
        $main_business = $request->input('main_business');

        $businessApplyData = BusinessApply::where('id',$business_apply_id)->first();
        if ($businessApplyData){
            $businessDataModel = new BusinessData();
            $businessDataModel->uid = $uid;
            $businessDataModel->business_apply_id = $business_apply_id;
            $businessDataModel->contact_number = $businessApplyData->phone;
            $businessDataModel->address = $businessApplyData->address;
            $businessDataModel->name = $businessApplyData->name;
            $businessDataModel->status = 1;

            $businessDataModel->category_id = 2;
            $businessDataModel->is_status = 2;
            if ($businessApplyData->work){
                $businessDataModel->main_business = $businessApplyData->work;
            }else{
                $businessDataModel->main_business = $main_business;
            }
            if ($businessDataModel->save()){
                dd('添加商家信息成功');
            }else{
                dd('添加商家信息失败');
            }

        }else{
            dd('商家申请记录不存在');
        }





    }

    //test视图模板测试
    public function myningtest(){
        return view('test',['title' => '测试模板']);

    }

    public function getTable(Request $request){
        $data = $request->all();
        dd($data);
    }

    //视图弹框
    public function returnView($data,$url){
        echo "<style>
a{font-size: 20px;text-decoration:none;font-weight: 400;line-height: 1.42;position: relative;display: inline-block;margin-bottom: 0;padding: 6px 12px;cursor: pointer;-webkit-transition: all;transition: all;
    -webkit-transition-timing-function: linear;transition-timing-function: linear;-webkit-transition-duration: .2s;transition-duration: .2s;text-align: center;
    vertical-align: top;white-space: nowrap;color: #fff;border: 1px solid #ccc;border-radius: 3px;background-clip: padding-box;background: #aaaaf5 !important;width:100px;height: 32px;
}</style>";
        echo "<div style = 'text-align:center;margin: 100px auto;font-size: 20px'>";
//dd($data);
        foreach ($data as $v){
            echo $v."<br/>";
        }
        echo "<a href='$url'>返回</a>";
        echo "</div>";
    }

    //修改导入让利金额
    public function xgcount_profit_price(){
        //count_profit_price
        $data = OrderIntegralLkDistribution::where('id',37)->first();
        $data->count_profit_price = 81894.11;
        if ($data->save()){
            dd('修改成功');
        }else{
            dd('修改失败');
        }
    }

    //扣除用户积分  152087
//    public function kcUserJf(){
//        set_time_limit(0);
//        ini_set('max_execution_time', '0');
//
//        $jfData = IntegralLogs::where('id','>',152087)->get();
////        $jfData = IntegralLogs::where('id','=',332)->get();
//        $i = 0;
////        dd($jfData->toArray());
//
//        foreach ($jfData->toArray() as $k=>$v){
//
////            dd($v['description'],$v['order_no']);
//            $oid = $this->getOderIdByDescription($v['description'],$v['order_no']);
//
////            if ($v['description']=='LR'){
////                dump($oid.'--'.$v['description']);
////            }
////        dump($oid.'--'.$v['description']);
//
//            $userInfo = Users::where('id',$v['uid'])->first();
//            if ($v['role']==1){//扣除消费者积分
//                $userInfo->integral = $userInfo->integral-$v['amount'];
//                $userInfo->save();
//
//            }elseif ($v['role']==2){//扣除商家积分
//                $userInfo->business_integral = $userInfo->business_integral-$v['amount'];
//                $userInfo->save();
//            }
//            //改变订单排队状态
//            $orderInfo = Order::where('id',$oid)->first();
//            $orderInfo->line_up = 1;
//            $orderInfo->save();
//
//            //删除用户积分记录
//            IntegralLogs::where('id',$v['id'])->delete();
//
//            $i++;
//
//        }
//
//        var_dump($i);
//
//    }


    public function getOderIdByDescription($desc,$order_no){
        $Order =  new Order();
        try {
            $oid = '';
            switch ($desc){
                case 'MZL':
                $oid = (new OrderMobileRecharge())->where('order_no',$order_no)->value('order_id');
                break;
                case 'SHOP':
                $oid = (new LkshopOrder())->where('order_no',$order_no)->value('oid');
                break;
                case 'UB':
                $oid = (new OrderUtilityBill())->where('order_no',$order_no)->value('order_id');
                break;
                case 'AT':
                $oid = (new OrderAirTrade())->where('order_no',$order_no)->value('oid');
                break;
                case 'VC':
                $oid = (new OrderVideo())->where('order_no',$order_no)->value('order_id');
                break;
                default:
                $oid = (new TradeOrder())->where('order_no',$order_no)->value('oid');
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

//批量修改非商家的用户的商家身份为1
public function getUserOnShUpdate(){
    set_time_limit(0);
    ini_set('max_execution_time', '0');
        $userData = Users::where('role',2)->get();
//        dd(count($userData->toArray()));
    $i=0;
        foreach($userData->toArray() as $k=>$v){
            $userSh = BusinessData::where('uid',$v['id'])->first();
            if ($userSh==null){
                echo $v['id'].'<br/>';
                $userInfo = Users::where('id',$v['id'])->first();
                $userInfo->role =1;
                $userInfo->save();
                $i++;
            }


        }
        var_dump($i);

}

    //批量生成图片记录
    public function plInsertUserImages(){
        set_time_limit(0);
        ini_set('max_execution_time', '0');
        $count = BusinessData::count();
        $i = 0;$j = 0;
        $shData = BusinessData::get()->toArray();
        $userIdImgMode = new UserIdImg();
        foreach ($shData as $k=>$v){
            if(!UserIdImg::where('uid',$v['uid'])->where('business_apply_id',$v['business_apply_id'])->exists()){
                $data['uid']=$v['uid'];
                $data['business_apply_id']=$v['business_apply_id'];
                $userIdImgMode->create($data);
                $j++;
            }
            $i++;
        }
        dump($count,$i,$j);


}

    //修改商城导入时间
    public function setShopOrderTime(Request $request){
        $time = $request->input('time');
        if ($time < 1627200000 && $time!=''){
            dd("重置导入订单的时间不能小于 1627200000");
        }
        $mch_orderData = LkshopOrderLog::where('type','mch_order')->first();
        $a1688_orderData = LkshopOrderLog::where('type','1688_order')->first();
        $mch_orderData->order_id = $time;
        $a1688_orderData->order_id = $time;
        if ($mch_orderData->save() && $a1688_orderData->save()){
            dd('重置成功1111111111');
        }else{
            dd('重置失败0000000000');
        }

    }

    //批量修改商家门头照
    public function plUpdateUserShimg2(){
        set_time_limit(0);
        ini_set('max_execution_time', '0');

        $shDataCount = BusinessData::where('banners','!=','')->count();
        $shDataInfo = BusinessData::where('banners','!=','')->get();

        $i = 0;
        $errorShId = array();
        if ($shDataCount > 0){
            $businessApplyModel = new BusinessApply();
            foreach ($shDataInfo->toArray() as $k=>$v){
                $OneDaTa = $businessApplyModel::where('id',$v['business_apply_id'])->first();
                if ($OneDaTa){
                    $OneDaTa->img2 = $v['banners'];
                    if($OneDaTa->save()){
                        $i++;
                    }else{
                        $errorShId['id1'][] = $v['id'];
                    }
                }else{
                    $errorShId['id2'][] = $v['id'];
                }
            }
        }else{
            dd('没有相关记录');
        }
        dd($shDataCount,$i,$errorShId);


    }


    //修改用户会员状态
    public function setUserInfoMemberStatus(Request $request){
        $uid = $request->input('uid');
        $member_status = $request->input('member_status');
        $userInfo = Users::where('id',$uid)->first();
        if ($userInfo){
            $userInfo->member_status = $member_status;
            if($userInfo->save()){
                dd("用户".$uid."修改成功-".$member_status);
            }else{
                dd("用户".$uid."修改失败-".$member_status);
            }

        }else{
            dd("用户不存在！");
        }


    }









}







