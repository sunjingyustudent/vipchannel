<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午3:18
 */
namespace common\logics\teacher;

use WebSocket\Exception;
use Yii;
use yii\base\Object;
use yii\data\Pagination;
use common\widgets\BinaryDecimal;

class RuleLogic extends Object implements IRule
{
    /** @var  \common\sources\read\teacher\RuleAccess $RRuleAccess */
    private $RRuleAccess;
    /** @var  \common\sources\write\teacher\RuleAccess $WRuleAccess */
    private $WRuleAccess;

    public function init()
    {
        /** @var  \common\sources\write\teacher\RuleAccess WRuleAccess */
        $this->RRuleAccess = Yii::$container->get('RRuleAccess');
        $this->WRuleAccess = Yii::$container->get('WRuleAccess');
        

        parent::init();
    }

    public function getRewardName($type)
    {
        return $this->RRuleAccess->getRewardName($type);
    }

    public function getRewardInfoById($reward_id)
    {
        return $this->RRuleAccess->getRewardById($reward_id);


    }

    public function getPrefixByRewardId($reward_id)
    {
        return $this->RRuleAccess->getPrefixByRewardId($reward_id);
    }

    public function getWorkInfo()
    {
        return $this->RRuleAccess->getWorkInfo();
    }

    public function getPlaceInfo()
    {
       // $placeInfo = $this->RTeacherAccess->getPlaceInfo();
        $placeInfo = $this->RRuleAccess->getPlaceInfo();

        return $placeInfo ? $placeInfo : [];
    }

      public function doAddPlace($request)
    {
        $name = $request['name'];
        $charge_ratio = $request['charge_ratio'];
        $class_hour_first = $request['class_hour_first'];
        $class_hour_second = $request['class_hour_second'];
        $class_hour_third = $request['class_hour_third'];

        if($name == '')
        {
            return '基地名不能为空';
        }

        if($charge_ratio == '')
        {
            return '扣款倍数不能为空';
        }

        if(!is_numeric($charge_ratio))
        {
            return '扣款倍数格式不正确';
        }

        if($class_hour_first == '')
        {
            return '25分钟课时费不能为空';
        }

        if(!is_numeric($class_hour_first))
        {
            return '25分钟课时费格式不正确';
        }

        if($class_hour_second == '')
        {
            return '45分钟课时费不能为空';
        }

        if(!is_numeric($class_hour_second))
        {
            return '45分钟课时费格式不正确';
        }

        if($class_hour_third == '')
        {
            return '50分钟课时费不能为空';
        }

        if(!is_numeric($class_hour_third))
        {
            return '50分钟课时费格式不正确';
        }

        $re = $this->WRuleAccess->doAddPlace($request);

        if($re)
        {
            return '';
        }else{
            return '添加失败！请联系技术人员';
        }
    }
    
    public function getPlaceById($place_id)
    {
       // $place_info = $this->RTeacherAccess->getPlaceById($place_id);
        $place_info = $this->RRuleAccess->getPlaceById($place_id);
        
        return $place_info ? $place_info : [];
    }
    
     public function doEditPlace($request)
    {
        $name = $request['name'];
        $charge_ratio = $request['charge_ratio'];
        $class_hour_first = $request['class_hour_first'];
        $class_hour_second = $request['class_hour_second'];
        $class_hour_third = $request['class_hour_third'];

        if($name == '')
        {
            return '基地名不能为空';
        }

        if($charge_ratio == '')
        {
            return '扣款倍数不能为空';
        }

        if(!is_numeric($charge_ratio))
        {
            return '扣款倍数格式不正确';
        }

        if($class_hour_first == '')
        {
            return '25分钟课时费不能为空';
        }

        if(!is_numeric($class_hour_first))
        {
            return '25分钟课时费格式不正确';
        }

        if($class_hour_second == '')
        {
            return '45分钟课时费不能为空';
        }

        if(!is_numeric($class_hour_second))
        {
            return '45分钟课时费格式不正确';
        }

        if($class_hour_third == '')
        {
            return '50分钟课时费不能为空';
        }

        if(!is_numeric($class_hour_third))
        {
            return '50分钟课时费格式不正确';
        }

        $re = $this->WRuleAccess->doEditPlace($request);
        if($re == 1)
        {
            return '';
        }else{
            return'修改失败！请联系技术人员';
        }

    }
    
    public function rewardIndex()
    {
       // $rewardInfo = $this->RTeacherAccess->getRewardInfo();
        $rewardInfo = $this->RRuleAccess->getRewardInfo();
        return $rewardInfo;
    }
    
    /**
     * @return mixed
     * @author xl
     * 添加奖惩规则Modal
     */
    public function addReward()
    {
       //$ruleInfo = $this->RTeacherAccess->getRuleInfo();
        $ruleInfo = $this->RRuleAccess->getRuleInfo();

        return $ruleInfo;
    }
    
    
    /**
     * @param $request
     * @return mixed
     * @author xl
     * 添加奖惩规则操作
     */
     public function doAddReward($request)
    {
        if($request['name'] == '')
        {
            return '规则名不能为空';
        }

        if($request['num'] == '' || !is_numeric($request['num']))
        {
            return '规则类型配置错误';
        }

        if($request['text'] == '')
        {
            return '系统默认模板不能为空';
        }

        //$re = $this->WTeacherAccess->doAddReward($request);
        $re = $this->WRuleAccess->doAddReward($request);

        if($re)
        {
            return '';
        }else{
            return '添加失败！请联系技术人员';
        }
    }
    
    /**
     * @return mixed
     * @author xl
     * 编辑奖惩规则Modal
     */
    public function editReward($reward_id)
    {
        $reward_info = $this->RRuleAccess->getRewardById($reward_id);

        $rule_info = $this->RRuleAccess->getRuleInfo();

        $data = array('reward_info' => $reward_info, 'rule_info' => $rule_info);

        return $data;
    }
    
    
    /**
     * @param $request
     * @return mixed
     * @author xl
     * 编辑奖惩规则操作
     */
    public function doEditReward($request)
    {
        if($request['name'] == '')
        {
            return '规则名不能为空';
        }

        if($request['num'] == '' || !is_numeric($request['num']))
        {
            return '规则类型配置错误';
        }

        if($request['text'] == '')
        {
            return '系统默认模板不能为空';
        }

        //$re = $this->WTeacherAccess->doEditReward($request);
         $re = $this->WRuleAccess->doEditReward($request);

        if($re)
        {
            return '';
        }else{
            return '修改失败！请联系技术人员';
        }
    }

    public function getWorkTypeInfo()
    {
        return $this->RRuleAccess->getWorkTypeInfo();
    }

    public function doAddWorkType($request)
    {
        $name = $request['name'];
        $time_start = $request['time_start'];
        $time_end = $request['time_end'];
        $instruction = $request['instruction'];

        if(empty($name))
        {
            return json_encode(array('error'=>'工作名称不能为空'));
        }

        if(strtotime($time_start) >= strtotime($time_end))
        {
            return json_encode(array('error'=>'开始时间不能大于结束时间'));
        }

        $result = $this->WRuleAccess->addWorkType($name, $time_start, $time_end, $instruction);

        if($result)
        {
            return json_encode(array('error'=>''));
        }else{
            return json_encode(array('error'=>'添加失败,请联系技术人员'));
        }
    }

    public function getWorkTypeById($work_id)
    {
        return $this->RRuleAccess->getWorkTypeById($work_id);
    }

    public function doEditWorkType($request)
    {
        $work_id = $request['work_id'];
        $name = $request['name'];
        $time_start = $request['time_start'];
        $time_end = $request['time_end'];
        $instruction = $request['instruction'];

        if(empty($name))
        {
            return json_encode(array('error'=>'工作名称不能为空'));
        }

        if(strtotime($time_start) >= strtotime($time_end))
        {
            return json_encode(array('error'=>'开始时间不能大于结束时间'));
        }

        $result = $this->WRuleAccess->editWorkType($work_id, $name, $time_start, $time_end, $instruction);

//        if($result){
            return json_encode(array('error'=>''));
//        }else{
//            return json_encode(array('error'=>'修改失败,请联系技术人员'));
//        }
    }

    public function deleteWorkType($work_id)
    {
        $re = $this->WRuleAccess->deleteWorkType($work_id);

        if (!empty($re))
        {
            return 1;
        }else{
            return 0;
        }
    }

    public function addAbsence($request)
    {
        $absence_time = strtotime($request['absence_time']);
        $teacher_id = $request['teacher_id'];
        $reason = $request['reason'];

        return $this->WRuleAccess->addAbsence($teacher_id, $absence_time, $reason,1);
    }

    public function addAbsenceTrain($request)
    {
        $absence_time = strtotime($request['absence_time']);
        $teacher_id = $request['teacher_id'];
        $reason = $request['reason'];

        return $this->WRuleAccess->addAbsence($teacher_id, $absence_time, $reason,2);
    }

    public function getPlaceCount()
    {
        $list = $this->RRuleAccess->getPlaceList();

        return array('error' => 0, 'data' => count($list));
    }

    public function addGradeRule($request)
    {
        $returnData = array("error" => 0 , "data" => array()) ;
        $countGradeRule = $this->RRuleAccess->countGradeRule($request);
        if($countGradeRule > 0)
        {
            $returnData['error'] = "等级存在，请确认" ;
           return $returnData ;
        }
        $transaction = Yii::$app->db->beginTransaction();
        try
        {
            $guild_rule_id = $this->WRuleAccess->addGradeRule($request);
            $request['grade_rule_id'] = $guild_rule_id ;
            $guild_rule_log_id = $this->WRuleAccess->addGradeRuleLog($request);
            $list = ["guild_rule_id"=>$guild_rule_id , "guild_rule_log_id"=>$guild_rule_log_id] ;
            $transaction->commit() ;
        }catch (Exception $e) {
            $transaction->rollBack();
            $returnData['error'] = "保存失败，请重新保存" ;
            return  $returnData ;
        }
        $returnData['data'] = $list ;
        return $returnData ;
    }

    public function getGradeRuleList()
    {
        $returnData = array("error" => 0 , "data" => array()) ;
        $returnData['data'] = $this->RRuleAccess->getGradeRuleList();
        return $returnData ;
    }

    public function getGradeRuleInfo($grade_rule_id)
    {
        $returnData = array("error" => 0 , "data" => array());
        $returnData['data'] = $this->RRuleAccess->getGradeRuleInfo($grade_rule_id) ;
        return $returnData ;
    }

    public function editGradeRule($request)
    {
        $returnData = array("error" => 0 , "data" => array()) ;
        $transaction = Yii::$app->db->beginTransaction();
        $countGradeRule = $this->RRuleAccess->countGradeRule($request);
        if($countGradeRule > 0)
        {
            $returnData['error'] = "等级存在，请确认" ;
            return $returnData ;
        }
        try{
            $guild_rule_id = $this->WRuleAccess->editGradeRule($request) ;
            $guild_rule_log_id = $this->WRuleAccess->addGradeRuleLog($request);
            $list = ["guild_rule_id"=>$guild_rule_id , "guild_rule_log_id"=>$guild_rule_log_id] ;
            $transaction->commit() ;
        }catch (Exception $e){
            $transaction->rollBack() ;
            $returnData['error'] = "修改失败，请重新保存" ;
            return  $returnData ;
        }
        $returnData['data'] = $list ;
        return $returnData ;
    }

    public function showGradeRuleLog($record_id)
    {
        $list = $this->RRuleAccess->showGradeRuleLog($record_id);

        return array('error' => 0, 'data' => $list);
    }
}