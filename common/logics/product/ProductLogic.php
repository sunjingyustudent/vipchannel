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

class ProductLogic extends Object implements IProduct {


    /** @var  \common\sources\read\order\OrderAccess  $ROrderAccess */
    private $ROrderAccess;
    /** @var  \common\sources\read\student\StudentAccess  $RStudentAccess */
    private $RStudentAccess;



    public function init()
    {
        $this->RStudentAccess = Yii::$container->get('RStudentAccess');
        $this->ROrderAccess = Yii::$container->get('ROrderAccess');
        parent::init();
    }

    public function getChargePage($openid)
    {
        $User = $this->RStudentAccess->getInitAndWechatByOpenId($openid);
        
        $data = $this->ROrderAccess->getProductOrderName();

        array_unshift($data,['id'=>0, 'name' =>'请选择商品套餐']);

        return [$data, $User, $openid];
    }

    public function getProductPackage()
    {
        $data = $this->ROrderAccess->getProductPackage();

        foreach ($data as &$row)
        {
            $row['link'] = Yii::$app->params['base_url'] . 'product/detail?id=' . $row['id'];
        }

        return $data;
    }

}