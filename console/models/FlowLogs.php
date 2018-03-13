<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/10/16
 * Time: 下午11:41
 */
namespace console\models;

use Yii;
use yii\db\ActiveRecord;

class FlowLogs extends ActiveRecord
{
    Public static function getDb()
    {
        Return Yii::$app->db_log;
    }

    public static function tableName()
    {
        return 'flow_logs';
    }
}