<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 17/1/3
 * Time: 10:30
 */
namespace common\sources\write\complain;

use Yii;


Class FeedbackAccess implements IFeedbackAccess
{
    public function updateFeedbackStatus($request)
    {
        $sql = "UPDATE sales_feedback SET status = :status, context = :context, time_updated = :time_updated "
               . " WHERE id = :id";

        $result = Yii::$app->db->createCommand($sql)
                ->bindValues([
                    ':id' => $request['id'],
                    ':status' => 1,
                    ':context' => $request['context'],
                    ':time_updated' => time()
                ])->execute();

        if($result >= 1){
            return 1;
        }else{
            return 0 ;
        }
    }
}