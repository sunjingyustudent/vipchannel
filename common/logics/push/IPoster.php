<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/3/23
 * Time: 上午10:17
 */
namespace common\logics\push;


interface IPoster {

    /**
     * @param $openId
     * @param $imgPath
     * @param string $weicode
     * @return mixed
     * @created by Jhu
     * 发送海报给VIP微课
     */
    public function sendPosterChannel($msg);

    /**
     * @param $openId
     * @param $imgPath
     * @param string $weicode
     * @return mixed
     * @created by Jhu
     * 发送海报给VIP陪练
     */
    public function sendPosterStudent($msg);

    /**
     * @param $msg
     * @return mixed
     * @created by Jhu
     * 每周发送海报功能
     */
    public function sendPosterChannelWeek($msg);
}