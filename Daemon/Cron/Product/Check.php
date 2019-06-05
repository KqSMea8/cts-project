<?php
/**
 * 商品自动上架
 * @author: cts <zhongfeng@staff.weibo.com>
 * @date: 2018/09/05
 */
require_once dirname(dirname(dirname(__FILE__))) . '/../stdafx.php';//每个PHP脚本都要引入这个文件
class Daemon_Cron_Product_Check extends Daemon_Abstract
{
    protected $cronName = 'PRODUCT_CHECK';

    public function run()
    {
        try {
            $sql = "select * from ex_product where status = ?  and shelf_time <= ? ";
            $data[] = Model_Const::PRODUCT_STATUS_WAIT;
            $data[] = date("Y-m-d H:i");
            $ret = Data_Exchange::fetchAll($sql,$data);
            if(!empty($ret)) {
                foreach($ret as $key=>$rowAry) {
                    Data_Exchange::update('ex_product',array('status' => Model_Const::PRODUCT_STATUS_SHOW,),"product_id = ?",array($rowAry['product_id']));
                }
            }

        } catch (Exception $e) {
            $this->warning("Unknown Exception:".$e->getMessage());
            $this->exceptions[] = $e;
        }
    }
}
new Daemon_Cron_Product_Check();  //注意和类名保持一致