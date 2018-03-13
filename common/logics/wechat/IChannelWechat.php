<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/16
 * Time: 下午6:19
 */
namespace common\logics\wechat;

use Yii;
use yii\base\Object;

interface IChannelWechat {

    /**
     * @param $msg
     * @return mixed
     * @created by Jhu
     * 处理推广大使公众号海报
     */
    public function dealPoster($msg);

    /**
     * @param $arr
     * @return mixed
     * create by wangke
     * 批量处理海报的发送（一个图片二个文本）
     */
    public function dealPosterPush($arr);
}