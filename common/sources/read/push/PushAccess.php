<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/9
 * Time: 下午2:25
 */
namespace common\sources\read\push;

use common\models\music\ClassPushDevice;
use Yii;
use yii\db\ActiveRecord;

Class PushAccess implements IPushAccess {

    public function getPushClientInfo($type, $userId)
    {
        return ClassPushDevice::find()
            ->select('clientID, clientType , is_stable_version , deviceInfor')
            ->where([
                'uid' => $userId,
                'type' => $type
            ])->asArray()->one();
    }
}