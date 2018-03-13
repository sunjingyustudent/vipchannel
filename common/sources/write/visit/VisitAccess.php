<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/4
 * Time: 下午1:19
 */
namespace common\sources\write\visit;

use Yii;
use common\models\music\ChannelVisitHistory;

class VisitAccess implements IVisitAccess
{

    public function addChannelVisit($id, $content, $timeNext, $nextContent, $classId, $isDone)
    {
        $model = new ChannelVisitHistory();
        $model->user_id_visit = Yii::$app->user->identity->id;
        $model->sale_channel_id = $id;
        $model->content = $content;
        $model->time_visit = time();
        $model->time_created = time();
        $model->time_next = empty($timeNext) ? 0
            : strtotime($timeNext);
        $model->next_content = $nextContent;
        $model->class_id = $classId;
        $model->is_done = $isDone;
        return $model->save();
    }
}
