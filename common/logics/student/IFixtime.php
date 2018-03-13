<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/22
 * Time: 上午9:55
 */
namespace common\logics\student;

use Yii;
use yii\base\Object;

interface IFixtime {
    /**
     * 安排固定课程
     * @param   $student_id  int
     * @return  array
     */
    public  function  getStudentFixTime($student_id);

    /**
     * 安排课程提交
     * @param   $openId   str
     * @param   $fixInfo  array
     * @return  mixed  
     */
   	public function getDoStudentFixTime($openId, $fixInfo='', $logid);


    /**
     * 获取学生bit位置
     * @param  $timeHead
     * @param  $timeFoot
     * @param  $classType
     * @return int
     */
    public function getStudentFixTimeBit($timeHead,$timeFoot,$classType);

    /**
     * @param $open_id
     * @param $week
     * @param $hour
     * @param $min
     * @param $class_type
     * @return mixed
     * @author xl
     * 获取学生固定时间可以排课的老师列表
     */
    public function getAvFixTeacherList($open_id, $week, $hour, $min, $class_type, $instrument_type, $filter_name);
}