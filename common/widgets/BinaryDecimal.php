<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 16/11/11
 * Time: 11:28
 */
namespace common\widgets;

class BinaryDecimal {

    public static function binaryToDecimal($dayTimeBit)
    {
        $flag = false;
        $each = array();
        $timeList = array();
        $num = 1;

        for ($i = 1; $i <= 49; $i ++)
        {
            if(($dayTimeBit & $num) == 0 && !$flag)
            {
                $flag = true;
                $tmp = $i / 2;
                $tmp = is_int($tmp) ? ($tmp - 1) . ":30" : floor($tmp) . ":00";
                $each['start'] = $tmp;
            }elseif(($dayTimeBit & $num) == $num && $flag) {
                $flag = false;
                $tmp = $i / 2;
                $tmp = is_int($tmp) ? ($tmp - 1) . ":30" : floor($tmp) . ":00";
                $each['end'] = $tmp;
                $timeList[] = $each;
                $each = [];
            }

            $num = $num << 1;

        }
        return $timeList;
    }

    public static function decimalToBinary($timeBit, $timeList)
    {
        foreach ($timeList as $row)
        {
            $startArr = explode(':', $row['time_start']);
            $endArr = explode(':', $row['time_end']);

            $check_time = self::checkFixTime($startArr, $endArr);

            if($check_time == false){
                return false;
            }

            $startPos = $startArr[1] == '30'
                ? $startArr[0] * 2 + 2
                : $startArr[0] * 2 + 1;

            $endPos = $endArr[1] == '30'
                ? $endArr[0] * 2 + 1
                : $endArr[0] * 2;

            for ($i = $endPos; $i >= $startPos; $i --)
            {
                $endBit = pow(2, $i - 1);
                $timeBit = $timeBit & (~$endBit);
            }
        }
        return $timeBit;
    }

    public static function checkTime($startArr, $endArr)
    {
        if(preg_match('/^[0-9]{1,2}$/',$startArr[0]) && preg_match('/^[0-9]{1,2}$/',$startArr[1]) && preg_match('/^[0-9]{1,2}$/',$endArr[0]) && preg_match('/^[0-9]{1,2}$/',$endArr[1]))
        {
            if($startArr[1] != "00" && $startArr[1] != "30"){
                return true;
            }
            if($startArr[0] < 0 || $startArr[0] > 24)
            {
                return true;
            }
            if($endArr[1] != '00' && $endArr[1] != '30'){
                return true;
            }
            if($endArr[0] < 0 || $endArr[0] > 24 )
            {
                return true;
            }
            if($startArr[0] > $endArr[0]){
                return true;
            }
            if(($startArr[0] == $endArr[0]) && ($startArr[1] >= $endArr[1])){
                return true;
            }
        }else{
            return true;
        }
    }

    public static function checkFixTime($startArr, $endArr)
    {
        if($startArr[1] != "00" && $startArr[1] != "30"){
            return false;
        }

        if($endArr[1] != '00' && $endArr[1] != '30'){
            return false;
        }

        if($startArr[0] > $endArr[0]){
            return false;
        }

        if(($startArr[0] == $endArr[0]) && ($startArr[1] > $endArr[1])){
            return false;
        }

        if(($startArr[0] == $endArr[0]) && ($startArr[1] == $endArr[1]) && (($startArr[1] != '00') || ($startArr[0] != '00'))){
            return false;
        }

        return true;
    }

    /**
     * @param $timeClass
     * @param $timeEnd
     * @return int|number
     * @author xl
     * 1代表有课
     */
    public static function getClassBit($timeClass, $timeEnd)
    {
        $timeStr = date('H:i',$timeClass);
        $timeArr = explode(':', $timeStr);
        $index = 2*$timeArr[0] + ($timeArr[1] === '00' ? 0 : 1);
        $num = pow(2,$index);
        $num += ($timeEnd - $timeClass == 1500 ? 0 : pow(2,$index+1));
        return $num;
    }

    /**
     * @param $time_start
     * @param $time_end
     * @return int|number
     * @author xl
     * 1代表请假
     */
    public static function getRestBit($time_start,$time_end)
    {
        $startStr = explode(':',date('H:i',$time_start));
        $endStr = explode(':',date('H:i',$time_end));
        $startPos = $startStr[1] == '30' ? $startStr[0] * 2 + 1 : $startStr[0] * 2;
        $endPos = $endStr[1] == '30' ? $endStr[0] * 2 + 1 : $endStr[0] * 2;

        $num = 0;
        for ($i=$startPos; $i < $endPos; $i ++)
        {
            $num += pow(2,$i);
        }

        return $num;
    }

    /**
     * @param $timeBit
     * @return int
     * @author xl
     * 计算时长，多少个0返回多少个30分钟
     */
    public static function getFixLong($timeBit)
    {
        $long = 0;
        for ($i=1; $i<49; $i++)
        {
            $index = pow(2,$i-1);

            if (($timeBit & $index) == 0)
            {
                $long ++;
            }
        }

        return $long;
    }

    /**
     * @param $timeBit
     * @return int
     * @author xl
     * 计算时长，多少个1返回多少个30分钟
     */
    public static function getTimeLong($timeBit)
    {
        $long = 0;

        for ($i=1; $i<49; $i++)
        {
            $index = pow(2,$i-1);

            if (($timeBit & $index) == 1)
            {
                $long ++;
            }
        }

        return $long;
    }

    public static function getStudentFixTimeBit($timeHead, $timeFoot, $classType)
    {
        $index = 2*$timeHead + ($timeFoot === '00' ? 0 : 1);
        $num = pow(2,$index);
        $num += ($classType == 1 ? 0 : pow(2,$index+1));
        return $num;
    }
}