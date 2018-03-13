<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/13
 * Time: 上午11:20
 */
namespace common\sources\read\classes;

interface IChannelRecordAccess {

    /**
     * @param $class_id
     * @return mixed
     * @author xl
     * 通过课ID获取channel_id
     */
    public function getChannelIds($class_id);

    /**
     * @param $channel_id
     * @return mixed
     * @author xl
     * 通过channel_id获取录音URL
     */
    public function getRecordUrlByChannelId($channel_id);

}