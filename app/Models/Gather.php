<?php

namespace App\Models;

use App\Exceptions\LogicException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Gather extends Model
{
    use HasFactory;

    protected $table = 'gather';

    /**获取拼团信息
     * @param Request $request
     * @return mixed
     * @throws LogicException
     */
    public function getGatherInfo ()
    {
        //返回
        $gatherData = DB::table('gather as g')
            ->leftJoin('gather_users as gu', 'g.id', 'gu.gid')
            ->where(['g.status' => 1])
            ->select(DB::raw('count(gu.id) as userTotal, g.id'))
            ->groupBy('g.id')
            ->get();

        return json_decode($gatherData, 1);
    }

    /**获取用户拼团信息
     * @param int $uid
     * @return mixed
     * @throws LogicException
     */
    public function getUserAllInfo (int $uid)
    {

    }

    /**获取用户单个拼团信息
     * @param int $gid
     * @param int $uid
     * @return mixed
     * @throws LogicException
     */
    public function getGatherUserInfo (int $gid, int $uid)
    {

    }
}
