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

class BusinessStatistics extends ActiveRecord
{
    public static function tableName()
    {
        return 'business_statistics';
    }

    public function isExit($timeStart)
    {
        $sql = "SELECT id FROM business_statistics WHERE time = :timeStart";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':timeStart' => $timeStart])
            ->queryScalar();
    }

    public function intoTable($share_all,$share_init,$subscribe_num,$register_num,$experience_num,$buy_num,$share_fee,$register_fee,$experience_fee,$time)
    {
        $query = "INSERT INTO business_statistics"
            . "(share_all,share_init,subscribe_num,register_num,experience_num,buy_num,share_fee,register_fee,experience_fee,time)"
            . " VALUES(:share_all,:share_init,:snum,:rnum,:enum,:bnum,:sfee,:rfee,:efee,:time) ";

        return Yii::$app->db->createCommand($query)
            ->bindValues([':share_all'=>$share_all,':share_init'=>$share_init,':snum' => $subscribe_num,':rnum'=>$register_num,':enum'=>$experience_num,':bnum'=>$buy_num,':sfee'=>$share_fee,':rfee'=>$register_fee,':efee'=>$experience_fee,':time'=>$time])
            ->execute();
    }
}