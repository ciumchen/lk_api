<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\OrderHotel
 *
 * @property int                             $id
 * @property string                          $order_no             订单号
 * @property int                             $user_id              充值用户ID
 * @property int                             $order_id             订单表ID
 * @property string                          $money                金额
 * @property string                          $trade_no             接口方返回单号
 * @property int                             $pay_status           平台订单付款状态:0未付款,1已付款
 * @property int                             $status               充值状态:0充值中,1成功,9撤销
 * @property string                          $goods_title          商品名称
 * @property string                          $item_id              商品编号[ID]
 * @property string                          $customer_name        入住人姓名，每个房间仅需填写1人。【多个人代表多个房间、使用逗号‘,’分隔】
 * @property string                          $hotel_id             酒店ID
 * @property string                          $contact_name         联系人姓名
 * @property string                          $contact_phone        联系人手机号码
 * @property string|null                     $in_date              入住时间
 * @property string|null                     $out_date             离开时间
 * @property int                             $man                  入住成人数，需和实施询价时填的一样
 * @property string|null                     $customer_arrive_time 客户到达时间 格式HH:mm:ss 例如09:20:30 表示早上9点20分30秒
 * @property string|null                     $special_remarks      特殊需求 可传入多个，格式：2,8。
 *                                                                 0 无要求
 *                                                                 2 尽量安排无烟房
 *                                                                 8 尽量安排大床 仅当床型为“X张大床或X张双床”时，此选项才有效
 *                                                                 10 尽量安排双床房 仅当床型为“X张大床或X张双床”时，此选项才有效
 *                                                                 11 尽量安排吸烟房
 *                                                                 12 尽量高楼层
 *                                                                 15 尽量安排有窗房
 *                                                                 16 尽量安排安静房间
 *                                                                 18 尽量安排相近房间
 * @property string                          $contact_email        联系人邮箱
 * @property int                             $child_num            入住儿童数，与实时询价时提交的应一致
 * @property string                          $child_ages           入住儿童的年龄，多个年龄用,分隔，与实时询价时提交的应一致
 * @property \Illuminate\Support\Carbon|null $created_at           创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at           更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel whereChildAges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel whereChildNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel whereContactEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel whereContactName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel whereContactPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel whereCustomerArriveTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel whereGoodsTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel whereHotelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel whereInDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel whereMan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel whereMoney($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel whereOutDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel wherePayStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel whereSpecialRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel whereTradeNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHotel whereUserId($value)
 * @mixin \Eloquent
 */
class OrderHotel extends Model
{
    
    use HasFactory;
    
    protected $table = 'order_hotel';
    
    /* 第三方订单状态 */
    const STATUS_DEFAULT = '0'; /* 默认状态[未获取] */
    const STATUS_SUCCESS = '1'; /* 获取成功 */
    const STATUS_FAIL    = '2'; /* 获取异常 */
    const STATUS_CANCEL  = '9'; /* 撤销 */
    /**
     * @var string[] 订单状态对应文字
     */
    public static $statusTexts = [
        self::STATUS_DEFAULT => '未获取',
        self::STATUS_SUCCESS => '获取成功',
        self::STATUS_FAIL    => '获取异常',
        self::STATUS_CANCEL  => '撤销',
    ];
    
    /**
     * Description:关联订单表
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @author lidong<947714443@qq.com>
     * @date   2021/6/29 0029
     */
    public function orders()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}
