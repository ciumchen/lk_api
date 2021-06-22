<?php

namespace App\Models;

use App\Exceptions\LogicException;
use App\Services\PassengerService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * App\Models\AirPassenger
 *
 * @property int $id
 * @property int|null $uid users 表 id
 * @property string|null $pname 乘客姓名
 * @property string|null $pidcard 乘客身份证号码
 * @property string|null $pphone 乘客手机号码
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|AirPassenger newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AirPassenger newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AirPassenger query()
 * @method static \Illuminate\Database\Eloquent\Builder|AirPassenger whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirPassenger whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirPassenger wherePidcard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirPassenger wherePname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirPassenger wherePphone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirPassenger whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirPassenger whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AirPassenger extends Model
{
    use HasFactory;

    protected $table = 'air_passenger';

    /**新增乘客信息
     * @param array $data
     * @return mixed
     * @throws
     */
    public function setPassenger(array $data)
    {
        $date = date('Y-m-d H:i:s');
        $idCard = (new PassengerService())->isCard($data['pidcard']);
        if (!$idCard)
        {
            throw new LogicException('请输入正确的身份证号码');
        }

        $passenger = new AirPassenger();
        $passenger->uid = (int)$data['uid'];
        $passenger->pname = $data['pname'];
        $passenger->pidcard = $data['pidcard'];
        $passenger->pphone = $data['pphone'];
        $passenger->created_at = $date;
        $passenger->updated_at = $date;
        $passenger->save();
    }

    /**获取乘客信息
     * @param string $uid
     * @return mixed
     * @throws
     */
    public function getPassenger(string $uid)
    {
        $users = (new AirPassenger())::where('uid', $uid)->exists();
        if (!$users)
            throw new LogicException('该用户不存在');

        return (new AirPassenger())::where('uid', $uid)->get(['id', 'pname', 'pidcard', 'pphone'])->toArray();
    }

    /**删除乘客信息
     * @param string $id
     * @return mixed
     * @throws
     */
    public function delPassenger(string $id)
    {
        $users = (new AirPassenger())::where('id', $id)->exists();
        if (!$users)
            throw new LogicException('该用户不存在');

        $res = (new AirPassenger())::where('id', $id)->delete();
        if ($res)
        {
            return json_encode(['code' => 200, 'msg' => '删除成功']);
        } else
        {
            return json_encode(['code' => 1000, 'msg' => '删除失败']);
        }
    }

    /**更新乘客信息
     * @param array $data
     * @return mixed
     * @throws
     */
    public function savePassenger(array $data)
    {
        $users = (new AirPassenger())::where('id', $data['id'])->exists();
        if (!$users)
            throw new LogicException('该用户不存在');

        $passengerData = (new AirPassenger())::find($data['id']);
        $passengerData->pname = $data['pname'];
        $passengerData->pidcard = $data['pidcard'];
        $passengerData->pphone = $data['pphone'];
        $passengerData->updated_at = date('Y-m-d H:i:s');
        $res = $passengerData->save();

        if ($res)
        {
            return json_encode(['code' => 200, 'msg' => '保存成功']);
        } else
        {
            return json_encode(['code' => 1000, 'msg' => '保存失败']);
        }
    }
}
