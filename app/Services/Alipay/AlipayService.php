<?php

namespace App\Services\Alipay;

use AlipayAop\AopClient;

class AlipayService
{
    public function test()
    {
        try {
            $aa = new AopClient();
            dump($aa);
        } catch (\Exception $e) {
            dd('1');
            echo $e->getMessage();
        }
        return $aa;
    }
}
