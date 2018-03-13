<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 16/7/19
 * Time: 下午7:55
 */

namespace common\models;

use yii\db\ActiveRecord;

class ErrorAPIBean extends ActiveRecord
{
    public static function getDb()
    {
        return \Yii::$app->db_log;
    }

    public static function tableName()
    {
        return 'error_api_logs';
    }

}