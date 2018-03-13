<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/13
 * Time: 上午11:44
 */
namespace common\sources\write\order;

use Yii;
use yii\db\ActiveRecord;

interface IOrderAccess {
    /**
     * 取消订单
     * @param  $request
     * @return $data
     */
    public function doCancelOrder($request);

    /**
     * 修改价格
     * @param  $code
     * @param  $money
     * @return bool
     */
	public function addProductCoupon($code, $money);


	 /**
     * 添加套餐
     * @param  $uid
     * @param  $openid
     * @param  $productID
     * @param  $name
     * @param  $newprice
     * @param  $type
     * @param  $class_num
     * @param  $old_order_id
     * @return str
     */
	public function addProductOrder($uid, $openid, $productID, $name, $newprice, $type, $class_num, $old_order_id);

    /**
     * 套餐升级
     * @param  $uid
     * @param  $openid
     * @param  $productID
     * @param  $name
     * @param  $newprice
     * @param  $type
     * @param  $class_num
     * @param  $old_order_id
     * @return str
     */
    public function addUpgradeProductOrder($uid, $openid, $productID, $name, $newprice, $type, $class_num, $old_order_id, $money);
}