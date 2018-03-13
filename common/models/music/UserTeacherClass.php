<?php
/**
 * Created by phpStorm.
 * User: xl
 * Date: 2017/2/24
 * Time: 19:43
 */
namespace common\models\music;

use Yii;
use yii\db\ActiveRecord;

class UserTeacherClass extends ActiveRecord {

    public static function getDb()
    {
        return Yii::$app->db;
    }

    public static function tableName()
    {
        return 'user_teacher_class';
    }
}