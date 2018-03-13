<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:06
 */

namespace common\sources\read\salary;
use common\models\music\TeacherDefinedAward;
use yii\base\Object;
use Yii;

Class OthersAccess implements IOthersAccess {

    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $mobile
     * @return mixed
     * @sjy 
     * 获取自定义惩罚列表个数
     */
    public function definedawardcount($timeStart,$timeEnd,$mobile){


         $sql = "SELECT  count(d.teacher_id) FROM teacher_defined_award as d"
            . " LEFT JOIN user_teacher as u ON u.id=d.teacher_id"
            . " WHERE u.is_disabled=0 AND d.createtime>= :timeStart AND d.createtime< :timeEnd"
            . (empty($mobile) ? "" : " AND (u.mobile like '%{$mobile}%' OR u.nick like '%{$mobile}%' )");
        

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':timeStart' => $timeStart, ':timeEnd' => $timeEnd])
            ->queryScalar();
        

    }
    
    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $pagenum
     * @param $mobile
     * @return mixed
     * @sjy 
     * 获取自定义惩罚列表
     */
    public function  definedawardlist($timeStart,$timeEnd,$mobile,$pagenum){
         $sql = "SELECT  d.teacher_id,d.intermark,u.nick,u.mobile,d.outermark,d.awardtype,d.money,d.createtime,d.title FROM teacher_defined_award as d"
                . " LEFT JOIN user_teacher as u ON u.id=d.teacher_id"
                . " WHERE u.is_disabled=0 AND d.createtime>= :timeStart AND d.createtime< :timeEnd"
                . (empty($mobile) ? "" : " AND (u.mobile like '%{$mobile}%' OR u.nick like '%{$mobile}%')")
                . " limit ".(($pagenum - 1) * 10). ", 10";

        $timeStart=strtotime($timeStart);
        $timeEnd=strtotime($timeEnd)+86400;

       return Yii::$app->db->createCommand($sql)
                ->bindValues([':timeStart' => $timeStart, ':timeEnd' => $timeEnd])
                ->queryAll();

    }

    
    /**
     * @return mixed
     * @sjy 
     * 获取在职老师姓名，id
     */ 
    public function getteachername(){
        $sql = "select id,nick from user_teacher where is_disabled=0 and is_formal=1";
        return Yii::$app->db->createCommand($sql) 
                ->queryAll();
    }

    public function getOthersMoney($teacher_id, $timeStart, $timeEnd)
    {
        return  TeacherDefinedAward::find()
                        ->select(' SUM(CASE  WHEN awardtype = 1 THEN money END) AS reward,
                                   SUM(CASE  WHEN awardtype = 0 THEN money END) AS punishment
                                ')
                        ->where('createtime >=:timeStart AND createtime <=:timeEnd',[':timeStart' => $timeStart,':timeEnd' => $timeEnd])
                        ->andWhere(['teacher_id' => $teacher_id])
                        ->asArray()
                        ->one();
    }

    public function getOtherRewardList($teacher_id, $timeStart, $timeEnd)
    {
        return TeacherDefinedAward::find()
                    ->select('outermark, money, title, createtime')
                    ->where(['teacher_id' => $teacher_id,'awardtype' => 1])
                    ->andWhere('createtime >= :timeStart',[':timeStart' => $timeStart])
                    ->andWhere('createtime < :timeEnd',[':timeEnd' => $timeEnd])
                    ->asArray()
                    ->all();
    }

    public function getOtherPunishmentList($teacher_id, $timeStart, $timeEnd)
    {
        return TeacherDefinedAward::find()
            ->select('outermark, money, title, createtime')
            ->where(['teacher_id' => $teacher_id,'awardtype' => 0])
            ->andWhere('createtime >= :timeStart',[':timeStart' => $timeStart])
            ->andWhere('createtime < :timeEnd',[':timeEnd' => $timeEnd])
            ->asArray()
            ->all();
    }

    public function getOtherRewardTotal($timeStart, $timeEnd)
    {
        return TeacherDefinedAward::find()
            ->select('sum(money)')
            ->where(['awardtype' => 1])
            ->andWhere('createtime >= :timeStart',[':timeStart' => $timeStart])
            ->andWhere('createtime < :timeEnd',[':timeEnd' => $timeEnd])
            ->scalar();
    }

    public function getOtherPunishmentTotal($timeStart, $timeEnd)
    {
        return TeacherDefinedAward::find()
            ->select('sum(money)')
            ->where(['awardtype' => 0])
            ->andWhere('createtime >= :timeStart',[':timeStart' => $timeStart])
            ->andWhere('createtime < :timeEnd',[':timeEnd' => $timeEnd])
            ->scalar();
    }


}