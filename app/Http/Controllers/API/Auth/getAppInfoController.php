<?php

namespace App\Http\Controllers\API\Auth;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class getAppInfoController extends Controller
{
    /**
     * Description:APP引导页
     *
     * @return string
     * @throws \App\Exceptions\LogicException
     * @author lidong<947714443@qq.com>
     * @date   2021/7/13 0013
     */
    public function startPages()
    {
        try {
            $images = Setting::getSetting('app_start_pages');
            $images = explode('|', $images);
            $images = array_filter($images);
        } catch (\Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($images);
    }
}
