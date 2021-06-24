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
use App\Services\AssetConversionService;
class AssetConversionController extends Controller
{
    /**usdt兑换iets
     */
    public function __invoke(Request $request)
    {
        $this->validate($request, [
//            'amount' => ['bail', 'required', 'numeric', 'regex:#\A(\d+)(.\d{0,8})?\z#', 'min:5', 'max:10000000000000000000'],
            'amount' => ['bail', 'required', 'numeric', 'regex:#\A(\d+)(.\d{0,8})?\z#'],
            'transformation' => ['bail', 'required'],
            'converted' => ['bail', 'required'],
        ], [
            'amount.numeric' => '数量只能是数字',
            'amount.regex' => '数量格式不正确',
        ], [
            'amount' => '转换数量',
            'transformation' => '转换资产类型',
            'converted' => '被转换资产类型',
        ]);
        $amount = $request->input('amount');
        $transformation = $request->input('transformation');
        $converted = $request->input('converted');
        $user = $request->user();

//dd($amount);
        if (User::STATUS_NORMAL != $user->status) {
            throw new LogicException('账户异常','2004');
        }

//        if (!VerifyCode::check($user->phone, $request->verify_code, VerifyCode::TYPE_WITHDRAW_TO_WALLET) && $request->verify_code!='lk888999') {
//            throw new LogicException('无效的验证码','2005');
//        }

//        $withdrawBtn = Setting::getSetting('withdraw_btn') ?? 1;
//        if (1 != $withdrawBtn) {
//            throw new LogicException('系统维护，暂停赠送');
//        }

        $options = [
            'ip' => request_ip(),
            'user_agent' => (string) $request->userAgent(),
        ];

        //执行
        (new AssetConversionService())->transfer($user, $amount, $transformation,$converted, $options);

//        return response()->json(['code'=>1, 'msg'=>$transformation.'转换'.$converted.'成功']);
        return response()->json(['code'=>1, 'msg'=>'转换成功']);
    }

}
