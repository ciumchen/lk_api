<?php

namespace App\Services;

use App\Models\IntegralLogs;
use App\Models\Setting;
use App\Models\SignIn;
use App\Models\TtshopUser;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class SignInService
{
    protected static $days_give_points_need = 7;
    
    /**
     * Description: 获取前一天连续登录次数
     *
     * @param  int     $uid
     * @param  string  $date
     *
     * @return \Illuminate\Database\Eloquent\HigherOrderBuilderProxy|int|mixed
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/7/9 0009
     */
    public function getPreDayContinuousLoginTimes($uid = 0, $yx_uid = 0, $date = '')
    {
        $date = date('Y-m-d', strtotime('-1 day', (empty($date) ? time() : strtotime($date))));
        try {
            if ($uid == 0 && $yx_uid == 0) {
                throw new Exception('缺少用户ID');
            }
            $SignIn = new SignIn();
            $where = [
                ['sign_date', '=', $date],
            ];
            if ($uid != 0) {
                $where[] = ['uid', '=', $uid];
            } else {
                $where[] = ['yx_uid', '=', $yx_uid];
            }
//            DB::connection()->enableQueryLog();
            $yesterday = $SignIn->where($where)
                                ->first();
//            $sql_msg = DB::getQueryLog();
//            dump($sql_msg);
//            dd($yesterday);
            $total_num = $yesterday->total_num ?? 0;
        } catch (Exception $e) {
            throw $e;
        }
        return $total_num;
    }
    
    /**
     * Description:通过优选商城用户ID获取用户来客信息
     *
     * @param $yx_uid
     *
     * @return \App\Models\User|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/7/12 0012
     */
    public function getUserLaike($yx_uid)
    {
        try {
            if (intval($yx_uid) <= 0) {
                throw new Exception('用户ID参数不可为空');
            }
            $YxUser = TtshopUser::findOrFail($yx_uid);
            if (empty($YxUser)) {
                throw new Exception('用户数据为空');
            }
            $User = User::wherePhone($YxUser->binding)->first();
        } catch (Exception $e) {
            throw $e;
        }
        return $User;
    }
    
    /**
     * Description:签到调用接口
     *
     * @param  int     $yx_uid
     * @param  string  $date
     *
     * @throws \Exception|\Throwable
     * @author lidong<947714443@qq.com>
     * @date   2021/7/9 0009
     */
    public function yxSignIn($yx_uid = 0, $date = '')
    {
        $date = date('Y-m-d', (empty($date) ? time() : strtotime($date)));
        DB::beginTransaction();
        try {
            if ($yx_uid == 0) {
                throw new Exception('缺少用户ID');
            }
            $User = $this->getUserLaike($yx_uid);
            if (empty($User)) {
                throw new Exception('未找到对应用户');
            }
            $uid = $User->id;
            $yesterday_total_num = $this->getPreDayContinuousLoginTimes($uid, $yx_uid, $date);
//            $total_num = $yesterday_total_num >= self::$days_give_points_need ? 0 : $yesterday_total_num;
            $total_num = $yesterday_total_num + 1;
            $SignIn = new SignIn();
            $SignInInfo = $SignIn->where('uid', '=', $uid)
                                 ->where('sign_date', '=', $date)
                                 ->first();
            if (!empty($SignInInfo)) {
                throw new Exception('已经签过到了');
            }
            $todayInfo = $SignIn->storeSignIn($uid, $yx_uid, $date, $total_num);

            if ($total_num >= self::$days_give_points_need) {
                /*达到或者超过*/
                $this->updateSignInAfterAddPoints($uid, $date);
                $todayInfo->is_add_points = SignIn::IS_ADD_POINTS_ADDED;
                $todayInfo->save();
            }
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();
        return true;
    }
    
    /**
     * Description: 签到添加积分
     *
     * @param  int     $uid
     * @param  string  $date
     *
     * @throws \Exception|\Throwable
     * @author lidong<947714443@qq.com>
     * @date   2021/7/9 0009
     */
    public function updateSignInAfterAddPoints($uid, $date = '')
    {
        $date = date('Y-m-d', (empty($date) ? time() : strtotime($date)));
        DB::beginTransaction();
        try {
            $SignIn = new SignIn();
            /* 查询对应天数的签到记录并标记为已处理 */
            $where = [
                ['sign_date', '<', $date],
                ['is_add_points', '=', SignIn::IS_ADD_POINTS_DEFAULT],
                ['uid', '=', $uid],
            ];
            $sign_date = [$date];
            $waited_list = $SignIn->where($where)
                                  ->orderByDesc('id')
                                  ->limit(self::$days_give_points_need - 1)
                                  ->get()
                                  ->each(function (&$item) use (&$sign_date) {
                                      $sign_date[] = $item->sign_date;
                                  });
            if (empty($waited_list)) {
                return true;
            }
            $expected_dates = $this->getPreSomeDays(self::$days_give_points_need - 1, $date);
            $res = array_diff($sign_date, $expected_dates);
            if (!empty($res)) {
                throw new Exception('非连续登录:'.json_encode($expected_dates));
            }
            $waited_list->each(function ($item) {
                $item->is_add_points = 1;
                $item->save();
            });
            /*添加积分*/
            $this->addPoints($uid, Setting::getSetting('sign_points'));
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();
        return true;
    }
    
    /**
     * Description:获取前几天的日期数组
     *
     * @param  int     $num
     * @param  string  $date
     *
     * @return array
     * @author lidong<947714443@qq.com>
     * @date   2021/7/12 0012
     */
    public function getPreSomeDays($num, $date = '')
    {
        $dates = [];
        for ($i = $num; $i >= 0; $i--) {
            $dates[] = date('Y-m-d', strtotime("-{$i} days", strtotime($date)));
        }
        return $dates;
    }
    
    /**
     * Description:添加积分
     *
     * @param                         $uid
     * @param                         $integral
     * @param  \App\Models\User|null  $User
     *
     * @return bool
     * @throws \Throwable
     * @author lidong<947714443@qq.com>
     * @date   2021/7/12 0012
     */
    public function addPoints($uid, $integral, User $User = null)
    {
        if (empty($User)) {
            $User = User::findOrFail($uid);
        }
        DB::beginTransaction();
        try {
            /*修改用户积分 `integral`*/
            $User->integral = $User->integral + $integral;
            $User->save();
            /*添加积分记录*/
            $AddPointsService = new AddPointsService();
            $AddPointsService->addSignInPoints($uid, $integral);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();
        return true;
    }
}
