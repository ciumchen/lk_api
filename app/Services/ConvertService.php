<?php

namespace App\Services;

use App\Models\ConvertLogs;
use Exception;
use Illuminate\Support\Facades\DB;

/** 兑换充值 **/

class ConvertService
{
    /**usdt 兑换话费
    * @param array $data
    * @return mixed
    * @throws
    */
    public function phoneBill(array $data)
    {
        DB::beginTransaction();
        try
        {
            $data['orderNo'] = createOrderNo();
            //插入数据到兑换记录
            $data['name'] = '';
            (new ConvertLogs())->setConvert($data);
    
            //插入数据到变动记录
            $data['remark'] = '兑换话费';
            (new ConvertLogs())->setAssetsLogs($data);

            //更新用户资产数据
            (new ConvertLogs())->updAssets($data);
        
            
        } catch (Exception $e)
        {
            throw $e;
            //dd($exception);
            DB::rollBack();
        }
        DB::commit();
    }
}