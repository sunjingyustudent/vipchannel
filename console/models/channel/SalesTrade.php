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

class SalesTrade extends ActiveRecord
{
    public static function tableName()
    {
        return 'sales_trade';
    }
    //获取分享红包收入
    public function getShareFee($timeStart,$timeEnd)
    {
        $sql = "SELECT IFNULL(SUM(money),0) FROM sales_trade WHERE studentName = '红包收入' AND status != 3 AND is_deleted = 0 AND time_created >= :timeStart AND time_created <= :timeEnd";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':timeStart' => $timeStart,':timeEnd'=>$timeEnd])
            ->queryScalar();
    }

    //获取注册红包收入
    public function getRegisterFee($timeStart,$timeEnd)
    {
        $sql = "SELECT IFNULL(SUM(money),0) FROM sales_trade WHERE studentName = '注册学生收入' AND status != 3 AND is_deleted = 0 AND time_created >= :timeStart AND time_created <= :timeEnd";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':timeStart' => $timeStart,':timeEnd'=>$timeEnd])
            ->queryScalar();
    }

    //获取体验红包收入
    public function getExperienceFee($timeStart,$timeEnd)
    {
        $sql = "SELECT IFNULL(SUM(money),0) FROM sales_trade WHERE comment LIKE '%体验%' AND status != 3 AND is_deleted = 0 AND time_created >= :timeStart AND time_created <= :timeEnd";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':timeStart' => $timeStart,':timeEnd'=>$timeEnd])
            ->queryScalar();
    }
}