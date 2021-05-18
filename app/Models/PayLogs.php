<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PayLogs extends Model
{
    protected $table = 'pay_logs';

    /**交易数据写入
     * @param array $data
     * @return bool
     */
    public function setPay(array $data)
    {
        return DB::table($this->table)->insert($data);
    }
}
