<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/12
 * Time: 下午5:23
 */
namespace common\models\music;

use Yii;
use yii\db\ActiveRecord;

class UserAccount extends ActiveRecord {

    public static function getDb()
    {
        return Yii::$app->db;
    }

    public static function tableName()
    {
        return 'user_account';
    }
}