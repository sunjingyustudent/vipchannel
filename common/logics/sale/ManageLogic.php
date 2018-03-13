<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/9
 * Time: 下午6:09
 */
namespace common\logics\sale;

use Yii;
use yii\base\Object;
use yii\db\Exception;

class ManageLogic extends Object implements IManage
{
    /** @var  \common\logics\account\AccountLogic $accountService */
    private $accountService;
    /** @var  \common\logics\student\StudentLogic $studentService */
    private $studentService;

    public function init()
    {
        $this->studentService = Yii::$container->get('studentService');
        $this->accountService = Yii::$container->get('accountService');
        parent::init();
    }
    public function getKefuList(){
        return $this->accountService->getKefuList();
    }

    public function countAllotPurchase($keyword, $start, $end){
        return $this->studentService->countAllotPurchase($keyword, $start, $end);
    }

    public function getAllotPurchaseList($keyword, $start, $end, $num){
        return $this->studentService->getAllotPurchaseList($keyword, $start, $end, $num);
    }

    public function distributeUserAccountOne($logid, $userId,$kefuId){
        return $this->studentService->distributeUserAccountOne($logid, $userId, $kefuId);
    }
    public function getNewSignKefuList(){
        return $this->accountService->getNewSignKefuList();
    }
    public function countAllotNewUser($introduce, $start, $end){
        return $this->studentService->countAllotNewUser($introduce, $start, $end);
    }
    public function getAllotNewUserList($introduce, $start, $end, $num){
        return $this->studentService->getAllotNewUserList($introduce, $start, $end, $num);
    }

    public function distributeNewUser($logid,$userId,$kefuId){
        return $this->studentService->distributeNewUser($logid, $userId, $kefuId);
    }

    public function countAgainAllotNotPay($keyword){
        return $this->studentService->countAgainAllotNotPay($keyword);
    }

    public function getAgainAllotNotPayList($keyword, $num){
        return $this->studentService->getAgainAllotNotPayList($keyword, $num);
    }

    public function countAgainAllotNotPurchase($keyword){
        return $this->studentService->countAgainAllotNotPurchase($keyword);
    }

    public function getAgainAllotNotPurchaseList($keyword, $num){
        return $this->studentService->getAgainAllotNotPurchaseList($keyword, $num);
    }

    public function distributeNotPurchase($logid,$userId,$kefuId){
        return $this->studentService->distributeNotPurchase($logid,$userId,$kefuId);
    }
    public function countAgainAllotNotFollow($btn, $keyword, $start, $end,$kefu_id){

        return $this->studentService->countAgainAllotNotFollow($btn, $keyword, $start, $end, $kefu_id);
    }

    public function getAgainAllotNotFollowList($btn, $keyword, $start, $end, $num, $kefu_id){
        return $this->studentService->getAgainAllotNotFollowList($btn, $keyword, $start, $end, $num, $kefu_id);
    }

    public function getPublicUserKefuInfo(){
        return $this->accountService->getPublicUserKefuInfo();
    }

    public function getAllUserKefuInfo()
    {
        return $this->accountService->getAllUserKefuInfo();
    }

    public function countPublicUserPage($type, $kefuId, $area , $keyword , $intention , $time_type, $start , $end){
        return $this->studentService->countPublicUserPage($type, $kefuId, $area , $keyword , $intention , $time_type, $start , $end);
    }

    public function getPublicUserList($type, $kefuId, $area, $keyword, $intention, $time_type, $start, $end,$num){
        return $this->studentService->getPublicUserList($type, $kefuId, $area , $keyword , $intention , $time_type, $start , $end,$num);
    }

    public function distributePublicUserKefu($logid,$userId,$kefuId){
        return $this->studentService->distributePublicUserKefu($logid,$userId,$kefuId);
    }

    public function distributeAllUserKefu($userId,$kefuId)
    {
        return $this->studentService->distributeAllUserKefu($userId,$kefuId);
    }

    public function getExClassReportKefuInfo()
    {
        return $this->accountService->getExClassReportKefuInfo();
    }


}
