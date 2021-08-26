<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\CardpayPassword;
use App\Services\UserGatherService;
use Illuminate\Http\Request;

class UserGatherController extends Controller
{
    /**判断用户是否设置购物卡密码
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function isSetupPwd(Request $request)
    {
        return (new CardpayPassword())->isSetupPwd($request->uid);
    }

    /**设置购物卡密码
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function addCardPwd(Request $request)
    {
        $data = $request->all();

        return (new CardpayPassword())->addCardPwd($data);
    }

    /**验证购物卡密码
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function provingCardPwd(Request $request)
    {
        $data = $request->all();

        return (new CardpayPassword())->provingCardPwd($data);
    }

    /**修改购物卡密码
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function editCardPwd(Request $request)
    {
        $data = $request->all();

        return (new UserGatherService())->editCardPwd($data);
    }
}
