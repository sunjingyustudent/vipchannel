<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 16/11/10
 * Time: 14:59
 */
namespace console\models\record;

use Yii;
use yii\db\ActiveRecord;

class UserAccount extends ActiveRecord
{

    public static function tableName()
    {
        return 'user_account';
    }

    public static function getBaseCourseFee()
    {
        $sql = "UPDATE user_teacher as u SET"
            . " class_hour_first = (select class_hour_first from base_place  as b where b.id = u.work_type),"
            . " class_hour_second = (select class_hour_second from base_place  as b where b.id = u.work_type),"
            . " class_hour_third = (select class_hour_third from base_place  as b where b.id = u.work_type)";

        return Yii::$app->db->createCommand($sql)
                    ->execute();
    }

}