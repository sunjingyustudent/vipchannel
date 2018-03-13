<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:06
 */

namespace common\sources\read\teacher;

use common\models\music\BasePlace;
use common\models\music\TeacherGradeRuleLog;
use common\models\music\TeacherRewardRule;
use common\models\music\TeacherWorkType;
use common\models\music\RewardRuleId;

use Yii;
use common\models\music\TeacherGradeRule ;

Class RuleAccess implements IRuleAccess {

    public function getRewardName($type)
    {
        $teacher_reward = TeacherRewardRule::find()
            ->select('id, name, text')
            ->where('status = 0');

        if ($type == 1)
        {
            $teacher_reward ->andWhere('rule_id = 1');
        }

        if ($type == 5)
        {
            $teacher_reward ->andWhere('rule_id = 5');
        }

        if ($type == 4)
        {
            $teacher_reward ->andWhere('rule_id = 4');
        }

        if ($type == 6)
        {
            $teacher_reward ->andWhere('rule_id = 6');
        }

        return $teacher_reward ->asArray()->all();
    }

    public function getRewardById($reward_id)
    {
        return TeacherRewardRule::find()
            ->where(['id' => $reward_id])
            ->asArray()
            ->one();
    }

    public function getPrefixByRewardId($reward_id)
    {
        return TeacherRewardRule::find()
            ->select('type')
            ->where('id = :id',[':id' => $reward_id])
            ->scalar();
    }

    public function getWorkInfo()
    {
        return TeacherWorkType::find()
            ->Where('is_deleted = 0')
            ->asArray()
            ->all();
    }

    
    public function getPlaceInfo()
    {
        return BasePlace::find()
            ->asArray()
            ->all();
    }
    
    public function getPlaceById($place_id)
    {
        return BasePlace::find()
            ->where('id = :id',[':id'=>$place_id])
            ->asArray()
            ->one();
    }
    
    /*
     * @return mixed
     * @author xl
     * 获取老师奖惩规则详细信息
     */
     public function getRewardInfo()
    {
        return TeacherRewardRule::find()
            ->alias('r')
            ->select('r.*,i.name as rule_name')
            ->leftJoin('reward_rule_id as i','i.rule_id = r.rule_id')
            ->orderBy('id asc')
            ->asArray()
            ->all();
    }
    
    /**
     * @return mixed
     * @author xl
     * 获取奖惩规则类型
     */
     public function getRuleInfo()
    {
        return RewardRuleId::find()
            ->select('rule_id,name')
            ->asArray()
            ->all();
    }

    public function getWorkTypeInfo()
    {
        return TeacherWorkType::find()
            ->Where('is_deleted = 0')
            ->asArray()
            ->all();
    }

    public function getWorkTypeById($work_id)
    {
        return TeacherWorkType::find()
            ->where('id = :id',[':id'=>$work_id])
            ->one();
    }

    public function getPlaceList()
    {
        return BasePlace::find()
                    ->select('id, name')
                    ->asArray()
                    ->all();
    }

    public function countGradeRule($request)
    {
       $teacherGradeRule =  TeacherGradeRule::find()
           ->where("teacher_type=:teacher_type and school_id=:school_id and grade_id=:grade_id and level=:level",[":grade_id"=>$request['grade_id'] , "level"=>$request['level'] , "teacher_type"=>$request['teacher_type'] , "school_id"=>$request['school_id']]);
       if(isset($request['grade_rule_id']) && !empty($request['grade_rule_id'])){
           $teacherGradeRule->andWhere("id != :grade_rule_id" , [":grade_rule_id" => $request['grade_rule_id']]);
       }
       return  $teacherGradeRule->count();
    }

    public function getGradeRuleList()
    {
        return TeacherGradeRule::find()
            ->alias('r')
            ->select('r.*,i.name as school_name')
            ->leftJoin('school as i','i.id = r.school_id')
            ->asArray()->all() ;
    }

    public function getGradeRuleInfo($grade_rule_id)
    {
        return TeacherGradeRule::find()
            ->where("id=:grade_rule_id",[":grade_rule_id" => $grade_rule_id])
            ->asArray()->one();
    }

    public function showGradeRuleLog($record_id)
    {
        return TeacherGradeRuleLog::find()
                        ->alias('r')
                        ->select('r.*,i.name as school_name')
                        ->leftJoin('school as i','r.school_id = i.id ')
                        ->where(['r.grade_rule_id' => $record_id])
                        ->asArray()
                        ->all();
    }
}