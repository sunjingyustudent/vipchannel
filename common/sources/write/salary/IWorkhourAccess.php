<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:05
 */

namespace common\sources\write\salary;

interface IWorkhourAccess {

    /**
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 更新课时费为已发布
     */
    public function updateIsPublish($timeStart, $timeEnd);
}