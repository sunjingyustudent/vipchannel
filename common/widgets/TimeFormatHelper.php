<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/2/3
 * Time: 下午6:41
 */
namespace common\widgets;

class TimeFormatHelper {

    public static function getClassLengthByClassType($type)
    {
        switch ($type)
        {
            case 1 :
                return '25分钟';
            case 2 :
                return '45分钟';
            case 3 :
                return '50分钟';
        }
    }

    public static function timeClassFormatAll($timeClass)
    {
        $week = date('w', $timeClass);
        
        switch ($week)
        {
            case 0 :
                $week = '周日';
                break;
            case 1 :
                $week = '周一';
                break;
            case 2 :
                $week = '周二';
                break;
            case 3 :
                $week = '周三';
                break;
            case 4 :
                $week = '周四';
                break;
            case 5 :
                $week = '周五';
                break;
            case 6 :
                $week = '周六';
                break;

        }
        return date('m月d日 ', $timeClass) . $week . date('H:i', $timeClass);
    }

    public static function timeFormat($timeStart, $timeEnd)
    {
        $week = date('w', $timeStart);

        switch ($week)
        {
            case 0 :
                $week = '周日 ';
                break;
            case 1 :
                $week = '周一 ';
                break;
            case 2 :
                $week = '周二 ';
                break;
            case 3 :
                $week = '周三 ';
                break;
            case 4 :
                $week = '周四 ';
                break;
            case 5 :
                $week = '周五 ';
                break;
            case 6 :
                $week = '周六 ';
                break;

        }

        return $week . date('m月d日 H:i', $timeStart) . date(' - H:i', $timeEnd);
    }
}