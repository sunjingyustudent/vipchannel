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
use yii\helpers\VarDumper;
use common\widgets\PhpExcel;


class RewardLogic extends Object implements IReward
{
    /** @var  \common\sources\read\teacher\TeacherAccess $WTeacherAccess */
    private $WTeacherAccess;
    /** @var  \common\sources\read\student\StudentAccess $RStudentAccess */
    private $RStudentAccess;
    /** @var  \common\sources\read\salary\RewardAccess $RRewardAccess */
    private $RRewardAccess;
    /** @var  \common\sources\write\salary\RewardAccess $WRewardAccess */
    private $WRewardAccess;
    /** @var  \common\sources\read\teacher\TeacherAccess $RTeacherAccess */
    private $RTeacherAccess;
    /** @var  \common\sources\read\salary\BasepayAccess $RBasepayAccess */
    private $RBasepayAccess;
    /** @var  \common\sources\write\salary\BasepayAccess $WBasepayAccess */
    private $WBasepayAccess;
    /** @var  \common\sources\read\teacher\RuleAccess $RRuleAccess */
    private $RRuleAccess;
    /** @var  \common\sources\read\classes\ClassAccess $RClassAccess */
    private $RClassAccess;
    private $salaryCompute;

    public function init()
    {
        $this->RRewardAccess = Yii::$container->get('RRewardAccess');
        $this->WRewardAccess = Yii::$container->get('WRewardAccess');
        $this->RTeacherAccess = Yii::$container->get('RTeacherAccess');
        $this->WTeacherAccess = Yii::$container->get('WTeacherAccess');
        $this->RStudentAccess = Yii::$container->get('RStudentAccess');
        $this->RClassAccess = Yii::$container->get('RClassAccess');
        $this->RRuleAccess = Yii::$container->get('RRuleAccess');
        $this->salaryCompute = Yii::$container->get('salaryCompute');
        $this->WBasepayAccess = Yii::$container->get('WBasepayAccess');
        $this->RBasepayAccess = Yii::$container->get('RBasepayAccess');
        parent::init();
    }

    public function operationExperiencePage($time, $filter, $type)
    {
        $timeStart = strtotime($time);

        $filter = trim($filter);

        $timeEnd = strtotime('+1 month',$timeStart);

        $teacher_ids = $this->RRewardAccess->getExToBuyCount($filter, $timeStart, $timeEnd, $type);

        $count = count($teacher_ids);

        return $count;
    }

    public function operationExperienceList($time, $filter, $type, $page_num)
    {
        $timeStart = strtotime($time);
        $filter = trim($filter);

        $timeEnd = strtotime("+1 month", $timeStart);

        $teacher_list = $this->RRewardAccess->getExToBuyList($filter, $timeStart, $timeEnd, $type, $page_num);

        if (!empty($type))
        {
            foreach ($teacher_list as $k => &$teacher) {
                $is_exit = $this->RRewardAccess->rewardRecordIsExit($teacher['teacher_id'], $timeStart, 4);

                if (empty($is_exit['prefix'])) {
                    $teacher_list[$k]['money'] = '-' . $is_exit['money'];
                } else {
                    $teacher_list[$k]['money'] = '+' . $is_exit['money'];
                }

                $teacher_list[$k]['reward_name'] = $is_exit['reward_name'];

            }

        }

        return $teacher_list;
    }

    public function operationExportExperienceList($time, $filter, $type)
    {
        $timeStart = strtotime($time);

        $filter = trim($filter);

        $timeEnd = strtotime('+1 month',$timeStart);

        $reward_list = $this->RRewardAccess->exportExToBuyList($filter, $timeStart, $timeEnd, $type);

        $reward_list_new = array();

        if (!empty($reward_list))
        {
            foreach ($reward_list as $key => $row)
            {
                $reward_list_new[$key] = array(
                    'id' => '',
                    'nick' => '',
                    'ex_to_buy' => ''
                );
                $reward_list_new[$key]['id'] = $row['teacher_id'];

                $reward_list_new[$key]['nick'] = $row['nick'];

                $reward_list_new[$key]['ex_to_buy'] = $row['ex_to_buy'];
            }
        }

        $title = '月体验课成单奖励列表';

        $fileName = $title . '.xls';

        $columnMap = array('老师ID','老师姓名','买单数');

        PhpExcel::getExcel($title, $reward_list_new, $columnMap, $fileName, $is_excel = 1, $width=10);


    }

    public function doAddRewardEx($request)
    {
        $teacher_id = $request['teacher_id'];
        $month_time = strtotime($request['timeDay']);
        $reward_id = $request['reward_id'];
        $text = $request['text'];
        $mark = $request['mark'];
        $createtime = $month_time;
        $money = $request['money'];

        $prefix = $this->RRuleAccess->getPrefixByRewardId($reward_id);

        $last_id = $this->WBasepayAccess->doAddRewardRecord($teacher_id, $reward_id, $month_time, $text, $mark, 4, $prefix, $money, $createtime);

        if(!empty($last_id))
        {
            return '';
        }else{
            return '添加失败！';
        }
    }


    /**
     * 月爽约页面
     */
    public function monthMissPage($time, $type, $filter)
    {
        $timeStart = strtotime($time);

        $timeEnd = strtotime('+1 month',$timeStart);

        $teacher_cancel_count = $this->RRewardAccess->getTeacherCancelCount($timeStart, $timeEnd, $filter, $type);

        return count($teacher_cancel_count);
    }

    /**
     * 月爽约老师列表
     */
    public function monthMissList($time, $type, $filter, $page_num)
    {
        $timeStart = strtotime($time);

        $timeEnd = strtotime('+1 month',$timeStart);

        $teacher_cancel_list = $this->RRewardAccess->getTeacherCancelList($timeStart, $timeEnd, $filter, $type, $page_num);

        if(!empty($type))
        {
            foreach ($teacher_cancel_list as $key => &$row)
            {

                $is_exit = $this->RRewardAccess->rewardRecordIsExit($row['teacher_id'], $timeStart, 6);

                if (empty($is_exit['prefix'])) {
                    $row['money'] = '-' . $is_exit['money'];
                } else {
                    $row['money'] = '+' . $is_exit['money'];
                }

                $row['reward_name'] = $is_exit['reward_name'];

            }
        }

        return $teacher_cancel_list;
    }

    public function doAddRewardCancel($request)
    {
        $teacher_id = $request['teacher_id'];
        $month_time = strtotime($request['month_time']);
        $reward_id = $request['reward_id'];
        $text = $request['text'];
        $mark = $request['mark'];
        $createtime=$month_time;
        $money = $request['money'];

        $prefix = $this->RRuleAccess->getPrefixByRewardId($reward_id);

        $last_id = $this->WBasepayAccess->doAddRewardRecord($teacher_id, $reward_id, $month_time, $text, $mark, 6, $prefix, $money, $createtime);

        if(!empty($last_id))
        {
            return '';
        }else{
            return '添加失败！';
        }
    }

    public function overtimePage($month, $filter, $type)
    {
        $timeStart = strtotime($month);

        $timeEnd = strtotime('+1 month',$timeStart);

        $filter = trim($filter);

        if (empty($type))
        {
            $overtime_ids = $this->RRewardAccess->getOvertimeNoDealCount($filter, $timeStart, $timeEnd);
        }else{
            $overtime_ids = $this->RRewardAccess->getOvertimeDealCount($filter, $timeStart, $timeEnd);
        }

        $count = count($overtime_ids);

        return $count;
    }

    public function overtimeList($month, $filter, $type, $page_num)
    {
        $timeStart = strtotime($month);

        $timeEnd = strtotime('+1 month',$timeStart);

        $filter = trim($filter);

        if (empty($type))
        {
            $overtime_list = $this->RRewardAccess->getOvertimeNoDelList($filter, $timeStart, $timeEnd, $page_num);
        }else{
            $overtime_list = $this->RRewardAccess->getOvertimeDealList($filter, $timeStart, $timeEnd, $page_num);

            foreach ($overtime_list as $k=>$item)
            {

                $is_exit = $this->RRewardAccess->rewardRecordIsExit($item['teacher_id'], $timeStart, 5);

                if(empty($is_exit['prefix']))
                {
                    $overtime_list[$k]['money'] = '-'.$is_exit['money'];
                }else{
                    $overtime_list[$k]['money'] = '+'.$is_exit['money'];
                }

                $overtime_list[$k]['reward_name'] = $is_exit['reward_name'];

            }
        }


        return $overtime_list;
    }

    public function rewardOvertimeTag()
    {
        $reward_info = $this->RRuleAccess->getRewardName(3);

        $data = array('reward_info' => $reward_info);

        return $data;
    }

    public function doAddRewardOvertime($request)
    {
        $teacher_id = $request['teacher_id'];
        $month_time = strtotime($request['month']);
//        $reward_id = $request['reward_id'];
        $text = $request['text'];
        $mark = $request['mark'];
        $long = $request['over_time'];
        $createtime = $month_time;
        $money = trim($request['money']);


//        $prefix = $this->RRuleAccess->getPrefixByRewardId($reward_id);

        $last_id = $this->WBasepayAccess->doAddRewardRecord($teacher_id, 0, $month_time, $text, $mark, 5, 1, $money, $createtime);

        if(!empty($last_id))
        {
            return '';
        }else{
            return '添加失败！';
        }
    }

    public function otherOvertimePage($month, $filter, $type)
    {
        $timeStart = strtotime($month);

        $timeEnd = strtotime('+1 month',$timeStart);

        $filter = trim($filter);

        if (empty($type))
        {
            $overtime_ids = $this->RRewardAccess->getOtherOvertimeNoDealCount($filter, $timeStart, $timeEnd);
        }else{
            $overtime_ids = $this->RRewardAccess->getOtherOvertimeDealCount($filter, $timeStart, $timeEnd);
        }

        $count = count($overtime_ids);

        return $count;
    }

    public function otherOvertimeList($month, $filter, $type, $page_num)
    {
        $timeStart = strtotime($month);

        $timeEnd = strtotime('+1 month',$timeStart);

        $filter = trim($filter);

        if (empty($type))
        {
            $overtime_list = $this->RRewardAccess->getOtherOvertimeNoDelList($filter, $timeStart, $timeEnd, $page_num);
        }else{
            $overtime_list = $this->RRewardAccess->getOtherOvertimeDealList($filter, $timeStart, $timeEnd, $page_num);

            foreach ($overtime_list as $k=>$item)
            {

                $is_exit = $this->RRewardAccess->rewardRecordIsExit($item['teacher_id'], $timeStart, 10);

                if(empty($is_exit['prefix']))
                {
                    $overtime_list[$k]['money'] = '-'.$is_exit['money'];
                }else{
                    $overtime_list[$k]['money'] = '+'.$is_exit['money'];
                }

                $overtime_list[$k]['reward_name'] = $is_exit['reward_name'];

            }
        }

        return $overtime_list;
    }

    public function doAddRewardOtherOvertime($request)
    {
        $teacher_id = $request['teacher_id'];
        $month_time = strtotime($request['month']);
//        $reward_id = $request['reward_id'];
        $text = $request['text'];
        $mark = $request['mark'];
        $long = $request['over_time'];
        $createtime = $month_time;
        $money = trim($request['money']);

//        $prefix = $this->RRuleAccess->getPrefixByRewardId($reward_id);

        $last_id = $this->WBasepayAccess->doAddRewardRecord($teacher_id, 0, $month_time, $text, $mark, 10, 1, $money, $createtime);

        if(!empty($last_id))
        {
            return '';
        }else{
            return '添加失败！';
        }
    }

    public function festivalPage($time, $filter, $type)
    {
        $time_start = strtotime($time);
        $time_end = $time_start + 86400;
        $filter = trim($filter);

        $count = $this->RRewardAccess->getFestivalCount($time_start, $time_end, $filter, $type);

        $count = empty($count) ? 0 : $count;

        return $count;
    }

    public function festivalList($time, $filter, $type, $page_num)
    {
        $time_start = strtotime($time);
        $time_end = $time_start + 86400;
        $filter = trim($filter);

        return $this->RRewardAccess->getFestivalList($time_start, $time_end, $filter, $type, $page_num);
    }

    public function doAddRewardFestival($request)
    {
        $teacher_id = $request['teacher_id'];
        $timeDay = strtotime($request['timeDay']);
        $reward_id = $request['reward_id'];
        $text = $request['text'];
        $mark = $request['mark'];
        $createtime = time();
        $money = $request['money'];

        $prefix = $this->RRuleAccess->getPrefixByRewardId($reward_id);

        $last_id = $this->WBasepayAccess->doAddRewardRecord($teacher_id, $reward_id, $timeDay, $text, $mark, 11, $prefix, $money, $createtime);

        if(!empty($last_id))
        {
            return '';
        }else{
            return '添加失败！';
        }

    }


    /**
     * @param $time
     * @param $filter
     * @param $type
     * @return mixed
     * @author sjy
     * 获取全勤奖count
     */
     public function attendancePageCount($time, $filter, $type)
     {
         $timeStart = strtotime($time);
         $timeEnd = strtotime("+1 month", $timeStart);

         $attendancePageCount = $this->RRewardAccess->getAttendanceTeacherCount($timeStart, $timeEnd, $filter, $type);
         
         return count($attendancePageCount);
     }


    /**
     * @param $time
     * @param $filter
     * @param $type
     * @return mixed
     * @author sjy
     * 获取全勤奖list
     */

    public function attendancePageList($time, $filter, $page_num,$type)
    {
         $timeStart = strtotime($time);
         $timeEnd = strtotime("+1 month", $timeStart);

         $attendancePageList=$this->RRewardAccess->getAttendanceTeacherList($timeStart, $timeEnd, $filter, $type, $page_num);

         return $attendancePageList;
    }


    /**
     * @param $time
     * @param $filter
     * @param $type
     * @return mixed
     * @author sjy
     * 处理全勤奖
     */
    public function getAttendanceDeal($request)
    {

        $timeStart = strtotime($request['time']);
        $teacher_id = $request['teacher_id'];
        $text = "全勤奖励";
        $remark = "全勤奖励";
        $type = "12";
        $prefix = "1";
        $createtime = $timeStart;
        $money = $request['money'];

        $transaction = Yii::$app->db->beginTransaction();

        try{

            $this->WRewardAccess->updateAttendance($teacher_id, $timeStart, 1);

            $this->WBasepayAccess->doAddRewardRecord($teacher_id, 0, $timeStart, $text, $remark, $type, $prefix, $money, $createtime);

            $transaction->commit();

            return 1;

        }catch (Exception $e) {
            $transaction->rollBack();

            return 0;
        }

    }
    
    /**
     * @param $time
     * @param $filter
     * @param $type
     * @return mixed
     * @author sjy
     * 处理好评奖励count
     */
    public function getGoodEvaluationCount($time, $filter, $type)
    {
         $timeStart = strtotime($time);
         $timeEnd = strtotime("+1 month", $timeStart);

         $count = $this->RRewardAccess->getGoodEvaluationCount($timeStart, $timeEnd, $filter, $type);

         return $count;
    } 
    
    
    /**
     * @param $time
     * @param $filter
     * @param $page_num
     * @param $type
     * @return mixed
     * @author sjy
     * 处理好评奖励list
     */
    public function getGoodEvaluationList($time, $filter, $page_num, $type)
    {
        $timeStart = strtotime($time);
        $timeEnd = strtotime("+1 month", $timeStart);

        $getGoodEvaluationList = $this->RRewardAccess->getGoodEvaluationList($timeStart, $timeEnd, $filter, $type, $page_num);

        if (!empty($type))
        {
            foreach ($getGoodEvaluationList as $k => &$teacher)
            {
                $is_exit = $this->RRewardAccess->rewardRecordIsExit($teacher['teacher_id'], $timeStart, 13);

                if (empty($is_exit['prefix'])) {
                    $getGoodEvaluationList[$k]['money'] = '-' . $is_exit['money'];
                } else {
                    $getGoodEvaluationList[$k]['money'] = '+' . $is_exit['money'];
                }

                $getGoodEvaluationList[$k]['reward_name'] = $is_exit['reward_name'];

            }

        }

         return $getGoodEvaluationList;
    }

    public function doAddRewardGoodEval($request)
    {
        $teacher_id = $request['teacher_id'];
        $month_time = strtotime($request['time']);
        $reward_id = 0;
        $text = $request['text'];
        $mark = $request['mark'];
        $createtime = $month_time;
        $prefix = 1;
        $money = $request['money'];

        $last_id = $this->WBasepayAccess->doAddRewardRecord($teacher_id, $reward_id, $month_time, $text, $mark, 13, $prefix, $money, $createtime);

        if(!empty($last_id))
        {
            return '';
        }else{
            return '添加失败！';
        }

    }

    public function getExperienceMoney($teacher_id, $teacher_buy, $reward_id)
    {
        $money = $this->salaryCompute->calculateSalary($teacher_id, '', '', '', $reward_id);

        if ($money == '未收录')
        {
            return '当前薪资未收录';
        }

        return $money;
    }

    public function getTripCount($type)
    {
        $ids = $this->RRewardAccess->getTripCount($type);

        $count = count($ids);

        return $count;
    }

    public function getTripList($type, $page_num)
    {
        return $this->RRewardAccess->getTripList($type, $page_num);
    }

    public function addTrip($teacher_id)
    {
        $re = $this->WRewardAccess->addTrip($teacher_id);

        return $re;
    }
    
    
    /**
     * @param 
     * @return mixed
     * @author sjy
     * 获取获得复购奖励的个数
     */
    public function getRepurchaseCount($time, $filter, $type)
    {
         return $this->RRewardAccess->getRepurchaseCount($time, $filter, $type);
    }
    
    /**
     * @param 
     * @return mixed
     * @author sjy
     * 获取获得复购奖励list
     */
    public function actionRepurchaseList($time,$filter,$page_num,$type){
        return $this->RRewardAccess->actionRepurchaseList($time,$filter,$page_num,$type);
    }
    
    /**
     * @param 
     * @return mixed
     * @author sjy
     * 获取给当前学生上过课的所有老师
     */
    public function teacherList($uid,$time_pay){
        return $this->RRewardAccess->teacherList($uid,$time_pay);
    }
    
    /**
     * @param 
     * @return mixed
     * @author sjy
     *处理复购奖励
     */
    public function repurchaseDeal($request){
        $price = $request['price'];
        $radio = $request['radio'];
        $studentid = $request['studentid'];
        $orderid = $request['orderid'];
        $time_pay = strtotime($request['timepay']);
        $month_time=strtotime($request['repurchase-date']);
        $reward_id=0;
        $mark="复购奖励";
        $text="复购奖励";
        $money=$radio*$price*0.01;
        $createtime=time();
        
        
        $transaction = Yii::$app->db->beginTransaction();
        try{   
        $result=$this->RRewardAccess->teacherList($studentid,$time_pay);
        if(!empty($result)){
                   foreach ($result as $k =>$item )
                   {
                       $teacher_id= $item["teacher_id"];
                       $this->WBasepayAccess->doAddRewardRecord($teacher_id, 0, $month_time, $text, $mark, 0, 0, $money, $createtime);
                   }
        }else{
            return "没有老师上过课";
        }
        
        $tag=$this->WRewardAccess->changeOrderStatus($orderid,$money);
        $transaction->commit();
        }catch (Exception $e) {
             $transaction->rollBack();
             return "操作失败";
        }
        
        return "";
    }






}