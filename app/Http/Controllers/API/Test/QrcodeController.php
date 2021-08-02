<?php

namespace App\Http\Controllers\API\Test;

use App\Http\Controllers\Controller;
use App\Services\QrcodeService;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrcodeController extends Controller
{
    /**
     * 测试启用
     */
    public function __construct()
    {
        die('测试接口');
    }
    
    //
    public function index(Request $request)
    {
//        dd((new QrcodeService())->userShareQrcode('tttt', '这就是链接二维码'));
//        $base_path = base_path();
//        $img_path = resource_path('img' . DIRECTORY_SEPARATOR . 'qrcode.png');
//        $relative_path = str_replace($base_path, '', $img_path);
//        $text = '这就是二维码';
//        $type = $request->input('type');
//        switch ($type) {
//            /** 合并 */
//            case 'png':
//                //生成中间有图片的二维码
//                $qr = QrCode::format('png')
//                    ->merge($relative_path)
//                    ->encoding('UTF-8')
//                    ->generate($text, public_path('images/share/test.png'));
//                break;
//            case 'png_30':
//                //生成中间有图片的二维码,且图片占整个二维码图片的30%.
//                $qr = QrCode::format('png')->merge($relative_path, .3)->generate($text);
//                break;
//            case 'rmt_png_30':
//                //生成中间有图片的二维码,且图片占整个二维码图片的30%.
//                $qr = QrCode::format('png')->merge('http://www.google.com/someimage.png', .3, true)->generate($text);
//                break;
//            /** 二进制合并 */
//            case 'str_png':
//                //生成中间有图片的二维码
//                $qr = QrCode::format('png')->mergeString(Storage::get($relative_path))->generate($text);
//                break;
//            case 'str_png_30':
//                //生成中间有图片的二维码,且图片占整个二维码图片的30%.
//                $qr = QrCode::format('png')->mergeString(Storage::get($relative_path), .3)->generate($text);
//                break;
//            case 'ee':
//                //生成中间有图片的二维码
//                $qr = QrCode::errorCorrection('H')->format('png')->merge($relative_path)->generate($text);
//                break;
//            default:
//                $qr = QrCode::size(100)->generate('test');
//        }
//        //
//        dump($qr);
//        return $qr;
    }
}
