<?php
declare(strict_types=1);
namespace  App\Controllers;


use App\Interfaces\EmailSecretKeyInterface as Mail;
use PHPMailer\PHPMailer\PHPMailer;
class Mailer implements Mail
{
    

    public static function sendMail($from, $from_name, $to, $subject, $content, $copy = null, $bcc = null){
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = Mail::Host;
        $mail->SMTPAuth = Mail::SMTPAuth;
        $mail->Username = Mail::Username; 
        $mail->Password = Mail::Password;
        $mail->SMTPSecure = Mail::SMTPSecure;
        $mail->Port = Mail::Port;
        
        $mail->setFrom($from, $from_name);
        
        $mail->addAddress($to);
        if($copy !== null){
            // should be comma seperated emails
            $mail->addCC($copy);
        }
       if($bcc !== null){
            // should be comma seperated emails
            $mail->addBCC($bcc);
       }
        
        $mail->Subject = $subject;

       
        $mail->isHTML(true);
        
       
        $mail->Body = $content;
       
        if($mail->send()){
            return true;
        }else{
            return false;
        }
        
    }
}
