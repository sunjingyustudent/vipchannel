<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 16/12/15
 * Time: 17:49
 */
namespace common\logics\teacher;

use common\models\music\TeacherWechatAcc;
use common\services\QiniuService;
use Yii;
use yii\base\Object;
use yii\data\Pagination;
use common\widgets\BinaryDecimal;
use common\widgets\Request;


class TeacherLogic extends Object implements ITeacher
{

    /** @var  \common\sources\read\teacher\TeacherAccess $RTeacherAccess */
    private $RTeacherAccess;
    /** @var  \common\sources\write\teacher\TeacherAccess $RTeacherAccess */
    private $WTeacherAccess;
    /** @var  \common\sources\read\student\StudentAccess $RStudentAccess */
    private $RStudentAccess;
    /** @var  \common\sources\read\classes\ClassAccess $RClassAccess */
    private $RClassAccess;
    /** @var  \common\sources\read\teacher\RuleAccess $RRuleAccess */
    private $RRuleAccess;
    /** @var  \common\sources\write\salary\BasepayAccess $WBasepayAccess */
    private $WBasepayAccess;
    /** @var  \common\sources\read\salary\BasepayAccess $RBasepayAccess */
    private $RBasepayAccess;
    /** @var  \common\sources\write\teacher\WorktimeAccess $WWorktimeAccess */
    private $WWorktimeAccess;


    public function init()
    {
        $this->RTeacherAccess = Yii::$container->get('RTeacherAccess');
        $this->WTeacherAccess = Yii::$container->get('WTeacherAccess');
        $this->RStudentAccess = Yii::$container->get('RStudentAccess');
        $this->RClassAccess = Yii::$container->get('RClassAccess');
        $this->RRuleAccess = Yii::$container->get('RRuleAccess');
        $this->WBasepayAccess = Yii::$container->get('WBasepayAccess');
        $this->RBasepayAccess = Yii::$container->get('RBasepayAccess');
        $this->WWorktimeAccess = Yii::$container->get('WWorktimeAccess');

        parent::init();
    }


    public function rewardIndex()
    {
        $rewardInfo = $this->RTeacherAccess->getRewardInfo();

        return $rewardInfo;
    }

    public function addReward()
    {
        $ruleInfo = $this->RTeacherAccess->getRuleInfo();

        return $ruleInfo;
    }

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

        $re = $this->WTeacherAccess->doAddReward($request);

        if($re)
        {
            return '';
        }else{
            return '添加失败！请联系技术人员';
        }
    }

    public function editReward($reward_id)
    {
        $reward_info = $this->RRuleAccess->getRewardById($reward_id);

        $rule_info = $this->RTeacherAccess->getRuleInfo();

        $data = array('reward_info' => $reward_info, 'rule_info' => $rule_info);

        return $data;
    }

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

        $re = $this->WTeacherAccess->doEditReward($request);

        if($re)
        {
            return '';
        }else{
            return '修改失败！请联系技术人员';
        }
    }


    public function getOvertimeRewardInfoById($reward_id)
    {
        $reward_info = $this->RTeacherAccess->getOvertimeRewardById($reward_id);

        return $reward_info;
    }


    public function getTeacherCount()

    {
        return $this->RTeacherAccess->getTeacherCount();
    }

    //插入老师信息表
    public function teacherWagesAdd($data)
    {
        $time = strtotime($data['time']);

        $basic_salaryId = $this->RTeacherAccess->getTeacherWagesId($time,7);
        $commissionId   = $this->RTeacherAccess->getTeacherWagesId($time,8);

        if(!empty($basic_salaryId['id']) && !empty($commissionId['id'])){
            return 1;
        }

        $count       = count($data['array']);
        $reward_id   = '';
        $remark      = '';
        $text        = '';
        for($i = 0;$i<$count;$i++){
            if($i == 0){
                $teacher_id[] = $data['array'][$i];
            }
            if($i != 0){
                if($i % 4 == 0){
                    $teacher_id[] = $data['array'][$i];
                }
                if($i % 4 == 2){
                    $basic_salary[] = $data['array'][$i];
                }
                if($i % 4 == 3){
                    $commission[] = $data['array'][$i];
                }
            }
        }

        $tcount      = count($teacher_id);
        $transaction = Yii::$app->db->beginTransaction();
        $time        = time();
        try{
            $sql = "INSERT INTO reward_record(teacher_id, reward_id, month_time, text, remark, type,prefix,money,time_created) VALUES";
            for($j = 0;$j < $tcount; $j++){
                $sql .= "('{$teacher_id[$j]}','{$reward_id}','{$time}','{$text}','$remark','7','1','{$basic_salary[$j]}','{$time}'),";
                if($j != $tcount-1){
                    $sql .= "('{$teacher_id[$j]}','{$reward_id}','{$time}','{$text}','$remark','8','1','{$commission[$j]}','{$time}'),";
                }else{
                     $sql .= "('{$teacher_id[$j]}','{$reward_id}','{$time}','{$text}','$remark','8','1','{$commission[$j]}','{$time}')";
                }
            }
            $this->WTeacherAccess->doAddAllRewardRecord($sql);
            $transaction->commit();
            return 0;
        }catch (Exception $e) {
            $transaction->rollBack();
            return 2;
        }

    }


    /**
     *  月爽约配置参数配置
     *  @param  $time  str
     *  @param  $name  str
     *  @param  $type  str
     *  @param  $num   str
     *  @return array
     */
    public function monthMissParams($time,$name,$type,$num='no')
    {
        
        $start   = strtotime($time.'/1');
        $time    = explode('/',$time);
        $day     = (strtotime($start . ' +1 month') - strtotime($start)) / 86400;
        //$day     = cal_days_in_month(CAL_GREGORIAN,intval($time['1']),intval($time['0']));
        $end     = $start + 3600*24*$day;
        $name    = trim($name);
        
        if($type == 0){
            $type = 'r.text IS NULL';
        }else{
            $type = 'r.text IS NOT NULL';
        }

        return [$start,$end,$name,$type,$num];
    }

    public function queryOvertimeList_2($month,$name,$type,$page_num){
        $arr=$this->monthtoarr($month);

        $teacherList=$this->RTeacherAccess->queryTeacherList($name,$type,$page_num);//查询所有的老师 nick id

        foreach ($teacherList as &$tec){
            $monthAndDayTbale=$this->reallyTeacherTable($arr,$tec['id']);//每天的真正排课时间
            $total=0;
            $tec['overtime_total'] =$total;

            if($monthAndDayTbale==1){

                continue;
            }


            foreach ($monthAndDayTbale as $key=>$day_table_bit){
                $list=BinaryDecimal::binaryToDecimal($day_table_bit);

                $day_firsttime=strtotime($key);//当天最早的时间
                $starttime=strtotime($key.' '.$list[0]['start']);//排课最早时间
                $endtime=strtotime($key.' '.$list[count($list)-1]['end']);//排课最晚时间
                $day_endtime=strtotime($key)+24*3600;//当天最晚时间

                //查询老师的实际上课时间
                $dayclassinfo=$this->RTeacherAccess->queryTeacherClassList($day_firsttime,$day_endtime,$tec['id']);

                if(empty($dayclassinfo)){
                    continue;
                }

                foreach ($dayclassinfo as $onerow){
                    $classstarttime=$onerow['time_class'];//实际的一次上课开始时间
                    $classendtime=$onerow['time_end'];//实际的一次上课结束时间
                    if($classstarttime < $starttime && $classendtime < $starttime ){
                        //echo "1111 sjks=".$classstarttime ." sjjs=".$classendtime ." pkks=".$starttime;
                        $total +=ceil(($classendtime-$classstarttime)/60);
                    }elseif($classstarttime < $starttime && $classendtime > $starttime){
                        $total +=ceil(($starttime-$classstarttime)/60);
                    }elseif ($classstarttime > $endtime && $classendtime > $endtime){
                        $total +=ceil(($classendtime-$classstarttime)/60);
                    }elseif ($classstarttime < $endtime && $classendtime > $endtime){
                        $total +=ceil(($classendtime-$endtime)/60);
                    }
                }
            }
            $tec['overtime_total'] =$total;
        }
        //var_dump($teacherList);

        return $teacherList;
    }

    /**
     * 得到实际的排课表
     * */
    public  function  reallyTeacherTable($arr,$tecid){
        $weekTable=array();
        $dayTable=array();
        $monthTable=array();
        $teacherWeekInfo=$this->RTeacherAccess->queryTeacherWeekInfo($tecid);//查询老师的周课表

        if(empty($teacherWeekInfo)){
            return 1;
        }

        foreach ($teacherWeekInfo as $day){
            if($day['week']==7){
                $weekTable[0]=$day['time_bit'];
            }else{
                $weekTable[$day['week']]=$day['time_bit'];
            }
        }

        foreach ($arr as $month_day){
            $monthTable[$month_day]=  $weekTable[date('w',strtotime($month_day))];
        }

        $teacherDayInfo=$this->RTeacherAccess->queryTeacherDayInfo($tecid);//查询老师的日课表

        if($teacherDayInfo){
             return $monthTable;
        }else{
            foreach ($teacherDayInfo as $ritable){
                $day=date("Y-m-d",$ritable['time_day']);
                $dayTable[$day]=$ritable['time_bit'];
            }

            return $dayTable+$monthTable;//相同主键的数据前面的数组覆盖后面的数组
        }
    }

    /**
     * 将每个月的每天变为数组
     * */

    public  function  monthtoarr($month){
        $time_st=strtotime($month);
        $j = date('t',$time_st);
        $month_first=date('Y-m-01',$time_st);

        for($i=0;$i<$j;$i++){
            $arr[]=date('Y-m-d',strtotime($month_first)+$i*24*3600);
        }

        return $arr;
    }


    public function doAddOvertimeRewardRest($request){
        $transaction = Yii::$app->db->beginTransaction();

        try{
            $money = $this->calculateSalary($request['teacher_id'],'',$request['min'],'',$request['reward_id']);

//            if ($money == '未收录')
//            {
//                return '加班薪资为0不需要收录';
//            }

            $prefix = $this->RTeacherAccess->getPrefixByRewardId($request['reward_id']);

            $this->WTeacherAccess->doAddRewardRecord($request['teacher_id'],$request['reward_id'],$request['month'],$request['text'],$request['mark'],5,$prefix,$money);


            $transaction->commit();

            return '';

        }catch (Exception $e) {
            $transaction->rollBack();

            return '添加失败！';
        }

    }

    public function getOperationStatisticalList()
    {
        $start = time() - 3600*24*20;
        $end   = time();
        return $this->RTeacherAccess->getOperationStatisticalList($start,$end);
    }

    /**
     * 获取老师档案列表数量
     */
    public function getTeacherRecordCount($search)
    {
        return $this->RTeacherAccess->getTeacherRecordCount($search);
    }

    /**
     * 获取老师档案列表
     */
    public function getTeacherRecordList($search, $page)
    {

        $data = $this->RTeacherAccess->getTeacherRecordList($search, $page);

        foreach ($data as &$item) {

            $subQuery = $this->RTeacherAccess->getTeacherInstrumentInfo($item['id']);

            $temp = "";
            foreach ($subQuery as $key) {
                $level = Yii::$app->params['instrument_grade'][$key['grade']].'-'.$key['level'];
                $temp = $temp . $key["name"] . "-" . $level . "<br />";
            }

            $item['level'] = $temp;
        }

        return $data;
    }

    public function getTeacherByKeyCount($word_key, $work_type, $place_type)
    {
        return $this->RTeacherAccess->getTeacherByKeyCount($word_key, $work_type, $place_type);
    }

    public function getTeacherByKeyInfo($word_key, $work_type, $place_type, $page_num)
    {
        $teacherInfo = $this->RTeacherAccess->getTeacherByKeyInfo($word_key, $work_type, $place_type, $page_num);

        foreach ($teacherInfo as &$item)
        {
//            $subQuery = $this->RTeacherAccess->getTeacherInstrument($item['id']);
//
//            $temp = "";
//            foreach ($subQuery as $key){
//                $level = $key['type'] == 0 ? "内:" . $key['level'] : "外:" . $this->instrumentType($key['level']);
//                $temp = $temp . '<p>'.$key["name"] . "-" . $level . "</p>";
//            }
            $subQuery = $this->RTeacherAccess->getTeacherInstrumentInfo($item['id']);
            $temp = "";
            foreach ($subQuery as $key){
               $temp .= '<p>'.$key['name'].'-'.Yii::$app->params['gradeList'][$key['grade']].'-'.$key['level']  .'</p>';
            }
            $item["level"] = $temp;
        }
        return $teacherInfo;
    }


    public function getTeacherTimedata($week, $teacher)
    {
        $time = $week == 0 ? time() : time() + 86400*7;
        list($timeStart, $timeEnd) = $this->getTimeByType(2, $time);

        $data = array();
        for($i = 1; $i < 8; $i ++) {

            $weekInfo = [];
            $dayTimeBit = 0;
            $fixedTimeBit = 0;
            $timeBitClass = 0;
            $timeDay = $timeStart + ($i-1) * 86400;
            $weekDay = date('w', $timeDay);
            $weekDay = $weekDay == 0 ? 7 : $weekDay;

            $dayTimeBit = $this->RTeacherAccess->getDayTimeBit($teacher, $timeDay);

            $fixedTimeBit = $this->RTeacherAccess->getFixedTimeBit($teacher, $weekDay);


            $fixedTimeBit = empty($fixedTimeBit['time_bit']) ? 281474976710656 : $fixedTimeBit['time_bit'];

            $timeBitRest = empty($dayTimeBit['time_bit']) ? $fixedTimeBit : $dayTimeBit['time_bit'];

            $timeBitClassList = $this->RTeacherAccess->getTimeBitClassList($i,$teacher);

            if(!empty($timeBitClassList)) {
                foreach($timeBitClassList as $class) {
                    $timeBitClass = $timeBitClass | $class;
                }
            }

            for($m = 0; $m < 48 ; $m ++) {
                $statusInfo = array('status' => 0, 'name' => '');
                $num = pow(2,$m);

                if(($timeBitRest & $num) == $num) {
                    $statusInfo['status'] = ($timeBitClass & $num) == $num ? 3 : 1;
                    if($statusInfo['status'] == 3) {
                        $statusInfo['name'] = $this->RTeacherAccess->getStundentFixTimeName($teacher, $weekDay, $num);
                    }
                }else {
                    $statusInfo['status'] = ($timeBitClass & $num) == $num ? 2 : 0;
                    if($statusInfo['status'] == 2) {
                        $statusInfo['name'] = $this->RTeacherAccess->getTeacherClassBeanName($teacher, $weekDay, $num);
                    }
                }
                $weekInfo[] = $statusInfo;
            }
            $data[] = $weekInfo;
        }

        return $data;
    }


    //老师简历信息
    public function getTeacherResumeInfo($id)
    {
        return $this->RTeacherAccess->getTeacherResumeInfo($id);
    }

    //公共方法
    function getTimeByType($type, $time) {
        switch ($type) {
            case 1 : //当日
                $timeStart = floor($time/86400)*86400 - 28800;
                $timeEnd = $timeStart + 86400;
                break;
            case 2 : //当周
                $week = date('w',$time);
                $timeStart = $week == 1 ? strtotime(date('Y-m-d',$time)) : strtotime('-1 Mon',$time);
                $timeEnd = $timeStart + 86400*7;
                break;
            case 3 : //当月
                $timeStart = strtotime(date('Y-m-01',$time));
                $timeStartDate = date('Y-m-01',$timeStart);
                $timeEnd = strtotime("$timeStartDate +1 month");
                break;
            default :
                return [0, 0];
                break;
        }
        return [$timeStart, $timeEnd];
    }

    /*
        筛选老师
    */
    public function getTeacherNameArray($keyword)
    {
        
        $teacherInfo = $this->RTeacherAccess->getTeacherList($keyword);

        foreach ($teacherInfo as &$row)
        {
            $row['utilization'] = $row['count_1'] / 2 + $row['count_2'];
        }

        if(empty($teacherInfo)){
            return json_encode('kong');
        }else{
           return json_encode($teacherInfo);
        }
        
        return $teacherInfo;
    }

    public function getTeacherInfoById($teacher_id)
    {
        return $this->RTeacherAccess->getTeacherInfoById($teacher_id);
    }

    public function getTeacherSchool($teacher_school_id)
    {
        return $this->RTeacherAccess->getTeacherSchool($teacher_school_id);
    }

    public function deleteTeacher($teacher_id)
    {
        $re = $this->WTeacherAccess->deleteTeacher($teacher_id);

        if (!empty($re))
        {
            return 1;
        }else{
            return 0;
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

    public function getTeacherListByKey($filter)
    {
        return $this->RTeacherAccess->getTeacherListByKey($filter);
    }
    
  

    public function doEditHead($teacher_id)
    {
        $file = $_FILES;

        if ($file['file']["error"] > 0) {
            return 0;
        } else {
            // 要上传的空间
            $bucket = Yii::$app->params['pnl_static_bucket'];

            $fileKey = md5($teacher_id . '_' . microtime() . '_' . rand(10, 99));

            // 上传到七牛后保存的文件名
            $key = 'user/head_icon/' . $fileKey;

            //本地上传的文件路径
            $filePath = $file['file']['tmp_name'];

            $err = QiniuService::uploadToQiniu($bucket, $key, $filePath);

            if ($err == false) {
                return json_encode(array("result"=>"0"));
            } else {
                $res = $this->WTeacherAccess->editTeacherHead($teacher_id, $key);

                if($res){
                    return json_encode(array("result"=>Yii::$app->params["pnl_static_path"] . $key));
                }else{
                    return json_encode(array("result"=>"1"));
                }
            }
        }
    }

    
    /**
     * @param 
     * @return mixed
     * @author sjy
     * 添加老师基本信息
     */
    public function addTeacher($employedtime,$name,$mobile,$password,$gender,$placeId,$workType,$token,$salary_rate,$teacher_type,$school_id){
          $employedtime=strtotime($employedtime);
          return $this->WTeacherAccess->addTeacher($employedtime,$name,$mobile,$password,$gender,$placeId,$workType,$token,$salary_rate,$teacher_type,$school_id);
    }
    

    /**
     * @param 
     * @return mixed
     * @author sjy
     * 获取乐器种类信息
     */
    public function getInstrument(){
          return $this->RTeacherAccess->getInstrument();
    }

    public function getResumeById($teacher_id)
    {
        return $this->RTeacherAccess->getResumeById($teacher_id);
    }

    public function doEditResume($teacher_id, $resume)
    {
        return  $this->WTeacherAccess->doEditResume($teacher_id, $resume);
    }

    public function getShowNameById($teacher_id)
    {
        return $this->RTeacherAccess->getShowNameById($teacher_id);
    }

    /**
     * @param
     * @return mixed
     * @author hll
     * 查找老师负责学校字段
     */
    public function getResponsibleSchoolArray($teacher_id)
    {
        $data = $this->RTeacherAccess->getResponsibleSchoolById($teacher_id);

        $school_array = explode(",",$data);

        $count = count($school_array);

        if($count>0)
        {
            array_pop($school_array);
        }

        return array('error' => 0, 'data' => $school_array);
    }

    public function doEditShowName($teacher_id, $show_name)
    {
        return $this->WTeacherAccess->doEditShowName($teacher_id, $show_name);
    }

    public function doEditFormal($teacher_id)
    {
        $re = $this->WTeacherAccess->doEditFormal($teacher_id);

        if (!empty($re))
        {
            return 1;
        }else{
            return 0;
        }
    }

    /**
     * @param 
     * @return mixed
     * @author sjy
     * 编辑老师
     */
    public function editTeacher($kefuId,$teacher_id,$name,$show_name,$mobile,$password,$teacherLevel,$instrumentLevel,$gender,$placeId,$workType,$employedtime,$is_test,$token,$type,$work_new,$type_new,$salary_rate,$teacher_type,$school_id,$style,$sounds,$teacher_experience,$responsible_school)
    {
        $employedtime= strtotime($employedtime);
        $transaction = Yii::$app->db->beginTransaction();

        try{
            $salaryInfo = $this->RTeacherAccess->getSalaryTime($teacher_id);

            if(($workType != $salaryInfo['work_type'] ))
            {
                if($salaryInfo == null){

                    $this->WBasepayAccess->addSalaryLog($workType,$kefuId,$teacher_id,0,0,0,0,0,0,0,0,0,0,0);
                }else{

                     $this->WBasepayAccess->addSalaryLog($workType,$kefuId,$teacher_id,$salaryInfo['salary_after'],$salaryInfo['salary_after'],$salaryInfo['class_hour_first'],$salaryInfo['class_hour_second'],$salaryInfo['class_hour_third'],$salaryInfo['salary_time'],$salaryInfo['hour_time'],$salaryInfo['allduty_award_rates'],$salaryInfo['absence_punished_rates'],$salaryInfo['allduty_time'],$salaryInfo['absence_time']);
                }
            }

            $userId = $this->WTeacherAccess->updateTeacher($employedtime,$teacher_id,$name,$show_name,$mobile,$password,$gender,$placeId,$workType,$token,$is_test,$work_new,$type_new,$salary_rate,$teacher_type,$school_id,$style,$sounds,$teacher_experience,$responsible_school);
//            $delete_instrument=$this->WTeacherAccess->deleteTeacherInstrument($teacher_id);
//
//            foreach($instrumentLevel as $instrument => $level) {
//                $this->WTeacherAccess->addTeacherInstrument($teacher_id, $level['instrument'], 0, $level['level_in']);
//                $this->WTeacherAccess->addTeacherInstrument($teacher_id, $level['instrument'], 1, $level['level_out']);
//            }

            $this->WBasepayAccess->deleteInstrumentSalary($teacher_id);

            foreach ($instrumentLevel as $item)
            {
                $this->WBasepayAccess->addInstrumentSalary($teacher_id, $item['instrument'], $item['grade'], $item['level'], $item['hour_first'], $item['hour_second'], $item['hour_third'], $item['salary']);

                $this->WBasepayAccess->addInstrumentSalaryLog($teacher_id, $item['instrument'], $item['grade'], $item['level'], $item['hour_first'], $item['hour_second'], $item['hour_third'], $item['salary']);
            }

            $transaction->commit();
        }catch (Exception $e) {
            $transaction->rollBack();
            return json_encode(array('error' => '修改失败'));
        }

        return json_encode(array('error' => ''));
    }

    /**
     * @param 
     * @return mixed
     * @author sjy
     * 添加老师
     */
    public function addTeacherInfo($kefuId,$teacher_id,$name,$show_name,$mobile,$password,$teacherLevel,$instrumentLevel,$gender,$placeId,$workType,$employedtime,$is_test,$token,$type,$work_new,$type_new,$salary_rate,$teacher_type,$school_id,$style,$sounds,$teacher_experience,$responsible_school)
    {
        $employedtime= strtotime($employedtime);
        $transaction = Yii::$app->db->beginTransaction();
                try{
                    $userId = $this->WTeacherAccess->addTeacher($employedtime,$name,$show_name,$mobile,$password,$gender,$placeId,$workType,$token,$is_test,$work_new,$type_new,$salary_rate,$teacher_type,$school_id,"",$style,$sounds,$teacher_experience,$responsible_school);
                    $execute_time = strtotime(date('Y-m-d', time()));
                    $this->WWorktimeAccess->addNewTeacherFixedTime($userId, $execute_time);
                    $this->WWorktimeAccess->addNewTeacherFixedTimeLog($userId, $execute_time);
//                    foreach($instrumentLevel as $instrument => $level) {
//                        $this->WTeacherAccess->addTeacherInstrument($userId, $level['instrument'], 0, $level['level_in']);
//                        $this->WTeacherAccess->addTeacherInstrument($userId, $level['instrument'], 1, $level['level_out']);
//                    }

                    foreach ($instrumentLevel as $item)
                    {
                        $this->WBasepayAccess->addInstrumentSalary($userId, $item['instrument'], $item['grade'], $item['level'], $item['hour_first'], $item['hour_second'], $item['hour_third'], $item['salary']);

                        $this->WBasepayAccess->addInstrumentSalaryLog($userId, $item['instrument'], $item['grade'], $item['level'], $item['hour_first'], $item['hour_second'], $item['hour_third'], $item['salary']);
                    }

                    $transaction->commit();
                }catch (Exception $e) {
                    $transaction->rollBack();
                    return json_encode(array('error' => '添加失败'));
                }
                return json_encode(array('error' => ''));
    }

    /**
     * @param
     * @return mixed
     * @author yh
     * 添加校招老师并添加乐器等级到teacher_instrument表，时段默认全关闭
     */
    public function addTeacherInfoAndInstrumentAndFixTime($instrumentLevel,$name,$show_name,$mobile,$password,$gender,$placeId,$workType,$employedtime,$is_test,$token,$work_new,$type_new,$salary_rate,$teacher_type,$school_id,$openid)
    {
        $employedtime= strtotime($employedtime);
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $teacher_id = $this->WTeacherAccess->addTeacher($employedtime,$name,$show_name,$mobile,$password,$gender,$placeId,$workType,$token,$is_test,$work_new,$type_new,$salary_rate,$teacher_type,$school_id,$openid,"","",0);
            //$teacher_id = $this->WTeacherAccess->addTeacher($employedtime,$name,$show_name,$mobile,$password,$gender,$placeId,$workType,$token,$is_test);
            foreach($instrumentLevel as $instrument => $level) {
                $this->WTeacherAccess->addTeacherInstrumentIntoTeacherInstrument($teacher_id, $level['instrument_id'], $level['grade'], $level['level']);
            }
            $execute_time = strtotime(date('Y-m-d', time()));
            $this->WWorktimeAccess->addNewTeacherFixedTime($teacher_id,$execute_time);
            $this->WWorktimeAccess->addNewTeacherFixedTimeLog($teacher_id,$execute_time);
            $transaction->commit();
        }catch (Exception $e) {
            $transaction->rollBack();
            return json_encode(array('error' => '添加失败'));
        }
        return 1;
    }


//    /**
//     * @param
//     * @return mixed
//     * @author yh
//     * 添加老师信息到user_teacher表，微信信息到teacher_wechat_acc表并添加乐器等级到teacher_instrument表
//     */
//    public function addSchoolTeacherWechatAndInstrument($instrumentLevel,$name,$show_name,$mobile,$password,$gender,$placeId,$workType,$employedtime,$is_test,$token,$work_new,$type_new,$salary_rate,$teacher_type,$school_id,$openid,$head,$nick,$subscribe_time)
//    {
//        $employedtime= strtotime($employedtime);
//        $transaction = Yii::$app->db->beginTransaction();
//        try{
//            $teacher_id = $this->WTeacherAccess->addTeacher($employedtime,$name,$show_name,$mobile,$password,$gender,$placeId,$workType,$is_test,$token,$work_new,$type_new,$salary_rate,$teacher_type,$school_id);
//            //$teacher_id = $this->WTeacherAccess->addTeacher($employedtime,$name,$show_name,$mobile,$password,$gender,$placeId,$workType,$token,$is_test);
//            foreach($instrumentLevel as $instrument => $level) {
//                $this->WTeacherAccess->addTeacherInstrumentIntoTeacherInstrument($teacher_id, $level['instrument_id'], $level['grade'], $level['level']);
//            }
//            $this->WTeacherAccess->doAddTeacherWechatAcc($teacher_id,$openid,$head,$nick,$name,$subscribe_time);
//            $transaction->commit();
//        }catch (Exception $e) {
//            $transaction->rollBack();
//            return ['error' => '添加失败'];
//        }
//        return ['error' => '','data' => ''];
//    }

     /**
     * @param 
     * @return mixed
     * @author sjy
     * 添加薪资信息
     */
    public function editSalaryInfo($kefuId,$teacher_id,$allduty_award_rates,$absence_punished_rates,$allduty_time,$absence_time,$salaryAfter,$salaryTime,$salary_25,$salary_45,$salary_50,$hour_time)
    {
           
            $transaction = Yii::$app->db->beginTransaction();
                try{
                    $salaryInfo =$this->RTeacherAccess->getSalaryTime($teacher_id);
                    
                    if(($absence_time!= $salaryInfo['absence_time']||$allduty_time != $salaryInfo['allduty_time']||$absence_punished_rates!=$salaryInfo['absence_punished_rates']||$allduty_award_rates !=$salaryInfo['allduty_award_rates']||$hour_time != $salaryInfo['hour_time']) || ($salaryTime != $salaryInfo['salary_time'])  || ($salaryAfter != $salaryInfo['salary_after']) || ($salary_25 != $salaryInfo['class_hour_first']) || ($salary_45 != $salaryInfo['class_hour_second']) || ($salary_50 != $salaryInfo['class_hour_third']))
                    {
                        if($salaryInfo == null){
                            $this->WBasepayAccess->addSalaryLog(0,$kefuId,$teacher_id,0,$salaryAfter,$salary_25,$salary_45,$salary_50,$salaryTime,$hour_time,$allduty_award_rates,$absence_punished_rates,$allduty_time,$absence_time);
                     
                        }else{
                             $this->WBasepayAccess->addSalaryLog($salaryInfo['work_type'],$kefuId,$teacher_id,$salaryInfo['salary_after'],$salaryAfter,$salary_25,$salary_45,$salary_50,$salaryTime,$hour_time,$allduty_award_rates,$absence_punished_rates,$allduty_time,$absence_time);
                    
                        }
                       
                        }
                   
                    $update_salary_info = $this->WTeacherAccess->updateTeacherSalaryInfo($teacher_id,$allduty_award_rates,$absence_punished_rates,$allduty_time,$absence_time,$salaryAfter,$salaryTime,$salary_25,$salary_45,$salary_50,$hour_time);

                    $transaction->commit();
                }catch (Exception $e) {
                    $transaction->rollBack();
                    return json_encode(array('error' => '修改失败'));
                }
            return json_encode(array('error' => ''));
    }
          
    /**
     * @param 
     * @return mixed
     * @author sjy
     * 获取老师的乐器
     */
    public function getTeacherInstrument($teacher_id){
          $instrument =$this->RTeacherAccess->getTeacherInstrument($teacher_id);
          
          $instrument_new = array();
        foreach($instrument as $key=>$item)
        {
            $instrument_new[$item['instrument_id']]['id'] = $item['instrument_id'];
            $instrument_new[$item['instrument_id']]['name'] = $item['name'];
            if($item['type'] == 0)
            {
                $instrument_new[$item['instrument_id']]['level_in'] = $item['level'];
            }else{
                $instrument_new[$item['instrument_id']]['level_out'] = $item['level'];
            }

        }
        
        return $instrument_new;
          
    }
    
    
    /**
     * @param 
     * @return mixed
     * @author sjy
     * 查找用户手机号是否存在
     */
    public function checkMobile($mobile,$userId = 0,$role = -1){
        
         if($this->RTeacherAccess->checkMobile($mobile, $userId, $role)) {
            return true;
        }else {
            return false;
        }
    }

    public function selectTeacher($filter)
    {
        $list = $this->RTeacherAccess->getTeacherListByKey($filter);

        return count($list);
    }

    public function getTeacherSalaryInfo($teacher_id)
    {
        $time_day = strtotime(date('Y-m-d',time()));

        $instrument = $this->RTeacherAccess->getInstrument();

        $teacher_info = $this->RTeacherAccess->getTeacherInfoById($teacher_id);

        $teacher_salary = $this->RBasepayAccess->getTeacherBasePay($teacher_id);

        if($teacher_info['teacher_type'] == 1){
            $school_id = 0;
        }else{
            $school_id = $teacher_info['school_id'];
        }
        foreach ($instrument as $key => $item)
        {
            $instrument[$key]['check'] = 0;
            $instrument[$key]['grade'] = 1;
            $instrument[$key]['level'] = 1;
            $salary = $this->RBasepayAccess->getBasicSalaryByGrade($teacher_info['teacher_type'], $school_id, 1, 1, $time_day);
            if (empty($salary))
            {
                $instrument[$key]['25'] = number_format(0, 2, '.', '');
                $instrument[$key]['45'] = number_format(0, 2, '.', '');
                $instrument[$key]['50'] = number_format(0, 2, '.', '');
                $instrument[$key]['salary_after'] = number_format(0, 2, '.', '');
            }else{
                $instrument[$key]['25'] = $salary['class_hour_first'];
                $instrument[$key]['45'] = $salary['class_hour_second'];
                $instrument[$key]['50'] = $salary['class_hour_third'];
                $instrument[$key]['salary_after'] = $salary['salary_after'];
            }

            $instrument[$key]['25_we'] = number_format(0, 2, '.', '');
            $instrument[$key]['45_we'] = number_format(0, 2, '.', '');
            $instrument[$key]['50_we'] = number_format(0, 2, '.', '');
            $instrument[$key]['salary_we'] = number_format(0, 2, '.', '');

            if (!empty($teacher_salary))
            {
                foreach ($teacher_salary as $k => $row)
                {
                   if ($row['instrument_id'] == $item['id'])
                   {
                       $instrument[$key]['check'] = 1;
                       $instrument[$key]['grade'] = $row['grade'];
                       $instrument[$key]['level'] = $row['level'];
                       $salary = $this->RBasepayAccess->getBasicSalaryByGrade($teacher_info['teacher_type'], $teacher_info['school_id'], $row['grade'], $row['level'], $time_day);
                       if (empty($salary))
                       {
                           $instrument[$key]['25'] = number_format(0, 2, '.', '');
                           $instrument[$key]['45'] = number_format(0, 2, '.', '');
                           $instrument[$key]['50'] = number_format(0, 2, '.', '');
                           $instrument[$key]['salary_after'] = number_format(0, 2, '.', '');
                       }else{
                           $instrument[$key]['25'] = $salary['class_hour_first'];
                           $instrument[$key]['45'] = $salary['class_hour_second'];
                           $instrument[$key]['50'] = $salary['class_hour_third'];
                           $instrument[$key]['salary_after'] = $salary['salary_after'];
                       }

                       $instrument[$key]['25_we'] = $row['hour_first'];
                       $instrument[$key]['45_we'] = $row['hour_second'];
                       $instrument[$key]['50_we'] = $row['hour_third'];
                       $instrument[$key]['salary_we'] = $row['salary'];
                   }
                }
            }
        }

        return array('error' => 0, 'data' => $instrument);
    }

    /**
     * 获取老师在某个时间区间内时段总小时数
     * @param $teacher_id
     * @param $time_start
     * @param $time_end
     * @return array
     */
/*    public function getTeacherFixWorkHourWithinTimeInterval($teacher_id, $time_start, $time_end)
    {
        // 4月1日
        $cur_end_time = $time_end;
        $total_hour = 0;
        // 循环1周
        for($idx = 1; $idx <=7; $idx++)
        {
            // 从end往前倒退一天，3月31日，星期5
            $cur_end_time = $cur_end_time - 86400;
            $cur_week = date('w',$cur_end_time);
            $cur_week = ($cur_week == 0? 7: $cur_week);
            // 该教师所有星期x的时段安排
            $teacher_time_bit = $this->RTeacherAccess->getTeacherFixedTimeRowOrderByExeTime($teacher_id,$cur_week);

            if(empty($teacher_time_bit))
            {
                continue;
            }

            $entry_idx = 0;
            // 最新执行的时段安排，及执行时间
            $last_time_execute =  $teacher_time_bit[$entry_idx]['time_execute'];
            $work_hour = BinaryDecimal::getFixLong($teacher_time_bit[$entry_idx]['time_bit']);
            // 每次循环计算指定时间段内所有星期x的工作时间
            $no_record = false;
            for($time = $cur_end_time; $time >= $time_start; $time -= 86400*7)
            {
                // 此日期的时间小于现工作时段执行时间，使用上一次执行的工作时段安排
                while($time < $last_time_execute)
                {
                    // 指向下一条记录
                    $entry_idx += 1;
                    // 此日期该老师没有对应时段记录，退出循环
                    if($entry_idx >= sizeof($teacher_time_bit))
                    {
                        $no_record = true;
                        break;
                    }

                    $last_time_execute = $teacher_time_bit[$entry_idx]['time_execute'];
                    $work_hour = BinaryDecimal::getFixLong($teacher_time_bit[$entry_idx]['time_bit']);
                }

                if($no_record)
                {
                    break;
                }

                $total_hour += $work_hour;
            }
        }

        $total_hour = $total_hour / 2;

        return ["error" => '', "data" => $total_hour];
    }*/


    public function getTeacherOpenId($teacher_id)
    {
        $data = $this->RTeacherAccess->getTeacherTypeOpenidById($teacher_id);

        return array('error' => 0, 'data' => $data);
    }
}