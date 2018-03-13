<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:06
 */

namespace common\sources\write\teacher;
use Yii;

Class RuleAccess implements IRuleAccess
{
    public function doAddPlace($request)
    {
        $sql = "INSERT INTO base_place(name, charge_ratio, time_created, class_hour_first, class_hour_second, class_hour_third) VALUES(:name, :charge_ratio, :time_created, :class_hour_first, :class_hour_second, :class_hour_third)";


        Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':name' => $request['name'],
                ':charge_ratio' => $request['charge_ratio'],
                ':class_hour_first' =>$request['class_hour_first'],
                ':class_hour_second' => $request['class_hour_second'],
                ':class_hour_third' => $request['class_hour_third'],
                ':time_created' => time()
                ])
                ->execute();

        return Yii::$app->db->getLastInsertID();
    }
    
     public function doEditPlace($request)
    {
        $sql = "UPDATE base_place SET name = :name, charge_ratio = :charge_ratio, class_hour_first = :class_hour_first, class_hour_second = :class_hour_second, "
                 ."class_hour_third = :class_hour_third, time_updated = :time_updated  "
                 . " WHERE id = :id";

      
        $result= Yii::$app->db->createCommand($sql)
                ->bindValues([
                    ':id' => $request['place_id'],
                    ':name' => $request['name'],
                    ':charge_ratio' => $request['charge_ratio'],
                    ':class_hour_first' => $request['class_hour_first'],
                    ':class_hour_second' => $request['class_hour_second'],
                    ':class_hour_third' => $request['class_hour_third'],
                    ':time_updated' =>  time()
                  
                ])->execute();
        

        if($result>=1)
        {
            return 1;
        }else{
            return 0;
        }
    }
    
    
      public function doAddReward($request)
    {
        $sql = "INSERT INTO teacher_reward_rule(name,rule_id,num,status,type,text,time_created) VALUES (:name,:rule_id,:num,:status,:type,:text,:time_created)";

        return Yii::$app->db->createCommand($sql)
                            ->bindValues([
                                    ':name' => $request['name'],
                                    ':rule_id' => $request['rule_id'],
                                    ':num' => $request['num'],
                                    ':status' => $request['status'],
                                    ':type' => $request['type'],
                                    ':text' => $request['text'],
                                    ':time_created' => time()
                                ])->execute();
    }
    
    
    public function doEditReward($request)
    {
        $sql = "UPDATE teacher_reward_rule SET name = :name, rule_id = :rule_id, num = :num, status = :status, type = :type, text = :text, time_updated = :time_updated "
               . " WHERE id = :id";

        $result = Yii::$app->db->createCommand($sql)
                ->bindValues([
                    ':id' => $request['rid'],
                    ':name' => $request['name'],
                    ':rule_id' => $request['rule_id'],
                    ':num' => $request['num'],
                    ':status' => $request['status'],
                    ':type' => $request['type'],
                    ':text' => $request['text'],
                    ':time_updated' => time()
                ])->execute();
        

        if($result >= 1)
        {
            return 1;
        }else{
            return 0;
        }
    }

    public function addWorkType($name, $time_start, $time_end, $instruction)
    {
        $sql = "INSERT INTO teacher_work_type(name, time_start, time_end, instruction, time_created) VALUES(:name, :time_start, :time_end, :instruction, :time_created)";

        Yii::$app->db->createCommand($sql)
            ->bindValues([':name' => $name, ':time_start' => $time_start, ':time_end' => $time_end, ':instruction' => $instruction, ':time_created' => time()])
            ->execute();

        return Yii::$app->db->getLastInsertID();
    }

    public function editWorkType($work_id, $name, $time_start, $time_end, $instruction)
    {
        $sql = "UPDATE teacher_work_type SET name = :name, time_start = :time_start, time_end = :time_end, instruction = :instruction, time_updated = :time_updated WHERE id = :work_id";

        return Yii::$app->db->createCommand($sql)
                ->bindValues([
                    'work_id' => $work_id,
                    ':name' => $name,
                    ':time_start' => $time_start,
                    ':time_end' => $time_end,
                    ':instruction' => $instruction,
                    ':time_updated' => $instruction
                ])->execute();

    }

    public function deleteWorkType($work_id)
    {
        $sql = "UPDATE teacher_work_type SET is_deleted = 1 WHERE id = :work_id";

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':work_id',$work_id)
            ->execute();
    }

    public function addAbsence($teacher_id, $absence_time, $reason, $type)
    {
        $sql = "INSERT INTO teacher_absence(teacher_id,reason,time_day,type) VALUES (:teacher_id,:reason,:time_day,:type)";

        Yii::$app->db->createCommand($sql)
                ->bindValues([
                    ':teacher_id' => $teacher_id,
                    ':reason' => $reason,
                    ':time_day' => $absence_time,
                    ':type' => $type
                    ])->execute();

        return Yii::$app->db->getLastInsertID();
    }
    public function  addGradeRule($request)
    {
        $sql = "INSERT INTO teacher_grade_rule
              (grade_id,level,salary_after,class_hour_first,class_hour_second,class_hour_third,salary_time,create_time,teacher_type,school_id)
              VALUES 
              (:grade_id,:level,:salary_after,:class_hour_first,:class_hour_second,:class_hour_third,:salary_time,:create_time,:teacher_type,:school_id)";

        Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':grade_id' => $request['grade_id'],
                ':level' => $request['level'],
                ':salary_after' => $request['salary_after'],
                ':class_hour_first' => $request['class_hour_first'],
                ':class_hour_second' => $request['class_hour_second'],
                ':class_hour_third' => $request['class_hour_third'],
                ':salary_time' => $request['salary_time'],
                ':create_time' => time(),
                ':teacher_type' => $request['teacher_type'],
                ':school_id' => $request['school_id'],
            ])->execute();
        return Yii::$app->db->getLastInsertID();
    }
    public function  addGradeRuleLog($request){
        $sql = "INSERT INTO teacher_grade_rule_log
              (grade_rule_id,grade_id,level,salary_after,class_hour_first,class_hour_second,class_hour_third,salary_time,create_time,teacher_type,school_id)
              VALUES 
              (:grade_rule_id,:grade_id,:level,:salary_after,:class_hour_first,:class_hour_second,:class_hour_third,:salary_time,:create_time,:teacher_type,:school_id)";

        Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':grade_rule_id' =>$request['grade_rule_id'] ,
                ':grade_id' => $request['grade_id'],
                ':level' => $request['level'],
                ':salary_after' => $request['salary_after'],
                ':class_hour_first' => $request['class_hour_first'],
                ':class_hour_second' => $request['class_hour_second'],
                ':class_hour_third' => $request['class_hour_third'],
                ':salary_time' => $request['salary_time'],
                ':create_time' => time(),
                ':teacher_type' => $request['teacher_type'],
                ':school_id' => $request['school_id'],
            ])->execute();
        return Yii::$app->db->getLastInsertID();
    }
    public function editGradeRule($request)
    {
        $sql = "UPDATE teacher_grade_rule SET 
              grade_id = :grade_id ,
              level = :level ,
              salary_after = :salary_after ,
              class_hour_first = :class_hour_first ,
              class_hour_second = :class_hour_second ,
              class_hour_third = :class_hour_third ,
              salary_time = :salary_time,
              teacher_type = :teacher_type,
              school_id = :school_id
              where id = :grade_rule_id
              ";
       return  Yii::$app->db->createCommand($sql)
                ->bindValues([
                    ':grade_rule_id' =>$request['grade_rule_id'] ,
                    ':grade_id' => $request['grade_id'],
                    ':level' => $request['level'],
                    ':salary_after' => $request['salary_after'],
                    ':class_hour_first' => $request['class_hour_first'],
                    ':class_hour_second' => $request['class_hour_second'],
                    ':class_hour_third' => $request['class_hour_third'],
                    ':salary_time' => $request['salary_time'],
                    ':teacher_type' => $request['teacher_type'],
                    ':school_id' => $request['school_id'],
                    ':grade_rule_id' => $request['grade_rule_id']
                ])->execute();
    }
}