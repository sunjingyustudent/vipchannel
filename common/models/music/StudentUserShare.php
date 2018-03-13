<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/14
 * Time: 上午9:51
 */
namespace common\models\music;

use Yii;
use yii\db\ActiveRecord;

class StudentUserShare extends ActiveRecord {

    public static function getDb()
    {
        return Yii::$app->db;
    }

    public static function tableName()
    {
        return 'student_user_share';
    }
}