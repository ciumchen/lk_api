<?php

namespace App\Http\Controllers\API\ThirdAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AlipayAuthController extends Controller
{
    //
    public function alipayAfterAuth()
    {
//        die('sssss');
        return view('alipay-after-auth');
    }
}
