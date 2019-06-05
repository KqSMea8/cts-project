<?php
/**
 * 抽奖函数
 *
 * @author      	chengmeng@staff.weibo.com
 * @version 	    2014-09-19
 * @copyright  	copyright(2013) weibo.com all rights reserved
 *
 */
class Tool_Lottery 
{
	public static function national_subsidy($trade_id, $uid = '', $is_older = 1, $type = 1) 
	{
		$_bonus = array() ;
		switch ($type) {
			case 1 :
				$level = self::lottery_level($is_older, $uid) ; //获取抽奖级别
				$_bonus = Comm_Config::get('lottery.national_subsidy') ;
				break ;
			case 2 :
				$name = 'lottery.cold_prevention' ;
				if ($is_older == 1) {
					$name .= '.older';
					$offset = 71 ;
				} else {
					$name .= '.newer' ;
					$offset = 67 ;
				}
				
				$level = self::level($name . '.ranges', $offset, 100) ;
				$_bonus = Comm_Config::get($name) ;
		}
		
		$boundary = mt_rand($_bonus['bonus'][$level][0], $_bonus['bonus'][$level][1]) ;
		$bonus = $boundary/100.00 ;
		
		Tool_Log::warning('uid = ' . $uid . ', $trade_id = ' . $trade_id . ', is_older=' . $is_older . ', level= ' . $level . ', bonus = ' . $bonus) ; 
		self::apply_remit($uid, $trade_id, $bonus, $_bonus['seller_id'], $_bonus['name']) ; //自动打款
		return $bonus ;
	}
	
	private static function lottery_level($is_older, $uid) 
	{
		$mid = rand(1, 10000) ;
		switch ($is_older) 
		{
			case 1 :
				if ($mid <= 1000) {
					$level = 6 ;
				} else {
					$level = 7 ;
				}
				$level_mod = 7 ;
				break ;
			case 0 :
				if ($mid < 9300) {
					$level = 5 ;
				} else {
					if ($mid < 9972) {
						if ($mid < 9873) {
							$level = 4 ;
						} else {
							$level = 3 ;
						}
					} else {
						if ($mid < 9997) {
							$level = 2;
						} else {
							$level = 1 ;
						}
					}
				}
				$level_mod = 5 ;
				break ;
			default:
				$level = 7 ;
				$level_mod = 7 ;
		}
		$id = $level + 60 ;
		
		$modelbonus = new Model_Lottery_Bonusinfo() ;
		
		$bonusInfo = $modelbonus->get_bonus_info($id);
		$lotteried = intval($bonusInfo['lottery']);
		$total = intval($bonusInfo['total']);
		
		if ($lotteried >= $total) {
			$id = $level_mod + 60 ;
			$level = $level_mod ;
		}
		
		if ($level == 1) {
			$num = 16 ;  //共16个1001大奖
			$day = date(j);
			$month = date(n);
			switch ($month)
			{
				case 9 :
					$num = $day - 22 ;
					break ;
				case 10 :
					$num = $day + 8 ;
					break ;
			}
			if ($lotteried >= $num) {
				$id = $level_mod + 60 ;
				$level = $level_mod ;
			}
		}
		$res = $modelbonus->update_bonus($id) ;
		if (!$res){
			Tool_Log::warning('the user : ' . $uid . ' update bonus info failed!');
			$level = $level_mod ;
		}
		return $level ;
	}
	
	public static function apply_remit($uid, $trade_id, $bonus, $seller_id, $reason) {
		$model_remit = new Model_Remit_Info() ;
		
		$auto_remit = array(
				'trade_id' =>$trade_id,
				'uid' => $uid,
				'amount' => $bonus,
				'seller_id' => $seller_id, //配置文件取
				'reason' => $reason,      //配置文件取
		) ;
		Tool_Log::warning(var_export($auto_remit, true)) ;
		$remit_rst = Dw_Remit::apply_remit($auto_remit) ;
		if ($remit_rst['error_code'] != '100000') {
			Tool_Log::warning('Treasure apply remit failed; uid = ' . $uid . ', trade_id = ' . $trade_id . ' : ' . $remit_rst['error']) ;
			return false ;
		}
			
		$auto_remit['apply_id'] = $remit_rst['data']['apply_id'] ;
		$auto_remit['create_time'] = $remit_rst['data']['create_time'] ;
		$rst = $model_remit->create_remit($auto_remit) ;
		if (!$rst) {
			Tool_Log::warning('Treasure create remit record failed; uid = ' . $uid . ', trade_id = ' . $trade_id) ;
			return false ;
		}
		return true ;
	}
	
	public static function apply_refund($seller_id, $trade_id, $money, $reason) {
		$refund_param = array(
				'source'=> Comm_Config::get('env.platform_api_source'),
				'uid'=> $seller_id,
				'sign_type'=> 'md5',
				'refund_url'=> Comm_Config::get('domain.ordersc') . '/interface/internal/refundnotify',
				'batch_no'=> $trade_id,
				'detail_data'=> "{$trade_id}^{$money}^{$reason}",
				'batch_num'=> 1
		) ;
			
		$apply_refund = Dw_Refund_Refund::apply_refund($refund_param) ;
		if (!$apply_refund) {
			Tool_Log::warning('Tool_lottery apply refund failed trade_id = ' . $trade_id . ' refund reason : ' . $reason);
			return false ;
		}
		return true ;
	}
	
	public static function level($activity, $offset, $max, $limit = '') {
		$ranges = Comm_Config::get($activity) ;
		$lucky_num = mt_rand(1, $max) ;
		$level = 0 ;
		$total_level = count($ranges) - 1 ;
		
		foreach ($ranges as $key => $prob) {
			if ($lucky_num > $prob && $lucky_num <= $ranges[$key+1]) {
				$level = $key + 1 ;
				break ;
			}
		}
		
		$id = $level + $offset ;
		$modelbonus = new Model_Lottery_Bonusinfo() ;
		
		if ($level != $total_level) {
			$bonusInfo = $modelbonus->get_bonus_info($id);
			$lotteried = intval($bonusInfo['lottery']);
			$total = intval($bonusInfo['total']);
			
			if ($limit && $level == 1) {
				$surplus_first = $modelbonus->get_limited_first_bonus($id, date('Y-m-d'));
				
				if (!$surplus_first || $limit > 1) {
					$level = $total_level ;
					$id = $level + $offset ;
				}
			}
			
			if ($total <= $lotteried) {
				$level = $total_level ;
				$id = $level + $offset ;
			}
		}
		
		$res = $modelbonus->update_bonus($id) ;
		if (!$res){
			$level = $total_level ;
		}
		return $level ;
	}
	
	public static function build_level($activity, $max) {
		$ranges = Comm_Config::get($activity) ;
		$lucky_num = mt_rand(1, $max) ;
		$level = 0 ;
		$total_level = count($ranges) - 1 ;
		
		foreach ($ranges as $key => $prob) {
			if ($lucky_num > $prob && $lucky_num <= $ranges[$key+1]) {
				$level = $key + 1 ;
				break ;
			}
		}
		
		return $level ;
	}
}