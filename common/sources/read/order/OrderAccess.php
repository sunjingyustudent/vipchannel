<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/13
 * Time: 上午11:45
 */
namespace common\sources\read\order;

use common\models\music\ProductOrder;
use common\models\music\Product;
use common\models\music\ProductCoupon;
use common\sources\read\product\ProductAccess;
use Yii;
use yii\db\ActiveRecord;

Class OrderAccess implements IOrderAccess {

    public function getProductOrderRowByOrderNo($orderNo)
    {
        return ProductOrder::find()
            ->where(['orderNo' => $orderNo])
            ->asArray()
            ->one();
    }


    public function getOrderCount($request)
    {
        if(empty($request['order_time'])){
            $time_start = strtotime("2000-01-01");
            $time_end = strtotime(("2020-12-30"));
        }else{
            $time_start = strtotime($request['order_time']);
            $time_end = strtotime($request['order_time']."+1 day");
        }


        $sql = "select count(*) from product_order as p
                left join user_init as u on p.open_id = u.openid left join wechat_acc as w on w.openid = p.open_id
                left join product_coupon as c on c.coupon = p.coupon left join user on user.id = w.uid where
                p.time_created >=  {$time_start} and p.time_created <= {$time_end}"
                .($request['order_type'] != 4 ? " and p.pay_status = {$request['order_type']} " : '')
                .(!empty($request['keyword']) ? " and user.nick like '%{$request['keyword']}%'" : '');

        return Yii::$app->db->createCommand($sql)->queryScalar();
    }

    public function getOrderList($request)
    {
        if(empty($request['order_time'])){
            $time_start = strtotime("2000-01-01");
            $time_end = strtotime(("2020-12-30"));
        }else{
            $time_start = strtotime($request['order_time']);
            $time_end = strtotime($request['order_time']."+1 day");
        }

        
        $sql = "select p.*,u.name,u.head,user.nick,user.mobile,c.money from product_order as p
                left join user_init as u on p.open_id = u.openid left join wechat_acc as w on w.openid = p.open_id
                left join product_coupon as c on c.coupon = p.coupon left join user on user.id = w.uid where
                p.time_created >=  {$time_start} and p.time_created <= {$time_end}"
            . ($request['order_type'] != 4 ? " and p.pay_status = {$request['order_type']}" : '')
            . (!empty($request['keyword']) ? " and user.nick like '%{$request['keyword']}%'" : '')
            . " order by p.time_created desc"
            . " LIMIT :offset, :limit";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':offset' => ($request['page_num'] - 1) * 8, ':limit' => 8])
            ->queryAll();
    }


    public function getEditOrderPage($id)    
    {
    	return ProductOrder::find()
		            ->alias("p")
		            ->select('product.*, p.*,u.name as wx_name,u.head,user.nick,user.mobile,pr.name as province,c.name as city')
		            ->leftJoin('product','product.id = p.pid')
		            ->leftJoin('user_init as u','p.open_id = u.openid')
		            ->leftJoin('wechat_acc as w','w.openid = p.open_id')
		            ->leftJoin('user','user.id = w.uid')
		            ->leftJoin('provinces as pr','pr.id = user.province')
		            ->leftJoin('cities as c','c.id = user.city')
		            ->where('p.id = :id',[':id'=>$id])
		            ->asArray()
		            ->one();
    }


    public function getProductOrderName()
    {
        return Product::find()
            ->select('id,name')
            ->where('isdel=0')
            ->asArray()
            ->all();
    }


    public function getProductById($productID)
    {
        return Product::findOne($productID);
    }  

    public function getProductCoupon($code)
    {
       return   ProductCoupon::findOne(['coupon'=>$code]); 
    }

    public function getProductPackage()
    {
        return Product::find()
            ->select('product.*,product_level.name as levelName')
            ->leftJoin('product_level','`product`.`level` =`product_level`.`id`')
            ->where('product.isdel=0 and product.level<>21')
            ->orderBy('product.id desc')
            ->asArray()
            ->all();
    }

    public function getAProductOrder($id)
    {
        return ProductOrder::findOne($id)->toArray();
    }

    public function getProductOrderInfo($uid)
    {
        return  ProductOrder::find()
                    ->select("id,pname,actual_fee")
                    ->where("pay_status=1 and uid=:uid",[':uid'=>$uid])
                    ->asArray()
                    ->all();
    }

    public function getProductOrderFree($uid)
    {
        return  ProductOrder::find()
                    ->select('actual_fee')
                    ->where('pay_status = 1 AND uid = :uid',[':uid' => $uid])
                    ->asArray()
                    ->column();
    }

    public function checkOrderIsSuccess($orderId)
    {
        return ProductOrder::find()
            ->where([
                'id' => $orderId,
                'pay_status' => 1,
            ])->count();
    }

    public function checkUserIsBuy($studentId)
    {
        return ProductOrder::find()
            ->where([
                'uid' => $studentId,
                'pay_status' => 1
            ])->count();
    }
    

}