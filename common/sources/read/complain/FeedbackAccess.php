<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 17/1/3
 * Time: 10:26
 */
namespace common\sources\read\complain;
use Yii;
use yii\db\ActiveRecord;
use common\models\music\SalesFeedback;


Class FeedbackAccess implements IFeedbackAccess
{
 public function getFeedbackCount($status)
    {
        $sql = "SELECT COUNT(*) AS count FROM sales_feedback WHERE"
            . (empty($status) ? " status = 0" : " status = 1");

        return Yii::$app->db->createCommand($sql)
            ->queryScalar();
    }
    
      public function getFeedbackInfo($status, $pagination)
    {
        
        return SalesFeedback::find()
            ->alias('s')
            ->select('s.id, s.feedback, s.openID, s.status, s.time_created, s.context, sc.nickname as teacher_name, sc.username as teacher_mobile, sc.head, stu.nick as student_name, stu.mobile as student_mobile')
            ->leftJoin('sales_channel as sc','sc.id = s.uid')
            ->leftJoin('user as stu','stu.id = s.studentID')
            ->where(empty($status) ? 's.status = 0' : 's.status = 1')
            ->orderBy(empty($status) ? 's.time_created desc' : 's.time_updated desc')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->asArray()
            ->all();
    }
    
    public function getFeedbackById($id)
    {
        $sql = "SELECT s.*, sc.bind_openid FROM sales_feedback AS s"
            . " LEFT JOIN sales_channel AS sc ON sc.id = s.uid WHERE s.id = :id";

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':id', $id)
            ->queryOne();
    }
}