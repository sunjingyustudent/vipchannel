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

interface IPurchase {
    /**
     * @param string $keyword
     * @return mixed
     * create by wangke
     * 查询全部复购的条数
     */
    public function countAllPurchasePage($keyword, $type);

    /**
     * @param $keyword
     * @param $num
     * @return mixed
     * create by wangke
     * 查询复购的全部客户列表
     */
    public function queryAllPurchaseList($keyword , $type, $num);

    /**
     * @param $student_id
     * @return mixed
     * create by wangke
     * 复购投诉页面的条数
     */
    public function countPurchaseComplain($keyword);

    /**
     * @param $keyword
     * @param $num
     * @return mixed
     * create by wangke
     * 复购投诉页面的列表信息
     */
    public function getPurchaseComplainList($keyword , $num);

    /**
     * @param $day
     * @return mixed
     * create by wangke
     * 复购的课程名单条数
     */
    public function countPurchaseCourse($day);

    /**
     * @param $day
     * @return mixed
     * create by wangke
     * 复购的课程名单列表信息
     */
    public function getPurchaseCourseList($day , $num);

    /**
     * 获取未跟进再分配页面（复购）
     * @param  $type
     * @param  $studentName
     * @param  $distributionTime
     * @param  $saleId
     * @return  array
     * create by  wangkai
     */
    public function getPurchaseUserAgainAllotNotFollowPage($type, $studentName, $distributionTime, $saleId);

    /**
     * 获取未跟进再分配列表（复购）
     * @param  $type
     * @param  $studentName
     * @param  $distributionTime
     * @param  $saleId
     * @param  $num
     * @return  array
     * create by  wangkai
     */
    public function getPurchaseUserAgainAllotNotFollowList($type, $studentName, $distributionTime, $saleId, $num);
}