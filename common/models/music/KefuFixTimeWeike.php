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

class KefuFixTimeWeike extends ActiveRecord {

    public static function getDb()
    {
        return Yii::$app->db;
    }

    public static function tableName()
    {
        return 'kefu_fix_time_weike';
    }

    public static function addKfuFixedTime($kefuId, $week, $timeBit, $timeExecute)
    {
        $sql = "INSERT INTO kefu_fix_time_weike(kefu_id,week,time_bit,time_execute,time_created) VALUES(:kefu_id,:week,".$timeBit.",:time_execute,:time_created)"
            . " ON DUPLICATE KEY UPDATE time_bit = ".$timeBit.", time_execute = :time_execute, time_updated = :time_update";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':kefu_id' => $kefuId,
                ':week' => $week,
                ':time_execute' => $timeExecute,
                ':time_created' => time(),
                ':time_update' => time()
            ])->execute();
    }
}