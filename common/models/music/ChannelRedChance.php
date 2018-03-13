<?php

namespace common\models\music;

use Yii;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "channel_red_chance".
 *
 * @property integer $id
 * @property integer $message_type
 * @property integer $rand_start
 * @property integer $rand_end
 * @property string $amount
 * @property string $is_delete
 * @property string $type
 */
class ChannelRedChance extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'channel_red_chance';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message_type', 'rand_start', 'rand_end', 'is_delete', 'type'], 'integer'],
            [['amount'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'message_type' => 'Message Type',
            'rand_start' => 'Rand Start',
            'rand_end' => 'Rand End',
            'amount' => 'Amount',
            'is_delete' => 'IS DELETE',
            'type'  => 'TYPE',
        ];
    }
}
