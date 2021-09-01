<?php

namespace App\Services;

use App\Models\AssetsType;
use App\Models\Order;
use App\Models\User;

/**
 * Description:用户返利相关逻辑
 *
 * Class UserRebateService
 *
 * @package App\Services
 * @author  lidong<947714443@qq.com>
 * @date    2021/9/1 0001
 */
class UserRebateService
{
    /* 分享佣金 */
    public function shareScale(Order $order, User $user, AssetsType $assetsType, $platformUid = 0)
    {
        if (intval($platformUid) == 0) {
            $platformUid = SystemService::$platform_id;
        }
        try {
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /* 邀请人是会员时返利计算 */
    public function memberInviterScale()
    {
    }
    
    /* 邀请人是银卡时返利计算  */
    public function silverInviterScale()
    {
    }
    
    /* 邀请人是金卡时返利计算  */
    public function goldInviterScale()
    {
    }
    
    /* 邀请人是钻石卡时返利计算  */
    public function diamondInviterScale()
    {
    }
    
    /* 银卡上级返利计算 */
    public function silverHigherScale()
    {
    }
    
    /* 金卡上级返利计算 */
    public function goldHigherScale()
    {
    }
    
    /* 钻石卡上级返利计算 */
    public function diamondHigherScale()
    {
    }
    
    /* 无上级调用平台返利计算 */
    public function platformScale()
    {
    }
    
    /* 同级奖励 */
    public function sameLevel()
    {
    }
    
    /* 每日加权平分奖累加 */
    public function weightRewards()
    {
//        每日累加
//        累加记录
    }
}
