<?php

namespace App\Http\Controllers\API\Message;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserMessage;

/** 用户充值消息通知 **/

class UserMsgController extends Controller
{
    /**插入消息
     * @param string $orderNo
     * @throws
     */
    public function setMsg(string $orderNo)
    {
        (new UserMessage())->setMsg($orderNo);
    }

    /**获取消息
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function getMsg(Request $request)
    {
        $data = $request->all();
        $uid = $data['uid'];

        return (new UserMessage())->getMsg($uid);
    }

    /**获取消息小红点
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function getReddot(Request $request)
    {
        $data = $request->all();
        $uid = $data['uid'];

        return (new UserMessage())->getReddot($uid);
    }

    /**删除消息小红点
     * @param Request $request
     * @throws
     */
    public function delReddot(Request $request)
    {
        $data = $request->all();
        $uid = $data['uid'];

        (new UserMessage())->delReddot($uid);
    }
}
