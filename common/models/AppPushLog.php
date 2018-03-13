<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/10/28
 * Time: 上午11:48
 */
namespace common\models;

use yii\db\ActiveRecord;

class AppPushLog extends ActiveRecord
{
    public static function getDb()
    {
        return \Yii::$app->db_log;
    }

    public static function tableName()
    {
        return 'app_push_log';
    }

}