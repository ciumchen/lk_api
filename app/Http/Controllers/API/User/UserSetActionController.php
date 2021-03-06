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
use App\Models\UserUpdatePhoneLogSd;
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
class UserSetActionController extends Controller
{

    //手动清空用户lk--html页面
//    public function qkSetUserLkAction(Request $request){
//        $data['lkdj'] = Setting::where('key','lk_unit_price')->value('value');
//        $userData = Users::find([2,6,15873])->toArray();
////        dd($userData);
//        foreach ($userData as $k=>$v){
//            $userinfo[$k]['用户uid'] = $v['id'];
//            $userinfo[$k]['消费者积分'] = $v['integral'];
//            $userinfo[$k]['商家积分'] = $v['business_integral'];
//            $userinfo[$k]['消费者lk'] = $v['lk'];
//            $userinfo[$k]['商家lk'] = $v['business_lk'];
//        }
//        $data['userInfo'] = $userinfo;
////        dd($data);
//        return view('qkUserLk')->with('data',$data);
//    }
//

    public function qkSetOneUserLkAction(Request $request){
        $data['lkdj'] = Setting::where('key','lk_unit_price')->value('value');

        return view('qkOneUserLk')->with('data',$data);
    }

    //设置lk单价为0
    public function setUserLkdj(){
        $lkdj = Setting::where('key','lk_unit_price')->first();
        $lkdj->value = 0;
        $lkdj->save();
//        $this->returnView(array("设置lk单价为0，操作成功！"),'qkSetUserLkAction');
        $this->returnView(array("设置lk单价为0，操作成功！"),'qkSetOneUserLkAction');
    }

    //////////////////////////////////////////////////
    public function addPdUserjf(){
        return view('addPdUserjf');
    }

    public function setPdUserOrderNo(Request $request){
        $oid = $request->input('oid');
        $orderData = Order::where(['id'=>$oid,'line_up'=>1])->first();
//        dd($orderData);
        if (empty($orderData)){
            $str = "操作失败，排队订单不存在";
        }else{
            if ($orderData->description==''){
                $description = "LR";
            }else{
                $description = $orderData->description;
            }
            DB::beginTransaction();
            try {
                (new OrderService())->MemberUserOrder($oid,$description);
                $orderData->status = 2;
                $orderData->line_up = 0;
                $orderData->save();
                DB::commit();
                $str = "操作成功";
            } catch (Exception $e) {
                DB::rollBack();
                $str = "操作失败";
            }
        }
        $this->returnView(array($str),'addPdUserjf');
    }


//////////////////////////////////////////////////
    //接收清空uid
//    public function jsQkSetUserLk(){
//
//        //uid2和6消费者积分、商家积分、消费者lk、商家lk
//        DB::beginTransaction();
//        try {
//            $userData2 = Users::find(2);
//            $userData2->integral = 0;
//            $userData2->business_integral = 0;
//            $userData2->lk = 0;
//            $userData2->business_lk = 0;
//            $userData2->save();
//
//            $userData6 = Users::find(6);
//            $userData6->integral = 0;
//            $userData6->business_integral = 0;
//            $userData6->lk = 0;
//            $userData6->business_lk = 0;
//            $userData6->save();
//
//            $userData15873 = Users::find(15873);
//            $userData15873->integral = 0;
//            $userData15873->business_integral = 0;
//            $userData15873->lk = 0;
//            $userData15873->business_lk = 0;
//            $userData15873->save();
//            DB::commit();
//        } catch (Exception $exception) {
//            DB::rollBack();
////                throw $exception;
//            return response()->json(['code' => 0, 'msg' => '修改失败']);
//        }
//
//        $this->returnView(array("清空用户积分和lk，操作成功！"),'qkSetUserLkAction');
//
//    }

    //清空单个用户的积分和lk
    public function jsQkSetOneUserLk(Request $request){
        $uid = $request->input('uid');

        DB::beginTransaction();
        try {
            $userData = Users::find($uid);
            $userData->integral = 0;
            $userData->business_integral = 0;
            $userData->lk = 0;
            $userData->business_lk = 0;
            $userData->save();
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
//                throw $exception;
            return response()->json(['code' => 0, 'msg' => '修改失败']);
        }

        $this->returnView(array("清空用户".$uid."积分和lk，操作成功！"),'qkSetOneUserLkAction');

    }



    //视图弹框
    public function returnView($data, $url)
    {
        echo "<style>
a{font-size: 20px;text-decoration:none;font-weight: 400;line-height: 1.42;position: relative;display: inline-block;margin-bottom: 0;padding: 6px 12px;cursor: pointer;-webkit-transition: all;transition: all;
    -webkit-transition-timing-function: linear;transition-timing-function: linear;-webkit-transition-duration: .2s;transition-duration: .2s;text-align: center;
    vertical-align: top;white-space: nowrap;color: #fff;border: 1px solid #ccc;border-radius: 3px;background-clip: padding-box;background: #aaaaf5 !important;width:100px;height: 32px;
}</style>";
        echo "<div style = 'text-align:center;margin: 100px auto;font-size: 20px'>";
//dd($data);
        foreach ($data as $v) {
            echo $v . "<br/>";
        }
        echo "<a href='$url'>返回</a>";
        echo "</div>";
    }


}
