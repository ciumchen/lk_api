<?php

namespace App\Models;

use App\Exceptions\LogicException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

/**
 * Class User
 * @method $id
 * @method $invite_uid
 * @method $role
 * @method $business_lk
 * @method $lk
 * @method $integral
 * @method $business_integral
 * @method $phone
 * @method $username
 * @method $avatar
 * @method $salt
 * @method $code_invite
 * @method $status
 * @method $is_auth
 * @method $created_at
 * @method $updated_at
 * @method $return_integral
 * @method $return_business_integral
 * @method $return_lk
 * @method $ban_reason
 * @method $member_head
 *
 * @package App\Models
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
                    $user->username = '用户_' . Str::random(8);
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
            return env('OSS_URL') . $value;
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
        return $this->sex_status[intval($this->attributes[ 'sex' ] ?? 0)];
    }

    public function getBirthAttribute($value)
    {
        return $value ?:'';
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

    /**
     * 确认密码是否正确.
     *
     * @param string $password
     *
     * @return bool
     */
    public function verifyPassword(string $password)
    : bool
    {
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
        if ($this->status == self::STATUS_NORMAL)
            return true;
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
    : bool
    {
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
