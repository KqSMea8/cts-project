<?php

require_once PATH_THIRD_LIB . '/PHPMailer/PHPMailer.php';

class Lib_Mail{
	const MAILPORT = 25;
	const SMTPSERVER = "smtp.sina.com.cn";
	
	private static $mail_account = array(
		'base' => array(
			'mail' => "proweibo@sina.cn",
			'mailfrom' => "新浪微博专业版",
			'password' => 'astroboy33',
		),
		'inner' => array(
			'mail' => "grossfundhuabei@sina.com",
			'mailfrom' => "微博支付系统",
			'password' => '1234qwertyuiop',
		),
	);

	public static function sendMail($content, $address, $subject = "",$ishtml = false,$mail_type = 'base',$cc = array()) {

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
                foreach($cc as $item){
                    $mail->AddCC($item);
                }
                $mail->SetLanguage ( 'en', T3PPATH . '/PHPMailer/language/' );

               	$re = $mail->Send ();
                return $re;
        }

}
