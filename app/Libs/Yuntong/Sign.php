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
                if ($status == false) {
                    throw new Exception('array key sort failed');
                }
                if ( !empty($salt)) {
                    $data = array_merge($data, $salt);
                }
                $str = self::params_build($data);
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
            $origin_sign = $data[ 'sign' ];
            unset($data[ 'sign' ]);
            $sign = self::make($data, $salt);
            if ($sign == $origin_sign) {
                return true;
            } else {
                throw new Exception('验签失败');
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
