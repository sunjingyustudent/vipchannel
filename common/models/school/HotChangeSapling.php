<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2016/12/15
 * Time: 13:51
 */

namespace common\models\school;

use yii\db\ActiveRecord;

class HotChangeSapling extends ActiveRecord
{
    public static function getDb()
    {
        return \Yii::$app->db_pnl;
    }

    public static function tableName()
    {
        return 'hot_change_sapling';
    }

}