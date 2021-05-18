<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawLogs extends Model
{
    protected $table = 'withdraw_logs';

    //状态 1默认 2成功 3待审核
    const STATUS_DEFAULT = 1;
    const STATUS_DONE = 2;
    const STATUS_TO_BE_VIEWED = 3;
    const STATUS_REFUSE = 4;

}
