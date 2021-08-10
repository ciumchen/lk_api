<?php

namespace App\Http\Controllers\API\Gather;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Models\Gather;
use App\Models\GatherGoldLogs;
use App\Services\GatherService;
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
        return (new Gather())->getGatherList();
    }

    /**参加拼团
     * @param Request $request
     * @return mixed
     * @throws LogicException
     */
    public function addGatherUser (Request $request)
    {
        $gid = $request->gid;
        $uid = $request->uid;

        //返回
        return (new GatherService())->addGatherUser($gid, $uid);
    }

    /**获取用户来拼金
     * @param Request $request
     * @return mixed
     * @throws LogicException
     */
    public function getGatherGold (Request $request)
    {
        return (new GatherGoldLogs())->getGatherGold($request->uid, $request->page, $request->perPage);
    }
}
