<?php
/**
 * Tool_Waterprint 
 * 打水印方法
 * @author    xiamengyu<mengyu5@staff.sina.com.cn> 
 * @created   2014-10-24
 * @copyright copyright(2013) weibo.com all rights reserved
 */
class Tool_Waterprint
{
    /**
     * printLotteryResult 
     * 在图片上打印中奖金额
     * @param mixed $lottery 
     * @static
     * @return void
     */
    public static function printLotteryResult($lottery){
        try{
            if($lottery){
                $picPath = APPPATH . '/htdocs/images/winter.png';
                $dst = imagecreatefrompng($picPath);
                $font = APPPATH . '/htdocs/sign_simsun.ttc';//字体
                $yellow = imagecolorallocate($dst, 255, 255, 0);//字体颜色
                //调整y值
                $len = strlen($lottery);
                switch($len){
                    case 1: $x = 190; break;
                    case 2: $x = 170; break;
                    case 3: $x = 150; break;
                    case 4: $x = 120; break;
                    case 5: $x = 100; break;
                    default: $x = 50; break;
                }

                imagefttext($dst, 35, 0, $x, 135, $yellow, $font, $lottery.'元');
                list($dst_w, $dst_h, $dst_type) = getimagesize($picPath);
                $newImage = imagecreatetruecolor($dst_w, $dst_h);
                //图像会比真实图像大一些，会有白边，重新剪裁
                imagecopyresampled($newImage, $dst, 0, 0, 0, 0, $dst_w+2, $dst_h+2, $dst_w, $dst_h);
                ob_start();
                $resouse = imagepng($newImage);
                $data = ob_get_contents();
                ob_end_clean();
                imagedestroy($newImage);
                imagedestroy($dst);

                //上传到s3
                $tool_storage = new Tool_Storage('mediaweibo');
                $name = 'winterlottery' . ($lottery * 100) . '.png';
                $result = array();
                $result = $tool_storage->upload_from_variable($name, $data, 'image/png');

                //每次调用分别实例化才行，否则验签不过
                $tool_storage = new Tool_Storage('mediaweibo');
                $data = $tool_storage->getFileUrl($name);

                if (!empty($data['data'])){
                    return $data['data'];
                }
            }

            return false;
        }catch(Exception $e){
            //echo $e->getMessage();
            Tool_Log::fatal('lottery print picture error:' . $e->getMessage());
            return false;
        }
    }
}
