<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/13
 * Time: 上午11:17
 */
namespace common\sources\write\channel;

use common\models\music\HistoryTrade;
use common\models\music\PosterPushStatistic;
use common\models\music\SalesCashout;
use common\models\music\SalesChannelScan;
use common\models\music\SalesPushMessage;
use common\models\music\SalesTrade;
use common\models\music\TemplatePushStatistic;
use common\models\music\UserShare;
use common\models\music\RedactiveRecord;
use common\models\music\RedpackChannel;
use common\models\music\SalesChannel;
use common\models\PnlQrCode;
use common\models\PnlCodeUsed;

use Yii;
use yii\db\ActiveRecord;

class ChannelAccess implements IChannelAccess
{
    public function addSalesChannelScan($openid, $salesId)
    {
        $salesChannelScan = new SalesChannelScan();

        $salesChannelScan->openid = $openid;
        $salesChannelScan->sales_id = $salesId;
        $salesChannelScan->time_created = time();

        return $salesChannelScan->save();
    }
    public function saveSalesTrade($userInfo, $leftInfo)
    {
        $salesTrade = new SalesTrade();

        $salesTrade->uid = $userInfo['sales_id'];
        $salesTrade->studentID = $userInfo['id'];
        $salesTrade->studentName =$userInfo['nick'];
        $salesTrade->money = floor($leftInfo['ac_amount'] * $leftInfo['price'] * 0.08 * 100) / 100;
        $salesTrade->comment = '学生退费';
        $salesTrade->status = 5;
        $salesTrade->time_created = time();

        return $salesTrade->save();
    }

    public function addSalesTradeUncashoutByPurchase($price, $studentInfo)
    {
        $trade = new SalesTrade();

        $trade->uid = $studentInfo['sales_id'];
        $trade->studentID = $studentInfo['id'];
        $trade->studentName = $studentInfo['nick'];
        $trade->money = $price;
        $trade->comment = '学生购买套餐';
        $trade->status = 9;
        $trade->time_created = time();

        return $trade->save();
    }

    public function addFatherSalesTradePurchase($data)
    {
        $trade = new SalesTrade();

        $trade->uid = $data['uid'];
        $trade->fromUid = $data['from_uid'];
        $trade->studentID = $data['student_id'];
        $trade->studentName = $data['student_name'];
        $trade->money = $data['money'];
        $trade->comment = '下属渠道收入(学生购买)';
        $trade->status = 9;
        $trade->time_created = time();

        return $trade->save();
    }

    public function addSalesTradeUncashoutByChange($price, $studentInfo)
    {
        $trade = new SalesTrade();

        $trade->uid = $studentInfo['sales_id'];
        $trade->studentID = $studentInfo['id'];
        $trade->studentName = $studentInfo['nick'];
        $trade->money = $price;
        $trade->comment = '学生更换套餐';
        $trade->status = 9;
        $trade->time_created = time();

        return $trade->save();
    }

    public function addSalesTrade($data)
    {
        $trade = new SalesTrade();

        $trade->uid = $data['uid'];
        $trade->studentID = $data['student_id'];
        $trade->studentName = $data['student_name'];
        $trade->classID = $data['class_id'];
        $trade->classType = $data['class_type'];
        $trade->price = $data['price'];
        $trade->recordID = $data['record_id'];
        $trade->money = $data['money'];
        $trade->descp = $data['descp'];
        $trade->comment = $data['comment'];
        $trade->status = $data['status'];
        $trade->fromUid = $data['fromUid'];
        $trade->time_created = time();
        
        return $trade->save();
    }

    public function updateRegisterSalesTrade($data)
    {
        $sql = "UPDATE sales_trade SET status = 1 WHERE uid = :uid AND studentID = :student_id AND studentName = :name";
        
        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':uid' => $data['uid'],
                ':student_id' => $data['student_id'],
                ':name' => $data['student_name']
            ])->execute();
    }

    public function addChannelAppPush($data)
    {
        $push = new SalesPushMessage();

        $push->uid = $data['uid'];
        $push->title = $data['title'];
        $push->content = $data['content'];
        $push->icon_path = $data['icon'];
        $push->time_created = time();
        
        return $push->save();
    }

    public function doEditUser($openId, $nickname, $phone, $worth, $remark, $instrument)
    {
        $sql = 'UPDATE sales_channel SET nickname = :nickname,  username = :phone, message_type = :worth, updated_at = :updated_at,remark=:remark,instrument=:instrument  WHERE bind_openid = :open_id';

        return Yii::$app->db->createCommand($sql)
                            ->bindValues([
                                ':nickname' => $nickname,
                                ':phone' => $phone,
                                ':worth' => $worth,
                                ':open_id' => $openId,
                                ':updated_at' => time(),
                                ':remark' => $remark,
                                ':instrument' => $instrument,
                            ])
                            ->execute();
    }

    public function doUpdateSaleChannelWorth($id, $worth)
    {
        $sql = 'UPDATE sales_channel SET message_type = :worth , updated_at = :updated_at WHERE id = :id';

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':worth' => $worth,
                ':updated_at' => time(),
                ':id' => $id
            ])
            ->execute();
    }

    public function insertSalesChannlWithWewhat($userInfo, $privateCode, $fromCode, $fromOpenid)
    {
        $user = new \common\models\SalesChannel();
        $user->type = 0;
        $user->username = '';
        $user->nickname = $userInfo['nickname'];
        $user->wechat_name = $userInfo['nickname'];
        $user->province = $userInfo['province'];
        $user->head = $userInfo['headimgurl'];
        $user->accessToken = Yii::$app->security->generateRandomString();
        $user->setPassword('123456');
        $user->generateAuthKey();
        $user->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
        $user->private_code = $privateCode;
        $user->from_code = $fromCode;
        $user->bind_openid = $fromOpenid;
        $user->union_id = $userInfo['unionid'];
        $user->update_time = time();

        $user->save();

        return Yii::$app->db->getLastInsertID();
    }

    public function updateUserShareWithPullNum($id)
    {
        $sql = 'UPDATE user_share SET pull_num = pull_num + 1, click_num = click_num + 1 WHERE id = :id';

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':id' => $id
            ])
            ->execute();
    }

    public function updateSalesChannelWithWpathById($path, $insertId, $time = 0)
    {
        $sql = 'UPDATE sales_channel SET weicode_path = :path, reqrcode_time = :reQrcodeTime WHERE id = :id';

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':path' => $path,
                ':id' => $insertId,
                ':reQrcodeTime' => $time
            ])
            ->execute();
    }

    public function updateSalesTradeStatus($id, $historyId)
    {
        $sql = 'UPDATE sales_trade SET is_cashout = 1, history_id = :history_id, cashout_code = :code  WHERE id = :id AND status > 0';

        return Yii::$app->db->createCommand($sql)
                        ->bindValues([':id' => $id, ':history_id' => $historyId, ':code' => md5(time().mt_rand(1000, 9999))])
                        ->execute();
    }

    public function addSalesTradeInfo($uid, $transaction, $money)
    {
        $trade = new SalesTrade();
        $trade->time_created = time();
        $trade->studentName = '红包奖励';
        $trade->uid = $uid;
        $trade->status = '-1';
        $trade->transaction_id = $transaction;
        $trade->money = $money;
        $trade->comment = '提现';
        $trade->save();

        return Yii::$app->db->getLastInsertID();
    }

    public function addHistoryTrade($tradeId, $uid, $payableMoney, $rewardMoney, $totalMoney)
    {
        $trade = new SalesCashout();
        $trade->trade_id = $tradeId;
        $trade->uid = $uid;
        $trade->cash = $payableMoney;
        $trade->reward = $rewardMoney;
        $trade->total = $totalMoney;
        $trade->time_created = time();
        $trade->save();

        return Yii::$app->db->getLastInsertID();
    }

    public function bindChannelKefu($uid, $kefuId)
    {
        $sql = 'UPDATE sales_channel SET kefu_id = :kefuId, updated_at = :update_time'
            . ' WHERE id = :userid ';

        Yii::$app->db->createCommand($sql)
            ->bindValues([':kefuId' => $kefuId,
                ':userid' => $uid,
                ':update_time' => time()
            ])
            ->execute();
    }

    public function doOpenPremission($uid)
    {
        $sql = 'UPDATE sales_channel SET have_premission = 1 WHERE id = :id';

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':id', $uid)
            ->execute();
    }

    public function doDeleteUser($id)
    {
        $sql = 'UPDATE sales_channel SET status = 10 where id =:id';

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':id', $id)
            ->execute();
    }



    public function doDeleteAllUser($id)
    {
        $sql = 'UPDATE sales_channel SET status = 10 where id =:id';

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':id', $id)
            ->execute();
    }


    public function doAddUserShareInfo($openId, $classId, $backType)
    {
        $share = new UserShare();
        $share->class_id = $classId;
        $share->open_id = $openId;
        $share->click_num = 0;
        $share->pull_num = 0;
        $share->is_purview = 1;
        $share->is_back_share = $backType;
        $share->share_time = time();
        return $share->save();
    }

    public function doUpdateUserShareInfo($openId, $classId, $backType)
    {
        $sql = 'UPDATE user_share SET is_purview = 1 WHERE open_id = :open_id AND class_id = :class_id';

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':class_id' => $classId,
                ':open_id' => $openId
            ])
            ->execute();
    }

    /*
     * create by sjy 2017-03-24
     * 添加红包活动记录
     */
    public function addRedpackageRecord($redlistinfo)
    {
        $RedactiveRecord = new RedactiveRecord();
        $RedactiveRecord->active_id = $redlistinfo["active_id"];
        $RedactiveRecord->open_id = $redlistinfo["open_id"];
        $RedactiveRecord->money = $redlistinfo["money"];
        $RedactiveRecord->createtime = $redlistinfo["createtime"];
        $RedactiveRecord->is_success = $redlistinfo["is_success"];
        $RedactiveRecord->error_log = $redlistinfo["error_log"];
        $RedactiveRecord->save();
    }

    public function savePosterPushStatistic($arr)
    {
        $time =  time();
        $poster_push_statistic = new  PosterPushStatistic();
        $poster_push_statistic->open_id = $arr['touser'];
        $poster_push_statistic->poster_id = $arr['poster_id'];
        $poster_push_statistic->create_time =$time;
        $poster_push_statistic->date = date('Ymd', $time);
        return $poster_push_statistic->save();
    }

    public function saveTemplatePushStatistic($msg)
    {
        $temp_sta = new TemplatePushStatistic();
        $temp_sta->template_id = $msg['template_info']['id'];
        $temp_sta->open_id = $msg['touser'];
        $temp_sta->create_time = time();
        return $temp_sta->save();
    }

    public function addBuyOrderTrade($uid, $studentID, $studentName, $money)
    {
        $sales_trade = new SalesTrade();

        $sales_trade->uid = $uid;
        $sales_trade->studentID = $studentID;
        $sales_trade->studentName = $studentName;
        $sales_trade->comment = '学生购买套餐';
        $sales_trade->money = $money;
        $sales_trade->status = 9;
        $sales_trade->time_created = time();

        return $sales_trade->save();
    }

    public function doChangeChannel($uid, $studentID, $salesId)
    {
        $sql = "UPDATE sales_trade SET uid = :uid WHERE studentID = :studentID AND uid =:sales_id AND time_created > 1488297600";
        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':uid' => $uid,
                ':studentID' => $studentID,
                ':sales_id' => $salesId
            ])
            ->execute();
    }

    public function doChangeChannelIsNull($studentID, $salesId)
    {
        $sql = "UPDATE sales_trade SET is_deleted = 1, comment = '学生购买套餐（更换为无渠道）'  WHERE studentID = :studentID  AND uid =:sales_id AND time_created > 1488297600 ";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':studentID' => $studentID,
                ':sales_id' => $salesId
            ])
            ->execute();
    }

    /*
     * 推荐体验课发红包记录表
     */
    public function addRedpackChannel($redlistinfo)
    {
        $RedpackChannel = new RedpackChannel();
        $RedpackChannel->open_id = $redlistinfo["openid"];
        $RedpackChannel->money = $redlistinfo["money"];
        $RedpackChannel->classId = $redlistinfo["classId"];
        $RedpackChannel->createtime = $redlistinfo["createtime"];
        $RedpackChannel->is_success = $redlistinfo["is_success"];
        $RedpackChannel->error_log = $redlistinfo["error_log"];
        $RedpackChannel->save();
    }

    public function lightenUser($openId, $lightenStatus)
    {
        $sql = 'UPDATE sales_channel SET lighten_status = :lighten_status WHERE bind_openid = :bind_openid';
        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':bind_openid' => $openId,
                ':lighten_status' => $lightenStatus
            ])
            ->execute();
    }

    public function getRewardByIdAndTime($uid, $startTime, $endTime)
    {
        return SalesTrade::find()
                ->select('id')
                ->where('status = 11 AND uid=:uid', [':uid' => $uid])
                ->andWhere("time_created >= $startTime AND time_created<=$endTime")
                ->one();
    }

    public function addChannleLog($category, $content)
    {
        $channelLog = new ChannelLogs;
        $channelLog->category = $category;
        $channelLog->content = $content;
        $channelLog->create_time = time();
        $channelLog->save();
    }

    public function saveEditSaleChannelByObject($object)
    {
        return $object->save();
    }

    public function saveChannelTransferInfo($object)
    {
        return $object->save();
    }

    public function saveTransferChannleReward($data)
    {
        $model = new SalesTrade;
        $model->uid = $data['uid'];
        $model->money = $data['money'];
        $model->descp = $data['descp'];
        $model->comment = $data['comment'];
        $model->status = $data['status'];
        $model->studentID = $data['studentID'];
        $model->studentName = $data['studentName'];
        $model->time_created = time();

        $model->save();
        return Yii::$app->db->getLastInsertID();
    }
    
    public function saveSuperClass($insertData)
    {
        $tableName = UserShare::tableName();
        $field = ['class_id', 'open_id', 'share_time', 'is_purview', 'is_back_share', 'user_id'];
        $class = Yii::$app->db->createCommand()->batchInsert($tableName, $field, $insertData)->execute();
        return $class;
    }

    public function doneVisitById($visitId)
    {
        $sql = "UPDATE channel_visit_history SET is_done = 1 WHERE id = :id";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':id' => $visitId,
            ])
            ->execute();
    }
    
    public function getChannelAuth($openId)
    {
        $user = SalesChannel::find()
                ->where('bind_openid = :bind_openid and status = 1', [
                    ':bind_openid' => $openId
                ])
                ->one();
        $user->auth_time = time();
        $re = $user->save();
        return $re;
    }
    
    public function doUserShareInfo($userid, $openId, $classId, $backType)
    {
        $share = UserShare::find()
                ->where('class_id = :class_id AND open_id = :open_id AND is_purview = 0', [
                    ':class_id' => $classId,
                    ':open_id' => $openId
                ])
                ->one();
        
        if (empty($share)) {
            $share = new UserShare();
        }
        
        $share->class_id = $classId;
        $share->open_id = $openId;
        $share->is_purview = 1;
        $share->user_id = $userid;
        $share->is_back_share = $backType;
        $share->share_time = time();
        return $share->save();
    }

    public function setTypeWoolParty($id)
    {
        $sql = "UPDATE sales_channel SET message_type = 3 WHERE id = :id";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':id' => $id,
            ])
            ->execute();
    }

    public function updatePnlCodeUsedById($id)
    {
        $sql = "UPDATE pnl_code_used SET status = 0,throw_time=:stime WHERE id = :id";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':id' => $id,
                ':stime' => time()
            ])
            ->execute();
    }

    public function updatePnlQrCodeById($cid)
    {
        $sql = "UPDATE pnl_qr_code SET type = 0 WHERE id = :id";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':id' => $cid
            ])
            ->execute();
    }

    public function insertPnlQrCode($weicodePath, $eventKey)
    {
        $model = new PnlQrCode;
        $model->weicode_path = $weicodePath;
        $model->event_key = $eventKey;
        $model->type = 0;
        return $model->save();
    }

    //此方法仅返回实例
    public function insertPnlCodeUsed($qrId, $originalId, $mappedId)
    {
        $model = new PnlCodeUsed;
        $model->qr_id = $qrId;
        $model->original_id = $originalId;
        $model->mapped_id = $mappedId;
        $model->created_time = time();
        $model->status = 1;
        return $model;
    }
}
