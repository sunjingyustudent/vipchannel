<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/7/13
 * Time: 上午11:08
 */
namespace console\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;

class Classlog extends ActiveRecord
{
    public static function tableName()
    {
        return 'class_log';
    }


    public static function ClearLog()
    {
    	$date = date('Y-m-d',time());
    	$date = $date-60*60*24*30;
        $sql = "SELECT COUNT('id') FROM class_log WHERE time > '{$date}'";
        $rowCount = Yii::$app->db->createCommand($sql)->rowCount();
        $count = ceil($rowCount/10000);

        $sql = "DELETE TOP 10000 FROM class_log WHERE time < '{$date}'";
    	for ($i=0; $i <$count ; $i++) { 
    		Yii::$app->db->createCommand($sql)->delete();
    	}
    }
}