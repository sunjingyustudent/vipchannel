<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2016/12/15
 * Time: 13:51
 */

namespace common\models\logs;

use yii\db\ActiveRecord;

class QualityLogs extends ActiveRecord
{
    public static function getDb()
    {
        return \Yii::$app->db_log;
    }

    public static function tableName()
    {
        return 'quality_logs';
    }

}