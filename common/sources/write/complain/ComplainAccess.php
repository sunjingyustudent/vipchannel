<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 17/1/3
 * Time: 10:30
 */
namespace common\sources\write\complain;


use common\models\music\Complain;
use Yii;
use yii\db\ActiveRecord;

Class ComplainAccess implements IComplainAccess
{
    public function relateClass($complain_id, $class_id)
    {
        $sql = "UPDATE complain SET class_id = :class_id, time_update = :time_update WHERE id = :complain_id";

        $re =  Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':class_id' => $class_id,
                ':complain_id' => $complain_id,
                ':time_update' => time()
            ])->execute();

        if($re >= 1)
        {
            return 1;
        }else{
            return 0;
        }
    }

    public function updateComplainStatus($request)
    {
        $sql = "UPDATE complain SET status = :status, teacher_context = :teacher_context, teacher_remark = :teacher_remark, time_update = :time_update  "
                 . " WHERE id = :id";

        return  Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':id' => $request['id'],
                ':status' => 1,
                ':teacher_context' => $request['teacher_context'],
                ':teacher_remark' => $request['teacher_remark'],
                ':time_update' => time()
            ])->execute();
    }

    public function doAddComplain($open_id,$request)
    {
        $sql = "insert into complain(open_id, content, status, tag, time_created)"
            . " VALUES (:open_id, :content, :status, :tag, :time_created)";

        Yii::$app->db->createCommand($sql)
                    ->bindValues([
                        ':open_id' => $open_id,
                        ':content' => $request['content'],
                        ':status' => 0,
                        ':tag' => 1,
                        ':time_created' => time()
                    ])->execute();


        return Yii::$app->db->getLastInsertID();
    }

    public function updateComplainTag($complain_id, $tag)
    {
        $sql = "UPDATE complain SET tag = :tag "
                 . " WHERE id = :id ";

        $result =  Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':id' => $complain_id,
                ':tag' => $tag
            ])->execute();
        

        if ($result >= 1 )
        {
            return 1;
        }else{
            return 0;
        }
    }

    public function updateComplainRewardRecordId($complain_id, $reward_record_id)
    {
        $sql = "UPDATE complain SET status = :status, reward_record_id = :reward_record_id, time_update = :time_update  "
                 . " WHERE id = :id ";

        $result =  Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':id' => $complain_id,
                ':status' => 2,
                ':reward_record_id' => $reward_record_id,
                ':time_update' => time()
            ])->execute();
        

        if($result >= 1){
            return 1;
        }else{
            return 0 ;
        }
    }

    /**
     * 处理投诉信息
     * @param  $id
     * @return int
     */
    public function doUpdateComplaintInfo($request, $teacher_context)
    {
        $complain = Complain::findOne(['id'=>$request['id']]);

        if(empty($teacher_context))
        {
            $complain->status = 1;
        }else{
            $complain->status = 3;
        }
        $complain->kefu_remark = $request['kefu_remark'];
        $complain->kefu_context = $request['kefu_context'];
        $complain->time_update = time();

        if($complain->save()){
            return 1;
        }else{
            return 0 ;
        }
    }

}