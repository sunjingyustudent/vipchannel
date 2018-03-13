<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/13
 * Time: 上午11:08
 */
namespace common\sources\write\teacher;

use common\models\music\SalesFeedback;
use common\models\music\StatisticsTeacherRest;
use common\models\music\TeacherRewardRule;
use Yii;
use common\models\music\BasePlace;
use common\models\music\TeacherWechatAcc;

Class TeacherAccess implements ITeacherAccess
{
    /**
     * 批量插入数据
     * @param   sql   str
     */
    public function doAddAllRewardRecord($sql)
    {
        $sql =  $sql;
        Yii::$app->db->createCommand($sql)
                    ->execute();
        return Yii::$app->db->getLastInsertID();
    }

    public function deleteTeacher($teacher_id)
    {
        $sql = "UPDATE user_teacher SET is_disabled = 1 WHERE id = :teacher_id";

        return Yii::$app->db->createCommand($sql)
                ->bindValue(':teacher_id',$teacher_id)
                ->execute();
    }
    
    /**
     * @param $teacher_id
     * @return mixed
     * @author sjy
     * 修改老师薪资信息
     */
    public function updateTeacherSalaryInfo($teacher_id,$allduty_award_rates,$absence_punished_rates,$allduty_time,$absence_time,$salaryAfter,$salaryTime,$salary_25,$salary_45,$salary_50,$hour_time){
        $sql = "UPDATE user_teacher SET  allduty_award_rates = :allduty_award_rates, absence_punished_rates = :absence_punished_rates, allduty_time = :allduty_time , "
                 ."absence_time = :absence_time, salary_after = :salary_after ,salary_time = :salary_time ,class_hour_first = :class_hour_first,class_hour_second = :class_hour_second,  "
                 ."class_hour_third = :class_hour_third, hour_time = :hour_time   "
                 . " WHERE id = :id";
      
        $result= Yii::$app->db->createCommand($sql)
                ->bindValues([
                    ':id' => $teacher_id,
                    ':allduty_award_rates' => $allduty_award_rates,
                    ':absence_punished_rates' => $absence_punished_rates,
                    ':allduty_time' => $allduty_time,
                    ':absence_time' =>  $absence_time,
                    ':salary_after' =>  $salaryAfter,
                    ':salary_time' =>  $salaryTime,
                    ':class_hour_first' =>  $salary_25,
                    ':class_hour_second' =>  $salary_45,
                    ':class_hour_third' => $salary_50,
                    ':hour_time' =>  $hour_time
                ])->execute();

        if($result>=1)
        {
            return 1;
        }else{
            return 0;
        }
    }
        
    /**
     * @param $teacher_id
     * @return mixed
     * @author sjy
     * 删除老师乐器
     */
    public function deleteTeacherInstrument($teacher_id){
         $sql = "DELETE FROM user_teacher_instrument WHERE user_id = :user_id";

         return Yii::$app->db->createCommand($sql)
            ->bindValue(':user_id',$teacher_id)
            ->execute();
    }
    
    /**
     * @param $teacher_id
     * @return mixed
     * @author sjy
     * 添加老师乐器
     */
    public function addTeacherInstrument($userId, $instrument, $type, $level){
      
        $sql = "INSERT INTO user_teacher_instrument(user_id,instrument_id,type,level) VALUES(:userId,:instrument,:type,:level)";

        return Yii::$app->db->createCommand($sql)
                        ->bindValues([':userId' => $userId, ':instrument' => $instrument, ':type' => $type, ':level' => $level])
                        ->execute();
    }

    /**
     * @param $teacher_id
     * @return mixed
     * @author yuhao
     * 添加老师乐器到teacher_instrument表
     */
    public function addTeacherInstrumentIntoTeacherInstrument($teacher_id, $instrument_id, $grade, $level){

        $sql = "INSERT INTO teacher_instrument(teacher_id,instrument_id,grade,level) VALUES(:teacher_id,:instrument_id,:grade,:level)";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':teacher_id' => $teacher_id, ':instrument_id' => $instrument_id, ':grade' => $grade, ':level' => $level])
            ->execute();
    }

    /**
     * @param $teacher_id
     * @return mixed
     * @author sjy
     * 添加老师基本信息
     */
    public function addTeacher($employedtime,$name,$show_name,$mobile,$password,$gender,$placeId,$workType,$token,$is_test,$work_new,$type_new,$salary_rate,$teacher_type,$school_id,$openid = '',$style,$sounds,$teacher_experience,$responsible_school=""){

         $sql = "INSERT INTO user_teacher(employedtime,nick,show_name,mobile,password,teacher_level,role,gender,place_id,work_type,time_created,accessToken,is_formal,is_test,work_id,type,salary_rate,teacher_type,school_id,open_id,style,sounds,teacher_experience,responsible_school)"
              . " VALUES(:employedtime,:name,:show_name,:mobile,:password,:teacherLevel,1,:gender,:placeId,:work_type,:time_created,:accessToken,:is_formal,:is_test,:work_id,:type_new,:salary_rate,:teacher_type,:school_id,:openid,:style,:sounds,:teacher_experience,:responsible_school)";

        Yii::$app->db->createCommand($sql)
                    ->bindValues([
                       ':employedtime' => $employedtime,
                        ':name' => $name,
                        ':show_name' => $show_name,
                        ':mobile' => $mobile,
                        ':password' => $password,
                        ':teacherLevel' => 1,
                        ':gender' => $gender,
                        ':placeId' => $placeId,
                        ':work_type' => $workType,
                        ':time_created' => time(),
                        ':accessToken' => $token,
                        ':is_formal' => 0,
                        ':is_test' => $is_test,
                        ':work_id' => $work_new,
                        ':type_new' => $type_new,
                        ':salary_rate' => $salary_rate,
                        ':teacher_type' => $teacher_type,
                        ':school_id' => $school_id,
                        ':openid' => $openid,
                        ':style' => $style,
                        ':sounds' => $sounds,
                        ':teacher_experience' => $teacher_experience,
                        ':responsible_school' => $responsible_school
                     ])
                    ->execute();

        return Yii::$app->db->getLastInsertID();
    }

    /**
     * @param $teacher_id
     * @return mixed
     * @author sjy
     * 更新老师基本信息
     */
    public function updateTeacher($employedtime,$teacher_id,$name,$show_name,$mobile,$password,$gender,$placeId,$workType,$token,$is_test,$work_new,$type_new,$salary_rate,$teacher_type,$school_id,$style,$sounds,$teacher_experience,$responsible_school=""){

        $sql = "UPDATE user_teacher SET  nick = :nick, show_name = :show_name, mobile = :mobile, gender = :gender , "
                 ."place_id = :place_id, work_type = :work_type, employedtime = :employedtime, is_test = :is_test, work_id = :work_id, type = :type_new, salary_rate = :salary_rate,"
                 ." teacher_type = :teacher_type, school_id = :school_id, style = :style, sounds = :sounds, teacher_experience = :teacher_experience, responsible_school = :responsible_school"

                 . " WHERE id = :id";

      
        $result= Yii::$app->db->createCommand($sql)
                ->bindValues([
                    ':employedtime' =>  $employedtime,
                    ':id' => $teacher_id,
                    ':nick' => $name,
                    ':show_name' => $show_name,
                    ':mobile' => $mobile,
                    ':gender' => $gender,
                    ':place_id' => $placeId,
                    ':work_type' =>  $workType,
                    ':is_test' => $is_test,
                    ':work_id' => $work_new,
                    ':type_new' => $type_new,
                    ':salary_rate' => $salary_rate,
                    ':teacher_type' => $teacher_type,
                    ':school_id' => $school_id,
                    ':style' => $style,
                    ':sounds' => $sounds,
                    ':teacher_experience' => $teacher_experience,
                    ':responsible_school' => $responsible_school
                ])->execute();
        
        if($result >= 1)
        {
            return 1;
        }else{
            return 0;
        }
    }

    public function editTeacherHead($teacher_id, $key)
    {
        $sql = "UPDATE user_teacher set head_icon = :key WHERE id = :teacher_id";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':key' => $key,':teacher_id' => $teacher_id])
            ->execute();
    }

    public function editTeacherPush($timeStart,$timeEnd,$teacher_id)
    {
        $sql = "UPDATE teacher_basic_salary set is_push = 1 WHERE teacher_id = :teacher_id AND time_day>=:timeStart AND time_day<:timeEnd";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':teacher_id' => $teacher_id,':timeStart' => $timeStart,':timeEnd' => $timeEnd,])
            ->execute();
    }

    public function doEditResume($teacher_id, $resume)
    {
        $sql = "UPDATE user_teacher SET resume = :resume, time_updated = :time_update WHERE id = :teacher_id";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':resume'=>$resume,':time_update'=>time(),':teacher_id'=>$teacher_id])
            ->execute();
    }

    public function doEditShowName($teacher_id, $show_name)
    {
        $sql = "UPDATE user_teacher SET show_name = :show_name, time_updated = :time_update WHERE id = :teacher_id";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':show_name'=>$show_name,':time_update'=>time(),':teacher_id'=>$teacher_id])
            ->execute();
    }

    public function doEditFormal($teacher_id)
    {
        $sql = "UPDATE user_teacher SET is_formal = 1 WHERE id = :teacher_id";

        return Yii::$app->db->createCommand($sql)
                ->bindValue(':teacher_id',$teacher_id)
                ->execute();
    }

    public function doAddTeacherWechatAcc($teacher_id,$openid,$head,$nick,$name,$subscribe_time)
    {
        $sql = "INSERT INTO teacher_wechat_acc(teacher_id,openid,head,nick,name,subscribe_time) "
            . "VALUES (:teacher_id, :openid, :head, :nick, :name, :subscribe_time)";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':teacher_id' => $teacher_id,
                'openid'    => $openid,
                'head'  => $head,
                'nick'  => $nick,
                ':name' => $name,
                ':subscribe_time' => $subscribe_time,
            ])
            ->execute();
    }
}