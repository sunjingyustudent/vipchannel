<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 17/2/3
 * Time: 10:56
 */
namespace common\models\music;

use Yii;
use yii\db\ActiveRecord;

class TeacherAbsence extends ActiveRecord {

    public static function getDb()
    {
        return Yii::$app->db;
    }

    public static function tableName()
    {
        return 'teacher_absence';
    }
}