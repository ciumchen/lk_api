<?php

namespace App\Models;

use App\Exceptions\LogicException;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Log;

/**
 * Class User
 *
 * @property int                                                                                                            $id
 * @property int|null                                                                                                       $invite_uid               邀请人id
 * @property int                                                                                                            $role                     1普通用户，2商家
 * @property int                                                                                                            $business_lk              商家权
 * @property int                                                                                                            $lk                       消费者权
 * @property string                                                                                                         $integral                 消费者积分
 * @property string                                                                                                         $business_integral        商家积分
 * @property string                                                                                                         $phone                    手机号
 * @property string|null                                                                                                    $username                 用户名
 * @property string|null                                                                                                    $avatar                   用户名头像
 * @property string                                                                                                         $salt                     盐
 * @property string                                                                                                         $code_invite              邀请码
 * @property int                                                                                                            $status                   1正常，2异常
 * @property int                                                                                                            $is_auth                  1未实名，2已实名
 * @property \Illuminate\Support\Carbon|null                                                                                $created_at
 * @property \Illuminate\Support\Carbon|null                                                                                $updated_at
 * @property string                                                                                                         $return_integral          已返消费者积分
 * @property string                                                                                                         $return_business_integral 已返商家积分
 * @property string                                                                                                         $return_lk                已返LK积分
 * @property string|null                                                                                                    $ban_reason               封禁原因
 * @property int                                                                                                            $member_head              1为普通用户，2为盟主
 * @property string                                                                                                         $sign                     个性签名
 * @property int                                                                                                            $sex                      性别:0保密,1男,2女
 * @property string|null                                                                                                    $birth                    生日
 * @property string                                                                                                         $real_name                真实姓名
 * @property-read \App\Models\BusinessData|null                                                                             $businessData
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Order[]                                              $businessOrder
 * @property-read int|null                                                                                                  $business_order_count
 * @property-read string                                                                                                    $avatar_url
 * @property-read string                                                                                                    $sex_text
 * @property-read User|null                                                                                                 $inviteUserData
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null                                                                                                  $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[]                           $tokens
 * @property-read int|null                                                                                                  $tokens_count
 * @property-read \App\Models\UserData|null                                                                                 $userData
 * @method static UserFactory factory(...$parameters)
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereAvatar($value)
 * @method static Builder|User whereBanReason($value)
 * @method static Builder|User whereBirth($value)
 * @method static Builder|User whereBusinessIntegral($value)
 * @method static Builder|User whereBusinessLk($value)
 * @method static Builder|User whereCodeInvite($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereIntegral($value)
 * @method static Builder|User whereInviteUid($value)
 * @method static Builder|User whereIsAuth($value)
 * @method static Builder|User whereLk($value)
 * @method static Builder|User whereMemberHead($value)
 * @method static Builder|User wherePhone($value)
 * @method static Builder|User whereRealName($value)
 * @method static Builder|User whereReturnBusinessIntegral($value)
 * @method static Builder|User whereReturnIntegral($value)
 * @method static Builder|User whereReturnLk($value)
 * @method static Builder|User whereRole($value)
 * @method static Builder|User whereSalt($value)
 * @method static Builder|User whereSex($value)
 * @method static Builder|User whereSign($value)
 * @method static Builder|User whereStatus($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User whereUsername($value)
 * @mixin \Eloquent
 * @package App\Models
 * @property int
 *               $market_business          市商身份，0不是，1是
 * @method static Builder|User whereMarketBusiness($value)
 * @property int|null $shop_uid 商城用户uid
 * @method static Builder|User whereShopUid($value)
 * @property string $alipay_user_id 用户支付宝ID
 * @property string $alipay_account 用户支付宝账户
 * @method static Builder|User whereAlipayAccount($value)
 * @method static Builder|User whereAlipayUserId($value)
 * @property int|null $member_status 来客会员状态，0普通用户，1会员
 * @property string $alipay_nickname 用户支付宝昵称
 * @property string $alipay_avatar 用户支付宝头像
 * @method static Builder|User whereAlipayAvatar($value)
 * @method static Builder|User whereAlipayNickname($value)
 * @method static Builder|User whereMemberStatus($value)
 */
class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;
    
    //正常用户状态
    const STATUS_NORMAL = 1;
    
    const STATUS_BANNED = 2;//已封禁
    
    const ROLE_NORMAL   = 1;  //普通用户
    
    const ROLE_BUSINESS = 2;//商家
    
    const NO_IS_AUTH    = 1;   //未实名
    
    const YES_IS_AUTH   = 2;  //已实名
    
    const CUSTOMER      = 1;     //普通用户
    
    const LEADER        = 2;       //盟主
    
    /**
     * 用户信息
     */
    public function userData()
    {
        return $this->hasOne(UserData::class, 'uid');
    }
    
    /**
     * 邀请用户信息
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function inviteUserData()
    {
        return $this->hasOne(User::class, 'id', 'invite_uid');
    }
    
    /**
     * 商家信息
     */
    public function businessData()
    {
        return $this->hasOne(BusinessData::class, 'uid');
    }
    
    /**订商家单
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function businessOrder()
    {
        return $this->hasMany(Order::class, 'business_uid');
    }
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'invite_uid',
        'phone',
        'username',
        'salt',
        'code_invite',
    ];
    /**
     * 附加字段
     *
     * @var string[]
     */
    protected $appends = ['avatar_url', 'sex_text'];
    public $sex_status = [
        '0' => '保密',
        '1' => '男',
        '2' => '女',
    ];
    /**
     * 默认头像图片
     *
     * @var string[]
     */
    public $avatar_default = [
        'https://static.catspawvideo.com/no_avatar/1.png',
        'https://static.catspawvideo.com/no_avatar/2.png',
        'https://static.catspawvideo.com/no_avatar/3.png',
        'https://static.catspawvideo.com/no_avatar/4.png',
        'https://static.catspawvideo.com/no_avatar/5.png',
    ];
    
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($user) {
            if (null === $user->username) {
                do {
                    $user->username = '用户_'.Str::random(8);
                } while (static::where('username', $user->username)->exists());
            }
            if (null === $user->code_invite) {
                do {
                    $user->code_invite = Str::random(6);
                } while (static::where('code_invite', $user->code_invite)->exists());
            }
            if (null === $user->avatar) {
//                $default = [
//                    'https://static.catspawvideo.com/no_avatar/1.png',
//                    'https://static.catspawvideo.com/no_avatar/2.png',
//                    'https://static.catspawvideo.com/no_avatar/3.png',
//                    'https://static.catspawvideo.com/no_avatar/4.png',
//                    'https://static.catspawvideo.com/no_avatar/5.png',
//                ];
                $user->avatar = $user->avatar_default[ array_rand($user->avatar_default) ];
            }
            if (null === $user->salt) {
                $user->salt = Str::random(6);
            }
        });
    }
    
    /**
     * 头像获取器
     *
     * @param $value
     *
     * @return string
     */
    public function getAvatarUrlAttribute($value)
    {
        $value = $this->attributes[ 'avatar' ] ?? '';
        if (empty($value) || in_array($value, $this->avatar_default)) {
            return $value;
        } else {
            return env('OSS_URL').$value;
        }
    }
    
    /**
     * 性别文字获取器
     *
     * @param $value
     *
     * @return string
     */
    public function getSexTextAttribute($value)
    {
        return $this->sex_status[ intval($this->attributes[ 'sex' ] ?? 0) ];
    }
    
    public function getBirthAttribute($value)
    {
        return $value ?: '';
    }
    
    /**
     * 修改密码
     *
     * @param string $password
     *
     * @return void
     */
    public function changePassword(string $password)
    {
        $salt = Str::random(6);
        //$password = '123456';
        Password::create([
                             'password' => encrypt_password($this->phone, $password, $salt),
                         ]);
        $this->update(['salt' => $salt]);
    }
    
    public function changePassword2(string $password)
    {
        $salt = Str::random(6);
        //$password = '123456';
        log::debug("=================修改用户手机号==================================".$this->phone);
        log::debug("=================修改用户手机号==================================".$salt);
        Password::create([
                             'password' => encrypt_password($this->phone, $password, $salt),
                         ]);
        $this->update(['salt' => $salt]);
    }
    
    /**
     * 确认密码是否正确.
     *
     * @param string $password
     *
     * @return bool
     */
    public function verifyPassword(string $password)
    : bool {
        $password = encrypt_password($this->phone, $password, $this->salt);
        return Password::where('password', $password)->exists();
    }
    
    /**
     * 根据手机号获取单条用户信息
     *
     * @param $phone
     *
     * @return mixed
     *
     */
    public function getUserByPhone($phone)
    {
        return $this->where('phone', '=', $phone)->first();
    }
    
    /**用户状态
     *
     * @return bool
     */
    public function checkStatus()
    {
        if ($this->status == self::STATUS_NORMAL) {
            return true;
        }
        throw new LogicException('此账号状态异常，请联系客服申诉。');
    }
    
    /**
     * 确认手机号码是否已存在.
     *
     * @param string $phone
     *
     * @return bool
     */
    public static function hasPhone(string $phone)
    : bool {
        return static::where('phone', $phone)->exists();
    }
    
    /**
     * 是否已实名认证
     *
     * @return bool
     */
    public function isVerifiedRealName()
    : bool
    {
        return true;
        return self::YES_IS_AUTH === $this->is_auth;
    }
}
