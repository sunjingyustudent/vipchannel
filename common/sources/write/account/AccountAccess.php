<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/10
 * Time: 上午10:58
 */
namespace common\sources\write\account;

use Yii;
use yii\db\ActiveRecord;
use common\models\music\UserAccount;
use common\models\music\KefuReception;

class AccountAccess implements IAccountAccess
{
    public function addCourseKefuInKefuManagement($userName, $nick, $email, $type, $telephoneName, $telephonePwd)
    {
        $userAcc = new UserAccount();
        $userAcc->type = 'crm';
        $userAcc->username = $userName;
        $userAcc->nickname = $nick;
        $userAcc->email = $email;
        $userAcc->status = 1;
        $userAcc->created_at = time();
        $userAcc->role = $type;
        $userAcc->telephone_system_name = $telephoneName;
        $userAcc->telephone_system_pwd = $telephonePwd;

        return $userAcc->save();
    }



    public function addEmployeManagement($request)
    {
        $userAcc = new UserAccount();
        $userAcc->type = 'channel';
        $userAcc->username = $request['username'];
        $userAcc->nickname = $request['nick'];
        $userAcc->email = $request['email'];
        $userAcc->status = 1;
        $userAcc->created_at = time();
        $userAcc->role = $request['type'];
        $userAcc->telephone_system_name = $request['telephone_name'];
        $userAcc->telephone_system_pwd = $request['telephone_pwd'];

        $userAcc->card = $request['card'];
        $userAcc->poster = $request['poster'];
        $userAcc->qrcode = $request['qrcode'];
        $userAcc->banner = $request['banner'];

        return $userAcc->save();
    }


    public function updateCourseKefuInKefuManagement($request)
    {
        $sql = 'UPDATE user_account SET nickname = :nickname '.
            ',email = :email '.
            ',role = :role '.
            ',telephone_system_name = :telephone_system_name '.
            ',telephone_system_pwd = :telephone_system_pwd '.
            ',card = :card '.
            ',poster = :poster '.
            ',qrcode = :qrcode '.
            ',banner = :banner '.
            ' WHERE id = :kefu_id ';
        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':nickname' => $request['nick'],
                ':email' => $request['email'],
                ':role' => $request['type'],
                ':telephone_system_name' => $request['telephone_name'],
                ':telephone_system_pwd' => $request['telephone_pwd'],
                ':kefu_id' => $request['kefu_id'],
                ':card' => $request['card'],
                ':poster' => $request['poster'],
                ':qrcode' => $request['qrcode'],
                ':banner' => $request['banner'],
            ])
            ->execute();
    }

    public function deleteKefu($kefuid)
    {
        //删除一个客服
        $sql = 'UPDATE user_account SET status = 10, updated_at = :time'
            . ' WHERE id = :kefu_id ';

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':kefu_id' => $kefuid,
                ':time' => time()
            ])
            ->execute();
    }

    /**
     * @param $kefu_id
     * @return mixed
     * create by wangke
     * VIP微课  开启
     */
    public function openEmploye($kefuid)
    {
        $sql = 'UPDATE user_account SET status = 1, updated_at = :time'
            . ' WHERE id = :kefu_id ';

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':kefu_id' => $kefuid,
                ':time' => time()
            ])
            ->execute();
    }


    public function doAddKefuReceptionInfo($kefuid, $worktime, $automaticgrowth)
    {
        $sql = "INSERT INTO kefu_reception (kefu_id, work_time, reception_quantity, automatic_growth)
 values(:kefu_id, :work_time, 1, :automatic_growth) ON DUPLICATE KEY UPDATE reception_quantity = reception_quantity + 0";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':kefu_id' => $kefuid,
                ':work_time' => $worktime,
                ':automatic_growth' => $automaticgrowth
            ])
            ->execute();
    }

    public function doAddChannelKefuReceptionInfo($kefuid, $worktime, $automaticgrowth)
    {
        $sql = "INSERT INTO channel_kefu_reception (kefu_id, work_time, reception_quantity, automatic_growth)
 values(:kefu_id, :work_time, 1, :automatic_growth) ON DUPLICATE KEY UPDATE reception_quantity = reception_quantity + 0";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':kefu_id' => $kefuid,
                ':work_time' => $worktime,
                ':automatic_growth' => $automaticgrowth
            ])
            ->execute();
    }

    public function doEditKefuReceptionInfo($kefuid, $worktime)
    {
        $sql = 'UPDATE  kefu_reception SET  reception_quantity = reception_quantity + 1 , automatic_growth = automatic_growth + 1 WHERE kefu_id = :kefu_id ';
        $sql .= 'AND  work_time = :work_time';

        return Yii::$app->db->createCommand($sql)
                        ->bindValues([
                            ':kefu_id' => $kefuid,
                            ':work_time' => $worktime
                        ])
                        ->execute();
    }

    public function doEditChannelKefuReceptionInfo($kefuid, $worktime)
    {
        $sql = 'UPDATE  channel_kefu_reception SET  reception_quantity = reception_quantity + 1 , automatic_growth = automatic_growth + 1 WHERE kefu_id = :kefu_id ';
        $sql .= 'AND  work_time = :work_time';

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':kefu_id' => $kefuid,
                ':work_time' => $worktime
            ])
            ->execute();
    }

    public function doAddKefuReceptionGrowth($kefuid, $worktime, $automaticgrowth)
    {
        $sql = "INSERT INTO kefu_reception (kefu_id, work_time, reception_quantity, automatic_growth)
 values(:kefu_id, :work_time, 1, :automatic_growth) ON DUPLICATE KEY UPDATE automatic_growth = :automatic_growth";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':kefu_id' => $kefuid,
                ':work_time' => $worktime,
                ':automatic_growth' => $automaticgrowth
            ])
            ->execute();
    }

    public function addKefuTimetable($kefuid, $timeDay, $timeBit)
    {
        $sql = "INSERT INTO kefu_timetable_weike(user_id,time_day,time_bit,time_created) VALUES(:kefu_id, :timeDay, ".$timeBit.", :time_created)"
            . " ON DUPLICATE KEY UPDATE time_bit = ".$timeBit.", time_updated = :time_update";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':kefu_id' => $kefuid,
                ':timeDay' => $timeDay,
                ':time_created' => time(),
                ':time_update'=>time()
            ])->execute();
    }

    public function addKfuFixedTime($kefuId, $week, $timeBit, $timeExecute)
    {
        $sql = "INSERT INTO kefu_fix_time_weike(kefu_id,week,time_bit,time_execute,time_created) VALUES(:kefu_id,:week,".$timeBit.",:time_execute,:time_created)"
            . " ON DUPLICATE KEY UPDATE time_bit = ".$timeBit.", time_execute = :time_execute, time_updated = :time_update";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':kefu_id' => $kefuId,
                ':week' => $week,
                ':time_execute' => $timeExecute,
                ':time_created' => time(),
                ':time_update' => time()
            ])->execute();
    }
    
    /*
     * 修改渠道经理的专属二维码
     * create by sjy
     */
    public function channelCode($channelcode, $userid)
    {
        $sql = 'UPDATE  user_account SET  channel_code = :channel_code WHERE id = :id ';
    
        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':channel_code' => $channelcode,
                ':id' => $userid
            ])
            ->execute();
    }
}
