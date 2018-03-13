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

class UserInit extends ActiveRecord
{
    public static function tableName()
    {
        return 'user_init';
    }

    //获取关注人数
    public function getSubscribeNum($timeStart,$timeEnd)
    {
        $sql = "SELECT COUNT(openid) FROM user_init  WHERE is_deleted = 0 AND subscribe_time >= :timeStart AND subscribe_time <= :timeEnd";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':timeStart' => $timeStart,':timeEnd'=>$timeEnd])
            ->queryScalar();
    }

    //获取有效分享数
    public function getShareInit($timeStart,$timeEnd)
    {
        $sql = "SELECT COUNT(DISTINCT sales_id) FROM user_init WHERE sales_id > 0 AND is_deleted = 0 AND subscribe_time >= :timeStart AND subscribe_time <= :timeEnd";
        return Yii::$app->db->createCommand($sql)
            ->bindValues([':timeStart' => $timeStart,':timeEnd'=>$timeEnd])
            ->queryScalar();
    }
}