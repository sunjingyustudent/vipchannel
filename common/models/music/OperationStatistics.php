<?php
/**
 * Created by Sublime.
 * User: wk
 * Date: 16/12/26
 * Time: 14:26
 */
namespace common\models\music;

use Yii;
use yii\db\ActiveRecord;

class OperationStatistics extends ActiveRecord {

    public static function getDb()
    {
        return Yii::$app->db;
    }

    public static function tableName()
    {
        return 'operation_statistics';
    }
}