<?php

namespace App\Services;

use App\Models\SignIn;

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
                throw new \Exception('缺少用户ID');
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
            $yesterday = $SignIn->where($where)->first();
            $total_num = $yesterday->total_num ?? 0;
            $total_num = $total_num >= self::$days_give_points_need ? 0 : $total_num;
        } catch (\Exception $e) {
            throw $e;
        }
        return $total_num;
    }
    
    /**
     * Description:签到调用接口
     *
     * @param          $uid
     * @param  int     $yx_uid
     * @param  string  $date
     *
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/7/9 0009
     */
    public function signIn($uid, $yx_uid = 0, $date = '')
    {
        $date = date('Y-m-d', (empty($date) ? time() : strtotime($date)));
        try {
            if ($uid == 0 && $yx_uid == 0) {
                throw new \Exception('缺少用户ID');
            }
            $yesterday_total_num = $this->getPreDayContinuousLoginTimes($uid, $yx_uid, $date);
            $total_num = $yesterday_total_num + 1;
            $SignIn = new SignIn();
            $SignIn->storeSignIn($uid, $yx_uid, $date, $total_num);
            if ($total_num >= self::$days_give_points_need) {
                /*达到或者超过*/
                $this->updateSignInAfterAddPoints($uid, $yx_uid, $date);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Description: 签到添加积分
     *
     * @param  int     $uid
     * @param  int     $yx_uid
     * @param  string  $date
     *
     * @author lidong<947714443@qq.com>
     * @date   2021/7/9 0009
     */
    public function updateSignInAfterAddPoints($uid, $yx_uid = 0, $date = '')
    {
        $date = date('Y-m-d', (empty($date) ? time() : strtotime($date)));
        try {
            $SignIn = new SignIn();
            /* 查询对应天数的签到记录并标记为已处理 */
            $where = [
                ['sign_date', '<', $date],
                ['is_add_points', '=', SignIn::IS_ADD_POINTS_DEFAULT],
            ];
            if ($uid != 0) {
                $where[] = ['uid', '=', $uid];
            } else {
                $where[] = ['yx_uid', '=', $yx_uid];
            }
            $waited_list = $SignIn->where($where)->orderByDesc('id')->limit(self::$days_give_points_need)->get();
            $sign_date = $SignIn->where($where)
                                ->orderByDesc('id')
                                ->limit(self::$days_give_points_need)
                                ->value('sign_date');
            return $sign_date;
            //TODO:添加积分
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
