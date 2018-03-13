<?php
/**
 * Created by phpStorm.
 * User: xl
 * Date: 2017/2/21
 * Time: 18:32
 */
namespace console\models\teacher;

use Yii;
use yii\db\ActiveRecord;

class PlaceHourRate extends ActiveRecord
{
    public static function tableName()
    {
        return 'place_hour_rate';
    }

    public static function intoHourRate($data)
    {
        return Yii::$app->db->createCommand()->batchInsert('place_hour_rate',
            ['hour','time_day','rate','info','place_id'],
            $data)->execute();
    }
}