<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午3:18
 */
namespace common\logics\salary;


use Yii;
use yii\base\Object;
use yii\data\Pagination;
use common\widgets\BinaryDecimal;

class OthersLogic extends Object implements IOthers
{

    /** @var  \common\sources\read\salary\OthersAccess $ROthersAccess */
    private $ROthersAccess;
    /** @var  \common\sources\write\salary\OthersAccess $WOthersAccess */
    private $WOthersAccess;

    public function init()
    {
     $this->ROthersAccess = Yii::$container->get('ROthersAccess');
     $this->WOthersAccess = Yii::$container->get('WOthersAccess');
     parent::init();
    }

    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $mobile
     * @return mixed
     * @sjy 
     * 获取自定义惩罚列表个数
     */
    public function definedawardcount($timeStart,$timeEnd,$mobile)
    {
         $count = $this->ROthersAccess->definedawardcount($timeStart,$timeEnd,$mobile);
         return $count;
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
    public function  definedawardlist($timeStart,$timeEnd,$mobile,$pagenum)
    {
          $list= $this->ROthersAccess->definedawardlist($timeStart,$timeEnd,$mobile,$pagenum);
          return $list;   
    }
    
    /**
     * @return mixed
     * @sjy 
     * 获取在职老师姓名，id
     */ 
    public function getteachername(){
        $list= $this->ROthersAccess->getteachername();
        return $list;
    }

       
    
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
            $list= $this->WOthersAccess->insertdefinedrecored($createtime,$teacher_id,$intermark,$outermark,$awardtype,$title,$money);
            return $list; 
     } 
}