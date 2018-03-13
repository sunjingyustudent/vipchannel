<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 16/9/21
 * Time: 10:26
 */
namespace console\models\channel;

use Yii;
use yii\db\ActiveRecord;

class SalesArticleShare extends ActiveRecord
{
    public static function tableName()
    {
        return 'sales_article_share';
    }
    //获取所有分享数
    public function getShare($timeStart,$timeEnd)
    {
        $sql = "SELECT COUNT(uid) FROM sales_article_share WHERE time_created >= :timeStart AND time_created <= :timeEnd";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':timeStart' => $timeStart,':timeEnd'=>$timeEnd])
            ->queryScalar();
    }

}