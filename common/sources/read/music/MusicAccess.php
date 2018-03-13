<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/9
 * Time: 下午4:33
 */
namespace common\sources\read\music;

use Yii;
use yii\db\ActiveRecord;
use common\models\music\ClassRoom;
use common\models\music\ClassImage;

Class MusicAccess implements IMusicAccess {
    
    public function getMusicLibrary($class_id)
    {
        return ClassRoom::find()
        			->select('course_info, marks')
		            ->where(['id' => $class_id])
		            ->one();
    }


    



}