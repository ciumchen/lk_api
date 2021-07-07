<?php

namespace App\Services;

use App\Models\ConvertLogs;

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
        $data['name'] = '';
        (new ConvertLogs())->setConvert($data);

        $data['remark'] = '兑换话费';
        (new ConvertLogs())->setAssetsLogs($data);
    }
}