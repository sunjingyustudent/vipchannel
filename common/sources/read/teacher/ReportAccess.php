<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:06
 */

namespace common\sources\read\teacher;

use Yii;
use common\models\music\OperationStatistics;

Class ReportAccess implements IReportAccess {

    public function getHomeStatisticsCount($timeStart, $timeEnd)
    {
        return OperationStatistics::find()
                ->where('create_time >= :timeStart',[':timeStart' => $timeStart])
                ->andWhere('create_time < :timeEnd',[':timeEnd' => $timeEnd])
                ->count();
    }

    public function getHomeStatistics($timeStart, $timeEnd, $page_num)
    {
        return OperationStatistics::find()
            ->where('create_time >= :timeStart', [':timeStart' => $timeStart])
            ->andWhere('create_time < :timeEnd', [':timeEnd' => $timeEnd])
            ->offset(($page_num - 1) * 10)
            ->limit(10)
            ->asArray()
            ->all();
    }

    public function getTeacherLeaveList($timeStart, $timeEnd, $filter, $page_num)
    {
        $sql = "SELECT u.id, u.nick, IFNULL(s.tmp_count,0) as tmp_leave, IFNULL(s.all_count,0) as all_leave, IFNULL(s.pause_count,0) as pause FROM user_teacher AS u"
            . " LEFT JOIN (SELECT SUM(tmp_leave) as tmp_count, SUM(all_leave) as all_count, SUM(pause) as pause_count, teacher_id FROM statistics_teacher_rest WHERE time_day >= :time_start AND time_day < :time_end GROUP BY teacher_id) AS s ON s.teacher_id = u.id"
            . " WHERE u.is_disabled = 0 and u.is_formal = 1 and u.is_test = 0"
            . (empty($filter) ? "" : " AND (u.mobile LIKE '%$filter%' OR u.nick LIKE '%$filter%')")
            . " ORDER BY all_leave DESC"
            . " LIMIT ".(($page_num-1)*10). ",10";


        return Yii::$app->db->createCommand($sql)
            ->bindValues([':time_start'=>$timeStart,':time_end'=>$timeEnd])
            ->queryAll();
    }

    public function getPlaceDayRateTotal($time_day, $place_id)
    {
        $sql = "SELECT AVG(rate) FROM `teacher_use_rate` WHERE place_id = :place_id AND time_day = :time_day";

        return Yii::$app->db->createCommand($sql)
                        ->bindValues(['place_id' => $place_id, 'time_day' => $time_day])
                        ->queryScalar();
    }

    public function getHourRateByPlaceId($timeStart, $timeEnd, $place_id)
    {
        $sql = "SELECT id, hour, time_day, rate FROM `place_hour_rate`"
            . " WHERE place_id = :place_id AND rate >= 0.9 AND time_day >= :timeStart AND time_day < :timeEnd";

        return Yii::$app->db->createCommand($sql)
                        ->bindValues([
                            ':place_id' => $place_id,
                            ':timeStart' => $timeStart,
                            ':timeEnd' => $timeEnd
                        ])->queryAll();
    }

    public function getRateDetail($id)
    {
        $sql = "SELECT info FROM place_hour_rate WHERE id = :id";

        return Yii::$app->db->createCommand($sql)
                        ->bindValue(':id', $id)
                        ->queryScalar();
    }

}