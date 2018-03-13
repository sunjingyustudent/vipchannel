<?php
/**
 * Created by phpStorm.
 * User: xl
 * Date: 2017/2/4
 * Time: 14:10
 */

namespace console\models\teacher;

use Yii;
use yii\db\ActiveRecord;

class TeacherAttendance extends ActiveRecord
{
    public static function tableName()
    {
        return 'teacher_attendance';
    }

    public static function getAttendanceList($timeStart, $timeEnd)
    {
        $sql = "SELECT t.id FROM user_teacher AS t"
            . " LEFT JOIN (SELECT teacher_id, count(id) as counts FROM statistics_teacher_rest WHERE time_day >= :timeStart AND time_day < :timeEnd"
            . " AND !(all_leave =0 and tmp_leave = 0 and pause = 0) GROUP BY teacher_id) AS s ON s.teacher_id = t.id"
            . " LEFT JOIN (SELECT teacher_id, COUNT(id) AS num FROM teacher_absence WHERE time_day >= :timeStart AND time_day < :timeEnd GROUP BY teacher_id) AS a ON a.teacher_id = t.id"
            . " WHERE t.employedtime < :timeStart  AND s.counts IS NULL AND a.num IS NULL AND t.is_formal = 1 AND t.is_disabled = 0";

        return Yii::$app->db->createCommand($sql)
                    ->bindValues([':timeStart' => $timeStart,':timeEnd' => $timeEnd])
                    ->queryColumn();
    }

    public static function intoAttendance($data)
    {
        return Yii::$app->db->createCommand()->batchInsert('teacher_attendance',
            ['teacher_id','time','is_attendance'],
            $data)->execute();
    }

    public static function getAbsenceList($timeStart, $timeEnd)
    {
        $sql = "SELECT t.id FROM user_teacher AS t"
            . " LEFT JOIN statistics_teacher_rest AS s ON s.teacher_id = t.id"
            . " WHERE s.time_day >= :timeStart AND s.time_day < :timeEnd"
            . " AND !(all_leave =0 and tmp_leave = 0 and pause = 0)"
            . " AND t.is_disabled= 0 AND t.is_formal =1"
            . " GROUP BY s.teacher_id  HAVING(count(s.id)> 2)";

        return Yii::$app->db->createCommand($sql)
                    ->bindValues([':timeStart' => $timeStart,':timeEnd' => $timeEnd])
                    ->queryColumn();
    }
    
    
    public static function runRepurchase($timeStart, $timeEnd)
    {
        $resql = "select uid from product_order"
            ." where time_pay < :timeStart and pay_status = 1 group by uid ";

        $reresult = Yii::$app->db->createCommand($resql)
                   ->bindValues([':timeStart' => $timeStart])
                   ->queryColumn();

        $sql = "select uid, actual_fee, time_pay from product_order"
            ." where time_pay >= :timeStart and time_pay < :timeEnd and pay_status = 1"
            . (empty($reresult) ? " AND uid = -1" : " AND uid IN (".implode(',',$reresult).")");
        
        $result = Yii::$app->db->createCommand($sql)
                        ->bindValues([':timeStart' => $timeStart,':timeEnd' => $timeEnd])
                        ->queryAll();
     
        return $result;
    }

    public static function intoRepurchaseRecord($data){
         return Yii::$app->db->createCommand()->batchInsert('user_repay',
            ['uid','time_pay','price'],
            $data)->execute();
    }
}