<?php
/**
 * vip陪练永久二维码使用情况
 */
namespace common\models;

use yii\db\ActiveRecord;

class PnlCodeUsed extends ActiveRecord
{

    public static function tableName()
    {
        return 'pnl_code_used';
    }
}
