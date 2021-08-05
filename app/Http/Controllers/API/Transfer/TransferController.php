<?php

namespace App\Http\Controllers\API\Transfer;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\AssetsType;
use App\Models\Setting;
use App\Models\User;
use App\Models\VerifyCode;
use App\Services\AddressService;
use App\Services\AssetsService;
use App\Services\TransferService;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    /**提现
     */
    public function __invoke(Request $request)
    {
        $this->validate($request, [
            'amount' => ['bail', 'required', 'numeric', 'regex:#\A(\d+)(.\d{0,8})?\z#', 'min:5', 'max:10000'],
            'address' => ['bail', 'required'],
            'verify_code' => ['bail', 'required'],
        ], [
            'amount.numeric' => '数量只能是数字',
            'amount.regex' => '数量格式不正确',
        ], [
            'amount' => '数量',
            'address' => '钱包地址',
            'verify_code' => '验证码',
        ]);

        $user = $request->user();

        if ($user->member_status == 0) {
            throw new LogicException('非来客会员无法提现，请购买来客会员!');
        }
        if (!$user->isVerifiedRealName()) {
            throw new LogicException('未完成实名认证.');
        }

        if (User::STATUS_NORMAL != $user->status) {
            throw new LogicException('账户异常');
        }

        if (!VerifyCode::check($user->phone, $request->verify_code, VerifyCode::TYPE_WITHDRAW_TO_WALLET)) {
            throw new LogicException('无效的验证码');
        }

        $withdrawBtn = Setting::getSetting('withdraw_btn') ?? 1;
        if (1 != $withdrawBtn) {
            throw new LogicException('系统维护，暂停提现');
        }

        $options = [
            'ip' => request_ip(),
            'user_agent' => (string) $request->userAgent(),
        ];

        //执行
        (new TransferService())->transfer($user, $request->amount, $request->address, $options);


        return response()->json(['code'=>1, 'msg'=>'划转成功']);
    }

    /**获取转账信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTransferInfo(Request $request)
    {
        $user = $request->user();

        //地址
        $data['address'] = Address::where("uid",$user->id)->value("address") ?? "";

        //余额
        $user = User::find($user->id);
        $assetsType = AssetsType::where("assets_name", AssetsType::DEFAULT_ASSETS_NAME)->first();
        $balance = AssetsService::getBalanceData($user, $assetsType);
        $data['amount'] = rtrim_zero($balance->amount ?? 0);
        $data['freeze_amount'] = rtrim_zero($balance->freeze_amount ?? 0);

        //手机号
        $data['phone'] = $user->phone;

        return response()->json(['code'=>0, 'data'=> $data]);

    }

    /**绑定地址
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\LogicException
     */
    public function bindAddress(Request $request)
    {
        $address = strtolower($request->input('address'));
        $user = $request->user();

        (new AddressService())->bindAddress($address, $user->id);

        return response()->json(['code'=>0, 'msg'=> "绑定成功"]);
    }

}
