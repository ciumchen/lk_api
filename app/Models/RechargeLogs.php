<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RechargeLogs extends Model
{
    use HasFactory;

    protected $table = 'recharge_logs';

    /**判断数据是否已存在
     * @param string $reorderId
     * @return mixed
     */
    public function exRecharges(string $reorderId)
    {
        return DB::table($this->table)->where('reorder_id', $reorderId)->exists();
    }
}
