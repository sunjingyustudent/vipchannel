<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 16/10/16
 * Time: 下午11:41
 */
namespace console\models\quality;

use Yii;
use yii\db\ActiveRecord;

class QualityLog extends ActiveRecord
{
    Public static function getDb()
    {
        Return Yii::$app->db_log;
    }

    public static function tableName()
    {
        return 'quality_logs';
    }
}