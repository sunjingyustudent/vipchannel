<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/9/22
 * Time: 下午3:48
 */
namespace console\models\record;

use Yii;
use yii\db\ActiveRecord;

class ClassEditHistory extends ActiveRecord
{

    public static function tableName()
    {
        return 'class_edit_history';
    }

    public function getFirstBuyNum($time_start,$time_end)
    {
        $sql = "SELECT student_id FROM class_edit_history"
            . " WHERE price > 0 AND is_add = 1 AND time_created >= :time_start AND time_created < :time_end AND student_id NOT IN"
            . " (SELECT student_id FROM class_edit_history WHERE price > 0 AND is_add = 1 AND time_created < :time_start GROUP BY student_id)"
            . " GROUP BY student_id";

        return Yii::$app->db->createCommand($sql)
                ->bindValues([':time_start'=>$time_start,':time_end'=>$time_end])
                ->queryColumn();
    }

    //首月复购人数
    public function getFirstRe($time_start,$time_end)
    {
        $sql = "SELECT COUNT(student_id) FROM"
            . " (SELECT COUNT(id) AS num,student_id FROM class_edit_history"
            . " WHERE is_add = 1  AND price > 0 AND time_created >= :time_start AND time_created < :time_end AND"
            . " student_id NOT IN (SELECT student_id FROM class_edit_history"
            . " WHERE price > 0 AND is_add = 1 AND time_created < :time_start GROUP BY student_id) GROUP BY student_id)"
            . " AS t WHERE num > 1";

        return Yii::$app->db->createCommand($sql)
                        ->bindValues([':time_start'=>$time_start,':time_end'=>$time_end])
                        ->queryScalar();
    }

    public function getReBuyNum($time_start,$time_end,$buy_ids)
    {
        $sql = "SELECT student_id FROM class_edit_history"
            . " WHERE price > 0 AND is_add = 1  AND time_created >= :time_start and time_created < :time_end"
            . " AND student_id IN (".implode(',',$buy_ids).") GROUP BY student_id";

        return Yii::$app->db->createCommand($sql)
                ->bindValues([':time_start'=>$time_start,':time_end'=>$time_end])
                ->queryColumn();
    }

    public function updateOrderId($id,$_orderId)
    {
        $sql = "UPDATE class_edit_history SET order_id = :order_id WHERE id = :id";
        
        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':order_id' => $_orderId,
                ':id' => $id
            ])->execute();
    }


    public function getFirstPrice($kefu_id,$timeStart,$timeEnd)
    {
        $sql = "SELECT h.student_id, sum(h.price) as price FROM class_edit_history AS h LEFT JOIN user_public_info as u ON u.user_id = h.student_id"
            . " WHERE h.price > 0 AND h.is_add = 1 AND h.is_deleted = 0 AND h.is_success = 1 AND h.time_created >= :timeStart AND h.time_created < :timeEnd AND u.kefu_id = :kefu_id"
            . " AND h.student_id NOT IN (SELECT student_id FROM class_edit_history WHERE price > 0 AND is_add = 1 AND is_success = 1 AND is_deleted = 0 AND time_created < :timeStart GROUP BY student_id)"
            . "GROUP BY h.student_id";

        return Yii::$app->db->createCommand($sql)
                            ->bindValues([':kefu_id' => $kefu_id, ':timeStart' => $timeStart, ':timeEnd' => $timeEnd])
                            ->queryAll();
    }

    public function getReBuy($timeStart,$timeEnd,$kefu_id)
    {
        $sql= "SELECT student_id, sum(price) as re_price FROM class_edit_history AS h LEFT JOIN user_public_info as u ON u.user_id = h.student_id"
            . " WHERE h.price > 0 AND h.is_add = 1 AND h.is_deleted = 0 AND h.is_success = 1 AND h.time_created >= :timeStart AND h.time_created < :timeEnd AND u.kefu_id = :kefu_id"
            . " AND h.student_id IN (SELECT student_id FROM class_edit_history WHERE price > 0 AND is_add = 1 AND is_deleted = 0 AND is_success = 1 AND time_created < :timeStart GROUP BY student_id)"
            . " GROUP BY h.student_id";

        return Yii::$app->db->createCommand($sql)
                        ->bindValues([
                            ':kefu_id' => $kefu_id,
                            ':timeStart' => $timeStart,
                            ':timeEnd' => $timeEnd
                        ])->queryAll();
    }

    public function getExperienceByKefu($studentids,$timeStart,$timeEnd)
    {
        $sql = "SELECT student_id FROM class_edit_history WHERE is_ex_class = 1 AND is_deleted = 0 AND status = 1 AND time_class >= :timeStart AND time_class < :timeEnd AND student_id IN (".implode(',',$studentids).") GROUP BY student_id";

        return Yii::$app->db->createCommand($sql)
                        ->bindValues([':timeStart'=>$timeStart,':timeEnd'=>$timeEnd])
                        ->queryColumn();
    }

    public function getBuyByKefu($kefu_id)
    {
        $sql = "SELECT count(DISTINCT student_id) FROM class_edit_history AS h LEFT JOIN user_public_info as u ON u.user_id = h.student_id WHERE h.price > 0 AND h.is_add = 1 AND h.is_deleted = 0 AND h.is_success = 1 AND u.kefu_id = :kefu_id";

        return Yii::$app->db->createCommand($sql)
                        ->bindValue(':kefu_id',$kefu_id)
                        ->queryScalar();
    }

}