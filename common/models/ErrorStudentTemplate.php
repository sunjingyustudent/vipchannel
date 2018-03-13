<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/9
 * Time: 下午3:46
 */
namespace common\models;

use yii\db\ActiveRecord;

class ErrorStudentTemplate extends ActiveRecord
{
    public static function getDb()
    {
        return \Yii::$app->db_log;
    }

    public static function tableName()
    {
        return 'error_student_template';
    }

}