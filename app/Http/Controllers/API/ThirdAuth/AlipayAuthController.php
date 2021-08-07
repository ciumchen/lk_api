<?php

namespace App\Http\Controllers\API\ThirdAuth;

use App\Http\Controllers\Controller;
use App\Models\UserAlipayAuthToken;
use Illuminate\Http\Request;
use App\Services\Alipay\AlipayCertService;

class AlipayAuthController extends Controller
{
    /**
     * Description:H5获取授权链接
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     * @author lidong<947714443@qq.com>
     * @date   2021/8/7 0007
     */
    public function getAuthUrlH5(Request $request)
    {
        $user = $request->user();
        $uid = $user->id;
        $AlipayCertService = new AlipayCertService();
        $return_url = route('h5-auth', ['uid' => $uid]);
        $return_data = [];
        $return_data[ 'open_url' ] = $AlipayCertService->getWebOpenUrl($return_url);
        $return_data[ 'click_url' ] = $AlipayCertService->getH5ClickUrl($return_url);
        return apiSuccess($return_data);
    }
    
    //授权成功后同步回调
    /*TODO:保存用户ID和auth_code*/
    public function alipayAfterAuth(Request $request, $uid)
    {
        $data = $request->all();
        $data = [
            'uid'       => 9596,
            'auth_code' => '78a5ac5bac61469b8947589de7a2SX92',
            'app_id'    => '2021002166656043',
            'source'    => 'alipay_wallet',
            'scope'     => 'auth_user',
        ];
        $AlipayCertService = new AlipayCertService();
        $AlipayCertService->saveUserAuthCode($data);
        dump($data);
        dd($uid);
        return view('alipay-after-auth');
    }
    
    public function userBinding(Request $request)
    {
        $user = $request->user();
        $uid = $user->id;
        $AlipayCertService = new AlipayCertService();
        $res = $AlipayCertService->userBinding($uid);
        dd($res);
    }
}
