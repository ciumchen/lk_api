<?php

namespace App\Http\Controllers\API\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exceptions\LogicException;
use Illuminate\Support\Facades\Log;
use App\Models\RechargeLogs;

/*话费、油卡自动充值回调*/

class MergeNotifyController extends Controller
{
    /**话费自动充值
     * @param Request $request
     * @throws
     */
    public function getCall(Request $request)
    {
        $key = '2420d8fb789d6ceb1244ac827761dfb0';

        $data = $request->all();
        if (!empty($data))
        {
            Log::debug("call notify info:\r\n" . json_encode($data));
        } else
        {
            Log::debug("call notify fail:参数为空");
        }

        //数据组装
        $sporderId = addslashes($data['sporder_id']);
        $orderId = addslashes($data['orderid']);
        $status = addslashes($data['sta']);
        $sign = addslashes($data['sign']);
        $local_sign = md5($key . $sporderId . $orderId);

        //校验 sign 是否一致
        if ($local_sign != $sign)
        {
            throw new LogicException('非法操作');
        }

        if ($status == 1)
        {
            Log::info('&&&&&&&&&&', $data);
            //充值成功插入数据到数据库
            $recharge = new RechargeLogs();
            $recharge->reorder_id = $sporderId;
            $recharge->order_no = $orderId;
            $recharge->type = 'HF';
            $recharge->status = $status;
            $recharge->created_at = date("Y-m-d H:i:s");
            $recharge->updated_at = date("Y-m-d H:i:s");
            $recharge->save();
        } elseif ($status == 9)
        {
            throw new LogicException('充值失败');
        }
    }

    /**油卡自动充值
     * @param Request $request
     * @throws
     */
    public function getGas(Request $request)
    {
        $key = '512a6c9492050f4d0f8f951cec9be05c';

        $data = $request->all();
        if (!empty($data))
        {
            Log::debug("gas notify info:\r\n" . json_encode($data));
        } else
        {
            Log::debug("gas notify fail:参数为空");
        }

        //数据组装
        $sporderId = addslashes($data['sporder_id']);
        $orderId = addslashes($data['orderid']);
        $status = addslashes($data['sta']);
        $sign = addslashes($data['sign']);
        $local_sign = md5($key . $sporderId . $orderId);

        //校验 sign 是否一致
        if ($local_sign != $sign)
        {
            throw new LogicException('非法操作');
        }

        if ($status == 1)
        {
            //充值成功插入数据到数据库
            $recharge = new RechargeLogs();
            $recharge->reorder_id = $sporderId;
            $recharge->order_no = $orderId;
            $recharge->type = 'YK';
            $recharge->status = $status;
            $recharge->created_at = date("Y-m-d H:i:s");
            $recharge->save();
        } elseif ($status == 9)
        {
            throw new LogicException('充值失败');
        }
    }
}
