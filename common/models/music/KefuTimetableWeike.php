<?php
/**
 * Created by PhpStorm.
 * User: wangkai
 * Date: 17//4/7
 * Time:  14:03
 */
namespace common\models\music;

use Yii;
use yii\db\ActiveRecord;

class KefuTimetableWeike extends ActiveRecord {

    public static function getDb()
    {
        return Yii::$app->db;
    }

    public static function tableName()
    {
        return 'kefu_timetable_weike';
    }

    public function addKefuTimetable($kefu_id, $timeDay, $timeBit)
    {
        $sql = "INSERT INTO kefu_timetable_weike(user_id,time_day,time_bit,time_created) VALUES(:kefu_id, :timeDay, ".$timeBit.", :time_created)"
            . " ON DUPLICATE KEY UPDATE time_bit = ".$timeBit.", time_updated = :time_update";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':kefu_id' => $kefu_id,
                ':timeDay' => $timeDay,
                ':time_created' => time(),
                ':time_update'=>time()
            ])->execute();
    }
}