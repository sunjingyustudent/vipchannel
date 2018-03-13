<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/4
 * Time: 下午12:25
 */
namespace common\logics\sale;

use Yii;
use yii\base\Object;
use yii\db\Exception;
use yii\db\Query;

class PurchaseLogic extends Object implements IPurchase
{
    /** @var  \common\logics\student\StudentLogic $studentService */
    private $studentService;
    /** @var  \common\logics\classes\ClassesLogic $classesService */
    private $classesService;
    /** @var  \common\logics\complain\ComplainLogic $complainService */
    private $complainService;
    /** @var  \common\logics\order\OrderLogic $orderService */
    private $orderService;

    public function init()
    {
        $this->studentService = Yii::$container->get('studentService');
        $this->classesService = Yii::$container->get('classesService');
//        $this->visitService = Yii::$container->get('visitService');
        $this->complainService = Yii::$container->get('complainService');
        $this->orderService = Yii::$container->get('orderService');
        parent::init();
    }

    public function countAllPurchasePage($keyword, $type){
        return $this->studentService->countAllPurchasePage($keyword, $type);
    }
    public function queryAllPurchaseList($keyword, $type, $num){
        return $this->studentService->queryAllPurchaseList($keyword, $type, $num);
    }



    public function countPurchaseComplain($keyword){
        return $this->complainService->countPurchaseComplain($keyword);
    }

    public function getPurchaseComplainList($keyword , $num)
    {
        return $this->complainService->getPurchaseComplainList($keyword, $num);
    }


    public function getClassCheckPage($keyword)
    {
        return $this->classesService->getClassCheckPage($keyword);
    }

    public function getClassCheckList($keyword,$num)
    {
        return $this->classesService->getClassCheckList($keyword,$num);
    }



    public function getTodoPurchaseList($keyword,$time)
    {
        return $this->studentService->getTodoPurchaseList($keyword,$time);
    }



    public function getNoClassPurchasePage($type)
    {
        return $this->studentService->getNoClassPurchasePage($type);
    }

    public function getNoClassPurchaseList($type,$num)
    {
        return $this->studentService->getNoClassPurchaseList($type,$num);
    }
    
    public function countPurchaseCourse($day){
        return $this->classesService->countPurchaseCourse($day);
    }

    public function getPurchaseCourseList($day , $num){
        return $this->classesService->getPurchaseCourseList($day , $num);
    }

    //待复购名单
    public function getRebuyPurchasePage($type, $number)
    {
        return $this->studentService->getRebuyPurchasePage($type,$number);
    }
    public function getRebuyPurchaseList($type, $num, $number)
    {
        return $this->studentService->getRebuyPurchaseList($type, $num, $number);
    }

    public function getPurchaseUserAgainAllotNotFollowPage($type, $studentName, $distributionTime, $saleId)
    {
        return $this->studentService->getPurchaseUserAgainAllotNotFollowPage($type, $studentName, $distributionTime, $saleId);
    }

    public function getPurchaseUserAgainAllotNotFollowList($type, $studentName, $distributionTime, $saleId, $num)
    {
        return $this->studentService->getPurchaseUserAgainAllotNotFollowList($type, $studentName, $distributionTime, $saleId, $num);
    }
}