<?php
/**
 * 商户发红包接口
 * User: cts
 * Date: 2019/1/7
 */
require_once dirname(dirname(dirname(__FILE__))) . '/../stdafx.php';//每个PHP脚本都要引入这个文件
class Daemon_Cron_Dxj_BonusAddTpl extends Daemon_Abstract
{
    protected $cronName = 'DXJ_BONUS_ADD_TPL';

    public function run()
    {
        try {
            //创建模版
            //$redRes = Model_Bonus::addtpl();
            $redRes = Model_Bonus::updateTpl();
            var_dump($redRes);

        } catch (Exception $e) {
            $this->warning('Unknown Exception:' . $e->getMessage());
            $this->exceptions[] = $e;
        }
    }
}
new Daemon_Cron_Dxj_BonusAddTpl();