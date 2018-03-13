<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/10/25
 * Time: 下午1:20
 */
namespace console\models\queue;

use Yii;
use yii\db\ActiveRecord;

class ClassPushDevice extends ActiveRecord {

    public static function tableName()
    {
        return 'class_push_device';
    }
}