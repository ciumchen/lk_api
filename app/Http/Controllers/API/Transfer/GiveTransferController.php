<?php

namespace App\Http\Controllers\Api\Transfer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exceptions\LogicException;
use App\Models\Address;
use App\Models\AssetsType;
use App\Models\Setting;
use App\Models\User;
use App\Models\VerifyCode;
use App\Services\AddressService;
use App\Services\AssetsService;
use App\Services\TransferService;
use App\Services\GiveTransferService;
class GiveTransferController extends Controller
{
    /**赠送
     */
    public function __invoke(Request $request)
    {
        $this->validate($request, [
            'amount' => ['bail', 'required', 'numeric', 'regex:#\A(\d+)(.\d{0,8})?\z#', 'min:5', 'max:10000'],
            'phone' => ['bail', 'required'],
            'verify_code' => ['bail', 'required'],
        ], [
            'amount.numeric' => '数量只能是数字',
            'amount.regex' => '数量格式不正确',
        ], [
            'amount' => '数量',
            'phone' => '手机号',
            'verify_code' => '验证码',
        ]);
        $phone = $request->input('phone');
        if(preg_match('/^1[3-9]\d{9}$/', $phone)!=1){
            throw new LogicException('手机号格式不合法');
        }

//        $userInfo = User::where('phone',$phone)->value('market_business');
        $userInfo = User::where('phone',$phone)->first();
        if ($userInfo==null){
            throw new LogicException('这个手机号的用户不存在');
        }
        if ($userInfo->market_business!=1){
            throw new LogicException('这个手机号的用户不是市商');
        }

        $user = $request->user();
//dd($user);
        if (User::STATUS_NORMAL != $user->status) {
            throw new LogicException('账户异常');
        }

//        if (!VerifyCode::check($user->phone, $request->verify_code, VerifyCode::TYPE_WITHDRAW_TO_WALLET)) {
//            throw new LogicException('无效的验证码');
//        }
//
//        $withdrawBtn = Setting::getSetting('withdraw_btn') ?? 1;
//        if (1 != $withdrawBtn) {
//            throw new LogicException('系统维护，暂停提现');
//        }

        $options = [
            'ip' => request_ip(),
            'user_agent' => (string) $request->userAgent(),
        ];

        //执行
        (new GiveTransferService())->transfer($user, $request->amount, $phone, $options);

        return response()->json(['code'=>1, 'msg'=>'赠送成功，大额赠送请等待审核']);
    }

//    /**获取转账信息
//     * @param Request $request
//     * @return \Illuminate\Http\JsonResponse
//     */
//    public function getTransferInfo(Request $request)
//    {
//        $user = $request->user();
//
//        //地址
//        $data['address'] = Address::where("uid",$user->id)->value("address") ?? "";
//
//        //余额
//        $user = User::find($user->id);
//        $assetsType = AssetsType::where("assets_name", AssetsType::DEFAULT_ASSETS_NAME)->first();
//        $balance = AssetsService::getBalanceData($user, $assetsType);
//        $data['amount'] = rtrim_zero($balance->amount ?? 0);
//        $data['freeze_amount'] = rtrim_zero($balance->freeze_amount ?? 0);
//
//        //手机号
//        $data['phone'] = $user->phone;
//
//        return response()->json(['code'=>0, 'data'=> $data]);
//
//    }
//
//    /**绑定地址
//     * @param Request $request
//     * @return \Illuminate\Http\JsonResponse
//     * @throws \App\Exceptions\LogicException
//     */
//    public function bindAddress(Request $request)
//    {
//        $address = strtolower($request->input('address'));
//        $user = $request->user();
//
//        (new AddressService())->bindAddress($address, $user->id);
//
//        return response()->json(['code'=>0, 'msg'=> "绑定成功"]);
//    }

}
