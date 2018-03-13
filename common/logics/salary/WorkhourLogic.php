<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午3:18
 */
namespace common\logics\salary;

use Yii;

use yii\base\Object;

class WorkhourLogic extends Object implements IWorkhour
{
    /** @var  \common\sources\read\salary\WorkhourAccess $RWorkhourAccess */
    private $RWorkhourAccess;
    /** @var  \common\sources\read\classes\ClassAccess $RClassAccess */
    private $RClassAccess;


    public function init()
    {
        $this->RWorkhourAccess = Yii::$container->get('RWorkhourAccess');
        $this->RClassAccess = Yii::$container->get('RClassAccess');

        parent::init();
    }

    public function getClassMoney($long, $time_class, $teacher_id)
    {
        return $this->RWorkhourAccess->getHourFee($long, $time_class, $teacher_id);
    }
}