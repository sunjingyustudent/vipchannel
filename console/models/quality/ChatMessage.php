<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 16/10/16
 * Time: 下午11:41
 */
namespace console\models\quality;

use Yii;
use yii\db\ActiveRecord;

class ChatMessage extends ActiveRecord
{

    public static function tableName()
    {
        return 'chat_message';
    }
}