<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\MyShare;
use Illuminate\Http\Request;

/** 我的分享 **/

class MyShareController extends Controller
{
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
}
