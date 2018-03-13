<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/13
 * Time: 上午11:48
 */
namespace common\sources\read\product;

use common\models\music\Product;
use common\models\music\ProductCoupon;
use Yii;
use yii\db\ActiveRecord;

Class ProductAccess implements IProductAccess {

    public function getProductRowById($pid)
    {
        return Product::find()
            ->where(['id' => $pid])
            ->asArray()
            ->one();
    }

    public function getPriceByCoupon($coupon)
    {
        return ProductCoupon::find()
            ->select('money')
            ->where(['coupon' => $coupon])
            ->scalar();
    }
}