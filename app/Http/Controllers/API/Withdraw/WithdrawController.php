<?php

namespace App\Http\Controllers\API\Withdraw;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WithdrawController extends Controller
{
    //
    public function setWithDrawOrder(Request $request)
    {
        $user = $request->user();
        $money = $request->input('money');
        try {
        } catch (\Exception $e) {
            throw new LogicException($e->getMessage());
        }
    }
}
