<?php

namespace App\Http\Controllers\API\ThirdAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AlipayAuthController extends Controller
{
    //授权成功后同步回调
    /*TODO:保存用户ID和auth_code*/
    public function alipayAfterAuth(Request $request, $uid)
    {
        $data = $request->all();
        dump($data);
        dd($uid);
        return view('alipay-after-auth');
    }
    
    public function getAuthUrlH5(Request $request)
    {
        $user = $request->getUser();
//        dd($request->all());
        $uid = 9569;
        $return_url = route('h5-auth', ['uid' => $uid]);
        $return_data = [];
        $auth_data = [
            'app_id'       => config('alipay.app_id'),
            'scope'        => 'auth_user',
            'redirect_uri' => $return_url,
        ];
        $return_data[ 'open_url' ] = $open_url = 'https://openauth.alipay.com/oauth2/publicAppAuthorize.htm?'.urldecode(http_build_query($auth_data));
        $return_data[ 'click_url' ] = 'alipays://platformapi/startapp?appId=20000067&url='.urlencode($open_url);
        return apiSuccess($return_data);
    }
}
