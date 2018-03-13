<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/13
 * Time: 上午11:41
 */
namespace common\sources\read\chat;

use common\models\music\ChannelChatLink;
use common\models\music\ChannelChatMessage;
use common\models\music\ChatLink;
use common\models\music\ChatMessage;
use common\models\music\ChatMessagePre;
use common\models\music\ChatWait;
use common\models\music\ChatWaitKefu;
use common\models\music\WechatAcc;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;
use common\models\music\QuickAnswer;
use common\models\music\GoldEge;


Class ChatAccess implements IChatAccess 
{

    public function countLinkByOpenid($openid)
    {
        return ChatLink::find()
            ->where([
                'open_id' => $openid,
                'is_connect' => 1,
                'is_hide' => 0
            ])->count();
    }


    public function countLinkInfoByOpenid($openid)
    {
        return ChatLink::find()
            ->where([
                'open_id' => $openid,
                'is_connect' => 1
            ])
            ->asArray()
            ->one();
    }   

    public function countStudentWaitByOpenid($openid)
    {
        return ChatWait::find()
            ->where([
                'open_id' => $openid
            ])->count();
    }


    public function  getWechatAccByopenid($student_id)
    {
        return WechatAcc::find()
                    ->select('id, openid')
                    ->where(['uid' => $student_id])
                    ->asArray()
                    ->one();
    }

    public function getWechatAccByExist($openId)
    {
        return  WechatAcc::find()
                    ->where(['openid' => $openId])
                    ->one();
    }

    public function getPageId($uid)
    {
        return ChatWaitKefu::find()
            ->select('page_id')
            ->where(['kefu_id' => $uid])
            ->scalar();
    }

    public function getAccessMessage($openId)
    {
        return ChatMessagePre::find()
            ->alias('m')
            ->select('m.*, u.name, u.head')
            ->leftJoin('user_init AS u', 'u.openid = m.open_id')
            ->where(['m.open_id' => $openId])
            ->orderBy('m.time_created ASC')
            ->asArray()
            ->all();
    }

    public function checkIsConncet($uid, $openId, $fd)
    {
        return ChatLink::find()
            ->where([
                'kefu_id' => $uid,
                'open_id' => $openId,
                'page_id' => $fd,
                'is_connect' => 1,
            ])->count();
    }

    public function getAllKeFu()
    {
        return ChatWaitKefu::find()->asArray()->all();
    }

    public function getConnectedOpenid($pageId)
    {
        return ChatMessagePre::find()
            ->alias('c')
            ->select('c.open_id')
            ->leftJoin('chat_link AS l', 'l.open_id = c.open_id')
            ->where('l.page_id = :page_id AND l.is_connect = 1', [
                ':page_id' => $pageId
            ])
            ->groupBy('c.open_id')
            ->asArray()
            ->column();
    }

    public function getChatHistoryCount($request)
    {

        $sql = "SELECT COUNT(u.nickname) AS count FROM chat_message AS c"
            . " LEFT JOIN user_init AS ui ON ui.openid = c.open_id"
            . " LEFT JOIN user_account AS u ON u.id = c.kefu_id"
            . " WHERE c.time_created >= :time_start AND c.time_created <= :time_end"
            . " AND u.nickname = '{$request['keyword']}'" ;

        return Yii::$app->db->createCommand($sql)
                            ->bindValues([':time_start'=>$request['time_start'], ':time_end'=>$request['time_end']])
                            ->queryScalar();
    }

    public function getChatHistoryList($request)
    {
        $sql = "SELECT c.id, u.nickname, ui.name, c.message, c.type, c.tag, c.time_created, u.head AS kefu_head, ui.head, c.is_fail  FROM chat_message AS c"
            . " LEFT JOIN user_init AS ui ON ui.openid = c.open_id"
            . " LEFT JOIN user_account AS u ON u.id = c.kefu_id"
            . " WHERE c.time_created >= :time_start AND c.time_created <= :time_end"
            . " AND u.nickname = '{$request['keyword']}'"
            . " LIMIT :offset, :limit";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':time_start'=>$request['time_start'], ':time_end'=>$request['time_end'], ':offset' => ($request['page_num'] -1)*10, ':limit' => 10])
            ->queryAll();
    }
    
    public function getLeftUserInfo($is_history, $keyword)
    {
        $query = new Query();

        $model = $query->select('l.id as link_id, l.open_id, u.name, u.head, up.purchase , up.is_high')
            ->from('chat_link AS l')
            ->leftJoin('user_init AS u', 'u.openid = l.open_id')
            ->leftJoin('user_public_info AS up', 'up.open_id = l.open_id')
            ->where(['l.kefu_id' => Yii::$app->user->identity->id, 'l.is_hide' => 0, 'l.is_connect' => empty($is_history) ? 1 : 0])
            ->andWhere('u.name LIKE "%'. $keyword .'%"')
            ->orderBy('l.sort ASC');

        if ($is_history == 1)
        {
            return $model->limit(20)->all();
        } else {
            return $model->all();
        }
    }


    public function getChannelLeftUserInfo($is_history, $keyword)
    {
        $query = new Query();

        $query->select('a.id link_id,a.open_id,b.wechat_name name,b.head, b.message_type');
        $query->from('channel_chat_link a');
        $query->leftJoin('sales_channel b','b.bind_openid = a.open_id');

        $query->where("a.is_hide=0 AND a.is_connect=$is_history AND b.status=1 AND a.kefu_id=:kefu_id");
        $query->params([':kefu_id' => Yii::$app->user->identity->id]);
        if($keyword){
            $query->andWhere(['LIKE','b.wechat_name',$keyword]);
        }

        $query->groupBy('a.open_id');
        $query->orderBy('a.sort ASC');
        return $query->all();
    }


    public function getCountLinkByOpenid($link_id)
    {
        return  ChatLink::find()
                    ->where(['id' => $link_id])
                    ->asArray()
                    ->one();
    }



    public function getChatMessageInfo($open_id)
    {
        $query = new Query();
        return  $query->select('m.id, m.message, m.tag, m.type, m.time_created, u.head, u.name as student_name, ua.nickname as kefu_name, ua.head as kefu_head, m.is_fail')
            ->from('chat_message AS m')
            ->leftJoin('user_init AS u', 'u.openid = m.open_id')
            ->leftJoin('user_account AS ua', 'ua.id = m.kefu_id')
            ->where(['m.open_id' => $open_id])
            ->orderBy('m.time_created DESC, m.id DESC')
            ->limit(10)
            ->all();
    }

    public function getChatKefuInfo($uid)
    {
        return ChatWaitKefu::find()
                    ->where(['kefu_id' => $uid])
                    ->asArray()
                    ->one();
    }

    public function getChatWaitInfo($openId)
    {
        return  ChatWait::find()
                    ->where(['open_id' => $openId])
                    ->one();
    }

    public function checkConnectCount($page_id, $uid)
    {
        return ChatWaitKefu::find()
            ->where(['kefu_id' => $uid, 'page_id' => $page_id])
            ->count();
    }

    public function getChatWaitById($wait_id)
    {
        return ChatWait::find()
            ->where(['id' => $wait_id])
            ->asArray()
            ->one();
    }

    public function getChatMessageAndInitInfo($open_id)
    {
        $query = new Query();
        return  $query->select('m.id, m.message, m.tag, m.type, m.time_created, u.head, u.name as student_name, uk.nickname as kefu_name, uk.head as kefu_head, m.is_fail')
                    ->from('chat_message AS m')
                    ->leftJoin('user_init AS u', 'u.openid = m.open_id')
                    ->leftJoin('user_account AS uk', 'uk.id = m.kefu_id')
                    ->where(['m.open_id' => $open_id])
                    ->orderBy('m.time_created DESC, m.id DESC')
                    ->limit(10)
                    ->all();
    }


    public function findNoHideClassLinkCount($open_id)
    {
        return ChatLink::find()
                ->where(['open_id' => $open_id, 'is_connect' => 1, 'is_hide' => 0])
                ->count();
    }

    public function findClassLinkIdByOpenId($openId)
    {
        return  ChatLink::find()->select('id')
            ->where(['open_id' => $openId, 'is_connect' => 1, 'is_hide' => 0])
            ->asArray()
            ->one();
    }

    public function getWaitKefuList($kefuId)
    {
        $sql = "SELECT k.kefu_id, u.nickname, u.head, k.page_id FROM chat_wait_kefu AS k"
            . " LEFT JOIN user_account AS u ON u.id = k.kefu_id"
            . " WHERE k.kefu_id != :kefu_id";
        
        return Yii::$app->db->createCommand($sql)
            ->bindValue(':kefu_id', $kefuId)
            ->queryAll();
    }


    public function getChatWaitByKefu($kefuId)
    {
        return  ChatWaitKefu::find()
                    ->where(['kefu_id' => $kefuId])
                    ->asArray()
                    ->one();
    }

    public function offChatInfo($linkId)
    {
        return ChatLink::find()
            ->where(['id' => $linkId, 'kefu_id' => Yii::$app->user->identity->id, 'is_connect' => 1])
            ->asArray()
            ->one();
    }

    public function getChatWaitKefuByPageId($pageId)
    {
        return ChatWaitKefu::find()
                ->where(['page_id' => $pageId])
                ->asArray()
                ->one();
    }

    public function  getChatMessagePreByPageId($pageId)
    {
        return  ChatMessagePre::find()->select('c.open_id')
                    ->alias('c')
                    ->leftJoin('chat_link as l', 'l.open_id = c.open_id')
                    ->where(['l.page_id' => $pageId, 'l.is_connect' => 1])
                    ->groupBy('c.open_id')
                    ->asArray()
                    ->column();
    }

    public function getQuickAnswerList($type)
    {
        return  QuickAnswer::find()
            ->where(['type' => $type, 'is_deleted' => 0])
            ->asArray()
            ->all();
    }

    public function getGoldEge($open_id)
    {
        return  GoldEge::find()
            ->where(['openid' => $open_id])
            ->asArray()
            ->one();
    }

    public function getNewChatWaitCount()
    {
        return ChatWait::find()
            ->where(['type' => 1])
            ->count();
    }

    public function getOtherChatWaitCount($type)
    {
        return ChatWait::find()
            ->where(['type' => $type])
            ->count();
    }


    public function getNewChatWaitInfo($offset, $limit)
    {
        $query = new Query();

        return  $query->select('c.id, u.name, u.head, u.subscribe_time, u.province, u.openid ')
                    ->from('chat_wait as c')
                    ->leftJoin('user_init as u', 'c.open_id = u.openid')
                    ->where(['c.type' => 1])
                    ->orderBy('u.subscribe_time ASC')
                    ->offset($offset)
                    ->limit($limit)
                    ->all();
    }


    public function getChatLinkNoHideInfo()
    {
        $query_2 = new Query();

        return  $query_2->select('l.id as link_id, l.open_id, u.name, u.head, m.counts as have_unread')
                        ->from('chat_link AS l')
                        ->leftJoin('user_init AS u', 'u.openid = l.open_id')
                        ->leftJoin('(SELECT open_id, COUNT(id) as counts FROM chat_message WHERE is_read = 0 AND kefu_id = '.Yii::$app->user->identity->id.' GROUP BY open_id) AS m', 'm.open_id = l.open_id')
                        ->where(['l.kefu_id' => Yii::$app->user->identity->id, 'l.is_hide' => 0])
                        ->all();
    }


    public function getOtherChatWaitInfo($offset, $limit, $type)
    {
        $query = new Query();

        return $query->select('c.id wait_id, u.id student_id, u.nick, u.mobile, ui.head, ui.subscribe_time, ui.province, uc.name as channel_name,ui.openid')
            ->from('chat_wait AS c')
            ->leftJoin('user_init AS ui', 'ui.openid = c.open_id')
            ->leftJoin('wechat_acc AS w', 'c.open_id = w.openid')
            ->leftJoin('user AS u', 'u.id = w.uid')
            ->leftJoin('user_channel AS uc', 'uc.id = u.channel_id')
            ->where(['c.type' => $type])
            ->orderBy('c.id ASC')
            ->offset($offset)
            ->limit($limit)
            ->all();
    }

    public function getChatLinkIsCurrentPage()
    {
        return ChatLink::find()
            ->where(['kefu_id' => Yii::$app->user->identity->id, 'is_current_page' => 1])
            ->asArray()
            ->one();
    }

    public function getChatMessageInfoByOpenId($open_id, $offset)
    {
        $query = new Query();
        return  $query->select('m.id, m.message, m.tag, m.type, m.time_created, u.head, u.name as student_name, uk.nickname as kefu_name, uk.head as kefu_head')
            ->from('chat_message AS m')
            ->leftJoin('user_init AS u', 'u.openid = m.open_id')
            ->leftJoin('user_account AS uk', 'uk.id = m.kefu_id')
            ->where(['m.open_id' => $open_id])
            ->orderBy('m.time_created DESC, m.id DESC')
            ->offset($offset)
            ->limit(10)
            ->all();
    }

    public function getChatLinkByOpenId($openId)
    {
       return ChatLink::find()->select('id, kefu_id, page_id')
            ->where(['open_id' => $openId, 'is_connect' => 1])
            ->asArray()
            ->one();
    }

    public function getChattingUserById($uid, $keyword)
    {
        return ChatLink::find()
            ->alias('c')
            ->select('c.id as link_id, u.nick, u.mobile, w.name as wechat_name, u.sales_id, u.channel_id, w.head, w.subscribe_time, w.province, c.open_id')
            ->leftJoin('wechat_acc AS wa', 'wa.openid = c.open_id')
            ->leftJoin('user_init AS w', 'w.openid = c.open_id')
            ->leftJoin('user AS u', 'u.id = wa.uid')
            ->where('c.kefu_id = :kefu_id AND c.is_connect = 1 AND c.is_hide = 0' . (empty($keyword) ? '' : ' AND (w.name LIKE "%'. $keyword .'%" OR u.nick LIKE "%'. $keyword .'%" OR u.mobile LIKE "%'. $keyword .'%")'), [':kefu_id' => $uid])
            ->orderBy('c.sort ASC')
            ->asArray()
            ->all();
    }

    public function getWaitingUser($keyword,$offset,$limit)
    {
        return ChatWait::find()
            ->alias('c')
            ->select('c.type as user_type, u.nick, u.mobile, w.name as wechat_name, u.sales_id, u.channel_id, w.head, w.subscribe_time, w.province, c.open_id, ua.nickname as kefu_name, uaa.nickname as kefu_name_re')
            ->leftJoin('wechat_acc AS wa', 'wa.openid = c.open_id')
            ->leftJoin('user AS u', 'u.id = wa.uid')
            ->leftJoin('user_public_info AS p', 'p.user_id = u.id')
            ->leftJoin('user_account AS ua', 'p.kefu_id = ua.id')
            ->leftJoin('user_account AS uaa', 'p.kefu_id_re = uaa.id')
            ->leftJoin('user_init AS w', 'w.openid = c.open_id')
            ->where((empty($keyword) ? 'c.type > 0' : ' w.name LIKE "%'. $keyword .'%" OR u.nick LIKE "%'. $keyword .'%" OR u.mobile LIKE "%'. $keyword .'%"'))
            ->offset($offset)
            ->limit($limit)
            ->asArray()
            ->all();
    }

    public function getWaitingUserBySaleId($kefuId,$keyword,$offset,$limit)
    {
        return ChatWait::find()
            ->alias('c')
            ->select('c.type as user_type, u.nick, u.mobile, w.name as wechat_name, u.sales_id, u.channel_id, w.head, w.subscribe_time, w.province, c.open_id, ua.nickname as kefu_name, uaa.nickname as kefu_name_re')
            ->leftJoin('wechat_acc AS wa', 'wa.openid = c.open_id')
            ->leftJoin('user AS u', 'u.id = wa.uid')
            ->leftJoin('user_public_info AS p', 'p.user_id = u.id')
            ->leftJoin('user_account AS ua', 'p.kefu_id = ua.id')
            ->leftJoin('user_account AS uaa', 'p.kefu_id_re = uaa.id')
            ->leftJoin('user_init AS w', 'w.openid = c.open_id')
            ->where(['p.kefu_id' => $kefuId])
            ->andWhere((empty($keyword) ? '' : ' w.name LIKE "%'. $keyword .'%" OR u.nick LIKE "%'. $keyword .'%" OR u.mobile LIKE "%'. $keyword .'%"'))
            ->offset($offset)
            ->limit($limit)
            ->asArray()
            ->all();
    }

    public function getWaitingUserByPurchaseId($kefuId,$keyword,$offset,$limit)
    {
        return ChatWait::find()
            ->alias('c')
            ->select('c.type as user_type, u.nick, u.mobile, w.name as wechat_name, u.sales_id, u.channel_id, w.head, w.subscribe_time, w.province, c.open_id, ua.nickname as kefu_name, uaa.nickname as kefu_name_re')
            ->leftJoin('wechat_acc AS wa', 'wa.openid = c.open_id')
            ->leftJoin('user AS u', 'u.id = wa.uid')
            ->leftJoin('user_public_info AS p', 'p.user_id = u.id')
            ->leftJoin('user_account AS ua', 'p.kefu_id = ua.id')
            ->leftJoin('user_account AS uaa', 'p.kefu_id_re = uaa.id')
            ->leftJoin('user_init AS w', 'w.openid = c.open_id')
            ->where(['p.kefu_id_re' => $kefuId])
            ->andWhere((empty($keyword) ? '' : ' w.name LIKE "%'. $keyword .'%" OR u.nick LIKE "%'. $keyword .'%" OR u.mobile LIKE "%'. $keyword .'%"'))
            ->offset($offset)
            ->limit($limit)
            ->asArray()
            ->all();
    }

    public function getHistoryMessageByOpenId($openId, $offset, $limit)
    {
        return ChatMessage::find()
            ->alias('c')
            ->select('c.kefu_id, u.nickname, u.head as kefu_head, i.head as user_head, c.message, c.tag, c.type, c.time_created, c.is_fail')
            ->leftJoin('user_init AS i', 'i.openid = c.open_id')
            ->leftJoin('user_account AS u', 'u.id = c.kefu_id')
            ->where(['c.open_id' => $openId])
            ->orderBy('c.time_created DESC')
            ->offset($offset)
            ->limit($limit)
            ->asArray()
            ->all();
    }

    public function getOnlineKefu()
    {
        return ChatWaitKefu::find()
            ->alias('c')
            ->select('c.kefu_id, c.page_id, u.role')
            ->leftJoin('user_account AS u', 'u.id = c.kefu_id')
            ->where(['u.status' => 1])
            ->asArray()
            ->all();
    }

    public function getChannelChatLinkTime($open_id, $kefu_id)
    {
        return ChannelChatMessage::find()
                            ->select('time_created')
                            ->where('open_id = :open_id ', [
                                ':open_id' => $open_id
                            ])
                            ->orderBy('id DESC')
                            ->limit(1)
                            ->scalar();
    }

    public function getAllChannelChatLinkTime($open_id, $kefu_id)
    {
        return ChannelChatMessage::find()
            ->select('time_created')
            ->where('open_id = :open_id ', [
                ':open_id' => $open_id
            ])
            ->orderBy('id DESC')
            ->limit(1)
            ->scalar();
    }

    public function getChannelLeftUserList()
    {

        $query = new Query();

        $query->select('a.id link_id,a.open_id,b.wechat_name name,b.head, b.message_type,max(c.id) s');
        $query->from('channel_chat_link a');
        $query->leftJoin('sales_channel b','b.bind_openid = a.open_id');
        $query->leftJoin('channel_chat_message c','c.open_id = a.open_id');
        $query->where('a.is_hide=0 AND a.is_connect=0 AND b.status=1 AND a.kefu_id=:kefu_id',[':kefu_id'=>Yii::$app->user->identity->id]);
        if($keyword){
            $query->andWhere('LIKE','b.wechat_name',$keyword);
        }
        $query->groupBy('a.open_id');
        $query->orderBy('s DESC');
        return $query->all();
    }

    public function getAllonRepayOpenId()
    {
        $query = new query();

        $query->select('a.tag,a.open_id,a.time_created');
        $query->from('channel_chat_message a');

        $query->rightJoin('(SELECT max(id) xid
                    FROM channel_chat_message
                    GROUP BY open_id)b','a.id=b.xid');
        $query->where('a.tag=0');
        return $query->all();
    }

    public function getNoRepayInfo($openid)
    {
        $query = new query();

        $query->select("a.wechat_name name, a.bind_openid, c.nickname"); 
        $query->from('sales_channel a');

        $query->leftJoin('channel_chat_link b','b.open_id = a.bind_openid AND b.is_connect = 1');
        $query->leftJoin('user_account c','c.id = a.kefu_id');

        $query->where("a.status = 1 AND a.bind_openid IN ($openid)");
        $query->groupBy('a.bind_openid');

        return $query->all();
    }

}