<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 16/9/28
 * Time: 15:47
 */
namespace console\models\record;

use yii\db\ActiveRecord;

class ClassLog extends ActiveRecord
{

    public static function tableName()
    {
        return 'class_log';
    }

}