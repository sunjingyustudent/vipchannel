<?php
/**
 * Created by phpStorm.
 * User: xl
 * Date: 2017/3/14
 * Time: 13:54
 */
namespace common\logics\classes;

interface IChannelRecord {

    /**
     * @param $class_id
     * @return mixed
     * @author xl
     * 查看课程录音记录
     */
    public function getClassChannelRecord($class_id);
}