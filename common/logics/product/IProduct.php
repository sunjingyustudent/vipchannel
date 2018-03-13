<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/2/3
 * Time: 下午3:37
 */
namespace common\logics\product;

use Yii;
use yii\base\Object;

interface IProduct {
    /**
     * 课时充值页面
     * @param  $openid
     * @return array
     * create by wangkai
     */
    public function getChargePage($openid);


    /**
     * 商品套餐
     * @return array
     * create by wangkai
     */
    public function getProductPackage();
}