<?php
/**
 * vip陪练永久二维码
 */
namespace common\models;

use yii\db\ActiveRecord;

class PnlQrCode extends ActiveRecord
{

    public static function tableName()
    {
        return 'pnl_qr_code';
    }
}
