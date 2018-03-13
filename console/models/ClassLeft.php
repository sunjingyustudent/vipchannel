<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/10/12
 * Time: 下午2:24
 */
namespace console\models;

use Yii;
use yii\db\ActiveRecord;

class ClassLeft extends ActiveRecord
{

    public static function tableName()
    {
        return 'class_left';
    }

    public function addGiveClassTimes($uid, $instrumentId, $timeType, $price, $amount)
    {
        $sql = "SELECT COUNT(id) FROM class_left WHERE user_id = :uid AND type = 2 AND instrument_id = :instrument AND time_type = :type";

        $count = Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':uid' => $uid,
                ':instrument' => $instrumentId,
                ':type' => $timeType
            ])->queryScalar();

        if(empty($count))
        {
            $sql = "INSERT INTO class_left(user_id, type, instrument_id, time_type, name, price, total_amount, amount, ac_amount) VALUES(:uid, :type, :instrument, :time_type, :name, :price, :total_amount, :amount, :ac_amount)";

            return Yii::$app->db->createCommand($sql)
                ->bindValues([
                    ':uid' => $uid,
                    ':type' => 2,
                    ':instrument' => $instrumentId,
                    ':time_type' => $timeType,
                    ':name' => '赠送课',
                    ':price' => $price,
                    ':total_amount' => $amount,
                    ':amount' => $amount,
                    ':ac_amount' => $amount
                ])->execute();
        }

        $sql = "UPDATE class_left SET total_amount = total_amount + :total_amount, amount = amount + :amount, ac_amount = ac_amount + :ac_amount WHERE user_id = :uid AND type = 2 AND instrument_id = :instrument AND time_type = :time_type";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':total_amount' => $amount,
                ':amount' => $amount,
                ':ac_amount' => $amount,
                ':uid' => $uid,
                ':instrument' => $instrumentId,
                ':time_type' => $timeType,
            ])->execute();
    }

    public function reduceClassLeft($leftId,$amount,$ac_amount)
    {
        $sql = "UPDATE class_left SET amount = amount - :amount, ac_amount = ac_amount - :ac_amount WHERE id = :id";
        
        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':amount' => $amount,
                ':ac_amount' => $ac_amount,
                ':id' => $leftId
            ])->execute();
    }
}