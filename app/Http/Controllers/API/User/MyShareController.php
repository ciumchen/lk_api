<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\MyShare;
use Illuminate\Http\Request;

/** 我的分享 **/

class MyShareController extends Controller
{
    /**判断用户是否为盟主
     * @param Request $request
     * @return mixed
     * @throws    
    */
    public function isManage(Request $request)
    {
        $data = $request->all();
        //返回
        return (new MyShare())->isManage($data);
    }

    /**用户个人分享
     * @param Request $request
     * @return mixed
     * @throws    
    */
    public function userShare(Request $request)
    {
        $data = $request->all();
        //返回
        return (new MyShare())->userShare($data);
    }

    /**商家用户分享
     * @param Request $request
     * @return mixed
     * @throws    
    */
    public function shopShare(Request $request)
    {
        $data = $request->all();
        //返回
        return (new MyShare())->shopShare($data);
    }

    /**用户团员资产记录
     * @param Request $request
     * @return mixed
     * @throws    
    */
    public function usersAssets(Request $request)
    {
        $data = $request->all();
        //返回
        return (new MyShare())->usersAssets($data);
    }

    /**用户团长资产记录
     * @param Request $request
     * @return mixed
     * @throws    
    */
    public function headsAssets(Request $request)
    {
        $data = $request->all();
        //返回
        return (new MyShare())->headsAssets($data);
    }

    /**团长团队资产记录
     * @param Request $request
     * @return mixed
     * @throws    
    */
    public function teamAssets(Request $request)
    {
        $data = $request->all();
        //返回
        return (new MyShare())->teamAssets($data);
    }

    //团长
    //operate_type=invite_rebate
    //operate_type=share_b_rebate&remark=邀请商家，获得盈利返佣

    //operate_type=share_b_rebate&remark=邀请商家盟主分红
    //operate_type=share_b_rebate&remark=同级别盟主奖励
}
