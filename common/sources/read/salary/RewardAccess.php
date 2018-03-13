<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:06
 */

namespace common\sources\read\salary;

use common\models\music\SalesTrade;
use Yii;
use common\models\music\RewardRecord;

Class RewardAccess implements IRewardAccess
{
    public function rewardRecordIsExit($teacher_id, $month_time, $type)
    {
        return RewardRecord::find()
            ->alias('r')
            ->select('r.id, r.money, tr.name as reward_name, r.prefix')
            ->leftJoin('teacher_reward_rule as tr','tr.id = r.reward_id')
            ->where('r.teacher_id = :tid',[':tid' => $teacher_id])
            ->andWhere('r.month_time = :month_time',[':month_time' => $month_time])
            ->andWhere('r.type = :type',[':type' => $type])
            ->asArray()
            ->one();
    }

    public function getTeacherCancelCount($timeStart, $timeEnd, $filter, $type)
    {
        $sql = "SELECT cs.teacher_id FROM teacher_class_cancel_statistics AS cs"
            . " LEFT JOIN user_teacher as t ON t.id = cs.teacher_id WHERE cs.time_day >= :timeStart AND cs.time_day < :timeEnd"
            . (empty($filter) ? "" : " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')");

        if (empty($type))
        {
           $sql .= " AND cs.teacher_id NOT IN (SELECT teacher_id FROM reward_record WHERE type = 6 AND month_time = :timeStart)";
        }else {
            $sql .= " AND cs.teacher_id IN (SELECT teacher_id FROM reward_record WHERE type = 6 AND month_time = :timeStart)";
        }

        $sql .= " GROUP BY cs.teacher_id ";

        return Yii::$app->db->createCommand($sql)
                    ->bindValues([
                        ':timeStart' => $timeStart,
                        ':timeEnd' => $timeEnd
                        ])->queryAll();
    }

    public function getTeacherCancelList($timeStart, $timeEnd, $filter, $type, $page_num)
    {
        $sql = "SELECT sum(cs.cancel_mount) AS cancel_mount, cs.teacher_id , t.nick, t.mobile FROM teacher_class_cancel_statistics AS cs"
            . " LEFT JOIN user_teacher as t ON t.id = cs.teacher_id WHERE cs.time_day >= :timeStart AND cs.time_day < :timeEnd"
            . (empty($filter) ? "" : " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')");

        if (empty($type))
        {
            $sql .= " AND cs.teacher_id NOT IN (SELECT teacher_id FROM reward_record WHERE type = 6 AND month_time = :timeStart)";
        }else {
            $sql .= " AND cs.teacher_id IN (SELECT teacher_id FROM reward_record WHERE type = 6 AND month_time = :timeStart)";
        }

        $sql .= " GROUP BY cs.teacher_id LIMIT ". ($page_num - 1) * 10 . ",10";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':timeStart' => $timeStart,
                ':timeEnd' => $timeEnd
            ])->queryAll();
    }

    public function getTeacherRewardTotal($teacher_id, $timeStart, $timeEnd)
    {
        $sql = "SELECT ifnull(sum(money),0) as reward, teacher_id from reward_record WHERE prefix = 1 AND  teacher_id = :teacher_id AND time_created >= :timeStart AND time_created < :timeEnd";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':teacher_id' => $teacher_id,
                ':timeStart' => $timeStart,
                ':timeEnd' => $timeEnd
            ])->queryOne();
    }

    public function getTeacherRewardPunishment($teacher_id, $timeStart, $timeEnd)
    {
        $sql = "SELECT salary_reward,salary_punish from teacher_reward_punish WHERE teacher_id = :teacher_id AND time_created >= :timeStart AND time_created < :timeEnd";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':teacher_id' => $teacher_id,
                ':timeStart' => $timeStart,
                ':timeEnd' => $timeEnd
            ])->queryOne();
    }

    public function getTeacherRewardList($teacher_id, $timeStart, $timeEnd)
    {
        return RewardRecord::find()
                    ->select('text, money, time_created')
                    ->where(['teacher_id' => $teacher_id, 'prefix' => 1])
                    ->andWhere('time_created >= :timeStart',[':timeStart' => $timeStart])
                    ->andWhere('time_created < :timeEnd',[':timeEnd' => $timeEnd])
                    ->asArray()
                    ->all();
    }

    public function getRewardTotal($timeStart, $timeEnd)
    {
        return RewardRecord::find()
            ->select('sum(money)')
            ->where(['prefix' => 1])
            ->andWhere('time_created >= :timeStart',[':timeStart' => $timeStart])
            ->andWhere('time_created < :timeEnd',[':timeEnd' => $timeEnd])
            ->scalar();
    }

    public function getExToBuyCount($filter, $timeStart, $timeEnd, $type)
    {
        $sql1 = "SELECT teacher_id FROM reward_record WHERE month_time = :timeStart AND type = 4";

        $teacherId_list = Yii::$app->db->createCommand($sql1)
            ->bindValue(':timeStart',$timeStart)
            ->queryColumn();

        $sql2 = "SELECT DISTINCT uid FROM product_order WHERE pay_status = 1 AND time_created < :timeStart";

        $studentBuy_list = Yii::$app->db->createCommand($sql2)
            ->bindValue(':timeStart',$timeStart)
            ->queryColumn();

        if (empty($type))
        {
            $sql = "SELECT c.teacher_id FROM product_order as p"
                . " LEFT JOIN class_room as c ON p.uid = c.student_id"
                . " LEFT JOIN user_teacher AS t ON t.id = c.teacher_id"
                . " WHERE p.pay_status = 1 AND p.time_pay >= :timeStart AND p.time_pay < :timeEnd AND c.`status` = 1 AND c.is_ex_class = 1"
                . (empty($studentBuy_list) ? "" : " AND p.uid NOT IN (".implode(',',$studentBuy_list).")")
                . (empty($teacherId_list) ? "" : " AND c.teacher_id NOT IN(".implode(',',$teacherId_list).")")
                . " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')"
                . " GROUP BY teacher_id";
        }else{
            $sql = "SELECT c.teacher_id FROM product_order as p"
                . " LEFT JOIN class_room as c ON p.uid = c.student_id"
                . " LEFT JOIN user_teacher AS t ON t.id = c.teacher_id"
                . " WHERE p.pay_status = 1 AND p.time_pay >= :timeStart AND p.time_pay < :timeEnd AND c.`status` = 1 AND c.is_ex_class = 1"
                . (empty($studentBuy_list) ? "" : " AND p.uid NOT IN (".implode(',',$studentBuy_list).")")
                . (empty($teacherId_list) ? " AND c.teacher_id = -1" : " AND c.teacher_id IN(".implode(',',$teacherId_list).")")
                . " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')"
                . " GROUP BY teacher_id";
        }

        return Yii::$app->db->createCommand($sql)
                ->bindValues([':timeStart' => $timeStart, ':timeEnd' => $timeEnd])
                ->queryColumn();
    }

    public function getExToBuyList($filter, $timeStart, $timeEnd, $type, $page_num)
    {
        $sql1 = "SELECT teacher_id FROM reward_record WHERE month_time = :timeStart AND type = 4";

        $teacherId_list = Yii::$app->db->createCommand($sql1)
            ->bindValue(':timeStart',$timeStart)
            ->queryColumn();

        $sql2 = "SELECT DISTINCT uid FROM product_order WHERE pay_status = 1 AND time_created < :timeStart";

        $studentBuy_list = Yii::$app->db->createCommand($sql2)
            ->bindValue(':timeStart',$timeStart)
            ->queryColumn();

        if (empty($type))
        {
            $sql = "SELECT COUNT(DISTINCT p.uid) AS ex_to_buy, c.teacher_id, t.nick FROM product_order as p"
                . " LEFT JOIN class_room as c ON p.uid = c.student_id"
                . " LEFT JOIN user_teacher AS t ON t.id = c.teacher_id"
                . " WHERE p.pay_status = 1 AND p.time_pay >= :timeStart AND p.time_pay < :timeEnd AND c.`status` = 1 AND c.is_ex_class = 1"
                . (empty($studentBuy_list) ? "" : " AND p.uid NOT IN (".implode(',',$studentBuy_list).")")
                . (empty($teacherId_list) ? "" : " AND c.teacher_id NOT IN(".implode(',',$teacherId_list).")")
                . " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')"
                . " GROUP BY c.teacher_id ORDER BY ex_to_buy DESC, c.teacher_id DESC"
                . " LIMIT ".(($page_num-1) * 10).",10";
        }else{
            $sql = "SELECT COUNT(DISTINCT p.uid) AS ex_to_buy, c.teacher_id, t.nick FROM product_order as p"
                . " LEFT JOIN class_room as c ON p.uid = c.student_id"
                . " LEFT JOIN user_teacher AS t ON t.id = c.teacher_id"
                . " WHERE p.pay_status = 1 AND p.time_pay >= :timeStart AND p.time_pay < :timeEnd AND c.`status` = 1 AND c.is_ex_class = 1"
                . (empty($studentBuy_list) ? "" : " AND p.uid NOT IN (".implode(',',$studentBuy_list).")")
                . (empty($teacherId_list) ? " AND c.teacher_id = -1" : " AND c.teacher_id IN(".implode(',',$teacherId_list).")")
                . " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')"
                . " GROUP BY c.teacher_id ORDER BY ex_to_buy DESC, c.teacher_id DESC"
                . " LIMIT ".(($page_num-1) * 10).",10";
        }

        return Yii::$app->db->createCommand($sql)
                    ->bindValues([':timeStart' => $timeStart, ':timeEnd' => $timeEnd])
                    ->queryAll();
    }

    public function exportExToBuyList($filter, $timeStart, $timeEnd, $type)
    {
        $sql1 = "SELECT teacher_id FROM reward_record WHERE month_time = :timeStart AND type = 4";

        $teacherId_list = Yii::$app->db->createCommand($sql1)
            ->bindValue(':timeStart',$timeStart)
            ->queryColumn();

        $sql2 = "SELECT DISTINCT uid FROM product_order WHERE pay_status = 1 AND time_created < :timeStart";

        $studentBuy_list = Yii::$app->db->createCommand($sql2)
            ->bindValue(':timeStart',$timeStart)
            ->queryColumn();

        if (empty($type))
        {
            $sql = "SELECT COUNT(DISTINCT p.uid) AS ex_to_buy, c.teacher_id, t.nick FROM product_order as p"
                . " LEFT JOIN class_room as c ON p.uid = c.student_id"
                . " LEFT JOIN user_teacher AS t ON t.id = c.teacher_id"
                . " WHERE p.pay_status = 1 AND p.time_pay >= :timeStart AND p.time_pay < :timeEnd AND c.`status` = 1 AND c.is_ex_class = 1"
                . (empty($studentBuy_list) ? "" : " AND p.uid NOT IN (".implode(',',$studentBuy_list).")")
                . (empty($teacherId_list) ? "" : " AND c.teacher_id NOT IN(".implode(',',$teacherId_list).")")
                . " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')"
                . " GROUP BY c.teacher_id ORDER BY ex_to_buy DESC, c.teacher_id DESC";
        }else{
            $sql = "SELECT COUNT(DISTINCT p.uid) AS ex_to_buy, c.teacher_id, t.nick FROM product_order as p"
                . " LEFT JOIN class_room as c ON p.uid = c.student_id"
                . " LEFT JOIN user_teacher AS t ON t.id = c.teacher_id"
                . " WHERE p.pay_status = 1 AND p.time_pay >= :timeStart AND p.time_pay < :timeEnd AND c.`status` = 1 AND c.is_ex_class = 1"
                . (empty($studentBuy_list) ? "" : " AND p.uid NOT IN (".implode(',',$studentBuy_list).")")
                . (empty($teacherId_list) ? " AND c.teacher_id = -1" : " AND c.teacher_id IN(".implode(',',$teacherId_list).")")
                . " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')"
                . " GROUP BY c.teacher_id ORDER BY ex_to_buy DESC, c.teacher_id DESC";
        }

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':timeStart' => $timeStart, ':timeEnd' => $timeEnd])
            ->queryAll();
    }

    public function getOvertimeDealCount($filter, $timeStart, $timeEnd)
    {
        $sql1 = "SELECT teacher_id FROM reward_record WHERE month_time = :timeStart AND type = 5";

        $teacherId_list = Yii::$app->db->createCommand($sql1)
            ->bindValue(':timeStart',$timeStart)
            ->queryColumn();

        $sql = "SELECT s.teacher_id FROM teacher_overtime_statistics AS s"
            . " LEFT JOIN user_teacher as t ON s.teacher_id = t.id"
            . " WHERE s.time_day >= :timeStart AND s.time_day < :timeEnd AND s.type = 1"
            . (empty($teacherId_list) ? " AND s.teacher_id = -1" : " AND s.teacher_id IN(".implode(',',$teacherId_list).")")
            . " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%') GROUP BY s.teacher_id";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':timeStart' => $timeStart,
                ':timeEnd' => $timeEnd
            ])->queryColumn();
    }

    public function getOvertimeNoDealCount($filter, $timeStart, $timeEnd)
    {
        $sql1 = "SELECT teacher_id FROM reward_record WHERE month_time = :timeStart AND type = 5";

        $teacherId_list = Yii::$app->db->createCommand($sql1)
            ->bindValue(':timeStart',$timeStart)
            ->queryColumn();

        $sql = "SELECT s.teacher_id FROM teacher_overtime_statistics AS s"
            . " LEFT JOIN user_teacher as t ON s.teacher_id = t.id"
            . " WHERE s.time_day >= :timeStart AND s.time_day < :timeEnd AND s.type = 1"
            . (empty($teacherId_list) ? "" : " AND s.teacher_id NOT IN(".implode(',',$teacherId_list).")")
            . " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%') GROUP BY s.teacher_id";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':timeStart' => $timeStart,
                ':timeEnd' => $timeEnd
            ])->queryColumn();
    }

    public function getOvertimeDealList($filter, $timeStart, $timeEnd, $page_num)
    {
        $sql1 = "SELECT teacher_id FROM reward_record WHERE month_time = :timeStart AND type = 5";

        $teacherId_list = Yii::$app->db->createCommand($sql1)
            ->bindValue(':timeStart',$timeStart)
            ->queryColumn();

        $sql = "SELECT SUM(s.over_time) AS over_time, s.teacher_id, t.nick, t.mobile FROM teacher_overtime_statistics AS s"
            . " LEFT JOIN user_teacher as t ON s.teacher_id = t.id"
            . " WHERE s.time_day >= :timeStart AND s.time_day < :timeEnd AND s.type = 1"
            . (empty($teacherId_list) ? " AND s.teacher_id = -1" : " AND s.teacher_id IN(".implode(',',$teacherId_list).")")
            . " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%') GROUP BY s.teacher_id LIMIT ".(($page_num-1)*10).",10";

        return Yii::$app->db->createCommand($sql)
                ->bindValues([
                    ':timeStart' => $timeStart,
                    ':timeEnd' => $timeEnd,
                ])->queryAll();
    }

    public function getOvertimeNoDelList($filter, $timeStart, $timeEnd, $page_num)
    {
        $sql1 = "SELECT teacher_id FROM reward_record WHERE month_time = :timeStart AND type = 5";

        $teacherId_list = Yii::$app->db->createCommand($sql1)
            ->bindValue(':timeStart',$timeStart)
            ->queryColumn();

        $sql = "SELECT SUM(s.over_time) AS over_time, s.teacher_id, t.nick, t.mobile FROM teacher_overtime_statistics AS s"
            . " LEFT JOIN user_teacher as t ON s.teacher_id = t.id"
            . " WHERE s.time_day >= :timeStart AND s.time_day < :timeEnd AND s.type = 1"
            . (empty($teacherId_list) ? "" : " AND s.teacher_id NOT IN(".implode(',',$teacherId_list).")")
            . " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%') GROUP BY s.teacher_id LIMIT ".(($page_num-1)*10).",10";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':timeStart' => $timeStart,
                ':timeEnd' => $timeEnd
            ])->queryAll();
    }

    public function getFestivalCount($time_start, $time_end, $filter, $type)
    {
        if (empty($type))
        {
            $sql = "SELECT count(DISTINCT t.id) FROM user_teacher as t LEFT JOIN class_room as c ON t.id = c.teacher_id"
                . " WHERE c.`status` = 1 AND c.is_deleted = 0 AND c.time_class >= :time_start AND c.time_class < :time_end"
                . " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')"
                . " AND t.id NOT IN (SELECT teacher_id FROM reward_record WHERE month_time = :time_start AND type = 11)";


        }else{
            $sql = "SELECT count(DISTINCT t.id) FROM user_teacher as t LEFT JOIN class_room as c ON t.id = c.teacher_id"
                . " WHERE c.`status` = 1 AND c.is_deleted = 0 AND c.time_class >= :time_start AND c.time_class < :time_end"
                . " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')"
                . " AND t.id IN (SELECT teacher_id FROM reward_record WHERE month_time = :time_start AND type = 11)";
        }

        return Yii::$app->db->createCommand($sql)
                ->bindValues([
                    ':time_start' => $time_start,
                    ':time_end' => $time_end
                ])->queryScalar();
    }

    public function getFestivalList($time_start, $time_end, $filter, $type, $page_num)
    {
        if (empty($type))
        {
            $sql = "SELECT t.nick, t.mobile, t.id AS teacher_id, a.count FROM user_teacher as t"
                . " LEFT JOIN (SELECT COUNT(id) AS count, teacher_id FROM class_room WHERE `status` = 1 AND is_deleted = 0 AND time_class >= :time_start AND time_class < :time_end GROUP BY teacher_id) AS a ON t.id = a.teacher_id"
                . " WHERE a.count > 0"
                . " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')"
                . " AND t.id NOT IN (SELECT teacher_id FROM reward_record WHERE month_time = :time_start AND type = 11)"
                . " LIMIT ".(($page_num-1)*10).",10";

        }else{

            $sql = "SELECT t.nick, t.mobile, t.id AS teacher_id, a.count, r.money, rr.name AS reward_name, r.prefix FROM user_teacher as t"
                . " LEFT JOIN (SELECT COUNT(id) AS count, teacher_id FROM class_room WHERE `status` = 1 AND is_deleted = 0 AND time_class >= :time_start AND time_class < :time_end GROUP BY teacher_id) AS a ON t.id = a.teacher_id"
                . " LEFT JOIN reward_record AS r ON r.teacher_id = t.id"
                . " LEFT JOIN teacher_reward_rule as rr ON rr.id = r.reward_id"
                . " WHERE a.count > 0 AND r.month_time = :time_start AND r.type = 11"
                . " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')"
                . " LIMIT ".(($page_num-1)*10).",10";
        }

        return Yii::$app->db->createCommand($sql)
                    ->bindValues([
                        ':time_start' => $time_start,
                        ':time_end' => $time_end
                    ])->queryAll();
    }

    public function getOtherOvertimeDealCount($filter, $timeStart, $timeEnd)
    {
        $sql1 = "SELECT teacher_id FROM reward_record WHERE month_time = :timeStart AND type = 10";

        $teacherId_list = Yii::$app->db->createCommand($sql1)
            ->bindValue(':timeStart',$timeStart)
            ->queryColumn();

        $sql = "SELECT s.teacher_id FROM teacher_overtime_statistics AS s"
            . " LEFT JOIN user_teacher as t ON s.teacher_id = t.id"
            . " WHERE s.time_day >= :timeStart AND s.time_day < :timeEnd AND s.type = 2"
            . (empty($teacherId_list) ? " AND s.teacher_id = -1" : " AND s.teacher_id IN(".implode(',',$teacherId_list).")")
            . " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%') GROUP BY s.teacher_id";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':timeStart' => $timeStart,
                ':timeEnd' => $timeEnd
            ])->queryColumn();
    }

    public function getOtherOvertimeNoDealCount($filter, $timeStart, $timeEnd)
    {
        $sql1 = "SELECT teacher_id FROM reward_record WHERE month_time = :timeStart AND type = 10";

        $teacherId_list = Yii::$app->db->createCommand($sql1)
            ->bindValue(':timeStart',$timeStart)
            ->queryColumn();

        $sql = "SELECT s.teacher_id FROM teacher_overtime_statistics AS s"
            . " LEFT JOIN user_teacher as t ON s.teacher_id = t.id"
            . " WHERE s.time_day >= :timeStart AND s.time_day < :timeEnd AND s.type = 2"
            . (empty($teacherId_list) ? "" : " AND s.teacher_id NOT IN(".implode(',',$teacherId_list).")")
            . " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%') GROUP BY s.teacher_id";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':timeStart' => $timeStart,
                ':timeEnd' => $timeEnd
            ])->queryColumn();
    }

    public function getOtherOvertimeDealList($filter, $timeStart, $timeEnd, $page_num)
    {
        $sql1 = "SELECT teacher_id FROM reward_record WHERE month_time = :timeStart AND type = 10";

        $teacherId_list = Yii::$app->db->createCommand($sql1)
            ->bindValue(':timeStart',$timeStart)
            ->queryColumn();

        $sql = "SELECT SUM(s.over_time) AS over_time, s.teacher_id, t.nick, t.mobile FROM teacher_overtime_statistics AS s"
            . " LEFT JOIN user_teacher as t ON s.teacher_id = t.id"
            . " WHERE s.time_day >= :timeStart AND s.time_day < :timeEnd AND s.type = 2"
            . (empty($teacherId_list) ? " AND s.teacher_id = -1" : " AND s.teacher_id IN(".implode(',',$teacherId_list).")")
            . " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%') GROUP BY s.teacher_id LIMIT ".(($page_num-1)*10).",10";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':timeStart' => $timeStart,
                ':timeEnd' => $timeEnd
            ])->queryAll();
    }

    public function getOtherOvertimeNoDelList($filter, $timeStart, $timeEnd, $page_num)
    {
        $sql1 = "SELECT teacher_id FROM reward_record WHERE month_time = :timeStart AND type = 10";

        $teacherId_list = Yii::$app->db->createCommand($sql1)
            ->bindValue(':timeStart', $timeStart)
            ->queryColumn();

        $sql = "SELECT SUM(s.over_time) AS over_time, s.teacher_id, t.nick, t.mobile FROM teacher_overtime_statistics AS s"
            . " LEFT JOIN user_teacher as t ON s.teacher_id = t.id"
            . " WHERE s.time_day >= :timeStart AND s.time_day < :timeEnd AND s.type = 2"
            . (empty($teacherId_list) ? "" : " AND s.teacher_id NOT IN(" . implode(',', $teacherId_list) . ")")
            . " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%') GROUP BY s.teacher_id LIMIT " . (($page_num - 1) * 10) . ",10";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':timeStart' => $timeStart,
                ':timeEnd' => $timeEnd
            ])->queryAll();
    }


    
    /**
     * @param $time
     * @param $filter
     * @param $type
     * @return mixed
     * @author sjy
     * 获取全勤奖列表
     */
    
    public function getAttendanceTeacherList($timeStart, $timeEnd, $filter, $type, $page_num)
    {
        if($type==0)
        {
            $sql = "SELECT t.id, t.nick, t.mobile, t.allduty_award_rates FROM user_teacher AS t "
                . " LEFT JOIN teacher_attendance AS a ON t.id = a.teacher_id"
                . " WHERE a.time >= :timeStart AND a.time < :timeEnd AND a.is_attendance = 1 AND a.tag = 0"
                . " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%') LIMIT ".(($page_num-1)*10).",10";

        }else{

            $sql = "SELECT t.id, t.nick, t.mobile, t.allduty_award_rates, r.money FROM user_teacher AS t "
                . " LEFT JOIN teacher_attendance AS a ON t.id = a.teacher_id"
                . " LEFT JOIN (SELECT teacher_id, money FROM reward_record WHERE type = 12 AND month_time = :timeStart) AS r ON r.teacher_id = t.id"
                . " WHERE a.time >= :timeStart AND a.time < :timeEnd AND a.is_attendance = 1 AND a.tag = 1"
                . " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%') LIMIT ".(($page_num-1)*10).",10";
        }

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':timeStart' => $timeStart,
                ':timeEnd' => $timeEnd
            ])->queryAll();
        
    }

    /**
     * @param $time
     * @param $filter
     * @param $type
     * @return mixed
     * @author sjy
     * 获取全勤奖个数
     */
    public function getAttendanceTeacherCount($timeStart, $timeEnd, $filter, $type)
    {
        if($type == 0){

            $sql = "SELECT t.id FROM user_teacher AS t"
                . " LEFT JOIN teacher_attendance AS a ON t.id = a.teacher_id"
                . " WHERE a.time >= :timeStart AND a.time < :timeEnd AND a.is_attendance = 1 AND a.tag = 0"
                . " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')";

        }else{

            $sql = "SELECT t.id FROM user_teacher AS t"
                . " LEFT JOIN teacher_attendance AS a ON t.id = a.teacher_id"
                . " WHERE a.time >= :timeStart AND a.time < :timeEnd AND a.is_attendance = 1 AND a.tag = 1"
                . " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')";
        }
        
        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':timeStart' => $timeStart,
                ':timeEnd' => $timeEnd
            ])->queryColumn();
    }
    
    
    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $filter
     * @param $type
     * @return mixed
     * @author sjy
     * 获取好评奖个数
     */
    public function getGoodEvaluationCount($timeStart, $timeEnd, $filter, $type)
    {
       
        $sql1 = "SELECT teacher_id FROM reward_record WHERE month_time = :timeStart AND type = 13";

        $teacherId_list = Yii::$app->db->createCommand($sql1)
            ->bindValue(':timeStart',$timeStart)
            ->queryColumn();

        if($type == 0){

            $sql = "SELECT count(DISTINCT c.teacher_id) FROM class_room as c"
                . " LEFT JOIN class_record as r on r.class_id = c.id "
                . " LEFT JOIN user_teacher as t on t.id = c.teacher_id "
                . " WHERE c.time_class >= :timeStart AND c.time_class < :timeEnd AND c.`status` < 2 AND c.is_deleted = 0 AND r.teacher_grade = 1"
                . (empty($filter) ? "" : " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')")
                . (empty($teacherId_list) ? "" : " AND c.teacher_id NOT IN(".implode(',',$teacherId_list).")");

        }else{

            $sql = "SELECT count(DISTINCT c.teacher_id) FROM class_room as c"
                . " LEFT JOIN class_record as r on r.class_id = c.id "
                . " LEFT JOIN user_teacher as t on t.id = c.teacher_id "
                . " WHERE c.time_class >= :timeStart AND c.time_class < :timeEnd AND c.`status` < 2 AND c.is_deleted = 0 AND r.teacher_grade = 1"
                . (empty($filter) ? "" : " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')")
                . (empty($teacherId_list) ? " AND c.teacher_id = -1" : " AND c.teacher_id IN(".implode(',',$teacherId_list).")");
        }
        
        return Yii::$app->db->createCommand($sql)
                    ->bindValues([
                        ':timeStart' => $timeStart,
                        ':timeEnd' => $timeEnd
                    ])->queryScalar();
    }
    
    
    /**
     * @param $time
     * @param $filter
     * @param $page_num
     * @param $type
     * @return mixed
     * @author sjy
     * 处理好评奖励list
     */
    public function getGoodEvaluationList($timeStart, $timeEnd, $filter, $type, $page_num)
    {
         $sql1 = "SELECT teacher_id FROM reward_record WHERE month_time = :timeStart AND type = 13";


         $teacherId_list = Yii::$app->db->createCommand($sql1)
            ->bindValue(':timeStart',$timeStart)
            ->queryColumn();

        if($type == 0){

            $sql = "SELECT ROUND(a.good/a.num*100,2) AS rate, t.id AS teacher_id, t.nick, t.mobile FROM user_teacher AS t"
                . " LEFT JOIN (SELECT COUNT(case when r.teacher_grade = 1 then r.teacher_grade end) as good, count(c.id) as num , c.teacher_id  FROM class_room as c LEFT JOIN class_record as r ON r.class_id = c.id"
                . " WHERE c.status < 2 AND c.is_deleted = 0 AND c.time_class >= :timeStart AND c.time_class < :timeEnd GROUP BY teacher_id) AS a ON a.teacher_id = t.id"
                . " WHERE good > 0"
                . (empty($filter) ? "" : " AND (t.nick LIKE '%$filter%' OR t.mobile LIKE '%$filter%')")
                . (empty($teacherId_list) ? "" : " AND t.id NOT IN(".implode(',',$teacherId_list).")")
                . " ORDER BY rate DESC"
                . " LIMIT ".(($page_num-1)*10).",10";


        }else{

            $sql = "SELECT ROUND(a.good/a.num*100,2) AS rate, t.id AS teacher_id, t.nick, t.mobile FROM user_teacher AS t"
                . " LEFT JOIN (SELECT COUNT(case when r.teacher_grade = 1 then r.teacher_grade end) as good, count(c.id) as num , c.teacher_id  FROM class_room as c LEFT JOIN class_record as r ON r.class_id = c.id"
                . " WHERE c.status < 2 AND c.is_deleted = 0 AND c.time_class >= :timeStart AND c.time_class < :timeEnd GROUP BY teacher_id) AS a ON a.teacher_id = t.id"
                . " WHERE good > 0"
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

    public function getTripCount($type)
    {
        if (empty($type)) {
            $sql1 = "SELECT teacher_id FROM statistics_teacher_rest WHERE pause = 1 AND all_leave = 0 AND tmp_leave = 0 GROUP BY teacher_id HAVING COUNT(id) > 15";

            $teacher_ids = Yii::$app->db->createCommand($sql1)
                ->queryColumn();

            $sql2 = "SELECT teacher_id FROM teacher_trip";

            $trip_ids = Yii::$app->db->createCommand($sql2)
                ->queryColumn();

            $ids = array_merge($teacher_ids, $trip_ids);

            $sql = "SELECT id FROM user_teacher"
                . " WHERE (unix_timestamp(NOW()) - employedtime) > 86400*365*2"
                . " AND is_disabled = 0 AND is_formal = 1"
                . (empty($ids) ? "" : " AND id NOT IN (" . implode(',', $ids) . ")");
        }else{

            $sql = "SELECT teacher_id FROM teacher_trip";
        }

        return Yii::$app->db->createCommand($sql)
                         ->queryColumn();
    }

    public function getTripList($type, $page_num)
    {
        if (empty($type))
        {
            $sql1 = "SELECT teacher_id FROM statistics_teacher_rest WHERE pause = 1 AND all_leave = 0 AND tmp_leave = 0 GROUP BY teacher_id HAVING COUNT(id) > 15";

            $teacher_ids = Yii::$app->db->createCommand($sql1)
                ->queryColumn();

            $sql2 = "SELECT teacher_id FROM teacher_trip";

            $trip_ids = Yii::$app->db->createCommand($sql2)
                ->queryColumn();

            $ids = array_merge($teacher_ids, $trip_ids);

            $sql = "SELECT id AS teacher_id, nick, mobile, employedtime, (unix_timestamp(NOW()) - employedtime) AS length FROM user_teacher "
                . " WHERE (unix_timestamp(NOW()) - employedtime) > 86400*365*2"
                . " AND is_disabled = 0 AND is_formal = 1"
                . (empty($ids) ? "" : " AND id NOT IN (" . implode(',', $ids) . ")")
                . " ORDER BY length DESC"
                . " LIMIT ".(($page_num-1)*10).",10";
        }else{
            $sql = "SELECT p.teacher_id, t.nick, t.mobile, t.employedtime, (unix_timestamp(NOW()) - t.employedtime) AS length FROM teacher_trip as p LEFT JOIN user_teacher AS t ON p.teacher_id = t.id ORDER BY length DESC"
                . " LIMIT ".(($page_num-1)*10).",10";
        }

        return Yii::$app->db->createCommand($sql)
                    ->queryAll();
    }
    
    /**
     * @param 
     * @return mixed
     * @author sjy
     * 获取获得复购奖励的个数
     */
    public function getRepurchaseCount($time, $filter, $type)
    {
        $timeStart = strtotime($time);
        $timeEnd = strtotime("+1 month", $timeStart);
        
        $sql="select ur.uid from user_repay as ur "
             ."left join user as u on u.id = ur.uid "   
             ." where ur.time_pay >= :timeStart and ur.time_pay < :timeEnd and ur.tag = :tag "
             . (empty($filter) ? "" : " AND (u.nick LIKE '%$filter%' OR u.mobile LIKE '%$filter%')");
        
        $result= Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':timeStart' => $timeStart,
                ':timeEnd' => $timeEnd,
                ':tag' => $type
            ])->queryAll();  
        
        return count($result);
      

    }
    
    /**
     * @param 
     * @return mixed
     * @author sjy
     * 获取获得复购奖励list
     */
    public function actionRepurchaseList($time,$filter,$page_num,$type)
    {
        $timeStart = strtotime($time);
        $timeEnd = strtotime("+1 month", $timeStart);
        
        $sql="select ur.money, ur.uid, ur.time_pay, ur.price, u.nick, u.mobile, ur.id from user_repay as ur "
             . "left join user as u on u.id = ur.uid "
             . " where ur.time_pay >= :timeStart and ur.time_pay < :timeEnd and ur.tag = :tag "
             . (empty($filter) ? "" : " AND (u.nick LIKE '%$filter%' OR u.mobile LIKE '%$filter%')")
             . " LIMIT ".(($page_num-1)*10).",10";
        
        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':timeStart' => $timeStart,
                ':timeEnd' => $timeEnd,
                ':tag' => $type
            ])->queryAll();
    }
    
    /**
     * @param 
     * @return mixed
     * @author sjy
     * 获取给当前学生上过课的所有老师
     */
    public function teacherList($uid, $time_pay)
    {

        $sql="select cr.teacher_id, ut.nick from class_room as cr "
             ."left join user_teacher as ut on ut.id = cr.teacher_id "   
             ." where cr.time_class < :timeStart and cr.status = 1 and cr.student_id = :uid ";
            
        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':timeStart' => $time_pay,
                ':uid' => $uid
            
            ])->queryAll();     
    }


}