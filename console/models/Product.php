<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/10/16
 * Time: 下午11:41
 */
namespace console\models;

use Yii;
use yii\db\ActiveRecord;

class Product extends ActiveRecord
{

    public static function tableName()
    {
        return 'product';
    }
}