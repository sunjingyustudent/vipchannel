<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/13
 * Time: 上午11:08
 */
namespace common\sources\write\teacher;

use Yii;
use common\models\music\UserTeacherSchool;
use common\models\music\UserSchoolInstrument;


Class TrainAccess implements ITrainAccess
{
    /**
     * @param $trace_id int
     * @param $type int 3删除 2废弃
     * @return string
     */
    public function updateTrace($trace_id, $type)
    {
        //删除
        if ($type == 5) {
            $sql1 = "DELETE FROM user_school_instrument WHERE user_id = :id";

            Yii::$app->db->createCommand($sql1)
                ->bindValue(':id', $trace_id)
                ->execute();

            $sql2 = "UPDATE user_teacher_school SET status = 5, is_deleted  = 1, class_id = 0, time_overed = :time_updated WHERE id = :id";

            return Yii::$app->db->createCommand($sql2)
                ->bindValue(':id', $trace_id)
                ->bindValue(':time_updated', time())
                ->execute();
        }
        //废弃
        if ($type == 2 ) {

            $sql2 = "UPDATE user_teacher_school SET status  = 2, class_id = 0, time_overed = :time_updated WHERE id = :id";

            return Yii::$app->db->createCommand($sql2)
                ->bindValue(':id', $trace_id)
                ->bindValue(':time_updated', time())
                ->execute();
        }
        //通过
        if ($type == 1 ) {

            $sql2 = "UPDATE user_teacher_school SET status  = 1, time_passed = :time_updated WHERE id = :id";

            return Yii::$app->db->createCommand($sql2)
                ->bindValue(':id', $trace_id)
                ->bindValue(':time_updated', time())
                ->execute();
        }
        //培训不通过
        if ($type == 4 ) {

            $sql2 = "UPDATE user_teacher_school SET status  = 4, time_overed = :time_updated WHERE id = :id";

            return Yii::$app->db->createCommand($sql2)
                ->bindValue(':id', $trace_id)
                ->bindValue(':time_updated', time())
                ->execute();
        }
        //培训通过
        if ($type == 6) {

            $sql2 = "UPDATE user_teacher_school SET status = 5, is_deleted  = 0, class_id = 0, time_passed = :time_updated WHERE id = :id";

            return Yii::$app->db->createCommand($sql2)
                ->bindValue(':id', $trace_id)
                ->bindValue(':time_updated', time())
                ->execute();
        }
        //审核不通过
        if ($type == 3 ) {

            $sql2 = "UPDATE user_teacher_school SET status  = 3, time_overed = :time_updated WHERE id = :id";

            return Yii::$app->db->createCommand($sql2)
                ->bindValue(':id', $trace_id)
                ->bindValue(':time_updated', time())
                ->execute();
        }
    }

    /**
     * @param $trace_id
     * @param $type
     * @param $listen_score
     * @param $line_score
     * @param $age_score
     * @param $num_score
     * @param $skill_score
     * @param $type_score
     * @param $opern_score
     * @return string
     */
    public function updateTraceByScore($trace_id, $status,$listen_score, $line_score, $age_score, $num_score, $skill_score,$type, $opern, $opern_score, $command, $command_score)
    {
        //废弃
        if ($status == 2 ) {
            $sql = "UPDATE user_teacher_school SET status  = 2, time_overed =:time_updated, listen_score = :listen_score, line_score = :line_score, age_score = :age_score, num_score = :num_score, skill_score = :skill_score, type = :type, opern = :opern, opern_score = :opern_score , command = :command, command_score = :command_score WHERE id = :id";
        }
        //通过
        if ($status == 1 ) {
            $sql = "UPDATE user_teacher_school SET status  = 1, time_passed =:time_updated, listen_score = :listen_score, line_score = :line_score, age_score = :age_score, num_score = :num_score, skill_score = :skill_score ,type = :type, opern = :opern, opern_score = :opern_score, command = :command, command_score = :command_score WHERE id = :id";
        }
        if ($status == 3 || $status == 4){
            $sql = "UPDATE user_teacher_school SET status  = ".$status.", time_overed =:time_updated, listen_score = :listen_score, line_score = :line_score, age_score = :age_score, num_score = :num_score, skill_score = :skill_score ,type = :type, opern = :opern, opern_score = :opern_score, command = :command, command_score = :command_score WHERE id = :id";
        }


        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':id' => $trace_id,
                ':time_updated' => time(),
                ':listen_score' => $listen_score,
                ':line_score' => $line_score,
                ':age_score' => $age_score,
                ':num_score' => $num_score,
                ':skill_score' => $skill_score,
                ':type' => $type,
                ':opern' => $opern,
                ':opern_score' => $opern_score,
                ':command' => $command,
                ':command_score' => $command_score
            ])->execute();
    }


    public function addClass($name, $time_start, $time_end)
    {
        $sql = "INSERT INTO user_teacher_class(name, time_start, time_end, time_created) VALUES"
            . " (:name, :time_start, :time_end, :time_created)";

        Yii::$app->db->createCommand($sql)
                        ->bindValues([
                            ':name' => $name,
                            ':time_start' => $time_start,
                            ':time_end' => $time_end,
                            ':time_created' => time()
                        ])->execute();

        return Yii::$app->db->getLastInsertID();
    }

    public function updateUserTeacherClassId($user_id, $class_id)
    {
        $sql = "UPDATE user_teacher_school SET class_id = :class_id, time_updated = :time WHERE id = :user_id";

        return Yii::$app->db->createCommand($sql)
                    ->bindValues([
                        ':class_id' => $class_id,
                        ':time' => time(),
                        ':user_id' => $user_id,
                    ])->execute();
    }

    /**
     * 修改
     * @param $trace_id
     * @param $name
     * @param $mobile
     * @param $school
     * @param $major
     * @param $grade
     * @param $hsPad
     * @return string
     */
    public function editTrace($trace_id, $name, $mobile, $school, $major, $grade, $hasPad)
    {
        $sql = "UPDATE user_teacher_school SET name= :name, mobile = :mobile, school = :school, major = :major, 
grade = :grade, hasPad = :hasPad, time_updated = :time_updated WHERE id=:id";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':id'       => $trace_id,
                ':name'        => $name,
                ':mobile'       => $mobile,
                ':school'             => $school,
                ':major'    => $major,
                ':grade'    => $grade,
                ':hasPad'    => $hasPad,
                ':time_updated' =>  time()
            ])
            ->execute();

    }

    public function editClassById($cid, $name, $time_start, $time_end)
    {
        $sql = "UPDATE user_teacher_class SET name = :name, time_start = :time_start, time_end = :time_end,"
            . " time_updated = :time_updated WHERE id = :cid";

        return Yii::$app->db->createCommand($sql)
                        ->bindValues([
                            ':name' => $name,
                            ':time_start' => $time_start,
                            ':time_end' => $time_end,
                            ':time_updated' => time(),
                            ':cid' => $cid
                        ])->execute();
    }

    public function updateAllClassIdByClassId($class_id)
    {
        $sql = "UPDATE user_teacher_school SET class_id = 0 WHERE class_id = :cid";

        return Yii::$app->db->createCommand($sql)
                        ->bindValue('cid', $class_id)
                        ->execute();
    }

    public function deleteClass($class_id)
    {
        $sql = "UPDATE user_teacher_class SET is_deleted = 1 WHERE id = :cid";

        return Yii::$app->db->createCommand($sql)
                    ->bindValue('cid', $class_id)
                    ->execute();
    }

    /**
     * @param $user_id int
     * @param $instrument_id int
     * @param $level
     * @return  int
     */
    public function addSchoolInstrument($user_id, $instrument_id, $grade, $level )
    {

        $sql = "INSERT INTO user_school_instrument(user_id, instrument_id, grade, level) VALUES(:user_id, :instrument, :grade, :level)"
            . " ON DUPLICATE KEY UPDATE instrument_id = :instrument, grade = :grade, level = :level";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':user_id' => $user_id, ':instrument' => $instrument_id, ':grade' => $grade, ':level' => $level])
            ->execute();
    }


    /**
     * @param  $user_id int
     *
     * @return int
     */
    public function deleteSchoolInstrument($user_id)
    {
        $sql = "DELETE FROM user_school_instrument WHERE user_id = :user_id";

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':user_id',$user_id)
            ->execute();
    }

    public function editTraceNew($trace_id, $name, $mobile, $school, $major, $grade, $hasPad, $type, $opern, $opern_score, $command, $command_score, $listen_score, $line_score, $age_score, $num_score, $skill_score)
    {
        $sql = "UPDATE user_teacher_school SET name= :name, mobile = :mobile, school = :school, major = :major,"
            . " grade = :grade, hasPad = :hasPad, type = :type, opern = :opern, opern_score = :opern_score, command = :command,"
            . " command_score = :command_score, listen_score = :listen_score, line_score = :line_score, age_score = :age_score, num_score = :num_score,"
            . " skill_score = :skill_score, time_updated = :time_updated WHERE id=:id";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':id' => $trace_id,
                ':name' => $name,
                ':mobile' => $mobile,
                ':school' => $school,
                ':major' => $major,
                ':grade' => $grade,
                ':hasPad' => $hasPad,
                ':time_updated' => time(),
                ':type' => $type,
                ':opern' => $opern,
                ':opern_score' => $opern_score,
                ':command' => $command,
                ':command_score' => $command_score,
                ':listen_score' => $listen_score,
                ':line_score' => $line_score,
                ':age_score' => $line_score,
                ':num_score' => $num_score,
                ':skill_score' => $num_score
            ])->execute();
    }

    public function allotClass($uid, $class_id)
    {
        $sql = "UPDATE user_teacher_school SET class_id = :class_id WHERE id = :uid";

        return Yii::$app->db->createCommand($sql)
                ->bindValues([':class_id' => $class_id, ':uid' => $uid])
                ->execute();
    }

    public function addSchool($name, $time_created, $time_updated)
    {
        $sql = "INSERT INTO school(name, time_created, time_updated) VALUES"
            . " (:name, :time_created, :time_updated)";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':name' => $name,
                ':time_created' => $time_created,
                ':time_updated' => $time_updated
            ])->execute();
    }

    public function editSchoolById($cid, $name ,$time_updated)
    {
        $sql = "UPDATE school SET name = :name, time_updated = :time_updated "
            . " WHERE id = :cid";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':name' => $name,
                ':time_updated' => $time_updated,
                ':cid' => $cid
            ])->execute();
    }

}