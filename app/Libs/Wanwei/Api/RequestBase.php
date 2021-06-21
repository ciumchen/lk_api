<?php

namespace Wanwei\Api;

use Wanwei\Conf\Config;
use Wanwei\Http\ShowapiRequest;

class RequestBase
{
    
    protected $config;
    
    protected $result;
    
    public function __construct(Config $config = null)
    {
        if (empty($config)) {
            $config = new Config();
        }
        $this->config = $config;
    }
    
    public function getShowApi($apiMethod)
    {
        $url = $this->config->getApiUrl();//调用URL,根据情况改变此值
        if (!empty($apiMethod) && is_string($apiMethod)) {
            $url = $url . '/' . $apiMethod;
        }
        $showapi_appid = $this->config->getAppId();
        $showapi_sign = $this->config->getAppSign();
        return new ShowapiRequest($url, $showapi_appid, $showapi_sign);
    }
    
    public function fetchResult($result)
    {
        $this->result = $result;
        try {
            $res = json_decode($result, true);
            if ($res == false) {
                throw new \Exception('结果解析失败-' . $result);
            }
            if ($res[ 'showapi_res_code' ] != '0') {
                throw new \Exception($res[ 'showapi_res_error' ] . ' showapi_res_id:' . $res[ 'showapi_res_id' ]);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
