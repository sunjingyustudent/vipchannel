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

class User extends ActiveRecord
{
    //获取注册人数
    public function getRegisterNum($timeStart,$timeEnd)
    {
        $sql = "SELECT COUNT(id) FROM user WHERE is_disabled = 0 AND time_created >= :timeStart AND time_created <= :timeEnd";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':timeStart' => $timeStart,':timeEnd'=>$timeEnd])
            ->queryScalar();
    }

    public function getList()
    {
        $sql = "SELECT u.id as user_id, u.mobile, u.nick, u.time_created, u.kefu_id, IFNULL(w.openid,'') as openid, IFNULL(v.counts,0) as visit_count, IFNULL(l.counts,0) as buy_count, u.city, u.province, u.birth, u.last_level FROM user AS u"
            . " LEFT JOIN wechat_acc AS w ON w.uid = u.id"
            . " LEFT JOIN (SELECT COUNT(id) as counts, student_id FROM visit_history GROUP BY student_id) AS v ON v.student_id = u.id"
            . " LEFT JOIN (SELECT COUNT(id) as counts, user_id FROM class_left WHERE type = 3 AND left_bit & 4 = 0 GROUP BY user_id) AS l ON l.user_id = u.id"
            . " WHERE u.is_auth = 1 AND u.is_disabled = 0";
        
        return Yii::$app->db->createCommand($sql)->queryAll();
    }
}