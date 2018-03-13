<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/10/27
 * Time: 下午7:02
 */
namespace common\widgets;

class Debug {

    public static function debug($message)
    {
        $date = '>>>>>>>>>>>>>> ' . date('Y-m-d H:i:s', time()) . " <<<<<<<<<<<<<<<<\n";
        file_put_contents('/tmp/hujiyu', $date, FILE_APPEND);
        file_put_contents('/tmp/hujiyu',print_r($message,true), FILE_APPEND);
    }
}