<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 16/10/16
 * Time: 下午11:41
 */
namespace console\models\cost;

use Yii;
use yii\db\ActiveRecord;

class SalesArticleShare extends ActiveRecord
{
    public static function tableName()
    {
        return 'sales_article_share';
    }
}