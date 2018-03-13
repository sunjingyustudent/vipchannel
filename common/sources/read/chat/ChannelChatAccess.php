<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/3/3
 * Time: 上午10:43
 */
namespace common\sources\read\chat;

use common\models\music\ChannelChatLink;
use common\models\music\ChannelChatMessage;
use common\models\music\ChannelChatMessagePre;
use common\models\music\ChannelChatWait;
use common\models\music\ChannelChatWaitKefu;
use common\models\music\ChannelQuickAnswer;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;

class ChannelChatAccess implements IChannelChatAccess
{
    public function getUserCount($type)
    {
        $query = new Query();

        $query->select("count(*)");
        //时间格式输出
        $query->from('channel_chat_wait a');
        $query->leftJoin('sales_channel c', 'c.bind_openid = a.open_id');
        $query->where('c.status = 1');

        if ($type) {
            $query->andWhere('a.type = :type AND c.kefu_id = :uid', [':type' => $type, ':uid' => Yii::$app->user->identity->id]);
        } else {
            //全部用户 1:新用户消息 2:推广用户消息 3:无价值消息 4:提现消息 5 当日新用户
            $query->andWhere('a.type >= :type', [':type' => $type]);
        }

        $query->groupBy('a.open_id');
        //如果发过来的消息是空的就在前面

        return  $query->count();
    }

    public function getUserList($type, $num)
    {
        return ChannelChatWait::find()
            ->alias('c')
            ->select('u.nickname, u.head, u.created_at, a.nickname AS kefu_nickname, c.open_id, province, c.id')
            ->leftJoin('sales_channel AS u', 'u.bind_openid = c.open_id')
            ->leftJoin('user_account AS a', 'a.id = u.kefu_id')
            ->where('u.status = 1 '
                . (!empty($type) ? ' AND c.type = :type AND u.kefu_id = '.Yii::$app->user->identity->id : ' AND c.type >= :type '), [
                ':type' => $type
                ])
            ->offset(($num - 1)*4)
            ->limit(4)
            ->asArray()
            ->all();
    }

    public function getPageId($uid)
    {
        return ChannelChatWaitKefu::find()
            ->select('page_id')
            ->where(['kefu_id' => $uid])
            ->scalar();
    }

    public function getAccessMessage($openId)
    {
        return ChannelChatMessagePre::find()
            ->alias('m')
            ->select('m.*, s.wechat_name as name, s.head')
            ->leftJoin('sales_channel AS s', 's.bind_openid = m.open_id')
            ->where(['m.open_id' => $openId])
            ->orderBy('m.time_created ASC')
            ->asArray()
            ->all();
    }

    public function getCountLinkByOpenid($linkId)
    {
        return  ChannelChatLink::find()
            ->where(['id' => $linkId])
            ->asArray()
            ->one();
    }

    public function getChatWaitByKefu($kefuId)
    {
        return  ChannelChatWaitKefu::find()
            ->where(['kefu_id' => $kefuId])
            ->asArray()
            ->one();
    }

    public function getChatWaitKefuByPageId($pageId)
    {
        return ChannelChatWaitKefu::find()
            ->where(['page_id' => $pageId])
            ->asArray()
            ->one();
    }

    public function getChatMessagePreByPageId($pageId)
    {
        return  ChannelChatMessagePre::find()
            ->select('c.open_id, c.message')
            ->alias('c')
            ->leftJoin('chat_link as l', 'l.open_id = c.open_id')
            ->where(['l.page_id' => $pageId, 'l.is_connect' => 1])
            ->groupBy('c.open_id')
            ->asArray()
            ->all();
    }

    public function checkIsConncet($uid, $openId, $fd)
    {
        return ChannelChatLink::find()
            ->where([
                'kefu_id' => $uid,
                'open_id' => $openId,
                'page_id' => $fd,
                'is_connect' => 1,
            ])->count();
    }

    public function getAllKeFu()
    {
        return ChannelChatWaitKefu::find()->asArray()->all();
    }

    public function getConnectedOpenid($pageId)
    {
        return ChannelChatMessagePre::find()
            ->alias('c')
            ->select('c.open_id')
            ->leftJoin('channel_chat_link AS l', 'l.open_id = c.open_id')
            ->where('l.page_id = :page_id AND l.is_connect = 1', [
                ':page_id' => $pageId
            ])
            ->groupBy('c.open_id')
            ->asArray()
            ->column();
    }

    public function countLinkInfoByOpenid($openid)
    {
        return ChannelChatLink::find()
            ->where([
                'open_id' => $openid,
                'is_connect' => 1
            ])
            ->asArray()
            ->one();
    }

    public function getChatKefuInfo($uid)
    {
        return ChannelChatWaitKefu::find()
            ->where(['kefu_id' => $uid])
            ->asArray()
            ->one();
    }

    public function findClassLinkIdByOpenId($openId)
    {
        return  ChannelChatLink::find()->select('id')
            ->where(['open_id' => $openId, 'is_connect' => 1, 'is_hide' => 0])
            ->asArray()
            ->one();
    }

    public function getChatWaitInfo($openId)
    {
        return  ChannelChatWait::find()
            ->where(['open_id' => $openId])
            ->one();
    }

    public function offChatInfo($linkId)
    {
        return ChannelChatLink::find()
            ->where(['id' => $linkId, 'kefu_id' => Yii::$app->user->identity->id, 'is_connect' => 1])
            ->asArray()
            ->one();
    }

    public function checkConnectCount($pageId, $uid)
    {
        return ChannelChatWaitKefu::find()
            ->where(['kefu_id' => $uid, 'page_id' => $pageId])
            ->count();
    }


    public function getChatLinkIsCurrentPage()
    {
        return ChannelChatLink::find()
            ->where(['kefu_id' => Yii::$app->user->identity->id, 'is_current_page' => 1])
            ->asArray()
            ->one();
    }

    public function getChatMessageInfoByOpenId($openId, $offset)
    {
        $query = new Query();
        return  $query->select('m.id, m.message, m.tag, m.type, m.time_created, u.head, u.nickname as student_name, uk.nickname as kefu_name, uk.head as kefu_head')
            ->from('channel_chat_message AS m')
            ->leftJoin('sales_channel AS u', 'u.bind_openid = m.open_id')
            ->leftJoin('user_account AS uk', 'uk.id = m.kefu_id')
            ->where(['m.open_id' => $openId])
            ->orderBy('m.time_created DESC, m.id DESC')
            ->offset($offset)
            ->limit(10)
            ->all();
    }

    public function getChatWaitById($waitId)
    {
        return ChannelChatWait::find()
            ->where(['id' => $waitId])
            ->asArray()
            ->one();
    }

    public function findNoHideClassLinkCount($openId)
    {
        return ChannelChatLink::find()
            ->where(['open_id' => $openId, 'is_connect' => 1, 'is_hide' => 0])
            ->count();
    }


    public function countLinkByOpenid($openid)
    {
        return ChannelChatLink::find()
            ->where([
                'open_id' => $openid,
                'is_connect' => 1
            ])->count();
    }

    public function getChatMessageAndInitInfo($openId)
    {
        $query = new Query();
        return  $query->select('m.id, m.message, m.tag, m.type, m.time_created, uk.nickname as kefu_name, uk.head as kefu_head, m.is_fail')
            ->from('channel_chat_message AS m')
            ->leftJoin('user_account AS uk', 'uk.id = m.kefu_id')
            ->where(['m.open_id' => $openId])
            ->orderBy('m.time_created DESC,m.id DESC')
            ->limit(10)
            ->all();
    }

    public function getWaitKefuList($kefuId)
    {
        $sql = "SELECT k.kefu_id, u.nickname, u.head, k.page_id FROM channel_chat_wait_kefu AS k"
            . " LEFT JOIN user_account AS u ON u.id = k.kefu_id"
            . " WHERE k.kefu_id != :kefu_id";

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':kefu_id', $kefuId)
            ->queryAll();
    }

    public function getChatLinkByOpenId($openId)
    {
        return ChannelChatLink::find()->select('id, kefu_id, page_id')
            ->where(['open_id' => $openId, 'is_connect' => 1])
            ->asArray()
            ->one();
    }

    public function countChannelWaitByOpenid($openId)
    {
        return ChannelChatWait::find()
            ->where(['open_id' => $openId])
            ->count();
    }

    public function getCountChannelChatLinkById($linkId)
    {
        return ChannelChatLink::find()
            ->where(['id' => $linkId])
            ->asArray()
            ->one();
    }

    public function getChannelChatMessageInfo($openId)
    {
        $query = new Query();
        
        return  $query->select('m.id, m.message, m.tag, m.type, m.time_created, u.head, u.nickname as student_name, ua.nickname as kefu_name, ua.head as kefu_head, m.is_fail')
            ->from('channel_chat_message AS m')
            ->leftJoin('sales_channel AS u', 'u.bind_openid = m.open_id')
            ->leftJoin('user_account AS ua', 'ua.id = m.kefu_id')
            ->where(['m.open_id' => $openId])
            ->orderBy('m.time_created DESC, m.id DESC')
            ->limit(10)
            ->all();
    }

    public function checkHaveTalk($openId)
    {
        return ChannelChatMessage::find()
            ->where(['open_id' => $openId])
            ->limit(1)
            ->count();
    }

    public function getQuickAnswerList($type)
    {
        return  ChannelQuickAnswer::find()
            ->where(['type' => $type, 'is_deleted' => 0])
            ->asArray()
            ->all();
    }

    public function getChannelWaitMessageUser($type, $num, $message = '')
    {
        $query = new Query();

        $query->select("a.id, c.nickname, c.head,d.nickname kefu_nickname,c.province");
        //时间格式输出
        $query->addselect(['FROM_UNIXTIME(c.created_at,"%Y-%m-%d %H:%i:%s") as created_at']);
        $query->from('channel_chat_wait a');
        $query->leftJoin('sales_channel c', 'c.bind_openid = a.open_id');
        $query->leftJoin('user_account d', 'd.id = c.kefu_id');
        $query->where('c.status = 1');

        if ($type) {
            $query->andWhere('a.type = :type AND c.kefu_id = :uid', [':type' => $type, ':uid' => Yii::$app->user->identity->id]);
        } else {
            //全部用户 1:新用户消息 2:推广用户消息 3:无价值消息 4:提现消息 5 当日新用户
            $query->andWhere('a.type >= :type', [':type' => $type]);
        }

        $query->groupBy('a.open_id');
        //如果发过来的消息是空的就在前面
        $query->orderBy('a.id DESC');
        $query->offset(($num - 1)*4);
        $query->limit(4);
        return $query->all();
    }

    public function getKeywords()
    {
        $query = new Query();
        
        $query->select('word');
        $query->from('validate_keyword');
        $query->where('is_delete = 0');
        return $query->all();
    }

    public function getLeftUserByOpenId($openId)
    {
        $query = new Query();

        $query->select('a.id,a.open_id,b.wechat_name name,b.head, b.message_type');
        $query->from('channel_chat_link a');
        
        $query->leftJoin('sales_channel b', 'b.bind_openid = a.open_id');
        $query->where("a.is_connect = 1 AND a.is_hide=0 AND a.open_id=:open_id", [':open_id'=>$openId]);
        return $query->one();
    }
}
