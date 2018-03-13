<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:06
 */

namespace common\sources\write\salary;

use Yii;

Class OthersAccess implements IOthersAccess {

    /**
     * @param $createtime
     * @param $teacher_id
     * @param $intermark
     * @param $outermark
     * @param $awardtype
     * @param $title
     * @param $money 
     * @return mixed
     * @sjy 
     * 添加自定义奖惩
     */
   public function insertdefinedrecored($createtime,$teacher_id,$intermark,$outermark,$awardtype,$title,$money){
       //插入数据
       
       $sql = "INSERT INTO teacher_defined_award(createtime, teacher_id, intermark, outermark, awardtype, title, money) VALUES(:createtime, :teacher_id, :intermark, :outermark, :awardtype, :title, :money)";
      $createtime=strtotime($createtime);
       
        Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':createtime'       => $createtime,
                ':teacher_id'        => $teacher_id,
                ':intermark'       => $intermark,
                ':outermark'             => $outermark,
                ':awardtype'           => $awardtype,
                ':title'             => $title,
                ':money'           => $money
            ])
            ->execute();

        return Yii::$app->db->getLastInsertID();
   }

   public function updateIsPublish($timeStart, $timeEnd)
   {
       $sql = "UPDATE teacher_defined_award SET is_publish = 1 WHERE createtime >= :timeStart AND createtime < :timeEnd";

       return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':timeStart' => $timeStart,
                ':timeEnd' => $timeEnd
            ])->execute();
   }
}