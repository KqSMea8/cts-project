<?php
/**
 * 图片处理
 * @package    Tool
 * @copyright  copyright(2012) weibo.com all rights reserved
 * @author     Stephen <zhangdi3@staff.sina.com.cn>
 * @version    2012-09-05
 */
class Tool_Image 
{
    public function getFileType($file,&$fileType,&$mime)
    {
        $file_content = @file_get_contents($file['tmp_name']);
        $bin = substr($file_content,0,2);
        $strInfo = @unpack("C2chars", $bin);
        $typeCode = intval($strInfo['chars1'].$strInfo['chars2']);
        $fileType = '';
        switch ($typeCode)
        {
            case 7790:
                $fileType = 'exe';
                return false;
                break;
            case 7784:
                $fileType = 'midi';
                return false;
                break;
            case 8297:
                $fileType = 'rar';
                return false;
                break;
            case 255216:
                $fileType = 'jpg';
                $mime = 'image/jpeg';
                return true;
                break;
            case 7173:
                $fileType = 'gif';
                $mime = 'image/gif';
                return true;
                break;
            case 6677:
                $fileType = 'bmp';
                $mime = 'image/bmp';
                return true;
                break;
            case 13780:
                $fileType = 'png';
                $mime = 'image/png';
                return true;
                break;
            default:
                return false;
                break;
        }
        return false;
    }
    
    /**    
    * 根据封面图url返回小缩略图url
    *     
    * @param string $pic
    *
    * @return string    
    */    
    public static function getSmallCoverPicUrl($pic)
    {    
        if(empty($pic))
        {   
            // 如果没有原图就用默认图
            return '/t4/appstyle/e_media/images/admin/wallpaper2.png';    
        }
        $p = strpos($pic, 'storage.mcp');
        if ( $p !== false) 
        { 
            if (strpos($pic, 'media.impress.sinaimg') === false) 
            {
                return 'http://media.impress.sinaimg.cn/square.128/' . substr($pic, $p);
            } 
            else if (strpos($pic, '/maxwidth.180') !== false) 
            {
                return str_replace('/maxwidth.180', '/square.128', $pic);
            } 
            else 
            {
                return $pic;
            }
        } 
        else if(preg_match('/ww\d\.sinaimg\.cn/', $pic)) 
        {    
            //图床图片,80*80
            $arr = explode("/", $pic);    
            $arr[3] = "square";
            return implode("/", $arr);
        }
        return $pic;
    }

    /**
     * 根据封面图url返回小缩略图url
     *
     * @param string $pic
     *
     * @return string
     */
    public static function getCoverPicture($material)
    {
        if ($material['material_picture_4'])
            return $material['material_picture_4'];
        
        return self::getSmallCoverPicUrl($material['material_picture']);
    }
    
    public static function get_dynamic_address ($image_address)
    {
        if (empty($image_address))
        {
            return false;
        }
        if (strpos($image_address, '/maxwidth.180') !== false)
        {
            return str_replace('/maxwidth.180', '/square.80', $image_address);
        }
    }
}