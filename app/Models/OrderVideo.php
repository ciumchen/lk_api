<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderVideo
 *
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
 * @mixin \Eloquent
 * @package App\Models
 */
class OrderVideo extends Model
{
    
    protected $table = 'order_video';
    
    /**
     * @var string[] 订单类型对应文字
     */
    public $createTypeTexts = [
        '1'  => '优酷会员',
        '2'  => '迅雷会员',
        '3'  => '土豆会员',
        '4'  => '爱奇艺会员',
        '5'  => '乐视会员',
        '6'  => '好莱坞会员',
        '7'  => '芒果TV移动PC端会员',
        '8'  => '芒果TV全屏会员',
        '9'  => '搜狐会员',
        '10' => '腾讯会员',
    ];
    
    use HasFactory;
    
    /**
     * @param string $account
     * @param float  $money
     * @param string $order_no
     * @param int    $order_id
     * @param int    $uid
     * @param int    $item_id
     *
     * @return $this
     * @throws \Exception
     */
    public function setOrder($account, $money, $order_no, $order_id, $uid, $item_id)
    {
        try {
            $this->account = $account;
            $this->money = $money;
            $this->order_id = $order_id;
            $this->item_id = $item_id;
            $this->order_no = $order_no;
            $this->user_id = $uid;
            $this->save();
        } catch (Exception $e) {
            throw  $e;
        }
        return $this;
    }
    
    /**
     * 腾讯会员
     *
     * @param string $account
     * @param float  $money
     * @param string $order_no
     * @param int    $order_id
     * @param int    $uid
     * @param int    $item_id
     *
     * @return $this
     * @throws \Exception
     */
    public function setTencentOrder($account, $money, $order_no, $order_id, $uid, $item_id)
    {
        try {
            $this->create_type = '10';
            $this->setOrder($account, $money, $order_no, $order_id, $uid, $item_id);
        } catch (Exception $e) {
            throw  $e;
        }
        return $this;
    }
    
    /**
     * 搜狐会员
     *
     * @param string $account
     * @param float  $money
     * @param string $order_no
     * @param int    $order_id
     * @param int    $uid
     * @param int    $item_id
     *
     * @return $this
     * @throws \Exception
     */
    public function setSoHuOrder($account, $money, $order_no, $order_id, $uid, $item_id)
    {
        try {
            $this->create_type = '9';
            $this->setOrder($account, $money, $order_no, $order_id, $uid, $item_id);
        } catch (Exception $e) {
            throw  $e;
        }
        return $this;
    }
    
    /**
     * 芒果TV全屏会员
     *
     * @param string $account
     * @param float  $money
     * @param string $order_no
     * @param int    $order_id
     * @param int    $uid
     * @param int    $item_id
     *
     * @return $this
     * @throws \Exception
     */
    public function setMgTVFullScreenOrder($account, $money, $order_no, $order_id, $uid, $item_id)
    {
        try {
            $this->create_type = '8';
            $this->setOrder($account, $money, $order_no, $order_id, $uid, $item_id);
        } catch (Exception $e) {
            throw  $e;
        }
        return $this;
    }
    
    /**
     * 芒果TV移动PC端会员
     *
     * @param string $account
     * @param float  $money
     * @param string $order_no
     * @param int    $order_id
     * @param int    $uid
     * @param int    $item_id
     *
     * @return $this
     * @throws \Exception
     */
    public function setMgTVMobileOrder($account, $money, $order_no, $order_id, $uid, $item_id)
    {
        try {
            $this->create_type = '7';
            $this->setOrder($account, $money, $order_no, $order_id, $uid, $item_id);
        } catch (Exception $e) {
            throw  $e;
        }
        return $this;
    }
    
    /**
     * 好莱坞会员
     *
     * @param string $account
     * @param float  $money
     * @param string $order_no
     * @param int    $order_id
     * @param int    $uid
     * @param int    $item_id
     *
     * @return $this
     * @throws \Exception
     */
    public function setHollyWoodOrder($account, $money, $order_no, $order_id, $uid, $item_id)
    {
        try {
            $this->create_type = '6';
            $this->setOrder($account, $money, $order_no, $order_id, $uid, $item_id);
        } catch (Exception $e) {
            throw  $e;
        }
        return $this;
    }
    
    /**
     * 乐视会员
     *
     * @param string $account
     * @param float  $money
     * @param string $order_no
     * @param int    $order_id
     * @param int    $uid
     * @param int    $item_id
     *
     * @return $this
     * @throws \Exception
     */
    public function setLeOrder($account, $money, $order_no, $order_id, $uid, $item_id)
    {
        try {
            $this->create_type = '5';
            $this->setOrder($account, $money, $order_no, $order_id, $uid, $item_id);
        } catch (Exception $e) {
            throw  $e;
        }
        return $this;
    }
    
    /**
     * 爱奇艺会员
     *
     * @param string $account
     * @param float  $money
     * @param string $order_no
     * @param int    $order_id
     * @param int    $uid
     * @param int    $item_id
     *
     * @return $this
     * @throws \Exception
     */
    public function setIQYiOrder($account, $money, $order_no, $order_id, $uid, $item_id)
    {
        try {
            $this->create_type = '4';
            $this->setOrder($account, $money, $order_no, $order_id, $uid, $item_id);
        } catch (Exception $e) {
            throw  $e;
        }
        return $this;
    }
    
    /**
     * 土豆会员
     *
     * @param string $account
     * @param float  $money
     * @param string $order_no
     * @param int    $order_id
     * @param int    $uid
     * @param int    $item_id
     *
     * @return $this
     * @throws \Exception
     */
    public function setTuDouOrder($account, $money, $order_no, $order_id, $uid, $item_id)
    {
        try {
            $this->create_type = '3';
            $this->setOrder($account, $money, $order_no, $order_id, $uid, $item_id);
        } catch (Exception $e) {
            throw  $e;
        }
        return $this;
    }
    
    /**
     * 迅雷会员
     *
     * @param string $account
     * @param float  $money
     * @param string $order_no
     * @param int    $order_id
     * @param int    $uid
     * @param int    $item_id
     *
     * @return $this
     * @throws \Exception
     */
    public function setXunLeiOrder($account, $money, $order_no, $order_id, $uid, $item_id)
    {
        try {
            $this->create_type = '2';
            $this->setOrder($account, $money, $order_no, $order_id, $uid, $item_id);
        } catch (Exception $e) {
            throw  $e;
        }
        return $this;
    }
    
    /**
     * 优酷会员
     *
     * @param string $account
     * @param float  $money
     * @param string $order_no
     * @param int    $order_id
     * @param int    $uid
     * @param int    $item_id
     *
     * @return $this
     * @throws \Exception
     */
    public function setYouKuOrder($account, $money, $order_no, $order_id, $uid, $item_id)
    {
        try {
            $this->create_type = '1';
            $this->setOrder($account, $money, $order_no, $order_id, $uid, $item_id);
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
}
