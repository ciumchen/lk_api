<?php

namespace App\Services;

use App\Models\AssetsLogs;
use App\Models\AssetsType;
use App\Models\Order;
use App\Models\User;
use App\Models\UserLevel;
use App\Models\UserLevelRelation;
use App\Models\WeightRewards;
use App\Models\WeightRewardsLog;
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
    
    /**
     * Description:分享佣金
     *
     * @param \App\Models\Order      $order
     * @param \App\Models\User       $user
     * @param \App\Models\AssetsType $assetsType
     * @param int                    $platformUid
     *
     * @throws \App\Exceptions\LogicException
     * @author lidong<947714443@qq.com>
     * @date   2021/9/3 0003
     */
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
            if (empty($parent) || $parent[ 'user_id' ] == $platformUid) { /* 未找到上级或者上级是平台 */
                $this->platformInviterScale($order, $assetsType, $platformUid,);
            } else {
                $this->inviterScale($order, $assetsType, $parent);
                /* 所有上级中找上级的高一级分佣 */
                $this->higherScale($order, $user, $assetsType, $platformUid, $userLevelInfo, $parent, $allParent);
                /* 平级奖分佣 */
                $this->sameLevel($order, $user, $assetsType, $platformUid, $userLevelInfo, $parent, $allParent);
            }
            /* 加权平分 */
            $this->weightRewardsCount($order);
        } catch (Exception $e) {
            Log::debug('shareScale:Error:'.$e->getMessage(), [json_encode($e)]);
            throw $e;
        }
    }
    
    /**
     * Description:邀请人返利计算
     *
     * @param \App\Models\Order      $order
     * @param \App\Models\AssetsType $assetsType
     * @param array                  $parent
     *
     * @throws \App\Exceptions\LogicException
     * @author lidong<947714443@qq.com>
     * @date   2021/9/3 0003
     */
    public function inviterScale(
        Order $order,
        AssetsType $assetsType,
        $parent = []
    ) {
        $LevelCache = self::getLevelCache();
        $shareScale = $LevelCache[ $parent[ 'level_id' ] ][ 'promotion_rewards_ratio' ];
        $shareAmount = bcmul($order->profit_price, bcdiv($shareScale, 100, 6), 3);
        AssetsService::BalancesChange(
            $order->order_no,
            $parent[ 'user_id' ],
            $assetsType,
            $assetsType->assets_name,
            $shareAmount,
            AssetsLogs::OPERATE_TYPE_INVITE_REBATE,
            '下级消费返佣'
        );
    }
    
    /**
     * Description:查找银卡金卡和钻石卡上级ID分佣
     *
     * @param \App\Models\Order      $order
     * @param \App\Models\User       $user
     * @param \App\Models\AssetsType $assetsType
     * @param int                    $platformUid
     * @param null                   $userLevelInfo
     * @param array                  $parent
     * @param array                  $allParent
     *
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/9/3 0003
     */
    public function higherScale(
        Order $order,
        User $user,
        AssetsType $assetsType,
        $platformUid = 0,
        $userLevelInfo = null,
        $parent = [],
        $allParent = []
    ) {
        if (empty($userLevelInfo)) {
            $userLevelInfo = UserLevelRelation::whereUserId($user->id)->first();
        }
        switch ($parent[ 'level_id' ]) {
            case SystemService::$memberLevelID:
            case SystemService::$vipLevelID: /* 会员从上级找银卡 */
                $parent = $this->silverHigherScale($order, $user, $assetsType, $platformUid, $userLevelInfo, $parent,
                                                   $allParent);
            case SystemService::$silverLevelId: /* 银卡从上级找金卡 */
                $this->goldHigherScale($order, $user, $assetsType, $platformUid, $userLevelInfo, $parent, $allParent);
            case SystemService::$goldLevelId: /* 金卡从上级找钻石卡 */
                $this->diamondHigherScale($order, $user, $assetsType, $platformUid, $userLevelInfo, $parent,
                                          $allParent);
                break;
            default:
                Log::debug('higherScale:Error:上级分佣异常', [json_encode($order).'||'.json_encode($user)]);
        }
    }
    
    /**
     * Description:无上级调用平台返利计算
     *
     * @param \App\Models\Order      $order
     * @param \App\Models\AssetsType $assetsType
     * @param int                    $platformUid
     *
     * @throws \App\Exceptions\LogicException
     * @author lidong<947714443@qq.com>
     * @date   2021/9/3 0003
     */
    public function platformInviterScale(
        Order $order,
        AssetsType $assetsType,
        $platformUid = 0
    ) {
        try {
            if (intval($platformUid) == 0) {
                $platformUid = SystemService::$platformId;
            }
            $LevelCache = self::getLevelCache();
            /* 上级是平台，平台直接分佣钻石卡额度以及平级奖额度 */
            $shareScale = $LevelCache[ SystemService::$diamondLevelId ][ 'promotion_rewards_ratio' ]
                          + $LevelCache[ SystemService::$diamondLevelId ][ 'same_level_rewards_ratio' ];
            $shareAmount = bcmul($order->profit_price, bcdiv($shareScale, 100, 6), 3);
            AssetsService::BalancesChange(
                $order->order_no,
                $platformUid,
                $assetsType,
                $assetsType->assets_name,
                $shareAmount,
                AssetsLogs::OPERATE_TYPE_INVITE_REBATE,
                '下级消费返佣+平级奖'
            );
        } catch (Exception $e) {
            Log::debug('platformInviterScale:Error'.$e->getMessage(), [json_encode($e)]);
            throw $e;
        }
    }
    
    /**
     * Description:银卡上级返利计算
     *
     * @param \App\Models\Order      $order
     * @param \App\Models\User       $user
     * @param \App\Models\AssetsType $assetsType
     * @param int                    $platformUid
     * @param null                   $userLevelInfo
     * @param array                  $parent
     * @param array                  $allParent
     *
     * @return array|mixed
     * @throws \App\Exceptions\LogicException
     * @author lidong<947714443@qq.com>
     * @date   2021/9/3 0003
     */
    public function silverHigherScale(
        Order $order,
        User $user,
        AssetsType $assetsType,
        $platformUid = 0,
        $userLevelInfo = null,
        $parent = [],
        $allParent = []
    ) {
        try {
            $silverParent = $this->getParentByLevelAndUid($allParent, SystemService::$silverLevelId,
                                                          $parent[ 'user_id' ]);
            if (empty($silverParent)) {
                $uid = $platformUid;
            } else {
                $uid = $silverParent[ 'user_id' ];
            }
            /* 计算极差奖比例 */
            $shareScale = $this->LevelRangeScale(SystemService::$vipLevelID, SystemService::$silverLevelId);
            $shareAmount = bcmul($order->profit_price, bcdiv($shareScale, 100, 6), 3);
            AssetsService::BalancesChange(
                $order->order_no,
                $uid,
                $assetsType,
                $assetsType->assets_name,
                $shareAmount,
                AssetsLogs::OPERATE_TYPE_INVITE_REBATE,
                '银卡极差奖'
            );
        } catch (Exception $e) {
            Log::debug('silverHigherScale:Error:'.$e->getMessage(), [json_encode($e)]);
            throw $e;
        }
        return $silverParent;
    }
    
    /**
     * Description:金卡上级返利计算
     *
     * @param \App\Models\Order      $order
     * @param \App\Models\User       $user
     * @param \App\Models\AssetsType $assetsType
     * @param int                    $platformUid
     * @param null                   $userLevelInfo
     * @param array                  $parent
     * @param array                  $allParent
     *
     * @return array|mixed
     * @throws \App\Exceptions\LogicException
     * @author lidong<947714443@qq.com>
     * @date   2021/9/3 0003
     */
    public function goldHigherScale(
        Order $order,
        User $user,
        AssetsType $assetsType,
        $platformUid = 0,
        $userLevelInfo = null,
        $parent = [],
        $allParent = []
    ) {
        try {
            $goldParent = $this->getParentByLevelAndUid($allParent, SystemService::$goldLevelId,
                                                        $parent[ 'user_id' ]);
            if (empty($goldParent)) {
                $uid = $platformUid;
            } else {
                $uid = $goldParent[ 'user_id' ];
            }
            /* 计算极差奖比例 */
            $shareScale = $this->LevelRangeScale(SystemService::$silverLevelId, SystemService::$goldLevelId);
            $shareAmount = bcmul($order->profit_price, bcdiv($shareScale, 100, 6), 3);
            AssetsService::BalancesChange(
                $order->order_no,
                $uid,
                $assetsType,
                $assetsType->assets_name,
                $shareAmount,
                AssetsLogs::OPERATE_TYPE_INVITE_REBATE,
                '金卡极差奖'
            );
        } catch (Exception $e) {
            Log::debug('goldHigherScale:Error:'.$e->getMessage(), [json_encode($e)]);
            throw $e;
        }
        return $goldParent;
    }
    
    /**
     * Description:钻石卡上级返利计算
     *
     * @param \App\Models\Order      $order
     * @param \App\Models\User       $user
     * @param \App\Models\AssetsType $assetsType
     * @param int                    $platformUid
     * @param null                   $userLevelInfo
     * @param array                  $parent
     * @param array                  $allParent
     *
     * @throws \App\Exceptions\LogicException
     * @author lidong<947714443@qq.com>
     * @date   2021/9/3 0003
     */
    public function diamondHigherScale(
        Order $order,
        User $user,
        AssetsType $assetsType,
        $platformUid = 0,
        $userLevelInfo = null,
        $parent = [],
        $allParent = []
    ) {
        try {
            $goldParent = $this->getParentByLevelAndUid($allParent, SystemService::$diamondLevelId,
                                                        $parent[ 'user_id' ]);
            if (empty($goldParent)) {
                $uid = $platformUid;
            } else {
                $uid = $goldParent[ 'user_id' ];
            }
            /* 计算极差奖比例 */
            $shareScale = $this->LevelRangeScale(SystemService::$goldLevelId, SystemService::$diamondLevelId);
            $shareAmount = bcmul($order->profit_price, bcdiv($shareScale, 100, 6), 3);
            AssetsService::BalancesChange(
                $order->order_no,
                $uid,
                $assetsType,
                $assetsType->assets_name,
                $shareAmount,
                AssetsLogs::OPERATE_TYPE_INVITE_REBATE,
                '钻石卡极差奖'
            );
        } catch (Exception $e) {
            Log::debug('diamondHigherScale:Error:'.$e->getMessage(), [json_encode($e)]);
            throw $e;
        }
    }
    
    /**
     * Description:获取极差奖比例
     *
     * @param $low_level_id
     * @param $high_level_id
     *
     * @return mixed
     * @author lidong<947714443@qq.com>
     * @date   2021/9/3 0003
     */
    public function LevelRangeScale($low_level_id, $high_level_id)
    {
        $LevelCache = self::getLevelCache();
        $scale = $LevelCache[ $high_level_id ][ 'promotion_rewards_ratio' ]
                 - $LevelCache[ $low_level_id ][ 'promotion_rewards_ratio' ];
        return $scale;
    }
    
    /**
     * Description:同级奖励
     *
     * @param \App\Models\Order      $order
     * @param \App\Models\User       $user
     * @param \App\Models\AssetsType $assetsType
     * @param int                    $platformUid
     * @param null                   $userLevelInfo
     * @param array                  $parent
     * @param array                  $allParent
     *
     * @throws \App\Exceptions\LogicException
     * @author lidong<947714443@qq.com>
     * @date   2021/9/3 0003
     */
    public function sameLevel(
        Order $order,
        User $user,
        AssetsType $assetsType,
        $platformUid = 0,
        $userLevelInfo = null,
        $parent = [],
        $allParent = []
    ) {
        try {
            if (intval($platformUid) == 0) {
                $platformUid = SystemService::$platformId;
            }
            $sameLevelParent = $this->getParentByLevelAndUid($allParent, $parent[ 'level_id' ], $parent[ 'user_id' ]);
            if (empty($sameLevelParent)) {
                $uid = $platformUid;
            } else {
                $uid = $sameLevelParent[ 'user_id' ];
            }
            $LevelCache = self::getLevelCache();
            $shareScale = $LevelCache[ $parent[ 'level_id' ] ][ 'promotion_rewards_ratio' ];
            $shareAmount = bcmul($order->profit_price, bcdiv($shareScale, 100, 6), 3);
            AssetsService::BalancesChange(
                $order->order_no,
                $uid,
                $assetsType,
                $assetsType->assets_name,
                $shareAmount,
                AssetsLogs::OPERATE_TYPE_INVITE_REBATE,
                '同级奖'
            );
        } catch (Exception $e) {
            Log::debug('sameLevel:Error:'.$e->getMessage(), [json_encode($e)]);
            throw $e;
        }
    }
    
    /**
     * Description:每日加权平分奖累加
     *
     * @param \App\Models\Order $order
     *
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/9/3 0003
     */
    public function weightRewardsCount(Order $order)
    {
        try {
            $LevelCache = self::getLevelCache();
            /* 获取 金银钻 三个等级的加权评分奖比例 */
            $silverShareScale = $LevelCache[ SystemService::$silverLevelId ];
            $goldShareScale = $LevelCache[ SystemService::$goldLevelId ];
            $diamondShareScale = $LevelCache[ SystemService::$diamondLevelId ];
            /* 计算三种等级可分润的金额 */
            $silverShareAmount = bcmul($order->profit_price, bcdiv($silverShareScale, 100, 6), 3);
            $goldShareAmount = bcmul($order->profit_price, bcdiv($goldShareScale, 100, 6), 3);
            $diamondShareAmount = bcmul($order->profit_price, bcdiv($diamondShareScale, 100, 6), 3);
            /* 奖金放入当日累计金额 */
            $WeightRewards = WeightRewards::whereCountDate(date('Ymd'))->firstOrNew();
            $WeightRewards->increment('silver_money', $silverShareAmount);
            $WeightRewards->increment('gold_money', $goldShareAmount);
            $WeightRewards->increment('diamond_money', $diamondShareAmount);
            /* 累加记录 */
            $this->weightRewardsCountLog($order, $silverShareAmount, $goldShareAmount, $diamondShareAmount,
                                         $silverShareScale, $goldShareScale, $diamondShareScale);
        } catch (Exception $e) {
            Log::debug('weightRewardsCount'.$e->getMessage(), [json_encode($e)]);
            throw $e;
        }
    }
    
    /**
     * Description:加权奖励累加记录
     *
     * @param \App\Models\Order $order
     * @param                   $silverShareAmount
     * @param                   $goldShareAmount
     * @param                   $diamondShareAmount
     * @param int               $silverShareScale
     * @param int               $goldShareScale
     * @param int               $diamondShareScale
     *
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/9/3 0003
     */
    public function weightRewardsCountLog(
        Order $order,
        $silverShareAmount,
        $goldShareAmount,
        $diamondShareAmount,
        $silverShareScale = 0,
        $goldShareScale = 0,
        $diamondShareScale = 0
    ) {
        try {
            $weightRewardsLog = new WeightRewardsLog();
            $weightRewardsLog->silver_money = $silverShareAmount;
            $weightRewardsLog->gold_money = $goldShareAmount;
            $weightRewardsLog->diamond_money = $diamondShareAmount;
            $weightRewardsLog->silver_ratio = $silverShareScale;
            $weightRewardsLog->gold_ratio = $goldShareScale;
            $weightRewardsLog->diamond_ratio = $diamondShareScale;
            $weightRewardsLog->money = $order->profit_price;
            $weightRewardsLog->save();
        } catch (Exception $e) {
            Log::debug('weightRewardsCountLog'.$e->getMessage(), [json_encode($e)]);
            throw $e;
        }
    }
    
    /* TODO:每天加权分奖励 */
    public function weightRewardsScale()
    {
        /* 统计当天银卡数量并获取ID */
        /* 统计当天金卡数量并获取ID */
        /* 统计当天钻石卡数量并获取ID */
        /* 统计被禁银卡数量并获取ID */
        /* 统计被禁金卡数量并获取ID */
        /* 统计被禁钻石卡数量并获取ID */
        /* 分别计算可平分金额 */
        /* 循环用户并更新资产 */
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
    public function getAllParentsLevel(
        $pid_route
    ) {
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
    public function getParentByLevel(
        $parents,
        $level
    ) {
        $target_parent = [];
        foreach ($parents as $row) {
            if ($row[ 'level_id' ] == $level) {
                $target_parent = $row;
//                break;
            }
        }
        return $target_parent;
    }
    
    /**
     * Description:通过ID在所有父级中查找用户父级的对应等级的父级
     *
     * @param $parents
     * @param $level
     * @param $uid
     *
     * @return array|mixed
     * @author lidong<947714443@qq.com>
     * @date   2021/9/3 0003
     */
    public function getParentByLevelAndUid($parents, $level, $uid)
    {
        $target_parent = [];
        foreach ($parents as $row) {
            if ($row[ 'level_id' ] == $level && $row[ 'user_id' ] < $uid) {
                $target_parent = $row;
//                break;
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
    public function getParentByInviteId(
        $parents,
        $invite_id
    ) {
        $target_parent = [];
        foreach ($parents as $row) {
            if ($row[ 'user_id' ] == $invite_id) {
                $target_parent = $row;
            }
        }
        return $target_parent;
    }
}
