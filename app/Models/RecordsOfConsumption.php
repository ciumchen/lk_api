<?php

namespace App\Models;

use App\Http\Controllers\API\Message\UserMsgController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Exceptions\LogicException;
use App\Models\Users;

class RecordsOfConsumption extends Model
{

    protected $table = 'integral_log';
    protected $appends = ['updated_date'];


    public function user()
    {
        return $this->belongsTo(Users::class, 'consumer_uid','id');
    }
    public function getUpdatedDateAttribute($value)
    {
//        dd($value);
        return date("Y-m-d H:i:s",strtotime($this->attributes['updated_at']));
    }

}
