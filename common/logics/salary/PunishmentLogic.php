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

class PunishmentLogic extends Object implements IPunishment
{
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
    /** @var  \common\sources\read\salary\WorkhourAccess $RWorkhourAccess */
    private $RWorkhourAccess;
    /** @var  \common\sources\read\teacher\RestAccess $RRestAccess */
    private $RRestAccess;
    /** @var  \common\sources\write\teacher\RestAccess $WRestAccess */
    private $WRestAccess;
    /** @var  \common\sources\read\teacher\RuleAccess $RRuleAccess */
    private $RRuleAccess;
    /** @var  \common\sources\read\salary\PunishmentAccess $RPunishmentAccess */
    private $RPunishmentAccess;
    /** @var  \common\sources\read\complain\ComplainAccess $RComplainAccess */
    private $RComplainAccess;
    /** @var  \common\sources\write\complain\ComplainAccess $WComplainAccess */
    private $WComplainAccess;
    /** @var  \common\sources\read\classes\ClassAccess $RClassAccess */
    private $RClassAccess;
    /** @var  \common\sources\write\salary\PunishmentAccess $WPunishmentAccess */
    private $WPunishmentAccess;
    private $salaryCompute;



    public function init()
    {
        $this->RRewardAccess = Yii::$container->get('RRewardAccess');
        $this->WRewardAccess = Yii::$container->get('WRewardAccess');
        $this->RTeacherAccess = Yii::$container->get('RTeacherAccess');
        $this->RBasepayAccess = Yii::$container->get('RBasepayAccess');
        $this->WBasepayAccess = Yii::$container->get('WBasepayAccess');
        $this->RWorkhourAccess = Yii::$container->get('RWorkhourAccess');
        $this->RRestAccess = Yii::$container->get('RRestAccess');
        $this->RRuleAccess = Yii::$container->get('RRuleAccess');
        $this->salaryCompute = Yii::$container->get('salaryCompute');
        $this->WRestAccess = Yii::$container->get('WRestAccess');
        $this->RPunishmentAccess = Yii::$container->get('RPunishmentAccess');
        $this->RComplainAccess = Yii::$container->get('RComplainAccess');
        $this->WComplainAccess = Yii::$container->get('WComplainAccess');
        $this->RClassAccess = Yii::$container->get('RClassAccess');
         $this->WPunishmentAccess = Yii::$container->get('WPunishmentAccess');
        parent::init();
    }

    public function rewardRestPage($timeStart, $timeEnd, $filter, $type)
    {
        $count = $this->RPunishmentAccess->getRewardRestCount($timeStart,$timeEnd,$filter,$type);

        return $count;
    }

    public function rewardRestList($timeStart, $timeEnd, $filter, $type, $page_num)
    {
        $restInfo = $this->RPunishmentAccess->getRewardRestInfo($timeStart,$timeEnd,$filter,$type,$page_num);

        foreach ($restInfo as &$item) {

            $subQuery = $this->RTeacherAccess->getInstrumentByTeacherId($item['teacher_id']);

            $temp = "";
            foreach ($subQuery as $key) {
                $level = $key['type'] == 0 ? "内:" . $key['level'] : "外:" . $this->instrumentType($key['level']);
                $temp = $temp . '<p>' . $key["name"] . "-" . $level . "</p>";
            }

            $item["level"] = $temp;

            $teacherRewardInfo = $this->RRuleAccess->getRewardById($item['reward_id']);

            $item['reward_name'] = $teacherRewardInfo['name'];

            if(empty($item['prefix']))
            {
                $item['money'] = '-'.$item['money'];
            }else{
                $item['money'] = '+'.$item['money'];
            }
        }

        return $restInfo;
    }

    public function rewardRestTag($rest_id)
    {
        $time_info = $this->RRestAccess->getRestById($rest_id);

        $time_string = date('Y-m-d',$time_info['time_day']).' '.date('H:i',$time_info['time_start']).'-'.date('H:i',$time_info['time_end']);

        $reward_info = $this->RRuleAccess->getRewardName(1);

        $time = $time_info['time_day'];

        $data = array('time_string' => $time_string, 'reward_info' => $reward_info, 'time' => $time);

        return $data;
    }

    public function doAddRewardRest($request)
    {
        $transaction = Yii::$app->db->beginTransaction();

        try{

            $createtime = time();

            $prefix = $this->RRuleAccess->getPrefixByRewardId($request['reward_id']);

            $lastId = $this->WBasepayAccess->doAddRewardRecord($request['teacher_id'], $request['reward_id'], '', $request['text'], $request['mark'], 1 , $prefix, $request['money'], $createtime);

            $this->WRestAccess->updateRestTag($request['rest_id'], $lastId);

            $transaction->commit();

            return '';

        }catch (Exception $e) {
            $transaction->rollBack();

            return '添加失败！';
        }
    }

    public function doAddRewardComplain($request)
    {

        $transaction = Yii::$app->db->beginTransaction();

        try{

            $class_info = $this->RClassAccess->getClassInfoById($request['class_id']);

            $createtime = time();

            $prefix = $this->RRuleAccess->getPrefixByRewardId($request['reward_id']);

            $lastId = $this->WBasepayAccess->doAddRewardRecord($class_info['teacher_id'],$request['reward_id'],'',$request['text'],$request['mark'], 2, $prefix, $request['money'], $createtime);

            $this->WComplainAccess->updateComplainRewardRecordId($request['complain_id'], $lastId);

            $transaction->commit();

            return '';

        }catch (Exception $e) {
            $transaction->rollBack();

            return '添加失败！';
        }

    }

    public function noRewardComplain($request)
    {
        $re = $this->WComplainAccess->updateComplainRewardRecordId($request['complain_id'],0);

        return $re;
    }
    
    
     /**
     * @param $timeStart
     * @param $timeEnd
     * @param $filter
     * @param $type
     * @return mixed
     * @author sjy
     * 获取缺勤条数
     */
    public function absencecount($time,$filter,$type)
    {
        $timeStart = strtotime($time);//当前时间月初
        $timeEnd = strtotime("+1 month", $timeStart);//当前时间月末

        $teacherrest = $this->RPunishmentAccess->getAbsenceCount($timeStart, $timeEnd, $filter, $type);

        return count($teacherrest);
    }
    
    
    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $filter
     * @param $type
     * @return mixed
     * @author sjy
     * 获取缺勤列表
     */
    public function absencelist($time,$filter,$type,$page_num)
    {
        
        $timeStart = strtotime($time);//当前时间月初
        $timeEnd = strtotime("+1 month", $timeStart);//当前时间月末
        
        $teacherrest=$this->RPunishmentAccess->getAbsenceList($timeStart,$timeEnd,$filter,$type,$page_num);

        return $teacherrest;
    }
    

     /**
     * @param $teacher_id
     * @return mixed
     * @author sjy
     * 添加缺勤处理
     */
    public function addPunishment($request)
    {

        $timeStart = strtotime($request['time']);
        $reward_id = "0";
        $month_time = $timeStart;
        $text = "缺勤惩罚";
        $remark = "缺勤惩罚";
        $type = "9";
        $prefix = "0";
        $createtime = $timeStart;
        $money = $request['money'];

        $transaction = Yii::$app->db->beginTransaction();

        try{

            $this->WRewardAccess->updateAttendance($request['teacher_id'], $timeStart, 0);

            $this->WBasepayAccess->doAddRewardRecord($request['teacher_id'], $reward_id, $month_time, $text, $remark, $type, $prefix, $money, $createtime);

            $transaction->commit();

            return 1;

        }catch (Exception $e) {
            $transaction->rollBack();

            return 0;
        }
       
    }

    public function badEvaluationCount($time, $filter, $type)
    {
        $timeStart = strtotime($time);
        $timeEnd = strtotime("+1 month", $timeStart);

        $count = $this->RPunishmentAccess->getBadEvaluationCount($timeStart, $timeEnd, $filter, $type);

        return $count;
    }

    public function badEvaluationList($time, $filter, $type, $page_num)
    {
        $timeStart = strtotime($time);
        $timeEnd = strtotime("+1 month", $timeStart);

        $list = $this->RPunishmentAccess->getBadEvaluationList($timeStart, $timeEnd, $filter, $type, $page_num);

        if (!empty($type))
        {
            foreach ($list as $k => &$teacher)
            {
                $is_exit = $this->RRewardAccess->rewardRecordIsExit($teacher['teacher_id'], $timeStart, 14);

                if (empty($is_exit['prefix'])) {
                    $list[$k]['money'] = '-' . $is_exit['money'];
                } else {
                    $list[$k]['money'] = '+' . $is_exit['money'];
                }

                $list[$k]['reward_name'] = $is_exit['reward_name'];

            }

        }

        return $list;
    }

    public function addRewardBadEvaluation($request)
    {
        $teacher_id = $request['teacher_id'];
        $month_time = strtotime($request['time']);
        $reward_id = 0;
        $text = $request['text'];
        $mark = $request['mark'];
        $createtime = $month_time;
        $prefix = 0;
        $money = $request['money'];

        $last_id = $this->WBasepayAccess->doAddRewardRecord($teacher_id, $reward_id, $month_time, $text, $mark, 14, $prefix, $money, $createtime);

        if(!empty($last_id))
        {
            return '';
        }else{
            return '添加失败！';
        }

    }

    public function getAbsenteeismCount($time, $filter, $type)
    {
        $timeStart = strtotime($time);
        $timeEnd = strtotime("+1 month",$timeStart);

        return $this->RPunishmentAccess->getAbsenteeismCount($timeStart, $timeEnd, $filter, $type);
    }

    public function getAbsenteeismList($time, $filter, $type, $page_num)
    {
        $timeStart = strtotime($time);
        $timeEnd = strtotime("+1 month",$timeStart);

        $list = $this->RPunishmentAccess->getAbsenteeismList($timeStart, $timeEnd, $filter, $type, $page_num);

        if (!empty($type))
        {
            foreach ($list as $k => &$teacher)
            {
                $is_exit = $this->RRewardAccess->rewardRecordIsExit($teacher['teacher_id'], $timeStart, 15);

                if (empty($is_exit['prefix'])) {
                    $list[$k]['money'] = '-' . $is_exit['money'];
                } else {
                    $list[$k]['money'] = '+' . $is_exit['money'];
                }

            }
        }

        return $list;
    }

    public function addRewardAbsenteeism($request)
    {
        $teacher_id = $request['teacher_id'];
        $month_time = strtotime($request['time']);
        $reward_id = 0;
        $text = $request['text'];
        $mark = $request['mark'];
        $createtime = $month_time;
        $prefix = 0;
        $money = $request['money'];

        $last_id = $this->WBasepayAccess->doAddRewardRecord($teacher_id, $reward_id, $month_time, $text, $mark, 15, $prefix, $money, $createtime);

        if(!empty($last_id))
        {
            return '';
        }else{
            return '添加失败！';
        }
    }

    public function getAbsenteeismTrainCount($time, $filter, $type)
    {
        $timeStart = strtotime($time);
        $timeEnd = strtotime("+1 month",$timeStart);

        return $this->RPunishmentAccess->getAbsenteeismTrainCount($timeStart, $timeEnd, $filter, $type);
    }

    public function getAbsenteeismTrainList($time, $filter, $type, $page_num)
    {
        $timeStart = strtotime($time);
        $timeEnd = strtotime("+1 month",$timeStart);

        $list = $this->RPunishmentAccess->getAbsenteeismTrainList($timeStart, $timeEnd, $filter, $type, $page_num);

        if (!empty($type))
        {
            foreach ($list as $k => &$teacher)
            {
                $is_exit = $this->RRewardAccess->rewardRecordIsExit($teacher['teacher_id'], $timeStart, 16);

                if (empty($is_exit['prefix'])) {
                    $list[$k]['money'] = '-' . $is_exit['money'];
                } else {
                    $list[$k]['money'] = '+' . $is_exit['money'];
                }

            }
        }

        return $list;
    }

    public function addRewardAbsenteeismTrain($request)
    {
        $teacher_id = $request['teacher_id'];
        $month_time = strtotime($request['time']);
        $reward_id = 0;
        $text = $request['text'];
        $mark = $request['mark'];
        $createtime = $month_time;
        $prefix = 0;
        $money = $request['money'];

        $last_id = $this->WBasepayAccess->doAddRewardRecord($teacher_id, $reward_id, $month_time, $text, $mark, 16, $prefix, $money, $createtime);

        if(!empty($last_id))
        {
            return '';
        }else{
            return '添加失败！';
        }
    }

    private function instrumentType($level)
    {
        if ($level == 1)
        {
            return '启蒙';
        }elseif ($level == 2)
        {
            return '初级';
        }elseif ($level == 3)
        {
            return '中级';
        }else{
            return '高级';
        }
    }
}