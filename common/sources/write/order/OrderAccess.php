<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/13
 * Time: 上午11:45
 */
namespace common\sources\write\order;

use Yii;
use yii\db\ActiveRecord;
use common\models\music\ProductOrder;
use common\models\music\ProductCoupon;


Class OrderAccess implements IOrderAccess {

	public function addProductOrder($uid, $openid, $productID, $name, $newprice, $type, $class_num, $old_order_id = 0)
	{
		$order = new ProductOrder();
        $order->orderNo = date('Ymd') . time() . mt_rand(1000,9999);
        $order->uid = $uid;
        $order->open_id = $openid;
        $order->pid = $productID;
        $order->pname = $name;
        $order->total_fee = $newprice;
        $order->actual_fee = $newprice;
        $order->coupon = '000000';
        $order->pay_method = 3;
        $order->pay_status = 1;
        $order->instrument = $type;
        $order->class_num = $class_num;
        $order->time_created = time();
        $order->time_updated = time();
        $order->time_pay = time();
        $order->old_order_id = empty($old_order_id) ? 0 : $old_order_id;

        $order->save();

        return $order->orderNo;
	}

    public function addUpgradeProductOrder($uid, $openid, $productID, $name, $newprice, $type, $class_num, $old_order_id, $money)
    {
        $order = new ProductOrder();
        $order->orderNo = date('Ymd') . time() . mt_rand(1000,9999);
        $order->uid = $uid;
        $order->open_id = $openid;
        $order->pid = $productID;
        $order->pname = $name;
        $order->total_fee = $newprice;
        $order->actual_fee = $money;
        $order->coupon = '000000';
        $order->pay_method = 3;
        $order->pay_status = 1;
        $order->instrument = $type;
        $order->class_num = $class_num;
        $order->time_created = time();
        $order->time_updated = time();
        $order->time_pay = time();
        $order->old_order_id = empty($old_order_id) ? 0 : $old_order_id;

        $order->save();

        return $order->orderNo;
    }

	public function addProductCoupon($code, $money)
	{
		$data = new ProductCoupon();
        $data->coupon = $code;
        $data->isMaker = Yii::$app->user->id;
        $data->money = $money;
        $data->usedOrder = '';
        $data->time_created = time();
        $data->time_updated = 0;

        return   $data->save();

	}

    public function doCancelOrder($request)
    {
        $data = ProductOrder::findOne($request['id']);

        $data->cancel_reason = $request['reason'];

        $data->pay_status = 3;
        $data->time_updated = time();

        $data->save();

        return $data;
    }


    public function doUpdateDone($id)
    {
        $data = ProductOrder::findOne($id);
        $data->pay_status = 1;
        $data->pay_method = 1;
        $data->time_updated = time();
        
        return $data->save();

    }


    public  function doupdatePrice($id, $price)
    {
    	$sql = 'UPDATE product_order SET actual_fee = :price, time_updated = :time WHERE id = :id';

	  	return Yii::$app->db->createCommand($sql)
                 ->bindValues([
                        ':id' => $id,
                        ':price' => $price,
                        ':time' => time()
                    ])->execute(); 
                     
    }

    public function doUpdateProducts($id, $old_id, $class_num, $actual_fee)
    {
    	$sql ='UPDATE product_order SET old_order_id = :old_id, class_num = :class_num, actual_fee = :actual_fee, time_updated = :time  WHERE id=:id';
    	
	  	return Yii::$app->db->createCommand($sql)
                 ->bindValues([
                        ':id' => $id,
                        ':old_id' => $old_id,
                        ':class_num' => $class_num,
                        ':actual_fee' => $actual_fee,
                        ':time' => time()
                    ])->execute(); 
    }
}