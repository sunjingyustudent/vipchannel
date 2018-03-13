<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/14
 * Time: 上午10:55
 */
namespace common\models\music;

use Yii;
use yii\db\ActiveRecord;

class VisitHistory extends ActiveRecord {

    public static function getDb()
    {
        return Yii::$app->db;
    }

    public static function tableName()
    {
        return 'visit_history';
    }
}