<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:06
 */

namespace common\sources\read\teacher;

use common\models\music\StatisticsTeacherRest;
use Yii;
Class RestAccess implements IRestAccess {

    public function getRestById($rest_id)
    {
        return StatisticsTeacherRest::find()
            ->select('time_day, time_start, time_end')
            ->where('id = :rest_id',[':rest_id' => $rest_id])
            ->asArray()
            ->one();
    }

    public function getTeacherLeaveInfo($teacher_id, $timeStart, $timeEnd)
    {
        $sql = "SELECT tmp_leave, all_leave, pause, time_day, time_date, time_start, time_end FROM statistics_teacher_rest"
            . " WHERE teacher_id = :teacher_id AND time_day >= :timeStart AND time_day < :timeEnd";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':teacher_id'=>$teacher_id,':timeStart'=>$timeStart,':timeEnd'=>$timeEnd])
            ->queryAll();
    }

    public function getLeaveByTeacherId($teacher_id, $timeDay)
    {
        $sql = "SELECT tmp_leave, all_leave, pause, time_day, time_date, time_start, time_end FROM statistics_teacher_rest"
            . " WHERE teacher_id = :teacher_id AND time_day = :timeDay";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':teacher_id'=>$teacher_id,':timeDay'=>$timeDay])
            ->queryOne();
    }

    public function countTeacherLeaveMonth($teacher_id, $timeStart, $timeEnd, $type)
    {
        $user_teacher =  StatisticsTeacherRest::find()
            ->where('teacher_id = :teacher_id',[':teacher_id'=>$teacher_id])
            ->andWhere('time_day >= :time_start',[':time_start'=>strtotime($timeStart)])
            ->andWhere('time_day < :time_end',[':time_end'=>$timeEnd]);

        if($type == 1)
        {
            return $user_teacher->sum('all_leave');
        }elseif ($type == 2)
        {
            return $user_teacher->sum('tmp_leave');
        }
        else{
            return $user_teacher->sum('pause');
        }
    }

    public function countTeacherLeaveAll($teacher_id, $type)
    {
        $rest = StatisticsTeacherRest::find()
            ->where('teacher_id = :teacher_id',[':teacher_id'=>$teacher_id]);

        if($type == 1)
        {
            return $rest->sum('all_leave');
        }elseif ($type == 2)
        {
            return $rest->sum('tmp_leave');
        }else{
            return $rest->sum('pause');
        }
    }

    public function getLeaveTime($teacher_id, $time_day)
    {
        $sql = "SELECT time_start, time_end FROM statistics_teacher_rest"
            . " WHERE teacher_id = :teacher_id AND time_day = :time_day AND !(tmp_leave = 0 AND all_leave = 0 AND pause = 0)";

        return Yii::$app->db->createCommand($sql)
                    ->bindValues([':teacher_id' => $teacher_id, ':time_day' => $time_day])
                    ->queryOne();
    }

    public function getWeekRest($week, $time_day)
    {
        $sql = "SELECT teacher_id, time_start, time_end FROM statistics_teacher_rest WHERE !(all_leave = 0 AND pause = 0 AND tmp_leave = 0) AND FROM_UNIXTIME(time_day,'%w') = :week AND time_day >= :time_day";

        return Yii::$app->db->createCommand($sql)
                    ->bindValues([
                        ':time_day' => $time_day,
                        ':week' => $week
                    ])->queryAll();
    }
}