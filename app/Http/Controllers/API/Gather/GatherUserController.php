<?php

namespace App\Http\Controllers\API\Gather;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Models\GatherUsers;
use Illuminate\Http\Request;

class GatherUserController extends Controller
{
    /**获取用户拼团信息
     * @param Request $request
     * @return mixed
     * @throws LogicException
     */
    public function getGatherInfo (Request $request)
    {
        $data = $request->all();
        return (new GatherUsers())->getGatherInfo($data);
    }

    /**获取拼团中奖信息
     * @param Request $request
     * @return mixed
     * @throws LogicException
     */
    public function getGatherLottery (Request $request)
    {
        return (new GatherUsers())->getGatherLottery($request->gid);
    }
}
