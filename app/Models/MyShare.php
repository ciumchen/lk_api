<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\LogicException;
use App\Services\MyShareService;

/**
 * App\Models\MyShare
 *
 * @method static \Illuminate\Database\Eloquent\Builder|MyShare newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MyShare newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MyShare query()
 * @mixin \Eloquent
 */
class MyShare extends Model
{
    use HasFactory;

    /**用户个人分享
     * @param array $data
     * @return mixed
     * @throws    
    */
    public function isManage(array $data)
    {
        $res = $this->isHead($data['uid']);
        //获取积分
        $assetsSum = (new MyShareService)->headsIntegral($data);

        //返回
        return [
            'res' => $res,
            'assetsSum' => $assetsSum
        ];
    }

    /**用户个人分享
     * @param array $data
     * @return mixed
     * @throws    
    */
    public function userShare(array $data)
    {
        //检查用户
        $this->isUser($data['uid']);
        $this->isInvite($data['uid']);

        //返回
        return (new MyShareService)->userShare($data);
    }

    /**用户个人分享
     * @param array $data
     * @return mixed
     * @throws    
    */
    public function shopShare(array $data)
    {
        //检查用户
        $this->isUser($data['uid']);
        $this->isInvite($data['uid']);

        //返回
        return (new MyShareService)->shopShare($data);
    }

    /**用户团员资产记录
     * @param array $data
     * @return mixed
     * @throws    
    */
    public function usersAssets(array $data)
    {
        //检查用户
        $this->isUser($data['uid']);
        $this->isInvite($data['uid']);

        //返回
        return (new MyShareService)->usersAssets($data);
    }

    /**用户团长资产记录
     * @param array $data
     * @return mixed
     * @throws    
    */
    public function headsAssets(array $data)
    {
        //检查用户
        $this->isUser($data['uid']);
        $this->isInvite($data['uid']);

        //返回
        return (new MyShareService)->headsAssets($data);
    }

    /**团长团队资产记录
     * @param array $data
     * @return mixed
     * @throws    
    */
    public function teamAssets(array $data)
    {
        //检查用户
        $this->isUser($data['uid']);
        $this->isInvite($data['uid']);

        //返回
        return (new MyShareService)->teamAssets($data);
    }

    /**用户团员总奖励
     * @param array $data
     * @return mixed
     * @throws    
    */
    public function profitTotal(array $data)
    {
        //检查用户
        $this->isUser($data['uid']);
        $this->isInvite($data['uid']);

        //返回
        return (new MyShareService)->profitTotal($data);
    }

    /**用户是否存在
     * @param int $uid
     * @return mixed
     * @throws    
    */
    public function isUser(int $uid)
    {
        $res = User::where(['id' => $uid, 'status' => 1])->exists();
        if (!$res)
        {
            throw new LogicException('此用户信息不存在');
        }
    }

    /**用户团员是否存在
     * @param int $uid
     * @return mixed
     * @throws    
    */
    public function isInvite(int $uid)
    {
        $res = User::where(['invite_uid' => $uid, 'status' => 1])->exists();
        if (!$res)
        {
            throw new LogicException('此用户团员信息不存在');
        }
    }

    /**用户是否为盟主
     * @param int $uid
     * @return mixed
     * @throws    
    */
    public function isHead(int $uid)
    {
        $res = User::where(['id' => $uid, 'status' => 1, 'member_head' => 2])->exists();
        if (!$res)
        {
            throw new LogicException('此盟主用户信息不存在');
        }

        //返回
        return $res;
    }
}
