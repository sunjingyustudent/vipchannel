<?php
/**
 * Created by phpStorm.
 * User: xl
 * Date: 2017/2/7
 * Time: 15:44
 */
namespace console\models\teacher;

use Yii;
use yii\db\ActiveRecord;

class UserTeacher extends ActiveRecord
{
    public static function tableName()
    {
        return 'user_teacher';
    }

    public static function getTeacherInstrument($teacher_id)
    {
        $sql = "SELECT user_teacher_instrument.*, instrument.name as name FROM user_teacher_instrument"
            . " LEFT JOIN instrument ON user_teacher_instrument.instrument_id = instrument.id"
            . " WHERE user_teacher_instrument.user_id = :userId";

        return Yii::$app->db->createCommand($sql)
                    ->bindValue(':userId',$teacher_id)
                    ->queryAll();
    }

    public static function getManagerInfo()
    {
        $sql = "SELECT id, nick, open_id, responsible_school FROM user_teacher WHERE type = 2";

        return Yii::$app->db->createCommand($sql)
            ->queryAll();
    }

    public static function getSchoolTeacher($school_ids)
    {
        $sql = "SELECT id FROM `user_teacher` WHERE is_disabled = 0 AND teacher_type = 2 AND school_id IN (".implode(',',$school_ids).")";

        return Yii::$app->db->createCommand($sql)
                            ->queryAll();
    }
}