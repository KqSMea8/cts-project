<?php
class Tool_Picid2url {
	const PHOTO_URL_CRC = "http://ww%d.sinaimg.cn/%s/%s.%s";
	const PHOTO_URL_CRC_BACKUP = "http://wb%d.sina.cn/%s/%s.%s";
	const PHOTO_URL = "http://ss%d.sinaimg.cn/%s/%s&690";
	const PHOTO_URL_WX = "http://wx%d.sinaimg.cn/%s/%s.%s";
	public static function get_pic_url($picid, $pictype="thumbnail"){
		if(!is_array($picid)) $picid = array($picid);
		$result = array();
		$photo_url_cdc = self::PHOTO_URL_CRC;

		foreach($picid as $pid) {
			if(empty($pid)) {
                continue;
			}
			//如果是图片格式直接返回
			if(strpos($pid, "http://") !== false) {
			    $result[$pid] = $pid;
			    continue;
			}
			
			//非英文和数字
			if (preg_match('/[^a-zA-Z0-9]/i', $pid)){
				$result[$pid] = "";
				continue;
			}
			if ($pid[9] == 'w' || $pid[9] == 'y') { 
			  // 新系统显示规则，注意新系统用的crc32做的域名哈希  orignal
				if($pictype == "orignal"){
					$pictype = "large";
				}
				$hv = sprintf("%u", crc32($pid));
				$zone = fmod(floatval($hv) ,4) + 1;
				$ext = ($pid[21] == 'g' ? 'gif' : 'jpg');

				if ($pid[9] == 'w') {
					$result[$pid] = sprintf($photo_url_cdc, $zone, $pictype, $pid, $ext);
				} elseif ($pid[9] == 'y') {
					$result[$pid] = sprintf(self::PHOTO_URL_WX, $zone, $pictype, $pid, $ext);
				}
				
			} else { 
				$num = (hexdec(substr($pid, -2)) % 16) + 1;
				$result[$pid] = sprintf(self::PHOTO_URL, $num, $pictype, $pid);
			} 
		}
		return $result;	
	}
	
	/**
	 * 根据图片地址获取图片ID
	 * @param string $pic_url
	 */
	public static function get_pid_by_url($pic_url) {
	    $patten = '/^http\:\/\/[a-zA-Z0-9]?[a-zA-Z0-9\-\.]*\/[a-zA-Z0-9]+\/([a-zA-Z0-9]+).[a-zA-Z]+$/i';
	    if(preg_match($patten, $pic_url, $matches)) {
	        $pid = $matches[1];
	        return $pid;
	    } else {
            return false;
	    }
	}
}