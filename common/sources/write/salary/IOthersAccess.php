<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:05
 */

namespace common\sources\write\salary;

interface IOthersAccess {
  
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

    /**
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 更新自定义奖惩记录为已发布
     */
  public function updateIsPublish($timeStart, $timeEnd);
}