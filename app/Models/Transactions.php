<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    use HasFactory;
    protected $table = "transactions";

    const STATUS_DEFAULT = 1;
    const STATUS_DONE = 2;

    const TX_STATUS_DEFAULT = 1;
    const TX_STATUS_SUCCESS = 2;
    const TX_STATUS_FAILED = 3;
}
