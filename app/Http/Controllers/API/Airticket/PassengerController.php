<?php

namespace App\Http\Controllers\API\Airticket;

use App\Http\Controllers\Controller;
use App\Models\AirPassenger;
use Illuminate\Http\Request;

/* 乘客信息 */

class PassengerController extends Controller
{
    /** 保存乘客信息
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function setPassenger(Request $request)
    {
        $data = $request->all();
        (new AirPassenger())->setPassenger($data);
    }

    /** 获取乘客信息
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function getPassenger(Request $request)
    {
        return (new AirPassenger())->getPassenger($request->uid);
    }

    /** 删除乘客信息
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function delPassenger(Request $request)
    {
        return (new AirPassenger())->delPassenger($request->id);
    }

    /** 更新乘客信息
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function savePassenger(Request $request)
    {
        $data = $request->all();
        return (new AirPassenger())->savePassenger($data);
    }
}
