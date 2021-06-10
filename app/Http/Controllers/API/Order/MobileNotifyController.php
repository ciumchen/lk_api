<?php

namespace App\Http\Controllers\API\Order;

use App\Http\Controllers\API\Message\UserMsgController;
use App\Http\Controllers\Controller;
use App\Models\RechargeLogs;
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
            if ($data[ 'recharge_state' ] == 1) {
                $recharge = new RechargeLogs();
                $recharge = $recharge->where('reorder_id', '=', $data[ 'tid' ])
                                     ->first();
                if (!empty($recharge)) {
                    $recharge->created_at = date("Y-m-d H:i:s");
                    $recharge->updated_at = date("Y-m-d H:i:s");
                    $recharge->save();
                } else {
                    $recharge = new RechargeLogs();
                    $recharge->reorder_id = $data[ 'tid' ];
                    $recharge->order_no = $data[ 'outer_tid' ];
                    $recharge->type = 'HF';
                    $recharge->status = 1;
                    $recharge->created_at = date("Y-m-d H:i:s");
                    $recharge->updated_at = date("Y-m-d H:i:s");
                    $recharge->save();
                }
            }
            (new UserMsgController())->setMsg($data[ 'outer_tid' ], 1);
        } catch (\Exception $e) {
            Log::debug('MobileNotify', [json_encode($data)]);
            die('failed');
        }
        die('success');
    }
}
