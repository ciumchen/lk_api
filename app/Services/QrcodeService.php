<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrcodeService
{

    public function userShareQrcode($id, $text)
    {
        $share_img_path = public_path('images/share/' . $id . '.png');
        if (!file_exists($share_img_path)) {
            $base_path = base_path();
            $img_path = resource_path('img' . DIRECTORY_SEPARATOR . 'icon.png');
            $relative_path = str_replace($base_path, '', $img_path);
            QrCode::errorCorrection('H')
                  ->size(220)
                  ->margin(2)
                  ->format('png')
                  ->style('round')
                  ->merge($relative_path, '0.2')
                  ->encoding('UTF-8')
                  ->generate($text, $share_img_path);
        }
        return str_replace('\\', '/', url('') . str_replace(public_path(), '', $share_img_path));
    }
}
