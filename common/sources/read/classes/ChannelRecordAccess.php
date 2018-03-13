<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/13
 * Time: 上午11:21
 */
namespace common\sources\read\classes;

use Yii;

Class ChannelRecordAccess implements IChannelRecordAccess {


    public function getChannelIds($class_id)
    {
        $sql = "SELECT channel_id FROM `class_channel` WHERE class_id = :class_id";

        return Yii::$app->db->createCommand($sql)
                    ->bindValue(':class_id', $class_id)
                    ->queryColumn();
    }

    public function getRecordUrlByChannelId($channel_id)
    {
        $sql = "SELECT record_url FROM class_channel_record WHERE channel_id = :channel_id";

        return Yii::$app->db->createCommand($sql)
                    ->bindValue(':channel_id', $channel_id)
                    ->queryColumn();
    }
}
