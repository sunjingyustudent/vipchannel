<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/4
 * Time: 下午12:32
 */
namespace common\sources\read\visit;

use common\models\music\ChannelVisitHistory;
use common\models\music\VisitHistory;
use Yii;

class VisitAccess implements IVisitAccess
{
    public function countVisitByStudentId($studentId)
    {
    }


    public function getSaleChannelVisitInfo($saleChannelId, $num)
    {
        return ChannelVisitHistory::find()
            ->alias('cv')
            ->select('cv.id, cv.user_id_visit, cv.time_visit, cv.time_next, cv.content, cv.next_content, cv.class_id, cv.is_done, cr.time_class, u.nick')
            ->leftJoin('class_room AS cr', 'cv.class_id = cr.id')
            ->leftJoin('user AS u', 'cr.student_id = u.id')
            ->where('cv.sale_channel_id = :sale_channel_id', [
                ':sale_channel_id' => $saleChannelId
            ])
            ->orderBy('cv.time_visit DESC')
            ->offset(($num - 1) * 1)
            ->limit(1)
            ->asArray()
            ->all();
    }

    public function getSaleChannelVisitCount($saleChannelId, $now)
    {
        $obj = ChannelVisitHistory::find()
            ->where('sale_channel_id = :sale_channel_id ', [
                ':sale_channel_id' => $saleChannelId
            ]);
        if (!empty($now)) {
            //今天之前的数量
            $obj = $obj->andWhere('user_id_visit = :user_id_visit AND is_done = 0 AND time_next < :time AND time_next > 0', [
                ':time' => $now,
                ':user_id_visit' => Yii::$app->user->identity->id
            ]);
        }

        return $obj->count();
    }

    public function getSaleChannelVisitNoDoneCount($saleChannelId, $now)
    {
        return ChannelVisitHistory::find()
            ->where('sale_channel_id = :sale_channel_id AND is_done = 0 AND time_next < :time AND class_id > 0', [
                ':sale_channel_id' => $saleChannelId,
                ':time' => $now
            ])
            ->count();
    }

    public function getSaleChannelVisitInfoByTime($saleChannelId, $time, $type)
    {
        return ChannelVisitHistory::find()
                    ->select('time_visit, time_next, content, next_content')
                    ->where('sale_channel_id = :sale_channel_id ', [':sale_channel_id' => $saleChannelId])
                    ->where(empty($type) ? 'time_visit < :time' : 'time_visit > :time', [':time' => $time])
                    ->orderBy('time_visit DESC')
                    ->one();
    }

    public function getRecentVisitRecord($studentId)
    {
        return VisitHistory::find()
                        ->select('content, time_created')
                        ->where(['student_id' => $studentId])
                        ->orderBy('time_created DESC')
                        ->asArray()
                        ->one();
    }
}
