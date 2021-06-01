<?php

namespace App\Models;

use App\Http\Controllers\API\Message\UserMsgController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Exceptions\LogicException;

class OrderIntegralLkDistribution extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_integral_lk_distribution';



}
