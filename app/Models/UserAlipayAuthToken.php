<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\UserAlipayAuthToken
 *
 * @method static Builder|UserAlipayAuthToken newModelQuery()
 * @method static Builder|UserAlipayAuthToken newQuery()
 * @method static Builder|UserAlipayAuthToken query()
 * @mixin \Eloquent
 * @property int         $id
 * @property int         $uid       用户ID
 * @property string      $auth_code 支付宝用户授权后的auth_code
 * @property string      $app_id    用户授权APPID
 * @property string      $source    用户授权source
 * @property string      $scope     用户授权scope
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|UserAlipayAuthToken whereAppId($value)
 * @method static Builder|UserAlipayAuthToken whereAuthCode($value)
 * @method static Builder|UserAlipayAuthToken whereCreatedAt($value)
 * @method static Builder|UserAlipayAuthToken whereId($value)
 * @method static Builder|UserAlipayAuthToken whereScope($value)
 * @method static Builder|UserAlipayAuthToken whereSource($value)
 * @method static Builder|UserAlipayAuthToken whereUid($value)
 * @method static Builder|UserAlipayAuthToken whereUpdatedAt($value)
 */
class UserAlipayAuthToken extends Model
{
    use HasFactory;
    
    protected $table = 'user_alipay_auth_token';
    
    /**
     * Description:通过UID获取用户授权token
     *
     * @param $uid
     *
     * @return mixed|null
     * @author lidong<947714443@qq.com>
     * @date   2021/8/7 0007
     */
    public static function getUserAuthCode($uid)
    {
        return UserAlipayAuthToken::whereUid($uid)
                                  ->where(
                                      'created_at',
                                      '>',
                                      date('Y-m-d H:i:s', strtotime('-1 days'))
                                  )
                                  ->where('is_used', '=', '0')
                                  ->value('auth_code');
    }
    
    /**
     * Description:
     *
     * @param int    $uid
     * @param string $auth_code
     * @param string $app_id
     * @param string $source
     * @param string $scope
     *
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/8/7 0007
     */
    public function saveAuthCode($uid, $auth_code, $app_id, $source, $scope)
    {
        try {
            $this->uid = $uid;
            $this->auth_code = $auth_code;
            $this->app_id = $app_id;
            $this->source = $source;
            $this->scope = $scope;
            $this->save();
        } catch (\Exception $e) {
            throw $e;
        }
        return $this->id;
    }
}
