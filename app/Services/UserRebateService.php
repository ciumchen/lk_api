<?php

namespace App\Services;

use App\Models\AssetsType;
use App\Models\Order;
use App\Models\User;
use App\Models\UserLevel;
use App\Models\UserLevelRelation;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
    /**
     * Description:获取等级规则缓存数据
     *
     * @return array|mixed
     * @author lidong<947714443@qq.com>
     * @date   2021/9/2 0002
     */
    public static function getLevelCache()
    {
        if (!Cache::get('level')) {
            $list = UserLevel::all()->pluck(null, 'id')->toArray();
            Cache::add('level', $list, 60 * 5);
        } else {
            $list = Cache::get('level');
        }
        return $list;
    }
    
    /* 分享佣金 */
    public function shareScale(Order $order, User $user, AssetsType $assetsType, $platformUid = 0)
    {
        if (intval($platformUid) == 0) {
            $platformUid = SystemService::$platformId;
        }
        try {
            $userLevelInfo = UserLevelRelation::whereUserId($user->id)->first();
            /* 获取所有上级 */
            $allParent = $this->getAllParentsLevel($userLevelInfo->pid_route);
            /* 获取直接上级 */
            $parent = $this->getParentByInviteId($allParent, $userLevelInfo->invite_id);
            /* 上级分佣 */
            switch ($parent[ 'level_id' ]) {
                case SystemService::$memberLevelID:
                case SystemService::$vipLevelID:
                    $this->memberInviterScale($order, $user, $assetsType, $platformUid, $userLevelInfo, $parent,
                                              $allParent);
                    break;
                case SystemService::$silverLevelId:
                    break;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /* 邀请人是会员时返利计算 */
    public function memberInviterScale(
        Order $order,
        User $user,
        AssetsType $assetsType,
        $platformUid = 0,
        $userLevelInfo = null,
        $parent = [],
        $allParent = []
    ) {
        $LevelCache = self::getLevelCache();
        $shareScale = $LevelCache[ $parent[ 'level_id' ] ][ 'promotion_rewards_ratio' ];
        /* 所有上级中找上级的高一级分佣 */
        
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
    /*************************************************************************************/
    /**
     * Description:获取所有上级信息
     *
     * @param $pid_route
     *
     * @return array|null
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/9/2 0002
     */
    public function getAllParentsLevel($pid_route)
    {
        if (empty($pid_route)) {
            return null;
        }
        try {
            $parents = UserLevelRelation::where('user_id', 'in', $pid_route)->orderByDesc('user_id')->get()->toArray();
        } catch (Exception $e) {
            Log::debug('getAllParents-Error:', [json_encode($e)]);
            throw $e;
        }
        return $parents ?? [];
    }
    
    /**
     * Description:根据等级获取上级
     *
     * @param $parents
     * @param $level
     *
     * @return array|mixed
     * @author lidong<947714443@qq.com>
     * @date   2021/9/2 0002
     */
    public function getParentByLevel($parents, $level)
    {
        $target_parent = [];
        foreach ($parents as $row) {
            if ($row[ 'level_id' ] == $level) {
                $target_parent = $row;
            }
        }
        return $target_parent;
    }
    
    /**
     * Description:根据ID获取上级
     *
     * @param $parents
     * @param $invite_id
     *
     * @return array|mixed
     * @author lidong<947714443@qq.com>
     * @date   2021/9/2 0002
     */
    public function getParentByInviteId($parents, $invite_id)
    {
        $target_parent = [];
        foreach ($parents as $row) {
            if ($row[ 'user_id' ] == $invite_id) {
                $target_parent = $row;
            }
        }
        return $target_parent;
    }
}
