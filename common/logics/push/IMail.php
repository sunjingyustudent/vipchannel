<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/25
 * Time: 下午3:02
 */
namespace common\logics\push;


interface IMail {

    /**
     * @param $msg
     * @return mixed
     * @created by Jhu
     * 发送邮件
     */
    public function sendMail($msg);
}