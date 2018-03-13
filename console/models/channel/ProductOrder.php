<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 16/9/20
 * Time: 14:20
 */
namespace console\models\channel;

use Yii;
use yii\db\ActiveRecord;

class ProductOrder extends ActiveRecord
{
    public static function tableName()
    {
        return 'product_order';
    }
    //获取购买人数
    public function getBuyNum($timeStart,$timeEnd)
    {
        $sql = "SELECT COUNT(DISTINCT uid) FROM product_order WHERE pay_status = 1 AND time_created >= :timeStart AND time_created <= :timeEnd ";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':timeStart' => $timeStart,':timeEnd'=>$timeEnd])
            ->queryScalar();
    }
}