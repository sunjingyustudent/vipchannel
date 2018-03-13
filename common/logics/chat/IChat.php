<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 16/12/27
 * Time: 下午5:45
 */
namespace common\logics\chat;

use Yii;
use yii\base\Object;

interface IChat
{

    /**
     * @param $xml
     * @return mixed
     * @created by Jhu
     * 处理用户微信发送的图片
     */
    public function dealChatImage($xml);

    /**
     * @param $xml
     * @return mixed
     * @created by Jhu
     * 处理用户微信发送语音
     */
    public function dealChatVoice($xml);

    /**
     * @param $xml
     * @return mixed
     * @created by Jhu
     * 处理用户文字消息
     */
    public function dealChatEmoji($xml);

    /**
     * @param $data
     * @param $fd
     * @return mixed
     * @created by Jhu
     * 处理连接socket事件
     */
    public function dealConnectEvent($server, $data, $fd);

    /**
     * @param $server
     * @param $data
     * @param $fd
     * @return mixed
     * @created by Jhu
     * 处理接入事件
     */
    public function dealAccessEvent($server, $data, $fd);

    /**
     * @param $server
     * @param $data
     * @param $fd
     * @return mixed
     * @created by Jhu
     * 处理点击查看用户聊天记录
     */
    public function dealViewEvent($server, $data, $fd);

    /**
     * @param $server
     * @param $data
     * @param $fd
     * @return mixed
     * @created by Jhu
     * 处理定时器推送的消息
     */
    public function dealTimerEvent($server, $data, $fd);

    /**
     * @param $server
     * @param $data
     * @param $fd
     * @return mixed
     * @created by Jhu
     * 处理转接消息
     */
    public function dealTansferEvent($server, $data, $fd);

    /**
     * @param $server
     * @param $data
     * @param $fd
     * @return mixed
     * @created by Jhu
     * 处理回复转接消息
     */
    public function dealRtransferEvent($server, $data, $fd);

    /**
     * @param $server
     * @param $data
     * @param $fd
     * @return mixed
     * @created by Jhu
     * 处理拒绝转接消息
     */
    public function dealRefuseTransferEvent($server, $data, $fd);

    /**
     * @param $pageId
     * @return mixed
     * @created by Jhu
     * 处理断开连接事件
     */
    public function doClose($pageId);

    /**
     * 获取聊天记录数量
     * @param $request
     * @return int
     */
    public function getChatHistoryCount($request);


    /**
     * 获取聊天列表
     * @param  $request
     * @return array
     */
    public function getChatHistoryList($request);

    /**
     * 查询历史接待信息
     * @param   $is_history
     * @param   $keyword
     * @return  array
     */
    public function getLeftUserInfo($is_history, $keyword);

    /**
     * 查询渠道历史接待信息
     * @param   $is_history
     * @param   $keyword
     * @return  array
     */
    public function getChannelLeftUserInfo($is_history, $keyword);


    /**
     * 点击左边头像
     * @param  $link_id
     * @return array
     */
    public function clickLinkRight($link_id);

    /**
     * 添加聊天
     * @param   $content
     * @param   $openId
     * @return  array
     */
    public function doAddMessage($content, $openId);

    /**
     * 判断是否是否连接
     * @param   $page_id
     * @return  int
     */
    public function getCheckConnectCount($page_id, $uid);

    /**
     * 赠送课程页面
     */
    public function getGiveClassPage();

    /**
     * 是否可以接入
     * @param $waitId
     * @param $page
     * @return array
     */
    public function checkAccess($waitId, $page);

    /**
     * 转接客服
     * @param $kefuId
     * @return  array
     * create by  wangkai
     */
    public function getTransferServer($kefuId);

    /**
     * 转接客服操作
     * @param $linkId
     * @param $kefuId
     * @param $logid
     * @return array
     */
    public function doEditTransfer($linkId, $kefuId, $logid);

    /**
     * 发送微信
     * @param  $request
     * @return array
     */
    public function sendWechat($request);

    /**
     * 断开连接
     * @param   $linkId
     * @param   $logid
     */
    public function offChat($linkId, $logid);

    /**
     * 加载更多的信息
     * @param  $offset
     * @return array
     */
    public function getLoadMoreInfo($offset);

    /**
     * @param $uid
     * @param $keyword
     * @return mixed
     * @created by Jhu
     * 获取赈灾接待用户
     */
    public function getChattingUserById($uid, $keyword);

    /**
     * @param $keyword
     * @return mixed
     * @created by Jhu
     * 获取等待接待用户
     */
    public function getWaitingUser($kefuId, $keyword, $offset, $limit);

    /**
     * @param $openId
     * @param $offset
     * @param $limit
     * @return mixed
     * @created by Jhu
     * 获取历史聊天记录
     */
    public function getHistoryMessageByOpenId($openId, $offset, $limit);

    /**
     * @param $content
     * @param $openId
     * @return mixed
     * @created by Jhu
     * 添加发送消息
     */
    public function addMessageApi($content, $openId, $uid);

    /**
     * @param $openId
     * @param $message
     * @param $messageId
     * @return mixed
     * @created by Jhu
     * 发送微信
     */
    public function sendWechatApi($openId, $message, $messageId);


    /**
     * 快捷信息编辑
     * @param   $qid
     * @param   $content
     * @return  array
     * create by  wangkai
     */
    public function doEditQucikAnswer($qid, $content);

    /**
     * 删除快捷信息
     * @param $id
     * @return
     * create by  wangkai
     */
    public function doDeleteQucikAnswer($id);

    /**
     * 获取不同类型的用户列表
     * @param  $type    一共有3个参数   2 代表注册未付费   3 代表注册已付费  4 高危用户
     * @param  $offset
     * @param  $limit
     * @return  array
     * create by  wangkai
     */
    public function getOtherUser($type, $offset, $limit);

    /**
     * 获取不同类型的用户列表数量
     * @param  $type    一共有3个参数   2 代表注册未付费   3 代表注册已付费  4 高危用户
     * @return  array
     * create by  wangkai
     */
    public function getOtherUserCount($type);

    /**
     * 获取新用户的用户数量
     * @return  array
     * create by  wangkai
     */
    public function getNewUserCount();

    /**
     *  获取新用户列表
     * @param $from
     * @param $offset
     * @param $limit
     * @return  array
     * create by  wangkai
     */
    public function getNewUser($from, $offset, $limit);

    /**
     * @param $linkId
     * @return mixed
     * @created by Jhu
     * 进去聊天页设置当前页
     */
    public function setPage($linkId);

    /**
     * @param $linkId
     * @return mixed
     * @created by Jhu
     * 退出聊天页
     */
    public function outPage($linkId);

    /**
     * @param $linkId
     * @param $kefuId
     * @return mixed
     * @created by Jhu
     * 移动端转接
     */
    public function doEditTransferApi($linkId, $kefuId);


    /**
     * 关闭socket链接
     * @param $pageId
     * @return  array
     * create by  wangkai
     */
    public function closeSocket($pageId);

    /**
     * 发送的图片并且保存图片
     * @param  $openId
     * @param  $file
     * @param  $logid
     * @return  array
     * create by  wangkai
     */
    public function doSendImage($openId, $file, $logid);

    /**
     *  添加快捷回复
     * @param   $content
     * @param   $type
     * @param   $logid
     * @return  array
     * create by  wangkai
     */
    public function addQuickAnswer($content, $type, $logid);
    
    /*
     * 开启用户未分享的所有课程（一键开启所有课程）
     * create by sjy 2017-06-23
     */
    public function opensuperclass($openid);
}