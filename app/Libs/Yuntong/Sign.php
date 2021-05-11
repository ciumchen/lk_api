<?php

namespace App\Libs\Yuntong;

use Exception;

class Sign
{

    /**
     * @param array $data
     * @param array $salt
     * @return string
     * @throws Exception
     */
    static public function make(array $data, array $salt = [])
    {
        try {
            if (is_array($data) && !empty($data)) {
                $status = ksort($data);
//                dump($data);
                if ($status == false) {
                    throw new Exception('array key sort failed');
                }
                if ( !empty($salt)) {
                    $data = array_merge($data, $salt);
                }
//                dump($data);
                $str = self::params_build($data);
//                dump($str);
                $sign = md5($str);
            } else {
                throw new Exception('sign can not be generate by empty array.');
            }
        } catch (Exception $e) {
            throw $e;
        }
        return $sign;
    }

    /**
     * @param array $data
     * @param array $salt
     * @return bool
     * @throws Exception
     */
    static public function check(array $data, array $salt = [])
    {
        try {
//            dump($data);
            $origin_sign = $data[ 'sign' ];
            $exclude = ['create_time', 'sign', 'pay_time', 'refund_time'];
            foreach ($exclude as $key) {
                if (array_key_exists($key, $data)) {
                    unset($data[ $key ]);
                }
            }
            $sign = self::make($data, $salt);
//            dump(strtoupper($origin_sign));
//            dump(strtoupper($sign));
//            dump($data);
            if (strtolower($sign) == strtolower($origin_sign)) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param array $array
     * @return string
     */
    static public function params_build($array = [])
    {
        $str = '';
        foreach ($array as $k => $val) {
            $str .= $k . '=' . $val . '&';
        }
        $str = trim($str, '&');
        return $str;
    }


}
