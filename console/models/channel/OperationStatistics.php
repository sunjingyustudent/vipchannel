<?php
/**
 * Created by PhpStorm.
 * User: wk
 * Date: 16/9/20
 * Time: 14:20
 */
namespace console\models\channel;

use Yii;
use yii\db\ActiveRecord;
class OperationStatistics extends ActiveRecord
{
    public static function tableName()
    {
        return 'operation_statistics';
    }
    

    // 插入运营统计日志
    public function doAddOperationStatisticsLog($start,$content)
    {
        $sql = "INSERT INTO operation_statistics(create_time,content) VALUES(:time,:content)";
        Yii::$app->db->createCommand($sql)
                     ->bindValues([
                        ':time'      => $start,
                        ':content'   => $content
                      ])
                     ->execute();
        return Yii::$app->db->getLastInsertID();
    }

}