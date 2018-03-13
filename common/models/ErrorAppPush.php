<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/10/27
 * Time: 下午3:40
 */
namespace common\models;

use yii\db\ActiveRecord;

class ErrorAppPush extends ActiveRecord
{
    public static function getDb()
    {
        return \Yii::$app->db_log;
    }

    public static function tableName()
    {
        return 'error_app_push';
    }

}