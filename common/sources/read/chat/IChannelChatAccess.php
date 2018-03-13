<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/3/3
 * Time: 上午10:39
 */
namespace common\sources\read\chat;

use Yii;
use yii\db\ActiveRecord;

interface IChannelChatAccess {
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
     * @param $uid
     * @return mixed
     * @created by Jhu
     * 根据用户ID获取pageid
     */
    public function getPageId($uid);

    /**
     * @param $openId
     * @return mixed
     * @created by Jhu
     * 获取用户阻塞消息
     */
    public function getAccessMessage($openId);

    /**
     * @param $uid
     * @param $openId
     * @param $fd
     * @return mixed
     * @created by Jhu
     * 检测是否处于连接状态
     */
    public function checkIsConncet($uid, $openId, $fd);

    /**
     * @return mixed
     * @created by Jhu
     * 获取所有在线客服
     */
    public function getAllKeFu();

    /**
     * @param $pageId
     * @return mixed
     * @created by Jhu
     * 获取有阻塞消息并且处于被接待状态用户的openid
     */
    public function getConnectedOpenid($pageId);

    /**
     * 查询用户连接的信息 (不需要是否连接)
     * @param   $link_id 是否连接
     * @return  array
     * created by wangkai
     */
    public function getCountLinkByOpenid($link_id);

    /**
     * 获取等待客服的状态
     * @param  $kefuId
     * @return array
     * created by wangkai
     */
    public function getChatWaitByKefu($kefuId);

    /**
     * 根据客服的page_id信息,获取信息
     * @param  $pageId
     * @return array
     * created by wangkai
     */
    public function getChatWaitKefuByPageId($pageId);

    /**
     * 根据pageId获取正在聊天中的openid
     * created by wangkai
     */
    public function  getChatMessagePreByPageId($pageId);

    /**
     * 根据OpenId去查找正在连接的信息
     * @param  　$openid
     * @return   array
     * created by wangkai
     */
    public function countLinkInfoByOpenid($openid);

    /**
     * 获取聊天客服信息
     * @return array
     * created by wangkai
     */
    public function getChatKefuInfo($uid);


    /**
     * 断开连接
     * @param   $linkId
     * @return  array
     * created by wangkai
     */
    public function offChatInfo($linkId);

    /**
     * 判断是否在连接状态
     * @param $page_id
     * @return int
     * created by wangkai
     */
    public function checkConnectCount($page_id, $uid);

    /**
     * 查询聊天等待状态
     * @param
     * @return  array
     * create by  wangkai
     */
    public function getChatWaitInfo($openId);

    /**
     * 获取当前正在聊天的链路信息
     * @return  array
     * created by wangkai
     */
    public function getChatLinkIsCurrentPage();

    /**
     * 根据openID 得到聊天的信息
     * @param  $open_id
     * @param  $offset
     * @return array
     * created by wangkai
     */
    public function getChatMessageInfoByOpenId($open_id, $offset);

    /**
     * 根据open_id 判断该用户是否正在聊天
     * @param   $open_id
     * @return  int
     * created by wangkai
     */
    public function findNoHideClassLinkCount($open_id);

    /**
     * 查询等待聊天的用户信息
     * @param   $wait_id
     * @return  array
     * created by wangkai
     */
    public function getChatWaitById($wait_id);

    /**
     * 统计聊天数量
     * @param  $openid
     * @return  array
     * create by  wangkai
     */
    public function countLinkByOpenid($openid);

    /**
     * 获取其他客服的基础信息
     * @param $kefuId
     * @return array
     * created by wangkai
     */
    public function getWaitKefuList($kefuId);

    /**
     * 根据openId  查找聊天链路的客服ID和接待ID
     * @param   $openId
     * @return  array
     * created by wangkai
     */
    public function getChatLinkByOpenId($openId);

    /**
     * @param $openId
     * @return mixed
     * @created by Jhu
     * 检查对应渠道是否在等待接待列表中
     */
    public function countChannelWaitByOpenid($openId);

    /**
     * 查询渠道用户连接的信息 (不需要是否连接)
     * @param   $link_id 是否连接
     * @return  array
     * created by wangkai
     */
    public function getCountChannelChatLinkById($link_id);

    /**
     * 获取销售渠道的聊天信息
     * @param $open_id
     * @return array
     * created by wangkai
     */
    public function getChannelChatMessageInfo($open_id);

    /**
     * 判断是否聊过天
     * @param $openId
     * @return mixed
     * created by wangkai
     */
    public function checkHaveTalk($openId);

    /**
     * 快捷回复
     * @param   $type
     * @return  array
     * created by wangkai
     */
    public function getQuickAnswerList($type);

}