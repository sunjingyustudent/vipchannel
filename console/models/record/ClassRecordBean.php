<?php
/**
 * Created by PhpStorm.
 * User: 123
 * Date: 6/22/16
 * Time: 1:33 PM
 */

namespace console\models\record;

use yii\db\ActiveRecord;

class ClassRecordBean extends ActiveRecord
{

    public static function tableName()
    {
        return 'class_record';
    }

}