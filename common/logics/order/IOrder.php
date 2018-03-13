<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 16/12/29
 * Time: 下午3:28
 */
namespace common\logics\order;

use Yii;
use yii\base\Object;

interface IOrder {

    /**
     * @param $request
     * @return mixed
     * @created by Jhu
     * 处理订单
     */
    public function dealTradeNotify($request);

    /**
     * @param $student_id
     * @return mixed
     * create by wangke
     * 购买信息
     */
    public function getBuyInfoNew($student_id);

    /**
     * @param $leftId
     * @return mixed
     * create by wangke
     * 购买信息的退费
     */
    public function editBuyInfo($leftId);

    /**
     * 退款
     * @param   $leftId
     * @return  array
     * create by wangkai
     */
    public function getUserRefund($leftId);

    /**
     * 升级套餐
     * @param   $request
     * @return  array
     * create by wangkai
     */
    public function doUpProduct($request);

    /**
     * 获取套餐类型
     * @return array
     * create by wangkai
     */    
    public function getProductType();

    /**
     * 获得套餐变更页面
     * @param  $id
     * @return array
     * create by wangkai
     */
    public function changeProductPackage($id);

    /**
     * 变更套餐信息
     * @param  $request array
     * @param  $logid   str
     * @return int
     * create by wangkai
     */
    public function doUpdateProducts($request, $logid);

    /**
     * 变更套餐价格 
     * @param  @request array
     * @param  $logid   str
     * @return int
     * create by wangkai
     */
    public function doUpdatePrice($request, $logid);

    /**
     * 更新订单
     * @param  $id
     * @param  $logid
     * @return int
     * create by wangkai
     */
    public function doUpdateDone($id, $logid);

    /**
     * 取消订单
     * @param  $request array
     * @param  $logid   int
     * @return int
     * create by wangkai
     */
    public function doCancelOrder($request, $logid);



    /**
     * 根据金钱成随机优惠码
     * @param  $money
     * @return int
     * create by wangkai
     */
    public function getMaker($money);

    /**
     * 进行课时充值
     * @param  $request
     * @param  $logid
     * @return int
     * create by wangkai
     */
    public function doUpdateCharge($request,$logid);

    /**
     * 进行套餐升级
     * @param  $request
     * @param  $logid
     * @return int
     * create by wangkai
     */
    public function doUpgradePackage($request,$logid);

    /**
     * 获取订单数量
     * @param $request
     * return int
     * create by wangkai
     */
    public function getOrderCount($request);
    
    /**
     * 获取订单列表
     * @param $request
     * @return int
     * create by wangkai
     */
    public  function getOrderList($request);

    /**
     * 获取订单编辑页面
     * @param  $id
     * @return array
     * create by wangkai
     */
    public function getEditOrderPage($id);

    /**
     * @param $msg
     * @return mixed
     * @created by Jhu
     * 处理用户购买后渠道延迟分成
     */
    public function delayProcessSalesTrade($msg);

    /**
     * 添加赠送课程
     */
    public function doGiveClass($message);

    /**
     * 添加课程记录
     */
    public function addClassHistory($historyId,$studentid,$instrumentid,$class_type,$amount);
}