<?php

/**
 * Tradition 赔率调整算法
 * 
 * 算法概要: 根据各个选项历史投注额, 来确定最新赔率.
 * 
 * 算法参数: 赔率最大值,赔率最小值, 赔率最大变更幅度
 * 
 * 算法输入: 当前各个选项赔率, 当前各个选项总投注额
 * 
 * 算法输出: 调整后赔率列表
 * 
 * @author wangguan@staff.weibo.com 
 *
 */


class Lib_Odds_Algorithm_Tradition {
    
    
    private $cfg_odds_max;
    private $cfg_odds_min;
    
    private $cfg_odds_change_rate;
    
    const RETURN_RATE_MAX = 0.70; //最大返奖率
    
    /**
     * 
     * @param float $odds_max  赔率最大值, 若计算结果大于该值, 则取该值.
     * @param float $odds_min  赔率最小值, 若计算结果小于该值, 则取该值.
     * @param float $odds_change_rate  赔率最大改变幅度(百分制), 若计算结果超出幅度范围, 则取范围内最大值(或最小值)
     * @param float $odds_change_min  赔率最小改变值, 若计算结果改变值小于该值, 则取原始值
     * @param int $back_rate 返奖率,百分制
     */
    public function __construct($odds_max, $odds_min, $odds_change_rate) {
        $this->cfg_odds_max = $odds_max;
        $this->cfg_odds_min = $odds_min;
        $this->cfg_odds_change_rate = $odds_change_rate;
    }
    
    /**
     * 计算并返回最新赔率
     * 
     * @param Array $odds_current  当前各选项赔率, 格式: array("选项ID"=>赔率, "选项ID"=>赔率)
     * @param Array $bet_current 当前各选项总投注额, 格式: array("选项ID"=>总投注额, "选项ID"=>总投注额)
     * 
     * @return Array  计算后的最新赔率, 格式: array("选项ID"=>最新赔率, "选项ID"=>最新赔率)
     */
    public function calculate($odds_current, $bet_current){
        
        //当前总投注额
        $total_bet = array_sum($bet_current);
        
        //当前返奖率
        $back_rate = $this->backRate($odds_current);
        
        //计算赔率
        $odds_new = array();
        foreach ($odds_current as $option_id => $v){
            $option_bet = empty($bet_current[$option_id])? 1: $bet_current[$option_id];
            $odds_new[$option_id] = $back_rate * $total_bet / $option_bet;
        }
        
        
        $max_option = ""; //赔率最大选项
        $max_odds = 0; //赔率最大值
        
        //设置赔率涨跌幅
        foreach ($odds_new as $option_id => $_odds_new){
            $_odds_current = $odds_current[$option_id];
            
            if($_odds_new - $_odds_current > 0 ) {
                $odds_new[$option_id] = min(array($_odds_new, $_odds_current * (1 + $this->cfg_odds_change_rate/100), $this->cfg_odds_max));
            } else {
                $odds_new[$option_id] = max(array($_odds_new, $_odds_current * (1 - $this->cfg_odds_change_rate/100), $this->cfg_odds_min));
            }
            
            //选出最大的赔率项, 用于后面限制返奖率<1
            if($odds_new[$option_id] > $max_odds) {
                $max_option = $option_id;
                $max_odds = $odds_new[$option_id];
            }
        }
        
        //若返奖率>最大返奖率, 则降低赔率最大的选项, 使返奖率=最大返奖率
        if ($this->backRate($odds_new) > self::RETURN_RATE_MAX) {
            $tmp = 0;
            foreach ($odds_new as $option_id => $_odds_new){
                if($option_id == $max_option ) continue ;
                $tmp += 1/$_odds_new;
            }
            $odds_new[$max_option] = 1 / ((1/self::RETURN_RATE_MAX) - $tmp);
        }
        
        
        //格式化成2位小数
        foreach ($odds_new as $option_id => $_odds_new){
            $odds_new[$option_id] = round($_odds_new, 2) ;
        }
        
        return $odds_new;
    }
    
    public function backRate($odds_list) {
        $backRate = 0;
        
        foreach($odds_list as $odds) {
            if($odds <= 0 ) continue ;
            $backRate += 1/$odds;
        }
        return 1/$backRate;
    }
    
}