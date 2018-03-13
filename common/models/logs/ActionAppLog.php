<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 16/7/19
 * Time: 下午7:55
 */

namespace common\models\logs;

use yii\db\ActiveRecord;

class ActionAppLog extends ActiveRecord
{
    public static function getDb()
    {
        return \Yii::$app->db_log;
    }

    public static function tableName()
    {
        return 'action_app_logs';
    }

}