<?php

namespace App\Models\Traits;

trait Transaction
{
    
    public function startTrans()
    {
        try {
            \DB::beginTransaction();
        } catch (\Throwable $e) {
        }
    }
}
