<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:06
 */

namespace common\sources\write\salary;
use Yii;

Class WorkhourAccess implements IWorkhourAccess {

    public function updateIsPublish($timeStart, $timeEnd)
    {
        $sql = "UPDATE teacher_class_money SET is_publish = 1 WHERE time_class >= :timeStart AND time_class < :timeEnd";

        return Yii::$app->db->createCommand($sql)
                ->bindValues([
                    ':timeStart' => $timeStart,
                    ':timeEnd' => $timeEnd
                ])->execute();
    }
}