<?php

namespace App\Services;

use App\Exceptions\LogicException;
use App\Models\AssetsLogs;
use App\Models\AssetsType;
use App\Models\CityNode;
use App\Models\IntegralLogs;
use App\Models\Order;
use App\Models\OrderMobileRecharge;
use App\Models\RebateData;
use App\Models\Setting;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\LkshopOrder;
use \App\Services\UserRebateService;
use \App\Services\ProvinceCityAreaDlService;
use Illuminate\Support\Facades\Log;

class GatherOrderService
{
    /**返佣
     * @param $order
     * @param $user
     * @param $business
     * @param $orderNo
     * @throws \App\Exceptions\LogicException
     * @throws \Exception
     */

    private function encourage($order, $user, $business, $orderNo)
    {
        //获取资产类型
        $assets = AssetsType::where("assets_name", AssetsType::DEFAULT_ASSETS_ENCOURAGE)
            ->first();
        //公益捐赠
        $welfareUid = Setting::getSetting('welfare_uid');
        $welfareAmount = 0;
        if ($welfareUid) {
            $welfare = Setting::getSetting('welfare');
            $welfareAmount = bcmul($order->profit_price, bcdiv($welfare, 100, 6), 2);
            AssetsService::BalancesChange(
                $orderNo,
                $welfareUid,
                $assets,
                $assets->assets_name,
                $welfareAmount,
                AssetsLogs::OPERATE_TYPE_CHARITY_REBATE,
                "公益捐赠"
            );
        }
        //来客平台
        $platformUid = Setting::getSetting('platform_uid');
        $platformAmount = 0;
        if ($platformUid) {
            $platform = Setting::getSetting('platform');
            $platformAmount = bcmul($order->profit_price, bcdiv($platform, 100, 6), 2);
            AssetsService::BalancesChange(
                $orderNo,
                $platformUid,
                $assets,
                $assets->assets_name,
                $platformAmount,
                AssetsLogs::OPERATE_TYPE_PLATFORM_REBATE,
                "来客平台维护费"
            );
        }
        //分享佣金
        /* 计算总佣金 */ /* 计算总佣金 */
        Log::debug("=======分享佣金=============UserRebateService==========开始============");
        $shareScale = UserRebateService::getShareRatio();
        $shareAmount = bcmul($order->profit_price, bcdiv($shareScale, 100, 6), 2);
        /* 邀请分红+同级分佣 */
        (new UserRebateService())->shareScale($order, $user, $assets, $platformUid);
        Log::debug("=======分享佣金=============UserRebateService==========结束============");
        //*****************************************************************
        //省市区代理返佣
        $ssqAmount = (new ProvinceCityAreaDlService())->inviteProvinceCityAreaD($order, $user, $assets, $orderNo,
            $platformUid);
        //*****************************************************************

        $market = bcadd($ssqAmount[ 'provinceAmount' ],
            bcadd($ssqAmount[ 'districtAmount' ], $ssqAmount[ 'cityAmount' ], 8), 8);
        $this->updateRebateData($welfareAmount, $shareAmount, $market, $platformAmount, $order->price, $user);
    }

    /**找盟主
     * @param       $invite_uid
     * @param       $amount
     * @param       $assets
     * @param       $msg
     * @param       $type
     * @param int   $level
     * @return false
     * @throws \App\Exceptions\LogicException
     */
    public function leaderRebate($orderNo, $invite_uid, $amount, $assets, $msg, $type, $level = 2)
    {
        if ($level <= 0) {
            return false;
        }
        $user = User::whereId($invite_uid)
                    ->first();
        //如果是盟主,奖励0.3直接给与他
        if ($user && $user->member_head == 2 && $user->status == User::STATUS_NORMAL) {
            if ($amount > 0) {
                AssetsService::BalancesChange($orderNo, $user->id, $assets, $assets->assets_name, $amount, $type, $msg);
            }
            return $user;
        } elseif ($user) {
            $level--;
            return $this->leaderRebate($orderNo, $user->invite_uid, $amount, $assets, $msg, $type, $level);
        } else {
            return false;
        }
    }

    /**更新返佣统计
     *
     * @param $welfare
     * @param $share
     * @param $market
     * @param $platform
     * @param $price
     * @param $user
     */
    public function updateRebateData($welfare, $share, $market, $platform, $price, $user)
    {
        $rebateData = RebateData::firstOrCreate(['day' => now()->toDateString()]);
        $rebateData->welfare = bcadd($rebateData->welfare, $welfare, 2);
        $rebateData->share = bcadd($rebateData->share, $share, 2);
        $rebateData->market = bcadd($rebateData->market, $market, 2);
        $rebateData->platform = bcadd($rebateData->platform, $platform, 2);
        $rebateData->people = Order::where("status", Order::STATUS_SUCCEED)
                                   ->distinct("uid")
                                   ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
                                   ->count() + 1;
        $rebateData->total_consumption = bcadd($price, $rebateData->total_consumption, 8);
        $rebateData->save();
    }

    //邀请补贴和邀请人积分添加
    //商户uid,实际让利比例，订单分类 HF YK MT,消费者uid
    public function addInvitePoints($order_business_uid, $order_profit_price, $description, $uid, $orderNo)
    {
//判断邀请补贴活动是否开启，如果开启就将邀请积分添加到该用户的邀请人里
        $InvitePointsArr = [
            'HF'  => 'Invite_points_hf',
            'YK'  => 'Invite_points_yk',
            'MT'  => 'Invite_points_mt',
            'ZL'  => 'Invite_points_zl',
            'MZL' => 'Invite_points_mzl',
            'VC'  => 'Invite_points_vc',
            'CLP' => 'Invite_points_clp',
            'CLM' => 'Invite_points_clm',
        ];
        $activityState = 0;
        if ($description != 'LR' && isset($InvitePointsArr[ $description ])) {
            $key = $InvitePointsArr[ $description ];
            //判断活动是否开启
            $setValue = Setting::where('key', $key)
                               ->value('value');
            if ($setValue != 0 && strstr($setValue, '|') != false) {
                $dateArr = explode('|', $setValue);
                if (strtotime($dateArr[ 0 ]) < time() && time() < strtotime($dateArr[ 1 ])) {
                    $invite_uid = User::where('id', $uid)
                                      ->value('invite_uid');//邀请人uid
                    $activityState = 1;
                } else {
                    $invite_uid = $order_business_uid;
                    $activityState = 0;
                }
            } else {
                $invite_uid = $order_business_uid;
                $activityState = 0;
            }
        } else {
            $invite_uid = $order_business_uid;
            $activityState = 0;
        }
        //给商家加积分，更新LK
        $business = User::find($invite_uid);
        $amountBeforeChange = $business->business_integral;
        $business->business_integral = bcadd($business->business_integral, $order_profit_price, 2);
        $businessLkPer = Setting::getSetting('business_Lk_per') ?? 60;
        //更新LK
        $business->business_lk = bcdiv($business->business_integral, $businessLkPer, 0);
        $business->save();
        IntegralLogs::addLog(
            $business->id,
            $order_profit_price,
            IntegralLogs::TYPE_SPEND,
            $amountBeforeChange,
            2,
            '商家完成订单',
            $orderNo,
            $activityState,
            $uid,
            $description
        );
    }

    /**
     * Description:
     * TODO:判断订单类型
     * @param                          $order_id
     * @param \App\Models\Order|null   $Order
     * @return mixed|string
     * @throws \Exception
     */
    public function getDescription($order_id, Order $Order = null)
    {
        if (empty($Order)) {
            $Order = Order::find($order_id);
        }
        try {
            if (empty($Order)) {
                throw new Exception('订单数据为空');
            }
            if (!empty($Order->trade)) { /* 兼容trade_order */
                $description = $Order->trade->description;
            }
            if (!empty($Order->mobile)) {
                switch ($Order->mobile->create_type) {
                    case OrderMobileRecharge::CREATE_TYPE_ZL:
                        $description = 'ZL';
                        break;
                    case OrderMobileRecharge::CREATE_TYPE_MZL:
                        $description = 'MZL';
                        break;
                    default:
                        break;
//                        $description = 'HF';
                }
            }
            if (!empty($Order->video)) { /* 视频会员订单 */
                $description = 'VC';
            }
            if (!empty($Order->air)) { /* 机票订单 */
                $description = 'AT';
            }
            if (!empty($Order->utility)) { /* 生活缴费 */
                $description = 'UB';
            }
            if (!empty($Order->lkshopOrder)) { /* 生活缴费 */
                $description = 'SHOP';
            }
            if (!empty($Order->convertLogs)) { /* 碎片兑换 */
                switch ($Order->convertLogs->type) {
                    case 1:
                        $description = 'CLP';
                        break;
                    case 2:
                        $description = 'CLM';
                        break;
                }
                //$description = 'CL';
            }
            /* 判断 是否已经获取到对应类型的订单*/
            if (empty($description)) {
                throw new Exception('没有对应类型的订单：'.json_encode($Order));
            }
        } catch (Exception $e) {
            throw $e;
        }
        return $description;
    }

    /**拼团录单
     * @param int    $id           order表ID
     * @param int    $consumer_uid 用户ID
     * @param string $description  订单类型
     * @param string $orderNo      订单号
     * @return mixed
     * @throws \Throwable
     */
    public function completeOrderGatger(int $id, int $consumer_uid, string $description, string $orderNo)
    {
        Log::debug("===============测试============拼团录单=======completeOrderGatger======================");
        DB::beginTransaction();
        try {
            $order = Order::find($id);
            if ($order->status != Order::STATUS_DEFAULT) {
                return false;
            }
            $order->status = Order::STATUS_SUCCEED;
            $order->pay_status = 'succeeded';//测试自动审核不要改支付状态
            $order->updated_at = date("Y-m-d H:i:s");
            //用户应返还几分比例
            $userRebateScale = Setting::getManySetting('user_rebate_scale');
            $businessRebateScale = Setting::getManySetting('business_rebate_scale');
            $rebateScale = array_combine($businessRebateScale, $userRebateScale);

            //通过，给用户加积分、更新LK
            /*$customer = User::lockForUpdate()
                ->find($order->uid);*/
            $customer = User::find($order->uid);
            //按比例计算实际获得积分
            $profit_ratio_offset = ($order->profit_ratio < 1) ? $order->profit_ratio * 100 : $order->profit_ratio;
            $profit_ratio = bcdiv($rebateScale[ intval($profit_ratio_offset) ], 100, 4);
            $customerIntegral = bcmul($order->price, $profit_ratio, 2);
            $amountBeforeChange = $customer->integral;
            $customer->integral = bcadd($customer->integral, $customerIntegral, 2);
            $lkPer = Setting::getSetting('lk_per') ?? 300;
            //更新LK
            $customer->lk = bcdiv($customer->integral, $lkPer, 0);
            $customer->save();
            IntegralLogs::addLog(
                $customer->id,
                $customerIntegral,
                IntegralLogs::TYPE_SPEND,
                $amountBeforeChange,
                1,
                '消费者完成订单',
                $orderNo,
                0,
                $consumer_uid,
                $description
            );
            //开启邀请补贴活动，添加邀请人积分，否则添加uid2用的商户积分
            $this->addInvitePoints(
                $order->business_uid,
                $order->profit_price,
                $description,
                $consumer_uid,
                $orderNo
            );
            $business = User::find($order->business_uid);
            //返佣
            $this->encourage($order, $customer, $business, $orderNo);
            $order->save();
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }
}
