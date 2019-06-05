<?php
/**
 * 分析链接
 *
 * @package    Tool
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */
class Tool_Analyze_Link {
	const SHORT_URL_DOMAIN = 't.cn'; //短链域名
	const SHORT_URL_DOMAIN2 = "sinaurl.cn";
	const SHORTURL_TYPE_WEB =	0;	//短url类型，默认0为网页
	const SHORTURL_TYPE_VIDEO =	1;	//视频
	const SHORTURL_TYPE_MP3 = 2; //MP3
	const SHORTURL_TYPE_WEIYUYIN = 6; //微语音
	const SHORTURL_TYPE_EVENT = 3; //活动
	const SHORTURL_TYPE_MAGIC = 4; // flash魔法表情
	const SHORTURL_TYPE_VOTE = 5; //投票
	const SHORTURL_TYPE_MOOD = 13; //心情
	const SHORTURL_TYPE_NEWS = 7; //新闻
	const SHORTURL_TYPE_KANDIAN = 23; //看点
	const SHORTURL_TYPE_GOODS = 11; //商品
	const SHORTURL_TYPE_QING = 8; //轻博客
	const SHORTURL_TYPE_WEIMANHUA = 21;//微漫画
	const SHORTURL_TYPE_GOVERNMENT_WTALK = 10;//政府微访谈
	const SHORTURL_TYPE_FANFAN = 15;//翻翻
	const SHORTURL_TYPE_BP = 25; // 特型feed  added by liuyu6
	const SHORTURL_TYPE_WEIBOOK = 24;//微读书
	const SHORTURL_TYPE_STOCK = 27;//股票
	const SHORTURL_TYPE_SINANEWS = 34;//新浪新闻	
	const SHORTURL_TYPE_WEIPAN = 28;//微盘
	const PARSE_LINK_GREP = '!http\:\/\/[a-zA-Z0-9\$\-\_\.\+\!\*\'\,\{\}\\\^\~\]\`\<\%\>\/\?\:\@\&\=(\&amp\;)\#\|]+!is';

	protected static $short_urls = array();
	protected static $short_url_infos = array();
	/**
	 * 解析短链前执行，搜集短链，为批量获取短链信息作准备
	 * @param string $content
	 * @return void|void
	 */
	public static function prepare_parse_link($content) {
		//短链解析
		preg_match_all(self::PARSE_LINK_GREP, $content, $out);
		if (!isset($out[0]) || count($out[0]) == 0) {
			return;
		}

		foreach($out[0] as $link_time) {
			$url_array = parse_url($link_time);
			if (!in_array(strtolower(trim($url_array['host'])), array(self::SHORT_URL_DOMAIN, self::SHORT_URL_DOMAIN2))) {
				continue;
			}

			$strin_short_url = trim($url_array['path'], "/");
			if (empty($strin_short_url)) {
				continue;
			}

			self::$short_urls[] = $strin_short_url;
		}
	}

	/**
	 * 批量获取prepare_parse_link搜集的短链的详细信息
	 * 
	 * @see prepare_parse_link
	 */
	public static function gen_short_url_info() {
		if (empty(self::$short_urls)) {
			return;
		}
		try{
			self::$short_url_infos = Tool_Shorturl::batch_info(self::$short_urls);
		}catch (Comm_Exception_Program $e){
			self::$short_url_infos = array();
		}
	}

	/**
	 * 短链转长短，建议依次先调用prepare_parse_link和gen_short_url_info，如果智链信息命中，直接从静态变量里取，否则直接调用接口取
	 * 
	 * @see prepare_parse_link gen_short_url_info
	 * @param array $short_urls
	 */
	public static function short_to_long(array $short_urls) {
		if (empty($short_urls)) {
			return array();
		}
		$long_urls = array();
		$q_short_urls = array();
		foreach($short_urls as $short_url) {
			if (!isset(self::$short_url_infos[$short_url])) {
				$q_short_urls[] = $short_url;
			} else {
				$long_urls[$short_url] = self::$short_url_infos[$short_url];
			}
		}
		if (empty($q_short_urls)) {
			return $long_urls;
		}
		try {
			$long_urls = array_merge($long_urls, Tool_Shorturl::batch_info($q_short_urls));
		} catch (Comm_Exception_Program $e) {
		}
		return $long_urls;
	}
	/**
	 * 
	 * 转换文本中的链接成标签
	 * @param string $content
	 */
	public static function parse_link_to_html($content, $is_forward=0, $has_root=0, $is_action=false, $mid = false){
		//短链解析
		$content_decode = htmlspecialchars_decode($content);
		preg_match_all(self::PARSE_LINK_GREP, $content_decode, $out);
		$media = $url_short = $video_from = $video_arr = $face_arr = $music_arr = array();
		$has_link = false;
		if(count($out[0])){
			$has_link = true;
			$content =  preg_replace_callback(self::PARSE_LINK_GREP,array("Tool_Analyze_Link", "searchs"), $content_decode);
			$content = htmlspecialchars($content);
			$url_short = $url_long = $source_url = $url_replace = $preg_url = array();

			foreach($out[0] as $link_item){	
				$url_array = parse_url($link_item);
				// 判断域名就是短URL,否则要转成短URL
				if(in_array(strtolower(trim($url_array['host'])),array(self::SHORT_URL_DOMAIN, self::SHORT_URL_DOMAIN2))) {
					$strin_short_url = trim($url_array['path'], "/");
					if(empty($strin_short_url)) {
						//修复 http://t.cn/  变成MD5 bug
						$url_replace[md5($link_item)] = '<a href="'.$link_item.'" target="_blank">'.$link_item.'</a>';
						$source_url[] = md5($link_item); 
						continue;
					}
					$url_short[] = $strin_short_url;
					$preg_url[md5($strin_short_url)] = $link_item;
				}else{
					$url_replace[md5($link_item)] = '<a href="'.$link_item.'" target="_blank">'.$link_item.'</a>';
				}
				$source_url[] = md5($link_item); 
			}
			$url_long = array();
			if(count($url_short)){
				$url_long = self::short_to_long($url_short);
			}
			$url_click = "";
			if(count($url_short) && count($url_long)){
				//获取配置文件信息
				$thirdapi_conf = Comm_Config::get('thirdapiconf');
				$thirdapi_conf_keys = array();
				foreach($thirdapi_conf as $key => $value) {
					if($value['feedshow'] === true) {
						$thirdapi_conf_keys[] = $key;
					}
				}
				$has_mood = false;
				foreach($url_short as $v){
					$item = $url_long[$v];
					if($item){
						$url = $item['url_short'];
						$js_html = "";
						if(isset($thirdapi_conf[$item['type']]['actiontitle'])) {
							$title = $thirdapi_conf[$item['type']]['actiontitle'];
							$item['annotations'][0]['title'] = $title;
						} else {
							$title = isset($item['annotations'][0]['title']) ? $item['annotations'][0]['title'] : Comm_I18n::text("tpls.pl.include.content.feed.media.show_source");
						}
						$title = rawurlencode(htmlspecialchars($title, ENT_QUOTES));
						$full_url = isset($item['url_long']) ? $item['url_long'] : "";
						$full_url_encode = rawurlencode($full_url);
						if(strpos($full_url,'#')){
							$full_url = "";
						}
						if(in_array($item['type'], $thirdapi_conf_keys)) {
							$source_data = '';
							if(isset($thirdapi_conf[$item['type']]['preimgsudakey']) && isset($thirdapi_conf[$item['type']]['preimgsudavalue'])) {
								$source_data = urlencode('key='.$thirdapi_conf[$item['type']]['preimgsudakey'].'&value='.$thirdapi_conf[$item['type']]['preimgsudavalue']);
								$item['annotations'][0]['source_suda'] = $source_data;
							}
							$sudadata['key'] = $thirdapi_conf[$item['type']]['sudakey'];
							$sudadata['value'] = $thirdapi_conf[$item['type']]['sudavalue'];
							if(!($has_root xor $is_forward)&& $is_action && !empty($full_url)){
								if(!empty($source_data)) {
									$js_html .= 'target="_blank" action-data="suda='.$source_data.'&title='.$title.'&amp;short_url='.$item['url_short'].'&amp;full_url='.$full_url_encode.'&amp;type='.$item['type'].'" action-type="feed_list_third_rend"';
								} else {
									$js_html .= 'target="_blank" action-data="title='.$title.'&amp;short_url='.$item['url_short'].'&amp;full_url='.$full_url_encode.'&amp;type='.$item['type'].'" action-type="feed_list_third_rend"';
								}
							}else{
								$js_html .= 'target="_blank"';
							}

							if($item['type'] == self::SHORTURL_TYPE_SINANEWS){
								$js_html = 'target="_blank"';
							}
							if(!empty($sudadata['key']) && !empty($sudadata['value'])) {
								$js_html.= ' suda-data="key='.$sudadata['key'].'&value='.$sudadata['value'].'"';
							}
							$strin_short_url_html = '<a title="'.$full_url.'" href="'.$url.'" '.$js_html.' >'.$url.'<span class="'.$thirdapi_conf[$item['type']]['feedclass'].'" title="'.$thirdapi_conf[$item['type']]['title'].'"></span></a>';
							$face_arr[] = $item['url_short'];
							if (count($media) == 0){
								$media = $item;
								$media['thirdapiflag'] = true;
								$media['sudadata'] = ' suda-data="key='.$sudadata['key'].'&value='.$sudadata['value'].'"';
								if (!isset($media['annotations'][0]['title']) || $media['annotations'][0]['title'] == '') {
									$media['annotations'][0]['title'] = Comm_I18n::text("tpls.pl.include.content.feed.media.show_source");						
								}
								if (isset($media['annotations'][0]['thumbid']) && $media['annotations'][0]['thumbid']){
									$pic_url = Tool_Picid2url::get_pic_url($media['annotations'][0]['thumbid'], 'large');
									$media['annotations'][0]['pic'] = $pic_url[$media['annotations'][0]['thumbid']];
								}elseif (isset($media['annotations'][0]['pid']) && $media['annotations'][0]['pid']){
									$pic_url = Tool_Picid2url::get_pic_url($media['annotations'][0]['pid']);
									$media['annotations'][0]['pic'] = $pic_url[$media['annotations'][0]['pid']];
								}else{
									if (isset($media['annotations'][0]['img_prev']) && $media['annotations'][0]['img_prev']) {
										$media['annotations'][0]['pic'] = $media['annotations'][0]['img_prev'];
									}
									else{
										$media['annotations'][0]['pic'] = "";
									}
								}
							}
						} else {
							switch($item['type']) {

								//@todo 扩展信息的嵌入 ，需要js指出
							case self::SHORTURL_TYPE_VIDEO:
								$video_arr[] = $item['url_short'];
								if(!($has_root xor $is_forward) && $is_action && !empty($full_url)){
									$js_html .= 'target="_blank" action-data="title='.$title.'&amp;short_url='.$item['url_short'].'&amp;full_url='.$full_url_encode.'&amp;metadata" action-type="feed_list_media_video"';
								}else{
									$js_html .= 'target="_blank"';
								}
								$strin_short_url_html = '<a title="'.$url.'" href="'.$url.'" '.$js_html.' >'.$url.'<span class="feedico_vedio" title="'.Comm_I18n::text('tpls.common.short_icon_video').'"></span></a>';
								if (count($media) == 0){
									$media = $item;
								}
								break;
							case self::SHORTURL_TYPE_WEIMANHUA:
								$manhua_arr[] = $item['url_short'];
								if(!($has_root xor $is_forward) && $is_action && !empty($full_url)){
									$js_html .= 'target="_blank" action-data="title='.$title.'&amp;short_url='.$item['url_short'].'&amp;full_url='.$full_url_encode.'&amp;metadata" action-type="feed_list_media_video"';
								}else{
									$js_html .= 'target="_blank"';
								}
								$strin_short_url_html = '<a title="'.$url.'" href="'.$url.'" '.$js_html.' >'.$url.'<span class="feedico_cartoon" title="'.Comm_I18n::text('tpls.common.short_icon_manhua').'"></span></a>';
								if (count($media) == 0){
									$item['annotations'][0]['pic'] = '';
									if($item['annotations'][0]['image_prev']) {
										$item['annotations'][0]['pic'] = $item['annotations'][0]['image_prev'];
									}
									$media = $item;
								}
								break;
							case self::SHORTURL_TYPE_WEIBOOK:
								if(!($has_root xor $is_forward) && $is_action && !empty($full_url)){
									$js_html .= 'target="_blank" action-data="title='.$title.'&amp;short_url='.$item['url_short'].'&amp;full_url='.$full_url_encode.'&amp;metadata" action-type="feed_list_media_video"';
								}else{
									$js_html .= 'target="_blank"';
								}
								$strin_short_url_html = '<a title="'.$url.'" href="'.$url.'" '.$js_html.' >'.$url.'<span class="feedico_read" title="'.Comm_I18n::text('tpls.common.short_icon_weidushu').'"></span></a>';
								if (count($media) == 0){
									$item['annotations'][0]['pic'] = '';
									if($item['annotations'][0]['image_prev']) {
										$item['annotations'][0]['pic'] = $item['annotations'][0]['image_prev'];
									}
									$media = $item;
								}
								break;
							case self::SHORTURL_TYPE_STOCK:
								$video_arr[] = $item['url_short'];
								if(!($has_root xor $is_forward) && $is_action && !empty($full_url)){
									$js_html .= 'target="_blank" action-data="title='.$title.'&amp;short_url='.$item['url_short'].'&amp;full_url='.$full_url_encode.'&amp;metadata" action-type="feed_list_media_video"';
								}else{
									$js_html .= 'target="_blank"';
								}
								$strin_short_url_html = '<a title="'.$url.'" href="'.$url.'" '.$js_html.' >'.$url.'<span class="W_ico16 icon_sw_stock" title="股票"></span></a>';
								if (count($media) == 0){
									$item['annotations'][0]['pic'] = '';
									if($item['annotations'][0]['img_prev']) {
										$item['annotations'][0]['pic'] = $item['annotations'][0]['img_prev'];
									}
									$media = $item;
								}
								break;
							case self::SHORTURL_TYPE_FANFAN:
								$manhua_arr[] = $item['url_short'];
								if(!($has_root xor $is_forward) && $is_action && !empty($full_url)){
									$js_html .= 'target="_blank" action-data="title='.$title.'&amp;short_url='.$item['url_short'].'&amp;full_url='.$full_url_encode.'&amp;metadata" action-type="feed_list_media_video"';
								}else{
									$js_html .= 'target="_blank"';
								}
								$strin_short_url_html = '<a title="'.$url.'" href="'.$url.'" '.$js_html.' >'.$url.'<span class="feedico_fanfan" title="'.Comm_I18n::text('tpls.common.short_icon_fanfan').'"></span></a>';
								if (count($media) == 0){
									$item['annotations'][0]['pic'] = '';
									if($item['annotations'][0]['img_prev']) {
										$item['annotations'][0]['pic'] = $item['annotations'][0]['img_prev'];
									}
									$media = $item;
								}

								break;
							case self::SHORTURL_TYPE_WEIYUYIN:
							case self::SHORTURL_TYPE_MP3:
								$music_arr[] = $item['url_short'];
								if(!($has_root xor $is_forward) && $is_action && !empty($full_url)){
									$js_html .= 'target="_blank" action-data="title='.$title.'&amp;short_url='.$item['url_short'].'&amp;full_url='.$full_url_encode.'" action-type="feed_list_media_music"';
								}else{
									$js_html .= 'target="_blank"';
								}			
								$strin_short_url_html = '<a title="'.$full_url.'" href="'.$url.'" '.$js_html.'  >'.$url.'<span class="feedico_music" title="'.Comm_I18n::text('tpls.common.short_icon_music').'"></span></a>';
								if (count($media) == 0){
									$item['annotations'][0]['pic'] = '/t3/style/images/index/music_s.gif';
									$media = $item;
								}
								break;
							case self::SHORTURL_TYPE_EVENT:
								if (is_array ( $item ['annotations'] ) && is_array ( $item ['annotations'] [0] ) && isset($item ['annotations'] [0]['img_prev']) && $item ['annotations'] [0]['img_prev'] != '') {
									if(!($has_root xor $is_forward) && $is_action && !empty($full_url)){
										$source_data = $suda = '';
										$title = Comm_I18n::text("tpls.pl.include.content.feed.media.show_source");						
										$source_data = urlencode('key=feedevent&value=linkjump');
										$item['annotations'][0]['source_suda'] = $source_data;
										$suda = 'suda-data=key=feedevent&value=feedshow';
										$js_html .= 'target="_blank" '.$suda.' action-data="suda='.$source_data.'&amp;title='.$title.'&amp;short_url='.$item['url_short'].'&amp;full_url='.$full_url_encode.'" action-type="feed_list_media_widget"';
									}else{
										$js_html .= 'target="_blank"';
									}
									$strin_short_url_html = '<a title="'.$url.'" href="'.$url.'" '.$js_html.' >'.$url.'<span class="feedico_active"></span></a>';
									if (count($media) == 0){
										$media = $item;
									}
								} else {
									$strin_short_url_html = '<a title="'.$full_url.'" target="_blank" href="'.$url.$url_click.'" title="'.$item['url_long'].'" target="_blank" mt="event" >'.$url.'<span class="feedico_active"></span></a>';
								}
								break;
							case self::SHORTURL_TYPE_MAGIC:
								if (!($has_root xor $is_forward) && $is_action && !empty($full_url)) {
									$js_html .= 'title="'.$full_url.'" target="_blank" href="'.$item['url_short'].'" action-data="swf='.$item['url_long'].'" action-type="feed_list_media_magic"';
								} else {
									$js_html .= 'title="'.$url.'" target="_blank" href="'.$item['url_short'].'" ';
								}
								$strin_short_url_html = '<a '.$js_html.' >'.$url.'<span class="feedico_magic" title="'.Comm_I18n::text('tpls.common.short_icon_magic').'"></span></a>';
								$face_arr[] = $item['url_short'];
								if (count($media) == 0){
									$media = $item;
								}
								break;
							case self::SHORTURL_TYPE_VOTE:
								if(!($has_root xor $is_forward)&& $is_action && !empty($full_url)){
									$js_html .= 'target="_blank" action-data="title='.$title.'&amp;short_url='.$item['url_short'].'&amp;full_url='.$full_url_encode.'&amp;type='.$item['type'].'" action-type="feed_list_media_vote"';
								}else{
									$js_html .= 'target="_blank"';
								}
								$strin_short_url_html = '<a title="'.$full_url.'" href="'.$url.'" '.$js_html.' >'.$url.'<span class="feedico_vote" title="'.Comm_I18n::text('tpls.common.short_icon_vote').'"></span></a>';
								if (count($media) == 0){
									$media = $item;
									if (isset($media['annotations'][0]['thumbid']) && $media['annotations'][0]['thumbid']){
										$pic_url = Tool_Picid2url::get_pic_url($media['annotations'][0]['thumbid'], 'large');
										$media['annotations'][0]['pic'] = $pic_url[$media['annotations'][0]['thumbid']];
									}elseif (isset($media['annotations'][0]['pid']) && $media['annotations'][0]['pid']){
										$pic_url = Tool_Picid2url::get_pic_url($media['annotations'][0]['pid']);
										$media['annotations'][0]['pic'] = $pic_url[$media['annotations'][0]['pid']];
									}else{
										$media['annotations'][0]['pic'] = "";
									}
								}
								break;
								/*case self::SHORTURL_TYPE_MOOD:
									$mood_image_url_prefix = '/t4/style/images/mood/face/';	// 心情图片的前置路径
									$title = Comm_I18n::text ('ajax.mood.diary');
									if (!($has_root xor $is_forward) && $is_action && !empty($full_url)) {
										$js_html .= 'target="_blank" action-data="title='.$title.'&amp;short_url='.$item['url_short'].'&amp;full_url='.$full_url_encode.'&amp;type='.$item['type'].'" action-type="feed_list_media_widget"';
									} else {
										$js_html .= 'target="_blank"';
									}

									$strin_short_url_html = '<a title="'.$full_url.'" href="'.$url.'" '.$js_html.' >'.$url.'<span class="feedico_mood" title="'.Comm_I18n::text('tpls.common.short_icon_mood').'"></span></a>';
									if ($has_mood === false){
										unset($media);
										$media = $item;
										if (isset($media['annotations'][0]['data']['img_prev'])) {// 拼接图片完整路径
											$media['annotations'][0]['data']['img_prev'] = $mood_image_url_prefix.$media['annotations'][0]['data']['img_prev'];
										}
										if (isset($media['annotations'][0]['data'])) {
											// 添加心情默认文案
											$astro_info = explode('_', $media['annotations'][0]['data']['content_id']);
											$allmood = Comm_Util::conf('mood_config');                              
											$media['mood_content'] = (isset($astro_info[0]) && isset($astro_info[1]) && !empty($allmood[$astro_info[0]]['content'][$astro_info[1]]))? $allmood[$astro_info[0]]['content'][$astro_info[1]] : $allmood[$astro_info[0]]['content'][0];    // 取对应心情的文案   
											$media['mood_feed_class'] = (isset($astro_info[0]) && !empty($allmood[$astro_info[0]]['feed_class']))? $allmood[$astro_info[0]]['feed_class'] : ''; // 取对应心情的文案
										}
										$has_mood = true;
									}
									$media['thirdapiflag'] = false;

								break;*/
							case self::SHORTURL_TYPE_NEWS:
								$strin_short_url_html = '<a title="'.$full_url.'" href="'.$url.$url_click.'" target="_blank" mt="news" action-type="feed_list_media_news" >'.$url.'<span class="feedico_news" title="'.Comm_I18n::text('tpls.common.short_icon_news').'"></span></a>';
								if (count($media) == 0){
									$media = $item;
								}
								break;
							case self::SHORTURL_TYPE_BP:
								# 即便type = 25，如果status不为1，也即非上线特型feed，也不显示“我要推广”图标
								foreach($url_short as $shorturl){
									if(strpos($item['url_short'],$shorturl)!==false){
										$slstr = $shorturl;
									}
								}
								$ret = Dr_Thirdapi::get_bpfeed_info($slstr);
								if($ret !== false){
									$strin_short_url_html = '<a title="'.$full_url.'" href="'.$url.$url_click.'" target="_blank" mt="bp" action-type="feed_list_media_bpfeed" >'.$url.'<span class="feedico_business" title="'.Comm_I18n::text('tpls.common.short_icon_bpfeed').'"></span></a>';
									$item['annotations'][0]['bp_feed_html'] = urldecode($ret);
								}else {
									$strin_short_url_html = '<a title="'.$full_url.'" href="'.$url.$url_click.'" target="_blank" mt="url" action-type="feed_list_url">'.$url.'</a>';
								}
								$media = $item;
								break;
							case self::SHORTURL_TYPE_KANDIAN:
								$video_arr[] = $item['url_short'];
								if(!($has_root xor $is_forward) && $is_action && !empty($full_url)){
									$js_html .= 'target="_blank" action-data="title='.$title.'&amp;short_url='.$item['url_short'].'&amp;full_url='.$full_url_encode.'" action-type="feed_list_media_video"';
								}else{
									$js_html .= 'target="_blank"';
								}
								$strin_short_url_html = '<a title="'.$url.'" href="'.$url.'" '.$js_html.' >'.$url.'<span class="feedico_expand_focus" title="'.Comm_I18n::text('tpls.common.short_icon_kandian').'"></span></a>';
								if (count($media) == 0){
									$media = $item;
								}
								break;
								/* 	商品feed停止解析
								case self::SHORTURL_TYPE_GOODS:
									if(!($has_root xor $is_forward)&& $is_action && !empty($full_url)){
										$source_data = $suda = '';
										if (is_array ( $item ['annotations'] ) && is_array ( $item ['annotations'] [0] )) {
											if (isset ( $item ['annotations'] [0] ['pid'] )) {
												$source_suda = urlencode('key=tblog_sales_feed_v4&value=source:'.$item ['annotations'][0]['pid'].':'.$item ['annotations'][0]['category']);
												$item['annotations'][0]['source_suda'] = $source_data;
												$suda = 'suda-data="key=tblog_sales_feed_v4&value=link:'.$item ['annotations'][0]['pid'].':'.$item ['annotations'][0]['category'].'"';
												$item['annotations'][0]['pic_small'] = "http://img.weitao.a.weibo.com/".$item['annotations'][0]['pid']."thumb.gif";
												$item['annotations'][0]['from'] = '去'.$item['annotations'][0]['from']."购买";
											}
										}
										$js_html .= 'target="_blank" '.$suda.' action-type="feed_list_media_widget" action-data="suda='.$source_data.'&amp;title='.$title.'&amp;short_url='.$url.'&amp;full_url='.$full_url_encode.'" title="'.$full_url.'" mt="url"';
									}else{
										$js_html .= 'target="_blank"';
									}
									$strin_short_url_html = '<a title="'.$full_url.'" href="'.$url.'" '.$js_html.' >'.$url.'<span class="feedico_expand_shop"></span></a>';

									if (count($media) == 0){
										$media = $item;
									}
									break;
								 */
							case self::SHORTURL_TYPE_WEIPAN:
								if(!($has_root xor $is_forward) && $is_action && !empty($full_url)){
									$js_html .= ' target="_blank" action-data="title='.$title.'&amp;short_url='.$item['url_short'].'&amp;full_url='.$full_url_encode.'&amp;metadata" action-type="feed_list_media_video"';
								}else{
									$js_html .= 'target="_blank"';
								}
								$strin_short_url_html = '<a title="'.$url.'" href="'.$url.'" '.$js_html.' >'.$url.'<span class="icon_sw_wepan" title="微盘"></span></a>';
								if (count($media) == 0){
									$item['annotations'][0]['pic'] = '';
									if($item['annotations'][0]['image_prev']) {
										$item['annotations'][0]['pic'] = $item['annotations'][0]['image_prev'];
									}
									$media = $item;
								}
								break;
							case self::SHORTURL_TYPE_QING:  
								if(!($has_root xor $is_forward) && $is_action && !empty($full_url)){
									$title = Comm_I18n::text("tpls.pl.include.content.feed.media.show_source");
									$js_html .= 'target="_blank" action-data="title='.$title.'&amp;short_url='.$item['url_short'].'&amp;full_url='.$full_url_encode.'&template_name='.$item ['annotations'] [0]['ctype'].'" action-type="feed_list_media_qing"';
								}else{
									$js_html .= 'target="_blank"';
								}
								$strin_short_url_html = '<a title="'.$url.'" href="'.$url.'" '.$js_html.' >'.$url.'<span class="feedico_qing"></span></a>';
								if (count($media) == 0){
									$media = $item;
								}
								break;
							case self::SHORTURL_TYPE_GOVERNMENT_WTALK://政府微访谈短链类型
								if($item['annotations'][0]['act'] == 2) {
									if(!($has_root xor $is_forward) && $is_action && !empty($full_url)) {
										$js_html .= 'target="_blank" action-data="title='.$title.'&amp;short_url='.$item['url_short'].'&amp;full_url='.$full_url_encode.'&amp;metadata"';
									} else {
										$js_html .= 'target="_blank"';
									}
									$strin_short_url_html = '<a title="'.$title.'" href="'.$url.'" '.$js_html.' >'.$url.'<span class="feedico_interview"  title="'.Comm_I18n::text('tpls.common.short_icon_weifangtan').'"></span></a>';
									$media = $item;
								} else {
									if(!($has_root xor $is_forward) && $is_action && !empty($full_url)){
										$js_html .= 'target="_blank" action-data="title='.$title.'&amp;short_url='.$item['url_short'].'&amp;full_url='.$full_url_encode.'&amp;metadata" action-type="feed_list_third_rend"';
									}else{
										$js_html .= 'target="_blank"';
									}
									$strin_short_url_html = '<a title="'.$title.'" href="'.$url.'" '.$js_html.' >'.$url.'<span class="feedico_interview" title="'.Comm_I18n::text('tpls.common.short_icon_weifangtan').'"></span></a>';
									if (count($media) == 0){
										$item['annotations'][0]['pic'] = '';
										if($item['annotations'][0]['img']) {
											$item['annotations'][0]['pic'] = $item['annotations'][0]['img'];
										}
										$media = $item;
									}
								}
								break;
							default:
								$strin_short_url_html = '<a title="'.$full_url.'" href="'.$url.$url_click.'" target="_blank" mt="url" action-type="feed_list_url">'.$url.'</a>';
								break;
							}
						}
					}else{
						$item = "http://".self::SHORT_URL_DOMAIN."/".$v;
						$url = $item;
						$strin_short_url_html = '<a title="'.$item.'" href="'.$item.$url_click.'" target="_blank" mt="url" >'.$item.'</a>';
					}
					$url = $preg_url[md5($v)];
					$url_replace[md5($url)] = $strin_short_url_html;
				}
			}else{
				foreach ($out[0] as $item){
					$url_replace[md5($item)] = '<a title="'.$item.'" href="'.$item.'" title="'.$item.'" target="_blank" mt="url" >'.$item.'</a>';
				}
			}
			//解决短链解析顺序不正确的问题
			foreach($source_url as $url) {
				$content = str_replace($url,$url_replace[$url], $content);
			}
			//转换
			// 	$content = str_replace($source_url, $url_replace, $content);  	
		}
		return array('content' => $content, 'media' => $media, 'has_link' => $has_link, 'short_url' => $url_short, 'video_from' => $video_from, 'video_arr' => $video_arr, 'music_arr' => $music_arr, 'face_arr' => $face_arr);		
	}

	/**
	 * 配合preg_replace_callback做正则替换
	 *
	 * @param array $matches		正则匹配到的url
	 */
	private function searchs($matches){
		$urlMd5 = md5($matches[0]);
		return $urlMd5;
	}

	public static function parse_source_content_link_for_ssm($content){
		//短链解析
		$grep = "!http\:\/\/[a-zA-Z0-9\$\-\_\.\+\!\*\'\,\{\}\\\^\~\]\`\<\%\>\/\?\:\@\&\=(\&amp\;)\#\|]+!is";	
		preg_match_all($grep, $content, $out);
		$url_short = $url_long = $video_from = $video_arr = $face_arr = $music_arr = array();
		$has_link = false;
		if(count($out[0])){
			$has_link = true;
			$content =  preg_replace_callback($grep,array("Tool_Analyze_Link", "searchs"), $content);
			foreach($out[0] as $link_item){	
				$url_array = parse_url($link_item);
				// 判断域名就是短URL,否则要转成短URL
				if(in_array(strtolower(trim($url_array['host'])),array(self::SHORT_URL_DOMAIN, self::SHORT_URL_DOMAIN2))) {
					$strin_short_url = trim($url_array['path'], "/");
					if(empty($strin_short_url)) {
						$url_long[] = $link_item; //只发http://t.cn 作为长链接处理。 
						continue;
					}
					$url_short[] = $strin_short_url;
				}else{
					$url_long[] = $link_item;
				}
			}
			if(count($url_short)){
				try{
					$url_short_info = Tool_Shorturl::batch_info($url_short);
				}catch (Exception $e){
					$url_short_info = array();
				}
			}
			if(count($url_short) && count($url_short_info)){
				foreach($url_short as $v){
					$item = $url_short_info[$v];
					if($item){
						switch($item['type']) {
						case self::SHORTURL_TYPE_VIDEO:
							$video_arr[] = $v;
							break;
						case self::SHORTURL_TYPE_WEIYUYIN:
						case self::SHORTURL_TYPE_MP3:
							$music_arr[] = $v;
							break;
						case self::SHORTURL_TYPE_EVENT:
							break;
						case self::SHORTURL_TYPE_MAGIC:
							$face_arr[] = $v;
							break;
						case self::SHORTURL_TYPE_VOTE:
							break;
						default:
							break;
						}
					}else{
					}
				}
			}else{
				foreach ($out[0] as $item){
				}
			}
			if (count($url_long)){

			}
		}
		return array('content' => $content, 'has_link' => $has_link, 'url_short' => $url_short, 'url_long' => $url_long, 'video_from' => $video_from, 'video_arr' => $video_arr, 'music_arr' => $music_arr, 'face_arr' => $face_arr);		

	}
}
