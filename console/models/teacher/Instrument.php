<?php
/**
 * Created by phpStorm.
 * User: xl
 * Date: 2017/2/22
 * Time: 15:25
 */
namespace console\models\teacher;

use Yii;
use yii\db\ActiveRecord;

class Instrument extends ActiveRecord
{
    public static function tableName()
    {
        return 'instrument';
    }

    public static function getInstrument(){
        $sql = "SELECT * FROM instrument";

        return Yii::$app->db->createCommand($sql)
            ->queryAll();
    }
}