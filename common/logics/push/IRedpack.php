<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/3/23
 * Time: 上午10:19
 */
namespace common\logics\push;


interface IRedpack {

    /**
     * @param $data
     * @return mixed
     * @created by Jhu
     * 发送红包
     */
    public function sendRedpack($data);

    /**
     * @param $msg
     * @return mixed
     * @created by Jhu
     * 给VIP微课发送活动红包
     */
    public function sendChannelActRedpack($msg);
}