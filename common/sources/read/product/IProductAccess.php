<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/13
 * Time: 上午11:47
 */
namespace common\sources\read\product;

use Yii;
use yii\db\ActiveRecord;

interface IProductAccess {

    /**
     * @param $pid
     * @return mixed
     * @created by Jhu
     * 获取商品信息
     */
    public function getProductRowById($pid);

    /**
     * @param $coupon
     * @return mixed
     * @created by Jhu
     * 获取优惠卷金额
     */
    public function getPriceByCoupon($coupon);
}