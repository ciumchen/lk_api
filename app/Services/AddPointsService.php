<?php

namespace App\Services;

use App\Models\IntegralLogs;
use App\Models\User;
use Illuminate\Http\Request;

class AddPointsService
{
    /**
     * Description:
     *
     * @param  int     $uid
     * @param  float   $integral
     * @param  string  $type
     *
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/7/12 0012
     */
    public function addPoints($uid, $integral, $type, $operate_type)
    {
        try {
            $ip = (new Request())->getClientIp();
            $IntegralLog = new IntegralLogs();
            $IntegralLog->description = $type;
            $IntegralLog->operate_type = $operate_type;
            $IntegralLog->uid = $uid;
            $IntegralLog->amount = $integral;
            $IntegralLog->amount_before_change = $this->getUserPoints($uid);
            $IntegralLog->ip = $ip;
            $IntegralLog->save();
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Description:获取用户积分
     *
     * @param $uid
     *
     * @return \Illuminate\Support\HigherOrderCollectionProxy|mixed
     * @author lidong<947714443@qq.com>
     * @date   2021/7/12 0012
     */
    public function getUserPoints($uid)
    {
        try {
            $User = User::findOrFail($uid);
            $points = $User->integral;
        } catch (\Exception $e) {
            throw $e;
        }
        return $points;
    }
    
    public function addSignInPoints($uid, $integral)
    {
        try {
            $type = 'SNI';
            $operate_type = 'sign_in';
            $this->addPoints($uid, $integral, $type, $operate_type);
        } catch (\Exception $e) {
            throw $e;
        }
        return true;
    }
}
