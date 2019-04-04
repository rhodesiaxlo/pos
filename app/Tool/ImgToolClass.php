<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-09-26
 * Time: 10:36
 */

namespace App\Tool;

class ImgToolClass
{
    public function __construct()
    {
        //
    }

    public static function uplode($data,$up_dir){
        $base64_img = trim($data);
        //存放在当前目录的upload文件夹下
        if(!file_exists($up_dir)){
            mkdir($up_dir,0777);
        }
        if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_img, $result)){
            $type = $result[2];
            if(in_array($type,array('pjpeg','jpeg','jpg','gif','bmp','png'))){
                $new_file = $up_dir.date('Ymd').time().rand(10,99999).'.'.$type;
                if(file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_img)))){
                    $img_path = str_replace('../../..', '', $new_file);
                    return $img_path;
                }else{
                    echo '图片上传失败</br>';

                }
            }else{
                //文件类型错误
                echo '图片上传类型错误';
            }

        }else{
            //文件错误
            echo '文件错误';
        }
    }
}