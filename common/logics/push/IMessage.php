<?php
/**
 * Created by PhpStorm.
 * User: wangkai
 * Date: 17/2/15
 * Time: 11:07
 */
namespace common\logics\push;

interface IMessage
{

    /**
     * @return mixed
     * @created by Jhu
     * 发送客服消息
     */
    public function sendKefuMessage($msg);

    /**
     * @param $msg
     * @return mixed
     * @created by Jhu
     * 发送客服消息给渠道公众号
     */
    public function sendChannelKefuMessage($msg);

    /**
     * @param $msg
     * @return mixed
     * @created by Jhu
     * 发送客服消息到老师公众号
     */
    public function sendTeacherKefuMessage($msg);

    /**
     * 客服手动排课发送课程提醒信息
     * @param   $class_id  课程ID
     * @return  array
     * create by  wangkai
     */
    public function sendManualAssignClassMessage($classId);

    /**
     * 客服更改课程时间发送课程时间更改信息
     * @param  $class_id
     * @param  $init_time
     * @param  $update_time
     * @return  array
     * create by  wangkai
     */
    public function sendUpdateClassTimeMessage($classId, $initTime, $updateTime);


    /**
     * 客服正在处理投诉内容，发送投诉被处理中的信息
     * @param
     * @return  array
     * create by  wangkai
     */
    public function sendBeingProcessComplainMessage($complainId);

    /**
     * 客服处理完成投诉内容，发送处理完成后的反馈信息
     * @param  $complain_id
     * @return  array
     * create by  wangkai
     */
    public function sendProcessComplainMessage($complainId);


    /**
     * 客服修改了订单价格成功发送的信息
     * @param $order_info
     * @return  array
     * create by  wangkai
     */
    public function sendUpdateOrderMessage($orderInfo);


    /**
     * 绑定复购用户成功发送的客服信息
     * @param $userId
     * @param $kefuId
     * @return  array
     * create by  wangkai
     */
    public function sendBindDistributeUserAccount($userId, $kefuId);

//    /**
//     * 用户关注公众号没有和公众号对话
//     * @param
//     * @return  array
//     * create by  wangkai
//     */
//    public function sendFollowUser($open_id);

    /**
     * 发送奖励过后的话术
     * @param   $bind_openid
     * @param   $message_info
     * @param   $history_id
     * @return  array
     * create by  wangkai
     */
    public function sendChannelRewardMessage($uid, $messageInfo, $historyId);

    /*
     * 发送取消体验课消息,发送客服消息
     * create by sjy
     */
    public function sendCancelexRecord($classId, $saleId);
    
    /*
     * 发送取消体验课消息,发送模板消息
     * create by sjy
     */
    public function sendCancelexTemplet($classId, $saleId);

        /**
     * 发送自己招募过来的学生完成课时发送学生的课时记录 并且提示老师要领取奖励
     * @param   $class_id
     * @return  array
     * create by  wangkai
     */
    public function sendClassRewardMessage($classId);

    /**
     * @param $share
     * @param $open_id
     * @return mixed
     * create by wangke
     * 微课程注册时 为下线发送客服消息
     */
    public function sendWechatClassSubscribe($share, $openId);

    /**
     * @param $message
     * @return mixed
     * @created by Jhu
     * 渠道关注公众号延迟消息
     */
    public function sendChannelSubscribeDelayMessage($message);

    /**
     * 发送历史奖励明细话术
     * @param   $uid
     * @param   $history_id
     * @return  array
     * create by  wangkai
     */
    public function sendHisotryChannelRewardMessage($uid, $historyId);

    /**
     * @param $msg
     * @return mixed
     * @created by Jhu
     * 自动回复功能
     */
    public function sendAutoAnswer($msg);

    /**
     * @param $open_id
     * @return mixed
     * create by wangke
     * vip陪练的关注后15分钟没有预约体验课
     */
    public function sendPnlFifteenMinuteNotWechatclass($openId);
}
