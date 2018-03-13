<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/13
 * Time: 上午11:17
 */
namespace common\sources\read\channel;

use common\models\music\ChannelPromotionEffect;
use common\models\music\ChannelVisitHistory;
use common\models\music\ClassRoom;
use common\models\music\HistoryTrade;
use common\models\music\SalesCashout;
use common\models\music\SalesChannel;
use common\models\music\SalesPictures;
use common\models\music\SalesTrade;
use common\models\music\User;
use common\models\music\UserChannel;
use common\models\music\UserInit;
use common\models\music\UserLinkKefuChat;
use common\models\music\WechatClass;
use common\models\music\ChannelRedChance;
use common\models\music\ChannelInvite;
use common\models\music\UserAccount;
use Yii;
use yii\db\ActiveRecord;
use common\models\music\UserShare;
use common\models\music\RedactiveRecord;
use common\models\music\Instrument;
use common\models\music\ChannelTransferInfo;
use console\models\channel\WaitStatistics;
use common\models\PnlQrCode;
use common\models\PnlCodeUsed;

class ChannelAccess implements IChannelAccess
{

    public function getSalesChannelNameById($salesId)
    {
        return SalesChannel::find()
            ->select('nickname')
            ->where([
                'id' => $salesId,
                'status' => 1
            ])
            ->scalar();
    }

    public function getUserChannelInfoById($channelId)
    {
        return UserChannel::find()
            ->select('id, type, name')
            ->where(['id' => $channelId])
            ->asArray()
            ->one();
    }

    public function getUserChannelId($keyword)
    {
        $query = UserChannel::find()
            ->select('id');

        $query = empty($keyword) ? $query->asArray()->column() : $query->where(['like', 'name', $keyword])->asArray()->column();

        return $query;
    }

    public function getSalesChannelList($keyword)
    {
        return SalesChannel::find()
            ->select('id, nickname, username')
            ->where('nickname LIKE "%' . $keyword . '%" OR username LIKE "%' . $keyword . '%"')
            ->andWhere('status = 1')
            ->asArray()
            ->all();
    }

    public function getStudentChannelList($keyword)
    {
        return User::find()
            ->select('channel_id_self, nick, mobile')
            ->where('nick LIKE "%' . $keyword . '%" OR mobile LIKE "%' . $keyword . '%"')
            ->andWhere('is_disabled = 0')
            ->asArray()
            ->all();
    }

    public function getChannelBindOpenid($salesId)
    {
        return SalesChannel::find()
            ->select('bind_openid')
            ->where([
                'status' => 1,
                'id' => $salesId
            ])->scalar();
    }

    public function getChannelBindOpenidByPrivateCode($fromCode)
    {
        return SalesChannel::find()
            ->select('bind_openid')
            ->where([
                'status' => 1,
                'private_code' => $fromCode
            ])->scalar();
    }


    public function getChannelFromCode($salesId)
    {
        return SalesChannel::find()
            ->select('wechat_name, from_code')
            ->where([
                'status' => 1,
                'id' => $salesId
            ])
            ->asArray()
            ->one();
    }

    public function getUserShareIsBack($openId, $classId, $isBack)
    {
        return UserShare::find()
            ->select('id')
            ->where('open_id  = :open_id AND class_id = :class_id ', [
                ':open_id' => $openId,
                ':class_id' => $classId
            ])
            ->scalar();
    }

    public function getChannelWeicodeByOpenid($openId)
    {
        return SalesChannel::find()
            ->select('weicode_channel')
            ->where([
                'status' => 1,
                'bind_openid' => $openId
            ])->scalar();
    }

    public function getStudentWeicodeByOpenid($openId)
    {
        return SalesChannel::find()
            ->select('weicode_path')
            ->where([
                'status' => 1,
                'bind_openid' => $openId
            ])->scalar();
    }

    public function getChannelUserByOpenid($openID)
    {
        return SalesChannel::findOne([
            'status' => 1,
            'bind_openid' => $openID
        ]);
    }


    public function getLastPictureInfo()
    {
        return SalesPictures::find()
            ->select('path, title, content')
            ->where('is_deleted = 0 AND is_default = 1')
            ->limit(1)
            ->asArray()
            ->one();
    }


    public function getRowBySalesId($salesId)
    {
        return SalesChannel::findOne([
            'id' => $salesId,
            'status' => 1
        ])->toArray();
    }


    public function getRowBySalesCode($fromcode)
    {
        return SalesChannel::findOne([
            'private_code' => $fromcode,
            'status' => 1
        ])
            ->toArray();
    }

    public function getUserChannelName($channelId)
    {
        return UserChannel::find()
            ->select('name')
            ->where(['id' => $channelId])
            ->scalar();
    }

    public function getSaleChannelUserCount($type, $keyword, $studentPhone)
    {
        $obj = SalesChannel::find()
            ->alias('u')
            ->leftJoin("(SELECT uid, IFNULL(SUM(CASE WHEN status > 0 AND status != 3 AND  status != 4 AND status != 5 THEN money END), 0) 
            - IFNULL(SUM(CASE WHEN status = -1 THEN money END ),0) AS money FROM sales_trade WHERE is_deleted = 0  GROUP BY uid) AS s",
                's.uid = u.id');

        if (!empty($studentPhone)) {
            $obj->leftJoin('user', 'user.sales_id = u.id')
                ->where("u.status = 1 AND user.mobile = :mobile", [':mobile' => $studentPhone]);
        } else {
            $obj->where('u.status = 1 ' . $type . (empty($keyword) ? "" : " AND (u.nickname LIKE '%$keyword%' OR u.wechat_name LIKE '%$keyword%' OR u.username LIKE '%$keyword%')"));
        }

        return $obj->count();
    }


    public function getAllSaleChannelUserCount($worth, $keyword, $kefuType, $studentPhone)
    {
        $query = new yii\db\Query();

        $query->select('count(*)');
        $query->from('sales_channel a');
        //学生条件
        if ($studentPhone) {
            $query->leftJoin('user d', 'd.sales_id = a.id');
            $query->where("a.status = 1 AND d.mobile = :mobile", [':mobile'=>$studentPhone]);
        } else {
            $query->where('a.status = 1 '
                . $worth . (empty($keyword) ? "" : " AND (a.nickname LIKE '%$keyword%' OR a.wechat_name LIKE '%$keyword%' OR a.username LIKE '%$keyword%')"));
            if (!empty($kefuType)) {
                if ($kefuType == -1) {
                    $query->andWhere('a.kefu_id = 0');
                } else {
                    $query->andWhere('a.kefu_id = :kefu_id', [
                        ':kefu_id' => $kefuType
                    ]);
                }
            }
        }
        return $query->scalar();
    }

    public function getSaleChannelUserList($type, $keyword, $num, $info, $studentPhone)
    {
        $obj = SalesChannel::find()
            ->alias('u')
            ->select('u.id, u.bind_openid, u.message_type, money, u.nickname, u.wechat_name, u.province, u.from_code, u.created_at, u.kefu_id, u.user_type, u.username AS mobile, u.subscribe')
            ->leftJoin("(SELECT uid, IFNULL(SUM(CASE WHEN status > 0 AND status != 3 AND  status != 4 AND status != 5 THEN money END), 0) 
            - IFNULL(SUM(CASE WHEN status = -1 THEN money END ), 0) AS money FROM sales_trade WHERE is_deleted = 0  GROUP BY uid) AS s", 's.uid = u.id');

        if (!empty($studentPhone)) {
            $obj->leftJoin('user', 'user.sales_id = u.id')
                ->where("u.status = 1 AND user.mobile = :mobile", [':mobile' => $studentPhone]);
        } else {
            $obj->where('u.status = 1 ' . $type . (empty($keyword) ? "" : " AND (u.nickname LIKE '%$keyword%' OR u.wechat_name LIKE '%$keyword%' OR u.username LIKE '%$keyword%')"));
        }

        return $obj->orderBy((!empty($info) ? 'money DESC' : 'id DESC'))
            ->offset(($num - 1) * 10)
            ->limit(10)
            ->asArray()
            ->all();
    }


    public function getAllSaleChannelUserList($num, $studentPhone, $worth = '', $keyword = '', $info = '', $kefuType = '')
    {
        $query = new yii\db\Query();

        $query->select('a.id,a.bind_openid,a.message_type,a.nickname,a.wechat_name,a.province,a.from_code,a.created_at,a.kefu_id,a.user_type,a.username mobile,b.money,c.nickname kefu_nickname');
        $query->from('sales_channel a');

        $query->leftJoin("(SELECT uid, IFNULL(SUM(CASE WHEN status > 0 AND status != 3 AND  status != 4 AND status != 5 THEN money END), 0) 
            - IFNULL(SUM(CASE WHEN status = -1 THEN money END ), 0) AS money FROM sales_trade WHERE is_deleted = 0  GROUP BY uid ORDER BY NULL) b", "b.uid = a.id");
        $query->leftJoin('user_account c', 'c.id = a.kefu_id');
        //学生条件
        if ($studentPhone) {
            $query->leftJoin('user d', 'd.sales_id = a.id');
            $query->where("a.status = 1 AND d.mobile = :mobile", [':mobile'=>$studentPhone]);
        } else {
            $query->where('a.status = 1 '
                . $worth . (empty($keyword) ? "" : " AND (a.nickname LIKE '%$keyword%' OR a.wechat_name LIKE '%$keyword%' OR a.username LIKE '%$keyword%')"));
            if (!empty($kefuType)) {
                if ($kefuType == -1) {
                    $query->andWhere('a.kefu_id = 0');
                } else {
                    $query->andWhere('a.kefu_id = :kefu_id', [
                        ':kefu_id' => $kefuType
                    ]);
                }
            }
        }
        $query->orderBy((!empty($info) ? 'money DESC' : 'a.id DESC'));
        $query->offset(($num - 1) * 10);
        $query->limit(10);
        return $query->all();
    }

    public function getUserInitByOpenId($openId)
    {
        return SalesChannel::find()
            ->select('id,nickname')
            ->where([
                'bind_openid' => $openId,
                'status' => 1
            ])
            ->asArray()
            ->one();
    }

    public function getSaleChannelTime($bindOpenid, $time)
    {
        return SalesChannel::find()
            ->select('id')
            ->where('bind_openid = :bind_openid AND status = 1 AND created_at < :time', [
                'bind_openid' => $bindOpenid,
                'time' => $time])
            ->scalar();
    }

    public function getSaleChannelUserInfo($openId)
    {
        return SalesChannel::find()
            ->select('id, bind_openid, message_type, nickname, wechat_name, user_type, province, message_type, created_at, kefu_id, user_type, username AS mobile , head, from_code, private_code ,remark,instrument,auth_time,subscribe,reqrcode_time,weicode_path')
            ->where('bind_openid = :open_id  AND status = 1 ', [':open_id' => $openId])
            ->asArray()
            ->one();
    }

    public function getWechatClassCount($keyword)
    {
        return WechatClass::find()
            ->alias('w')
            ->where('is_delete = 0  AND is_disable = 0  AND end_time = 0 AND (class_time > :time OR is_back = 1 ) ' . (empty($keyword) ? ' ' : " AND title LIKE '%{$keyword}%'"), [
                ':time' => time()
            ])
            ->count();
    }

    public function getWechatClassList($openId, $keyword, $num)
    {
        return WechatClass::find()
            ->alias('w')
            ->select('w.id, url, title, content, teacher_name, is_top, class_time, is_back')
            ->where('is_delete = 0 AND is_disable = 0  AND end_time = 0 AND (class_time > :time OR is_back = 1 ) ' . (empty($keyword) ? ' ' : " AND title LIKE '%{$keyword}%'"), [
                ':time' => time()
            ])
            ->orderBy('is_top DESC, create_time DESC ')
            ->offset(($num - 1) * 10)
            ->limit(10)
            ->asArray()
            ->all();
    }

    public function getListenWechatClass($openId, $classId, $isBackShare)
    {
        return UserShare::find()
            ->select('id')
            ->where('class_id = :class_id AND open_id = :open_id AND is_purview = 1 ', [
                ':open_id' => $openId,
                ':class_id' => $classId
            ])
            ->asArray()
            ->one();
    }

    public function getChannelPoster($openId)
    {
        return SalesChannel::find()
            ->select('weicode_path')
            ->where('status = 1 AND bind_openid  = :bind_openid', [':bind_openid' => $openId])
            ->scalar();
    }

    public function getListenPurviewWechatClass($openId, $classId, $backType)
    {
        return UserShare::find()
            ->select('id')
            ->where('class_id = :class_id AND open_id = :open_id AND is_purview = 0  ', [
                ':open_id' => $openId,
                ':class_id' => $classId
            ])
            ->asArray()
            ->one();
    }

    public function getWechatClassId($title, $classTime)
    {
        return WechatClass::find()
            ->select('id')
            ->where('id = :id ', [':title' => $title, ':class_time' => $classTime])
            ->scalar();
    }

    public function getSalesChannelPhone($phone, $openId)
    {
        return SalesChannel::find()
            ->where('status = 1 AND bind_openid != :open_id AND username = :phone ', [':phone' => $phone, ':open_id' => $openId])
            ->count();
    }

    public function getSalesChannelNickByPrivate($fromCode)
    {
        return SalesChannel::find()
            ->select('wechat_name')
            ->where('status = 1 AND private_code = :from_code', [
                ':from_code' => $fromCode
            ])
            ->scalar();
    }

    public function countChannelByOpenid($openId)
    {
        return SalesChannel::find()
            ->where([
                'status' => 1,
                'bind_openid' => $openId
            ])->count();
    }

    public function getChannelNameByOpenid($openId)
    {
        return SalesChannel::find()
            ->select('wechat_name')
            ->where([
                'bind_openid' => $openId,
                'status' => 1
            ])->scalar();
    }

    public function getSaleChannelByOpenId($openId)
    {
        return SalesChannel::find()
            ->select('id, nickname')
            ->where([
                'status' => 1,
                'bind_openid' => $openId
            ])
            ->one();
    }

    public function getSaleChannelByPrivetaCode($code)
    {
        return SalesChannel::findOne([
            'private_code' => $code,
            'status' => 1
        ]);
    }

    public function getUserShreByShareId($id)
    {
        return UserShare::find()
            ->alias('us')
            ->select('us.open_id,wc.title,wc.id,wc.is_back')
            ->leftJoin('wechat_class AS wc', 'wc.id = us.class_id')
            ->where('us.id = :id', [
                ':id' => $id
            ])
            ->asArray()
            ->all();
    }

    public function getFromCodeByOpenid($openId)
    {
        return \common\models\SalesChannel::find()
            ->select('private_code')
            ->where('bind_openid = :openid AND status = 1', [
                ':openid' => $openId
            ])
            ->scalar();
    }

    public function getSaleChannelByLineDown($fromOpenid)
    {
        return \common\models\SalesChannel::findone([
            'bind_openid' => $fromOpenid,
            'status' => 1
        ]);
    }

    public function getHavePremission($userId)
    {
        return SalesChannel::find()
            ->select('id')
            ->where('status = 1 AND have_premission = 1 AND id = :user_id', ['user_id' => $userId])
            ->scalar();
    }

    public function getThisSaleChannelReward($saleChannelId)
    {
        return SalesTrade::find()
            ->select('SUM(money)')
            ->where('status > 0 AND status != 3 AND  status != 4 AND status != 5 AND uid = :sale_channel_id AND is_deleted = 0', [
                ':sale_channel_id' => $saleChannelId
            ])
            ->scalar();
    }

    public function getHistorySaleChannelReward($saleChannelId)
    {
        return SalesTrade::find()
            ->select('SUM(money)')
            ->where('status < 0  AND uid = :sale_channel_id AND is_deleted = 0', [
                ':sale_channel_id' => $saleChannelId
            ])
            ->scalar();
    }

    public function getThisSaleChannelCount($saleChannelId)
    {
        return SalesTrade::find()
            ->alias('s')
            ->select('u.wechat_name, u.nickname, s.comment, s.money, s.time_created')
            ->leftJoin('sales_channel AS u', 'u.id = s.uid')
            ->where('s.uid = :sale_channel_id AND  s.is_deleted = 0 AND is_cashout = 0 ', [
                ':sale_channel_id' => $saleChannelId
            ])
            ->count();
    }

    public function getHistoryTradeTime($id)
    {
        return HistoryTrade::find()
            ->select('create_time, total_amount')
            ->where('id = :id', [':id' => $id])
            ->asArray()
            ->one();
    }

    public function getTotaltalAmount($uid)
    {
        return HistoryTrade::find()
            ->select('SUM(total_amount)')
            ->where('uid = :id', [':id' => $uid])
            ->scalar();
    }

    public function getHistorySaleChannelCount($saleChannelId)
    {
        return SalesTrade::find()
            ->alias('s')
            ->select('u.wechat_name, u.nickname, s.comment, s.money, s.time_created')
            ->leftJoin('sales_channel AS u', 'u.id = s.uid')
            ->where('s.uid = :sale_channel_id AND s.is_deleted = 0 AND is_cashout = 1  AND s.status > 0', [
                ':sale_channel_id' => $saleChannelId
            ])
            ->count();
    }

    public function getHistoryTradeCount($saleChannelId)
    {
        return SalesCashout::find()
            ->where('uid = :sale_channel_id', [':sale_channel_id' => $saleChannelId])
            ->count();
    }

    public function getHistoryTradeList($saleChannelId, $num)
    {
        return SalesCashout::find()
            ->select('id, uid, cash AS payable_amount, reward AS reward_amount, total AS total_amount, time_created')
            ->where('uid = :sale_channel_id', [':sale_channel_id' => $saleChannelId])
            ->orderBy('id DESC')
            ->offset(($num - 1) * 4)
            ->limit(4)
            ->asArray()
            ->all();
    }

    public function getThisSaleChannelList($saleChannelId, $num)
    {
        return SalesTrade::find()
            ->alias('s')
            ->select('u.wechat_name, u.nickname, s.comment, s.money, s.status,s.time_created')
            ->leftJoin('sales_channel AS u', 'u.id = s.uid')
            ->where('s.uid = :sale_channel_id  AND s.is_deleted = 0 AND is_cashout = 0', [
                ':sale_channel_id' => $saleChannelId
            ])
            ->orderBy('s.time_created DESC')
            ->offset(($num - 1) * 4)
            ->limit(4)
            ->asArray()
            ->all();
    }

    public function getNoCashoutChannelId($userId)
    {
        return SalesTrade::find()
            ->select("id")
            ->where('uid = :uid AND is_cashout = 0', [':uid' => $userId])
            ->asArray()
            ->column();
    }

    public function getHistorySaleChannelList($saleChannelId, $num)
    {
        return SalesTrade::find()
            ->alias('s')
            ->select('u.wechat_name, u.nickname, s.comment, s.money, s.time_created')
            ->leftJoin('sales_channel AS u', 'u.id = s.uid')
            ->where('s.uid = :sale_channel_id  AND s.is_deleted = 0 AND s.is_cashout = 1 AND s.status > 0', [
                ':sale_channel_id' => $saleChannelId
            ])
            ->offset(($num - 1) * 4)
            ->limit(4)
            ->asArray()
            ->all();
    }

    public function getSaleChannelCode($privateCode)
    {
        return SalesChannel::find()
            ->select('nickname')
            ->where('private_code = :private_code AND status = 1', [':private_code' => $privateCode])
            ->scalar();
    }


    public function getAllSaleChannelCode($privateCode)
    {
        return SalesChannel::find()
            ->select('nickname')
            ->where('private_code = :private_code AND status = 1', [':private_code' => $privateCode])
            ->scalar();
    }

    public function getClassShareByUid($uid)
    {
        return UserShare::find()
            ->select('class_id')
            ->where(['user_id' => $uid])
            ->scalar();
    }

    public function getLiveClassNameById($classId)
    {
        return WechatClass::find()
            ->select('title')
            ->where(['id' => $classId])
            ->scalar();
    }

    public function getSalesChannelFromcodeById($salesId)
    {
        return SalesChannel::find()
            ->select('nickname, from_code')
            ->where([
                'id' => $salesId,
                'status' => 1
            ])
            ->asArray()
            ->one();
    }

    public function getSalesChannelInfo($private)
    {
        return SalesChannel::find()
            ->select('id, nickname')
            ->where([
                'private_code' => $private,
                'status' => 1
            ])->asArray()->one();
    }

    public function doExistSaleTradeRecord($uid)
    {
        return SalesTrade::find()
            ->select('is_cashout')
            ->where('uid = :uid AND status > 0', [':uid' => $uid])
            ->offset('id DESC')
            ->scalar();
    }

    public function getChannelInfo($id)
    {
        return SalesTrade::find()
            ->select('
                        COUNT(CASE WHEN status = 8 THEN id END) AS ex_count, 
                        COUNT(CASE WHEN status = 9 AND fromUid = 0 THEN id END) AS buy_count,
                        COUNT(CASE WHEN status = 9 AND fromUid > 0 THEN id END) AS two_buy_count
                    ')
            ->where('uid = :uid AND is_deleted = 0', [':uid' => $id])
            ->asArray()
            ->one();
    }

    public function getRegisterCount($id)
    {
        return User::find()
            ->select('COUNT(id)')
            ->where('sales_id = :sales_id AND is_disabled = 0', [':sales_id' => $id])
            ->scalar();
    }

    public function getRegisterList($id, $num)
    {
        return User::find()
            ->alias('u')
            ->select('u.time_created, u.mobile, u.nick ')
            ->where('sales_id = :sales_id AND u.is_disabled = 0', [':sales_id' => $id])
            ->orderBy('u.time_created DESC')
            ->offset(($num - 1) * 4)
            ->limit(4)
            ->asArray()
            ->all();
    }

    public function getExUserCount($id)
    {
        return SalesTrade::find()
            ->where('status = 8 AND is_deleted = 0 AND uid = :id', [':id' => $id])
            ->count();
    }

    public function getExUserList($id, $num)
    {
        return SalesTrade::find()
            ->alias('s')
            ->select('c.time_class AS time_created, u.nick, u.mobile')
            ->leftJoin('class_room AS c', 'c.id = s.classID')
            ->leftJoin('user_public AS u', 'u.user_id = s.studentID')
            ->where('s.status = 8 AND s.is_deleted = 0 AND uid = :id', [':id' => $id])
            ->orderBy('c.time_created DESC')
            ->offset(($num - 1) * 4)
            ->limit(4)
            ->asArray()
            ->all();
    }

    public function getBuyUserCount($id)
    {
        return SalesTrade::find()
            ->where('status = 9 AND is_deleted = 0 AND uid = :id AND fromUid = 0', [':id' => $id])
            ->count();
    }

    public function getBuyUserList($id, $num)
    {
        return SalesTrade::find()
            ->alias('s')
            ->select('u.nick, u.mobile, money, s.time_created')
            ->leftJoin('user_public AS u', 'u.user_id = s.studentID')
            ->where('status = 9 AND s.is_deleted = 0 AND uid = :id AND fromUid = 0', [':id' => $id])
            ->orderBy('s.time_created DESC')
            ->offset(($num - 1) * 4)
            ->limit(4)
            ->asArray()
            ->all();
    }

    public function getTwoBuyUserCount($id)
    {
        return SalesTrade::find()
            ->where('status = 9 AND is_deleted = 0 AND uid = :id AND fromUid > 0', [':id' => $id])
            ->count();
    }

    public function getTwoBuyUserList($id, $num)
    {
        return SalesTrade::find()
            ->alias('s')
            ->select('u.nick, u.mobile, money, s.time_created')
            ->leftJoin('user_public AS u', 'u.user_id = s.studentID')
            ->where('s.status = 9 AND s.is_deleted = 0 AND uid = :id AND fromUid > 0', [':id' => $id])
            ->orderBy('s.time_created DESC')
            ->offset(($num - 1) * 4)
            ->limit(4)
            ->asArray()
            ->all();
    }

    public function getUserSharePullCount($id)
    {
//        return UserShare::find()
//                    ->select('IFNULL(SUM(pull_num),0) AS num ')
//                    ->where(['user_id' => $id])
//                    ->scalar();

        $sql = 'SELECT IFNULL(SUM(pull_num), 0) FROM user_share WHERE open_id = :id';

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':id' => $id])
            ->queryScalar();
    }

    public function getPrivateCode($id)
    {
        return SalesChannel::find()
            ->select('private_code')
            ->where('status = 1 AND id = :id', [':id' => $id])
            ->scalar();
    }


    public function getRewardUserCount($time, $keyword, $rewardType)
    {
        $timeEnd = empty($time) ? '' : $time + 86400;

        $obj = SalesChannel::find()
            ->alias('u')
            ->leftJoin("(SELECT uid, IFNULL(SUM(CASE WHEN status > 0 AND status != 3 AND  status != 4 AND status != 5 THEN money END), 0) 
                    - IFNULL(SUM(CASE WHEN status = -1 THEN money END), 0) AS money FROM sales_trade WHERE is_deleted = 0 "
                . (empty($time) ? "" : " AND time_created >= $time AND time_created <= $timeEnd") . " GROUP BY uid) AS s", 's.uid = u.id'
            );
        if (!empty($rewardType)) {
            if ($rewardType == -2) {//二级买单
                $obj = $obj->leftJoin('(SELECT uid,COUNT(uid) AS trade_num FROM sales_trade WHERE is_deleted = 0 AND fromUid !=0 AND status = 9 ' .
                    (empty($time) ? "" : " AND time_created >= $time AND time_created <= $timeEnd")
                    . ' GROUP BY uid) AS ss', 'ss.uid = u.id');
            } elseif ($rewardType == -3) {//买单
                $obj = $obj->leftJoin('(SELECT uid,COUNT(uid) AS trade_num FROM sales_trade WHERE is_deleted = 0 AND fromUid =0 AND status = 9 ' .
                    (empty($time) ? "" : " AND time_created >= $time AND time_created <= $timeEnd")
                    . ' GROUP BY uid) AS ss', 'ss.uid = u.id');
            } elseif ($rewardType == 8) {//体验课
                $obj = $obj->leftJoin('(SELECT uid,COUNT(uid) AS trade_num FROM sales_trade WHERE is_deleted = 0 AND status = 8 ' .
                    (empty($time) ? "" : " AND time_created >= $time AND time_created <= $timeEnd")
                    . ' GROUP BY uid) AS ss', 'ss.uid = u.id');
            } elseif ($rewardType == 1) {//软文
                $obj = $obj->leftJoin('(SELECT uid,COUNT(uid) AS trade_num FROM sales_trade WHERE is_deleted = 0 AND status = 1 '
                    . (empty($time) ? "" : " AND time_created >= $time AND time_created <= $timeEnd")
                    . ' GROUP BY uid) AS ss', 'ss.uid = u.id');
            } elseif ($rewardType == 11) {//微课拉新奖
                $obj = $obj->leftJoin('(SELECT uid,COUNT(uid) AS trade_num FROM sales_trade WHERE is_deleted = 0 AND status = 11 '
                    . (empty($time) ? "" : " AND time_created >= $time AND time_created <= $timeEnd")
                    . ' GROUP BY uid) AS ss', 'ss.uid = u.id');
            } elseif ($rewardType == 12) {//转渠道奖励
                $obj = $obj->leftJoin('(SELECT uid,COUNT(uid) AS trade_num FROM sales_trade WHERE is_deleted = 0 AND status = 12 '
                    . (empty($time) ? "" : " AND time_created >= $time AND time_created <= $timeEnd")
                    . ' GROUP BY uid) AS ss', 'ss.uid = u.id');
            } elseif ($rewardType == 13) {//体验达人奖
                $obj = $obj->leftJoin('(SELECT uid,COUNT(uid) AS trade_num FROM sales_trade WHERE is_deleted = 0 AND status = 13 '
                    . (empty($time) ? "" : " AND time_created >= $time AND time_created <= $timeEnd")
                    . ' GROUP BY uid) AS ss', 'ss.uid = u.id');
            }
            return $obj->where('ss.trade_num > 0 AND u.status = 1 AND (money > 0 OR money = 0) AND u.kefu_id = :kefu_id '
                . (empty($keyword) ? "" : " AND (nickname LIKE '%$keyword%' OR wechat_name LIKE '%$keyword%' OR username LIKE '%$keyword%')"),
                [
                    ':kefu_id' => Yii::$app->user->identity->id
                ])
                ->count();
        }

        return $obj->where('u.status = 1 AND (money > 0 OR money = 0) AND u.kefu_id = :kefu_id ' . (empty($keyword) ? "" : " AND (nickname LIKE '%$keyword%' OR wechat_name LIKE '%$keyword%' OR username LIKE '%$keyword%')"), [
            ':kefu_id' => Yii::$app->user->identity->id
        ])
            ->count();
    }


    public function getRewardAllUserCount($time, $keyword)
    {
        $timeEnd = empty($time) ? '' : $time + 86400;

        return SalesChannel::find()
            ->alias('u')
            ->leftJoin("(SELECT uid, IFNULL(SUM(CASE WHEN status > 0 AND status != 3 AND  status != 4 AND status != 5 THEN money END ), 0) 
            - IFNULL(SUM(CASE WHEN status = -1 THEN money END ), 0) AS money FROM sales_trade WHERE is_deleted = 0 "
                . (empty($time) ? "" : " AND time_created >= :time AND time_created <= :time_end") . " GROUP BY uid) AS s", 's.uid = u.id'
            )
            ->where('u.status = 1 AND money > 0 ' . (empty($keyword) ? "" : " AND (nickname LIKE '%$keyword%' OR wechat_name LIKE '%$keyword%')"), [
                ':time' => $time,
                ':time_end' => $timeEnd,
            ])
            ->count();
    }

    /**
     * 此段代码可以删除
     * create by wangke
     */
    public function getRewardUserList($num, $time, $keyword, $rewardType)
    {
        $timeEnd = empty($time) ? '' : $time + 86400;

        $obj = SalesChannel::find()
            ->alias('u')
            ->select('id, bind_openid, message_type, money, nickname, wechat_name, province, from_code, created_at, kefu_id, user_type, username AS mobile, subscribe')
            ->leftJoin("(SELECT uid, IFNULL(SUM(CASE WHEN status > 0 AND status != 3 AND  status != 4 AND status != 5 THEN money END), 0) 
            - IFNULL(SUM(CASE WHEN status = -1 THEN money END), 0) AS money FROM sales_trade WHERE is_deleted = 0 "
                . (empty($time) ? "" : "  AND time_created >= $time AND time_created <= $timeEnd") . " GROUP BY uid) AS s", 's.uid = u.id'
            );
        if (!empty($rewardType)) {
            if ($rewardType == -2) {//二级买单
                $obj = $obj->leftJoin('(SELECT uid,COUNT(uid) AS trade_num FROM sales_trade WHERE is_deleted = 0 AND fromUid !=0 AND status = 9  ' .
                    (empty($time) ? "" : " AND time_created >= $time AND time_created <= $timeEnd")
                    . ' GROUP BY uid) AS ss', 'ss.uid = u.id');
            } elseif ($rewardType == -3) {//买单
                $obj = $obj->leftJoin('(SELECT uid,COUNT(uid) AS trade_num FROM sales_trade WHERE is_deleted = 0 AND fromUid =0 AND status = 9 ' .
                    (empty($time) ? "" : " AND time_created >= $time AND time_created <= $timeEnd")
                    . '  GROUP BY uid) AS ss', 'ss.uid = u.id');
            } elseif ($rewardType == 8) {//体验课
                $obj = $obj->leftJoin('(SELECT uid,COUNT(uid) AS trade_num FROM sales_trade WHERE is_deleted = 0 AND status = 8 ' .
                    (empty($time) ? "" : " AND time_created >= $time AND time_created <= $timeEnd")
                    . ' GROUP BY uid) AS ss', 'ss.uid = u.id');
            } elseif ($rewardType == 1) {//软文
                $obj = $obj->leftJoin('(SELECT uid,COUNT(uid) AS trade_num FROM sales_trade WHERE is_deleted = 0 AND status = 1 ' .
                    (empty($time) ? "" : " AND time_created >= $time AND time_created <= $timeEnd")
                    . ' GROUP BY uid) AS ss', 'ss.uid = u.id');
            } elseif ($rewardType == 11) {//微课拉新奖
                $obj = $obj->leftJoin('(SELECT uid,COUNT(uid) AS trade_num FROM sales_trade WHERE is_deleted = 0 AND status = 11 '
                    . (empty($time) ? "" : " AND time_created >= $time AND time_created <= $timeEnd")
                    . ' GROUP BY uid) AS ss', 'ss.uid = u.id');
            } elseif ($rewardType == 12) {//转渠道奖励
                $obj = $obj->leftJoin('(SELECT uid,COUNT(uid) AS trade_num FROM sales_trade WHERE is_deleted = 0 AND status = 12 '
                    . (empty($time) ? "" : " AND time_created >= $time AND time_created <= $timeEnd")
                    . ' GROUP BY uid) AS ss', 'ss.uid = u.id');
            } elseif ($rewardType == 13) {//体验达人奖
                $obj = $obj->leftJoin('(SELECT uid,COUNT(uid) AS trade_num FROM sales_trade WHERE is_deleted = 0 AND status = 13 '
                    . (empty($time) ? "" : " AND time_created >= $time AND time_created <= $timeEnd")
                    . ' GROUP BY uid) AS ss', 'ss.uid = u.id');
            }
            return $obj->where('ss.trade_num > 0 AND u.status = 1 AND (money > 0 OR money = 0) AND u.kefu_id = :kefu_id '
                . (empty($keyword) ? "" : " AND (nickname LIKE '%$keyword%' OR wechat_name LIKE '%$keyword%' OR username LIKE '%$keyword%')"), [
                ':kefu_id' => Yii::$app->user->identity->id
                ])
                ->orderBy('money DESC')
                ->offset(($num - 1) * 8)
                ->limit(8)
                ->asArray()
                ->all();
        }

        return $obj->where('u.status = 1 AND (money > 0 OR money = 0) AND u.kefu_id = :kefu_id '
            . (empty($keyword) ? "" : " AND (nickname LIKE '%$keyword%' OR wechat_name LIKE '%$keyword%' OR username LIKE '%$keyword%')"), [
            ':kefu_id' => Yii::$app->user->identity->id
            ])
            ->orderBy('money DESC')
            ->offset(($num - 1) * 8)
            ->limit(8)
            ->asArray()
            ->all();
    }

    /**
     * 此段代码可以删除
     * create by wangke
     */
    public function getRewardAllUserList($num, $time, $keyword)
    {
        $timeEnd = empty($time) ? '' : $time + 86400;

        return SalesChannel::find()
            ->alias('u')
            ->select('id, bind_openid, message_type, money, nickname, wechat_name, province, from_code, created_at, kefu_id, user_type, username AS mobile')
            ->leftJoin("(SELECT uid, IFNULL(SUM(CASE WHEN status > 0 AND status != 3 AND  status != 4 AND status != 5 THEN money END), 0) 
            - IFNULL(SUM(CASE WHEN status = -1 THEN money END), 0) AS money FROM sales_trade WHERE is_deleted = 0 "
                . (empty($time) ? "" : "  AND time_created >= :time AND time_created <= :time_end") . " GROUP BY uid) AS s", 's.uid = u.id')
            ->where('u.status = 1 AND money > 0 ' . (empty($keyword) ? "" : " AND (nickname LIKE '%$keyword%' OR wechat_name LIKE '%$keyword%')"), [
                ':time' => $time,
                ':time_end' => $timeEnd,
            ])
            ->orderBy('money DESC')
            ->offset(($num - 1) * 8)
            ->limit(8)
            ->asArray()
            ->all();
    }

    public function getNewWechatUserCount($privateCode)
    {
        return SalesChannel::find()
            ->where('from_code = :private_code AND status = 1', [':private_code' => $privateCode])
            ->count();
    }

    public function getNewWechatUserList($privateCode, $num)
    {
        return SalesChannel::find()
            ->select('created_at AS time_created, wechat_name')
            ->where('from_code = :private_code AND status = 1', [':private_code' => $privateCode])
            ->orderBy('time_created DESC')
            ->offset(($num - 1) * 4)
            ->limit(4)
            ->asArray()
            ->all();
    }

    /*
     * create by sjy 2017-03-24
     * 根据活动id获取本次活动用户已领红包总和
     */
    public function getRedpackageActiveSumById($activeId)
    {
        $redTopSql = "SELECT SUM(money) FROM redactive_record where active_id = " . $activeId;
        $redTop = Yii::$app->db->createCommand($redTopSql)
            ->queryScalar();
        return $redTop;
    }

    /*
     * create by sjy 2017-03-24
     * 判断用户在本次活动中是否领取过红包
     */
    public function isReUser($openid, $activeId)
    {
        $reUser = RedactiveRecord::find()
            ->where('open_id = :open_id and active_id = :active_id and is_success = 1 ', [
                ':open_id' => $openid,
                ':active_id' => $activeId,
            ])
            ->asArray()
            ->one();
        return $reUser;
    }

    /*
     * 获取用户二维码
     * create by sjy 2017-03-24
     */
    public function getUserWeicode($openid)
    {
        $weicode = SalesChannel::find('weicode_path')
            ->select('weicode_path')
            ->where('status = 1 AND bind_openid = :open_id ', [
                ':open_id' => $openid,
            ])
            ->asArray()
            ->one();
        return $weicode;
    }


    public function getPromotionEffectPage($start, $end, $uid)
    {
        return ChannelPromotionEffect::find()
            ->where('time_created >= :start AND time_created < :end AND sales_id = :uid', [
                ':start' => $start,
                ':end' => $end,
                ':uid' => $uid
            ])
            ->count();
    }

    public function getPromotionEffectList($start, $end, $num, $uid)
    {
        return ChannelPromotionEffect::find()
            ->select('*')
            ->where('time_created >= :start AND time_created < :end AND sales_id = :uid', [
                ':start' => $start,
                ':end' => $end,
                ':uid' => $uid
            ])
            ->orderBy('time_created DESC')
            ->offset(($num - 1) * 50)
            ->limit(50)
            ->asArray()
            ->all();
    }

    public function getSalesChannelOpenidById($salesId)
    {
        return SalesChannel::find()
            ->select('bind_openid')
            ->where('status = 1 AND id = :id', [':id' => $salesId])
            ->scalar();
    }

    public function getAllPoster($posterId = 0)
    {
        if ($posterId) {
            return SalesPictures::find()
                ->select('path, title, content')
                ->where('id = :id', [':id' => $posterId])
                ->asArray()
                ->one();
        } else {
            return SalesPictures::find()
                ->select('id,name,path,is_default')
                ->where('is_deleted = 0')
                ->orderBy('is_default DESC, id DESC')
                ->asArray()
                ->all();
        }
    }

    public function getIntroducePage($where)
    {
        $command = new yii\db\Query();
        $command->select('id,name,url,content');
        $command->from('introduce_set');
        $command->where('is_delete=0');
        if (isset($where['id'])) {
            $command->andWhere('id=:id', [':id' => $where['id']]);
            return $command->one();
        }
        return $command->all();
    }

    public function getUserBySalesChannelKefu($kefuid)
    {
        return User::find()->alias('a')
            ->leftJoin('sales_channel b', 'b.id = a.sales_id')
            ->where('a.sales_id>0 AND a.is_disabled=0 AND b.kefu_id = :kefuid', [
                ':kefuid' => $kefuid
            ])
            ->select('a.id,a.init_id')
            ->asArray()->all();
    }

    public function getExClassReportCount($stime, $etime, $status, $initId)
    {
        $obj = UserInit::find()->alias('a')
            ->leftJoin('user b', 'b.init_id = a.id')
            ->where('b.sales_id>0 AND a.subscribe_time BETWEEN :stime AND :etime', [
                ':stime' => $stime,
                ':etime' => $etime,
            ])
            ->andFilterWhere(['IN', 'a.id', $initId]);
        //是否预约课程状态
        if ($status) {//关注已预约
            $obj->andWhere('EXISTS(SELECT id FROM class_room c WHERE c.student_id = b.id AND c.is_ex_class=1 AND c.status<2)');
        } else {//关注未预约
            $obj->andWhere('NOT EXISTS(SELECT id FROM class_room c WHERE c.student_id = b.id AND c.is_ex_class=1)');
        }
        return $obj->count();
    }

    public function getExClassReportList($stime, $etime, $status, $initIds, $num)
    {
        $obj = UserInit::find()->alias('a')
            ->leftJoin('user b', 'b.init_id = a.id')
            ->leftJoin('sales_channel c', 'c.id = b.sales_id')
            ->where('b.sales_id>0 AND a.subscribe_time BETWEEN :stime AND :etime', [
                ':stime' => $stime,
                ':etime' => $etime,
            ])
            ->andFilterWhere(['IN', 'a.id', $initIds]);
        //是否预约课程状态0 注册未预约  1 注册已预约
        if ($status) {
            $obj->andWhere('EXISTS(SELECT id FROM class_room c WHERE c.student_id = b.id AND c.is_ex_class=1 AND c.status<2)');
        } else {
            $obj->andWhere('NOT EXISTS(SELECT id FROM class_room c WHERE c.student_id = b.id AND c.is_ex_class=1)');
        }
        $obj->select('b.id,c.id sales_id,c.bind_openid,b.nick,b.mobile,c.nickname,c.username,c.kefu_id');
        return $obj->orderBy('a.id DESC')
            ->offset(($num - 1) * 100)
            ->limit(100)
            ->asArray()->all();
    }

    public function getExClassByUserId($userids, $status)
    {
        return ClassRoom::find()
            ->where('is_ex_class = 1')
            ->andWhere(['IN', 'status' , $status])
            ->andWhere(['IN', 'student_id', $userids])
            ->select('student_id,time_class')
            ->asArray()->all();
    }

    public function getUserAccountById($kefuids)
    {
        return UserAccount::find()
            ->where(['IN', 'id', $kefuids])
            ->select('id, nickname')
            ->asArray()->all();
    }

    public function getAnyDayExClassReportCount($stime, $etime, $status, $userids)
    {
        return ClassRoom::find()->alias('a')
            ->leftJoin('user b', 'a.student_id = b.id')
            ->where('a.is_ex_class=1 AND b.sales_id > 0 AND a.status=:status', [':status' => $status])
            ->andWhere(['BETWEEN', 'a.time_class', $stime, $etime])
            ->andFilterWhere(['IN', 'b.id', $userids])
            ->count();
    }

    public function getAnyDayExClassReportList($stime, $etime, $status, $userids, $num)
    {
        return ClassRoom::find()->alias('a')
            ->select('a.id cid,a.is_teacher_cancel,a.time_class,b.nick,b.mobile,c.id sales_id,c.bind_openid,c.nickname,c.username,c.kefu_id,d.undo_reason')
            ->leftJoin('user b', 'a.student_id = b.id')
            ->leftJoin('sales_channel c', 'c.id = b.sales_id')
            ->leftJoin('class_record d', 'd.class_id = a.id')
            ->where('a.is_ex_class=1 AND b.sales_id>0 AND a.status=:status', [':status' => $status])
            ->andWhere(['BETWEEN', 'a.time_class', $stime, $etime])
            ->andFilterWhere(['IN', 'b.id', $userids])
            ->offset(($num - 1) * 100)
            ->limit(100)
            ->asArray()->all();
    }

    public function getCountByfromCode($privateCode, $startTime, $endTime)
    {
        return ChannelInvite::find()
            ->where(['private_code' => $privateCode])
            ->andWhere("create_time >= :startTime AND create_time < :endTime", [
                ':startTime' => $startTime,
                ':endTime' => $endTime
            ])
            ->count();
    }

    public function getMoneyByrRand($rand = 0, $messageType, $type)
    {
        return ChannelRedChance::find()
            ->select('amount')
            ->where([
                'is_delete' => 0,
                'type' => $type,
                'message_type' => $messageType,
            ])
            ->andWhere("rand_start <= $rand AND rand_end >= $rand")
            ->scalar();
    }

    public function getChannelByPrivateCode($privateCode)
    {
        return SalesChannel::find()
            ->select('*')
            ->where('private_code = :privateCode AND status = 1', [':privateCode' => $privateCode])
            ->one();
    }

    public function getInstrumentByIds($ids = '')
    {
        $model = Instrument::find();
        if ($ids) {
            $model->select('name');
            $model->where("id IN ($ids)");
            $model->asArray();
            return $model->column();
        }
        $model->select('*');
        $model->asArray();
        return $model->all();
    }

    public function getTransferList($num, $arr)
    {
        $query = new yii\db\Query();

        $query->select('a.old_channel_name old_name,a.old_channel_moblie old_mobile,a.old_channel_wechat_name
            old_w_name,a.new_channel_name new_name,a.new_channel_moblie new_mobile,a.new_channel_wechat_name
            new_w_name,a.remark,a.type,a.student_id,a.status,a.id,b.nick,b.mobile');
        $query->addselect(['FROM_UNIXTIME(a.created_time,"%Y-%m-%d %H:%i:%s") as created_time']);

        $query->from('channel_transfer_info a');
        $query->leftJoin('user b', 'b.id = a.student_id');
        $query->where("a.type LIKE '%2'");
        if ($arr['uids']) {
            $query->andWhere("a.student_id IN ({$arr['uids']})");
        }
        if ($arr['ckeyword']) {
            $query->andWhere("a.old_channel_name LIKE '%" . $arr['ckeyword'] . "%' 
                            OR a.old_channel_moblie LIKE '%" . $arr['ckeyword'] . "%' 
                            OR a.old_channel_wechat_name LIKE '%" . $arr['ckeyword'] . "%' 
                            OR a.new_channel_name LIKE '%" . $arr['ckeyword'] . "%' 
                            OR a.new_channel_moblie LIKE '%" . $arr['ckeyword'] . "%' 
                            OR a.new_channel_wechat_name LIKE '%" . $arr['ckeyword'] . "'");
        }
        if ($arr['aid']) {
            $query->andWhere('a.user_id = :account_id', [
                ':account_id' => $arr['aid']
            ]);
        }
        if ($arr['stime']) {
            $query->andWhere("a.created_time BETWEEN :stime AND :etime", [
                ':stime' => $arr['stime'],
                ':etime' => $arr['etime']
            ]);
        }
        if ($num < 0) {
            return $query->count();
        } else {
            $query->orderBy('id DESC');
            $query->offset(($num - 1) * 5);
            $query->limit(5);
            return $query->all();
        }
    }

    public function getSalesTradeById($id)
    {
        return SalesTrade::find()
            ->select('*')
            ->where('id = :id', [':id' => $id])
            ->asArray()
            ->one();
    }

    public function getTransferNewChannelInfo($id)
    {
        $query = new yii\db\Query();
        $query->select('a.status,a.student_id,a.id transfer_id,a.new_channel_id channel_id,a.new_channel_name uname,a.new_channel_wechat_name wechat_name,a.new_channel_moblie new_moblie,b.money,b.descp');
        $query->from('channel_transfer_info a');
        $query->leftJoin('sales_trade b', 'b.id = a.sales_trade_id');
        $query->where("a.type like '%2' AND a.id = :id", [':id' => $id]);
        return $query->one();
    }

    public function getChannelTransferTnfoById($id)
    {
        return ChannelTransferInfo::find()
            ->select('*')
            ->where('id = :id', [':id' => $id])
            ->one();
    }

    public function getPersonalServerPage($sdate, $edate)
    {
        $sql = UserLinkKefuChat::find()
            ->alias('ul')
            ->leftJoin('sales_channel AS sc', 'sc.bind_openid = ul.open_id')
            ->where('sc.status = 1 AND ul.create_time BETWEEN :sdate AND :edate', [
                ':sdate' => $sdate,
                ':edate' => $edate
            ]);

        return $sql->count();
    }

    public function getPersonalServerList($num, $sdate, $edate)
    {
        $sql = UserLinkKefuChat::find()
            ->alias('ul')
            ->select('ul.create_time, ul.open_id, sc.nickname, sc.created_at')
            ->leftJoin('sales_channel AS sc', 'sc.bind_openid = ul.open_id')
            ->where('sc.status = 1 AND ul.create_time BETWEEN :sdate AND :edate', [
                ':sdate' => $sdate,
                ':edate' => $edate
            ]);

        return $sql->orderBy('ul.create_time DESC ')
            ->offset(($num - 1) * 10)
            ->limit(10)
            ->asArray()
            ->all();
    }

    public function getMonthGiftPage($sdate, $edate, $userType, $kefuId)
    {
        $sql = SalesTrade::find()
            ->alias('st')
            ->leftJoin('sales_channel AS sc', 'sc.id = st.uid')
            ->where('st.is_deleted = 0 AND sc.status = 1 AND st.time_created BETWEEN :startDate AND :endDate', [
                ':startDate' => $sdate,
                ':endDate' => $edate,
            ]);
        if ($userType == 1) {
            //拉老师的奖励
            $sql = $sql->andWhere('st.status = 11');
        } elseif ($userType == 2) {
            //拉学生的奖励，报错拉一个学生和拉3个
            $sql = $sql->andWhere('st.status = 13');
        } elseif ($userType == 3) {
            //首次体验奖查询
            $sql = $sql->andWhere('st.status = 8 AND st.money = 88 ');
        }

        //渠道经理查询
        if (!empty($kefuId)) {
            $sql = $sql->andWhere('sc.kefu_id = :kefu_id', [
                ':kefu_id' => $kefuId
            ]);
        }

        return $sql->count();
    }

    public function getMonthGiftList($num, $sdate, $edate, $userType, $kefuId)
    {
        $sql = SalesTrade::find()
            ->alias('st')
            ->select('sc.id, sc.bind_openid, ua.nickname AS kefuName, st.time_created, sc.wechat_name AS nickname, sc.username, st.status, st.money')
            ->leftJoin('sales_channel AS sc', 'sc.id = st.uid')
            ->leftJoin('user_account AS ua', 'ua.id = sc.kefu_id')
            ->where('st.is_deleted = 0 AND sc.status = 1 AND st.time_created BETWEEN :startDate AND :endDate', [
                ':startDate' => $sdate,
                ':endDate' => $edate,
            ]);
        if ($userType == 1) {
            //拉老师的奖励
            $sql = $sql->andWhere('st.status = 11');
        } elseif ($userType == 2) {
            //拉学生的奖励，拉3个
            $sql = $sql->andWhere('st.status = 13');
        } elseif ($userType == 3) {
            //首次体验奖查询
            $sql = $sql->andWhere('st.status = 8 AND st.money = 88 ');
        }


        //渠道经理查询
        if (!empty($kefuId)) {
            $sql = $sql->andWhere('sc.kefu_id = :kefu_id', [
                ':kefu_id' => $kefuId
            ]);
        }

        $data = $sql->orderBy('st.time_created DESC')
            ->offset(($num - 1) * 200)
            ->limit(200)
            ->asArray()
            ->all();

        //var_dump($data);
        return $data;
    }

    public function getWaitStatisticsPage($startTime, $endTime)
    {
        $data = WaitStatistics::find()
            ->where('create_time > :startTime and create_time < :endTime', [
                ':startTime' => $startTime,
                ':endTime' => $endTime
            ])
            ->orderBy('create_time asc')
            ->asArray()
            ->all();

        return $data;
    }

    public function getAllClass()
    {
        $class = WechatClass::find()
            ->select('id,is_back')
            ->where('is_delete = 0')
            ->asArray()
            ->all();
        return $class;
    }

    public function getUserShare($openid)
    {
        $share = UserShare::find()
            ->select('class_id')
            ->where('open_id = :open_id', [
                ':open_id' => $openid
            ])
            ->asArray()
            ->column();
        return $share;
    }

    public function getChannelExClassInfo($channelId)
    {
        $data = ClassRoom::find()
            ->alias('cr')
            ->select('cr.id, cr.time_class, u.nick')
            ->leftJoin('user AS u', 'cr.student_id = u.id')
            ->where('cr.is_ex_class = 1 AND cr.is_deleted = 0 AND cr.status = 1 AND u.sales_id = :channelId', [
                ':channelId'=> $channelId
            ])
            ->orderBy('time_class DESC')
            ->asArray()
            ->all();
        return $data;
    }

    public function getVisitInfoById($visitId)
    {
        return ChannelVisitHistory::find()
            ->select('time_next, sale_channel_id, user_id_visit')
            ->where('id = :id', [
                ':id' => $visitId
            ])
            ->asArray()
            ->one();
    }

    public function getSaleChannelVisitNoDoneCountWhitDon($saleChannelId, $dayEnd)
    {
        return ChannelVisitHistory::find()
            ->where('sale_channel_id = :sale_channel_id AND is_done = 0 AND time_next < :time AND class_id > 0', [
                ':sale_channel_id' => $saleChannelId,
                ':time' => $dayEnd
            ])
            ->count();
    }

    public function getWoolPartyCount($kefuId)
    {
        return SalesChannel::find()
            ->where("id NOT IN (SELECT uid FROM sales_trade
                        WHERE status IN(8,9) AND is_deleted = 0 AND fromUid =0
                        GROUP BY uid
                        ORDER BY NULL)")
            ->andWhere(['in', 'message_type', [1, 2]])
            ->andWhere("status = 1")
            ->andFilterWhere([
                'kefu_id' => $kefuId
            ])
            ->count();
    }

    public function getWoolPartyChannel($num, $kefuId)
    {
        $join = "(SELECT user_id,SUM(money) money FROM redactive_record WHERE is_success=1 AND error_log =0 GROUP BY user_id ORDER BY NULL)";

        return SalesChannel::find()->alias('a')
            ->select('a.id,a.kefu_id,a.private_code,a.nickname,a.wechat_name,a.message_type,a.created_at,a.bind_openid')
            ->addselect(['COALESCE(b.money,0) money'])
            ->leftJoin(['b' => $join], 'b.user_id = a.id')
            ->where("a.id NOT IN (SELECT uid FROM sales_trade
                        WHERE status IN(8,9) AND is_deleted = 0 AND fromUid =0
                        GROUP BY uid
                        ORDER BY NULL)")
            ->andWhere(['in', 'a.message_type', [1, 2]])
            ->andWhere("a.status = 1")
            ->andFilterWhere(['a.kefu_id' => $kefuId])
            ->orderBy('b.money DESC')
            ->offset(($num -1) * 10)
            ->limit(10)
            ->asArray()
            ->all();
    }

    public function getChannelNumBySalesId($array)
    {
        return SalesChannel::find()
            ->select('from_code,count(from_code) channel_num')
            ->where(['in', 'from_code', $array])
            ->groupBy('from_code')
            ->asArray()
            ->all();
    }
    public function getSalesChannelMessageTypeById($id)
    {
        return SalesChannel::find()
            ->select('message_type')
            ->where('status = 1 AND id = :id', [
                ':id' => $id
            ])
            ->asArray()
            ->one();
    }

    public function getSalesChannelById($id)
    {
        return SalesChannel::find()
            ->where('status = 1 AND id =:id', [':id' => $id])
            ->asArray()
            ->one();
    }
 
    public function getPnlQrCodeByEventKey($mappedId)
    {
        return PnlQrCode::find()->alias('a')
            ->select('b.id,a.id cid')
            ->leftJoin('pnl_code_used b', 'a.id = b.qr_id')
            ->where('b.status = 1 AND a.type = 1 AND b.mapped_id = :mapped_id', [':mapped_id' => $mappedId])
            ->asArray()
            ->one();
    }

    public function getQrCodeNumByType($type = 0)
    {
        return PnlQrCode::find()
            ->where('type = 0')
            ->count();
    }

    public function getOneNewQrcode($id = 0)
    {
        return PnlQrCode::find()
            ->where("type = 0 AND id> $id")
            ->orderBy('id ASC')
            ->one();
    }

    public function getPnlCodeUsedByMappedId($mappedId)
    {
        return PnlCodeUsed::find()
            ->where("mapped_id =:mapped_id AND status = 1", [
                ':mapped_id'=>$mappedId
                ])
            ->one();
    }

    public function getPnlCodeUsedByOriginalId($originalId)
    {
        return PnlCodeUsed::find()
            ->where('original_id=:original_id AND status=1', [
                ':original_id'=>$originalId
            ])
            ->asArray()
            ->one();
    }

    public function getPnlQrCodeByPath($path)
    {
        return PnlQrCode::find()
            ->where("weicode_path = :weicode_path", [
                ':weicode_path' => $path
            ])
            ->asArray()
            ->one();
    }

    public function getChannelVisitHistoryByIds($classIds)
    {
        return ChannelVisitHistory::find()
            ->where(['IN', 'class_id', $classIds])
            ->select('id,class_id')
            ->asArray()->all();
    }
}
