<?php
/**
 * 商品库存预警
 * @author: cts <haoman@staff.weibo.com>
 * @date: 2018/1/8
 */
require_once dirname(dirname(dirname(__FILE__))) . '/../stdafx.php';//每个PHP脚本都要引入这个文件
class Daemon_Cron_Product_Stock extends Daemon_Abstract{


    public function run()
    {
        try {
            $sql = ' select * from ex_product where stock != 0 and stock_alert != 0 and stock <= stock_alert and status = 1 ';
            echo $sql,"\n";
            $ret = Data_Exchange::fetchAll($sql, array());
            if(!empty($ret)) {
                $contentAry = array();
                foreach ($ret as $val) {
                    $contentAry[] = "商品 ".$val['name'] . " 库存不足，请关注！当前库存" . $val['stock'];
                }
                if($contentAry) {
                    $address = array(
                        "libo16@staff.weibo.com",
                        "moci@staff.weibo.com",
                        "xutao3@staff.weibo.com",
                    );
                    //$address = array("haoman@staff.weibo.com","zhongfeng@staff.weibo.com");
                    $content = implode("\n",$contentAry);
                    $subject = "积分兑换商品库存不足提醒";
                    Lib_Mail::sendMail($content, $address, $subject);
                }
            }

        }catch (Exception $e){
            Lib_Log::warning($e->getFile() . ":" . $e->getLine() . "#" . $e->getMessage());
            var_dump($e);
        }
    }
}

new Daemon_Cron_Product_Stock();  //注意和类名保持一致