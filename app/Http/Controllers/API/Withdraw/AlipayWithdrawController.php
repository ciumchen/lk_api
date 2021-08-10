<?php

namespace App\Http\Controllers\API\Withdraw;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AlipayWithdrawController extends Controller
{
// 发起提现请求
    public function payUser(Request $request)
    {
        $user = $request->user();
        $channel = $request->input('channel');
        $withdraw_id = $request->input('withdraw_id');
        /*TODO:提现到支付宝*/
    }
    //接收返回
}
