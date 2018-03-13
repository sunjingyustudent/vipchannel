<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午3:18
 */
namespace common\logics\salary;

use common\widgets\PhpExcel;
use common\widgets\BinaryDecimal;
use Yii;
use yii\base\Object;

class SalaryLogic extends Object implements ISalary
{
    /** @var  \common\sources\read\salary\RewardAccess $RRewardAccess */
    private $RRewardAccess;
    /** @var  \common\sources\read\teacher\TeacherAccess $RTeacherAccess */
    private $RTeacherAccess;
    /** @var  \common\sources\read\salary\BasepayAccess $RBasepayAccess */
    private $RBasepayAccess;
    /** @var  \common\sources\read\salary\WorkhourAccess $RWorkhourAccess */
    private $RWorkhourAccess;
    /** @var  \common\sources\read\salary\PunishmentAccess $RPunishmentAccess */
    private $RPunishmentAccess;
    /** @var  \common\sources\read\salary\OthersAccess $ROthersAccess */
    private $ROthersAccess;
    /** @var  \common\sources\write\salary\BasepayAccess $WBasepayAccess */
    private $WBasepayAccess;
    /** @var  \common\sources\write\salary\WorkhourAccess $WWorkhourAccess */
    private $WWorkhourAccess;
    /** @var  \common\sources\write\salary\RewardAccess $WRewardAccess */
    private $WRewardAccess;
    /** @var  \common\sources\write\salary\OthersAccess $WOthersAccess */
    private $WOthersAccess;
    /** @var  \common\sources\read\classes\ClassAccess $WClassAccess */
    private $RClassAccess;
    /** @var  \common\sources\read\teacher\RestAccess $WRestAccess */
    private $RRestAccess;
    /** @var  \common\compute\SalaryCompute $salaryCompute */
    private $salaryCompute ;


    public function init()
    {
        $this->RRewardAccess = Yii::$container->get('RRewardAccess');
        $this->RTeacherAccess = Yii::$container->get('RTeacherAccess');
        $this->RBasepayAccess = Yii::$container->get('RBasepayAccess');
        $this->RWorkhourAccess = Yii::$container->get('RWorkhourAccess');
        $this->RPunishmentAccess = Yii::$container->get('RPunishmentAccess');
        $this->ROthersAccess = Yii::$container->get('ROthersAccess');
        $this->WBasepayAccess = Yii::$container->get('WBasepayAccess');
        $this->WWorkhourAccess = Yii::$container->get('WWorkhourAccess');
        $this->WRewardAccess = Yii::$container->get('WRewardAccess');
        $this->WOthersAccess = Yii::$container->get('WOthersAccess');
        $this->RClassAccess = Yii::$container->get('RClassAccess');
        $this->RRestAccess = Yii::$container->get('RRestAccess');
        $this->salaryCompute = Yii::$container->get('salaryCompute');

        parent::init();
    }

    // 获取老师 昵称 ID wang
    public function  getTeacherWagesList($month_time, $base, $work, $filter)
    {
        $timeStart = strtotime($month_time);

        $timeEnd = strtotime("+1 month", $timeStart);

        $teacher_list = $this->RTeacherAccess->getSalaryTeacher($base,$work,$filter);

//        print_r($teacher_list);exit;

        if (!empty($teacher_list))
        {
            foreach ($teacher_list as $key => $row)
            {
                $teacher_list[$key]['salary'] = $this->RBasepayAccess->getTeacherMonthSalary($row['id'],$timeStart,$timeEnd)['salary'];

                $teacher_list[$key]['class_commission'] = $this->RWorkhourAccess->getClassCommission($row['id'],$timeStart,$timeEnd)['class_commission'];

                $teacher_list[$key]['reward'] = $this->RRewardAccess->getTeacherRewardTotal($row['id'],$timeStart,$timeEnd)['reward'];

                $others = $this->ROthersAccess->getOthersMoney($row['id'],$timeStart,$timeEnd);

                $teacher_list[$key]['other_reward'] = empty($others['reward']) ? 0 :$others['reward'];

                $teacher_list[$key]['punishment'] = $this->RPunishmentAccess->getTeacherPunishmentTotal($row['id'],$timeStart,$timeEnd)['punishment'];

                $teacher_list[$key]['other_punishment'] = empty($others['punishment']) ? 0 : $others['punishment'];
            }
        }

        return $teacher_list;
    }

    public function showDetail($teacher_id, $month, $type)
    {
        $timeStart = strtotime($month);
        $timeEnd = strtotime("+1 month", $timeStart);

        if($type == 1)
        {
            return $this->RBasepayAccess->getTeacherMonthSalaryList($teacher_id, $timeStart, $timeEnd);
        }elseif ($type == 2)
        {
            return $this->RWorkhourAccess->getClassCommissionList($teacher_id, $timeStart, $timeEnd);
        }elseif ($type == 3)
        {
            return $this->RRewardAccess->getTeacherRewardList($teacher_id, $timeStart, $timeEnd);

        }elseif ($type == 4)
        {
            return $this->RPunishmentAccess->getTeacherPunishmentList($teacher_id, $timeStart, $timeEnd);
        }elseif ($type == 5)
        {
            return $this->ROthersAccess->getOtherRewardList($teacher_id, $timeStart, $timeEnd);
        }else{
            return $this->ROthersAccess->getOtherPunishmentList($teacher_id, $timeStart, $timeEnd);
        }
    }

    public function getMonthTotalMoney($month)
    {
        $timeStart = strtotime($month);
        $timeEnd = strtotime('+1 month', $timeStart);

        $total = array();

        $total['salary_total'] = $this->RBasepayAccess->getSalaryTotal($timeStart, $timeEnd);

        $total['commission_total'] = $this->RWorkhourAccess->getRewardTotal($timeStart, $timeEnd);
        $total['reward_total'] = $this->RRewardAccess->getRewardTotal($timeStart, $timeEnd);
        $total['punishment_total'] = $this->RPunishmentAccess->getPunishmentTotal($timeStart, $timeEnd);
        $total['other_reward_total'] = $this->ROthersAccess->getOtherRewardTotal($timeStart, $timeEnd);
        $total['other_punishment_total'] = $this->ROthersAccess->getOtherPunishmentTotal($timeStart, $timeEnd);

        return $total;
    }

    public function confirmSalary($month)
    {
        $timeStart = strtotime($month);

        $timeEnd = strtotime('+1 month',$timeStart);

        $transaction = Yii::$app->db->beginTransaction();
        try{

            $this->WBasepayAccess->updateIsPublish($timeStart, $timeEnd);

            $this->WWorkhourAccess->updateIsPublish($timeStart, $timeEnd);

            $this->WRewardAccess->updateRewardIsPublish($timeStart, $timeEnd);

            $this->WOthersAccess->updateIsPublish($timeStart, $timeEnd);

            $transaction->commit();

            return json_encode(array('error' => ''));

        }catch (Exception $e) {
            $transaction->rollBack();
            return json_encode(array('error' => '发布失败'));
        }
    }

    public function isPublish($month)
    {
        $timeStart = strtotime($month);

        $timeEnd = strtotime('+1 month',$timeStart);

        $is_publish = $this->RBasepayAccess->isPublish($timeStart, $timeEnd);

        return $is_publish;
    }

    public function getSalaryLogByTeacherId($teacher_id)
    {
        return $this->RBasepayAccess->getSalaryLogByTeacherId($teacher_id);
    }

    public function exportSalary($month_time, $base, $work, $filter)
    {
        $timeStart = strtotime($month_time);

        $timeEnd = strtotime("+1 month", $timeStart);

        $teacher_list = $this->RTeacherAccess->getSalaryTeacher($base,$work,$filter);

        $teacher_list_new = array();

        if (!empty($teacher_list))
        {

            foreach ($teacher_list as $key => $row)
            {

                $teacher_list_new[$key] = array(
                    'id' => '',
                    'nick' => '',
                    'mobile' => '',
                    'work_hour' => 0,
                    'tmp_leave' => 0,
                    'normal_leave' => 0,
                    '25_min_class' => 0,
                    '45_min_class' => 0,
                    '50_min_class' => 0,
                    'salary' => 0,
                    'class_commission' => 0,
                    'reward' => 0,
                    'punishment' => 0,
                    'ac_salary' => 0
                );

                $teacher_list_new[$key]['id'] = $row['id'];

                $teacher_list_new[$key]['nick'] = $row['nick'];

                $teacher_list_new[$key]['mobile'] = $row['mobile'];

                $teacher_list_new[$key]['salary'] = $this->RBasepayAccess->getTeacherMonthSalary($row['id'],$timeStart,$timeEnd)['salary'];

                $teacher_list_new[$key]['class_commission'] = $this->RWorkhourAccess->getClassCommission($row['id'],$timeStart,$timeEnd)['class_commission'];

                /*$teacher_list_new[$key]['reward'] = $this->RRewardAccess->getTeacherRewardTotal($row['id'],$timeStart,$timeEnd)['reward'];*/

                $teacher_list_new[$key]['reward'] = $this->RRewardAccess->getTeacherRewardPunishment($row['id'],$timeStart,$timeEnd)['salary_reward'];

                $others = $this->ROthersAccess->getOthersMoney($row['id'],$timeStart,$timeEnd);

                /*$teacher_list_new[$key]['other_reward'] = empty($others['reward']) ? '0': $others['reward'];*/

                /*$teacher_list_new[$key]['punishment'] = $this->RPunishmentAccess->getTeacherPunishmentTotal($row['id'],$timeStart,$timeEnd)['punishment'];*/

                $teacher_list_new[$key]['punishment'] = $this->RRewardAccess->getTeacherRewardPunishment($row['id'],$timeStart,$timeEnd)['salary_punish'];

                /*$teacher_list_new[$key]['other_punishment'] = empty($others['punishment']) ? '0' : $others['punishment'];*/

                /*$ac_salary = $teacher_list_new[$key]['salary'] + $teacher_list_new[$key]['class_commission'] + $teacher_list_new[$key]['reward'] + $teacher_list_new[$key]['other_reward'] - $teacher_list_new[$key]['punishment'] - $teacher_list_new[$key]['other_punishment'];*/

                $ac_salary = $teacher_list_new[$key]['salary'] + $teacher_list_new[$key]['class_commission'] + $teacher_list_new[$key]['reward']  - $teacher_list_new[$key]['punishment'];

                $teacher_list_new[$key]['ac_salary'] = empty($ac_salary) ? '0' : $ac_salary;


                $class_list = $this->RWorkhourAccess->getClassCommissionList($row['id'],$timeStart,$timeEnd);
                $class_count = [
                    '25' => 0,
                    '45' => 0,
                    '50' => 0
                ];

                foreach($class_list as $class_entry)
                {
                    if($class_entry['time_end'] - $class_entry['time_class'] == 1500 )
                    {
                        $class_count['25'] += 1;
                    }
                    elseif($class_entry['time_end'] - $class_entry['time_class'] == 2700 )
                    {
                        $class_count['45'] += 1;
                    }
                    elseif($class_entry['time_end'] - $class_entry['time_class'] == 3000 )
                    {
                        $class_count['50'] += 1;
                    }
                }

                $teacher_list_new[$key]['25_min_class'] = $class_count['25'];
                $teacher_list_new[$key]['45_min_class'] = $class_count['45'];
                $teacher_list_new[$key]['50_min_class'] = $class_count['50'];


                // 获取老师在某个时间区间内时段及请假总小时数
                $work_hour = 0;
                $tmp_overlap_sum = 0;
                $all_overlap_sum = 0;
                $cur_end_time = $timeEnd;
                // 循环1周
                for($idx = 1; $idx <=7; $idx++)
                {
                    // 从end往前倒退一天，3月31日，星期5
                    $cur_end_time = $cur_end_time - 86400;
                    $cur_week = date('w',$cur_end_time);
                    $cur_week = ($cur_week == 0? 7: $cur_week);
                    // 该教师所有星期x的时段安排
                    // $teacher_time_bit = $this->RTeacherAccess->getTeacherFixedTimeRowOrderByExeTime($row['id'],$cur_week);
                    $teacher_time_bit = $this->salaryCompute->getTeacherFixedTimeRowOrderByExeTime($row['id'],$cur_week)['data'];

                    if(empty($teacher_time_bit))
                    {
                        continue;
                    }

                    $entry_idx = 0;
                    // 最新执行的时段安排，及执行时间
                    $last_time_execute =  $teacher_time_bit[$entry_idx]['time_execute'];
                    $work_hour_list = BinaryDecimal::binaryToDecimal($teacher_time_bit[$entry_idx]['time_bit']);
                    // 每次循环计算指定时间段内所有星期x的工作时间
                    $no_record = false;
                    for($time = $cur_end_time; $time >= $timeStart; $time -= 86400*7)
                    {
                        $leave_info =  $this->RRestAccess->getTeacherLeaveInfo($row['id'],$time,$time+86400);
                        $tmp_leave_start = [];
                        $tmp_leave_end = [];
                        $normal_leave_start = [];
                        $normal_leave_end = [];
                        foreach($leave_info as $leave_entry)
                        {
                            if($leave_entry['tmp_leave'] == 0 && $leave_entry['all_leave'] == 0 && $leave_entry['pause'] == 0)
                            {
                                continue;
                            }
                            $start_str = explode(':',date('H:i',$leave_entry['time_start']));
                            $end_str = explode(':',date('H:i',$leave_entry['time_end']));
                            $start = $start_str[0] * 60 + $start_str[1];
                            $end = $end_str[0] * 60 + $end_str[1];

                            if($leave_entry['tmp_leave'] == 1)
                            {
                                $tmp_leave_start[] = $start;
                                $tmp_leave_end[] = $end;
                            }
                            elseif($leave_entry['all_leave'] == 1)
                            {
                                $normal_leave_start[] = $start;
                                $normal_leave_end[] = $end;
                            }
                        }

                        // $work_hour_list = [];
                        // 此日期的时间小于现工作时段执行时间，使用上一次执行的工作时段安排
                        while($time < $last_time_execute)
                        {
                            // 指向下一条记录
                            $entry_idx += 1;
                            // 此日期该老师没有对应时段记录，退出循环
                            if($entry_idx >= sizeof($teacher_time_bit))
                            {
                                $no_record = true;
                                break;
                            }

                            $last_time_execute = $teacher_time_bit[$entry_idx]['time_execute'];
                            $work_hour_list = BinaryDecimal::binaryToDecimal($teacher_time_bit[$entry_idx]['time_bit']);
                        }

                        if($no_record)
                        {
                            break;
                        }

                        $work_start = [];
                        $work_end = [];

                        foreach ($work_hour_list as $item)
                        {
                            $start_str = explode(':',$item['start']);
                            $end_str = explode(':',$item['end']);

                            $start = $start_str[0] * 60 + $start_str[1];
                            $end = $end_str[0] * 60 + $end_str[1];

                            $work_start[] = $start;
                            $work_end[] = $end;

                            $work_hour += ($end - $start);
                        }

                        $tmp_overlap = [];
                        $cur_work = 0;
                        $cur_leave = 0;
                        while (($cur_work < sizeof($work_start)) && ($cur_leave < sizeof($tmp_leave_start)))
                        {
                            $cur_start = max($work_start[$cur_work],$tmp_leave_start[$cur_leave]);
                            $cur_end = min($work_end[$cur_work],$tmp_leave_end[$cur_leave]);
                            $tmp_overlap[] = [$cur_start,$cur_end];
                            if ($cur_end >= $work_end[$cur_work])
                            {
                                $cur_work += 1;
                            }
                            if ($cur_end >= $tmp_leave_end[$cur_leave])
                            {
                                $cur_leave += 1;
                            }
                        }

                        $normal_overlap = [];
                        $cur_work = 0;
                        $cur_leave = 0;
                        while (($cur_work < sizeof($work_start)) && ($cur_leave < sizeof($normal_leave_start)))
                        {
                            $cur_start = max($work_start[$cur_work],$normal_leave_start[$cur_leave]);
                            $cur_end = min($work_end[$cur_work],$normal_leave_end[$cur_leave]);
                            $normal_overlap[] = [$cur_start,$cur_end];
                            if ($cur_end >= $work_end[$cur_work])
                            {
                                $cur_work += 1;
                            }
                            if ($cur_end >= $normal_leave_end[$cur_leave])
                            {
                                $cur_leave += 1;
                            }
                        }

                        foreach($tmp_overlap as $lap_pair)
                        {
                            if($lap_pair[0] < $lap_pair[1])
                            {
                                $tmp_overlap_sum = $tmp_overlap_sum + ($lap_pair[1] - $lap_pair[0]);
                            }
                        }

                        foreach($normal_overlap as $lap_pair)
                        {
                            if($lap_pair[0] < $lap_pair[1])
                            {
                                $all_overlap_sum = $all_overlap_sum + ($lap_pair[1] - $lap_pair[0]);
                            }
                        }

                    }
                }

                $teacher_list_new[$key]['tmp_leave'] = $tmp_overlap_sum / 60;
                $teacher_list_new[$key]['normal_leave'] = $all_overlap_sum / 60;
                $teacher_list_new[$key]['work_hour'] = $work_hour / 60;
            }
        }

        // print_r($teacher_list_new);exit;

        $title = '工资列表';
        $fileName = $title . '.xls';

        $columnMap = array('老师ID','老师姓名','手机号','固定时段','临时请假','提前请假','25分钟课时数','45分钟课时数','50分钟课时数','时段底薪','课时费','奖励','惩罚','总薪资');

        PhpExcel::getExcel($title, $teacher_list_new, $columnMap, $fileName, $is_excel = 1, $width=10);

    }

    public function importSalary($request)
    {
        $filepath = $request['filepath'];

        $month_time = $request['month_time'];

        $tableData = PhpExcel::readExcel($filepath);

        if($tableData == 100)
        {
            return array('error' => 1);
        } else {
            $count = count($tableData);
            $time_created = strtotime($month_time) + 1;
            if ($tableData[1][0] == "老师ID" && $tableData[1][11] == "奖励" && $tableData[1][12] == "惩罚")
            {
                for ($row = 2; $row <= $count; $row++)
                {
                    $teacher_id = $tableData[$row][0] ? $tableData[$row][0] : 0;
                    $salary_reward = $tableData[$row][11] ? $tableData[$row][11] : 0;
                    $salary_punish = $tableData[$row][12] ? $tableData[$row][12] : 0;
                    $this->WRewardAccess->importSalary($teacher_id, $salary_reward, $salary_punish, $time_created);
                }
                return array('error' => 0);
            } else {
                return array('error' => 1);
            }
        }
    }

    public function getTeacherWagesListNew($month_time, $base, $work, $filter)
    {

        $timeStart = strtotime($month_time);

        $timeEnd = strtotime("+1 month", $timeStart);

        $teacher_list = $this->RTeacherAccess->getSalaryTeacher($base, $work, $filter);

//        print_r($teacher_list);exit;

        if (!empty($teacher_list))
        {
            foreach ($teacher_list as $key => $row)
            {
                $instrumentList = $this->RTeacherAccess->getTeacherInstrumentInfo($row['id']) ;

                $teacher_list[$key]['instrumentList'] = $instrumentList ;

                $teacher_list[$key]['salary'] = $this->RBasepayAccess->getTeacherMonthSalary($row['id'],$timeStart,$timeEnd)['salary'];

                $teacher_list[$key]['class_commission'] = $this->RWorkhourAccess->getClassCommission($row['id'],$timeStart,$timeEnd)['class_commission'];

                $teacher_list[$key]['reward'] = $this->RRewardAccess->getTeacherRewardPunishment($row['id'],$timeStart,$timeEnd)['salary_reward'];

                $teacher_list[$key]['punishment'] = $this->RRewardAccess->getTeacherRewardPunishment($row['id'],$timeStart,$timeEnd)['salary_punish'];

//                $teacher_list[$key]['reward'] = $this->RRewardAccess->getTeacherRewardTotal($row['id'],$timeStart,$timeEnd)['reward'];

//                $others = $this->ROthersAccess->getOthersMoney($row['id'],$timeStart,$timeEnd);
//
//                $teacher_list[$key]['other_reward'] = empty($others['reward']) ? 0 :$others['reward'];
//
//                $teacher_list[$key]['punishment'] = $this->RPunishmentAccess->getTeacherPunishmentTotal($row['id'],$timeStart,$timeEnd)['punishment'];
//
//                $teacher_list[$key]['other_punishment'] = empty($others['punishment']) ? 0 : $others['punishment'];
            }
        }

        return array('error' => 0, 'data' => $teacher_list);
    }

    public function getUnPushTeacher($month_time, $base, $work, $filter)
    {
        $timeStart = strtotime($month_time);

        $timeEnd = strtotime("+1 month", $timeStart);

        $teacher_list = $this->RTeacherAccess->getUnPushTeacher($timeStart, $timeEnd, $base, $work, $filter);

        return array('error' => 0, 'data' => $teacher_list);
    }

}