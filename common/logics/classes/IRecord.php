<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/9
 * Time: 下午4:58
 */
namespace common\logics\classes;

use Yii;
use yii\base\Object;

interface IRecord {
    /**
     * 获取购买与体验记录
     * @param  $request  array
     * @return array
     */
    public  function getBuyHistoryPage($request);
}