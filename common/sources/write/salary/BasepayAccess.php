<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:06
 */

namespace common\sources\write\salary;

use common\models\music\TeacherSalary;
use Yii;

Class BasepayAccess implements IBasepayAccess {

    /**
     *  插入reward_record表
     *  @param teacher_id  int
     *  @param time        str
     *  @param money       int
     *  @param type        int
     */
    public function doAddRewardRecord($teacher_id, $reward_id, $month_time, $text, $remark, $type, $prefix, $money,$createtime)
    {
        $sql = "INSERT INTO reward_record(teacher_id, reward_id, month_time, text, remark, type, prefix, money, time_created) VALUES(:teacher_id, :reward_id, :month_time, :text, :remark, :type, :prefix, :money, :time_created)";

        Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':teacher_id'       => $teacher_id,
                ':reward_id'        => $reward_id,
                ':month_time'       => $month_time,
                ':text'             => $text,
                ':remark'           => $remark,
                ':type'             => $type,
                ':prefix'           => $prefix,
                ':money'            => $money,
                ':time_created'     => $createtime
            ])
            ->execute();

        return Yii::$app->db->getLastInsertID();
    }

    public function updateIsPublish($timeStart, $timeEnd)
    {
       $sql = "UPDATE teacher_salary SET is_publish = 1 WHERE time_day >= :timeStart AND time_day < :timeEnd";

       return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':timeStart' => $timeStart,
                ':timeEnd' => $timeEnd
            ])->execute();
    }
    
    /**
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author sjy
     * 添加一条薪资改变记录
     */
    public function addSalaryLog($workType,$kefuId,$teacher_id,$salarybefore,$salaryAfter,$salary_25,$salary_45,$salary_50,$salaryTime,$hour_time,$allduty_award_rates,$absence_punished_rates,$allduty_time,$absence_time){
         $sql = "INSERT INTO salary_change_log(work_type,user_id,teacher_id,salary_before,salary_after,class_hour_first,class_hour_second,class_hour_third,salary_time,hour_time,time_created,allduty_award_rates,allduty_time,absence_punished_rates,absence_time )"
            . " VALUES(:work_type,:user_id,:teacher_id,:salary_before,:salary_after,:salary_25,:salary_45,:salary_50,:salary_time,:hour_time,:time_created,:allduty_award_rates,:allduty_time,:absence_punished_rates,:absence_time )";

        Yii::$app->db->createCommand($sql)
                    ->bindValues([
                        ':user_id' => $kefuId,
                        ':teacher_id' => $teacher_id,
                       ':work_type' => $workType,
                        ':salary_before' => $salarybefore,
                        ':salary_after' => $salaryAfter,
                        ':salary_25' => $salary_25,
                        ':salary_45' => $salary_45,
                        ':salary_50' => $salary_50,
                        ':salary_time' => $salaryTime,
                        ':hour_time' => $hour_time,
                        ':time_created' => time(),
                         ':allduty_award_rates' => $allduty_award_rates,
                         ':allduty_time' => $allduty_time,
                         ':absence_punished_rates' => $absence_punished_rates,
                         ':absence_time' => $absence_time
                    ])
                    ->execute();

        return Yii::$app->db->getLastInsertID();
    }

    public function deleteInstrumentSalary($teacher_id)
    {
       $sql = "DELETE FROM teacher_instrument WHERE teacher_id = :teacher_id";

       return Yii::$app->db->createCommand($sql)
                ->bindValue('teacher_id', $teacher_id)
                ->execute();
    }

    public function addInstrumentSalary($teacher_id, $instrument_id, $grade, $level, $hour_first, $hour_second, $hour_third, $salary)
    {
        $sql = "INSERT INTO teacher_instrument(teacher_id, instrument_id, grade, `level`, hour_first, `hour_second`, hour_third, salary)"
            . " VALUES(:teacher_id, :instrument_id, :grade, :level, :hour_first, :hour_second, :hour_third, :salary)";
        return Yii::$app->db->createCommand($sql)
                    ->bindValues([
                        ':teacher_id' => $teacher_id,
                        ':instrument_id' => $instrument_id,
                        ':grade' => $grade,
                        ':level' => $level,
                        ':hour_first' => $hour_first,
                        ':hour_second' => $hour_second,
                        ':hour_third' => $hour_third,
                        ':salary' => $salary
                    ])->execute();
    }

    public function addInstrumentSalaryLog($teacher_id, $instrument_id, $grade, $level, $hour_first, $hour_second, $hour_third, $salary)
    {
        $sql = "INSERT INTO teacher_instrument_log(teacher_id, instrument_id, grade, `level`, hour_first, `hour_second`, hour_third, salary, time_day, time_created)"
            . " VALUES(:teacher_id, :instrument_id, :grade, :level, :hour_first, :hour_second, :hour_third, :salary, :time_day, :time_created)";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':teacher_id' => $teacher_id,
                ':instrument_id' => $instrument_id,
                ':grade' => $grade,
                ':level' => $level,
                ':hour_first' => $hour_first,
                ':hour_second' => $hour_second,
                ':hour_third' => $hour_third,
                ':salary' => $salary,
                ':time_day' => strtotime(date('Y-m-d',time())),
                ':time_created' => time()
            ])->execute();
    }

}