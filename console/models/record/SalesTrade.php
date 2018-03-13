<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/9/22
 * Time: 下午4:07
 */
namespace console\models\record;

use Yii;
use yii\db\ActiveRecord;

class SalesTrade extends ActiveRecord
{

    public static function tableName()
    {
        return 'sales_trade';
    }

    public function addSalesTrade($salesInfo, $price, $list, $comment, $status, $uid = 0, $name = '')
    {
        $classType = $this->getClassType($list['time_class'], $list['time_end']);

        if(!empty($uid))
        {
            $total = $price;
            $descp = $comment;
            $name = "下属渠道：$name";
        }else {
            $total = $price;
            $descp = $comment;
            $name = $list['nick'];
        }

        $sql = "INSERT INTO sales_trade(uid, studentID, studentName, classID, classType, price, recordID, money, descp, comment, status, fromUid, time_created) VALUES(:uid,:studentID,:studentName,:classID,:classType,:price,:recordID,:money,:descp,:comment,:status,:fromUid,:time_created)";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':uid' => $salesInfo['id'],
                ':studentID' => $list['studentID'],
                ':studentName' => $name,
                ':classID' => $list['class_id'],
                ':classType' => $classType,
                ':price' => $price,
                ':recordID' => $list['record_id'],
                ':money' => $total,
                ':descp' => $descp,
                ':comment' => $comment,
                ':status' => $status,
                ':fromUid' => $uid,
                ':time_created' => $list['time_created']
            ])->execute();
    }

    private function getClassType($start, $end)
    {
        if($end - $start == 1500)
        {
            $classType = 25;
        }
        if($end - $start == 2700)
        {
            $classType = 45;
        }
        if($end - $start == 3000)
        {
            $classType = 50;
        }

        return $classType;
    }
}