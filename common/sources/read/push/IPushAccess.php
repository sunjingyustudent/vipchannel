<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/9
 * Time: 下午2:24
 */
namespace common\sources\read\push;

use Yii;
use yii\db\ActiveRecord;

interface IPushAccess {

    /**
     * @param $type
     * @param $userId
     * @return mixed
     * @created by Jhu
     * 获取推送客户端信息
     */
    public function getPushClientInfo($type, $userId);
}