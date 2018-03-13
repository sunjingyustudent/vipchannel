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

class RestLogic extends Object implements IRest
{
    private $RWorktimeAccess;
    private $RRestAccess;
    private $WRestAccess;
    private $WWorktimeAccess;
    private $RClassAccess;
    private $WClassAccess;


    public function init()
    {
        $this->RWorktimeAccess = Yii::$container->get('RWorktimeAccess');
        $this->RRestAccess = Yii::$container->get('RRestAccess');
        $this->WRestAccess = Yii::$container->get('WRestAccess');
        $this->WWorktimeAccess = Yii::$container->get('WWorktimeAccess');
        $this->RClassAccess = Yii::$container->get('RClassAccess');
        $this->WClassAccess = Yii::$container->get('WClassAccess');

        parent::init();
    }

    public function editTeacherLeave($request)
    {
        $startArr = explode(':', $request['time_start']);
        $endArr = explode(':', $request['time_end']);

        $re = BinaryDecimal::checkFixTime($startArr, $endArr);

        if($re == false){
            return json_encode(array('error' => '请假时间段格式错误'));
        }

        $start = strtotime($request['timeDay'].' '.$request['time_start']);

        $end = strtotime($request['timeDay'].' '.$request['time_end']);

        if($start >= $end)
        {
            return json_encode(array('error' => '时间段：开始时间不能大于或等于结束时间'));
        }

        $timeDay = strtotime($request['timeDay']);
        $timeEnd = strtotime($request['timeEnd']);

        if($request['is_check'] == 1)
        {
            $transaction = Yii::$app->db->beginTransaction();
            try {

                while ($timeDay <= $timeEnd)
                {
                    $time_start = strtotime(date('Y-m-d',$timeDay).' '.$request['time_start']);

                    $time_end = strtotime(date('Y-m-d',$timeDay).' '.$request['time_end']);

                    $re = $this->WRestAccess->addTeacherLeave($request['teacher_id'], $request['leaveType'], $timeDay, $time_start, $time_end);

                    if (empty($re))
                    {
                        return json_encode(array('error' => '添加请假记录失败'));
                    }

                    $leave_bit = BinaryDecimal::getRestBit($time_start, $time_end);

                    $re = $this->updateTeacherId($request['teacher_id'], $timeDay, $leave_bit);

                    if (!empty($re))
                    {
                        return json_encode(array('error' => $re));
                    }

                    $timeDay = $timeDay + 86400;
                }

                $transaction->commit();
                return json_encode(array('error' => ''));

            }catch (Exception $e) {
                $transaction->rollBack();
                return json_encode(array('error' => '提交失败'));
            }
        }else {

            $transaction = Yii::$app->db->beginTransaction();
            try {

//                $time_start = strtotime($request['timeDay'] . ' ' . $request['time_start']);
//
//                $time_end = strtotime($request['timeDay'] . ' ' . $request['time_end']);

                $re = $this->WRestAccess->addTeacherLeave($request['teacher_id'], $request['leaveType'], $timeDay, $start, $end);

                if (empty($re)) {
                    return json_encode(array('error' => '添加请假记录失败'));
                }

                $leave_bit = BinaryDecimal::getRestBit($start, $end);

                $re = $this->updateTeacherId($request['teacher_id'], $timeDay, $leave_bit);

                if (!empty($re))
                {
                    return json_encode(array('error' => $re));
                }

                $transaction->commit();
                return json_encode(array('error' => ''));

            }catch (Exception $e) {
                $transaction->rollBack();
                return json_encode(array('error' => '提交失败'));
            }
        }
    }

    private function updateTeacherId($teacher_id, $timeDay, $leaveBit)
    {
        $userId = Yii::$app->user->id;

        $role = Yii::$app->user->identity->role;

        $classInfo = $this->RClassAccess->getDayClassByTeacherId($teacher_id, $timeDay);

        if (!empty($classInfo))
        {
            foreach ($classInfo as $class)
            {
                $classBit = BinaryDecimal::getClassBit($class['time_class'], $class['time_end']);

                if (($leaveBit & $classBit) > 0)
                {
                    $is_updated = $this->WClassAccess->updateClassTeacherId($class['id']);

                    if (empty($is_updated))
                    {
                        return '更新老师ID失败';
                    }

                    $fail_id = $this->WClassAccess->intoFailLog($class['id'], $class['student_id'], $teacher_id, $userId, $role, 2);

                    if (empty($fail_id))
                    {
                        return '插入错误课日志失败';
                    }
                }
            }
        }

        return 0;
    }

    public function deleteTeacherLeave($request)
    {
        $timeDay = strtotime($request['timeDay']);
        $timeEnd = strtotime($request['timeEnd']);

        if($request['is_check'] == 1)
        {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                while($timeDay <= $timeEnd)
                {
                    $re = $this->WRestAccess->deleteLeave($request['teacher_id'], $timeDay);

                    if (empty($re))
                    {
                        return 0;
                    }

                    $timeDay = $timeDay + 86400;
                }

                $transaction->commit();
                return 1;

            }catch (Exception $e) {
                $transaction->rollBack();
                return 0;
            }
        }else{

            $re = $this->WRestAccess->deleteLeave($request['teacher_id'], $timeDay);

            if (empty($re))
            {
                return 0;
            }else{
                return 1;
            }
        }

    }

    public function getCalendar($request)
    {
        $dateMonth = !isset($request['time_start'])
            ? date('Y-m-01', time())
            : date('Y-m-01',strtotime($request['time_start']));

        $timeStart = strtotime($dateMonth);
        $timeEnd = strtotime("$dateMonth + 1 month -1 day");
        $week = date('w', $timeStart);
        $week = $week == 0 ? 7 : $week;

        $leave_info = $this->RRestAccess->getTeacherLeaveInfo($request['teacher_id'], $timeStart, $timeEnd + 86400);

        $time = ['start' => $timeStart, 'end' => $timeEnd, 'weekStart' => $week];

        $tmpList = array();
        $leaveList = array();
        $pauseList = array();

        foreach ($leave_info as $leave)
        {
            if ($leave['tmp_leave'] == 1)
            {
                $tmpList[] = date('d', $leave['time_day']);
            }
            if ($leave['all_leave'] == 1)
            {
                $leaveList[] = date('d', $leave['time_day']);
            }
            if ($leave['pause'] == 1)
            {
                $pauseList[] = date('d', $leave['time_day']);
            }
        }

        $data = array(
            'time' => $time,
            'tmpList' => $tmpList,
            'leaveList' => $leaveList,
            'pauseList' => $pauseList
        );

        return $data;
    }

    public function getTeacherLeaveByTeacher($teacher_id, $timeDay)
    {
        return $this->RRestAccess->getLeaveByTeacherId($teacher_id, $timeDay);
    }

    public function getLeaveCount($teacher_id, $timeDay)
    {
        $timeStart = date('Y-m-01',strtotime($timeDay));
        $timeEnd = strtotime("$timeStart + 1month");

        $leave_month_all = $this->RRestAccess->countTeacherLeaveMonth($teacher_id, $timeStart, $timeEnd, 1);
        $leave_month_tmp = $this->RRestAccess->countTeacherLeaveMonth($teacher_id, $timeStart, $timeEnd, 2);
        $leave_month_pause = $this->RRestAccess->countTeacherLeaveMonth($teacher_id, $timeStart, $timeEnd, 3);
        $leave_all = $this->RRestAccess->countTeacherLeaveAll($teacher_id, 1);
        $leave_tmp = $this->RRestAccess->countTeacherLeaveAll($teacher_id, 2);
        $leave_pause = $this->RRestAccess->countTeacherLeaveAll($teacher_id, 3);

        $data = [
            'leave_month_all' => $leave_month_all,
            'leave_month_tmp' => $leave_month_tmp,
            'leave_month_pause' => $leave_month_pause,
            'leave_all' => $leave_all,
            'leave_tmp' => $leave_tmp,
            'leave_pause' => $leave_pause
        ];

        return $data;
    }

    public function editTeacherLeave1($request)
    {
        $userId = Yii::$app->user->id;

        $timeList = array(array('time_start'=>$request['time_start'],'time_end'=>$request['time_end']));

        $timeBit = 562949953421311;

        $leave_bit = BinaryDecimal::decimalToBinary($timeBit, $timeList);

        if($leave_bit == false){
            return json_encode(array('error' => '请假时间段格式错误'));
        }

        $leave_bit = ~$leave_bit;

        $time_start = $request['timeDay'].' '.$request['time_start'];

        $time_end = $request['timeDay'].' '.$request['time_end'];

        $time_start = strtotime($time_start);
        $time_end = strtotime($time_end);
        $timeDay = strtotime($request['timeDay']);
        $timeEnd = strtotime($request['timeEnd']);

        if($time_start >= $time_end)
        {
            if($time_start != $timeDay && $time_end != $timeDay){
                return json_encode(array('error' => '时间段：开始时间不能大于或等于结束时间'));
            }
        }

        if($request['is_check'] == 1)
        {
            $transaction = Yii::$app->db->beginTransaction();
            try {

                while ($timeDay <= $timeEnd)
                {
                    $re = $this->WRestAccess->addTeacherLeave($request['teacher_id'], $request['leaveType'], $timeDay, $time_start, $time_end);

                    if (empty($re))
                    {
                        return json_encode(array('error' => '添加请假失败'));
                    }

                    //修改日课表时间
                    $re = $this->addTeacherTimeTableByLeave($timeDay, $request['teacher_id'], $leave_bit);

                    if (empty($re['re']))
                    {
                        return json_encode(array('error' => '修改日课表时间失败'));
                    }else{
                        //进入错入课表

                        $re = $this->addFailClass($userId, $request['teacher_id'], $timeDay, $re['dayBit']);

                        if (!empty($re))
                        {
                            return $re;
                        }
                   }

                    $timeDay = $timeDay + 86400;
                }

                $transaction->commit();
                return json_encode(array('error' => ''));

            }catch (Exception $e) {
                $transaction->rollBack();
                return json_encode(array('error' => '提交失败'));
            }
        }else {

            $transaction = Yii::$app->db->beginTransaction();

            try{

                $re = $this->WRestAccess->addTeacherLeave($request['teacher_id'], $request['leaveType'], $timeDay, $time_start, $time_end);

                if (empty($re))
                {
                    return json_encode(array('error' => '提交失败'));
                }else{

                    //修改日课表时间
                    $re = $this->addTeacherTimeTableByLeave($timeDay, $request['teacher_id'], $leave_bit);

                    if (empty($re['re']))
                    {
                        return json_encode(array('error' => '修改日课表时间失败'));
                    }else{
                        //进入错误课表

                        $re = $this->addFailClass($userId, $request['teacher_id'], $timeDay, $re['dayBit']);

                        if (!empty($re))
                        {
                            return $re;
                        }
                    }

                    $transaction->commit();
                    return json_encode(array('error' => ''));
                }
            }catch (Exception $e) {
                $transaction->rollBack();
                return json_encode(array('error' => '提交失败'));
            }
        }
    }

    public function addTeacherTimeTableByLeave($timeDay, $teacher_id, $leave_bit)
    {
        $timeBit = 562949953421311;

        $week = date('w', $timeDay);
        $week = $week == 0 ? 7 : $week;
        $fixedTimeRow = $this->RWorktimeAccess->getTeacherFixedTime($teacher_id, $week);

        $fixedTimeRow['time_bit'] = empty($fixedTimeRow['time_bit']) ? 281474976710656 : $fixedTimeRow['time_bit'];

        $num = 1;
        for($i=1; $i<=49; $i++)
        {
            $leave_per = ($leave_bit & $num) == $num ? 1 : 0;
            $fixedTimeRow_per = ($fixedTimeRow['time_bit'] & $num) == $num ? 1 : 0;
            if(($fixedTimeRow_per == 0) && ($leave_per == 0))
            {
                $endBit = pow(2, $i - 1);
                $timeBit = $timeBit & (~$endBit);
            }

            $num = $num << 1;
        }

        $re = $this->WWorktimeAccess->addTeacherDayTime($teacher_id, $timeDay, $timeBit);

        return array('re' => $re, 'dayBit' => $timeBit);

    }

    public function addFailClass($userId, $teacher_id, $timeDay, $timeBit)
    {
        $classInfo = $this->RClassAccess->getDayClassByTeacherId($teacher_id, $timeDay);

        if(!empty($classInfo)) {

            foreach ($classInfo as $class)
            {

                $classBit = BinaryDecimal::getClassBit($class['time_class'], $class['time_end']);

                if (($timeBit & $classBit) > 0) {

                    $is_deleted = $this->WClassAccess->updateClassTimeDelete($class['id']);
                    if (empty($is_deleted))
                    {
                        return '更新is_deleted失败';
                    }

                    $history_id = $this->WClassAccess->updateHistory($userId, 2, $class['history_id']);
                    if (empty($history_id))
                    {
                        return '更新history失败';
                    }

                    $left_id = $this->WClassAccess->addClassTimesByLeftId($class['left_id']);
                    if (empty($left_id))
                    {
                        return '更新left_id失败';
                    }

                    $fail_id = $this->WClassAccess->addClassFail($class['id'], 2);

                    if (empty($fail_id))
                    {
                        return '加入错误课表失败';
                    }
                }
            }

            return 0;
        }

        return 0;
    }

    public function deleteLeave1($request)
    {
        $userId = Yii::$app->user->id;

        $timeDay = strtotime($request['timeDay']);
        $timeEnd = strtotime($request['timeEnd']);

        if($request['is_check'] == 1)
        {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                while($timeDay <= $timeEnd)
                {
                    $re = $this->WRestAccess->deleteLeave($request['teacher_id'], $timeDay);

                    if (empty($re))
                    {
                        return 0;
                    }

                    $week = date('w', $timeDay);
                    $week = $week == 0 ? 7 : $week;

                    $fixedTimeRow = $this->RWorktimeAccess->getTeacherFixedTime($request['teacher_id'], $week);

                    $timeBit = empty($fixedTimeRow['time_bit']) ? 281474976710656 : $fixedTimeRow['time_bit'];

                    $re = $this->WWorktimeAccess->addTeacherDayTime($request['teacher_id'], $timeDay, $timeBit);

                    if (empty($re))
                    {
                        return 0;
                    }else{
                        $re = $this->addFailClass($userId, $request['teacher_id'], $timeDay, $timeBit);

                        if (!empty($re))
                        {
                            return 0;
                        }
                    }

                    $timeDay = $timeDay + 86400;
                }

                $transaction->commit();
                return 1;

            }catch (Exception $e) {
                $transaction->rollBack();
                return 0;
            }
        }else{

            $re = $this->WRestAccess->deleteLeave($request['teacher_id'], $timeDay);

            if (empty($re))
            {
                return 0;
            }else{

                $week = date('w', $timeDay);
                $week = $week == 0 ? 7 : $week;

                $fixedTimeRow = $this->RWorktimeAccess->getTeacherFixedTime($request['teacher_id'], $week);

                $timeBit = empty($fixedTimeRow['time_bit']) ? 281474976710656 : $fixedTimeRow['time_bit'];

                $re = $this->WWorktimeAccess->addTeacherDayTime($request['teacher_id'], $timeDay, $timeBit);

                if (empty($re))
                {
                    return 0;
                }else{

                    $re = $this->addFailClass($userId, $request['teacher_id'], $timeDay, $timeBit);

                    if (!empty($re))
                    {
                        return 0;
                    }

                    return 1;
                }
            }
        }
    }

    /**
     * 获取指定时间段内请假总小时数
     * @param $teacher_id
     * @param $time_start
     * @param $time_end
     */
/*    public function getTeacherLeaveWithinTimeInterval($teacher_id,$time_start,$time_end)
    {
        $leave_list = $this->RRestAccess->getTeacherLeaveInfo($teacher_id,$time_start,$time_end);
        $leave_hour = 0;

        foreach($leave_list as $leave_info)
        {
            if($leave_info['tmp_leave'] == 0 && $leave_info['all_leave'] == 0 && $leave_info['pause'] == 0)
            {
                continue;
            }

            $leave_hour = $leave_hour + ($leave_info['time_end'] - $leave_info['time_start']);
        }

        $leave_hour = $leave_hour / 3600;

        return ['error' => '', 'data' => $leave_hour];
    }*/
}