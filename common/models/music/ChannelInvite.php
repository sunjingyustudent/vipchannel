<?php

namespace common\models\music;

use Yii;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "channel_invite".
 *
 * @property integer $id
 * @property integer $private_code
 * @property string $open_id
 * @property integer $create_time
 * @property integer $type
 */
class ChannelInvite extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'channel_invite';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['private_code', 'create_time', 'type'], 'integer'],
            [['open_id'], 'string', 'max' => 100],
            [['open_id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'private_code' => 'Private Code',
            'open_id' => 'Open ID',
            'create_time' => 'Create Time',
            'type' => 'Type',
        ];
    }
}
