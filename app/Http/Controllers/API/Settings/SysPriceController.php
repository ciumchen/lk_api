<?php

namespace App\Http\Controllers\API\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

/** 获取后台设置充值金额 **/

class SysPriceController extends Controller
{
    /**获取充值金额
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function getSysPrice(Request $request)
    {
        //获取数据
        return (new Setting())->getSysPrice($request->type);
    }
}
