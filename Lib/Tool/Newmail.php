<?php
require_once T3PPATH.'/PHPMailer/PHPMailer.php';
class Tool_Newmail{
	const MAILPORT = 25;
	const SMTPSERVER = "smtp.sina.com.cn";
	
	private static $mail_account = array(
	'base' => array(
	'mail' => "proweibo@sina.cn",
	'mailfrom' => "微博专业版",
	'password' => 'astroboy33',
	),
	'keyword' => array(
	'mail' => "keywordmonitor@sina.cn",
	'mailfrom' => "微博关键词监控",
	'password' => 'keyword1q2w3e',
	),
	);

	public static function sendMail($content, $address, $subject = "",$ishtml = false,$mail_type = 'base') {

		if(!is_array($address))
		$address = array($address);
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
                $mail->SetLanguage ( 'en', T3PPATH . '/PHPMailer/language/' );

               	$re = $mail->Send ();
                return $re;
        }

}