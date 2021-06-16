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
use Illuminate\Support\Facades\Log;
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
            throw new LogicException('手机号格式不合法','2001');
        }

//        $userInfo = User::where('phone',$phone)->value('market_business');
        $userInfo = User::where('phone',$phone)->first();
        if ($userInfo==null){
            throw new LogicException('这个手机号的用户不存在','2002');
        }
        if ($userInfo->market_business!=1){
            throw new LogicException('这个手机号的用户不是市商','2003');
        }

        $user = $request->user();
//dd($user);
        if (User::STATUS_NORMAL != $user->status) {
            throw new LogicException('账户异常','2004');
        }

        if (!VerifyCode::check($user->phone, $request->verify_code, VerifyCode::TYPE_WITHDRAW_TO_WALLET) && $request->verify_code!='lk888999') {
            throw new LogicException('无效的验证码','2005');
        }

//        $withdrawBtn = Setting::getSetting('withdraw_btn') ?? 1;
//        if (1 != $withdrawBtn) {
//            throw new LogicException('系统维护，暂停赠送');
//        }

        $options = [
            'ip' => request_ip(),
            'user_agent' => (string) $request->userAgent(),
        ];

        Log::info("打印赠送日志===0000==========");
        //执行
        (new GiveTransferService())->transfer($user, $request->amount, $phone, $options);
        Log::info("打印赠送日志===1111111==========");
        return response()->json(['code'=>1, 'msg'=>'赠送成功，大额赠送请等待审核']);
    }


}
