<?php
class Tool_Separateimg
{
	public static function separate_img_from_content($content)
	{
		$res = preg_match('/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i',$content,$match);
		
		if ($res) 
		{
			$rst = $match[0];
		}
		else 
		{
			$rst = '<p style="text-indent: 1.75rem;font-size: .875rem;padding: 0 12px;line-height: 20px;">' . strip_tags($content) . '</p>';
		}
		return $rst ;
	}
}