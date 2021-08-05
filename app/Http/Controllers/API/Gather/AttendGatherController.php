<?php

namespace App\Http\Controllers\API\Gather;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Models\Gather;
use Illuminate\Http\Request;

class AttendGatherController extends Controller
{
    /**获取拼团信息
     * @param Request $request
     * @return mixed
     * @throws LogicException
     */
    public function getGatherInfo ()
    {
        //返回
        return (new Gather())->getGatherInfo();
    }

    /**参加拼团
     * @param Request $request
     * @return mixed
     * @throws LogicException
     */
    public function addGatherUser (Request $request)
    {
        //判断用户金额
        //判断用户当天次数，没人每天最多30次
    }
}
