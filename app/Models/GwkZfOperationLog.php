<?php

namespace App\Models;

use App\Exceptions\LogicException;
use App\Libs\Yuntong\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
