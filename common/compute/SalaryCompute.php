<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午3:18
 */
namespace common\compute;

use common\widgets\BinaryDecimal;
use Yii;
use yii\base\Object;

class SalaryCompute extends Object implements ISalaryCompute
{
    /** @var  \common\sources\read\teacher\RuleAccess $RRuleAccess */
    private $RRuleAccess;
    /** @var  \common\sources\read\salary\BasepayAccess $RBasepayAccess */
    private $RBasepayAccess;
    /** @var  \common\sources\read\salary\WorkhourAccess $RWorkhourAccess */
    private $RWorkhourAccess;
    /** @var  \common\sources\read\teacher\WorktimeAccess $RWorktimeAccess */
    private $RWorktimeAccess;
    /** @var  \common\sources\read\teacher\TeacherAccess $RTeacherAccess */
    private $RTeacherAccess;
    /** @var  \common\sources\read\teacher\RestAccess $RRestAccess */
    private $RRestAccess;


    public function init()
    {
        $this->RRuleAccess = Yii::$container->get('RRuleAccess');
        $this->RBasepayAccess = Yii::$container->get('RBasepayAccess');
        $this->RWorkhourAccess = Yii::$container->get('RWorkhourAccess');
        $this->RWorktimeAccess = Yii::$container->get('RWorktimeAccess');
        $this->RTeacherAccess = Yii::$container->get('RTeacherAccess');
        $this->RRestAccess = Yii::$container->get('RRestAccess');

        parent::init();
    }

    public function calculateSalary($teacher_id, $time, $long, $class_id, $reward_id)
    {
        $reward_info = $this->RRuleAccess->getRewardById($reward_id);

        if ($reward_info['rule_id'] == 1)        //按底薪倍数
        {
            $day_salary = $this->RBasepayAccess->getTeacherDaySalary($teacher_id, $time);

            if (empty($day_salary))
            {
                $data = array(
                    'text' => '当前薪资未收录',
                    'money' => 'null'
                );
            } else {
                $money = $day_salary * $reward_info['num'];

                $data = array(
                    'text' => $day_salary.' * '.$reward_info['num'].' = '.$money,
                    'money' => $money
                );
            }
        } elseif ($reward_info['rule_id'] == 2)    //按固定金额
        {
            $money = $reward_info['num'];

            $data = array(
                'text' => 1 .' * '.$reward_info['num'].' = '.$money,
                'money' => $money
            );
        } elseif ($reward_info['rule_id'] == 3 || $reward_info['rule_id'] == 5 || $reward_info['rule_id'] == 6)     //按分钟|按爽约|按体验成单
        {
            $money = $long * $reward_info['num'];

            $data = array(
                'text' => $long.' * '.$reward_info['num'].' = '.$money,
                'money' => $money
            );
        } else                                      //按课时提成
        {
            $hour_fee = $this->RWorkhourAccess->getHourFeeByClassId($class_id);

            if (empty($hour_fee))
            {
                $hour_fee = $this->RWorkhourAccess->getHourFee($long, $time, $teacher_id);
            }

            if (empty($hour_fee))
            {
                $data = array(
                    'text' => '当前薪资未收录',
                    'money' => 'null'
                );
            }else{
                $money = $hour_fee * $reward_info['num'];

                $data = array(
                    'text' => $hour_fee.' * '.$reward_info['num'].' = '.$money,
                    'money' => $money
                );
            }

        }

        return $data;
    }

    /**
     * @author xl
     *
     */
    public function calculateAttendance($teacher_id, $timeStart, $timeEnd, $rate)
    {

        $salary = $this->RBasepayAccess->getTeacherMonthSalary($teacher_id, $timeStart, $timeEnd);

        if(empty($salary['salary'])){

            $data = array(
                'text' => '当前薪资未收录',
                'money' => 'null'
            );
        }else{
            $money = $salary['salary'] * $rate;

            $data = array(
                'text' => $salary['salary'].' * '.$rate.' = '.$money,
                'money' => $money
            );
        }

        return $data;
    }


    public function computeHourFee($teacher_id, $time_day)
    {
        //实时获取老师时段费=基础费+微调费，获取乐器最大的一个

        $teacher_info = $this->RTeacherAccess->getTeacherInfoById($teacher_id);

        $instrument = $this->RTeacherAccess->getTeacherInstrumentNew($teacher_id);

        $salary_after = array();

        foreach ($instrument as $ke => $row)
        {
            //获取基础费
            $basic_salary = $this->RBasepayAccess->getBasicSalaryByGrade($teacher_info['teacher_type'], $teacher_info['school_id'], $row['grade'], $row['level'], $time_day);

            $basic_salary['salary_after'] = empty($basic_salary['salary_after']) ? 0 : $basic_salary['salary_after'];

            $salary_after[$ke] = $row['salary'] + $basic_salary['salary_after'];
        }

        if (count($salary_after) == 0)
        {
            $hour_fee = 0;
        }elseif (count($salary_after) == 1)
        {
            $hour_fee = $salary_after[0];
        }else{
            $hour_fee = max($salary_after);
        }

        return array('error' => 0, 'data' => $hour_fee);
    }

    public function computeSalary($teacher_id, $time_day)
    {
        $teacher_info = $this->RTeacherAccess->getTeacherInfoById($teacher_id);

        //微调值
        $instrument_list = $this->RTeacherAccess->getTeacherInstrumentNew($teacher_id);

        $salary = array();

        foreach ($instrument_list as $key =>$item)
        {
            $salary[$key]['instrument_id'] = $item['instrument_id'];

            $salary[$key]['grade'] = $item['grade'];

            $salary[$key]['level'] = $item['level'];

            //获取基础费
            $basic_salary = $this->RBasepayAccess->getBasicSalaryByGrade($teacher_info['teacher_type'], $teacher_info['school_id'], $item['grade'], $item['level'], $time_day);

            $basic_salary['salary_after'] = empty($basic_salary['salary_after']) ? 0 : $basic_salary['salary_after'];

            $salary[$key]['salary_after'] = $item['salary'] + $basic_salary['salary_after'];

            $basic_salary['class_hour_first'] = empty($basic_salary['class_hour_first']) ? 0 : $basic_salary['class_hour_first'];

            $salary[$key]['class_hour_first'] = $item['hour_first'] + $basic_salary['class_hour_first'];

            $basic_salary['class_hour_second'] = empty($basic_salary['class_hour_second']) ? 0 : $basic_salary['class_hour_second'];

            $salary[$key]['class_hour_second'] = $item['hour_second'] + $basic_salary['class_hour_second'];

            $basic_salary['class_hour_third'] = empty($basic_salary['class_hour_third']) ? 0 : $basic_salary['class_hour_third'];

            $salary[$key]['class_hour_third'] = $item['hour_third'] + $basic_salary['class_hour_third'];

        }

        return array('error' => 0, 'data' => $salary);
    }

    public function getHourFee($teacher_id, $time_day)
    {
        $salary_after = $this->RBasepayAccess->getHourFee($teacher_id, $time_day);

        if (count($salary_after) == 0)
        {
            $hour_fee = 0;
        }elseif (count($salary_after) == 1)
        {
            $hour_fee = $salary_after[0];
        }else{
            $hour_fee = max($salary_after);
        }

        return array('error' => 0, 'data' => $hour_fee);
    }

    public function getTeacherFixTimeAll($teacher_id, $time_day)
    {
        $fix_bit = $this->RWorktimeAccess->getTeacherFixTimeAll($teacher_id, $time_day);

        $fix_bits = array();

        if (empty($fix_bit))
        {
            $fix_bits = array(
                '1' => 281474976710656,
                '2' => 281474976710656,
                '3' => 281474976710656,
                '4' => 281474976710656,
                '5' => 281474976710656,
                '6' => 281474976710656,
                '7' => 281474976710656
            );
        }else{
            foreach ($fix_bit as $item)
            {
                $fix_bits[$item['week']] = $item['time_bit'];
            }
        }

        return array('error' => 0, 'data' => $fix_bits);
    }
    
    public function getTeacherFixTimeByWeek($teacher_id, $time_day)
    {
        $week = date('w',$time_day);

        $week = empty($week) ? 7 : $week;

        $fix_bit = $this->RWorktimeAccess->getTeacherFixTimeByWeek($teacher_id, $week, $time_day);

        $fix_bit = empty($fix_bit) ? 281474976710656 : $fix_bit;

        return array('error' => 0, 'data' => $fix_bit);
    }

    public function getTeacherClassMoney($teacher_id, $instrument_id, $time_class, $class_long)
    {
        $class_money = $this->RWorkhourAccess->getClassMoney($teacher_id, $instrument_id, $time_class, $class_long);

        return array('error' => 0, 'data' => $class_money);
    }

    public function getOverTime($teacher_id, $time_start, $time_end)
    {
        $time_day = strtotime(date('Y-m-d',$time_start));

        $salary_compute = new SalaryCompute();

        $fix_bit = $salary_compute->getTeacherFixTimeByWeek($teacher_id, $time_day)['data'];

        $time_list = BinaryDecimal::binaryToDecimal($fix_bit);

/*        //将固定时间转为数组
        $fix_arr = array();

        foreach ($time_list as $item)
        {
            $startStr1 = explode(':',$item['start']);
            $endStr1 = explode(':',$item['end']);

            $timeStart1 = $startStr1[0] * 60 + $startStr1[1];

            $timeEnd1 = $endStr1[0] * 60 + $endStr1[1];

            while ($timeStart1 <= $timeEnd1)
            {
                array_push($fix_arr, $timeStart1);

                $timeStart1 ++;
            }
        }

        //将课程时间转为数组
        $class_arr = array();

        $startStr2 = explode(':',date('H:i',$time_start));
        $endStr2 = explode(':',date('H:i',$time_end));

        $timeStart2 = $startStr2[0] * 60 + $startStr2[1];

        $timeEnd2 = $endStr2[0] * 60 + $endStr2[1];

        while ($timeStart2 < $timeEnd2)
        {
            array_push($class_arr, $timeStart2);

            $timeStart2 ++;
        }

        $count = 0;

        //如果课程时间不在固定时间内，则返回分钟数
        foreach ($class_arr as $value)
        {
            if (!in_array($value, $fix_arr))
            {
                $count ++;
            }
        }

        if ($count == count($class_arr))
        {
            $count = $count - 1;
        }*/

        $slot_start = [];
        $slot_end = [];

        foreach ($time_list as $item)
        {
            $startStr1 = explode(':',$item['start']);
            $endStr1 = explode(':',$item['end']);

            $timeStart1 = $startStr1[0] * 60 + $startStr1[1];

            $timeEnd1 = $endStr1[0] * 60 + $endStr1[1];

            array_push($slot_start,$timeStart1);
            array_push($slot_end,$timeEnd1);
        }

        $class_start = [];
        $class_end = [];
        $startStr2 = explode(':',date('H:i',$time_start));
        $endStr2 = explode(':',date('H:i',$time_end));

        $timeStart2 = $startStr2[0] * 60 + $startStr2[1];
        $timeEnd2 = $endStr2[0] * 60 + $endStr2[1];

        array_push($class_start,$timeStart2);
        array_push($class_end,$timeEnd2);

        $overlap = [];
        $cur_class = 0;
        $cur_time_slot = 0;
        while (($cur_class < sizeof($class_start)) && ($cur_time_slot < sizeof($slot_start)))
        {
            $cur_start = max($slot_start[$cur_time_slot],$class_start[$cur_class]);
            $cur_end = min($slot_end[$cur_time_slot],$class_end[$cur_class]);
            array_push($overlap,[$cur_start,$cur_end]);
            if ($cur_end >= $slot_end[$cur_time_slot])
            {
                $cur_time_slot += 1;
            }
            if ($cur_end >= $class_end[$cur_class])
            {
                $cur_class += 1;
            }
        }

        $overlap_sum = 0;
        foreach($overlap as $lap_pair)
        {
            if($lap_pair[0] < $lap_pair[1])
            {
                $overlap_sum = $overlap_sum + ($lap_pair[1] - $lap_pair[0]);
            }
        }

        $class_sum = $class_end[0] - $class_start[0];
        $nonoverlap = $class_sum - $overlap_sum;

        return array('error' => 0, 'data' => $nonoverlap);
    }

    public function getRestTime($teacher_id, $time_day)
    {
        $rest_time = $this->RRestAccess->getLeaveTime($teacher_id, $time_day);

        return array('error' => 0, 'data' => $rest_time);
    }

    public function getTeacherNotAvailableByClass($time_class, $time_end)
    {
//        $time_class = 1489543200;
//        $time_end = 1489544700;

        $week = date('w', $time_class);

        $week = empty($week) ? 7 : $week;

        $list = $this->RWorktimeAccess->getAvailableListByClass($week, $time_class);

        $teacher_ids = array();

        $no_av_ids = array();

        if (!empty($list))
        {
            $class_bit = BinaryDecimal::getClassBit($time_class, $time_end);

            foreach ($list as $value)
            {
                if (!in_array($value['teacher_id'], $teacher_ids))
                {
                    if (($value['time_bit'] & $class_bit) > 0)
                    {
                        $no_av_ids[] = $value['teacher_id'];
                    }

                    $teacher_ids[] = $value['teacher_id'];
                }else{
                    continue;
                }
            }

            return array('error' => 0, 'data' => $no_av_ids);
        }else{
            return array('error' => 0, 'data' => array());
        }
    }

    public function getAvailableFixWeek($week, $time_class, $time_end)
    {
//        $week = 5;
//        $time_class = 1489629600;
//        $time_end = 1489631100;

        $class_bit = BinaryDecimal::getClassBit($time_class, $time_end);

        $current_day = strtotime(date('Y-m-d', time()));

        $week_string = $this->getWeekString($week);

        $first_week = strtotime("next $week_string", $current_day);

        $rest_ids = $this->getClassRestIds($week, $current_day, $class_bit);

        $teacher_list = $this->RWorktimeAccess->getTeacherFixTimeList($week);

        $fix_list = array();
        $time_execute_list = array();
        $av_list = array();

        foreach ($teacher_list as $row)
        {
            $fix_list[$row['teacher_id']][] = $row;
            $time_execute_list[$row['teacher_id']][] = $row['time_execute'];
        }

        foreach ($fix_list as $k => $item)
        {
            if(($item[0]['time_bit'] & $class_bit) > 0)          //最大的执行上班时间和上课时间冲突
            {
                continue;
            }else{                                             //最大的执行上班时间与上课时间不冲突

                //如果最大执行时间小于第一周
                if ($item[0]['time_execute'] < $first_week)
                {
                    if (!in_array($k, $rest_ids))
                    {
                        $av_list[] = $item[0]['teacher_id'];
                    }

                    continue;
                }else{                              //如果执行时间大于第一周

                    if (in_array($first_week, $time_execute_list[$k]))   //如果执行时间包含第一周则循环到第一周时间停止
                    {
                        //初始值
                        $tag = true;

                        foreach ($item as $key => $value)
                        {
                            if ($value['time_execute'] < $first_week)
                            {
                                break;
                            } else {
                                if ($key == 0)
                                {
                                    $tag = true;
                                } else {

                                    $current_week = date('w', $value['time_execute']) == 0 ? 7 : date('w', $value['time_execute']);

                                    if ($current_week == $week)
                                    {
                                        if (($value['time_bit'] & $class_bit) > 0)
                                        {
                                            $tag = false;

                                            break;
                                        }
                                    }else{
                                        $next_week_time = strtotime("next $week_string", $value['time_execute']);

                                        //当前执行时间到当前下一个固定周的时长
                                        $long_1 = $next_week_time - $value['time_execute'];

                                        //当前执行时间到下一个执行时间的时长
                                        $long_2 = $item[$key - 1]['time_execute'] - $value['time_execute'];

                                        if ($long_1 < $long_2)
                                        {
                                            if (($value['time_bit'] & $class_bit) > 0)
                                            {
                                                $tag = false;

                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if (($tag == true) && !in_array($k, $rest_ids))
                        {
                            $av_list[] = $k;
                        }
                    }else {

                        //初始值
                        $tag = true;

                        foreach ($item as $key => $value)
                        {
                            if ($value['time_execute'] < $first_week)
                            {
                                if (($value['time_bit'] & $class_bit) > 0)
                                {
                                    $tag = false;
                                    break;
                                }
                            } else {
                                if ($key == 0)
                                {
                                    $tag = true;
                                } else {

                                    $current_week = date('w', $value['time_execute']) == 0 ? 7 : date('w', $value['time_execute']);

                                    if ($current_week == $week)
                                    {
                                        if (($value['time_bit'] & $class_bit) > 0)
                                        {
                                            $tag = false;

                                            break;
                                        }
                                    }else{
                                        $next_week_time = strtotime("next $week_string", $value['time_execute']);

                                        //当前执行时间到当前下一个固定周的时长
                                        $long_1 = $next_week_time - $value['time_execute'];

                                        //当前执行时间到下一个执行时间的时长
                                        $long_2 = $item[$key - 1]['time_execute'] - $value['time_execute'];

                                        if ($long_1 < $long_2)
                                        {
                                            if (($value['time_bit'] & $class_bit) > 0)
                                            {
                                                $tag = false;

                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if (($tag == true) && !in_array($k, $rest_ids))
                        {
                            $av_list[] = $k;
                        }
                    }
                }
            }
        }

        return array('error' => 0, 'data' => $av_list);
    }

    /**
     * @param $time_start
     * @param $time_end
     * @param $class_bit
     * @author xl
     * 获取同一星期请假与上课冲突的老师ID
     */
    public function getClassRestIds($week, $current_day, $class_bit)
    {
        $rest_list = $this->RRestAccess->getWeekRest($week, $current_day);

        $rest_ids = array();

        if (!empty($rest_list))
        {
            foreach ($rest_list as $rest)
            {
                $rest_bit = BinaryDecimal::getRestBit($rest['time_start'], $rest['time_end']);

                if (($rest_bit & $class_bit) > 0)
                {
                    $rest_ids[] = $rest['teacher_id'];
                }
            }
        }

        return $rest_ids;
    }

    public function getWeekString($week)
    {
        switch ($week)
        {
            case 1:
                return "Monday";
                break;
            case 2:
                return "Tuesday";
                break;
            case 3:
                return "Wednesday";
                break;
            case 4:
                return "Thursday";
                break;
            case 5:
                return "Friday";
                break;
            case 6:
                return "Saturday";
                break;
            default:
                return "Sunday";
        }
    }

    /**
     * 获取老师时段信息按执行时间排序
     * @param $teacherId
     * @param $week
     * @return array
     */
    public function getTeacherFixedTimeRowOrderByExeTime($teacher_id,$week)
    {
        $data = $this->RTeacherAccess->getTeacherFixedTimeRowOrderByExeTime($teacher_id,$week);

        return array('error' => 0, 'data' => $data);
    }
}