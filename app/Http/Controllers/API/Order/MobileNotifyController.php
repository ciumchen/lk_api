<?php

namespace App\Http\Controllers\API\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Log;

class MobileNotifyController extends Controller
{
    
    //
    public function callback(Request $request)
    {
        $data = $request->all();
        Log::debug('MobileNotify', [json_encode($data)]);
    }
}
