<?php

namespace App\Console\Commands;

use App\Exceptions\LogicException;
use App\Models\AssetsLogs;
use App\Models\IntegralLogs;
use App\Models\OrderIntegralLkDistribution;
use App\Models\OrderMobileRecharge;
use App\Models\OrderUtilityBill;
use App\Models\OrderVideo;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Models\AssetsType;
use App\Models\CityNode;
use App\Models\RebateData;
use App\Models\TradeOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Matrix\Exception;

class AddIntegral extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:addIntegral';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
//        log::info('=================自动添加积分任务===================================');

        //判断昨日是否返佣
        //判断是否已执行
        $isRebateStatus = RebateData::where("day", Carbon::yesterday()->toDateString())->where('status', 2)->value('status');
        if ($isRebateStatus!=2){
//            log::info("========================昨日未返佣，禁止添加排队订单积分，昨日返佣状态是：".$isRebateStatus);
            dd("========================昨日未返佣，禁止添加排队订单积分，昨日返佣状态是：null ".$isRebateStatus);
            return false;
        }else{
            var_dump("========================昨日返佣状态是：$isRebateStatus");
        }
        $setValue = Setting::where('key', 'consumer_integral')->value('value');
        if ($setValue == 1) {
            $orderInfo = Order::where("status", 2)->where("line_up", 1)->orderBy('id', 'asc')->first();
            if ($orderInfo != null) {
                $orderInfo = $orderInfo->toArray();
            } else {
//                log::info('=================排队订单为空===================================');
                return "排队订单为空";
            }

            $orderId = $orderInfo['id'];
//            log::info("=================打印订单信息1==================================",$orderInfo);
//            $order_no = $orderInfo['trade__order']['order_no'];
            $orderldModer = new OrderIntegralLkDistribution();
            $todaytime = strtotime(date("Y-m-d"), time());
            $lddata = $orderldModer::where('day', $todaytime)->first();
            $LkBlData = array();
            //统计lk总数
            $countLk = User::sum('lk');
            $LkBlData['count_lk'] = $countLk;
            if ($lddata == '') {
                $orderldModer->day = $todaytime;
                $orderldModer->count_lk = $countLk;
                $orderldModer->save();
                $LkBlData['count_profit_price'] = 0;
            } else {
                $redata = $orderldModer::where('day', $todaytime)->first();
                $LkBlData['count_profit_price'] = $redata->count_profit_price;
            }

            $lddata = $orderldModer::where('day', $todaytime)->first();
            $id = $lddata->id;
//            log::info("=================打印---日志---信息--11111111111==================================");
            $profit_ratio = array(
                5 => 'price_5',
                10 => 'price_10',
                20 => 'price_20',
                "other_price" => 'other_price',
            );
//            log::info("=================打印---日志---信息--22222222222==================================");
            if ($orderInfo['name']!='商城订单'){
                $field = $profit_ratio[floor($orderInfo['profit_ratio'])];
                $LkBlData[$field] = bcadd($lddata->$field, $orderInfo['price'], 2);//累计消费金额
                $scddType = 0;
            }else{
                $LkBlData["other_price"] = bcadd($lddata->other_price, $orderInfo['price'], 2);//累计消费金额
                $scddType = 1;
            }
//            log::info("=================打印---日志---信息--3333333333==================================");
            //控制添加积分
            $addCountProfitPrice = bcadd($LkBlData['count_profit_price'], $orderInfo['profit_price'], 2);
            $old_addCountProfitPrice = $LkBlData['count_profit_price'];
            $LkBlData['dr_count'] = $lddata->dr_count+1;
            if ($LkBlData['count_profit_price'] != 0) {
                $lk_unit_price = Setting::where('key', 'lk_unit_price')->value('value');
                if (($old_addCountProfitPrice * 0.675 / $LkBlData['count_lk']) < $lk_unit_price) {
                    if ($this->completeOrder($orderId,$scddType)==100){
                        $LkBlData['count_profit_price'] = $addCountProfitPrice;
                        DB::table('order_integral_lk_distribution')->where('id', $id)->update($LkBlData);
                        return "添加积分成功";
                    }else{
                        return "添加积分失败";
                    }
//                    log::info('=================添加积分成功1===================================');
                } else {
//                    log::info('=================添加积分已达到上限数量===================================');
                    return "添加积分已达到上限数量";
                }

            } else {
                if ($this->completeOrder($orderId,$scddType)==100){
                    $LkBlData['count_profit_price'] = $addCountProfitPrice;
                    DB::table('order_integral_lk_distribution')->where('id', $id)->update($LkBlData);
                    return "添加积分成功";
                }else{
                    return "添加积分失败";
                }
            }

        } else {
            log::info('=================后台未开启控单===================================');
            return "后台未开启控单";
        }

    }


    public function completeOrder(string $orderId,$scddType=0,Order $orderData=null)
    {
        try {
            if (empty($orderData)){
                $orderData = Order::find($orderId);
            }
//            $orderData = Order::find($orderId);
            $orderService = new OrderService();
//            log::debug("=================打印订单信息01==================================".$orderId);
            $orderType = $orderService->getDescription($orderId, $orderData);//订单类型
//            log::debug("=================打印订单信息02==================================".$orderType);
//            log::debug("=================打印订单信息03==================================",$orderData->toArray());
//        dd($orderInfo,$orderData);
            if ($orderType == 'LR' || $orderType == 'HF' || $orderType == 'YK' || $orderType == 'MT' || $orderType == 'ZL') {
                $dataInfo = $orderData->trade;
            } elseif ($orderType == 'VC') {
                $dataInfo = $orderData->video;
            } elseif ($orderType == 'AT') {
                $dataInfo = $orderData->air;
            } elseif ($orderType == 'UB') {
                $dataInfo = $orderData->utility;
            } elseif ($orderType == 'SHOP') {
                $dataInfo = $orderData->lkshopOrder;
            } elseif ($orderType == 'MZL') {
                $dataInfo = $orderData->mobile;
            } elseif ($orderType == 'CLP' || $orderType == 'CLM') {
                $dataInfo = $orderData->convertLogs;
            } else {
                log::debug("=================打印订单信息3-000000========错误==========================".$orderType);
                return false;
            }
        } catch (\Exception $e) {
            report($e);
            throw new LogicException('类型错误：'.$e);
            log::debug("=================打印订单信息3-111111========错误==========================".$e);
        }

        $consumer_uid = $dataInfo->user_id??$dataInfo->uid;
        $description = $orderType;
        $orderNo = $dataInfo->order_no;

//        log::debug("=================打印订单信息3-2222200000000==================================".$consumer_uid);
//        log::debug("=================打印订单信息3-22222==================================".$description);
//        log::debug("=================打印订单信息3-22222==================================".$orderNo);
//        log::debug("=================打印订单信息3-22222==================================",$dataInfo->toArray());

        DB::beginTransaction();
        try {
            $order = Order::lockForUpdate()->find($orderId);
//            log::debug("=================打印订单信息3-88888==================================".$order->line_up);
            if ($order->line_up != 1)
                return false;
            $order->line_up = 0;
            $order->import_day = date("Ymd",time());
            $order->updated_at = date("Y-m-d H:i:s");
            //用户应返还几分比例

            if ($scddType==0){
                $userRebateScale = Setting::getManySetting('user_rebate_scale');
                $businessRebateScale = Setting::getManySetting('business_rebate_scale');
                $rebateScale = array_combine($businessRebateScale, $userRebateScale);
            }elseif ($scddType==1){
                $userRebateScale = intval($order->profit_ratio*5);
                $businessRebateScale = intval($order->profit_ratio);
                $rebateScale = array($businessRebateScale=>$userRebateScale);
            }else{
                log::debug("=================订单的让利比例错误=============================");
                return false;
            }
//            log::debug("=================让利比例数组=============================".$scddType);
//            log::debug("=================让利比例数组=============================",$rebateScale);
            //通过，给用户加积分、更新LK
            $customer = User::lockForUpdate()->find($order->uid);
//            log::debug("=================打印订单信息4444444444=====1111111111111=============================");
            //按比例计算实际获得积分
            $profit_ratio_offset = ($order->profit_ratio < 1) ? $order->profit_ratio * 100 : $order->profit_ratio;
//            log::debug("=================打印订日志信息--aaa=============================".$profit_ratio_offset);
            $profit_ratio = bcdiv($rebateScale[intval($profit_ratio_offset)], 100, 4);
//            log::debug("=================打印订日志信息--bbb=============================".$profit_ratio);
            $customerIntegral = bcmul($order->price, $profit_ratio, 2);
//            log::debug("=================打印订日志信息--ccc=============================".$customerIntegral);
            $amountBeforeChange = $customer->integral;
//            log::debug("=================打印订日志信息--ddd=============================".$amountBeforeChange);
            $customer->integral = bcadd($customer->integral, $customerIntegral, 2);
//            log::debug("=================打印订日志信息--eee=============================");
            $lkPer = Setting::getSetting('lk_per') ?? 300;
            //更新LK
            $customer->lk = bcdiv($customer->integral, $lkPer, 0);
//            log::debug("=================打印订单信息4444444444=====2222222=============================");
            $customer->save();
//            log::debug("=================打印订单信息4444444444=====333333333=============================");
            IntegralLogs::addLog($customer->id, $customerIntegral, IntegralLogs::TYPE_SPEND, $amountBeforeChange, 1, '消费者完成订单', $orderNo, 0, $consumer_uid, $description);
            //开启邀请补贴活动，添加邀请人积分，否则添加uid2用的商户积分
//            //$this->addInvitePoints($order->business_uid, $order->profit_price, $description, $consumer_uid, $orderNo);//不使用这个添加积分，用下面的OrderService的方法添加积分
//            log::debug("=================打印订单信息4444444444=====4444444444444444=============================");
//            log::debug("=================打印订单信息4444444444=====aaaa=============================".$order->business_uid);
//            log::debug("=================打印订单信息4444444444=====aaaa=============================".$order->profit_price);
//            log::debug("=================打印订单信息4444444444=====aaaa=============================".$description);
//            log::debug("=================打印订单信息4444444444=====aaaa=============================".$consumer_uid);
//            log::debug("=================打印订单信息4444444444=====aaaa=============================".$orderNo);
            (new OrderService())->addInvitePoints($order->business_uid, $order->profit_price, $description, $consumer_uid, $orderNo);
//            log::debug("=================打印订单信息4444444444=====555555555555555555=============================");
            $order->save();
            DB::commit();
            return 100;
//            log::debug("=================打印订单信息--成功=================================");
        } catch (\Exception $exception) {
            DB::rollBack();
//            var_dump($exception->getMessage());
            return false;
            log::debug("=================打印订单信息--添加排队积分失败--000==================================");
        }
    }

    /**返佣
     *
     * @param $order
     * @param $user
     * @param $business
     *
     * @throws \App\Exceptions\LogicException
     */
    private function encourage($order, $user, $business)
    {
        //获取资产类型
        $assets = AssetsType::where("assets_name", AssetsType::DEFAULT_ASSETS_ENCOURAGE)->first();
        //公益捐赠
        $welfareUid = Setting::getSetting('welfare_uid');
        $welfareAmount = 0;
        if ($welfareUid) {
            $welfare = Setting::getSetting('welfare');
            $welfareAmount = bcmul($order->profit_price, bcdiv($welfare, 100, 6), 2);
            AssetsService::BalancesChange(
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
                $platformUid,
                $assets,
                $assets->assets_name,
                $platformAmount,
                AssetsLogs::OPERATE_TYPE_PLATFORM_REBATE,
                "来客平台维护费"
            );
        }
        //分享佣金
        $invite = User::where('status', User::STATUS_NORMAL)->whereId($user->invite_uid)->first();
        if (!$invite) {
            $uid = $platformUid;
            $remark = '下级消费返佣（上级账号被封禁或不存在）';
        } else {
            $remark = '下级消费返佣';
            $uid = $invite->id;
        }
        $shareScale = Setting::getSetting('share_scale');
        $shareAmount = bcmul($order->profit_price, bcdiv($shareScale, 100, 6), 2);
        AssetsService::BalancesChange(
            $uid,
            $assets,
            $assets->assets_name,
            $shareAmount,
            AssetsLogs::OPERATE_TYPE_INVITE_REBATE,
            $remark
        );
        //市节点返佣
        $cityNodeRebate = Setting::getSetting('city_node_rebate') ?? 0;
        $cityAmount = 0;
        if ($cityNodeRebate > 0) {
            //判断商家是否在市节点
            $cityNode =
                CityNode::where('status', 1)
                    ->where('province', $order->business->province)
                    ->whereNotNull('uid')
                    ->where('city', $order->business->city)
                    ->whereNull('district')
                    ->first();
            if (!$cityNode) {
                $uid = $platformUid;
                $remark = '市级节点暂无，分配到来客平台';
            } else {
                $remark = '市节点运营返佣';
                $uid = $cityNode->uid;
            }
            //市长分配比列0.25%
            $cityAmount = bcmul($order->profit_price, bcdiv($cityNodeRebate, 100, 6), 8);
            AssetsService::BalancesChange(
                $uid,
                $assets,
                $assets->assets_name,
                $cityAmount,
                AssetsLogs::OPERATE_TYPE_CITY_REBATE,
                $remark
            );
        }
        //区节点返佣
        $districtNodeRebate = Setting::getSetting('district_node_rebate') ?? 0;
        $districtAmount = 0;
        if ($districtNodeRebate > 0) {
            //判断商家是否在区节点
            $districtNode =
                CityNode::where('status', 1)
                    ->where('province', $order->business->province)
                    ->whereNotNull('uid')
                    ->where('city', $order->business->city)
                    ->where('district', $order->business->district)
                    ->first();
            if (!$districtNode) {
                $uid = $platformUid;
                $remark = '区级节点暂无，分配到来客平台';
            } else {
                $remark = '区级节点运营返佣';
                $uid = $districtNode->uid;
            }
            //区长分配0.45%
            $districtAmount = bcmul($order->profit_price, bcdiv($districtNodeRebate, 100, 6), 8);
            AssetsService::BalancesChange(
                $uid,
                $assets,
                $assets->assets_name,
                $districtAmount,
                AssetsLogs::OPERATE_TYPE_DISTRICT_REBATE,
                $remark
            );
        }
        //同级返佣
        $sameLeader = Setting::getSetting('same_leader') ?? 0;
        $sameAmount = 0;
        //同级别分配比列0.1%
        if ($sameLeader > 0)
            $sameAmount = bcmul($order->profit_price, bcdiv($sameLeader, 100, 6), 8);
        //盟主返佣
        $leaderShare = Setting::getSetting('leader_share') ?? 0;
        $headAmount = 0;
        //盟主分配0.7%
        if ($leaderShare > 0)
            $headAmount = bcmul($order->profit_price, bcdiv($leaderShare, 100, 6), 8);
        $memberHead = User::whereId($business->invite_uid)->first();
        //普通用户邀请商家返佣
        $userBRebate = Setting::getSetting('user_b_rebate') ?? 0;
        $ordinaryAmount = 0;
        if ($userBRebate > 0)
            $ordinaryAmount = bcmul($order->profit_price, bcdiv($userBRebate, 100, 6), 8);
        //同级奖励是否给平台
        $isSamePlat = false;
        if ($memberHead->status != User::STATUS_NORMAL) {
            $uid = $platformUid;
            $remark = '直推人账户被封禁，分配到平台账户';
            $inviteAmount = bcadd($headAmount, $ordinaryAmount, 8);
        } else {
            $inviteAmount = $ordinaryAmount;
            $remark = '邀请商家，获得盈利返佣';
            $uid = $memberHead->id;
            //如果直推上级是盟主用户
            if ($memberHead->member_head == 2) {
                //直接拿0.7%奖励
                $inviteAmount = bcadd($headAmount, $ordinaryAmount, 8);
                //同级盟主奖励
                $tes =
                    $this->leaderRebate($memberHead->invite_uid, $sameAmount, $assets, '同级别盟主奖励', AssetsLogs::OPERATE_TYPE_SHARE_B_REBATE, 1);
                if ($tes == false)
                    $isSamePlat = true;
            } else {
                //往上找2级 是否盟主
                $res =
                    $this->leaderRebate($memberHead->invite_uid, $headAmount, $assets, '邀请商家盟主分红', AssetsLogs::OPERATE_TYPE_SHARE_B_REBATE, 2);
                if ($res == false) {
                    if ($headAmount > 0)
                        AssetsService::BalancesChange($platformUid, $assets, $assets->assets_name, $headAmount, AssetsLogs::OPERATE_TYPE_SHARE_B_REBATE, '没有盟主，分配到平台账户');
                    $isSamePlat = true;
                } else {
                    //同级盟主奖励
                    $res =
                        $this->leaderRebate($res->invite_uid, $sameAmount, $assets, '同级别盟主奖励', AssetsLogs::OPERATE_TYPE_SHARE_B_REBATE, 1);
                    if ($res == false)
                        $isSamePlat = true;
                }
            }
        }
        if ($sameAmount > 0 && $isSamePlat == true)
            AssetsService::BalancesChange($platformUid, $assets, $assets->assets_name, $sameAmount, AssetsLogs::OPERATE_TYPE_SHARE_B_REBATE, '没有同级盟主，分配到平台账户');
        if ($inviteAmount > 0)
            AssetsService::BalancesChange($uid, $assets, $assets->assets_name, $inviteAmount, AssetsLogs::OPERATE_TYPE_SHARE_B_REBATE, $remark);
        $market =
            bcadd($districtAmount, bcadd($cityAmount, bcadd(bcadd($sameAmount, $headAmount, 8), $ordinaryAmount, 8), 8), 8);
        $this->updateRebateData($welfareAmount, $shareAmount, $market, $platformAmount, $order->price, $user);
    }

    /**找盟主
     *
     * @param     $invite_uid
     * @param     $amount
     * @param     $assets
     * @param     $msg
     * @param     $type
     * @param int $level
     *
     * @return false
     * @throws \App\Exceptions\LogicException
     */
    public function leaderRebate($invite_uid, $amount, $assets, $msg, $type, $level = 2)
    {
        if ($level <= 0)
            return false;
        $user = User::whereId($invite_uid)->first();
        //如果是盟主,奖励0.3直接给与他
        if ($user && $user->member_head == 2 && $user->status == User::STATUS_NORMAL) {
            if ($amount > 0)
                AssetsService::BalancesChange($user->id, $assets, $assets->assets_name, $amount, $type, $msg);
            return $user;
        } elseif ($user) {
            $level--;
            return $this->leaderRebate($user->invite_uid, $amount, $assets, $msg, $type, $level);
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
            'HF' => 'Invite_points_hf',
            'YK' => 'Invite_points_yk',
            'MT' => 'Invite_points_mt',
            'ZL' => 'Invite_points_zl',
            'VC' => 'Invite_points_vc',
            'CLP' => 'Invite_points_clp',
            'CLM' => 'Invite_points_clm',
        ];
        $activityState = 0;
        if ($description != 'LR' && isset($InvitePointsArr[$description])) {
            $key = $InvitePointsArr[$description];
            //判断活动是否开启
            $setValue = Setting::where('key', $key)->value('value');
            if ($setValue != 0 && strstr($setValue, '|') != false) {
                $dateArr = explode('|', $setValue);
                if (strtotime($dateArr[0]) < time() && time() < strtotime($dateArr[1])) {
                    $invite_uid = User::where('id', $uid)->value('invite_uid');//邀请人uid
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
        $business = User::lockForUpdate()->find($invite_uid);
        $amountBeforeChange = $business->business_integral;
        $business->business_integral = bcadd($business->business_integral, $order_profit_price, 2);
        $businessLkPer = Setting::getSetting('business_Lk_per') ?? 60;
        //更新LK
        $business->business_lk = bcdiv($business->business_integral, $businessLkPer, 0);
        $business->save();
        IntegralLogs::addLog($business->id, $order_profit_price, IntegralLogs::TYPE_SPEND, $amountBeforeChange, 2, '商家完成订单', $orderNo, $activityState, $uid, $description);
    }


}
