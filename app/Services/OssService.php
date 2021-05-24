<?php

namespace App\Services;

use App\Exceptions\LogicException;
use Illuminate\Support\Facades\Storage;
use OSS\OssClient;

class OssService
{

    /**上传图片
     *
     * @param string $content 图片BASE64字符串
     *
     * @param string $path    保存文件夹路径 [如: 'business/']
     *
     * @return string
     * @throws \App\Exceptions\LogicException
     * @throws \OSS\Core\OssException
     */
    public static function base64Upload($content, $path = 'business/')
    {
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $content, $result)) {
            $type = $result[ 2 ];
            $img_content = base64_decode(str_replace($result[ 1 ], '', $content));
            $filename = sha1($img_content) . '.' . $type;
            $new_file = $path . $filename;
            // 写入到oss
            $ossClient = new OssClient(env('OSS_ACCESS_ID'), env('OSS_ACCESS_KEY'), env('OSS_ENDPOINT'));
            $res = $ossClient->putObject(env('OSS_BUCKET'), $new_file, $img_content);
            if ($res[ 'info' ][ 'http_code' ] == 200) {
                return '/' . $new_file;
            } else {
                throw new LogicException('图片提交失败，请稍后再试，或联系管理员', 197);
            }
        } else {
            throw new LogicException('图片上传失败', 197);
        }
    }
}
