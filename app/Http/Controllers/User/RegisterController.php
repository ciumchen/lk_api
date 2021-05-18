<?php

namespace App\Http\Controllers\User;

use App\Exceptions\LogicException;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyCodesRequest;
use App\Models\Setting;
use App\Models\VerifyCode;
use Illuminate\Http\Request;


class RegisterController extends Controller
{
    /**注册页面
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function __invoke(Request $request)
    {
        $data['title'] = '注册';
        $data['invite_code'] = $request->get('invite_code');
        return view('register',$data);
    }

    /**APP下载
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function downloadApp(){
        $data['title'] = 'APP下载';


        $data['android_app'] = Setting::getSetting('android_app');
        $data['ios_app'] = Setting::getSetting('ios_app');
        return view('downloadApp',$data);
    }
}
