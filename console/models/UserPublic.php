<?php
/**
 * Created by PhpStorm.
 * User:
 * Date: 17/06/15
 * Time: 上午9:40
 */
namespace console\models;

use Yii;
use yii\db\ActiveRecord;

class UserPublic extends ActiveRecord
{

    public static function tableName()
    {
        return 'user_public';
    }
}