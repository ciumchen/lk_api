<?php

namespace App\Http\Controllers\API\ThirdAuth;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Models\UserAlipayAuthToken;
use Exception;
use Illuminate\Http\Request;
use App\Services\Alipay\AlipayCertService;
use function Symfony\Component\Translation\t;

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
    
    /**
     * Description:授权成功后同步回调
     *
     * @param Request $request
     * @param int     $uid
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     * @author lidong<947714443@qq.com>
     * @date   2021/8/9 0009
     */
    public function alipayAfterAuth(Request $request, $uid)
    {
        $data = $request->all();
        $data[ 'uid' ] = $uid;
        $AlipayCertService = new AlipayCertService();
        $status = true;
        $msg = '支付宝绑定成功';
        try {
            $AlipayCertService->saveUserAuthCode($data);
            $AlipayCertService->userBinding($uid);
        } catch (Exception $e) {
            $status = false;
            $msg = $e->getMessage();
        }
        return view('alipay-after-auth', ['auth_status' => $status, 'msg' => $msg]);
    }
    
    /**
     * Description:用户绑定查询
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \App\Exceptions\LogicException
     * @author lidong<947714443@qq.com>
     * @date   2021/8/9 0009
     */
    public function userBinding(Request $request)
    {
        try {
            $user = $request->user();
            $uid = $user->id;
            $AlipayCertService = new AlipayCertService();
            $res = $AlipayCertService->userBinding($uid);
            if (!$res) {
                throw new Exception('用户授权信息获取失败');
            }
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
    }
    
    /**
     * Description:用户绑定查询
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     * @throws \App\Exceptions\LogicException
     * @author lidong<947714443@qq.com>
     * @date   2021/8/9 0009
     */
    public function userBindingCheck(Request $request)
    {
        try {
            $user = $request->user();
            $uid = $user->id;
            $AlipayCertService = new AlipayCertService();
            $res = $AlipayCertService->userBindingCheck($uid);
            if (!$res) {
                throw new Exception('未绑定');
            }
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess('', '绑定成功');
    }
    
    /**
     * Description:获取用户授权最后记录
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     * @throws \App\Exceptions\LogicException
     * @author lidong<947714443@qq.com>
     * @date   2021/8/16 0016
     */
    public function getUserLastAuth(Request $request)
    {
        try {
            $user = $request->user();
            $AlipayCertService = new AlipayCertService();
            $res = $AlipayCertService->changeAlipayAuthCheck($user->id);
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess();
    }
}
