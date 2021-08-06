<?php

namespace App\Services\Alipay;

class AlipayBaseService
{
    protected $config = [
        'app_id'                  => '',
        'alipay_public_cert_path' => '',
        'alipay_root_cert_path'   => '',
        'gateway'                 => '',
        'sign_type'               => '',
        'mch_pid'                 => '',
        'app_public_cert_path'    => '',
        'alipay_public_key'       => '',
        'app_public_key'          => '',
        'app_private_key'         => '',
        'salt'                    => '',
    ];
    
    /**
     * @param array|null $config 支付宝相关配置
     *                           [
     *                           'app_id'                  => '',
     *                           'alipay_public_cert_path' => '',
     *                           'alipay_root_cert_path'   => '',
     *                           'gateway'                 => '',
     *                           'sign_type'               => '',
     *                           'mch_pid'                 => '',
     *                           'app_public_cert_path'    => '',
     *                           'alipay_public_key'       => '',
     *                           'app_public_key'          => '',
     *                           'app_private_key'         => '',
     *                           'salt'                    => '',
     *                           ]
     */
    public function __construct(array $config = null)
    {
        if (is_array($config) && !empty($config)) {
        
        }
        
    }
}
