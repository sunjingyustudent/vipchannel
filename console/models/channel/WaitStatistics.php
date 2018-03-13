<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/11/16
 * Time: 上午9:40
 */
namespace console\models\channel;

use Yii;
use yii\db\ActiveRecord;

class  WaitStatistics extends ActiveRecord
{

    public static function tableName()
    {
        return 'wait_statistics';
    }
}