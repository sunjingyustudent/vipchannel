<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/9
 * Time: 下午5:06
 */
namespace common\sources\read\classes;

use Yii;
use yii\db\ActiveRecord;

interface IRecordAccess {
    /**
	 * 获取某用户上课时间
	 * @param  $student_id  int
	 * @return $array
	 */
	public function getBeginsClassTime($student_id);

    /**
     * @param $classId
     * @return mixed
     * @created by Jhu
     * 根据classid获取课单ID
     */
	public function getRecordId($classId);
}