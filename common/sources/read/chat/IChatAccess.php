<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/13
 * Time: 上午11:40
 */
namespace common\sources\read\chat;

use Yii;
use yii\db\ActiveRecord;

interface IChatAccess {

    /**
     * @param $openid
     * @return mixed
     * @created by Jhu
     * 通过openid查看是否当前有接待人
     */
    public function countLinkByOpenid($openid);

    /**
     * @param $openid
     * @return mixed
     * @created by Jhu
     * 通过openid查看是否在等待列表
     */
    public function countStudentWaitByOpenid($openid);

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
     * 根据OpenId去查找正在连接的信息
     * @param  　$openid
     * @return   array
     */
    public function countLinkInfoByOpenid($openid);

    /**
     * 获取用户的聊天OpenID
     * @param   $student_id  int
     * @return  array
     */
    public function  getWechatAccByopenid($student_id);


    /**
     * 查询用户是否存在
     * @param  $openId  int
     * @return array
     */
    public function getWechatAccByExist($openId); 


    /**
     * 查询聊天历史记录数量
     * @param $request
     * @return int
     */
    public function getChatHistoryCount($request);

    /**
     * 查询聊天历史记录列表
     * @param $request
     * @return array
     */
    public function getChatHistoryList($request);

    /**
     * 查询金蛋记录是否已经生成
     * @param $open_id
     * @return array
     */
    public function getGoldEge($open_id);


    /**
     * 查询历史接待信息
     * @param   $is_history 
     * @param   $keyword
     * @return  array
     */
    public function getLeftUserInfo($is_history, $keyword);

    /**
     * 查询历史接待信息
     * @param   $is_history
     * @param   $keyword
     * @return  array
     */
    public function getChannelLeftUserInfo($is_history, $keyword);

    /**
     * 查询用户连接的信息 (不需要是否连接)
     * @param   $link_id 是否连接
     * @return  array
     */
    public function getCountLinkByOpenid($link_id);

    /**
     * 获取聊天信息
     * @param $open_id
     * @return array
     */
    public function getChatMessageInfo($open_id);

    /**
     * 获取聊天客服信息
     * @return array
     */
    public function getChatKefuInfo($uid);

    /**
     * 查询聊天等待信息
     * @param $Openid
     * @return array
     */
    public function getChatWaitInfo($openId);

    /**
     * 判断是否在连接状态
     * @param $page_id
     * @return int
     */
    public function checkConnectCount($page_id, $uid);

    /**
     * 查询等待聊天的用户信息
     * @param   $wait_id
     * @return  array
     */
    public function getChatWaitById($wait_id);

    /**
     * 获取聊天信息和用户初始化信息
     * @param $open_id
     * @return array
     */
    public function getChatMessageAndInitInfo($open_id);

    /**
     * 根据open_id 判断该用户是否正在聊天
     * @param   $open_id
     * @return  int
     */
    public function findNoHideClassLinkCount($open_id);

    /**
     * 根据open_id 找到当前聊天中的ID
     * @param  $openID
     * @return array
     */
    public function findClassLinkIdByOpenId($openId);

    /**
     * 获取其他客服的基础信息
     * @param $kefuId
     * @return array
     */
    public function getWaitKefuList($kefuId);

    /**
     * 获取等待客服的状态
     * @param  $kefuId
     * @return array
     */
    public function getChatWaitByKefu($kefuId);

    /**
     * 断开连接
     * @param   $linkId
     * @return  array
     */
    public function offChatInfo($linkId);

    /**
     * 根据客服的page_id信息,获取信息
     * @param  $pageId
     * @return array
     */
    public function getChatWaitKefuByPageId($pageId);

    /**
     * 根据pageId获取正在聊天中的openid
     */
    public function  getChatMessagePreByPageId($pageId);

    /**
     * 快捷回复
     * @param   $type
     * @return  array
     */
    public function getQuickAnswerList($type);

    /**
     * 查询所有微信新用户数量
     * @return int
     */
    public function getNewChatWaitCount();

    /**
     * 获取不同类型等待用户的的数量  (1:新用户 2:未付费用户 3:付费用户 4:高危用户)
     * @return  int
     */
    public function getOtherChatWaitCount($type);

    /**
     * 获取新用户的信息
     * @param  $offset
     * @param  $limit
     * @return array
     */
    public function getNewChatWaitInfo($offset, $limit);


    /**
     * 获取没有被覆盖的的用户信息
     */
    public function getChatLinkNoHideInfo();

    /**
     * 获取不同类型的美誉被覆盖的用户信息
     * @param  $offset
     * @param  $limit
     * @param  $type  1:新用户 2:未付费用户 3:付费用户 4:高危用户
     * @return array 
     */
    public function getOtherChatWaitInfo($offset, $limit, $type);


    /**
     * 获取当前正在聊天的链路信息
     * @return  array
     */
    public function getChatLinkIsCurrentPage();


    /**
     * 根据openID 得到聊天的信息
     * @param  $open_id
     * @param  $offset
     * @return array
     */
    public function getChatMessageInfoByOpenId($open_id, $offset);


    /**
     * 根据openId  查找聊天链路的客服ID和接待ID
     * @param   $openId
     * @return  array
     */
    public function getChatLinkByOpenId($openId);

    /**
     * @param $uid
     * @param $keyword
     * @return mixed
     * @created by Jhu
     * 获取正在接待用户
     */
    public function getChattingUserById($uid, $keyword);

    /**
     * @param $keyword
     * @return mixed
     * @created by Jhu
     * 获取等待接待用户
     */
    public function getWaitingUser($keyword, $offset, $limit);

    /**
     * @param $kefuId
     * @param $keyword
     * @param $offset
     * @param $limit
     * @return mixed
     * @created by Jhu
     * 根据新钱顾问获取等待接待用户
     */
    public function getWaitingUserBySaleId($kefuId,$keyword,$offset,$limit);

    /**
     * @param $kefuId
     * @param $keyword
     * @param $offset
     * @param $limit
     * @return mixed
     * @created by Jhu
     * 根据复购顾问获取等待接待用户
     */
    public function getWaitingUserByPurchaseId($kefuId,$keyword,$offset,$limit);
    
    /**
     * @param $openId
     * @param $offset
     * @param $limit
     * @return mixed
     * @created by Jhu
     * 获取历史消息
     */
    public function getHistoryMessageByOpenId($openId, $offset, $limit);

    /**
     * @return mixed
     * @created by Jhu
     * 获取所有在线客服和类型
     */
    public function getOnlineKefu();

    /**
     * 获取最早的聊天记录
     * @param  $open_id
     * @param  $kefu_id
     * @return  array
     * create by  wangkai
     */
    public function getChannelChatLinkTime($open_id, $kefu_id);


    /**
     * @param $open_id
     * @param $kefu_id
     * @return mixed
     * create by wangke
     * VIP微课 管理视角 最早的聊天记录
     */
    public function getAllChannelChatLinkTime($open_id, $kefu_id);
}