<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/11/14
 * Time: 下午2:45
 */
namespace console\models;

use Yii;
use yii\db\ActiveRecord;

class ChatMessage extends ActiveRecord
{

    public static function tableName()
    {
        return 'chat_message';
    }
}