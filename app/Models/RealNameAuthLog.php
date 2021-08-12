<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RealNameAuthLog extends Model
{
    use HasFactory;

    protected $table = 'real_name_auth_log';
    protected $fillable = [
        'uid',
        'day',
        'second',
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