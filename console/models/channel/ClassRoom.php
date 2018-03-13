<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 16/9/20
 * Time: 14:20
 */
namespace console\models\channel;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;

class ClassRoom extends ActiveRecord
{
    public static function tableName()
    {
        return 'class_room';
    }
    

    //获取体验人数
    public function getExperienceNum($timeStart,$timeEnd)
    {
        $sql = "SELECT COUNT(student_id) FROM class_room WHERE is_deleted = 0 AND is_ex_class = 1 AND status = 1 AND time_class >= :timeStart AND time_end <= :timeEnd";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':timeStart' => $timeStart,':timeEnd'=>$timeEnd])
            ->queryScalar();
    }

    //获取约课人数/次数
    public function getClassStudent($type =1,$time_start,$time_end,$studentids)
    {
        if($type == 1){
            $sql = "SELECT COUNT(DISTINCT student_id) FROM class_room"
                . " WHERE is_ex_class = 0 AND status = 1 AND time_class > :time_start"
                . " AND time_end < :time_end AND student_id IN (".implode(',',$studentids).")";
        }else{
            $sql = "SELECT COUNT(student_id) FROM class_room"
                . " WHERE is_ex_class = 0 AND status = 1 AND time_class > :time_start"
                . " AND time_end < :time_end AND student_id IN (".implode(',',$studentids).")";
        }

        return Yii::$app->db->createCommand($sql)
            ->bindValues(['time_start'=>$time_start,'time_end'=>$time_end])
            ->queryScalar();
    }

    public function getBuyStudent()
    {
        $sql = " SELECT c.price, c.student_id, u.nick FROM class_edit_history AS c"
            . " LEFT JOIN user as u ON u.id = c.student_id"
            . " LEFT JOIN (SELECT max(time_created) AS time, student_id FROM class_edit_history"
            . " WHERE price > 0 AND is_add = 1 AND is_deleted = 0 AND is_success = 1 GROUP BY student_id) AS t ON t.student_id = c.student_id"
            . " WHERE c.time_created = t.time AND c.price > 0 AND c.is_add = 1 AND c.is_deleted = 0 AND c.is_success = 1";

        return Yii::$app->db->createCommand($sql)->queryAll();
    }

    public function getClassCount($type,$time_start,$time_end)
    {
        $sql = "SELECT ch.student_id, IFNULL(t.count,0) AS count FROM class_edit_history AS ch LEFT JOIN (SELECT COUNT(id) as count, student_id FROM class_room ";

        if($type == 25){
            $sql .= " WHERE (time_end - time_class) = 1500 AND is_ex_class = 0";
        }
        if($type == 45){
            $sql .= " WHERE (time_end - time_class) = 2700 AND is_ex_class = 0";
        }
        if($type == 50){
            $sql .= " WHERE (time_end - time_class) = 3000 AND is_ex_class = 0";
        }
        if($type == 1){
            $sql .= " WHERE is_ex_class = 1";
        }
        $sql .= " AND status = 1 AND is_deleted = 0 AND time_class > :time_start AND time_end < :time_end GROUP BY student_id ) AS t ON t.student_id = ch.student_id WHERE ch.price > 0 AND ch.is_add = 1 AND ch.is_deleted = 0 "
            . " AND ch.is_success = 1 GROUP BY ch.student_id";

        return Yii::$app->db->createCommand($sql)
                ->bindValues([':time_start'=>$time_start,":time_end"=>$time_end])
                ->queryAll();
    }

    public function getExInfo()
    {
        $sql = "SELECT u.id, (u.ex_class_times + IFNULL(c.counts,0)) as ex_times FROM user AS u"
        . " LEFT JOIN (SELECT student_id, COUNT(id) as counts FROM class_room WHERE is_ex_class = 1 AND status != 2 AND status != 3 AND is_deleted = 0 GROUP BY student_id) AS c ON c.student_id = u.id";
        
        return Yii::$app->db->createCommand($sql)->queryAll();
    }
    
    public function updateLeftId($leftId,$classIds)
    {
        $ids = implode(',',$classIds);
        $sql = "UPDATE class_room SET left_id = :left_id WHERE id in ($ids)";
        
        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':left_id' => $leftId,
            ])->execute();
    }

    public function getkefuUnbuy()
    {
        $sql = "SELECT u.nick, u.mobile, k.nickname FROM user AS u"
            . " LEFT JOIN user_account as k ON k.id = u.kefu_id"
            . " LEFT JOIN (SELECT student_id, COUNT(id) as counts FROM class_edit_history WHERE price > 0 AND is_add = 1 AND is_success = 1 AND is_deleted = 0 GROUP BY student_id) AS c ON c.student_id = u.id"
            . " WHERE c.counts IS NULL AND u.is_disabled = 0";
        
        return Yii::$app->db->createCommand($sql)->queryAll();
    }

    public static function getTeacherClassCancelMount($timeStart, $timeEnd)
    {
        $sql = "SELECT c.teacher_id, COUNT(c.id) as mount FROM class_room as c"
            . " LEFT JOIN class_record as r ON c.id = r.class_id"
            . " WHERE (c.`status` = 2 OR c.`status` = 3) AND c.is_teacher_cancel = 0 AND (r.time_created - c.time_class) <= 1800"
            . " AND r.time_created >= :timeStart AND r.time_created < :timeEnd GROUP BY c.teacher_id";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':timeStart' => $timeStart, ':timeEnd' => $timeEnd])
            ->queryAll();
    }

    public static function getTeacherIds($time_start, $place_id)
    {
        $sql = "SELECT COUNT(DISTINCT c.teacher_id) FROM class_room AS c LEFT JOIN user_teacher AS t ON c.teacher_id = t.id"
            . " WHERE c.status = 1 AND c.is_deleted = 0 AND c.time_class >= :time_start AND c.time_class < :time_end"
            . " AND t.place_id = :place_id";

        return Yii::$app->db->createCommand($sql)
                        ->bindValues([
                            ':time_start' => $time_start,
                            ':time_end' => $time_start + 3600,
                            ':place_id' => $place_id
                        ])->queryScalar();
    }

    public static function getClassByLevel($place_id, $timeStart, $music, $level)
    {
        $sql = "SELECT COUNT(c.id) FROM `class_room` AS c"
            . " LEFT JOIN user_teacher_instrument AS i ON i.user_id = c.teacher_id"
            . " LEFT JOIN user_teacher as t ON c.teacher_id = t.id"
            . " WHERE i.type = 1 AND c.instrument_id = :music AND i.`level` = :level AND i.instrument_id = :music"
            . " AND c.time_class >= :timeStart AND c.time_class < :timeEnd AND c.`status` = 1 AND c.is_deleted = 0"
            . " AND t.place_id = :place_id ";

        return Yii::$app->db->createCommand($sql)
                        ->bindValues([
                            ':music' => $music,
                            ':level' => $level,
                            ':timeStart' => $timeStart,
                            ':timeEnd' => $timeStart + 3600,
                            ':place_id' => $place_id
                            ])->queryScalar();
    }
}