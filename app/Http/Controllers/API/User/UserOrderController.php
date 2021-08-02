<?php

namespace App\Http\Controllers\API\User;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Services\bmapi\MobileRechargeService;
use Illuminate\Http\Request;

class UserOrderController extends Controller
{
    //
    public function batchMobileOrderDetails(Request $request)
    {
        $order_id = $request->input('order_id');
        $user = $request->user();
        try {
            $MobileService = new MobileRechargeService();
            $details = $MobileService->getBatchDetails(
                $order_id,
                $user->id,
                ['mobile', 'money', 'status', 'status_text']
            );
        } catch (\Exception $e) {
            throw new  LogicException($e->getMessage());
        }
        return apiSuccess($details);
    }
}
