<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/25
 * Time: 下午3:03
 */
namespace common\logics\push;

use common\services\EmailService;
use common\widgets\PhpExcel;
use Yii;
use yii\base\Object;

class MailLogic extends Object implements IMail
{
    public function sendMail($msg)
    {
        $msg = json_decode($msg, true);
        //print_r($msg);die;
        if (!empty($msg['file']) && $msg['file']['type'] == 'xls')
        {
            PhpExcel::getExcel(
                $msg['title'],
                $msg['file']['data'],
                $msg['file']['map'],
                $msg['file']['name']
            );
        }

        if (!EmailService::sendMail(
            $msg['send_to'],
            $msg['cc_to'],
            $msg['text'],
            $msg['title'],
            $msg['file']
        )) {
            //error_log
        }
        
        return true;
    }
}