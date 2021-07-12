<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TtshopUser
 *
 * @property int         $id
 * @property int         $type                    用户类型：0=管理员，1=普通用户
 * @property string      $username
 * @property string      $password
 * @property string      $auth_key
 * @property string      $access_token
 * @property int         $addtime
 * @property int         $is_delete
 * @property string      $wechat_open_id          微信openid
 * @property string      $wechat_union_id         微信用户union id
 * @property string      $nickname                昵称
 * @property string      $avatar_url              头像url
 * @property int         $store_id                商城id
 * @property int         $is_distributor          是否是分销商 0--不是 1--是 2--申请中
 * @property int         $parent_id               父级ID
 * @property int         $time                    成为分销商的时间
 * @property string      $total_price             累计佣金
 * @property string      $price                   可提现佣金
 * @property int         $is_clerk                是否是核销员 0--不是 1--是
 * @property int         $saas_id                 SaaS账户id
 * @property int|null    $shop_id
 * @property int|null    $level                   会员等级
 * @property int         $integral                用户当前积分
 * @property int         $total_integral          用户总获得积分
 * @property string|null $money                   余额
 * @property string|null $contact_way             联系方式
 * @property string|null $comments                备注
 * @property string|null $binding                 授权手机号
 * @property string      $wechat_platform_open_id 微信公众号openid
 * @property int         $platform                小程序平台 0 => 微信, 1 => 支付宝
 * @property int         $blacklist               黑名单 0.否 | 1.是
 * @property int         $parent_user_id          可能成为上级的ID
 * @property int|null    $is_app                  是否app注册  0否  1是
 * @property int|null    $mch_id                  商户id 商户后台设置管理员时用到
 * @property string|null $clientid                客户id
 * @property string|null $apple_openid            苹果openID
 * @property int|null    $is_web                  1 = pc  2 = mobile
 * @property string|null $app_source              app来源
 * @method static Builder|TtshopUser newModelQuery()
 * @method static Builder|TtshopUser newQuery()
 * @method static Builder|TtshopUser query()
 * @method static Builder|TtshopUser whereAccessToken($value)
 * @method static Builder|TtshopUser whereAddtime($value)
 * @method static Builder|TtshopUser whereAppSource($value)
 * @method static Builder|TtshopUser whereAppleOpenid($value)
 * @method static Builder|TtshopUser whereAuthKey($value)
 * @method static Builder|TtshopUser whereAvatarUrl($value)
 * @method static Builder|TtshopUser whereBinding($value)
 * @method static Builder|TtshopUser whereBlacklist($value)
 * @method static Builder|TtshopUser whereClientid($value)
 * @method static Builder|TtshopUser whereComments($value)
 * @method static Builder|TtshopUser whereContactWay($value)
 * @method static Builder|TtshopUser whereId($value)
 * @method static Builder|TtshopUser whereIntegral($value)
 * @method static Builder|TtshopUser whereIsApp($value)
 * @method static Builder|TtshopUser whereIsClerk($value)
 * @method static Builder|TtshopUser whereIsDelete($value)
 * @method static Builder|TtshopUser whereIsDistributor($value)
 * @method static Builder|TtshopUser whereIsWeb($value)
 * @method static Builder|TtshopUser whereLevel($value)
 * @method static Builder|TtshopUser whereMchId($value)
 * @method static Builder|TtshopUser whereMoney($value)
 * @method static Builder|TtshopUser whereNickname($value)
 * @method static Builder|TtshopUser whereParentId($value)
 * @method static Builder|TtshopUser whereParentUserId($value)
 * @method static Builder|TtshopUser wherePassword($value)
 * @method static Builder|TtshopUser wherePlatform($value)
 * @method static Builder|TtshopUser wherePrice($value)
 * @method static Builder|TtshopUser whereSaasId($value)
 * @method static Builder|TtshopUser whereShopId($value)
 * @method static Builder|TtshopUser whereStoreId($value)
 * @method static Builder|TtshopUser whereTime($value)
 * @method static Builder|TtshopUser whereTotalIntegral($value)
 * @method static Builder|TtshopUser whereTotalPrice($value)
 * @method static Builder|TtshopUser whereType($value)
 * @method static Builder|TtshopUser whereUsername($value)
 * @method static Builder|TtshopUser whereWechatOpenId($value)
 * @method static Builder|TtshopUser whereWechatPlatformOpenId($value)
 * @method static Builder|TtshopUser whereWechatUnionId($value)
 * @mixin \Eloquent
 */
class TtshopUser extends Model
{
    use HasFactory;
    
    public $connection = 'mysql_mall';
    protected $table = 'ttshop_user';
}
