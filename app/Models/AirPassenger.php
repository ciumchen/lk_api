<?php

namespace App\Models;

use App\Exceptions\LogicException;
use App\Services\PassengerService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

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
