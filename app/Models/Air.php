<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Air extends Model
{
    use HasFactory;

    const appKey = '10002911';
    const accessToken = '2dd520ba581a4db5a3fcbd074e19d618';
    const appSecret = 'oBfoIUjgyTREH5c70qeAueUXgAoZT0AW';
    const url = 'http://api.bm001.com/api';
}
