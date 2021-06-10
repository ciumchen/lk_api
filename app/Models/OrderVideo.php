<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderVideo
 *
 * @property int    id
 * @property string order_no
 * @property int    user_id
 * @property string account
 * @property int    order_id
 * @property int    item_id
 * @property float  money
 * @property string trade_no
 * @property int    pay_status
 * @property int    status
 * @property string goods_title
 * @property int    create_type 订单类型:1优酷会员,2迅雷会员,3土豆会员,4爱奇艺会员,5乐视会员,6好莱坞会员,7芒果TV移动PC端会员,8芒果TV全屏会员,9搜狐会员,10腾讯会员,
 * @property string created_at
 * @property string updated_at
 *
 * @package App\Models
 */
class OrderVideo extends Model
{
    
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
}
