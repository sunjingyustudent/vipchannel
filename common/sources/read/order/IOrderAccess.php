<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/13
 * Time: 上午11:44
 */
namespace common\sources\read\order;

use Yii;
use yii\db\ActiveRecord;

interface IOrderAccess {

    /**
     * @param $orderNo
     * @return mixed
     * @created by Jhu
     * 获取订单信息
     */
    public function getProductOrderRowByOrderNo($orderNo);

    /**
     * 获取订单数量
     * @param $request
     * return int
     */
    public function getOrderCount($request);

    /**
     * 获取订单列表
     * @param $request
     * return array
     */
    public function getOrderList($request);

     /**
     * 获取订单编辑页面
     * @param  $id
     * @return array
     */
    public function getEditOrderPage($id);

    /**
     * 套餐变更
     * @param  $id 
     * @return array
     */
    public function getAProductOrder($id);


    /**
     * 套餐信息
     * @param $uid
     * @return array
     */
    public function getProductOrderInfo($uid);    


    /**
     * 获取产品优惠券
     * @param $code
     * @return array
     */
    public function getProductCoupon($code);

    /**
     * 获取产品信息 基于ID
     * @param $productID
     * @return array
     */
    public function getProductById($productID);

    /**
     * 获取套餐的名称
     * @return  array
     */
	public function getProductOrderName();


    /**
     * 获取该用户的套餐
     * @param
     * @return  array
     * create by  wangkai
     */
    public function getProductOrderFree($uid);

    /**
     * @param $orderId
     * @return mixed
     * @created by Jhu
     * 检查订单是否存在
     */
    public function checkOrderIsSuccess($orderId);

    /**
     * @param $studentId
     * @return mixed
     * @created by Jhu
     * 检查用户是否付费用户
     */
    public function checkUserIsBuy($studentId);
}