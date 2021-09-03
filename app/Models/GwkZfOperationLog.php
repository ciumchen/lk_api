<?php

namespace App\Models;

use App\Exceptions\LogicException;
use App\Libs\Yuntong\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
/**
 * App\Models\GwkZfOperationLog
 *
 * @property int $id
 * @property int $oid oid
 * @property string $order_no 订单号
 * @property int $status 1未处理，2处理中，3处理完成
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|GwkZfOperationLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GwkZfOperationLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GwkZfOperationLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|GwkZfOperationLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GwkZfOperationLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GwkZfOperationLog whereOid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GwkZfOperationLog whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GwkZfOperationLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GwkZfOperationLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GwkZfOperationLog extends Model
{
    use HasFactory;

    protected $table = 'gwk_zf_operation_log';
    protected $fillable = [
        'id',
        'oid',
        'order_no',
        'status',
        'created_at',
        'updated_at',
    ];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    //创建处理记录
    public function CreateGwkClLog($oid,$order_no,$status=1){
        $logData = new GwkZfOperationLog();
        $logData->oid = $oid;
        $logData->order_no = $order_no;
        $logData->status = $status;
        $logData->save();

    }
}
