<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 16/11/23
 * Time: 14:07
 */

namespace console\models\record;

use yii\db\ActiveRecord;

class CoursekefuBindHistory extends ActiveRecord
{

    public static function tableName()
    {
        return 'coursekefu_bind_history';
    }

}