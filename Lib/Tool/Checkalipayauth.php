<?php
/**
 * 是否开通商家售卖判断
 *
 * @author  	chengmeng@staff.sina.com.cn
 * @version 	2014-02-14
 * @copyright  	copyright(2014) weibo.com all rights reserved
 *
 */
class Tool_Checkalipayauth
{
	public static function check_auth($uid, $frozen = true)
	{
		$modelFrim = new Model_Firm_Info() ;
		$firmInfo = $modelFrim->get_firm_info($uid) ;
        Comm_Context::set('firmStatus', $firmInfo->status);
		
        if ($firmInfo) 
        {
            $contract_model = new Model_Contract_Info();
            $contract_num = $contract_model->get_contract_counts_by_firmid($firmInfo->firm_id);
            
            if ($contract_num) {
                switch ($firmInfo->status)
                {
                    case Do_Firm_Info::STATUS_CHECKED :
		                $merchant = Dr_Firm_Merchantinfo::check_merchant(array('merchant_id' => $uid));
		               
			        	if ($merchant['existed'] != true) {
			        		$page = new Page_Apply_Judgepay() ;
			        	} else {
			        		$page = '' ;
			        	}
                        break;
                    case Do_Firm_Info::STATUS_UNCHECK :
                        if ($firmInfo->alipay_sign == Do_Firm_Info::ALIPAY_SIGN_AUTHED)
                        {
                            $page = new Page_Apply_Commit() ;
                        }
                        break;
                    case Do_Firm_Info::STATUS_UNPASS :
                        $reason = Dr_Failedreasonlog::getFailedReasonLogByTypeId(1, $uid);
                        $reason = htmlspecialchars($reason['msg']);
                        Comm_Context::set('firmUnpassReason', $reason);
                        $page = new Page_Apply_Commit() ;
                        break;
                    case Do_Firm_Info::STATUS_FROZEN :
                        if ($frozen)//冻结时去冻结页面
                        {
                            $page = new Page_Apply_Frozen() ;
                        }else//冻结仍可用
                        {
                            $page = '';
                            break;
                        }
                        break;
                }
            }
        } 
		
		return isset($page) ? $page : new Page_Apply_Index() ;
	}
}
