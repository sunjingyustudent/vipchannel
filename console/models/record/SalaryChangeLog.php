<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/9/22
 * Time: 下午4:15
 */
namespace console\models\record;

use Yii;
use yii\db\ActiveRecord;

class SalaryChangeLog extends ActiveRecord
{

    public static function tableName()
    {
        return 'salary_change_log';
    }
    
}