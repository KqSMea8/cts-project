<?php
/**
 * 积分兑换商品日志 rsync 推送兑换商品
 * User: cts(haoman@staff.weibo.com)
 * Date: 2019/2/28
 * 第一次推全量，以后每天推增量（前一天的创建商品or前一天有更新的商品）
 * 提案：http://rt.intra.sina.com.cn/issue/view?issue_id=343520
 */

ini_set('memory_limit', '1024M');
set_time_limit(0);

require_once dirname(dirname(dirname(__FILE__))) . '/../stdafx.php';//每个PHP脚本都要引入这个文件
class Daemon_Cron_PushData_Product extends Daemon_Abstract
{
    protected $cronName = 'PUSHDATA_PRODUCT';
    protected $product_cache;

    public function run()
    {
        $this->cronName = $this->cronName . time();
        try {
            $start = date('Y-m-d', strtotime('-1 days'))." 00:00:00";
            //$start = "2018-11-29 00:00:00";
            $end = date('Y-m-d', strtotime('-1 days'))." 23:59:59";

            //数据存放根目录
            $base_path = "{$_SERVER['SINASRV_DATA_DIR']}product";
            //当天推送地址日期存放
            $data_path = date("Ym",strtotime("-1 day"))."/".date("d",strtotime("-1 day"));
            //推送文件的目标目录  e.g.  201902/28/
            $target_path="{$base_path}/{$data_path}/";
            if (!is_dir($target_path)) {
                mkdir($target_path,0777,true);
            }
            //准备推送的数据存放文件
            $yesterday = date("Ymd",strtotime("-1 day"));
            $file = $target_path."exchange_product_".$yesterday.".txt";

            if(file_exists($file)) {
                exit('file '.$file.' exists');
            }

            $sql = "select * from ex_product where (`create_time` >= '{$start}' and `create_time` <= '{$end}') or (`update_time` >= '{$start}' and `update_time` <= '{$end}')";
            $ret = Data_Exchange::fetchAll($sql);


            if(!empty($ret)) {
                foreach($ret as $key=>$rowAry) {
                    $str = implode("\t",$rowAry);
                    file_put_contents($file, $str."\r\n", FILE_APPEND);
                }
            }else {
                $str = '';
                file_put_contents($file, $str."\r\n", FILE_APPEND);
            }

            $users = array(
                'haoman@staff.weibo.com',
            );
            Tool_Mail::sendMail("积分兑换商品日志生成成功file:{$file}",$users);



        } catch (Exception $e) {
            $this->warning("Unknown Exception:".$e->getMessage());
            $this->exceptions[] = $e;
        }
    }
}
new Daemon_Cron_PushData_Product();  //注意和类名保持一致