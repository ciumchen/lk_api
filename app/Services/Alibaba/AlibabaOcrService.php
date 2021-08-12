<?php

namespace App\Services\Alibaba;

use App\Exceptions\LogicException;
use App\Models\Address;
use Illuminate\Support\Facades\DB;

use Util\Autoloader;
use Util\DictionaryUtil;
use Util\HttpUtil;
use Util\MessageDigestUtil;
use Util\SignUtil;

use Http\HttpResponse;
use Http\HttpClient;
use Http\HttpRequest;

use Constant\ContentType;
use Constant\Constants;
use Constant\HttpSchema;
use Constant\HttpHeader;
use Constant\HttpMethod;
use Constant\SystemHeader;

class AlibabaOcrService
{
    private  $appKey = "203968138";
    private $appSecret = "pO2sz0WVx62xF8rXNWcRMkT0fW0vkKKW";
    private $url = "http://dm-51.data.aliyun.com/rest/160601/ocr/ocr_idcard.json";
//    private $file = "https://www.baidu.com/img/PCtm_d9c8750bed0b3c7d089fa7d55720d6cf.png";

    //如果没有configure字段，configure设为空
    public $configure = array(
    "side" => "face"
    );
        //$configure = array()
    public function getOCR($file){
        if(substr($file, 0, 4) == "http") {
            $base64 = $file;
        }else if($fp = fopen($file, "rb", 0)) {
            $binary = fread($fp, filesize($file)); // 文件读取
            fclose($fp);
            $base64 = base64_encode($binary); // 转码
        }

        $request = array(
            "image" => "$base64"
        );
        if(count($this->configure) > 0){
            $request["configure"] = json_encode($this->configure);
        }
        $body = json_encode($request);
        $response = $this->doPost($this->url, $this->appKey,$this->appSecret, $body);
        $stat = $response->getHttpStatusCode();
        if($stat == 200){
            $result_str = $response->getBody();
//            printf("result is :\n %s\n", $result_str);
            return $result_str;

        }else{
//            printf("Http error code: %d\n", $stat);
//            printf("Error msg in body: %s\n", $response->getBody());
//            printf("header: %s\n", $response->getHeader());
            return false;
        }

    }

    //
    function doPost($url, $appKey, $appSecret, $bodyContent) {
        //域名后、query前的部分
        $urlEles = parse_url($url);
        $host = $urlEles["scheme"] . "://" . $urlEles["host"];
        $path = $urlEles["path"];

        $request = new HttpRequest($host, $path, HttpMethod::POST, $appKey, $appSecret);

        //设定Content-Type，根据服务器端接受的值来设置
        $request->setHeader(HttpHeader::HTTP_HEADER_CONTENT_TYPE, ContentType::CONTENT_TYPE_JSON);

        //设定Accept，根据服务器端接受的值来设置
        $request->setHeader(HttpHeader::HTTP_HEADER_ACCEPT, ContentType::CONTENT_TYPE_JSON);

        //注意：业务body部分，不能设置key值，只能有value
        if (0 < strlen($bodyContent)) {
            $request->setHeader(HttpHeader::HTTP_HEADER_CONTENT_MD5, base64_encode(md5($bodyContent, true)));
            $request->setBodyString($bodyContent);
        }

        //指定参与签名的header
        $request->setSignHeader(SystemHeader::X_CA_TIMESTAMP);
        $response = HttpClient::execute($request);
        return $response;
    }




}
