<?php


/**
 * 赔率相关的计算工具
 *
 */


class Lib_Odds_Tools {
    /**
     * 根据赔率计算返奖率
     * 
     * 一般情况下返奖率不能大于1, 平台会亏损
     * 
     * 返奖率 = 各赔率倒数之和
     * 
     */
    public static function backRate($odds_list) {
        $backRate = 0;
        
        foreach($odds_list as $odds) {
            if($odds <= 0 ) continue ;
            $backRate += 1/$odds;
        }
        return 1/$backRate;
    }
}