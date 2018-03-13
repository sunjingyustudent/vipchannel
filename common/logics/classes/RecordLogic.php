<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/9
 * Time: 下午4:58
 */
namespace common\logics\classes;

use Yii;
use yii\base\Object;
use yii\db\Exception;
use common\services\LogService;

class RecordLogic extends Object implements IRecord
{
    /** @var  \common\sources\read\classes\RecordAccess  $RRecordAccess */
    private $RRecordAccess;
    /** @var  \common\sources\read\classes\ClassAccess  $RClassAccess */
    private $RClassAccess;
    /** @var  \common\sources\write\classes\ClassAccess  $WClassAccess */
    private $WClassAccess;
    /** @var  \common\sources\read\student\StudentAccess  $RStudentAccess */
    private $RStudentAccess;
    /** @var  \common\sources\read\teacher\TeacherAccess  $RTeacherAccess */
    private $RTeacherAccess;

    public function init()
    {
        $this->RClassAccess = Yii::$container->get('RClassAccess');
        $this->RTeacherAccess = Yii::$container->get('RTeacherAccess');
        $this->WClassAccess = Yii::$container->get('WClassAccess');
        $this->RStudentAccess = Yii::$container->get('RStudentAccess');
        $this->RRecordAccess = Yii::$container->get('RRecordAccess');
        parent::init();
    }


    /**
     * 获取购买与体验记录
     * @param  $request  array
     * @return array
     */
    public  function getBuyHistoryPage($request)
    {
    	$ex = $this->RRecordAccess->getBeginsClassTime($request['student_id']);

    	$bill = $this->RRecordAccess->getClassEditHistoryInfo($request['student_id']);


    	$leftInfo = $this->RRecordAccess->getClassLeftInfo($request['student_id']);
    	
    	return [$ex, $bill, $leftInfo];
    }

    


    public function getSelfClassList($request)
    {

        $timeStart = strtotime($request['date']);
        $timeEnd = $timeStart + 86400;

        return  $this->RRecordAccess->getSelfClassList($request['student_id'], $timeStart, $timeEnd);

    }


    




}