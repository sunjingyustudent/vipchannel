<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/9/6
 * Time: 上午9:30
 */
namespace common\widgets;

class Json {

    public static function dieJson($result)
    {
        if($result === true)
        {
            $resultArray = array('error' => '');
        }elseif (!isset($result['error']))
        {
            $resultArray = array('error' => '', 'data' => $result);
        }else
        {
            $resultArray = $result;
        }

        return json_encode($resultArray,JSON_UNESCAPED_SLASHES);
    }
}