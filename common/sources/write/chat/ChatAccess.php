<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/13
 * Time: 上午11:41
 */
namespace common\sources\write\chat;

use common\models\music\ChannelChatLink;
use common\models\music\ChatLink;
use common\models\music\ChatMessage;
use common\models\music\ChatMessagePre;
use common\models\music\ChatWait;
use common\models\music\ChatWaitKefu;
use Yii;
use yii\db\ActiveRecord;

Class ChatAccess implements IChatAccess {
    
    public function addChatMessagePre($openid, $filePath, $type)
    {
        $model = new ChatMessagePre();
        
        $model->open_id = $openid;
        $model->message = $filePath;
        $model->type = $type;
        $model->time_created = time();
        
        return $model->save();
    }
    
    public function updateAllChatLinkStatus($open_id)
    {
        return ChatLink::updateAll(['is_current_page' => 0], 'kefu_id = :kefu_id AND is_current_page = 1 AND open_id != :open_id', [':kefu_id' => Yii::$app->user->identity->id, ':open_id' => $open_id]);

    }



    public function updateChatLinkStatus($id)
    {
        $sql = 'UPDATE chat_link SET is_current_page = 1 WHERE id=:id';

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':id', $id)
            ->execute();
    }



    public function addChatMessage($openId,$content,$uid)
    {
        $message = new ChatMessage();
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
        return ChatLink::updateAllCounters(['sort' => 1], ['kefu_id' => $uid, 'is_hide' => 0]);

    } 


    public  function doEditChatLinkStatus($openId, $page_id, $uid)
    {
        $link = ChatLink::find()
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
        $sql = 'DELETE FROM chat_wait WHERE open_id = :openId';

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':openId', $openId)
            ->execute();
    }

    public function editChatLinkSignHide($open_id)
    {
        ChatLink::updateAll(['is_hide' => 1], ['kefu_id' => Yii::$app->user->identity->id, 'open_id' => $open_id]);
    }


    public function editChatLinkByKefu()
    {
        ChatLink::updateAll(['is_current_page' => 0], ['kefu_id' => Yii::$app->user->identity->id, 'is_current_page' => 1]);
    }

    public function addChatLink($open_id, $page_id, $uid)
    {
        $chatLink = new ChatLink();
        $chatLink->open_id = $open_id;
        $chatLink->kefu_id = $uid;
        $chatLink->page_id = $page_id;
        $chatLink->is_connect = 1;
        $chatLink->is_current_page = 1;
        $chatLink->sort = 0;
        return  $chatLink->save();
    }

    public function editLinkInfoStatus($id)
    {
        $sql = 'UPDATE chat_link SET is_connect = 0,is_current_page = 0 WHERE id=:id';

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':id', $id)
            ->execute();
    }

    public  function editChatLinkByKefuId($kefuId, $open_id)
    {
       ChatLink::updateAll(['is_hide' => 1], ['kefu_id' => $kefuId, 'open_id' => $open_id]);
    }

    public function updateAllCountersByKefu($kefuId)
    {
       ChatLink::updateAllCounters(['sort' => 1], ['kefu_id' => $kefuId, 'is_hide' => 0]);
    }


    public function addClassLinkBykefu($kefuId, $open_id, $page_id)
    {
        $chatLink = new ChatLink();
        $chatLink->open_id = $open_id;
        $chatLink->kefu_id = $kefuId;
        $chatLink->page_id = $page_id;
        $chatLink->is_connect = 1;
        $chatLink->sort = 0;
        return $chatLink->save();
    }

 
    public function editChatMessageFail($id)
    {
        $sql = 'UPDATE chat_message SET is_fail = 1 WHERE id=:id';

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':id', $id)
            ->execute();
    }

    public function deleteChatWaitKefu($id)
    {
        $sql = 'DELETE FROM chat_wait_kefu WHERE id=:id';

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':id', $id)
            ->execute(); 
    }

    public function updateChatConnectByPageId($pageId)
    {
         ChatLink::updateAll(['is_connect' => 0, 'is_current_page' => 0], ['page_id' => $pageId]);
    }


    public function addChatWait($open_id, $type)
    {
        $sql = "INSERT INTO chat_wait (open_id, type) VALUES(:open_id, :type) ON DUPLICATE KEY UPDATE count = count + 1";

        Yii::$app->db->createCommand($sql)
                     ->bindValues([
                            ':open_id' => $open_id,
                            ':type' => $type
                        ])->execute();

        return Yii::$app->db->getLastInsertID();
    }


    public function addResponseLog($open_id, $name)
    {
        $sql = "INSERT INTO chat_wait (open_id, name, time_created) VALUES(:open_id, :name, :time_created)";

        Yii::$app->db->createCommand($sql)
                     ->bindValues([
                            ':open_id' => $open_id,
                            ':name' => $name,
                            ':time_created' => time()
                        ])->execute(); 
                     
        return Yii::$app->db->getLastInsertID();
    }


    public function addQuickAnswer($type, $content)
    {
        $sql = "INSERT INTO quick_answer (type, content,is_deleted) VALUES(:type, :content,0)";

         Yii::$app->db->createCommand($sql)
                     ->bindValues([
                            ':type' => $type,
                            ':content' => $content
                        ])->execute();

        return Yii::$app->db->getLastInsertID();
                     
    }

    public function addSendUrl($open_id)
    {
        $sql = "INSERT INTO gold_ege (openid) VALUES(:openid)";

        Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':openid' => $open_id
            ])->execute();

        return Yii::$app->db->getLastInsertID();

    }


    public  function addChatMessageMould($open_id, $content)
    {
        $message = new ChatMessage();
        $message->open_id = $open_id;
        $message->kefu_id = Yii::$app->user->identity->id;
        $message->message = $content . ' (模版消息)';
        $message->type = 1;
        $message->tag = 1;
        $message->time_created = time();

        return $message->save();
    }

    public function addKefuWait($uid, $fd)
    {
        $chat = new ChatWaitKefu();

        $chat->kefu_id = $uid;
        $chat->page_id = $fd;
        
        return $chat->save();
    }

    public function addMessage($messageList, $data)
    {
        $chat = new ChatMessage();

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
        return ChatMessagePre::deleteAll(['id' => $messagePreIdList]);
    }

    public function deleteChatWaitKefuByPage($fd)
    {
        $sql = "DELETE FROM chat_wait_kefu WHERE page_id = :page_id";
        return Yii::$app->db->createCommand($sql)
            ->bindValue(':page_id', $fd)
            ->execute();
    }

    public function disconnectByPageId($pageId)
    {
        return ChatLink::updateAll(
            ['is_connect' => 0, 'is_current_page' => 0],
            ['page_id' => $pageId]
        );
    }

    public function addChatMessageInfo($openId, $key)
    {
        $chat = new ChatMessage();
        $chat->open_id = $openId;
        $chat->kefu_id = Yii::$app->user->identity->id;
        $chat->message = $key;
        $chat->type = 2;
        $chat->tag = 1;
        $chat->time_created = time();

        return  $chat->save();
    }

    public function doEditQucikAnswer($id, $content)
    {
        $sql = 'UPDATE quick_answer SET content = :content WHERE id = :id';

        return  Yii::$app->db->createCommand($sql)
                        ->bindValues([':content' => $content, ':id' => $id])
                        ->execute();
    }


    public function doDeleteQucikAnswer($id)
    {
        $sql = 'DELETE FROM quick_answer WHERE id = :id';

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':id', $id)
            ->execute();
    }

    public function updateCurrentPage($linkId)
    {
        $sql = "UPDATE chat_link SET is_current_page = 1 WHERE id = :id";

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':id', $linkId)
            ->execute();
    }

    public function updateCurrentPageOut($linkId)
    {
        $sql = "UPDATE chat_link SET is_current_page = 0 WHERE id = :id";

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':id', $linkId)
            ->execute();
    }

}