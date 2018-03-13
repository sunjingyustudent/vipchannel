<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/13
 * Time: 上午11:40
 */
namespace common\sources\write\chat;

use Yii;
use yii\db\ActiveRecord;

interface IChatAccess {

    /**
     * @param $openid
     * @param $filePath
     * @param $type
     * @return mixed
     * @created by Jhu
     * 添加消息到准备表
     */
    public function addChatMessagePre($openid, $filePath, $type);

    /**
     * @param $xml
     * @return mixed
     * @created by Jhu
     * 添加聊天等待
     */
    public function addChatWait($openId, $type);

    /**
     * @param $data
     * @return mixed
     * @created by Jhu
     * 添加在线客服
     */
    public function addKefuWait($uid, $fd);

    /**
     * @param $messageList
     * @param $data
     * @return mixed
     * @created by Jhu
     * 添加聊天记录
     */
    public function addMessage($messageList, $data);

    /**
     * @param $messagePreIdList
     * @return mixed
     * @created by Jhu
     * 删除阻塞消息
     */
    public function deleteMessagePre($messagePreIdList);

    /**
     * @param $fd
     * @return mixed
     * @created by Jhu
     * 客服下线
     */
    public function deleteChatWaitKefuByPage($pageId);

    /**
     * @param $pageId
     * @return mixed
     * @created by Jhu
     * 与相应的page断开连接
     */
    public function disconnectByPageId($pageId);
    
     /**
     * 修改所有用户当前页面
     * @param $open_id
     * @return bool
      * created by wangkai
     */
    public function updateAllChatLinkStatus($open_id);

    /**
     * 修改具体用户的当前页面
     * @param  $id
     * @return int
     * created by wangkai
     */
    public function updateChatLinkStatus($id);

    /**
     * 新建聊天信息
     * created by wangkai
     */
    public function addChatMessage($openId,$content,$uid);

    /**
     * 取消客服高危状态
     * created by wangkai
     */
    public function updateChatLinkIsHide($uid);

    /**
     * 修改连接状态为在线以及取消高位状态
     * created by wangkai
     */
    public  function doEditChatLinkStatus($openId, $page_id, $uid);

    /**
     * 删除等待状态
     * created by wangkai
     */
    public function deleteChatWait($openId);

    /**
     * 标记高位用户
     * created by wangkai
     */
    public function editChatLinkSignHide($open_id);

    /**
     * 修改聊天状态
     * @param $id
     * created by wangkai
     */
    public function editLinkInfoStatus($id);

    /**
     * 根据客服ID 和OpenID 修改聊天链路
     * created by wangkai
     */
    public  function editChatLinkByKefuId($kefuId, $open_id);

    /**
     * 修改全部的客服ID
     * created by wangkai
     */
    public function updateAllCountersByKefu($kefuId);


    /**
     * 添加课程状态
     * @param $kefuId
     * @param $open_id
     * @param $page_id
     * @return bool
     * created by wangkai
     */
    public function addClassLinkBykefu($kefuId, $open_id, $page_id);


    /**
     * 修改错误的聊天记录
     * @param  $id
     * created by wangkai
     */
    public function editChatMessageFail($id);

    /**
     * 删除聊天等待的客服
     * @param $id
     * created by wangkai
     */
    public function deleteChatWaitKefu($id);

    /**
     * 添加响应日志
     * @param $open_id
     * @param $name
     */
    public function addResponseLog($open_id, $name);

    /**
     * 添加快捷回复
     * @param $type
     * @param $content
     * created by wangkai
     */
    public function addQuickAnswer($type, $content);

    /**
     * 添加发送链接
     * @param $open_id
     * created by huanglonglong
     */
    public function addSendUrl($open_id);

    /**
     * 添加聊天模板信息
     * @param $open_id
     * @param $content
     * created by wangkai
     */
    public  function addChatMessageMould($open_id, $content);

    /**
     * 删除快捷信息
     * @param $id
     * @return  
     * create by  wangkai
     */
    public function doDeleteQucikAnswer($id);

    /**
     * 编辑快捷信息
     * @param  $id
     * @param  $content
     * @return  array
     * create by  wangkai
     */
    public function doEditQucikAnswer($id, $content);

    /**
     * @param $linkId
     * @return mixed
     * @created by Jhu
     * 更新当前页
     */
    public function updateCurrentPage($linkId);

    /**
     * @param $linkId
     * @return mixed
     * @created by Jhu
     * 退出当前聊天页
     */
    public function updateCurrentPageOut($linkId);

    /**
     * @param $open_id
     * @param $page_id
     * @param $uid
     * @return mixed
     * @created by Jhu
     * 添加聊天连接
     */
    public function addChatLink($open_id, $page_id, $uid);

    /**
     * 修改聊天内容根据page_id
     * @param
     * @return  array
     * create by  wangkai
     */
    public function updateChatConnectByPageId($pageId);
}