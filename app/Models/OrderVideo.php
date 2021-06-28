<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderVideo
 *
 * @package App\Models
 * @property int                             $id
 * @property string                          $order_no    订单号
 * @property int                             $user_id     充值用户ID
 * @property string                          $account     充值账号
 * @property int                             $order_id    订单表ID
 * @property string                          $money       充值金额
 * @property string                          $trade_no    接口方返回单号
 * @property int                             $pay_status  平台订单付款状态:0未付款,1已付款
 * @property int                             $status      充值状态:0充值中,1成功,9撤销
 * @property string                          $goods_title 商品名称
 * @property string                          $item_id     会员充值 标准商品编号
 * @property int                             $create_type
 *           订单类型:1优酷会员,2迅雷会员,3土豆会员,4爱奇艺会员,5乐视会员,6好莱坞会员,7芒果TV移动PC端会员,8芒果TV全屏会员,9搜狐会员,10腾讯会员,
 * @property string                          $channel     下单渠道:bm斑马力方,ww万维易源
 * @property string|null                     $card_list   订单卡密信息
 * @property \Illuminate\Support\Carbon|null $created_at  创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at  更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|OrderVideo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderVideo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderVideo query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderVideo whereAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderVideo whereCreateType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderVideo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderVideo whereGoodsTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderVideo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderVideo whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderVideo whereMoney($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderVideo whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderVideo whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderVideo wherePayStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderVideo whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderVideo whereTradeNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderVideo whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderVideo whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderVideo whereCardList($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderVideo whereChannel($value)
 * @mixin \Eloquent
 */
class OrderVideo extends Model
{
    
    protected $table = 'order_video';
    
    /* 订单渠道标识 */
    const CHANNEL_WANWEI      = 'ww'; /* 万维易源渠道标识 */
    const CHANNEL_BANMALIFANG = 'bm'; /* 斑马力方渠道标识 */
    /* 订单类型状态 */
    const CREATE_TYPE_YOUKU       = '1'; /* 优酷会员 */
    const CREATE_TYPE_XUNLEI      = '2'; /* 迅雷会员 */
    const CREATE_TYPE_TUDOU       = '3'; /* 土豆会员 */
    const CREATE_TYPE_IQIYI       = '4'; /* 爱奇艺会员 */
    const CREATE_TYPE_LESHI       = '5'; /* 乐视会员 */
    const CREATE_TYPE_HOLLYWOOD   = '6'; /* 好莱坞会员 */
    const CREATE_TYPE_MONGO_PC_TV = '7'; /* 芒果TV移动PC端会员 */
    const CREATE_TYPE_MONGO_TV    = '8'; /* 芒果TV全屏会员 */
    const CREATE_TYPE_SOHU        = '9'; /* 搜狐会员 */
    const CREATE_TYPE_TENCENT     = '10'; /* 腾讯会员 */
    /* 第三方订单状态 */
    const STATUS_DEFAULT = '0'; /* 默认状态[未获取] */
    const STATUS_SUCCESS = '1'; /* 获取成功 */
    const STATUS_FAIL    = '2'; /* 获取异常 */
    const STATUS_CANCEL  = '9'; /* 撤销 */
    /**
     * @var string[] 渠道标识对应文字
     */
    public static $channel_text = [
        self::CHANNEL_WANWEI      => '万维易源',
        self::CHANNEL_BANMALIFANG => '斑马力方',
    ];
    
    /**
     * @var string[] 订单类型对应文字
     */
    public static $createTypeTexts = [
        self::CREATE_TYPE_YOUKU       => '优酷会员',
        self::CREATE_TYPE_XUNLEI      => '迅雷会员',
        self::CREATE_TYPE_TUDOU       => '土豆会员',
        self::CREATE_TYPE_IQIYI       => '爱奇艺会员',
        self::CREATE_TYPE_LESHI       => '乐视会员',
        self::CREATE_TYPE_HOLLYWOOD   => '好莱坞会员',
        self::CREATE_TYPE_MONGO_PC_TV => '芒果TV移动PC端会员',
        self::CREATE_TYPE_MONGO_TV    => '芒果TV全屏会员',
        self::CREATE_TYPE_SOHU        => '搜狐会员',
        self::CREATE_TYPE_TENCENT     => '腾讯会员',
    ];
    
    /**
     * @var string[] 订单状态对应文字
     */
    public static $statusTexts = [
        self::STATUS_DEFAULT => '未获取',
        self::STATUS_SUCCESS => '获取成功',
        self::STATUS_FAIL    => '获取异常',
        self::STATUS_CANCEL  => '撤销',
    ];
    
    use HasFactory;
    
    /**
     * 斑马力方接口订单
     *
     * @param  string  $account
     * @param  float   $money
     * @param  string  $order_no
     * @param  int     $order_id
     * @param  int     $uid
     * @param  int     $item_id
     *
     * @return $this
     * @throws \Exception
     */
    public function setBmOrder($account, $money, $order_no, $order_id, $uid, $item_id)
    {
        try {
            $this->account = $account;
            $this->money = $money;
            $this->order_id = $order_id;
            $this->item_id = $item_id;
            $this->order_no = $order_no;
            $this->channel = self::CHANNEL_BANMALIFANG;
            $this->user_id = $uid;
            $this->save();
        } catch (Exception $e) {
            throw  $e;
        }
        return $this;
    }
    
    /**
     * Description:万维易源接口订单
     *
     * @param  int     $uid       用户ID
     * @param  int     $order_id  订单ID
     * @param  string  $order_no  订单号
     * @param  float   $money     支付价格
     * @param  string  $genusId   商品ID
     *
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/6/22 0022
     */
    public function setWanWeiOrder($uid, $order_id, $order_no, $money, $genusId)
    {
        try {
            $this->channel = self::CHANNEL_WANWEI;
            $this->money = $money;
            $this->user_id = $uid;
            $this->order_id = $order_id;
            $this->order_no = $order_no;
            $this->item_id = $genusId;
            $this->save();
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Description:万维爱奇艺订单
     *
     * @param  int     $uid       用户ID
     * @param  int     $order_id  订单ID
     * @param  string  $order_no  订单号
     * @param  float   $money     支付价格
     * @param  string  $genusId   商品ID
     *
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/6/22 0022
     */
    public function setWanWeiIQYiOrder($uid, $order_id, $order_no, $money, $genusId)
    {
        try {
            switch ($genusId) {
                case '3b347362cf63be7632d8f064a652821a': /* 爱奇艺黄金会员 月卡 */
                    $title = '爱奇艺黄金会员(月卡)';
                    break;
                case '021ea602b1a5be7bda2ce8d5b30e1a25': /* 爱奇艺黄金会员 季卡*/
                    $title = '爱奇艺黄金会员(季卡)';
                    break;
                case 'a6b189913e61860c05fe286df309f390': /* 爱奇艺黄金会员 年卡*/
                    $title = '爱奇艺黄金会员(年卡)';
                    break;
                case '53d3ee944e34d86dedba7c17ccf0dd5b': /* 爱奇艺黄金会员 半年卡*/
                    $title = '爱奇艺黄金会员(半年卡)';
                    break;
                default:
                    $title = '';
            }
            $this->create_type = self::CREATE_TYPE_IQIYI;
            $this->goods_title = $title;
            $this->setWanWeiOrder($uid, $order_id, $order_no, $money, $genusId);
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Description:万维 优酷订单
     *
     * @param  int     $uid       用户ID
     * @param  int     $order_id  订单ID
     * @param  string  $order_no  订单号
     * @param  float   $money     支付价格
     * @param  string  $genusId   商品ID
     *
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/6/22 0022
     */
    public function setWanweiYouKuOrder($uid, $order_id, $order_no, $money, $genusId)
    {
        try {
            switch ($genusId) {
                case 'abce9ed46812ae4c7472cae99c5f00dd': /* 优酷黄金会员 周卡 */
                    $title = '优酷黄金会员(周卡)';
                    break;
                case 'cc5da3a37df39b254e399e028bf268b6': /* 优酷黄金会员 月卡*/
                    $title = '优酷黄金会员(月卡)';
                    break;
                case 'f0478e790d16a6e1433b57eae4191010': /* 优酷黄金会员 季卡*/
                    $title = '优酷黄金会员(季卡)';
                    break;
                default:
                    $title = '';
            }
            $this->create_type = self::CREATE_TYPE_YOUKU;
            $this->goods_title = $title;
            $this->setWanWeiOrder($uid, $order_id, $order_no, $money, $genusId);
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Description:万维 腾讯视频VIP订单
     *
     * @param  int     $uid       用户ID
     * @param  int     $order_id  订单ID
     * @param  string  $order_no  订单号
     * @param  float   $money     支付价格
     * @param  string  $genusId   商品ID
     *
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/6/22 0022
     */
    public function setWanweiTencentOrder($uid, $order_id, $order_no, $money, $genusId)
    {
        try {
            switch ($genusId) {
                case 'aa72ad792faa41e09f1c93943a3db890': /* 腾讯视频VIP 月卡*/
                    $title = '腾讯视频VIP(月卡)';
                    break;
                case 'ff2a2437e2d91c5cbc15eec7fbfe77b1': /* 腾讯视频VIP 季卡*/
                    $title = '腾讯视频VIP(季卡)';
                    break;
                case 'd63fb08d8cbce1e4ccec0237ae871299': /* 腾讯视频VIP 年卡*/
                    $title = '腾讯视频VIP(年卡)';
                    break;
                default:
                    $title = '';
            }
            $this->create_type = self::CREATE_TYPE_TENCENT;
            $this->goods_title = $title;
            $this->setWanWeiOrder($uid, $order_id, $order_no, $money, $genusId);
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * 腾讯会员
     *
     * @param  string  $account
     * @param  float   $money
     * @param  string  $order_no
     * @param  int     $order_id
     * @param  int     $uid
     * @param  int     $item_id
     *
     * @return $this
     * @throws \Exception
     */
    public function setTencentOrder($account, $money, $order_no, $order_id, $uid, $item_id)
    {
        try {
            $this->create_type = self::CREATE_TYPE_TENCENT;
            $this->setBmOrder($account, $money, $order_no, $order_id, $uid, $item_id);
        } catch (Exception $e) {
            throw  $e;
        }
        return $this;
    }
    
    /**
     * 搜狐会员
     *
     * @param  string  $account
     * @param  float   $money
     * @param  string  $order_no
     * @param  int     $order_id
     * @param  int     $uid
     * @param  int     $item_id
     *
     * @return $this
     * @throws \Exception
     */
    public function setSoHuOrder($account, $money, $order_no, $order_id, $uid, $item_id)
    {
        try {
            $this->create_type = '9';
            $this->setBmOrder($account, $money, $order_no, $order_id, $uid, $item_id);
        } catch (Exception $e) {
            throw  $e;
        }
        return $this;
    }
    
    /**
     * 芒果TV全屏会员
     *
     * @param  string  $account
     * @param  float   $money
     * @param  string  $order_no
     * @param  int     $order_id
     * @param  int     $uid
     * @param  int     $item_id
     *
     * @return $this
     * @throws \Exception
     */
    public function setMgTVFullScreenOrder($account, $money, $order_no, $order_id, $uid, $item_id)
    {
        try {
            $this->create_type = '8';
            $this->setBmOrder($account, $money, $order_no, $order_id, $uid, $item_id);
        } catch (Exception $e) {
            throw  $e;
        }
        return $this;
    }
    
    /**
     * 芒果TV移动PC端会员
     *
     * @param  string  $account
     * @param  float   $money
     * @param  string  $order_no
     * @param  int     $order_id
     * @param  int     $uid
     * @param  int     $item_id
     *
     * @return $this
     * @throws \Exception
     */
    public function setMgTVMobileOrder($account, $money, $order_no, $order_id, $uid, $item_id)
    {
        try {
            $this->create_type = '7';
            $this->setBmOrder($account, $money, $order_no, $order_id, $uid, $item_id);
        } catch (Exception $e) {
            throw  $e;
        }
        return $this;
    }
    
    /**
     * 好莱坞会员
     *
     * @param  string  $account
     * @param  float   $money
     * @param  string  $order_no
     * @param  int     $order_id
     * @param  int     $uid
     * @param  int     $item_id
     *
     * @return $this
     * @throws \Exception
     */
    public function setHollyWoodOrder($account, $money, $order_no, $order_id, $uid, $item_id)
    {
        try {
            $this->create_type = '6';
            $this->setBmOrder($account, $money, $order_no, $order_id, $uid, $item_id);
        } catch (Exception $e) {
            throw  $e;
        }
        return $this;
    }
    
    /**
     * 乐视会员
     *
     * @param  string  $account
     * @param  float   $money
     * @param  string  $order_no
     * @param  int     $order_id
     * @param  int     $uid
     * @param  int     $item_id
     *
     * @return $this
     * @throws \Exception
     */
    public function setLeOrder($account, $money, $order_no, $order_id, $uid, $item_id)
    {
        try {
            $this->create_type = '5';
            $this->setBmOrder($account, $money, $order_no, $order_id, $uid, $item_id);
        } catch (Exception $e) {
            throw  $e;
        }
        return $this;
    }
    
    /**
     * 爱奇艺会员
     *
     * @param  string  $account
     * @param  float   $money
     * @param  string  $order_no
     * @param  int     $order_id
     * @param  int     $uid
     * @param  int     $item_id
     *
     * @return $this
     * @throws \Exception
     */
    public function setIQYiOrder($account, $money, $order_no, $order_id, $uid, $item_id)
    {
        try {
            $this->create_type = '4';
            $this->setBmOrder($account, $money, $order_no, $order_id, $uid, $item_id);
        } catch (Exception $e) {
            throw  $e;
        }
        return $this;
    }
    
    /**
     * 土豆会员
     *
     * @param  string  $account
     * @param  float   $money
     * @param  string  $order_no
     * @param  int     $order_id
     * @param  int     $uid
     * @param  int     $item_id
     *
     * @return $this
     * @throws \Exception
     */
    public function setTuDouOrder($account, $money, $order_no, $order_id, $uid, $item_id)
    {
        try {
            $this->create_type = '3';
            $this->setBmOrder($account, $money, $order_no, $order_id, $uid, $item_id);
        } catch (Exception $e) {
            throw  $e;
        }
        return $this;
    }
    
    /**
     * 迅雷会员
     *
     * @param  string  $account
     * @param  float   $money
     * @param  string  $order_no
     * @param  int     $order_id
     * @param  int     $uid
     * @param  int     $item_id
     *
     * @return $this
     * @throws \Exception
     */
    public function setXunLeiOrder($account, $money, $order_no, $order_id, $uid, $item_id)
    {
        try {
            $this->create_type = '2';
            $this->setBmOrder($account, $money, $order_no, $order_id, $uid, $item_id);
        } catch (Exception $e) {
            throw  $e;
        }
        return $this;
    }
    
    /**
     * 优酷会员
     *
     * @param  string  $account
     * @param  float   $money
     * @param  string  $order_no
     * @param  int     $order_id
     * @param  int     $uid
     * @param  int     $item_id
     *
     * @return $this
     * @throws \Exception
     */
    public function setYouKuOrder($account, $money, $order_no, $order_id, $uid, $item_id)
    {
        try {
            $this->create_type = '1';
            $this->setBmOrder($account, $money, $order_no, $order_id, $uid, $item_id);
        } catch (Exception $e) {
            throw  $e;
        }
        return $this;
    }
    
    /**
     * 通过订单号查询订单
     *
     * @param $order_no
     *
     * @return mixed
     */
    public function getOrderByOrderNo($order_no)
    {
        return $this->where('order_no', '=', $order_no)
                    ->first();
    }
    
    /**
     * 通过订单id查询订单
     *
     * @param $order_id
     *
     * @return mixed
     */
    public function getOrderByOrderId($order_id)
    {
        return $this->where('order_id', '=', $order_id)
                    ->first();
    }
}
