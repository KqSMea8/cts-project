<?php
class Tool_Message {
	public static function sendmsg_scheme($redenvelopeid, $clientVersion, $content, $title) {
		$pagetitle = '发微博';
		if(substr($clientVersion, 0, 3) < 4.5){//4.5以下版本
			$pageid = 'give';
			$content = urlencode($content) ;
			$url = sprintf(Comm_Config::get('scheme.send_weibo_big'), $content, $pageid, $pagetitle);
			$url .= '&title='. $pagetitle;
		}else{//4.5以上
			//$desUrl = urlencode('http://mall.sc.weibo.com/redenvelope/create?sinainternalbrowser=topnav&page=2');
			//$short_url = Tool_Shorturl::shorten($desUrl) ;
			$short_url = 'http://t.cn/RzGmQbl' ;
			$content = urlencode($content . $short_url);
			$urls = json_encode(array(
					array(
							'title'   => $title,
							'icon'    => 'http://u1.sinaimg.cn/upload/2014/06/20/timeline_card_small_money.png',
							'content' => $content,
					)
			));
			$urls = urlencode($urls);
			$url = sprintf(Comm_Config::get('scheme.send_weibo_small'), $content, $urls, $pagetitle);
		}
		return  $url . '&go_home=1';
	}
}