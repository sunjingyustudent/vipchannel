<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 16/7/19
 * Time: 下午7:55
 */

namespace common\models;

use yii\db\ActiveRecord;

class ActionLogBean extends ActiveRecord
{
    public static function getDb()
    {
        return \Yii::$app->db_log;
    }

    public static function tableName()
    {
        return 'action_logs';
    }

}