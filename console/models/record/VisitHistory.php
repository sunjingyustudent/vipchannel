<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 16/11/23
 * Time: 16:01
 */

namespace console\models\record;

use Yii;
use yii\db\ActiveRecord;

class VisitHistory extends ActiveRecord
{

    public static function tableName()
    {
        return 'visit_history';
    }

    public function getVisitBykefu($experienceids,$timeStart,$timeEnd)
    {
        $sql = "SELECT count(student_id) FROM visit_history WHERE time_created >= :timeStart AND time_created < :timeEnd"
            . " AND student_id IN (".implode(',',$experienceids).") GROUP BY student_id";

        return Yii::$app->db->createCommand($sql)
                        ->bindValues([':timeStart' => $timeStart, ':timeEnd' => $timeEnd])
                        ->queryScalar();
    }

}