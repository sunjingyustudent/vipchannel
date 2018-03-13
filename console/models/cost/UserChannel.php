<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/11/16
 * Time: 上午9:40
 */
namespace console\models\cost;

use Yii;
use yii\db\ActiveRecord;

class UserChannel extends ActiveRecord
{

    public static function tableName()
    {
        return 'user_channel';
    }
}