<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/10
 * Time: 上午10:58
 */
namespace common\sources\read\account;

use common\models\music\ChannelKefuReception;
use common\models\music\ChannelVisitHistory;
use common\models\music\KefuFixTime;
use common\models\music\KefuFixTimeWeike;
use common\models\music\KefuTimetable;
use common\models\music\KefuTimetableWeike;
use common\models\music\VisitHistory;
use Yii;
use yii\db\ActiveRecord;
use common\models\music\UserAccount;
use common\models\music\KefuReception;

class AccountAccess implements IAccountAccess
{

    /**
     * 获取所有复购和新签的用户列表
     */
    public function getKefuList()
    {
        return UserAccount::find()
            ->select('id, nickname')
            ->where('role =4 AND status = 1')
            ->asArray()
            ->all();
    }

    public function getUserAccountOne($kefuId)
    {
        return UserAccount::find()
            ->select('day_user, nickname, head')
            ->where(['id' => $kefuId])
            ->one();
    }


    public function getAllUserUserAccountOne($kefuId)
    {
        return UserAccount::find()
            ->select('day_user, nickname, head')
            ->where(['id' => $kefuId])
            ->one();
    }

    public function getNewSignKefuList()
    {
        return UserAccount::find()
            ->select('id, nickname')
            ->where('role =1 AND status = 1')
            ->asArray()
            ->all();
    }

    public function getNewSignKefuNick($kefuId)
    {
        return UserAccount::find()
            ->select('nickname')
            ->where('id = :id', [
                ':id' => $kefuId
            ])
            ->scalar();
    }

    public function getReKefuList()
    {
        return UserAccount::find()
            ->select('id, nickname')
            ->where('(role = 1 OR role = 4 ) AND status = 1')
            ->asArray()
            ->all();
    }

    public function getNewAccountList()
    {
        return UserAccount::find()
            ->select('id, nickname')
            ->where('role = 1 AND status = 1')
            ->asArray()
            ->all();
    }


    public function getAllUserKefuInfo()
    {
        return UserAccount::find()
            ->select('id, nickname')
            ->where('role = 5 AND status = 1')
            ->asArray()
            ->all();
    }

    public function getKefuRoleByKefuid($kefuId)
    {
        return UserAccount::find()
            ->select('role')
            ->where('id = :id', [
                ':id' => $kefuId
            ])
            ->scalar();
    }


    private function getSalesSql($keyword, $timestart, $timeend)
    {
        $bodySql = 'role IN (1,2,4) AND status != 10';
        $bodyParams = [':time_start' => $timestart, ':time_end' => $timeend];

        if (!empty($keyword)) {
            $bodySql .= " AND nickname LIKE '%$keyword%'";
        }

        return [$bodySql, $bodyParams];
    }

    public function countSalesKefu($keyword, $timestart, $timeend)
    {
        list($bodySql, $bodyParams) = $this->getSalesSql($keyword, $timestart, $timeend);

        return UserAccount::find()
            ->alias('u')
            ->select('u.id')
            ->where($bodySql, $bodyParams)
            ->count();
    }

    public function countEmploye($keyword, $status)
    {


        $obj = UserAccount::find()
            ->alias('u')
            ->where('role = 5 ');
        if (!empty($keyword)) {
            $obj = $obj->andWhere(['LIKE', 'nickname', $keyword]);
        }

        if ($status != 0) {
            $obj = $obj->andWhere(' status = :status', [
                ':status' => $status
            ]);
        }
        return $obj->count();
    }


    public function getSalesKefuList($keyword, $timestart, $timeend, $num)
    {
        list($bodySql, $bodyParams) = $this->getSalesSql($keyword, $timestart, $timeend);

        return UserAccount::find()
            ->alias('u')
            ->select('u.id, u.nickname, u.day_user, u.no_buy, i.day_users, i.buy_num, i.day_to_buy_rate, i.ex_num, i.visit, i.visit_rate, i.connect_num, i.connect_rate, i.price_per, i.rebuy, i.rebuy_per, i.price_first, i.price_rebuy')
            ->leftJoin('(SELECT kefu_id,sum(day_user) as day_users, sum(buy_num) as buy_num, sum(day_to_buy_rate) as day_to_buy_rate, sum(ex_num) as ex_num, sum(visit) as visit, sum(visit_rate) as visit_rate, sum(connect_num) as connect_num, sum(connect_rate) as connect_rate, sum(price_per) as price_per, sum(rebuy) as rebuy, sum(rebuy_per) as rebuy_per, sum(price_first) as price_first, sum(price_rebuy) as price_rebuy FROM kefu_info WHERE timeDay >= :time_start AND timeDay <= :time_end GROUP BY kefu_id ) as i', 'i.kefu_id = u.id')
            ->where($bodySql, $bodyParams)
            ->offset(($num - 1) * 5)
            ->limit(5)
            ->asArray()
            ->all();
    }

    public function getEmployeList($keyword, $status, $num)
    {
        $obj = UserAccount::find()
            ->alias('u')
            ->select('u.id, u.nickname, u.telephone_system_name, u.no_buy, u.status')
            ->where('role = 5 ');
        if (!empty($keyword)) {
            $obj = $obj->andWhere(['LIKE', 'nickname', $keyword]);
        }
        if ($status != 0) {
            $obj = $obj->andWhere(' status = :status', [
                ':status' => $status
            ]);
        }

        return $obj->offset(($num - 1) * 10)
            ->limit(10)
            ->asArray()
            ->all();
    }


    public function getUserAccountNickName()
    {
        return UserAccount::find()
            ->select('id, nickname')
            ->where(['role' => 1, 'status' => 1])
            ->asArray()
            ->all();
    }


    public function getAtWorkKefuId($time)
    {
        return UserAccount::find()
            ->alias('u')
            ->select('u.id')
            ->leftJoin('kefu_timetable as t', 't .user_id = u.id ')
            ->where("t.time_day = :time AND u.role = 1 AND time_bit <= 299067162755071 ", [':time' => $time])
            ->column();
    }

    public function getAllAtWorkKefuId($time)
    {
        $sql = 'SELECT a.id, CONV(k.time_bit,2,10) as time_bit FROM kefu_timetable as k LEFT JOIN user_account AS a ON a.id = k.user_id 
WHERE k.time_day = :time AND a.role = 1';

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':time' => $time])
            ->queryAll();
    }

    public function getAllAtWorkChannelKefuId($time)
    {
        $sql = 'SELECT a.id, CONV(k.time_bit,2,10) as time_bit FROM kefu_timetable_weike as k LEFT JOIN user_account AS a ON a.id = k.user_id 
WHERE k.time_day = :time AND a.role = 5';

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':time' => $time])
            ->queryAll();
    }


    public function getAtWorkKefuInfo($time, $workinfo)
    {
        $workinfo = empty($workinfo) ? 0 : implode(',', $workinfo);

        return UserAccount::find()
            ->alias('u')
            ->select('u.id')
            ->leftJoin('(SELECT kefu_id,reception_quantity,automatic_growth FROM kefu_reception WHERE work_time = :time) AS re', 're.kefu_id = u.id')
            ->where("u.id IN ({$workinfo}) AND u.role = 1 AND u.status = 1 AND re.reception_quantity <= day_user ", ['time' => $time])
            ->orderBy('automatic_growth ASC')
            ->scalar();
    }

    public function getAtWorChannelKefuInfo($time, $workinfo)
    {
        $workinfo = empty($workinfo) ? 0 : implode(',', $workinfo);
        return UserAccount::find()
            ->alias('u')
            ->select('u.id')
            ->leftJoin('(SELECT kefu_id,reception_quantity,automatic_growth FROM channel_kefu_reception WHERE work_time = :time) AS re', 're.kefu_id = u.id')
            ->where("u.id IN ({$workinfo}) AND u.role = 5 AND u.status = 1 ", ['time' => $time])
            ->orderBy('automatic_growth ASC')
            ->scalar();
    }


    public function getAtWeekWorkCount($week, $time, $exculudeid)
    {
        return UserAccount::find()
            ->alias('u')
            ->leftJoin('kefu_fix_time as t', 't .kefu_id = u.id ')
            ->where("u.id NOT IN ($exculudeid) AND time_bit <= 299067162755071 AND t.week = :week AND u.role = 1 AND time_execute <= :time  ", [':week' => $week, ':time' => $time])
            ->count();
    }

    public function getAtWeekWorkKefuId($week, $timebit, $excludeid)
    {
        return UserAccount::find()
            ->alias('u')
            ->select('u.id')
            ->leftJoin('kefu_fix_time as t', 't.kefu_id = u.id')
            ->where("u.id NOT IN ($excludeid)  AND t.week = :week AND u.role = 1 AND t.time_execute < :time", [':week' => $week, ':time' => time()])
            ->andWhere(empty($timebit) ? ' t.time_bit < 562949953421311' : " t.time_bit & {$timebit} = 0")
            ->column();
    }

    public function getAtWeekWorkChannelKefuId($week, $timebit, $excludeid)
    {
        return UserAccount::find()
            ->alias('u')
            ->select('u.id')
            ->leftJoin('kefu_fix_time_weike as t', 't.kefu_id = u.id')
            ->where("u.id NOT IN ($excludeid)  AND t.week = :week AND u.role = 5 AND t.time_execute < :time", [':week' => $week, ':time' => time()])
            ->andWhere(empty($timebit) ? ' t.time_bit < 562949953421311' : " t.time_bit & {$timebit} = 0")
            ->column();
    }


    public function getMaxNumber($worktime)
    {
        return KefuReception::find()
            ->alias('re')
            ->select('MAX(automatic_growth)')
            ->where('work_time = :work_time', [':work_time' => $worktime])
            ->scalar();
    }

    public function getChannelMaxNumber($worktime)
    {
        return ChannelKefuReception::find()
            ->alias('re')
            ->select('MAX(automatic_growth)')
            ->where('work_time = :work_time', [':work_time' => $worktime])
            ->scalar();
    }

    public function getKefuNickByOpenId($openid)
    {
        return UserAccount::find()
            ->alias('u')
            ->select('u.nickname')
            ->leftJoin('user_public_info AS p', 'p.kefu_id = u.id')
            ->where('p.open_id = :open_id', [':open_id' => $openid])
            ->scalar();
    }

    public function getReKefuNickByOpenId($openid)
    {
        return UserAccount::find()
            ->alias('u')
            ->select('u.nickname')
            ->leftJoin('user_public_info AS p', 'p.kefu_id_re = u.id')
            ->where('p.open_id = :open_id', [':open_id' => $openid])
            ->scalar();
    }

    public function getAccountInfoByKefuId($kefuid)
    {
        return UserAccount::find()
            ->select('id,username,role,nickname,email,telephone_system_name,telephone_system_pwd,card,poster,qrcode,banner')
            ->where('id = :kefu_id', [
                ':kefu_id' => $kefuid
            ])
            ->one();
    }

    public function getChannelTodoList($start, $end)
    {
        return ChannelVisitHistory::find()
            ->alias('v')
            ->select('sale_channel_id AS uid, v.next_content, u.nickname, u.head, u.created_at, u.bind_openid')
            ->leftJoin('sales_channel AS u', 'u.id = v.sale_channel_id')
            ->where(['v.user_id_visit' => Yii::$app->user->identity->id])
            ->andWhere('v.is_done = 0 AND v.time_next > :start AND v.time_next < :end ', [
                ':start' => $start, ':end' => $end
            ])
            ->orderBy('v.time_next DESC')
            ->asArray()
            ->all();
    }

    public function getShowTodolistCount($dayEnd)
    {
        return ChannelVisitHistory::find()
            ->alias('v')
            ->where(['v.user_id_visit' => Yii::$app->user->identity->id])
            ->andWhere('v.is_done = 0 AND v.time_next >= :start AND v.time_next < :end ', [
                ':start' => 0, ':end' => $dayEnd
            ])
            ->count();
    }

    public function getUserAccountById($id)
    {
        return UserAccount::find()
            ->select('id, nickname')
            ->where(['id' => $id])
            ->asArray()
            ->one();
    }

    public function getPurChaseUserInfo($keyword)
    {
        return UserAccount::find()
            ->select('id, nickname')
            ->where('role = 4 AND status != 10' . (empty($keyword) ? '' : " AND nickname LIKE '{$keyword}%'"))
            ->asArray()
            ->all();
    }


    public function getEmployeDayTime($kefuid, $timeDay)
    {
        $sql = "SELECT CONV(time_bit, 2, 10) FROM kefu_timetable_weike WHERE user_id=:uid AND time_day=:tday ";
        $dayTimeBit = Yii::$app->db->createCommand($sql)
            ->bindValues(['uid' => $kefuid, ':tday' => $timeDay])
            ->queryScalar();

        return $dayTimeBit;
    }

    public function getEmployeWeekTime($kefuid, $week)
    {
        $fixedTimeRow = KefuFixTimeWeike::find()
            ->select('time_bit, time_execute')
            ->where(
                "kefu_id = :kefu_id AND week = :week",
                [':kefu_id' => $kefuid, ':week' => $week]
            )->asArray()->one();
        return $fixedTimeRow;
    }

    public function getFixedTimeBitList($kefuid)
    {

        return KefuFixTimeWeike::find()
            ->select('week,time_bit')
            ->where('kefu_id = :kefu_id', [':kefu_id' => $kefuid])
            ->asArray()
            ->all();
    }


    public function getExecuteTime($kefuid)
    {
        return KefuFixTimeWeike::find()
            ->select('time_execute')
            ->where('kefu_id = :kefu_id', [':kefu_id' => $kefuid])
            ->scalar();
    }


    public function getEndVisitRecordTime($id)
    {
        return VisitHistory::find()
            ->select('time_created')
            ->where(['user_id_visit' => $id])
            ->orderBy('id DESC')
            ->scalar();
    }

    public function getUserAccountDetailById($kefuId)
    {
        return UserAccount::findOne(['id' => $kefuId]);
    }

    public function getExClassReportKefuInfo()
    {
        return UserAccount::find()
            ->select('id, nickname')
            ->where('role = 5 AND status = 1')
            ->asArray()
            ->all();
    }

    public function validateUniqueUsername($userName)
    {
        return UserAccount::find()
            ->select('id')
            ->where('username = :username', [
                ':username' => $userName
            ])
            ->asArray()
            ->one();
    }

    public function validateUniqueEmail($email)
    {
        return UserAccount::find()
            ->select('id')
            ->where('email = :email', [
                ':email' => $email
            ])
            ->asArray()
            ->one();
    }


    public function validateUpdataUniqueEmail($id, $email)
    {
        return UserAccount::find()
            ->select('id')
            ->where('email = :email AND id !=:id ', [
                ':email' => $email,
                ':id' => $id
            ])
            ->asArray()
            ->one();
    }

    public function getChannelCode($userid)
    {
        return UserAccount::find()
            ->select('channel_code')
            ->where('id =:id ', [
                ':id' => $userid
            ])
            ->asArray()
            ->one();
    }

    public function getKefuByIds($array)
    {
        return UserAccount::find()
            ->select('id,nickname')
            ->where(['in', 'id', $array])
            ->asArray()
            ->all();
    }
}
