<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/10/12
 * Time: 上午11:28
 */
namespace console\models;

use Yii;
use yii\db\ActiveRecord;

class ProductOrder extends ActiveRecord
{

    public static function tableName()
    {
        return 'product_order';
    }
    
    public function updateClassNum($orderId,$num)
    {
        $sql = "UPDATE product_order SET class_num = :num WHERE id = :id";
        
        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':num' => $num,
                ':id' => $orderId
            ])->execute();
    }
}