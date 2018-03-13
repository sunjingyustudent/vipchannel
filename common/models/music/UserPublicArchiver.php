<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/14
 * Time: 上午10:18
 */
namespace common\models\music;

use Yii;
use yii\db\ActiveRecord;

class UserPublicArchiver extends ActiveRecord {

    public static function getDb()
    {
        return Yii::$app->db;
    }

    public static function tableName()
    {
        return 'user_public_archiver';
    }
}