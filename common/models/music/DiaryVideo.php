<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/15
 * Time: 上午10:32
 */
namespace common\models\music;

use Yii;
use yii\db\ActiveRecord;

class DiaryVideo extends ActiveRecord {

    public static function getDb()
    {
        return Yii::$app->db;
    }

    public static function tableName()
    {
        return 'diary_video';
    }
}