<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:06
 */

namespace common\sources\read\salary;

use common\models\music\RewardRecord;
use Yii;

Class PunishmentAccess implements IPunishmentAccess {

    public function getRewardRestCount($timeStart, $timeEnd, $filter, $type)
    {
        $sql = "SELECT count(s.id) FROM statistics_teacher_rest as s"
            . " LEFT JOIN user_teacher as u ON u.id = s.teacher_id"
            . " WHERE !(s.tmp_leave = 0 AND s.all_leave = 0 AND s.pause = 0) AND s.time_day >= :timeStart AND s.time_day < :timeEnd"
            . (empty($filter) ? "" : " AND (u.mobile like '%{$filter}%' OR u.nick like '%{$filter}%')")
            . (empty($type) ? " AND s.tag = 0" : " AND s.tag >= 1");

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':timeStart' => $timeStart, ':timeEnd' => $timeEnd])
            ->queryScalar();
    }

    public function getRewardRestInfo($timeStart, $timeEnd, $filter, $type, $page_num)
    {
        $sql = "SELECT s.id, s.teacher_id, rr.money, rr.reward_id, rr.prefix, u.mobile, u.nick, u.head_icon, b.name as place_name, s.time_day, s.time_start, s.time_end, s.tmp_leave, s.all_leave, s.pause FROM statistics_teacher_rest as s"
            . " LEFT JOIN user_teacher as u ON u.id = s.teacher_id"
            . " LEFT JOIN base_place as b ON b.id = u.place_id"
            . " LEFT JOIN reward_record as rr ON rr.id = s.tag"
            . " WHERE !(s.tmp_leave = 0 AND s.all_leave = 0 AND s.pause = 0) AND s.time_day >= :timeStart AND s.time_day < :timeEnd"
            . (empty($filter) ? "" : " AND (u.mobile like '%{$filter}%' OR u.nick like '%{$filter}%')")
            . (empty($type) ? " AND s.tag = 0" : " AND s.tag >= 1")
            . " limit ".(($page_num - 1) * 10). ", 10";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':timeStart' => $timeStart, ':timeEnd' => $timeEnd])
            ->queryAll();
    }

    public function getTeacherPunishmentTotal($teacher_id, $timeStart, $timeEnd)
    {
        $sql = "SELECT ifnull(sum(money),0) as punishment, teacher_id from reward_record WHERE prefix = 0 AND  teacher_id = :teacher_id AND time_created >= :timeStart AND time_created < :timeEnd";

        return Yii::$app->db->createCommand($sql)
                    ->bindValues([
                        ':teacher_id' => $teacher_id,
                        ':timeStart' => $timeStart,
                        ':timeEnd' => $timeEnd
                    ])->queryOne();
    }

    public function getTeacherPunishmentList($teacher_id, $timeStart, $timeEnd)
    {
        return RewardRecord::find()
            ->select('text, money, time_created')
            ->where(['teacher_id' => $teacher_id, 'prefix' => 0])
            ->andWhere('time_created >= :timeStart',[':timeStart' => $timeStart])
            ->andWhere('time_created < :timeEnd',[':timeEnd' => $timeEnd])
            ->asArray()
            ->all();
    }

    public function getPunishmentTotal($timeStart, $timeEnd)
    {
        return RewardRecord::find()
            ->select('sum(money)')
            ->where(['prefix' => 0])
            ->andWhere('time_created >= :timeStart',[':timeStart' => $timeStart])
            ->andWhere('time_created < :timeEnd',[':timeEnd' => $timeEnd])
            ->scalar();
    }

    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $filter
     * @param $type
     * @return mixed
     * @author sjy
     * 获取缺勤列表
     */
    public function getAbsenceList($timeStart,$timeEnd,$filter,$type,$page_num)
    {
        if($type==0)
        {
            $sql = "SELECT t.id, t.nick, t.mobile, t.absence_punished_rates FROM user_teacher AS t "
                . " LEFT JOIN teacher_attendance AS a ON t.id = a.teacher_id"
                . " WHERE a.time >= :timeStart AND a.time < :timeEnd AND a.is_attendance = 0 AND a.tag = 0"
                . " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%') LIMIT ".(($page_num-1)*10).",10";

        }else{

            $sql = "SELECT t.id, t.nick, t.mobile, t.absence_punished_rates, r.money FROM user_teacher AS t "
                . " LEFT JOIN teacher_attendance AS a ON t.id = a.teacher_id"
                . " LEFT JOIN (SELECT teacher_id, money FROM reward_record WHERE type = 9 AND month_time = :timeStart) AS r ON r.teacher_id = t.id"
                . " WHERE a.time >= :timeStart AND a.time < :timeEnd AND a.is_attendance = 0 AND a.tag = 1"
                . " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%') LIMIT ".(($page_num-1)*10).",10";
        }

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':timeStart' => $timeStart,
                ':timeEnd' => $timeEnd
            ])->queryAll();


    }
    
    
    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $filter
     * @param $type
     * @return mixed
     * @author sjy
     * 获取缺勤条数
     */
    public function getAbsenceCount($timeStart, $timeEnd,$filter,$type)
    {
        if($type == 0){

            $sql = "SELECT t.id FROM user_teacher AS t"
                . " LEFT JOIN teacher_attendance AS a ON t.id = a.teacher_id"
                . " WHERE a.time >= :timeStart AND a.time < :timeEnd AND a.is_attendance = 0 AND a.tag = 0"
                . " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')";

        }else{

            $sql = "SELECT t.id FROM user_teacher AS t"
                . " LEFT JOIN teacher_attendance AS a ON t.id = a.teacher_id"
                . " WHERE a.time >= :timeStart AND a.time < :timeEnd AND a.is_attendance = 0 AND a.tag = 1"
                . " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')";
        }

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':timeStart' => $timeStart,
                ':timeEnd' => $timeEnd
            ])->queryColumn();
    }

    public function getBadEvaluationCount($timeStart, $timeEnd, $filter, $type)
    {
        $sql1 = "SELECT teacher_id FROM reward_record WHERE month_time = :timeStart AND type = 14";

        $teacherId_list = Yii::$app->db->createCommand($sql1)
            ->bindValue(':timeStart',$timeStart)
            ->queryColumn();

        if($type == 0){

            $sql = "SELECT count(DISTINCT c.teacher_id) FROM class_room as c"
                . " LEFT JOIN class_record as r ON r.class_id = c.id "
                . " LEFT JOIN user_teacher as t ON t.id = c.teacher_id "
                . " WHERE c.time_class >= :timeStart AND c.time_class < :timeEnd AND c.`status` < 2 AND c.is_deleted = 0 AND (r.teacher_grade = 2 OR r.teacher_grade = 3)"
                . (empty($filter) ? "" : " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')")
                . (empty($teacherId_list) ? "" : " AND c.teacher_id NOT IN(".implode(',',$teacherId_list).")");

        }else{

            $sql = "SELECT count(DISTINCT c.teacher_id) FROM class_room as c"
                . " LEFT JOIN class_record as r ON r.class_id = c.id "
                . " LEFT JOIN user_teacher as t ON t.id = c.teacher_id "
                . " WHERE c.time_class >= :timeStart AND c.time_class < :timeEnd AND c.`status` < 2 AND c.is_deleted = 0 AND (r.teacher_grade = 2 OR r.teacher_grade = 3)"
                . (empty($filter) ? "" : " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')")
                . (empty($teacherId_list) ? " AND c.teacher_id = -1" : " AND c.teacher_id IN(".implode(',',$teacherId_list).")");
        }

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':timeStart' => $timeStart,
                ':timeEnd' => $timeEnd
            ])->queryScalar();
    }

    public function getBadEvaluationList($timeStart, $timeEnd, $filter, $type, $page_num)
    {
        $sql1 = "SELECT teacher_id FROM reward_record WHERE month_time = :timeStart AND type = 14";

        $teacherId_list = Yii::$app->db->createCommand($sql1)
            ->bindValue(':timeStart',$timeStart)
            ->queryColumn();

        if($type == 0){

            $sql = "SELECT ROUND(a.bad/a.num*100,2) AS rate, t.id AS teacher_id, t.nick, t.mobile FROM user_teacher AS t"
                . " LEFT JOIN (SELECT COUNT(case when r.teacher_grade = 2 OR r.teacher_grade = 3 then r.teacher_grade end) as bad, count(c.id) as num , c.teacher_id  FROM class_room as c LEFT JOIN class_record as r ON r.class_id = c.id"
                . " WHERE c.status < 2 AND c.is_deleted = 0 AND c.time_class >= :timeStart AND c.time_class < :timeEnd GROUP BY teacher_id) AS a ON a.teacher_id = t.id"
                . " WHERE bad > 0"
                . (empty($filter) ? "" : " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')")
                . (empty($teacherId_list) ? "" : " AND t.id NOT IN(".implode(',',$teacherId_list).")")
                . " ORDER BY rate DESC"
                . " LIMIT ".(($page_num-1)*10).",10";


        }else{

            $sql = "SELECT ROUND(a.bad/a.num*100,2) AS rate, t.id AS teacher_id, t.nick, t.mobile FROM user_teacher AS t"
                . " LEFT JOIN (SELECT COUNT(case when r.teacher_grade = 2 OR r.teacher_grade = 3 then r.teacher_grade end) as bad, count(c.id) as num , c.teacher_id  FROM class_room as c LEFT JOIN class_record as r ON r.class_id = c.id"
                . " WHERE c.status < 2 AND c.is_deleted = 0 AND c.time_class >= :timeStart AND c.time_class < :timeEnd GROUP BY teacher_id) AS a ON a.teacher_id = t.id"
                . " WHERE bad > 0"
                . (empty($filter) ? "" : " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')")
                . (empty($teacherId_list) ? " AND t.id = -1" : " AND t.id IN(".implode(',',$teacherId_list).")")
                . " ORDER BY rate DESC"
                . " LIMIT ".(($page_num-1)*10).",10";
        }

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':timeStart' => $timeStart,
                ':timeEnd' => $timeEnd
            ])->queryAll();
    }

    public function getAbsenteeismCount($timeStart, $timeEnd, $filter, $type)
    {
        if (empty($type))
        {
            $sql = "SELECT COUNT(DISTINCT b.teacher_id) FROM `teacher_absence` AS b"
                . " LEFT JOIN user_teacher AS t ON b.teacher_id = t.id"
                . " WHERE b.time_day >= :timeStart AND b.time_day < :timeEnd AND b.type = 1"
                . (empty($filter) ? "" : " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')")
                . " AND b.teacher_id NOT IN (SELECT teacher_id FROM reward_record WHERE month_time = :timeStart AND type = 15)";
        }else{
            $sql = "SELECT COUNT(DISTINCT b.teacher_id) FROM `teacher_absence` AS b"
                . " LEFT JOIN user_teacher AS t ON b.teacher_id = t.id"
                . " WHERE b.time_day >= :timeStart AND b.time_day < :timeEnd AND b.type = 1"
                . (empty($filter) ? "" : " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')")
                . " AND b.teacher_id IN (SELECT teacher_id FROM reward_record WHERE month_time = :timeStart AND type = 15)";
        }

        return Yii::$app->db->createCommand($sql)
                    ->bindValues([
                        ':timeStart' => $timeStart,
                        ':timeEnd' => $timeEnd
                    ])->queryScalar();
    }

    public function getAbsenteeismList($timeStart, $timeEnd, $filter, $type, $page_num)
    {
        if (empty($type))
        {
            $sql = "SELECT COUNT(b.id) AS count, b.teacher_id, t.nick, t.mobile FROM `teacher_absence` AS b"
                . " LEFT JOIN user_teacher AS t ON b.teacher_id = t.id"
                . " WHERE b.time_day >= :timeStart AND b.time_day < :timeEnd AND b.type = 1"
                . (empty($filter) ? "" : " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')")
                . " AND b.teacher_id NOT IN (SELECT teacher_id FROM reward_record WHERE month_time = :timeStart AND type = 15)"
                . " GROUP BY b.teacher_id ORDER BY count DESC"
                . " LIMIT ".(($page_num-1)*10).",10";
        }else{
            $sql = "SELECT COUNT(b.id) AS count, b.teacher_id, t.nick, t.mobile FROM `teacher_absence` AS b"
                . " LEFT JOIN user_teacher AS t ON b.teacher_id = t.id"
                . " WHERE b.time_day >= :timeStart AND b.time_day < :timeEnd AND b.type = 1"
                . (empty($filter) ? "" : " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')")
                . " AND b.teacher_id IN (SELECT teacher_id FROM reward_record WHERE month_time = :timeStart AND type = 15)"
                . " GROUP BY b.teacher_id ORDER BY count DESC"
                . " LIMIT ".(($page_num-1)*10).",10";;
        }

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':timeStart' => $timeStart,
                ':timeEnd' => $timeEnd
            ])->queryAll();
    }

    public function getAbsenteeismTrainCount($timeStart, $timeEnd, $filter, $type)
    {
        if (empty($type))
        {
            $sql = "SELECT COUNT(DISTINCT b.teacher_id) FROM `teacher_absence` AS b"
                . " LEFT JOIN user_teacher AS t ON b.teacher_id = t.id"
                . " WHERE b.time_day >= :timeStart AND b.time_day < :timeEnd AND b.type = 2"
                . (empty($filter) ? "" : " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')")
                . " AND b.teacher_id NOT IN (SELECT teacher_id FROM reward_record WHERE month_time = :timeStart AND type = 16)";
        }else{
            $sql = "SELECT COUNT(DISTINCT b.teacher_id) FROM `teacher_absence` AS b"
                . " LEFT JOIN user_teacher AS t ON b.teacher_id = t.id"
                . " WHERE b.time_day >= :timeStart AND b.time_day < :timeEnd AND b.type = 2"
                . (empty($filter) ? "" : " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')")
                . " AND b.teacher_id IN (SELECT teacher_id FROM reward_record WHERE month_time = :timeStart AND type = 16)";
        }

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':timeStart' => $timeStart,
                ':timeEnd' => $timeEnd
            ])->queryScalar();
    }

    public function getAbsenteeismTrainList($timeStart, $timeEnd, $filter, $type, $page_num)
    {
        if (empty($type))
        {
            $sql = "SELECT COUNT(b.id) AS count, b.teacher_id, t.nick, t.mobile FROM `teacher_absence` AS b"
                . " LEFT JOIN user_teacher AS t ON b.teacher_id = t.id"
                . " WHERE b.time_day >= :timeStart AND b.time_day < :timeEnd AND b.type = 2"
                . (empty($filter) ? "" : " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')")
                . " AND b.teacher_id NOT IN (SELECT teacher_id FROM reward_record WHERE month_time = :timeStart AND type = 16)"
                . " GROUP BY b.teacher_id ORDER BY count DESC"
                . " LIMIT ".(($page_num-1)*10).",10";
        }else{
            $sql = "SELECT COUNT(b.id) AS count, b.teacher_id, t.nick, t.mobile FROM `teacher_absence` AS b"
                . " LEFT JOIN user_teacher AS t ON b.teacher_id = t.id"
                . " WHERE b.time_day >= :timeStart AND b.time_day < :timeEnd AND b.type = 2"
                . (empty($filter) ? "" : " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')")
                . " AND b.teacher_id IN (SELECT teacher_id FROM reward_record WHERE month_time = :timeStart AND type = 16)"
                . " GROUP BY b.teacher_id ORDER BY count DESC"
                . " LIMIT ".(($page_num-1)*10).",10";;
        }

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':timeStart' => $timeStart,
                ':timeEnd' => $timeEnd
            ])->queryAll();
    }

}