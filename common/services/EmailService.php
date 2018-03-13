<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/25
 * Time: 下午2:16
 */
namespace common\services;

use Yii;

class EmailService {

    /**
     * @param $sendTo     //发送人
     * @param $ccTo       //抄送人 
     * @param $text       //邮件正文
     * @param $title      //邮件标题
     * @param $fileName   //附件
     * @return bool
     * @created by Jhu
     */
    public static function sendMail (
        $sendTo, 
        $ccTo, 
        $text, 
        $title, 
        $file
    ) {
        $mail = Yii::$app->mailer->compose();
        //$mail->setTo(['hujiyu@pnlyy.com']);
        $mail->setTo($sendTo);
        $mail->setCc($ccTo);
        $mail->setTextBody($text);
        $mail->setSubject($title);
        
        if (!empty($file)) 
        {
            $mail->attach($file['name']);
        }
        
        if ($mail->send())
        {
            $mail = null;
            return true;
        }else {
            $mail = null;
            return false;
        }
    }
}