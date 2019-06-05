<?php

require_once dirname(dirname(dirname(__FILE__))) . '/stdafx.php'; //每个PHP脚本都要引入这个文件

/**
 * 模版私信发送
 */
class Daemon_Cron_TemplateMsgSend extends Daemon_Abstract
{
    public function run()
    {
        $redis_obj = new Lib_Redis(Model_Const::REDIS_ALIAS_EXCHANGE, 1);
        $key = Lib_Config::get('DataKey.template_msg_list');

        while (1) {
            $msg_json = $redis_obj->rpop($key);
            if (empty($msg_json)) {
                exit();
            }

            $map = json_decode($msg_json, true);

            if (!is_array($map)) {
                $this->warning(sprintf('not array, msg_json: %s', $msg_json));
                continue;
            }

            Model_Notify::sendCardMsg($map['to_uid'], $map['tid'], $map['data'], $map['note'], $map['url'], $map['link_name']);
        }
    }
}

new Daemon_Cron_TemplateMsgSend();
