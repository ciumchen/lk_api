<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * App\Models\RealNameAuth
 *
 * @method static \Illuminate\Database\Eloquent\Builder|RealNameAuth newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RealNameAuth newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RealNameAuth query()
 * @mixin \Eloquent
 */
class RealNameAuth extends Model
{
    use HasFactory;

    protected $table = 'real_name_auth';
    protected $fillable = [
        'uid',
        'name',
        'num_id',
        'status',
        'img_just',
        'img_back',
        'remark',
        'created_at',
        'updated_at',
    ];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
