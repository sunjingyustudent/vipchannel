<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/3/3
 * Time: 上午10:37
 */
namespace common\logics\chat;

use Yii;
use yii\base\Object;

interface IChannelChat {

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
     * 根据Type 类型获得渠道用户数量
     * @param    $type
     * @return   str
     * create by  wangkai
     */
    public function getUserCount($type);

    /**
     *  根据Type 类型获得渠道用户列表
     * @param   $type
     * @param   $num
     * @return  array
     * create by  wangkai
     */
    public function getUserList($type, $num);

    /**
     * 断开连接
     * @param   $linkId
     */
    public function offChat($linkId);

    /**
     * 添加聊天
     * @param   $content
     * @param   $openId
     * @return  array
     */
    public function doAddMessage($content, $openId);

    /**
     * 发送微信
     * @param  $request
     * @return array
     */
    public function sendWechat($request);

    /**
     * 关闭socket链接
     * @param $pageId
     * @return  array
     * create by  wangkai
     */
    public function closeSocket($pageId);

    /**
     * 判断是否是否连接
     * @param   $page_id
     * @return  int
     */
    public function getCheckConnectCount($page_id,$uid);

    /**
     * 加载更多的信息
     * @param  $offset
     * @return array
     */
    public function getLoadMoreInfo($offset);


    /**
     * 获取accessTablk 以便跳转到聊天页面
     * @param   $open_id
     * @return  array
     * create by  wangkai
     */
    public function getAccessTalk($open_id);

    /**
     * 根据open_id 找到当前聊天中的ID
     * @param  $openID
     * @return array
     */
    public function getLink($openId);


    /**
     * 发送奖励
     * @param $user_id
     * @param $title
     * @param $money
     * @return  array
     * create by  wangkai
     */
    public function sendReward($user_id, $title, $money);

    /**
     * 转接客服
     * @param $kefuId
     * @return  array
     * create by  wangkai
     */
    public function getTransferServer($kefuId);

    /**
     * 查看是否有被正在接待
     * @param  $openId
     * @return  array
     * create by  wangkai
     */
    public  function getCheckTalk($openId);

    /**
     * 发送的图片并且保存图片
     * @param  $openId
     * @param  $file
     * @param  $logid
     * @return  array
     * create by  wangkai
     */
    public function doSendImage($openId, $file, $uid);

    /**
     * 发送海报
     * @param  $openId
     * @param  $path
     * @param  $uid
     * @return  array
     * create by  wangkai
     */
    public function doSendHaiBao($openId, $path, $uid);

    /**
     * 修改转接状态
     * @param  $linkId
     * @param  $kefuId
     * @return  array
     * create by  wangkai
     */
    public function doEditTransfer($linkId, $kefuId);

    /**
     * @param $openId
     * @return mixed
     * @created by Jhu
     * 用户关注渠道公众号添加关注消息
     */
    public function addSubscribeMessage($openId);

    /**
     * 点击左边的聊天头像进入聊天系统
     * @param $link_id
     * @return  array
     * create by  wangkai
     */
    public function clickChannelLinkRight($link_id);

    /**
     * 快捷回复内容展示
     * @param   $type (0代表 新用户 1代表 推广价值用户 2代表无推广价值用户)
     * @return  array
     * create by  wangkai
     */
    public function getQuickAnswerList($type);

    /**
     *  添加快捷回复
     * @param   $content
     * @param   $type
     * @return  array
     * create by  wangkai
     */
    public function addQuickAnswer($content, $type);

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
     * 保存相关话术和金钱
     * @param $id
     * @param $message
     * @param $money
     * @return  array
     * create by  wangkai
     */
    public function doSaveChatMessage($id, $message, $money);

    /**
     * 保存系统被动回复话术
     * @param  $data  array
     * @return array
     * create by  wangkai
     */
    public function doPassiveSaveChatMessage($data);
}