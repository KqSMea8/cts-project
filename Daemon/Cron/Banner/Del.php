<?php
/**
 * banner自动下架
 * @author: cts <zhongfeng@staff.weibo.com>
 * @date: 2018/09/05
 */
require_once dirname(dirname(dirname(__FILE__))) . '/../stdafx.php';//每个PHP脚本都要引入这个文件
class Daemon_Cron_Banner_Del extends Daemon_Abstract
{
    protected $cronName = 'BANNER_DEL';

    public function run()
    {
        try {
            $sql = "select * from ex_banner where end_time <= ? ";
            $data[] = date("Y-m-d H:i");
            $ret = Data_Exchange::fetchAll($sql,$data);
            if(!empty($ret)) {
                foreach($ret as $key=>$rowAry) {
                    $delSql = "delete from ex_banner where banner_id = ? limit 1";
                    Data_Exchange::exec($delSql,array($rowAry['banner_id']));
                }
            }

        } catch (Exception $e) {
            $this->warning("Unknown Exception:".$e->getMessage());
            $this->exceptions[] = $e;
        }
    }
}
new Daemon_Cron_Banner_Del();  //注意和类名保持一致