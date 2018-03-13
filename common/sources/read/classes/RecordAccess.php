<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/9
 * Time: 下午5:06
 */
namespace common\sources\read\classes;

use common\models\music\ClassRecord;
use Yii;
use yii\db\ActiveRecord;
use common\models\music\ClassRoom;
use common\models\music\ClassEditHistory;
use common\models\music\ClassLeft;
use common\models\music\ClassComment;


Class RecordAccess implements IRecordAccess {
	/**
	 * 获取某用户上课时间
	 * @param  $student_id  int
	 * @return $array
	 */
	public function getBeginsClassTime($student_id)
	{
		return  ClassRoom::find()
					->select('time_class')
					->where('student_id = :student_id', [':student_id' => $student_id])
				    ->andWhere(['is_ex_class' => 1])
				    ->andWhere('status != 2 AND status != 3')
				    ->andWhere(['is_deleted' => 0])
				    ->orderBy('time_class ASC')
				    ->column();
	}

	/**
	 * 获取课程修改记录信息
	 * @param  $student_id  int
	 * @return $array
	 */
	public function getClassEditHistoryInfo($student_id)
	{
		return  ClassEditHistory::find()
					->select('price, amount, type, time_created')
            		->where('student_id = :student_id', [':student_id' => $student_id])
            		->andWhere('price > 0')
            		->andWhere(['is_add' => 1, 'is_success' => 1, 'is_deleted' => 0])
        			->orderBy('time_created ASC')
           			->all();
	}

	/**
	 * 获取课单
	 * @param  $student_id  int
	 * @return $array
	 */
	public function getClassLeftInfo($student_id)
	{
		return  ClassLeft::find()
		            ->select('id, type, left_bit, name, total_amount, amount, ac_amount')
		            ->where(['user_id' => $student_id])
		            ->orderBy('type ASC')
		            ->asArray()
		            ->all();
	}


   	/**
     * 获取自己课程信息
     */
    public function getSelfClassList($studentId, $timeStart, $timeEnd)
    {
        $sql = "SELECT c.id, c.left_id, cf.id as fail_id, c.time_class, c.time_end, c.instrument_id, c.teacher_id, IFNULL(ut.nick,'') as teacher_name, c.marks, c.status, c.is_deleted, c.is_ex_class, c.is_problem, c.problem_marks, CONV(c.status_bit,2,10) AS status_bit, c.course_info, cf.is_deleted as fail_delete FROM class_room AS c"
            . " LEFT JOIN user_teacher AS ut ON ut.id = c.teacher_id"
            . " LEFT JOIN class_fail AS cf ON cf.class_id = c.id"
            . " WHERE (c.is_deleted = 0 OR (c.is_deleted = 1 AND cf.is_deleted = 0 AND cf.id IS NOT NULL)) AND c.student_id = :student_id"
            . (empty($timeStart) ? '' : " AND c.time_class >= $timeStart AND c.time_class < $timeEnd")
            . " GROUP BY c.id"
            . " ORDER BY c.time_class ASC";

        return Yii::$app->db->createCommand($sql)->bindValue(':student_id', $studentId)
            ->queryAll();
    }

    public function getRecordId($classId)
    {
        return ClassRecord::find()
            ->select('id')
            ->where(['class_id' => $classId])
            ->scalar();
    }

    /**
     * 获取课程老师评价列表数量
     * @param  $all
     * @param  $time
     * @return int
     */
    public  function  getTeacherEvaluateCount($all,$time)
    {
    	$query = ClassComment::find()
		            ->select('class_comment.*,user.nick as userName,user.mobile as userMobile,user_teacher.nick as teacherName')
		            ->leftJoin('user','`class_comment`.`student_id` =`user`.`id`')
		            ->leftJoin('user_teacher','`class_comment`.`teacher_id` =`user_teacher`.`id`');

		if($all==0){
            $query->andWhere("status < 5");
        }

        if($time>0){
            $query->andWhere("class_comment.time_created > ".$time);
            $query->andWhere("class_comment.time_created < ".($time+86400));
        }

        return  $query->count();
    }

    /**
     * 获取课程老师评价列表
     * @param  $all
     * @param  $time
     * @return int
     */
    public  function  getTeacherEvaluateList($all, $time, $page)
    {
        $query = ClassComment::find()
            ->select('class_comment.*,user.nick as userName,user.mobile as userMobile,user_teacher.nick as teacherName')
            ->leftJoin('user','`class_comment`.`student_id` =`user`.`id`')
            ->leftJoin('user_teacher','`class_comment`.`teacher_id` =`user_teacher`.`id`');

        if($all==0){
            $query->andWhere("status < 5");
        }

        if($time>0){
            $query->andWhere("class_comment.time_created > ".$time);
            $query->andWhere("class_comment.time_created < ".($time+86400));
        }

        return $query->orderBy('id')
		            ->offset(($page-1)*8)
		            ->limit(8)
		            ->asArray()
		            ->all();
    }

}