<?php
/**
<<<<<<< HEAD
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/13
 * Time: 下午6:22
 */

namespace common\models\music;

use Yii;
use yii\db\ActiveRecord;

class UserPublic extends ActiveRecord {

    public static function getDb()
    {
        return Yii::$app->db;
    }

    public static function tableName()
    {
        return 'user_public';
    }
}