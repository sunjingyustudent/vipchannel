<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/2 0002
 * Time: 下午 5:27
 */
namespace common\models\music;

use Yii;
use yii\db\ActiveRecord;

class TeacherGradeRuleLog extends ActiveRecord {

    public static function getDb()
    {
        return Yii::$app->db;
    }

    public static function tableName()
    {
        return 'teacher_grade_rule_log';
    }
}