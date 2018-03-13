<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 16/12/16
 * Time: 10:01
 */
namespace common\sources\read\teacher;

use common\models\music\UserSchoolInstrument;
use Yii;
use common\models\music\UserTeacherSchool;
use common\models\music\UserTeacherClass;
use common\models\music\School;

Class TrainAccess implements ITrainAccess
{
    /**
     * @param string $filter  空|手机号|姓名
     * @return array
     */
    public function  selectTraceList($filter)
    {
        return UserTeacherSchool::find()
            ->select("id,openid, nick, subscribe_time, name, mobile, school, major, grade, type, opern, command, register_time, status, is_deleted, opern_score, command_score,"."`opern_score` + `command_score` as score")
            ->where("status = 0 and is_deleted = 0 and openid != '' and mobile != '' ")
            ->andWhere((empty($filter) ? "" : "(mobile like '%{$filter}%' OR name like '%{$filter}%')"))
            ->orderBy('score DESC, subscribe_time DESC')
            ->asArray()
            ->all();
    }

    /**
     * @param $filter
     * @param $page_num int
     * @return array
     */
    public function getTraceList($filter, $page_num)
    {
        return UserTeacherSchool::find()
            ->select("id,openid, nick, subscribe_time, name, mobile, school, major, grade, type, opern, command, register_time, status, is_deleted, opern_score, command_score,"."`opern_score` + `command_score` as score")
            ->where("status = 0 and is_deleted = 0 and openid != '' and mobile != '' ")
            ->andWhere((empty($filter) ? "" : "(mobile like '%{$filter}%' OR name like '%{$filter}%')"))
            ->orderBy('score DESC, subscribe_time DESC')
            ->offset(($page_num-1) * 10)
            ->limit(10)
            ->asArray()
            ->all();
    }

    /**
     * @param string $filter  空|手机号|姓名
     * @return array
     */
    public function getFailedList($filter, $page_num){
        return UserTeacherSchool::find()
            ->select("id,openid, nick, time_overed, name, mobile, school, major, grade, type, opern, command, register_time, status, is_deleted, opern_score, command_score,"."`opern_score` + `command_score` as score")
            ->where("(status = 3 or status = 4) and is_deleted = 0 and openid != '' and mobile != '' ")
            ->andWhere((empty($filter) ? "" : "(mobile like '%{$filter}%' OR name like '%{$filter}%')"))
            ->orderBy('score DESC, time_overed DESC')
            ->offset(($page_num-1) * 10)
            ->limit(10)
            ->asArray()
            ->all();
    }

    /**
     * @param string $filter  空|手机号|姓名
     * @return array
     */
    public function selectFailedList($filter)
    {
        return UserTeacherSchool::find()
            ->where("(status = 3 or status = 4) and is_deleted = 0 and openid != '' and mobile != '' ")
            ->andWhere((empty($filter) ? "" : "(mobile like '%{$filter}%' OR name like '%{$filter}%')"))
            ->count();
    }

    /**
     * @param int $trace_id
     * @return array
     */
    public function selectTraceDetail($trace_id)
    {
        return UserTeacherSchool::find()
            ->where('id=:id',[':id'=>$trace_id])
            ->asArray()
            ->one();
    }

    /**
     * @param string $filter
     * @return array
     */
    public function selectTraceTeacherList($filter)
    {
        return UserTeacherSchool::find()
            ->select('id, nick, subscribe_time, name, mobile, school, major, grade, type, opern, command, register_time, status, is_deleted, opern_score, command_score')
            ->where("status = 1 and is_deleted = 0 and openid != '' and mobile != '' ")
            ->andWhere((empty($filter) ? "" : "(mobile like '%{$filter}%' OR name like '%{$filter}%')"))
            ->orderBy('subscribe_time DESC')
            ->asArray()
            ->all();
    }

    /**
     * @param string $filter
     * @param int $page_num
     * @return array
     */
    public function getTraceTeacherList($filter, $page_num)
    {
        return UserTeacherSchool::find()
            ->select('id, nick, subscribe_time, name, mobile, school, major, grade, type, opern, command, register_time, status, is_deleted, opern_score, command_score, time_passed, class_id')
            ->where("status = 1 and is_deleted = 0 and openid != '' and mobile != '' ")
            ->andWhere((empty($filter) ? "" : "(mobile like '%{$filter}%' OR name like '%{$filter}%')"))
            ->orderBy('subscribe_time DESC')
            ->offset(($page_num-1) * 10)
            ->limit(10)
            ->asArray()
            ->all();
    }

    /**
     * @param $user_id int
     * @return array
     *
     */
    public function getSchoolInstrument($user_id)
    {
       return UserSchoolInstrument::find()->select('user_id, instrument_id, grade, level')
           ->where('user_id ='.$user_id)
           ->asArray()
           ->all();
    }


    /**
     * @param string $filter
     * @return array
     */
    public function selectTraceQuitList($filter)
    {
        return UserTeacherSchool::find()
            ->select('id, nick, subscribe_time, name, mobile, school, major, grade, type, opern, command, register_time, status, is_deleted, opern_score, command_score')
            ->where("status = 2 and is_deleted = 0 and openid != '' and mobile != '' ")
            ->andWhere((empty($filter) ? "" : "(mobile like '%{$filter}%' OR name like '%{$filter}%')"))
            ->orderBy('subscribe_time DESC')
            ->asArray()
            ->all();
    }

    public function getAddUserList()
    {
        $sql = "SELECT id, name, mobile FROM user_teacher_school WHERE class_id < 1 AND openid != '' AND mobile != '' AND is_deleted = 0 AND status = 1";

        return Yii::$app->db->createCommand($sql)
                        ->queryAll();
    }

    public function getClassList($filter, $page_num)
    {
        $sql = "SELECT COUNT(u.id) AS num, c.id, c.name, c.time_start, c.time_end, c.time_created, c.time_updated FROM `user_teacher_class` AS c"
            . " LEFT JOIN user_teacher_school as u ON u.class_id = c.id"
            . " WHERE c.is_deleted = 0"
            . (empty($filter) ? "" : " AND c.name LIKE '%".$filter."%'")
            . " GROUP BY c.id"
            . " LIMIT ".(($page_num-1)*10).",10";

        return Yii::$app->db->createCommand($sql)
                    ->queryAll();
    }

    public function getClassCount($filter)
    {
        $sql = "SELECT count(id) FROM user_teacher_class WHERE is_deleted = 0"
            . (empty($filter) ? "" : " AND name LIKE '%" . $filter . "%'");

        return Yii::$app->db->createCommand($sql)
            ->queryScalar();
    }

    /**
     * @param string $filter
     * @param int $page_num
     * @return array
     */
    public function getTraceQuitList($filter, $page_num)
    {
        return UserTeacherSchool::find()
            ->select('id, nick, subscribe_time, name, mobile, school, major, grade, type, opern, command, register_time, status, is_deleted, opern_score, command_score, time_overed')
            ->where("status = 2 and is_deleted = 0 and openid != '' and mobile != '' ")
            ->andWhere((empty($filter) ? "" : "(mobile like '%{$filter}%' OR name like '%{$filter}%')"))
            ->orderBy('subscribe_time DESC')
            ->offset(($page_num-1) * 10)
            ->limit(10)
            ->asArray()
            ->all();
    }

    public function getClassInfoById($id)
    {
        return UserTeacherClass::find()
                        ->where(['id' => $id])
                        ->asArray()
                        ->one();
    }

    public function getClassMembersById($class_id)
    {
        return UserTeacherSchool::find()
                        ->select('id, name, mobile')
                        ->where(['class_id' => $class_id, 'is_deleted' => 0])
                        ->asArray()
                        ->all();
    }

    public function getClassCreatedTime($class_id)
    {
        return UserTeacherClass::find()
                        ->select('time_created')
                        ->where(['id' => $class_id])
                        ->scalar();
    }



    public function getEndClassUserList($class_id)
    {
        $sql = "SELECT u.id, u.name, u.mobile FROM user_teacher_school AS u LEFT JOIN user_teacher_class as c ON c.id = u.class_id WHERE c.time_end + 86400 <= UNIX_TIMESTAMP() AND  u.is_deleted = 0"
            . (empty($class_id) ? "" : " AND c.id != :cid");

        return Yii::$app->db->createCommand($sql)
                        ->bindValue(':cid', $class_id)
                        ->queryAll();
    }

    public function getAllotClass($uid)
    {

        $sql = "SELECT id, name FROM user_teacher_class WHERE is_deleted = 0 AND time_end + 86400 > UNIX_TIMESTAMP()"
            . " AND id NOT IN (SELECT class_id FROM user_teacher_school WHERE id = :uid)";

        return Yii::$app->db->createCommand($sql)
                        ->bindValue(':uid', $uid)
                        ->queryAll();
    }

    public function getSchoolList()
    {
        return School::find()
            ->asArray()
            ->all();
    }

    public function getSchoolById($schoolId)
    {
        return School::find()
            ->where(['id' => $schoolId])
            ->asArray()
            ->one();
    }
}
