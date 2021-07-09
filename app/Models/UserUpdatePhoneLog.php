<?php

namespace App\Models;

use App\Exceptions\LogicException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;


/**
 * App\Models\UserUpdatePhoneLog
 *
 * @property int $id
 * @property int|null $user_id users表 -- id
 * @property int|null $time 修改时间
 * @property string|null $edit_to_phone 修改的手机号
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder|UserUpdatePhoneLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserUpdatePhoneLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserUpdatePhoneLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserUpdatePhoneLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUpdatePhoneLog whereEditToPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUpdatePhoneLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUpdatePhoneLog whereTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUpdatePhoneLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUpdatePhoneLog whereUserId($value)
 * @mixin \Eloquent
 */
class UserUpdatePhoneLog extends Authenticatable
{

    use HasFactory, HasApiTokens, Notifiable;
    protected $table = 'user_update_phone_log';

    protected $fillable = [
        'user_id',
        'time',
        'edit_to_phone',
    ];

}
