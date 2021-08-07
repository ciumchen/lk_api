<?php

namespace App\Http\Controllers\API\ThirdAuth;

use App\Http\Controllers\Controller;
use App\Models\UserAlipayAuthToken;
use Illuminate\Http\Request;
use App\Services\Alipay\AlipayCertService;

class AlipayAuthController extends Controller
{
    /**
     * @var string
     */
    protected $service = AlipayCertService::class;
    
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
        $return_url = route('h5-auth', ['uid' => $uid]);
        $return_data = [];
        $return_data[ 'open_url' ] = (new $this->service)->getWebOpenUrl($return_url);
        $return_data[ 'click_url' ] = (new $this->service)->getH5ClickUrl($return_url);
        return apiSuccess($return_data);
    }
    
    //授权成功后同步回调
    /*TODO:保存用户ID和auth_code*/
    public function alipayAfterAuth(Request $request, $uid)
    {
        $data = $request->all();
        
        dump($data);
        dd($uid);
        return view('alipay-after-auth');
    }
    
    public function userBinding(Request $request)
    {
        $user = $request->user();
        $uid = $user->id;
        
    }
}
