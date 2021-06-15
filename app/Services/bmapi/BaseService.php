<?php

namespace App\Services\bmapi;

use App\Models\RechargeLogs;
use Illuminate\Support\Facades\Log;

/**
 * Description:斑马力方回调更新充值记录表
 *
 * Class BaseService
 *
 * @package App\Services\bmapi
 * @author  lidong<947714443@qq.com>
 * @date    2021/6/11 0011
 */
class BaseService
{
    
    /**
     * Description:
     *
     * @param array  $data
     * @param string $type
     *
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/6/11 0011
     */
    public function updateRechargeLogs($data, $type)
    {
        try {
            $rechargeLogs = new RechargeLogs();
            $recharge = $rechargeLogs->where('reorder_id', '=', $data[ 'tid' ])
                                     ->first();
            if (!empty($recharge)) {
                $recharge->created_at = date("Y-m-d H:i:s");
                $recharge->updated_at = date("Y-m-d H:i:s");
                $recharge->save();
            } else {
                $recharge = $rechargeLogs;
                $recharge->reorder_id = $data[ 'tid' ];
                $recharge->order_no = $data[ 'outer_tid' ];
                $recharge->type = $type;
                $recharge->status = 1;
                $recharge->created_at = date("Y-m-d H:i:s");
                $recharge->updated_at = date("Y-m-d H:i:s");
                $recharge->save();
            }
        } catch (\Exception $e) {
            Log::debug('BmUpdateRechargeLogs', [json_encode($data)]);
            throw $e;
        }
        return $recharge;
    }
}
