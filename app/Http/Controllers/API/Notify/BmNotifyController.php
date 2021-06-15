<?php

namespace App\Http\Controllers\API\Notify;

use App\Http\Controllers\API\Message\UserMsgController;
use App\Http\Controllers\Controller;
use App\Models\RechargeLogs;
use App\Services\bmapi\VideoCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Description:
 *
 * Class BmNotifyController
 *
 * @package App\Http\Controllers\API\Notify
 * @author  lidong<947714443@qq.com>
 * @date    2021/6/11 0011
 */
class BmNotifyController extends Controller
{
    
    /**
     * Description:
     *
     * @param \Illuminate\Http\Request $request
     *
     * @author lidong<947714443@qq.com>
     * @date   2021/6/11 0011
     */
    public function videoNotify(Request $request)
    {
        $data = $request->all();
        Log::debug('VideoNotify-data:', [json_encode($data)]);
        try {
            $VideoService = new VideoCardService();
            $VideoService->notify($data);
            if ($data[ 'recharge_state' ] == 1) {
                $VideoService->updateRechargeLogs($data);
            }
        } catch (\Exception $e) {
            Log::debug('VideoNotify Error:' . $e->getMessage(), [json_encode($data)]);
            die('failed');
        }
        /* 发送消息 */
        /* TODO:看需求是否开启 */
//        (new UserMsgController())->setMsg($data[ 'outer_tid' ], 1);
        die('success');
    }
}
