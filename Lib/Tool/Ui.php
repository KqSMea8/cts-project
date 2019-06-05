<?php
class Tool_Ui {
	
    const PHOTO_URL_CRC = "http://ww%d.sinaimg.cn/%s/%s.%s";
    const PHOTO_URL_CRC_BACKUP = "http://wb%d.sina.cn/%s/%s.%s";
    const PHOTO_URL = "http://ss%d.sinaimg.cn/%s/%s&690";
	const PHOTO_URL_DEFAUT = '/t4/appstyle/e/apps/promotion/images/promotion_default.png';
    const EVENT_URL_FORMAT = 'http://e.weibo.com/%s/event/%s';
    
    /**
     * pid2url 
     * 通过图片id获取图片url
     * @param mixed $picid 
     * @param string $pictype 
     * @static
     * @return void
     */
    public static function pid2url($picid,$pictype="thumbnail"){
	
	    if(!is_array($picid)){
	    	$picid = array($picid);
	    }
		$result = array();
		if (false && Comm_Config::get('control.use_photo_url_cdc_backup')){
		    $photo_url_cdc = self::PHOTO_URL_CRC_BACKUP;
		}else{
		    $photo_url_cdc = self::PHOTO_URL_CRC;
		}
		foreach($picid as $pid) {
		    if(empty($pid)) {
				throw new Comm_Exception_Program('pid_error');
		    }
		    //非英文和数字
		    if (preg_match('/[^a-zA-Z0-9]/i', $pid)){
				$result[$pid] = "";
				continue;
		    }
		    if (isset($pid[9]) && $pid[9] == 'w') { 
			// 新系统显示规则，注意新系统用的crc32做的域名哈希  orignal
				if($pictype == "orignal"){
				    $pictype = "large";
				}
				$hv = sprintf("%u", crc32($pid));
				$zone = fmod(floatval($hv) ,4) + 1;
				$ext = ($pid[21] == 'g' ? 'gif' : 'jpg');
				$result[$pid] = sprintf($photo_url_cdc, $zone, $pictype, $pid, $ext);
		    } else {
				$num = (hexdec(substr($pid, -2)) % 16) + 1;
				$result[$pid] = sprintf(self::PHOTO_URL, $num, $pictype, $pid);
		    } 
		}
		return $result;	
    }
    
    /**
     * 对一条pic进行转换，返回一条url
     * @param string $picid
     * @param string $pictype
     * @throws Comm_Exception_Program
     * @return Ambigous <string>
     */
    public static function single_pid2url($picid, $pictype="thumbnail"){
    	if( !is_string($picid) ){
    		throw new Comm_Exception_Program('pid must be a string!');
    	}
    	$result = self::pid2url($picid, $pictype);
    	return $result[$picid];
    }

    /**
     * 根据图片地址获取图片ID
     * @param string $pic_url
     */
    public static function url2pid($pic_url) {
		$patten = '/^http\:\/\/[a-zA-Z0-9]?[a-zA-Z0-9\-\.]*\/[a-zA-Z0-9]+\/([a-zA-Z0-9]+).[a-zA-Z]+$/i';
		if(preg_match($patten, $pic_url, $matches)) {
		    $pid = $matches[1];
		    return $pid;
		} else {
		    return false;
		}
    }

    /*
       $arrInput = array(
       'uid' => 企业id
       'type' => 活动类型（站外/站内）
       'actid' => 活动 id
       )
     */
    public static function get_ekey($arrInput){
		return $arrInput['uid'] . '-' . $arrInput['type'] . '-' . $arrInput['actid'];
    }
    
    /**
     * 截取字符串
     * @param string $str 要截取的字符串
     * @param int $lenth 保留的长度
     * @param string $append 超过后追加的字符
     */
    public static function stringLimit($str, $lenth = 18 , $append = '...'){
    	$strEncoding = mb_detect_encoding($str, 'auto', true );
		if ($lenth >= mb_strwidth($str, $strEncoding)) {
			return $str;
		}
		return mb_strimwidth ( $str, 0, $lenth * 2, $append, $strEncoding );
    }

    /**
     * 转义防止xss
     */
    public static function escape($str){
    	return htmlentities(stripcslashes($str),ENT_QUOTES,'utf-8');
    }
    
    /**
     * 将文本内容中的 a 标签的 target 属性修改为 parent
     * @param $text
     * @return mixed
     */
    public static function alter_link_taget_to_parent($text) {
    	return str_replace("<a", "<a target='_parent'", $text);
    }
    
	/**
	 * 把一个数字根据区间长度分割成区间数组
	 * @param int $number 中的数目
	 * @param int $interval_len 区间大小
	 * ex: split_to_intrtvals(1200, 1000)
	 * rst: array[
	 * 			array[1,1000],
	 * 			array[1001,1200]
	 * 			 ];
	 * @return array
	 */
    public static function split_to_intrtvals($number, $interval_len){
    	$arr_intervals = array();
    	$s_index = 1;
		$interval_num = intval($number/$interval_len);
		$remainer = $number % $interval_len;
		for($i = 1; $i<= $interval_num; $i++){
			$arr_intervals[] = array($s_index, $s_index + $interval_len -1 );
			$s_index = $s_index + $interval_len;
		}
		$remainer > 0 && $arr_intervals[] = array($s_index, $s_index + $remainer -1 );
		return $arr_intervals;
    }
    
/**
 * 获得通用的下载对话框html
 * @param int $num_records 总共的条数
 * @param int $interval_len 下载区段的长度
 * @param string $d_url_prefix 下载链接前缀（除了page和count外的前面的那段链接）
 * @return string
 */
    public static function get_html_downloaddia($num_records, $interval_len, $d_url_prefix){
    	//没有记录可以下载
    	if($num_records <= 0 || $interval_len <= 0){
    		$html = <<<DOC
<div class="layer_point">
	<dl class="point clearfix">
	    <dt>
	     <span class="icon_warnM"></span>
	    </dt>
	    <dd>
	     <p class="W_texta">当前没有内容可以导出！</p>
	    </dd>
   </dl>
   <div class="btn">
    	<a href="javascript:void(0)" class="W_btn_d" action-type="ok"><span>确定</span></a>
   </div>
</div>
DOC;
    		return $html;
    	}
    	//有记录
    	$intervals = self::split_to_intrtvals($num_records, $interval_len);
    	if( strpos($d_url_prefix, '?') ){//strpos不会是0,要么url就一个'?',应该做一些处理？
    		$strlen = strlen($d_url_prefix);
    		$d_url_prefix{$strlen-1} != '&' && $d_url_prefix .= '&';
    	}else{
    		$d_url_prefix .= '?';
    	}
    	$html_li_str = '';
    	foreach($intervals as $key=>$interval){
    		$s_index = number_format($interval[0]);
    		$e_index = number_format($interval[1]);
    		$p = $key+1;
    		$d_url = $d_url_prefix . "page={$p}&count={$interval_len}";
    		$html_li_str .= "<li><p class='list_cont'>第{$s_index}~{$e_index}条内容。<a href='{$d_url}' class='right'><i class='icon_download2'></i>下载</a></p></li>";
    	}
    	//输出模板
    	$html =  <<<DOC
<div class="layer_option">
	<div class="text_tips">
    	<i class="icon_warn"></i>共{$num_records}条内容
    </div>
    <ul class="layer_select_awards">
		{$html_li_str}
    </ul>
    <div class="btn" style="text-align:center;">
    	<a href="javascript:void(0)" class="W_btn_d" action-type="ok"><span>确定</span></a>
    </div>
</div>
DOC;
    	return $html;
    }
    
    /**
     * 获得活动访问链接
     * @param string|int $uid 活动创建者uid
     * @param int $actid 活动ID
     */
    public static function event_url($uid, $actid){
    	return sprintf(self::EVENT_URL_FORMAT, $uid, $actid);
    }
    
    /**
     * 获取发奖填写信息地址
     * @param int $actid
     * @param int $uid
     * @param int $prize_id
     */
    public static function  award_address_url($actid, $uid, $prize_id){
    	return "http://e.weibo.com/$uid/event/$actid/address?prizeid=$prize_id&type=1";
    }
    /**
     * 获取活动简介
     * @param int $pack_type 套餐类型
     */
    public static function get_html_act_intro($pack_type){
    	$html = '';
    	switch($pack_type){
    		case Controller_Detail::PACKAGE_TRY://申领试用
    			$html = <<<DOC
<div class="text_info">
 <dl>
	 <dt><i class="list_style1"></i><span>活动简介</span></dt>
	 <dd>申请试用的活动能够帮助您将限量的免费试用品发放给满足条件的用户，同时收获用户的体验报告，外加优质的口碑传播。 </dd>
 </dl>
 <dl>
 	<dt><i class="list_style1"></i><span>用户参与流程</span></dt>
 	<dd>申请试用 &gt; 分享试用体验</dd>
 </dl>
</div>
<div class="slides_wrap" node-type="sliderWrapper">
   <div class="slides_list_wrap" node-type="slider">
    <ul class="clearfix" node-type="container" style="position: relative; width: 4840px; left: 0px;">
     <li><a href="#"><img src="/t4/appstyle/e_promotion/images/common/combo_img/combo_4_1.jpg" width="960" /></a></li>
     <li><a href="#"><img src="/t4/appstyle/e_promotion/images/common/combo_img/combo_4_2.jpg" width="960" /></a></li>
     <li><a href="#"><img src="/t4/appstyle/e_promotion/images/common/combo_img/combo_4_3.jpg" width="960" /></a></li>
   	</ul>
   </div>
   <ul class="slide_tab" node-type="turn_page">
    <li action-type="goPage" action-data="0" class="cur"></li>
    <li action-type="goPage" action-data="1" class=""></li>
    <li action-type="goPage" action-data="2" class=""></li>
   </ul>
</div>
DOC;
    			break;
    		case Controller_Detail::PACKAGE_COUPON:
    			//优惠券
    			$html = <<<DOC
<div class="text_info">
 <dl>
	 <dt><i class="list_style1"></i><span>活动简介</span></dt>
	 <dd>无论是现金券、体验券、礼品券、折扣券都可以通过这种方式发放，优惠券将通过微博通知以及短信方式发送到用户手机，用户只需要在付款时出示微博通知/短信即可获得相应的优惠。</dd>
 </dl>
 <dl>
 	<dt><i class="list_style1"></i><span>用户参与流程</span></dt>
 	<dd>领取优惠券 &gt; 分享消费体验</dd>
 </dl>
</div>
<div class="slides_wrap" node-type="sliderWrapper">
   <div class="slides_list_wrap" node-type="slider">
    <ul class="clearfix" node-type="container" style="position: relative; width: 4840px; left: 0px;">
     <li><a href="#"><img src="/t4/appstyle/e_promotion/images/common/combo_img/combo_2_1.jpg" width="960" /></a></li>
     <li><a href="#"><img src="/t4/appstyle/e_promotion/images/common/combo_img/combo_2_2.jpg" width="960" /></a></li>
     <li><a href="#"><img src="/t4/appstyle/e_promotion/images/common/combo_img/combo_2_3.jpg" width="960" /></a></li>
    </ul>
   </div>
   <ul class="slide_tab" node-type="turn_page">
    <li action-type="goPage" action-data="0" class="cur"></li>
    <li action-type="goPage" action-data="1" class=""></li>
    <li action-type="goPage" action-data="2" class=""></li>
   </ul>
</div>
DOC;
    			break;
    		case Controller_Detail::PACKAGE_VOTE:
    			//投票抽奖
    			$html = <<<DOC
<div class="text_info">
 <dl>
	 <dt><i class="list_style1"></i><span>活动简介</span></dt>
	 <dd>图片+文字投票，可支持2-4个选项。帮您轻松了解消费者的喜好偏爱，为了吸引用户参与，您需要设置一些奖品激励。</dd>
 </dl>
 <dl>
 	<dt><i class="list_style1"></i><span>用户参与流程</span></dt>
 	<dd>投票 &gt; 抽奖</dd>
 </dl>
</div>
<div class="slides_wrap" node-type="sliderWrapper">
   <div class="slides_list_wrap" node-type="slider">
    <ul class="clearfix" node-type="container" style="position: relative; width: 4840px; left: 0px;">
     <li><a href="#"><img src="/t4/appstyle/e_promotion/images/common/combo_img/combo_1_1.jpg" width="960" /></a></li>
     <li><a href="#"><img src="/t4/appstyle/e_promotion/images/common/combo_img/combo_1_2.jpg" width="960" /></a></li>
    </ul>
   </div>
   <ul class="slide_tab" node-type="turn_page">
    <li action-type="goPage" action-data="0" class="cur"></li>
    <li action-type="goPage" action-data="1" class=""></li>
   </ul>
</div>
DOC;
    			break;
    		case Controller_Detail::PACKAGE_TEXT:
    			//文字征集
    			$html = <<<DOC
<div class="text_info">
 <dl>
	 <dt><i class="list_style1"></i><span>活动简介</span></dt>
	 <dd>通过文字征集您可以轻松发起一次评选活动，比如：口号征集，三行情诗大赛、微小说PK......让聪慧的微博用户为您创造内容，制造话题，扩散传播吧！ </dd>
 </dl>
 <dl>
 	<dt><i class="list_style1"></i><span>用户参与流程</span></dt>
 	<dd>文字征集 &gt; 发奖</dd>
 </dl>
</div>
<div class="slides_wrap" node-type="sliderWrapper">
   <div class="slides_list_wrap" node-type="slider">
    <ul class="clearfix" node-type="container" style="position: relative; width: 4840px; left: 0px;">
     <li><a href="#"><img src="/t4/appstyle/e_promotion/images/common/combo_img/combo_3_1.jpg" width="960" /></a></li>
     <li><a href="#"><img src="/t4/appstyle/e_promotion/images/common/combo_img/combo_3_2.jpg" width="960" /></a></li>
    </ul>
   </div>
   <ul class="slide_tab" node-type="turn_page">
    <li action-type="goPage" action-data="0" class="cur"></li>
    <li action-type="goPage" action-data="1" class=""></li>
   </ul>
</div>
DOC;
    			break;
    		case Controller_Detail::PACKAGE_PICTURE_COLLECT:
    			$html = <<<DOC
<div class="text_info">
 <dl>
	 <dt><i class="list_style1"></i><span>活动简介</span></dt>
	 <dd>大型征集评选活动。通过奖品刺激参赛者投稿和投票者投票，以瀑布流形式展示参赛者作品，生动直观。可设置两重奖励鼓励用户参加：参与抽奖、评选优胜奖。</dd>
 </dl>
 <dl>
 	<dt><i class="list_style1"></i><span>用户参与流程</span></dt>
 	<dd>图文 &gt; 评选</dd>
 </dl>
</div>
<div class="slides_wrap" node-type="sliderWrapper">
   <div class="slides_list_wrap" node-type="slider">
    <ul class="clearfix" node-type="container" style="position: relative; width: 4840px; left: 0px;">
     <li><a href="#"><img src="/t4/appstyle/e_promotion/images/common/combo_img/combo_5_1.jpg" width="950" /></a></li>
     <li><a href="#"><img src="/t4/appstyle/e_promotion/images/common/combo_img/combo_5_2.jpg" width="950" /></a></li>
     <li><a href="#"><img src="/t4/appstyle/e_promotion/images/common/combo_img/combo_5_3.jpg" width="950" /></a></li>
     <li><a href="#"><img src="/t4/appstyle/e_promotion/images/common/combo_img/combo_5_4.jpg" width="950" /></a></li>
    </ul>
   </div>
   <ul class="slide_tab" node-type="turn_page">
    <li action-type="goPage" action-data="0" class="cur"></li>
    <li action-type="goPage" action-data="1" class=""></li>
    <li action-type="goPage" action-data="2" class=""></li>
    <li action-type="goPage" action-data="3" class=""></li>
   </ul>
</div>
DOC;
				break;
		case Controller_Detail::PACKAGE_HOTSELL:
			$html = <<<DOC
<div class="text_info">
 <dl>
	 <dt><i class="list_style1"></i><span>活动简介</span></dt>
	 <dd>在线售卖套餐是活动平台新推出的一种售卖活动形式，为企业提供模板化一站式的自主创建流程，便于企业更方便快捷的在微博内创建并发起在线售卖活动。目前，在线售卖套餐可支持标准模板和自定义模板。</dd>
 </dl>
 <dl>
 	<dt><i class="list_style1"></i><span>用户参与流程</span></dt>
 	<dd>商品页展示&gt; 购买流程&gt; 支付</dd>
 </dl>
</div>
<div class="slides_wrap" node-type="sliderWrapper">
   <div class="slides_list_wrap" node-type="slider">
    <ul class="clearfix" node-type="container" style="position: relative; width: 4840px; left: 0px;">
     <li><a href="#"><img src="/t4/appstyle/e_promotion/images/common/combo_img/combo_6_1.jpg" width="950" /></a></li>
    </ul>
   </div>
   <ul class="slide_tab" node-type="turn_page">
    <li action-type="goPage" action-data="0" class="cur"></li>
   </ul>
</div>
DOC;
				break;
    		default:
    			break;
    	}
    	return $html;
    }
    
    /**
     * 解析feed或是comment内容
     * @param string $content
     * @return string
     */
    public static function format_wb_content($content){
    	//解析url
    	$pattern = '!http\:\/\/[a-zA-Z0-9\$\-\_\.\+\!\*\'\,\{\}\\\^\~\]\`\<\%\>\/\?\:\@\&\=(\&amp\;)\#\|]+!is';
    	$content = preg_replace($pattern, '<a href="${0}" target="_blank">${0}</a>', $content);
    	//解析微话题 #show me the money#
    	$content = preg_replace('/#([^#]+)#/i', '<a href="http://s.weibo.com/weibo/${1}" target="_blank">${0}</a>', $content);
    	//解析@某某
    	$tools_analyze_at = new Tool_Analyze_At();
    	$at_names = $tools_analyze_at->get_at_username($content);
    	$content = $tools_analyze_at->replace_weihao_to_nick($content, $at_names);
    	if( count($at_names) ){
    		$tools_analyze_at->at_to_link($content, $at_names);
    	}
    	//解析表情 [江南style]
    	$content = Tool_Analyze_Icon::text_to_icon($content);
    	return $content;
    }
    
    /**
     * 对企业微博左导的icon item分类
     * @param array $items
     */
    public static function classify_leftnav_items($items){
    	$app = $event = array();
    	foreach($items as $item){
    		if($item['display'] == 0){
    			continue;
    		}
    		if($item['type'] == 'app'){
    			$app[] = $item;
    		}
    		if($item['type'] == 'event'){
    			$event[] = $item;
    		}
    	}
    	//超过6个取6个
    	count($event) > 6 && $event = array_slice($event, 0, 5);
    	return array('apps' => $app, 'events' => $event);
    }
    
    /**
     * 渲染一个Pl
     * @param Comm_Bigpipe_Pagelet $pl
     * @param boolean $return_string
     * @return mixed
     */
    public static function render_single_pagelet(Comm_Bigpipe_Pagelet $pl, $return_string = true)
    {
    	$meta = $pl->get_meta_data();
    	$data = $pl->prepare_data();
    
    	$tpl = new WBSmarty();
    	$tpl->assign($meta);
    	$tpl->assign($data);
    	$html = $tpl->fetch($pl->get_template());
    
    	if($return_string){
    		return $html;
    	}else{
    		echo $html;
    	}
    }
    
    /**
     * 获得中奖后信填写地址
     * @param int $owner_uid 活动创建者UID
     * @param int $actid 活动ID
     * @param int $prize_id 奖品ID
     * @param int $type 抽奖类型[0|1] （ 0：即时，1：延时）
     * @return string
     */
    public static function get_roll_url($owner_uid, $actid, $prize_id, $type = 0){
    	
    	try{
    		$type = Arg::check($type, 'int', 'enum,0,1', Comm_Argchecker::NEED, Comm_Argchecker::RIGHT);
    	}catch(Exception_Arg $ae){
    		$type = 0;
    	}
    	
    	return "http://e.weibo.com/{$owner_uid}/event/{$actid}/address?prizeid={$prize_id}&type={$type}";
    }
}