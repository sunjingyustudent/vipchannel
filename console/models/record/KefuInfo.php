<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 16/11/14
 * Time: 16:46
 */
namespace console\models\record;

use Yii;
use yii\db\ActiveRecord;

class KefuInfo extends ActiveRecord
{

    public static function tableName()
    {
        return 'kefu_info';
    }

    public function isExit($timeStart)
    {
        $sql = "SELECT id FROM kefu_info WHERE timeDay = :timeStart";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':timeStart' => $timeStart])
            ->queryScalar();
    }

    public function intoKefuInfo($kefu_data)
    {
        $sql = "INSERT INTO kefu_info(kefu_id,day_user,buy_num,day_to_buy_rate,ex_num,visit,visit_rate,connect_num,connect_rate,price_per,rebuy,rebuy_per,price_first,price_rebuy,timeDay)"
            . " VALUES(:kefu_id,:day_user,:buy_num,:day_to_buy_rate,:ex_num,:visit,:visit_rate,:connect_num,:connect_rate,:price_per,:rebuy,:rebuy_per,:price_first,:price_rebuy,:timeDay)";

        return Yii::$app->db->createCommand($sql)
                        ->bindValues([
                            ':kefu_id' => $kefu_data['kefu_id'],
                            ':day_user' => $kefu_data['day_user'],
                            ':buy_num' => $kefu_data['buy_num'],
                            ':day_to_buy_rate' => $kefu_data['day_to_buy_rate'],
                            ':ex_num' => $kefu_data['ex_num'],
                            ':visit' => $kefu_data['visit'],
                            ':visit_rate' => $kefu_data['visit_rate'],
                            ':connect_num' => $kefu_data['connect_num'],
                            ':connect_rate' => $kefu_data['connect_rate'],
                            ':price_per' => $kefu_data['price_per'],
                            ':rebuy' => $kefu_data['rebuy'],
                            ':rebuy_per' => $kefu_data['rebuy_per'],
                            ':price_first' => $kefu_data['price_first'],
                            ':price_rebuy' => $kefu_data['price_rebuy'],
                            ':timeDay' => $kefu_data['timeDay']
                        ])->execute();
    }

}