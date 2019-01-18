<?php

if (! defined ( 'BASEPATH' ))
    exit ( 'No direct script access allowed' );
    
require_once("phpqrcode/qrlib.php");

// http://phpqrcode.sourceforge.net/
class Phpqrcode
{

    static function makePng($text, $outfile = false, $level, $size = 3, $margin = 4,$saveandprint = false )
    {
        $upload_path =  FCPATH.'/upload/'.date('Y-m-d').'/';

        //创建文件夹
        if(!is_dir($upload_path)) 
        {
            mkdir($upload_path, 0755, true);
        }

        $filename = $upload_path.$outfile;

        QRcode::png($text, $filename, $level, $size, $margin, $saveandprint); 

        $filename = str_replace(FCPATH, IMG_HOST, $filename);

        return $filename;
    }

}


?>