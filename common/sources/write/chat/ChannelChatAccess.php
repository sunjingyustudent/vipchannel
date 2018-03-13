<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/3/3
 * Time: 上午10:44
 */
namespace common\sources\write\chat;

use common\models\music\ChannelChatLink;
use common\models\music\ChannelChatMessage;
use common\models\music\ChannelChatMessagePre;
use common\models\music\ChannelChatWait;
use common\models\music\ChannelChatWaitKefu;
use Yii;
use yii\db\ActiveRecord;

Class ChannelChatAccess implements IChannelChatAccess {

    public function addChatMessagePre($openid, $filePath, $type)
    {
        $model = new ChannelChatMessagePre();

        $model->open_id = $openid;
        $model->message = $filePath;
        $model->type = $type;
        $model->time_created = time();

        return $model->save();
    }

    public function addKefuWait($uid, $fd)
    {
        $chat = new ChannelChatWaitKefu();

        $chat->kefu_id = $uid;
        $chat->page_id = $fd;

        return $chat->save();
    }

    public function addMessage($messageList, $data)
    {
        $chat = new ChannelChatMessage();

        foreach ($messageList as $message)
        {
            $_chat = clone $chat;

            $_chat->open_id = $data['open_id'];
            $_chat->kefu_id = $data['kefu_id'];
            $_chat->message = $message['message'];
            $_chat->type = $message['type'];
            $_chat->tag = 0;
            $_chat->time_created = $message['time_created'];
            $_chat->is_read = 1;

            $_chat->save();
        }

        return true;
    }

    public function deleteMessagePre($messagePreIdList)
    {
        return ChannelChatMessagePre::deleteAll(['id' => $messagePreIdList]);
    }

    public function editLinkInfoStatus($id)
    {
        $sql = 'UPDATE channel_chat_link SET is_connect = 0,is_current_page = 0 WHERE id=:id';

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':id', $id)
            ->execute();
    }

    public  function editChatLinkByKefuId($kefuId, $open_id)
    {
        ChannelChatLink::updateAll(['is_hide' => 1], ['kefu_id' => $kefuId, 'open_id' => $open_id]);
    }

    public function updateAllCountersByKefu($kefuId)
    {
        ChannelChatLink::updateAllCounters(['sort' => 1], ['kefu_id' => $kefuId, 'is_hide' => 0]);
    }

    public function addClassLinkBykefu($kefuId, $open_id, $page_id)
    {
        $chatLink = new ChannelChatLink();
        $chatLink->open_id = $open_id;
        $chatLink->kefu_id = $kefuId;
        $chatLink->page_id = $page_id;
        $chatLink->is_connect = 1;
        $chatLink->sort = 0;
        return $chatLink->save();
    }

    public function deleteChatWaitKefu($id)
    {
        $sql = 'DELETE FROM channel_chat_wait_kefu WHERE id=:id';

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':id', $id)
            ->execute();
    }

    public function updateChatConnectByPageId($pageId)
    {
        ChannelChatLink::updateAll(['is_connect' => 0, 'is_current_page' => 0], ['page_id' => $pageId]);
    }

    public function addChatWait($open_id, $type)
    {
        $sql = "INSERT INTO channel_chat_wait (open_id, type) VALUES(:open_id, :type) ON DUPLICATE KEY UPDATE count = count + 1";

        Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':open_id' => $open_id,
                ':type' => $type
            ])->execute();

        return Yii::$app->db->getLastInsertID();
    }

    public function deleteChatWaitKefuByPage($fd)
    {
        $sql = "DELETE FROM channel_chat_wait_kefu WHERE page_id = :page_id";

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':page_id', $fd)
            ->execute();
    }

    public function disconnectByPageId($pageId)
    {
        return ChannelChatLink::updateAll(
            ['is_connect' => 0, 'is_current_page' => 0],
            ['page_id' => $pageId]
        );
    }

    public function addChatMessage($openId,$content,$uid)
    {
        $message = new ChannelChatMessage();
        $message->open_id = $openId;
        $message->kefu_id = $uid;
        $message->message = $content;
        $message->type = 1;
        $message->tag = 1;
        $message->time_created = time();

        $message->save();

        return $message->id;
    }

    public function updateChatLinkIsHide($uid)
    {
        return ChannelChatLink::updateAllCounters(['sort' => 1], ['kefu_id' => $uid, 'is_hide' => 0]);

    }

    public function doEditChatLinkStatus($openId, $page_id, $uid)
    {
        $link = ChannelChatLink::find()
            ->where([
                'open_id' => $openId,
                'is_hide' => 0,
                'kefu_id' => $uid])
            ->one();

        $link->page_id = $page_id;
        $link->is_connect = 1;
        $link->is_current_page = 1;
        $link->sort = 0;
        return  $link->save();
    }

    public function deleteChatWait($openId)
    {
        $sql = 'DELETE FROM channel_chat_wait WHERE open_id = :openId';

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':openId', $openId)
            ->execute();
    }

    public function editChatMessageFail($id)
    {
        $sql = 'UPDATE channel_chat_message SET is_fail = 1 WHERE id=:id';

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':id', $id)
            ->execute();
    }


    public function addChatMessageInfo($openId, $key, $uid)
    {
        $chat = new ChannelChatMessage();
        $chat->open_id = $openId;
        $chat->kefu_id = $uid;
        $chat->message = $key;
        $chat->type = 2;
        $chat->tag = 1;
        $chat->time_created = time();

        return  $chat->save();
    }

    public function editChatLinkSignHide($open_id)
    {
        ChannelChatLink::updateAll(['is_hide' => 1], ['kefu_id' => Yii::$app->user->identity->id, 'open_id' => $open_id]);
    }

    public function editChatLinkByKefu()
    {
        ChannelChatLink::updateAll(['is_current_page' => 0], ['kefu_id' => Yii::$app->user->identity->id, 'is_current_page' => 1]);
    }

    public function addChatLink($open_id, $page_id, $uid)
    {
        $chatLink = new ChannelChatLink();
        $chatLink->open_id = $open_id;
        $chatLink->kefu_id = $uid;
        $chatLink->page_id = $page_id;
        $chatLink->is_connect = 1;
        $chatLink->is_current_page = 1;
        $chatLink->sort = 0;
        return  $chatLink->save();
    }

    public function updateAllChannelChatLinkStatus($open_id)
    {
        return ChannelChatLink::updateAll(['is_current_page' => 0], 'kefu_id = :kefu_id AND is_current_page = 1 AND open_id != :open_id', [':kefu_id' => Yii::$app->user->identity->id, ':open_id' => $open_id]);
    }

    public function updateChannelChatLinkStatus($id)
    {
        $sql = 'UPDATE channel_chat_link SET is_current_page = 1 WHERE id=:id';

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':id', $id)
            ->execute();
    }

    public function addQuickAnswer($type, $content)
    {
        $sql = "INSERT INTO channel_quick_answer (type, content,is_deleted) VALUES(:type, :content,0)";

        Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':type' => $type,
                ':content' => $content
            ])->execute();

        return Yii::$app->db->getLastInsertID();

    }

    public function doEditQucikAnswer($id, $content)
    {
        $sql = 'UPDATE channel_quick_answer SET content = :content WHERE id = :id';

        return  Yii::$app->db->createCommand($sql)
            ->bindValues([':content' => $content, ':id' => $id])
            ->execute();
    }

    public function doDeleteQucikAnswer($id)
    {
        $sql = 'DELETE FROM channel_quick_answer WHERE id = :id';

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':id', $id)
            ->execute();
    }

    public function doSaveChatMessage($kefu_id, $openid, $message)
    {
        $chat = new ChannelChatMessage();

        $chat->open_id = $openid;
        $chat->kefu_id = $kefu_id;
        $chat->message = $message;
        $chat->type = 1;
        $chat->tag = 1;
        $chat->time_created = time();
        $chat->is_read = 0;
        $chat->is_fail = 0;

        return $chat->save();
    }

    public function doPassiveSaveChatMessage($open_id, $message)
    {
        $chat = new ChannelChatMessagePre();
        $chat->open_id = $open_id;
        $chat->message = $message;
        $chat->type = 1;
        $chat->time_created = time();

        return $chat->save();
    }

    public function readMessageByOpenId($open_id)
    {
        $sql = 'UPDATE channel_chat_message SET is_read = 1 WHERE tag=0 AND is_read = 0 AND open_id = :open_id';

        return  Yii::$app->db->createCommand($sql)
            ->bindValues([':open_id' => $open_id])
            ->execute();       
    }
}