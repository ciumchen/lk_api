<?php


namespace App\Libs\Yuntong;


class Sign
{
    /**
     * @param array $data
     * @param array $salt
     * @return string
     * @throws \Exception
     */
    static public function make(array $data, array $salt = [])
    {
        try {
            if (is_array($data) && !empty($data)) {
                $status = ksort($data);
                if ($status == false) {
                    throw new \Exception('array key sort failed');
                }
                $str = self::params_build($data);
                if (!empty($salt)) {
                    $str .= self::params_build($salt);
                }
                $sign = md5($str);

            } else {
                throw new \Exception('sign can not be generate by empty array.');
            }
        } catch (\Exception $e) {
            throw $e;
        }
        return $sign;
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
