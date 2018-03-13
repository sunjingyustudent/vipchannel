<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/9
 * Time: 下午4:33
 */
namespace common\sources\read\music;

use Yii;

interface IMusicAccess {
        /**
     * 获取课程乐谱
     * @param  $class_id
     * @return array
     */
    public function getMusicLibrary($class_id);
}