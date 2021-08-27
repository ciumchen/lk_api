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
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function addUsereIncome(AdvertRequest $request)
    {
        $data = $request->all();

        return (new AdvertIsementService())->addUsereIncome($data);
    }
}
