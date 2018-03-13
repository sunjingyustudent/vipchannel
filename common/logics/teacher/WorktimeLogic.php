<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午3:18
 */
namespace common\logics\teacher;

use common\compute\SalaryCompute;
use Yii;
use yii\base\Object;
use common\widgets\BinaryDecimal;

class WorktimeLogic extends Object implements IWorktime
{
    /** @var  \common\sources\read\teacher\WorktimeAccess $RWorktimeAccess */
    private $RWorktimeAccess;
    /** @var  \common\sources\write\teacher\WorktimeAccess $WWorktimeAccess */
    private $WWorktimeAccess;
    /** @var  \common\sources\read\classes\ClassAccess $RClassAccess */
    private $RClassAccess;
    /** @var  \common\sources\write\classes\ClassAccess $WClassAccess */
    private $WClassAccess;
    /** @var  \common\sources\read\salary\BasepayAccess $RBasepayAccess */
    private $RBasepayAccess;
    /** @var  \common\compute\SalaryCompute $salaryCompute */
    private $salaryCompute;


    public function init()
    {
        $this->RWorktimeAccess = Yii::$container->get('RWorktimeAccess');
        $this->WWorktimeAccess = Yii::$container->get('WWorktimeAccess');
        $this->RClassAccess = Yii::$container->get('RClassAccess');
        $this->WClassAccess = Yii::$container->get('WClassAccess');
        $this->RBasepayAccess = Yii::$container->get('RBasepayAccess');
        $this->salaryCompute = Yii::$container->get('salaryCompute');

        parent::init();
    }


    public function teacherTimeDate($week, $teacher_id)
    {
        $time = $week == 0 ? time() : time() + 86400*7;
        $week = date('w',$time);
        $timeStart = $week == 1 ? strtotime(date('Y-m-d',$time)) : strtotime('-1 Mon',$time);

        $data = array();

        for($i = 1; $i < 8; $i ++) {

            $weekInfo = [];
            $timeBitClass = 0;
            $timeDay = $timeStart + ($i-1) * 86400;
            $weekDay = date('w', $timeDay);
            $weekDay = $weekDay == 0 ? 7 : $weekDay;

            $fixedTimeBit = $this->salaryCompute->getTeacherFixTimeByWeek($teacher_id, $timeDay)['data'];

            $fixedTimeBit = empty($fixedTimeBit) ? 281474976710656 : $fixedTimeBit;
            $timeBitClassList = $this->RWorktimeAccess->getTeacherClassFixTime($teacher_id, $i);

            if(!empty($timeBitClassList)) {
                foreach($timeBitClassList as $class) {
                    $timeBitClass = $timeBitClass | $class;
                }
            }

            for($m = 0; $m < 48 ; $m ++) {
                $statusInfo = array('status' => 0, 'name' => '');
                $num = pow(2,$m);
                if(($fixedTimeBit & $num) == $num) {
                    $statusInfo['status'] = ($timeBitClass & $num) == $num ? 3 : 1;
                    if ($statusInfo['status'] == 3) {
                        $statusInfo['name'] = $this->RWorktimeAccess->getFixedTimeStudentName($teacher_id, $weekDay, $num);
                    }
                }else {
                    $statusInfo['status'] = ($timeBitClass & $num) == $num ? 2 : 0;
                    if($statusInfo['status'] == 2) {
                        $statusInfo['name'] = $this->RWorktimeAccess->getFixedTimeStudentName($teacher_id, $weekDay, $num);
                    }

                }

                $weekInfo[] = $statusInfo;
            }

            $data[] = $weekInfo;
        }

        return $data;
    }

    public function getTeacherFixTime($teacher_id)
    {
        $time_day = strtotime(date('Y-m-d', time()));

        $fixedTimeBitList = $this->RWorktimeAccess->getTeacherFixTimeAll($teacher_id, $time_day);
        $executeTime = $this->RWorktimeAccess->getTeacherFixedTimeExecuteTime($teacher_id, $time_day);

        $weekModel = [1,2,3,4,5,6,7];
        $data = [];
        $data['time_execute'] = empty($executeTime) ? '' : $executeTime;

        foreach($weekModel as $value)
        {
            $each = [];
            $flag = false;

            if(!empty($fixedTimeBitList))
            {
                foreach($fixedTimeBitList as $row) {
                    if($row['week'] == $value) {
                        $each['week'] = $row['week'];
                        $each['time_bit'] = $row['time_bit'];
                        $data['bit_info'][] = $each;
                        $flag = true;
                    }
                }
            }

            if(!$flag) {
                $each['week'] = $value;
                $each['time_bit'] = 281474976710656;
                $data['bit_info'][] = $each;
            }
        }

        foreach($data['bit_info'] as $key =>$item)
        {
            $data['bit_info'][$key]['time_list'] = BinaryDecimal::binaryToDecimal($item['time_bit']);

            $long = BinaryDecimal::getFixLong($item['time_bit']);

            $hour_fee = $this->salaryCompute->computeHourFee($teacher_id, $time_day)['data'];

            $data['bit_info'][$key]['money'] = round(($long / 2) * $hour_fee, 2);
        }

       return $data;
    }

    public function addTeacherFixTime($request)
    {
        $userId = Yii::$app->user->id;

        $role = Yii::$app->user->identity->role;

        $teacherId = $request['teacher_id'];
        $timeExecute = strtotime($request['time']);
        $timeList = $request['fix_info'];

        $nextExecute = $this->RWorktimeAccess->getNextTimeExecute($teacherId, $timeExecute);

        $nextExecute = empty($nextExecute) ? 0 : $nextExecute;

        $transaction = Yii::$app->db->beginTransaction();
        try {

            foreach ($timeList as $key => $row)
            {
                $timeBit = 562949953421311;

                foreach ($row as $item)
                {
                    $week = explode("_",$item['week'])[1];
                    $startArr = explode(':', $item['time_start']);
                    $endArr = explode(':', $item['time_end']);

                    $re = $this->checkFixTime($startArr, $endArr);

                    if ($re == 1)
                    {
                        return '时间格式错误';
                    }else{
                        $startPos = $startArr[1] == '30'
                            ? $startArr[0] * 2 + 2
                            : $startArr[0] * 2 + 1;

                        $endPos = $endArr[1] == '30'
                            ? $endArr[0] * 2 + 1
                            : $endArr[0] * 2;

                        for ($i = $endPos; $i >= $startPos; $i--) {
                            $endBit = pow(2, $i - 1);
                            $timeBit = $timeBit & (~$endBit);
                        }
                    }

                }

                $re = $this->WWorktimeAccess->addTeacherFixedTime($teacherId, $week, $timeBit, $timeExecute);

                if (empty($re))
                {
                    return '添加teacher_info失败';
                }

                $add_log = $this->WWorktimeAccess->addTeacherFixedTimeLog($teacherId, $week, $timeBit, $timeExecute);

                if (empty($add_log))
                {
                    return '添加teacher_info_log失败';
                }

                $classInfo = $this->RClassAccess->getWeekClassByTeacherId($teacherId, $week, $timeExecute, $nextExecute);

                if (!empty($classInfo))
                {
                    foreach ($classInfo as $class)
                    {
                        $classBit = BinaryDecimal::getClassBit($class['time_class'], $class['time_end']);

                        if (($timeBit & $classBit) > 0)
                        {
                            $is_updated = $this->WClassAccess->updateClassTeacherId($class['id']);

                            if (empty($is_updated))
                            {
                                return '更新老师ID失败';
                            }

                            $fail_id = $this->WClassAccess->intoFailLog($class['id'], $class['student_id'], $class['teacher_id'], $userId, $role, 1);

                            if (empty($fail_id))
                            {
                                return '插入错误课日志失败';
                            }
                        }
                    }
                }
            }

            $transaction->commit();

            return '';
        } catch (Exception $e) {

            $transaction->rollBack();
            return '执行异常';
        }

    }

    private function checkFixTime($startArr, $endArr)
    {

        if($startArr[1] != "00" && $startArr[1] != "30"){
            return 1;
        }

        if($endArr[1] != '00' && $endArr[1] != '30'){
            return 1;
        }

        if($startArr[0] > $endArr[0]){
            return 1;
        }

        if(($startArr[0] == $endArr[0]) && ($startArr[1] > $endArr[1])){
            return 1;
        }

        if(($startArr[0] == $endArr[0]) && ($startArr[1] == $endArr[1]) && (($startArr[1] != '00') || ($startArr[0] != '00'))){
            return 1;
        }

        return 0;
    }

    public function getTeacherTime($teacher_id, $timeDay)
    {
        $week = date('w', $timeDay);
        $week = $week == 0 ? 7 : $week;

        $dayTimeBit = $this->RWorktimeAccess->getTeacherDayTime($teacher_id, $timeDay);

        $fixedTimeRow = $this->RWorktimeAccess->getTeacherFixedTime($teacher_id, $week);

        if(!empty($fixedTimeRow) && $fixedTimeRow['time_execute'] <= $timeDay) {
            $dayTimeBit = empty($dayTimeBit) ? $fixedTimeRow['time_bit'] : $dayTimeBit;
        }else {
            $dayTimeBit = empty($dayTimeBit) ? 281474976710656 : $dayTimeBit;
        }

        $timeList = BinaryDecimal::binaryToDecimal($dayTimeBit);

        return $timeList;
    }

    /**
     * @param $request
     * @return int
     * @author xl
     * 添加周课表（有日课表）
     */
    public function addFixedTime($request)
    {
        $userId = Yii::$app->user->id;
        $role = 2;
        $teacherId = $request['teacher_id'];
        $timeExecute = strtotime($request['time']);
        $timeList = $request['fix_info'];

        $transaction = Yii::$app->db->beginTransaction();
        try {

            foreach ($timeList as $key => $row)
            {
                $timeBit = 562949953421311;

                foreach ($row as $item) {

                    $week = explode("_",$item['week'])[1];
                    $startArr = explode(':', $item['time_start']);
                    $endArr = explode(':', $item['time_end']);

                    $re = $this->checkFixTime($startArr, $endArr);

                    if ($re == 1)
                    {
                        return '时间格式错误';
                    }else{
                        $startPos = $startArr[1] == '30'
                            ? $startArr[0] * 2 + 2
                            : $startArr[0] * 2 + 1;

                        $endPos = $endArr[1] == '30'
                            ? $endArr[0] * 2 + 1
                            : $endArr[0] * 2;

                        for ($i = $endPos; $i >= $startPos; $i--) {
                            $endBit = pow(2, $i - 1);
                            $timeBit = $timeBit & (~$endBit);
                        }
                    }
                }

                $re = $this->WWorktimeAccess->addTeacherFixedTime($teacherId, $week, $timeBit, $timeExecute);

                if (empty($re))
                {
                    return '添加teacher_info失败';
                }

                $classInfo = $this->RClassAccess->getWeekClassByTeacherId($teacherId, $week, $timeExecute);

                if (!empty($classInfo))
                {
                    foreach ($classInfo as $class)
                    {
                        $classBit = 0;

                        $timeDay = strtotime(date('Y-m-d', $class['time_class']));

                        $classBit = BinaryDecimal::getClassBit($class['time_class'], $class['time_end']);

                        $dayTimeBit = $this->RWorktimeAccess->getTeacherDayTime($teacherId, $timeDay);

                        $timeBit = empty($dayTimeBit) ? $timeBit : $dayTimeBit;

                        if (($timeBit & $classBit) > 0) {

                            $delete_id= $this->WClassAccess->updateClassTimeDelete($class['id']);

                            if (empty($delete_id))
                            {
                                return '更新is_deleted失败';
                            }

                            $history_id = $this->WClassAccess->updateHistory($userId, $role, $class['history_id']);
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
                }
            }

            $transaction->commit();

            return '';
        } catch (Exception $e) {

            $transaction->rollBack();
            return '执行异常';
        }
    }

    /**
     * @param $request
     * @return int
     * @author xl
     * 老师日课表添加
     */
    public function addTeacherTime($request)
    {
        $userId = Yii::$app->user->id;
        $role = 2;
        $teacher_id = $request['teacher_id'];
        $time = $request['time'];
        $timeDay = strtotime($time);

        if(isset($request['fix_info'])){
            $timeList = $request['fix_info'];
        }else{
            $timeList = array();
        }

        $timeBit = 562949953421311;

        $timeBit = BinaryDecimal::decimalToBinary($timeBit, $timeList);

        if($timeBit == false){
            return '时间格式错误';
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {

            $re = $this->WWorktimeAccess->addTeacherDayTime($teacher_id, $timeDay, $timeBit);

            if (empty($re))
            {
                return '添加time_table失败';
            }

            $classInfo = $this->RClassAccess->getDayClassByTeacherId($teacher_id, $timeDay);

            if(!empty($classInfo)) {
                foreach($classInfo as $class)
                {
                    $classBit = 0;
                    $classBit = BinaryDecimal::getClassBit($class['time_class'], $class['time_end']);

                    if(($timeBit & $classBit) > 0)
                    {
                        $delete_id= $this->WClassAccess->updateClassTimeDelete($class['id']);

                        if (empty($delete_id))
                        {
                            return '更新is_deleted失败';
                        }

                        $history_id = $this->WClassAccess->updateHistory($userId, $role, $class['history_id']);
                        if (empty($history_id))
                        {
                            return '更新history失败';
                        }

                        $left_id = $this->WClassAccess->addClassTimesByLeftId($class['left_id']);
                        if (empty($left_id))
                        {
                            return '更新left失败';
                        }

                        $fail_id = $this->WClassAccess->addClassFail($class['id'], 2);

                        if (empty($fail_id))
                        {
                            return '更新fail失败';
                        }
                    }
                }
            }

            $transaction->commit();

            return '';

        } catch(Exception $e) {
            $transaction->rollBack();

            return '执行异常';
        }
    }

    public function teacherFixTimeRecord($teacher_id)
    {
        $list = $this->RWorktimeAccess->teacherFixTimeRecord($teacher_id);

        $list_new = array();

        foreach ($list as $key => $item)
        {
            $time_list = BinaryDecimal::binaryToDecimal($item['time_bit']);

            $tmp = "";

            foreach ($time_list as $time)
            {
                $tmp .= $time['start'] . '-' . $time['end'] . '</br>';
            }

            $list_new[$item['time_execute']][$item['week']] = $tmp;
        }

        return array('error' => 0, 'data' => $list_new);
    }
}