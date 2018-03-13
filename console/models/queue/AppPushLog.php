<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/10/27
 * Time: 上午11:12
 */
namespace console\models\queue;

use Yii;
use yii\db\ActiveRecord;

class AppPushLog extends ActiveRecord {

    Public static function getDb()
    {
        Return Yii::$app->db_log;
    }

    public static function tableName()
    {
        return 'app_push_log';
    }
}