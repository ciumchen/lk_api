<?php

namespace App\Models;

use App\Http\Controllers\API\Message\UserMsgController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Exceptions\LogicException;

class RecordsOfConsumption extends Model
{

    protected $table = 'integral_log';



    public function user()
    {
        return $this->belongsTo(User::class, 'consumer_uid','id');
    }


}
