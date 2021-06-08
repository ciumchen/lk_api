<?php

namespace App\Http\Controllers\API\Order;

use App\Http\Controllers\Controller;
use App\Services\bmapi\MobileRechargeService;
use Illuminate\Http\Request;
use Log;

class MobileNotifyController extends Controller
{
    
    //
    public function callback(Request $request)
    {
        $data = $request->all();
//        Log::debug('MobileNotify', [json_encode($data)]);
        /*
        {
        "user_id": "A5626842",
        "sign": "C0F9E3501C0DB8EBA781993D8268B073FBF9EE79",
        "recharge_state": "1",
        "outer_tid": "PY_20210605210408281427",
        "tid": "S2106052397812",
        "timestamp": "2021-06-05 21:05:12"
        }
        */
        try {
            $MobileRechargeService = new MobileRechargeService();
            $MobileRechargeService->notify($data);
        } catch (\Exception $e) {
            Log::debug('MobileNotify', [json_encode($data)]);
        }
    }
}
