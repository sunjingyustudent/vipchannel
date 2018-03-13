<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午3:18
 */
namespace common\logics\teacher;

use common\widgets\BinaryDecimal;
use Yii;
use yii\base\Object;
use common\widgets\PhpExcel;

class ReportLogic extends Object implements IReport
{
    /** @var  \common\sources\read\teacher\ReportAccess $RReportAccess */
    private $RReportAccess;
    /** @var  \common\sources\read\classes\ClassAccess $RClassAccess */
    private $RClassAccess;
    /** @var  \common\sources\read\teacher\TeacherAccess $RTeacherAccess */
    private $RTeacherAccess;
    /** @var  \common\sources\read\teacher\RestAccess $RRestAccess */
    private $RRestAccess;
    /** @var  \common\sources\read\teacher\RuleAccess $RRuleAccess */
    private $RRuleAccess;

    public function init()
    {
        $this->RReportAccess = Yii::$container->get('RReportAccess');
        $this->RClassAccess = Yii::$container->get('RClassAccess');
        $this->RTeacherAccess = Yii::$container->get('RTeacherAccess');
        $this->RRestAccess = Yii::$container->get('RRestAccess');
        $this->RRuleAccess = Yii::$container->get('RRuleAccess');

        parent::init();
    }

    public function getTeacherStatisticsCount($timeStart, $timeEnd)
    {
        return $this->RReportAccess->getHomeStatisticsCount($timeStart, $timeEnd);
    }

    public function getTeacherStatistics($timeStart, $timeEnd, $page_num)
    {
        return $this->RReportAccess->getHomeStatistics($timeStart, $timeEnd, $page_num);
    }

    public function getTeacherCourseStatistics($teacher_id, $year)
    {
        for($i = 1;$i <= 12;$i ++) {
            $timeYear = date("$year-$i-01");
            $timeStart = strtotime($timeYear);
            $timeEnd = strtotime("$timeYear +1 month");

            $classCount[$i-1] = $this->countClassByTeacherWeek($teacher_id, $timeStart, $timeEnd);

        }

        return $classCount;
    }

    public function getCourseStatisticsWeek($teacher_id, $time)
    {
        $dateMonth = empty($time) ? date('Y-m-01', time()) : date('Y-m-01',strtotime($time));

        $timeStart = strtotime($dateMonth);
        $timeEnd = strtotime("$dateMonth + 1 month");
        $week = date('w', $timeStart);
        $week = $week == 0 ? 7 : $week;

        $long = $week - 1;

        $time_start = strtotime("$dateMonth - $long day");

        while($time_start <= $timeEnd)
        {
            $time_end = strtotime('+7 day',$time_start);
            $course_statistics[date('Y-m-d',$time_start)] = $this->countClassByTeacherWeek($teacher_id, $time_start, $time_end);

            $time_start = strtotime("+ 7day",$time_start);
        }

        return $course_statistics;
    }

    private function countClassByTeacherWeek($teacher_id ,$time_start, $time_end)
    {
        $classCount['ex_25'] = $this->RClassAccess->getClassCountByTeacher($teacher_id, $time_start, $time_end, 1500, 1);
        $classCount['ex_45'] = $this->RClassAccess->getClassCountByTeacher($teacher_id, $time_start, $time_end, 2700, 1);
        $classCount['ex_50'] = $this->RClassAccess->getClassCountByTeacher($teacher_id, $time_start, $time_end, 3000, 1);
        $classCount['buy_25'] = $this->RClassAccess->getClassCountByTeacher($teacher_id, $time_start, $time_end, 1500, 0);
        $classCount['buy_45'] = $this->RClassAccess->getClassCountByTeacher($teacher_id, $time_start, $time_end, 2700, 0);
        $classCount['buy_50'] = $this->RClassAccess->getClassCountByTeacher($teacher_id, $time_start, $time_end, 3000, 0);
        $classCount['problem'] = $this->RClassAccess->getClassProblemByTeacher($teacher_id, $time_start, $time_end);
        $classCount['total'] = $classCount['ex_25'] + $classCount['ex_45'] + $classCount['ex_50'] + $classCount['buy_25'] + $classCount['buy_45'] + $classCount['buy_50'];

        return $classCount;
    }

    public function getTeacherLeaveList($timeStart, $timeEnd, $filter, $page_num)
    {
        return $this->RReportAccess->getTeacherLeaveList($timeStart, $timeEnd, $filter, $page_num);
    }
    
    
     public function monitorList($day, $hour)
    {
        $monitor_list = array();

        $music_type = $this->RTeacherAccess->getInstrument();

        foreach ($music_type as $music)
        {
            $monitor_list[$music['id']]['name'] = $music['name'];

            for($i=1; $i<=4; $i++)//启蒙等四个级别
            {
                $class_1 = 0;

                for ($j=1; $j<=4; $j++)//前四周
                {
                    $timeStart = strtotime(date('Y-m-d',strtotime($day."-$j week")). " $hour:00");

                    $class_1 += $this->RClassAccess->getClassByTime($timeStart, $music['id'], $i);
                }

                $current_time = strtotime($day . "$hour:00");

                $class_day = $this->RClassAccess->getClassByTime($current_time, $music['id'], $i);

                $teacher_available_list = $this->getTeacherAvailable($current_time, $music['id'], $i, $hour);

                $monitor_list[$music['id']]['info'][$i]['class_per'] = floor($class_1 / 4 * 100) / 100;

                $monitor_list[$music['id']]['info'][$i]['class_day'] = $class_day;
                $monitor_list[$music['id']]['info'][$i]['teacher_av'] = array('count' => count($teacher_available_list), 'list' => serialize($teacher_available_list));

                $class_count = $this->getClassCount($teacher_available_list, $current_time);

                $monitor_list[$music['id']]['info'][$i]['teacher_hav'] = array('count' => count($class_count['class_have']), 'list' => serialize($class_count['class_have']));
//                $monitor_list[$music['id']]['info'][$i]['teacher_av_class'] = count($teacher_available_list) - count($teacher_have_class_list);

                $monitor_list[$music['id']]['info'][$i]['teacher_class_no'] = array('count' => count($class_count['class_no']), 'list' => serialize($class_count['class_no']));

                $monitor_list[$music['id']]['info'][$i]['teacher_class_one'] = array('count' => count($class_count['class_one']), 'list' => serialize($class_count['class_one']));
            }

        }

        return $monitor_list;

    }
    
     private function getTeacherAvailable($current_time, $type, $level, $hour)
    {
        $teacher_list = $this->RTeacherAccess->getTeacherInfo($type, $level);

        $week = date('w',$current_time);

        $week = $week == 0 ? 7 : $week;

        $teacher_av_list = array();

        $time_day = strtotime(date('Y-m-d',$current_time));

        foreach ($teacher_list as $key=>$teacher)
        {
            $teacher_fix_bit = $this->RTeacherAccess->getTeacherFixTimeByWeek($teacher['id'], $week);

            $fix_bit = empty($teacher_fix_bit) ? 281474976710656 : $teacher_fix_bit;

            $rest_info = $this->RRestAccess->getLeaveTime($teacher['id'], $time_day);

            if (empty($rest_info))
            {
                $rest_bit = 281474976710656;
            }else{
                $rest_bit = BinaryDecimal::getRestBit($rest_info['time_start'], $rest_info['time_end']);
            }

            $time_bit = $fix_bit | $rest_bit;

            $start_attr = $hour * 2;
            $end_attr = $hour * 2 + 1;

            $start_bit = pow(2, $start_attr);
            $end_bit = pow(2, $end_attr);

            $start = ($time_bit & $start_bit) == $start_bit ? 0 : 1;
            $end = ($time_bit & $end_bit) == $end_bit ? 0 : 1;

            if($start == 1 || $end == 1)
            {
                $teacher_av_list[$key]['teacher_id'] = $teacher['id'];
                $teacher_av_list[$key]['nick'] = $teacher['nick'];
                $teacher_av_list[$key]['mobile'] = $teacher['mobile'];
            }
        }

        return $teacher_av_list;
    }

    public function getClassCount($teacher_list, $time)
    {
        $class_no = array();
        $class_one = array();
        $class_have = array();

        foreach ($teacher_list as $key=>$item)
        {
            $count = $this->RClassAccess->getHaveClassByTeacher($item['teacher_id'], $time);

            if ($count == 0)
            {
                $class_no[$key]['nick'] = $item['nick'];
                $class_no[$key]['mobile'] = $item['mobile'];
            }

            if ($count == 1)
            {
                $class_one[$key]['nick'] = $item['nick'];
                $class_one[$key]['mobile'] = $item['mobile'];
            }

            if ($count >= 1)
            {
                $class_have[$key]['nick'] = $item['nick'];
                $class_have[$key]['mobile'] = $item['mobile'];
            }
        }

        return array('class_no' => $class_no, 'class_one' => $class_one, 'class_have' => $class_have);
    }

    public function getUseRateList($timeStart, $timeEnd)
    {
        $timeStart =strtotime($timeStart);
        $timeEnd = strtotime($timeEnd);
        $timeEnd = $timeEnd + 86400;

        $place_ids = $this->RRuleAccess->getPlaceList();

        $data = array();

        foreach ($place_ids as $key => $place)
        {
            $data[$key]['name'] = $place['name'];

            $data[$key]['rate_total'] = $this->getPlaceRateTotal($timeStart, $timeEnd, $place['id']);

            $data[$key]['hour'] = $this->RReportAccess->getHourRateByPlaceId($timeStart, $timeEnd, $place['id']);
        }

//        print_r($data);exit;
        return array('error' => 0, 'data' => $data);
    }

    private function getPlaceRateTotal($timeStart, $timeEnd, $place_id)
    {
        $day_num = ($timeEnd - $timeStart) / 86400;

        $rate_total = 0;

        while ($timeStart < $timeEnd)
        {
            $rate_total += $this->RReportAccess->getPlaceDayRateTotal($timeStart, $place_id);

            $timeStart = $timeStart + 86400;
        }

        $rate_avg = round($rate_total / $day_num * 100 ,2) . '%';

//        print_r($rate_avg);exit;

        return $rate_avg;
    }

    public function getRateDetail($id)
    {
        $info = $this->RReportAccess->getRateDetail($id);

        $info = unserialize($info);

        return array('error' => 0, 'data' => $info);
    }

    public function getGoodAnalysisCount($filter)
    {
        $count = $this->RTeacherAccess->getGoodAnalysisTeacherCount($filter);

        return array('error' => 0, 'data' => $count);
    }

    public function getGoodAnalysisList($timeStart, $timeEnd, $filter, $page_num)
    {
        $timeStart = strtotime($timeStart);
        $timeEnd = strtotime($timeEnd) + 86400;

        $teacher_list = $this->RTeacherAccess->getGoodAnalysisTeacherIds($filter, $page_num);

        $class_info = $this->RClassAccess->getClassRecordList($timeStart, $timeEnd, $filter);

        $data = array();

        foreach ($teacher_list as $row)
        {
            $data[$row['id']] = array(
                'nick' => '',
                'mobile' => '',
                'place_name' => '',
                'instrument' => '',
                '25_good' => '0',
                '45_good' => '0',
                '50_good' => '0',
                'good' => '0',
                'medium' => '0',
                'bad' => '0',
                'has_grade' => '0',
                '25_class' => '0',
                '45_class' => '0',
                '50_class' => '0',
                'class_total' => '0',
                'rate' => '0%',
            );

            $data[$row['id']]['nick'] = $row['nick'];
            $data[$row['id']]['mobile'] = $row['mobile'];
            $data[$row['id']]['place_name'] = $row['place_name'];

            $instrument_info = $this->RTeacherAccess->getTeacherInstrumentNew($row['id']);

            $tmp = '';
            foreach ($instrument_info as $item)
            {
                $tmp .= $item['instrument_name'] . '_'. $this->getInstrumentGrade($item['grade']). '_' . $item['level'] . ',';
            }

            $data[$row['id']]['instrument'] = $tmp;

            foreach ($class_info as $class)
            {
                if ($class['teacher_id'] == $row['id'])
                {
                    $data[$row['id']]['class_total'] = $data[$row['id']]['class_total'] + 1;

                    if (($class['time_end'] - $class['time_class']) == 1500)
                    {
                        $data[$row['id']]['25_class'] = $data[$row['id']]['25_class'] + 1;

                    }elseif (($class['time_end'] - $class['time_class']) == 2700)
                    {
                        $data[$row['id']]['45_class'] = $data[$row['id']]['45_class'] + 1;

                    }else
                    {
                        $data[$row['id']]['50_class'] = $data[$row['id']]['50_class'] + 1;

                    }

                    //好评
                    if ($class['teacher_grade'] == 1)
                    {
                        $data[$row['id']]['good'] = $data[$row['id']]['good'] + 1;

                        if (($class['time_end'] - $class['time_class']) == 1500)
                        {
                            $data[$row['id']]['25_good'] = $data[$row['id']]['25_good'] + 1;

                        }elseif (($class['time_end'] - $class['time_class']) == 2700)
                        {
                            $data[$row['id']]['45_good'] = $data[$row['id']]['45_good'] + 1;

                        }else
                        {
                            $data[$row['id']]['50_good'] = $data[$row['id']]['50_good'] + 1;

                        }
                    }elseif ($class['teacher_grade'] == 2)      //中评
                    {
                        $data[$row['id']]['medium'] = $data[$row['id']]['medium'] + 1;

                    }elseif ($class['teacher_grade'] == 3)       //差评
                    {
                        $data[$row['id']]['bad'] = $data[$row['id']]['bad'] + 1;

                    }

                    $data[$row['id']]['has_grade'] =  $data[$row['id']]['good'] + $data[$row['id']]['medium'] + $data[$row['id']]['bad'];

                    $data[$row['id']]['rate'] = floor($data[$row['id']]['good'] / $data[$row['id']]['class_total'] * 10000) / 100 . '%';
                }
            }
        }

        return array('error' => 0, 'data' => $data);
    }

    public function getInstrumentGrade($grade)
    {
        if ($grade == 1)
        {
            return '启蒙';
        }elseif ($grade == 2)
        {
            return '初级';
        }elseif ($grade == 3)
        {
            return '中级';
        }else{
            return '高级';
        }
    }


    public function exportGoodAnalysis($timeStart, $timeEnd, $filter, $page_num)
    {

        $data = $this->getGoodAnalysisList($timeStart, $timeEnd, $filter, $page_num=0)['data'];

        $title = $timeStart.'-'.$timeEnd.'好评数据';
//        $fileName = '/tmp/' . $title . '.xls';

        $columnMap = array('姓名','手机号','基地','乐器','25好评','45好评','50好评','总好评','中评数','差评数','已评价数','25课时','45课时','50课时','课时总数','好评率');

        PhpExcel::getExcel($title, $data, $columnMap, $fileName = '', $is_excel = 1, $width=10);
    }
}