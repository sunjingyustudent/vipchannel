<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午3:17
 */

namespace common\logics\salary;

interface IOthers {
    
    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $mobile
     * @return mixed
     * @sjy 
     * 获取自定义惩罚列表个数
     */
    public function definedawardcount($timeStart,$timeEnd,$mobile);
    
    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $pagenum
     * @param $mobile
     * @return mixed
     * @sjy 
     * 获取自定义惩罚列表
     */
    public function  definedawardlist($timeStart,$timeEnd,$mobile,$pagenum);
    
    
    /**
     * @return mixed
     * @sjy 
     * 获取在职老师姓名，id
     */ 
    public function getteachername();
      
      
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
    public function insertdefinedrecored($createtime,$teacher_id,$intermark,$outermark,$awardtype,$title,$money);
    
 
}