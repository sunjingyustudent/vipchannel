<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2017/3/31
 * Time: 16:34
 */
namespace console\models\record ;
use yii\db\ActiveRecord ;

class ClassNet extends ActiveRecord{
    public static function tableName()
    {
        return 'class_net';
    }
}