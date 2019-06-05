<?php
/**
 * Model_Redenvelope_Info
 * 红包接口
 * @author    xiamengyu<mengyu5@staff.sina.com.cn>
 * @created   2016-4-26
 * @copyright copyright(2013) weibo.com all rights reserved
 */
class Api_Bonus extends Api_Abstract {

    protected $connectTimeout = 3000;
    protected $timeout = 3000;

    protected $domain = 'http://i.hb.e.weibo.com';

    //发红包
    public function send($outOrderId, $tplID, $amount, $uid){
        try {
            //$this->setDebug(true);
            $params = array(
                'firm_id' => Model_JF::MID_DXJ,
                'out_order_id' => $outOrderId,
                'tpl_id' => $tplID,
                'amount' => $amount,
                'uid' => $uid,
                'sign_type' => 'md5',
            );
            $params['sign'] = $sign = Tool_Sign::generate_sign($params, Model_JF::BONUS_KEY);
            return $this->post('/v2/bonus/c/create', $params, self::RETURN_TYPE_JSON);
        }catch(Exception $e) {
            Lib_Log::warning('Send Bonus failed:' . $e->getMessage());
        }

    }

    //创建模版
    public function addtpl() {
        try {
            $params = array(
                'firm_id' => Model_JF::MID_DXJ,
                'title' => "积分兑红包",
                'sign_type' => 'md5',
            );
            $params['sign'] = $sign = Tool_Sign::generate_sign($params, Model_JF::BONUS_KEY);
            return $this->post('/v2/bonus/c/set/add', $params, self::RETURN_TYPE_JSON);

        }catch(Exception $e) {
            Lib_Log::warning('Add Bonus Tpl failed:' . $e->getMessage());
        }
    }

    //更新模版
    public function updateTpl() {
        try {
            $params = array(
                'firm_id' => Model_JF::MID_DXJ,
                'title' => "积分兑红包",
                'sign_type' => 'md5',
                'show_id' => Model_Const::FIRMUid,
                'tpl_id' => Model_JF::BONUS_TPL_ID,
            );
            $params['sign'] = $sign = Tool_Sign::generate_sign($params, Model_JF::BONUS_KEY);
            return $this->post('/v2/bonus/c/set/update', $params, self::RETURN_TYPE_JSON);

        }catch(Exception $e) {
            Lib_Log::warning('Add Bonus Tpl failed:' . $e->getMessage());
        }
    }


}
