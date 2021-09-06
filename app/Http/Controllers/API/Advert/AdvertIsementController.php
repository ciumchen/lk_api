<?php

namespace App\Http\Controllers\API\Advert;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdvertRequest;
use App\Models\AdvertUsers;
use App\Services\AdvertIsementService;
use Illuminate\Http\Request;

class AdvertIsementController extends Controller
{
    /**新增用户广告收入记录
     * @param AdvertRequest $request
     * @return mixed
     * @throws
     */
    public function addUsereIncome(AdvertRequest $request)
    {
        $data = $request->all();

        return (new AdvertIsementService())->addUsereIncome($data);
    }

    /**获取用户广告奖励金额
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function getUsereAdvert(Request $request)
    {
        return (new AdvertUsers())->getUserAward($request->uid);
    }

    /**新增用户广告奖励兑换记录
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function addTakeAward(Request $request)
    {
        return (new AdvertIsementService())->addTakeAward($request->uid);
    }

    /**新增拼团广告记录
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function addGatherAdvert(Request $request)
    {
        $data = $request->all();

        return (new AdvertIsementService())->addGatherAdvert($data);
    }
}
