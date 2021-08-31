<?php

namespace App\Models;

use App\Exceptions\LogicException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CheckPaymentPasswordLog extends Model
{
    use HasFactory;

    protected $table = 'check_payment_password_log';
    protected $fillable = [
        'uid',
        'time',
        'num',
        'remark',
        'created_at',
        'updated_at',
    ];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

}
