<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/22
 * Time: 上午9:55
 */
namespace common\logics\student;

use common\widgets\BinaryDecimal;
use Yii;
use yii\base\Object;
use yii\db\Exception;
use common\services\LogService;

class FixtimeLogic extends Object implements IFixtime
{
    /** @var  \common\sources\read\classes\ClassAccess  $RClassAccess */
    private $RClassAccess;
    /** @var  \common\sources\write\classes\ClassAccess  $RClassAccess */
    private $WClassAccess;
    /** @var  \common\sources\read\chat\ChatAccess  $RChatAccess */
    private $RChatAccess;
    /** @var  \common\sources\read\teacher\TeacherAccess  $RTeacherAccess */
    private $RTeacherAccess;
    /** @var  \common\compute\SalaryCompute $salaryCompute */
    private $salaryCompute ;


    /** @var  \common\sources\read\student\StudentAccess  $RStudentAccess */





    public function init()
    {
        $this->RClassAccess = Yii::$container->get('RClassAccess');
        $this->WClassAccess = Yii::$container->get('WClassAccess');
        $this->RChatAccess = Yii::$container->get('RChatAccess');
        $this->RTeacherAccess = Yii::$container->get('RTeacherAccess');
        $this->salaryCompute = Yii::$container->get('salaryCompute');
        parent::init();
    }


//    public function getStudentFixTime($student_id)
//    {
//    	$user = $this->RChatAccess->getWechatAccByopenid($student_id);
//        $timeList = $this->RClassAccess->getStudentFixTimeInfo($student_id);
//        $teacherList = $this->RTeacherAccess->getTeacherByName();
//
//        $weekList = array (
//            ['key' => 1, 'week' => '周一'], ['key' => 2, 'week' => '周二'],
//            ['key' => 3, 'week' => '周三'], ['key' => 4, 'week' => '周四'],
//            ['key' => 5, 'week' => '周五'], ['key' => 6, 'week' => '周六'],
//            ['key' => 7, 'week' => '周日']
//        );
//
//        $classType = array (
//            ['key' => 1, 'type' => '25分钟'],
//            ['key' => 2, 'type' => '45分钟'],
//            ['key' => 3, 'type' => '50分钟']
//        );
//
//        $hourList = array();
//
//        for($i = 0;$i < 24; $i ++)
//        {
//            $hourList[] = array('key' => $i, 'hour' => $i);
//        }
//
//
//        return [$teacherList, $weekList, $hourList, $classType, $timeList, $user['openid']];
//    }

    public function getStudentFixTime($student_id)
    {
        $user = $this->RChatAccess->getWechatAccByopenid($student_id);
        $timeList = $this->RClassAccess->getStudentFixTimeInfo($student_id);

        if (!empty($timeList))
        {
            foreach ($timeList as &$item)
            {
                if ($item['gender'] == 0)
                {
                    $gender = '男';
                }else{
                    $gender = '女';
                }

                $grade_info = $this->RTeacherAccess->getTeacherGradeByInstrument($item['teacher_id'], $item['instrument_id']);

                $item['teacher_name'] = $item['teacher_name'] . '[' . $gender . '-' . $grade_info['grade'] . '-' . $grade_info['level'] . ']';
            }
        }

        $weekList = array (
            ['key' => 1, 'week' => '周一'], ['key' => 2, 'week' => '周二'],
            ['key' => 3, 'week' => '周三'], ['key' => 4, 'week' => '周四'],
            ['key' => 5, 'week' => '周五'], ['key' => 6, 'week' => '周六'],
            ['key' => 7, 'week' => '周日']
        );

//        $classType = array (
//            ['key' => 1, 'type' => '25分钟'],
//            ['key' => 2, 'type' => '45分钟'],
//            ['key' => 3, 'type' => '50分钟']
//        );

        $classType = $this->RClassAccess->getLeftClassType($student_id);

        $instrumentType = $this->RClassAccess->getLeftInstrument($student_id);

//        $instrumentType = array (
//            ['key' => 1, 'type' => '钢琴'],
//            ['key' => 2, 'type' => '小提琴'],
//            ['key' => 3, 'type' => '手风琴'],
//            ['key' => 4, 'type' => '古筝'],
//        );

        $hourList = array();

        for($i = 0;$i < 24; $i ++)
        {
            $hourList[] = array('key' => $i, 'hour' => $i);
        }


        return [$weekList, $hourList, $classType, $timeList, $user['openid'], $instrumentType];
    }

   	public function getDoStudentFixTime($openId, $fixInfo='', $logid)
    {
   	
        $student = $this->RChatAccess->getWechatAccByExist($openId);

        if(!empty($student))
        {
            if (!empty($fixInfo)) {
                foreach ($fixInfo as &$row) {

                    if (empty($row['teacher_id']))
                    {
                        $error_message = '周'.$row['week'].' '.$row['time'].' '.'请选择老师';
                        return json_encode(array('error' => $error_message));
                    }

                    $timeArr = explode(':', $row['time']);
                    if ($timeArr[0] < 0 || $timeArr[0] >= 24 || ($timeArr[1] != '00' && $timeArr[1] != '30')) {
                        return json_encode(array('error' => '时间错误'));
                    }
                    $row['time_bit'] = $this->getStudentFixTimeBit($timeArr[0], $timeArr[1], $row['class_type']);

                    $student_time = $this->RClassAccess->studentTimeExit($row['teacher_id'],$row['week'],$student->uid);
                    
                    foreach($student_time as $bit)
                    {
                        $num = 1;
                        for($i = 1; $i <= 49; $i++)
                        {
                            $bit_per = ($bit & $num) == $num ? 1 : 0;
                            $time_per = ($row['time_bit'] & $num) == $num ? 1 : 0;

                            if($bit_per == 1 && $time_per == 1)
                            {
                                $error_message = '周'.$row['week'].' '.$row['time'].' '.'已有学生';
                                return json_encode(array('error' => $error_message));
                            }

                            $num = $num << 1;
                        }
                    }

//                    $teacher_fix_time = $this->RClassAccess->getTeacherFixedTime($row['teacher_id'],$row['week']);
//
//                    $num = 1;
//                    for($i = 1; $i <= 49; $i++)
//                    {
//                        $teacher_fix_per = ($teacher_fix_time & $num) == $num ? 1 : 0;
//                        $time_per = ($row['time_bit'] & $num) == $num ? 1 : 0;
//
//                        if($teacher_fix_per == 1 && $time_per == 1)
//                        {
//                            $error_message = '周'.$row['week'].' '.$row['time'].' '.'老师休息';
//                            return json_encode(array('error' => $error_message));
//                        }
//
//                        $num = $num << 1;
//                    }

                }
                $this->WClassAccess->deleteStudentFixTime($student['uid']);
                $this->WClassAccess->addStudentFixTime($student['uid'], $fixInfo);
            } else {
                $this->WClassAccess->deleteStudentFixTime($student['uid']);
            }
        }else {
            return json_encode(array('error' => '该学生未注册或未绑定微信'));
        }

        LogService::OutputLog($logid, 'add', '', '学生固定时间');

        return json_encode(array('error' =>''));
    }

    public function getStudentFixTimeBit($timeHead,$timeFoot,$classType) 
    {
        $index = 2*$timeHead + ($timeFoot === '00' ? 0 : 1);
        $num = pow(2,$index);
        $num += ($classType == 1 ? 0 : pow(2,$index+1));
        return $num;
    }

    public function getAvFixTeacherList($open_id, $week, $hour, $min, $class_type, $instrument_type, $filter_name)
    {
        $filter_name = trim($filter_name);
        $student = $this->RChatAccess->getWechatAccByExist($open_id);

        if(!empty($student))
        {
            $time_class = strtotime(date('Y-m-d',time()).' '.$hour.':'.$min);

            if ($class_type == 1)
            {
                $time_end = $time_class + 1500;
            }elseif ($class_type == 2)
            {
                $time_end = $time_class + 2700;
            }else{
                $time_end = $time_class + 3000;
            }

            $av_list = $this->salaryCompute->getAvailableFixWeek($week, $time_class, $time_end)['data'];

            $class_bit = BinaryDecimal::getClassBit($time_class, $time_end);

            $student_teacher_fix_exit = $this->RClassAccess->getStudentTeacherFixIsExit($student->uid, $week, $class_bit);

            $teacher_list = $this->RTeacherAccess->getTeacherNameByCondition($av_list, $student_teacher_fix_exit, $instrument_type, $filter_name);

            return array('error' => 0, 'data' => $teacher_list);
        }else{
            return array('error' => '该学生未注册或未绑定微信', 'data' => '');
        }
    }


}