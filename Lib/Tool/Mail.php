<?php
/**
 * Tool_Mail
 * 发送电子邮件
 * @author    xiamengyu<mengyu5@staff.sina.com.cn>
 * @created   2014-8-20
 * @copyright copyright(2013) weibo.com all rights reserved
 */
require_once PATH_ROOT . DS . "Lib" . DS . "Third" .'/PHPMailer/PHPMailer.php';

class Tool_Mail{

    const MAILPORT = 25;
    //const SMTPSERVER = "smtp.sina.com.cn";
    const SMTPSERVER = "smtp.163.com";
    private static $mail_account = array(
        'base' => array(
//            'mail' => "proweibo@sina.cn",
//            'mailfrom' => "新浪微博专业版",
//            'password' => 'astroboy33',

            'mail' => "xiaoxiaotiny001@163.com",
            'mailfrom' => "日志推送",
            'password' => 'liuhui0731',
        ),
        'keyword' => array(
            'mail' => "keywordmonitor@sina.cn",
            'mailfrom' => "712项目 微博关键词监控",
            'password' => 'keyword1q2w3e',
        ),

        'daily_notice' => array(
            'mail' => "daily_notice_001@163.com",
            'mailfrom' => "开心乐园-积分兑换",
            'password' => 'liuhui9',
        ),
        'daily_notice_money' => array(
            'mail' => "daily_notice_001@163.com",
            'mailfrom' => "开心乐园-积分兑红包",
            'password' => 'liuhui9',
        ),
        'week_notice' => array(
            'mail' => "daily_notice_001@163.com",
            'mailfrom' => "开心乐园-积分兑换-周报",
            'password' => 'liuhui9',
        ),
        'week_notice_money' => array(
            'mail' => "daily_notice_001@163.com",
            'mailfrom' => "开心乐园-积分兑红包-周报",
            'password' => 'liuhui9',
        ),
        'kucunyujing' => array(
            'mail' => "yiyuanduobaomail@163.com",
            'mailfrom' => "712项目 库存预警",
            'password' => 'sina370126',

        ),
        'productcode' => array(
            'mail' => "yiyuanduobaomail@163.com",
            'mailfrom' => "712项目 抽奖码报警",
            'password' => 'sina370126',

        ),
    );
    //接口IP 受限,不能正常调用
    /*public static function send($mailToArray, $content, $mailArray = array(), $isHtml = false, $debug = false){
        if (!is_array($mailToArray) || !$content) {
            return false;
        }   
        $mailtos = implode(';', $mailToArray);
        $data = self::getSendInfo();
        $data['to'] = $mailtos;
        $data['body'] = $content;

        if (isset($mailArray['SUBJECT']) && $mailArray['SUBJECT']) {
            $data['title'] = $mailArray['SUBJECT'];
        }   

        $url = 'http://edsapi.mail.sina.com/edsapi/api/sendmoremail.php';
        $postData = array(
                'url' => $url,
                'is_post' => true,
                'data' => $data,
            );
        $res = Tool_Curl::request($postData);
        $res = json_decode($res, true);
        return $res['result']; 
    }*/

    /*private static function getSendInfo(){
        $data = array(
            'from'     => 'weibo_app@vip.sina.com',
            'username' => 'webmaster@weiboopen.sina.com',
            'password' => 'kMGM4NTY3NGI3YTM',
            'nickname' => '微博支付', //发送者显示名称
            'title'    => '微博支付友情提示', //邮件标题
            'sendtype' => 0,
        );
        return $data;
    }*/

    public static function sendMail($content, $address, $subject = "",$ishtml = false,$mail_type = 'base') {

        if(!is_array($address)){
            $address = array($address);
        }

        $body = $content;
        $mail = new PHPMailer ( );
        $mail->IsSMTP ();
        $mail->Port = self::MAILPORT;
        $mail->Host = self::SMTPSERVER;

        $account = self::$mail_account[$mail_type];
        $mail->Username = $account['mail'];
        $mail->Password = $account['password'];
        $mail->From = $account['mail'];
        $mail->FromName = $account['mailfrom'];

        $mail->CharSet = "utf-8";

        $mail->IsHTML ( $ishtml );
        $mail->Body = $body;
        $mail->ClearAddresses ();
        $mail->Subject = $subject;
        foreach ( $address as $maillist ) {
            $mail->AddAddress ( $maillist );
        }
        //$mail->SetLanguage ( 'en', T3PPATH . '/PHPMailer/language/' );
        $mail->SetLanguage ( 'en', PATH_ROOT . DS . "Lib" . DS . "Third" .'/PHPMailer/language/' );

        $re = $mail->Send ();
        if($mail->IsError()){
            return $mail->ErrorInfo;
        }
        return $re;
    }


    public static function sendMailWithAtta($content, $address, $subject = "",$ishtml = false,$mail_type = 'base', $attachments = array()) {

        if(!is_array($address)){
            $address = array($address);
        }

        $body = $content;
        $mail = new PHPMailer ( );
        $mail->IsSMTP ();
        $mail->Port = self::MAILPORT;
        $mail->Host = self::SMTPSERVER;

        $account = self::$mail_account[$mail_type];
        $mail->Username = $account['mail'];
        $mail->Password = $account['password'];
        $mail->From = $account['mail'];
        $mail->FromName = $account['mailfrom'];

        $mail->CharSet = "utf-8";
        //$mail->attachment = '';
        $mail->IsHTML ( $ishtml );
        $mail->Body = $body;
        $mail->ClearAddresses ();
        $mail->Subject = $subject;
        foreach ( $address as $maillist ) {
            $mail->AddAddress ( $maillist );
        }
        if(!empty($attachments)){
            foreach($attachments as $attachmentlist){
                $mail->AddAttachment($attachmentlist['path'], $attachmentlist['name']);
            }
        }
        //$mail->SetLanguage ( 'en', T3PPATH . '/PHPMailer/language/' );
        $mail->SetLanguage ( 'en', PATH_ROOT . DS . "Lib" . DS . "Third" .'/PHPMailer/language/' );

        $re = $mail->Send ();
        if($mail->IsError()){
            return $mail->ErrorInfo;
        }
        return $re;
    }
}
