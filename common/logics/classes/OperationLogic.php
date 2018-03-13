<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/9
 * Time: 下午5:28
 */
namespace common\logics\classes;

use common\widgets\BinaryDecimal;
use console\models\teacher\PlaceHourRate;
use Yii;
use yii\base\Object;
use yii\db\Exception;
use common\services\LogService;
use common\widgets\Request;
use callmez\wechat\sdk;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;

class OperationLogic extends Object implements IOperation
{
    /** @var  \common\sources\read\classes\RecordAccess  $RRecordAccess */
    private $RRecordAccess;
    /** @var  \common\sources\read\chat\ChatAccess  $RChatAccess */
    private $RChatAccess;
    /** @var  \common\sources\write\student\StudentAccess  $WStudentAccess */
    private $WStudentAccess;
    /** @var  \common\sources\write\channel\ChannelAccess  $WChannelAccess */
    private $WChannelAccess;
    /** @var  \common\sources\read\classes\ClassAccess  $RClassAccess */
    private $RClassAccess;
    /** @var  \common\sources\write\classes\ClassAccess  $WClassAccess */
    private $WClassAccess;
    /** @var  \common\sources\read\student\StudentAccess  $RStudentAccess */
    private $RStudentAccess;
    /** @var  \common\sources\read\teacher\TeacherAccess  $RTeacherAccess */
    private $RTeacherAccess;
    /** @var  \common\logics\push\TemplateLogic  $templateService */
    private $templateService;
    /** @var  \common\compute\SalaryCompute  $salaryCompute */
    private $salaryCompute;


    public function init()
    {
        $this->RClassAccess = Yii::$container->get('RClassAccess');
        $this->RTeacherAccess = Yii::$container->get('RTeacherAccess');
        $this->WClassAccess = Yii::$container->get('WClassAccess');
        $this->RRecordAccess = Yii::$container->get('RRecordAccess');
        $this->RChatAccess = Yii::$container->get('RChatAccess');
        $this->RStudentAccess = Yii::$container->get('RStudentAccess');
        $this->WStudentAccess = Yii::$container->get('WStudentAccess');
        $this->WChannelAccess = Yii::$container->get('WChannelAccess');
        $this->templateService = Yii::$container->get('templateService');
        $this->salaryCompute = Yii::$container->get('salaryCompute');

        parent::init();
    }

    public function batchDeleteClass($classIds, $logid)
    {

        $ids = $this->RClassAccess->getLeftInfoByClassIds($classIds);

        $leftIds = array();
        $historyIds = array();

        foreach($ids as $k=>$val){
            $leftIds[] = $val['left_id'];
            $historyIds[] = $val['history_id'];
        }

        $leftCount = array_count_values($leftIds);

        //transaction start
        $transaction = Yii::$app->db->beginTransaction();

        try{
            $this->WClassAccess->batchUpdateClassTimeDelete($classIds);
            $this->WClassAccess->resetClassAmountByLeftId($leftCount);
            $this->WClassAccess->updateSuccessById($historyIds);

            $transaction->commit();
            LogService::OutputLog($logid, 'Delete', '', '批量删除课程');
            return json_encode(array('error' => ''));

        }catch(Exception $e){
            $transaction->rollback();
            return json_encode(array('error' => '删除失败！'));
        }
    }

    /**
     * 修改时间格式
     * @param   $time
     * @return  int
     */
    public function timeFormat($time)
    {
        $arr = explode('T', $time);
        return strtotime($arr[0] . ' ' . $arr[1]);
    }

    public function getClassTimeEnd($type, $timeStart)
    {
        switch ($type) {
            case 1 :
                return $timeStart + 1500;
            case 2 :
                return $timeStart + 2700;
            case 3 :
                return $timeStart + 3000;
        }
    }

    public function filterTeacher2List($teacher2List)
    {
        $teacherIdList = array();
        foreach ($teacher2List as $teacher) {
            $teacherIdList[] = $teacher['id'];
        }
        return $teacherIdList;
    }

    /**
     * 添加课程
     */
    public function doAddClass($request, $logid)
    {
//        var_dump($request);
//        die();
        
        if(empty($request['teacher_id'])){
            return json_encode(array('error' => '请选择老师'));
        }
        
        $role =  Yii::$app->user->identity->role;
        $userId = Yii::$app->user->identity->id;

        //根据套餐id,获取套餐信息
        $classInfo = $this->RClassAccess->getClassLeft($request['left_id']);
        //拆分课程开始时间
        $request['time_start'] = $this->timeFormat($request['time_start']);

        //获取套餐类型（1、体验课，2、赠送，3、套餐  PS: 这里是为了根据课程种类来隐藏掉某些不想给他的课程）
        $request['class_type'] = $classInfo['type'];

        //根据课程开始时间和套餐类型计算课程结束时间
        $request['time_end'] = $this->getClassTimeEnd($classInfo['time_type'], $request['time_start']);

        //获取套餐的乐器类型
        $request['instrument_id'] = $classInfo['instrument_id'];

        $request['class_id'] = 0;
        //获取套餐的时间类型
        $type = $classInfo['time_type'];
        //判断套餐是否是体验课
        $isExClass = $classInfo['type'] == 1 ? 1 : 0;
        $instrumentId = $classInfo['instrument_id'];
        $timeStart = $request['time_start'];
        //获取设置课程的周数
        $weeks = $request['weeks'];
        //获取当前的学生id
        $studentId = $request['student_id'];
        //获取当前选中的老师id
        $teacherId = $request['teacher_id'];
        $marks = $request['marks'];
        $timeEnd = $request['time_end'];
        $status = empty($request['is_red']) ? 0 : 8;

        if ($isExClass == 1 && $type == 3) {
            return json_encode(array('error' => '体验课不能排50分钟课程'));
        }

        if($timeStart < time() && $userId != 49 && $userId != 2)
        {
            return json_encode(array('error' => '不能排过去的时间'));
        }

        //根据老师id获取老师信息
        $teacherInfo = $this->RTeacherAccess->getTeacherBaseInfo($teacherId);

        //获取该用户是否是付费用户
        $isEx = $this->RClassAccess->getClassEditHistoryCount($studentId);

      
        //根据套餐id获取该套餐的剩余数量
        $leftTimesAmount = $this->RClassAccess->getClassLeftAmountCount($request['left_id']);
       

        //获取老师的等级（算钱）
        $peiCardAmount = empty($teacherId) ? 1 : $teacherInfo['teacher_level'];
        
        //计算一个值？老师等级*上课周数
        $peiCardAmountTotal = $peiCardAmount * $weeks;

        //获取可以排课的老师
        $teacher1List = $this->RClassAccess->getTeacherAvailableList($request);
        //获取可以抢占的体验课
        //如果当前学生是付费用户，那么可以抢占体验课，查询当前时间内只有体验课的老师
        if(empty($request['ttype'])){
            $teacher2List = empty($isEx) ? [] : $this->RClassAccess->getTeacherHaveExClass($request);
        }else{
            if($request['ttype']==1){
                $teacher2List=[];
            }else{
                $teacher2List = empty($isEx) ? [] : $this->RClassAccess->getTeacherHaveExClass($request);
            }
            
        }

        //获取可以抢占的体验课id
        $teacherId2List = empty($teacher2List) ? [] : $this->filterTeacher2List($teacher2List);

        //合并可以排课的老师
        $teacherList = array_merge($teacher1List, $teacher2List);
        
        //获取当前课程时间段，teacherid=0的数量
        $noTeacherCount = $this->RClassAccess->countNoTeacher($timeStart, $timeEnd, $instrumentId);
        
        //检查当前老师是否可利用
        $isAvailableTeacher = $this->checkTeacherAvailable($teacherList, $teacherId);
        if ($isAvailableTeacher !== true && $teacherId > 0) {
            return $isAvailableTeacher;
        }
        
        //检查当前学生是否有时间上课
        $isAvailableStudent = $this->RClassAccess->checkStudentAvailable($studentId, $timeStart, $timeEnd);
         if (!$isAvailableStudent) {
            return json_encode(array('error' => '该学生此时间段有课'));
        }
        
/*
        if (!empty($isEx) && $isExClass == 1) {
            return json_encode(array('error' => '付费用户只能排购买课'));
        }
*/
        
        if (date('i', $timeStart) != '30' && date('i', $timeStart) != '00') {
            return json_encode(array('error' => '排课只能排整点或半点'));
        }

      
        if ($leftTimesAmount < $peiCardAmountTotal) {
            return json_encode(array('error' => '剩余课时不足'));
        }

//        //获取休息的老师
//           $teacher3List =  $this->RClassAccess->getRestTeacher($request);
//        
//           if (in_array($teacherId, $teacher3List) && $teacherId > 0) {
//                return json_encode(array('error' => '当前老师休息，请电话联系后排课'));
//            }

        if (empty($isEx) && in_array($teacherId, $teacherId2List) && $teacherId > 0) {
            return json_encode(array('error' => '付费学生权限'));
        }
/*
        if (empty($isEx) || (!empty($isEx) && !in_array($teacherId, $teacherId2List)) || $teacherId == 0) {
            if (count($teacherList) <= $noTeacherCount) {
                return json_encode(array('error' => '该时间段老师数量不足'));
            }
        }
*/
       

        $transaction = Yii::$app->db->beginTransaction();
        //echo 1;
        //检查套餐内的课程的数量
        if (!$this->WClassAccess->reduceClassTimes($request['left_id'], $peiCardAmountTotal)) {
            $transaction->rollback();//事务回滚
            return json_encode(array('error' => '更新课时失败'));
        }
        
        for ($i = 1; $i <= $weeks; $i++) {
            
            $timeDay = floor($timeStart / 86400) * 86400 - 28800;
            //echo 2;
            //添加课程信息
          
            $classId = $this->WClassAccess->addClassTime($request['left_id'],$instrumentId, $isExClass, $timeStart, $timeEnd, $studentId, $teacherId, $marks, $status);

            if (!($classId > 0)) {
                $transaction->rollback();
                return json_encode(array('error' => '添加课程失败'));
            }

            //如果是体验课，判断是否使用户上的第一节体验课
            if ($isExClass == 1) {
                if ($this->RClassAccess->checkIsFirstEx($studentId)) {
                    //更新第一次体验课的课程信息
                    if (!$this->WClassAccess->updateFirstExClass($classId)) {//echo 4;
                        $transaction->rollback();
                        return json_encode(array('error' => '跟新第一次体验状态失败'));
                    }
                }
            }

            //序列化这个课程id
            $detail = serialize(array('class_id' => $classId));
            //添加课程编辑历史记录
            $historyId = $this->WClassAccess->addHistory($userId, $role, $studentId, $instrumentId, $peiCardAmount, $isExClass, $type, 0, 1, 1, '', $detail);
            if (!($historyId > 0)) {
                $transaction->rollback();
                return json_encode(array('error' => '添加历史失败'));
            }//echo 7;
            
            if (!$this->WClassAccess->updateHistoryIdByClassId($classId, $historyId)) {
                $transaction->rollback();
                return json_encode(array('error' => '更新历史失败'));
            }//echo 8;
            
            
            if (!$this->WClassAccess->addCounts($timeDay, $type, 1, $studentId)) {
                $transaction->rollback();
                return json_encode(array('error' => '添加统计失败'));
            }
            
            if (!empty($isEx) && in_array($teacherId, $teacherId2List) && $teacherId > 0) {
                $classFailInfo = $this->RClassAccess->getClassFailInfo($teacherId, $timeStart, $timeEnd,$classId);
                foreach ($classFailInfo as $classFail) {
                    
//                    $this->WClassAccess->updateClassTimeDelete($classFail['id']);
//                    $this->WClassAccess->updateHistory($userId, $role, $classFail['history_id']);
//                    $this->WClassAccess->addClassTimesByLeftId($classFail['left_id']);
//                    $this->WClassAccess->addClassFail($classFail['id'], 1);
                    
                     //现在操作（将被挤掉的体验课的老师id设置为0，将该节被挤掉的体验课记录到错误课表日志中）
                    //将原本假删除的课程，由isdelete=1；改为老师的teacherid=0
                     $this->WClassAccess->updateClassInfoTeacher($classFail['id']);
                     //将被占的课程添加到错误课表日志中去
                     $this->WClassAccess->intoFailLog($classFail['id'], $classFail['student_id'], $teacherId, $userId, $role, 3);
                     
//                    //将历史记录中被抢占的课程是否成功状态设置为0
//                    $this->WClassAccess->updateHistory($userId, $role, $classFail['history_id']); 
//                    //将被抢占的体验课还给该套餐
//                    $this->WClassAccess->addClassTimesByLeftId($classFail['left_id']);
                    
                }
            }

            $teacher_list= array(
                'teacher_id' => $teacherId,
                'timeStart' => $timeStart,
                'timeEnd' => $timeEnd,
                );

            $add_list[] = $teacher_list;

            $timeStart += 604800;
            $timeEnd += 604800;
            $marks = '';
        }

        LogService::OutputLog($logid, 'insert', '', '排课');

        $transaction->commit();

        // 超过10点发送模板
        $isNight =  time() > strtotime('22:00') ? 1 : 0;

        return array('error' => '', 'data' => array(
            'isNight' => $isNight,
            'date' => date('Y/m/d', $request['time_start']),
            'class_id' => $classId,
            'type' => $classInfo->type,
            'student_id' => $classInfo->user_id,
            'time_class' => $request['time_start'],
            'time_end' => $timeEnd,
            'teacher_id' =>  $teacherId,
            'instrument_name' => $instrumentId,
            'add_list' => $add_list)
        );
    }

    public function checkTeacherAvailable($teacherList, $teacherId)
    {
        $teacher2List = array();
        if (empty($teacherList[0]['id'])) {
            return array('error' => '该老师此时间段没空！,请刷新页面,重新选择');
        }
        foreach ($teacherList as &$row) {
            $teacher2List[] = $row['id'];
        }
        
        if (!in_array($teacherId, $teacher2List)) {
            return array('error' => '该老师此时间段没空,请刷新页面,重新选择');
        }
        
        return true;
    }


    /**
     * 查看空闲老师
     * @param  $request array
     */
    public function getTeacherAvailable($request)
    {
       
        $request['ttype'] = isset($request['ttype']) ? $request['ttype'] : 0;
        $request['class_id'] = isset($request['class_id']) ? $request['class_id'] : 0;
        $request['time_start'] = $this->timeFormat($request['time_start']);

        if ($request['time_start'] == false) {
            return json_encode(array('error' => 'invalid_time'));
        }
        
        //获取学生这节课购买的课程的套餐信息
        $classInfo = $this->RClassAccess->getClassLeftTermId($request['left_id']);
        //获取套餐类型（1、体验课，2、赠送，3、套餐  PS: 这里是为了根据课程种类来隐藏掉某些不想给他的课程）
        $request['class_type'] = $classInfo['type'];

        //获取套餐时间类型（1:25分钟 2:45分钟 3:50分钟）
        $request['type'] = $classInfo['time_type'];
        //根据套餐类型计算课程的结束时间
        $request['time_end'] = $this->getClassTimeEnd($request['type'], $request['time_start']);
        $request['instrument_id'] = $classInfo['instrument_id'];

        //判断该学生是否是付费用户
        $isEx = $this->RClassAccess->getClassEditHistoryCount($request['student_id']);

        //获取可以排课的老师listgetTeacherAvailableList
        $teacher1List = $this->RClassAccess->getTeacherAvailableList($request);

        //如果当前学生是付费用户，那么可以抢占体验课，查询当前时间内只有体验课的老师
        if(empty($request['ttype'])){
            $teacher2List = empty($isEx) ? [] : $this->RClassAccess->getTeacherHaveExClass($request);
        }else{
            if($request['ttype']==1){
                $teacher2List=[];
            }else{
                $teacher2List = empty($isEx) ? [] : $this->RClassAccess->getTeacherHaveExClass($request);
            }
            
        }

       $teacherList = array_merge($teacher1List, $teacher2List);

        //找到该课程的任课老师，（当前老师）
        $teacherNow = empty($request['class_id']) ? 0
            :$this->RClassAccess->getClassRoomByteacherId($request['class_id']);

        foreach ($teacherList as &$row) {
            $row['is_current'] = $row['id'] == $teacherNow['teacher_id'] ? 1 : 0;
        }


//        return json_encode(array('error' => '', 'data' => $teacherList), JSON_UNESCAPED_SLASHES);
        return $teacherList;
    } 
    
    
    public function getTeacherAvailableTable($request)
    {
        // ttype (1 可排课老师  2 不可排课老师  3 测试老师)
        $request['ttype'] = isset($request['ttype']) ? $request['ttype'] : 0;
        $request['class_id'] = isset($request['class_id']) ? $request['class_id'] : 0;
        $request['time_start'] = $this->timeFormat($request['time_start']);

        if ($request['time_start'] == false) {
            return json_encode(array('error' => 'invalid_time'));
        }
        
        //获取学生这节课购买的课程的套餐信息
        $classInfo = $this->RClassAccess->getClassLeftTermId($request['left_id']);
        //获取套餐类型（1、体验课，2、赠送，3、套餐  PS: 这里是为了根据课程种类来隐藏掉某些不想给他的课程）
        $request['class_type'] = $classInfo['type'];

//        //获取套餐类型（1、体验课，2、赠送，3、套餐）
//        $request['type'] = $classInfo['time_type'];

        //获取套餐时间类型（1:25分钟 2:45分钟 3:50分钟）
        $request['type'] = $classInfo['time_type'];

        //根据套餐类型计算课程的结束时间
        $request['time_end'] = $this->getClassTimeEnd($request['type'], $request['time_start']);
        $request['instrument_id'] = $classInfo['instrument_id'];

        //判断该学生是否是付费用户
        $isEx = $this->RClassAccess->getClassEditHistoryCount($request['student_id']);

        //获取可以排课的老师listgetTeacherAvailableList
        $teacher1List = $this->RClassAccess->getTeacherAvailableList($request);
        
        //如果当前学生是付费用户，那么可以抢占体验课，查询当前时间内只有体验课的老师
        if(empty($request['ttype'])){
            $teacher2List = empty($isEx) ? [] : $this->RClassAccess->getTeacherHaveExClass($request);
        }else{
            if($request['ttype']==1){
                $teacher2List=[];
            }else{
                $teacher2List = empty($isEx) ? [] : $this->RClassAccess->getTeacherHaveExClass($request);
            }
        }
        
         
       $teacherList = array_merge($teacher1List, $teacher2List);
       
        //找到该课程的任课老师，（当前老师）
       $teacherNow = empty($request['class_id']) ? 0
            :$this->RClassAccess->getClassRoomByteacherId($request['class_id']);

       foreach ($teacherList as &$row) {
            $row['is_current'] = $row['id'] == $teacherNow['teacher_id'] ? 1 : 0;

            if ($row['teacher_type'] == 0)
            {
                $row['teacher_type']  = '【无】';
            } elseif ($row['teacher_type'] == 1) {
                $row['teacher_type'] = '【社招】';
            } else {
                $row['teacher_type'] = '【校招】';
            }
       }

        $teacherList = array_slice($teacherList, ($request['num']-1)*10,10);
        

//        return json_encode(array('error' => '', 'data' => $teacherList), JSON_UNESCAPED_SLASHES);
        return $teacherList;
    } 
    

    /**
     * 编辑该课程
     * @param  $request  array
     * @param  $logid    str
     * @return mixed
     */
    public function doEditClass($request, $logid)
    {
        $role =  Yii::$app->user->identity->role;
        $userId = Yii::$app->user->identity->id;

        //查询该课程套餐信息
        $classLeft = $this->RClassAccess->getClassLeft($request['left_id']);

        //拆分开始时间
        $request['time_start'] = $this->timeFormat($request['time_start']);
        //获取套餐类型（1、体验课，2、赠送，3、套餐  PS: 这里是为了根据课程种类来隐藏掉某些不想给他的课程）
        $request['class_type'] = $classLeft['type'];
        //获取套餐选择的乐器
        $request['instrument_id'] = $classLeft['instrument_id'];
        $classId = $request['class_id'];

        //判断是否是体验课
        $isExClass = $classLeft['type'] == 1 ? 1 : 0;
        
        $instrumentId = $classLeft['instrument_id'];
        //获取套餐的时间类型
        $type = $classLeft['time_type'];
        
        //获取新课程开始时间
        $timeStart = $request['time_start'];
        $marks = $request['marks'];//备注信息
        
        $isFail = !empty($request['is_fail'])?$request['is_fail']:0;
        
        $timeEnd = $this->getClassTimeEnd($type, $timeStart);

        $request['time_end'] = $timeEnd;

        if (date('i', $timeStart) != '30' && date('i', $timeStart) != '00') {
            return array('error' => '上课时间只能是整点或半点');
        }
        if (empty($request['teacher_id'])) {
            return array('error' => '请选择老师');
        }

        $teacherId = $request['teacher_id'];
        if ($isExClass == 1 && $type == 3) {
            return array('error' => '体验课不能排50分钟课程');
        }
        $transaction = Yii::$app->db->beginTransaction();

        //根据课程id获取当前课的历史修改信息和修改类型
        $classInfo = $this->RClassAccess->getRowById($classId);


        //根据学生id获取该学生课程修改记录的条数
        $isEx = $this->RClassAccess->getClassEditHistoryCount($classInfo['student_id']);

        //根据teacherid获取老师信息
        $teacherInfoNow = $this->RClassAccess->getRowByTeacherId($teacherId);

        //给老师等级赋值
        $peiCardAmountNow = $teacherInfoNow['teacher_level'];
        //$noTeacherCount = $classRoomModel->countNoTeacher($timeStart, $timeEnd);

        if ($isFail != $classInfo['is_deleted']) {
            return array('error' => '课程已经修改,请刷新页面');
        }
/*
        if (!empty($isEx) && $isExClass == 1) {
            return json_encode(array('error' => '付费用户只能排购买课'));
        }
*/
        $sign = false;

        if ($teacherId != $classInfo['teacher_id'] || $request['left_id'] != $classInfo['left_id'] || $timeStart != $classInfo['time_class'] || $timeEnd != $classInfo['time_end'] || $isFail == 1) {
            $sign = true;
            //获取可利用老师列表
            $teacher1List = $this->RClassAccess->getTeacherAvailableList($request);
            //如果当前用户是付费用户，获取可抢占的体验课老师
            //如果当前学生是付费用户，那么可以抢占体验课，查询当前时间内只有体验课的老师
        if(empty($request['ttype'])){
            $teacher2List = empty($isEx) ? [] : $this->RClassAccess->getTeacherHaveExClass($request);
        }else{
            if($request['ttype']==1)
            {
             $teacher2List=[];
            }else{
             $teacher2List = empty($isEx) ? [] : $this->RClassAccess->getTeacherHaveExClass($request);   
            }
           
            
        }
            
            //获取可抢占老师的id数组
            $teacherId2List = empty($teacher2List) ? [] : $this->filterTeacher2List($teacher2List);
            //合并可以选课的老师
            $teacherList = array_merge($teacher1List, $teacher2List);
            
            //判断当前选中的老师是否是可排课老师
            $isAvailableTeacher = $this->checkTeacherAvailable($teacherList, $teacherId);
            
            //获取休息的老师
           $teacher3List =  $this->RClassAccess->getRestTeacher($request);
        
//           if (in_array($teacherId, $teacher3List) && $teacherId > 0) {
//                return json_encode(array('error' => '当前老师休息，请电话联系后排课'));
//            }
            
            //判断当前时间段该学生是否可以排课
            $isAvailableStudent = $this->RClassAccess->checkStudentAvailable($classInfo['student_id'], $timeStart, $timeEnd, $classId);

             if (!$isAvailableStudent) {
                return array('error' => '学生该时间段有课');
            }
            
            if (($request['left_id'] != $classInfo['left_id'] && $isFail == 0) || $isFail == 1)
            {
                //获取用户购买数量
                $left2TimesAmount = $this->RClassAccess->getClassLeftAmountCount($request['left_id']);

                //老师等级>套餐数量
                if ($teacherInfoNow['teacher_level'] > $left2TimesAmount)
                {
                    return array('error' => '剩余课时不足');
                }
            }

            if (empty($isEx) && in_array($teacherId, $teacherId2List) && $teacherId > 0) {
                return array('error' => '付费用户权限');
            }

            
            //返回提示，当前老师有课
            if ($isAvailableTeacher !== true) {
                return $isAvailableTeacher;
            }
            /*
            if (empty($isEx) || (!empty($isEx) && !in_array($teacherId, $teacherId2List))) {
                if (count($teacherList) <= $noTeacherCount) {
                    return json_encode(array('error' => '该时间段老师数量不足'));
                }
            }
            */
           

        }

        if ($this->WClassAccess->updateClassTimeEdit($classId, $request['left_id'],$isExClass, $timeStart, $timeEnd, $teacherId, $instrumentId, $marks, $isFail) > 0) {

            $this->WClassAccess->updateHistoryEdit($userId, $role, $classLeft['order_id'],$peiCardAmountNow, $isExClass, $type, $classInfo['history_id'], $isFail);

            if ($teacherId != $classInfo['teacher_id'] && !empty($classInfo['teacher_id']) && ($classInfo['status_bit'] & 2) == 2) {
                $statusBit = "status_bit=status_bit&(~2), status_bit=status_bit|4";
                $this->WClassAccess->updateClassStatusBit($classId, $statusBit);
            }

            if($request['is_red'] == 1) {
                $this->WClassAccess->updateClassStatusBit($classId, "status_bit=status_bit|8");
            }else {
               $this->WClassAccess->updateClassStatusBit($classId, "status_bit=status_bit&(~8)");
            }

            if ($isFail == 1)
            {
                $this->WClassAccess->reduceClassTimes($request['left_id'], 1);
                $this->WClassAccess->deleteClassFail($classId);
            } else {
                if ($request['left_id'] != $classInfo['left_id'])
                {
                    $this->WClassAccess->reduceClassTimes($request['left_id'], 1);
                    $this->WClassAccess->addClassTimesByLeftId($classInfo['left_id']);
                }
            }

            //付费课把体验课挤掉
            if ($sign && !empty($isEx) && in_array($teacherId, $teacherId2List) && $teacherId > 0)
            {
//                var_dump("挤掉体验课");
                
                //查询被挤掉的体验课的信息
                $classFailInfo = $this->RClassAccess->getClassFailInfo($teacherId, $timeStart, $timeEnd,$classId);

                foreach ($classFailInfo as $classFail)
                {
//                    //原来操作
//                    //假删除被强占的体验课
//                    $this->WClassAccess->updateClassTimeDelete($classFail['id']);
//                    //将历史记录中被抢占的课程是否成功状态设置为0
//                    $this->WClassAccess->updateHistory($userId, $role, $classFail['history_id']);
//                    
//                    //将被抢占的体验课还给该套餐
//                    $this->WClassAccess->addClassTimesByLeftId($classFail['left_id']);
//                    
//                    //将该记录添加到错误课表中
//                    $this->WClassAccess->addClassFail($classFail['id'], 1);
                    
                    //现在操作（将被挤掉的体验课的老师id设置为0，将该节被挤掉的体验课记录到错误课表日志中）
                    //将原本假删除的课程，由isdelete=1；改为老师的teacherid=0
                     $this->WClassAccess->updateClassInfoTeacher($classFail['id']);
                     //将被占的课程添加到错误课表日志中去
                     $this->WClassAccess->intoFailLog($classFail['id'], $classFail['student_id'], $teacherId, $userId, $role, 3);
                     
//                    //将历史记录中被抢占的课程是否成功状态设置为0
//                    $this->WClassAccess->updateHistory($userId, $role, $classFail['history_id']); 
//                    //将被抢占的体验课还给该套餐
//                    $this->WClassAccess->addClassTimesByLeftId($classFail['left_id']);
                    
                }
            }


            //两小时内修改给老师发送模板

            //如果不修改老师
//            if ($classInfo['teacher_id'] == $teacherId)
//            {
//                if (($classInfo['time_class'] - time()) <= 7200)
//                {
//                    $teacher = $this->RTeacherAccess->getTeacherTypeOpenidById($teacherId);
//
//                    if (($teacher['teacher_type'] == 2) && (!empty($teacher['open_id'])))
//                    {
//                        //给原老师发送修改通知
//                        $this->templateService->sendTeacherEditClass($classInfo['time_class'], $classInfo['time_end'], $timeStart, $timeEnd, $teacher['nick'], $teacher['open_id']);
//                    }
//                }else{
//                    if (($timeStart - time()) <= 7200)
//                    {
//                        $teacher = $this->RTeacherAccess->getTeacherTypeOpenidById($teacherId);
//
//                        //给原老师发送修改通知
//                        $this->templateService->sendTeacherEditClass($classInfo['time_class'], $classInfo['time_end'], $timeStart, $timeEnd, $teacher['nick'], $teacher['open_id']);
//                    }
//                }
//
//            }else{
//
//                //如果距离原上课时间两小时之内给原老师发送取消通知
//                if (($classInfo['time_class']-time()) <= 7200)
//                {
//                    $teacher = $this->RTeacherAccess->getTeacherTypeOpenidById($classInfo['teacher_id']);
//
//                    if (($teacher['teacher_type'] == 2) && (!empty($teacher['open_id'])))
//                    {
//                        $msg = array(
//                            'time_class' => $classInfo['time_class'],
//                            'time_end' => $classInfo['time_end'],
//                            'instrument_name' => $classInfo['instrument_name']
//                        );
//
//                        $this->templateService->sendTeacherCancelClass($msg, $teacher['nick'], $teacher['open_id']);
//                    }
//                }
//
//                //如果距离新上课时间两小时之内给新老师发送临时加课通知
//                if (($timeStart - time()) <= 7200)
//                {
//                    $teacher_new = $this->RTeacherAccess->getTeacherTypeOpenidById($teacherId);
//
//                    if (($teacher_new['teacher_type'] == 2) && (!empty($teacher_new['open_id'])))
//                    {
//                        $this->templateService->sendTeacherAddClass($timeStart, $timeEnd, $teacher_new['nick'], $teacher_new['open_id']);
//                    }
//                }
//            }


            $transaction->commit();

            LogService::OutputLog($logid, 'update', '', '编辑课');

            // 超过10点发送模板
            $isNight =  time() > strtotime('22:00') ? 1 : 0;

            return ['error' => 0, 'data' => array(
                'date' => date('Y/m/d', $timeStart),
                'isNight' => $isNight,
                'init_time' => $classInfo['time_class'],
                'edit_info' => array(
                    'time_class_old' => $classInfo['time_class'],
                    'time_end_old' => $classInfo['time_end'],
                    'teacher_id_old' => $classInfo['teacher_id'],
                    'instrument_name_old' => $classInfo['instrument_name'],
                    'time_class_new' => $timeStart,
                    'time_end_new' => $timeEnd,
                    'teacher_id_new' => $teacherId,
                    'instrument_name_new' => $instrumentId,
                    'student_id' => $classInfo['student_id']
                )
            )
            ];
        } else {
            return array('error' => '编辑失败');
        }
    }


    public function doCancelClass($request, $logid)
    {
        $userId = Yii::$app->user->identity->id;

        $classInfo = $this->RClassAccess->getClassRoomInfo($request['class_id']);

        $teacherInfo = $this->RTeacherAccess->getTeacherBaseInfo($classInfo['teacher_id']);

        $studentInfo = $this->RStudentAccess->getUserById($classInfo['student_id']);

        if ($classInfo['time_end'] < time() && $userId != 2 && $userId != 126) {
            return json_encode(array('error' => '不能取消已过时间的课程'));
        }

        if ($classInfo['status'] == 2 || $classInfo['is_deleted'] == 1) {
            return json_encode(array('error' => '过程已经取消或删除,请勿重复操作'));
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $recordId = $this->WClassAccess->addClassCancel($request);

            if ($recordId > 0) {
                if (empty($request['is_reduce'])) {
                    $this->WClassAccess->updateClassStatus($request['class_id'], $request['cancel_type'], 2);
                    $this->WClassAccess->updateClassHistoryStatus(2, $classInfo['history_id']);
                    $this->WClassAccess->addClassTimesByLeftId($classInfo['left_id']);
                } else {
                    $this->WClassAccess->updateClassStatus($request['class_id'], $request['cancel_type'], 3);
                    $this->WClassAccess->updateReduceClass($classInfo['history_id']);
                    $this->WClassAccess->reduceAcClassTimes($classInfo['left_id']);
                }

                if ($classInfo['is_send'] == 1 && !empty($teacherInfo)) {
                    $this->sendTeacherCancelClass($teacherInfo, $classInfo, $studentInfo);
                }

                $transaction->commit();

                //发送消息
                unset($request['content']);
                $request['time_cancel'] = time();
                $request['time_class'] = $classInfo['time_class'];
                $request['time_end'] = $classInfo['time_end'];
                $request['instrument_name'] = $classInfo['instrument_id'];
                $request['teacher_id'] = $classInfo['teacher_id'];

                LogService::OutputLog($logid, 'update', '', '取消课');

                // 超过10点发送模板
                $isNight =  time() > strtotime('22:00') ? 1 : 0;

                return array (
                    'message' => $request,
                    'data' =>
                        array(
                            'error' => '',
                            'data' => array(
                                'isNight' => $isNight,
                                'student_id' => $classInfo['student_id'],
                                'date_1' => date('Y/m/d', $classInfo['time_class']),
                                'date_2' => date('Y-m', $classInfo['time_class']),
                                'teacher_id' => $classInfo['teacher_id'],
                                'time_class' => $classInfo['time_class'],
                                'time_end' => $classInfo['time_end'],
                                'instrument_name' => $classInfo['instrument_id']
                            )));
            } else {
                return json_encode(array('error' => '执行失败'));
            }
        }catch (Exception $ex) {
            $transaction->rollBack();
            return json_encode(array('error' => '执行失败'));
        }
    }

    public function sendTeacherCancelClass($teacherInfo, $classInfo, $studentInfo)
    {
        $week = array(' 周日', ' 周一', ' 周二', ' 周三', ' 周四', ' 周五', ' 周六');
        $time = date('m-d', $classInfo['time_class']) . $week[date('w', $classInfo['time_class'])];
        list($urlSend, $urlUser) = $this->sendTeacherPrepare();
        $wechatUserInfo = json_decode(file_get_contents($urlUser), true);
        foreach ($wechatUserInfo['userlist'] as $user) {
            if ($teacherInfo['mobile'] == $user['mobile']) {
                $touser = $user['userid'];
                break;
            }
        }
        $text = $teacherInfo['nick'] . "老师您好，您(" . $time . ")的一节课被取消!\n\n";
        $text .= date('H:i', $classInfo['time_class']) . "-" . date('H:i', $classInfo['time_end']) . "\n{$studentInfo['nick']} " . (empty($classInfo['is_ex_class']) ? "付费课" : "体验课") . "\n\n";
        $text .= "此消息为系统自动发送，请勿回复";

        if(!empty($touser))
        {
            $content = array(
                'touser' => $touser,
                'msgtype' => 'text',
                'agentid' => Yii::$app->params['corp_id'],
                'text' => array('content' => $text)
            );

            Request::httpPost($urlSend, json_encode($content, JSON_UNESCAPED_UNICODE));
        }
        return true;
    }



    public function sendTeacherPrepare()
    {
        $corpId = Yii::$app->params['corp_id'];
        $corpSecret = Yii::$app->params['corp_secret'];
        $departmentTeacherId = Yii::$app->params['department_teacher_id'];

        $tokenInfo = json_decode(file_get_contents("https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=" . $corpId . "&corpsecret=" . $corpSecret), true);

        if (isset($tokenInfo['errcode']) && $tokenInfo['errcode'] > 0) {
            die(json_encode(array('error' => $tokenInfo['errcode']), JSON_UNESCAPED_SLASHES));
        }

        $urlSend = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=" . $tokenInfo['access_token'];
        $urlUser = "https://qyapi.weixin.qq.com/cgi-bin/user/list?access_token={$tokenInfo['access_token']}&department_id=" . $departmentTeacherId . "&status=0";
        return [$urlSend, $urlUser];
    }



    /**
     * 删除错误课程
     * @param $classId
     * @param $logid
     * @return mixed
     */
    public  function  deleteClassFail($classId, $logid)
    {
        $fail = $this->RClassAccess->getClassFailBaseInfo($classId);

        if(!empty($fail))
        {
            $this->WClassAccess->deleteClassFail($classId);

            $classInfo = $this->RClassAccess->getSendClassInfo($classId);

            $data = array(
                'error' => '',
                'data' => array(
                    'student_id' => $classInfo['student_id'],
                    'date_1' => date('Y/m/d', $classInfo['time_class']),
                    'date_2' => date('Y-m', $classInfo['time_class'])
                ));

            LogService::OutputLog($logid,'update',serialize($classId),'单个删除错误课');
        }else {
            $data = array('error' => '课程已被删除,请勿重复操作');
        }

        return json_encode($data);

    }


    function httpPost($url, $params = [], $headerMap = [])
    {
        $headers = array();
        foreach ($headerMap as $key => $value) {
            $headers[] = $key . ':' . $value;
        }
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, @$_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, TRUE); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "miaoke:music111");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        //curl_setopt($curl, CURLOPT_HEADER, 1);
        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            return false;
        }
        curl_close($curl); // 关键CURL会话
        return $tmpInfo; // 返回数据
    }

    public function deleteClass($classId, $logid)
    {
        $userId = Yii::$app->user->identity->id;
        $role = 2;

        $classInfo = $this->RClassAccess->getRowById($classId);

        if ($classInfo['status'] == 2 || $classInfo['is_deleted'] == 1) {
            return json_encode(array('error' => '课程已取消或删除,请勿重复操作'));
        }

        $transaction = Yii::$app->db->beginTransaction();

        if ($this->WClassAccess->updateClassTimeDelete($classId))
        {
            if ($this->WClassAccess->updateHistory($userId, $role, $classInfo['history_id']))
            {
                if ($this->WClassAccess->addClassTimesByLeftId($classInfo['left_id']))
                {
                    $transaction->commit();

                    LogService::OutputLog($logid, 'update', '', '删除课');

                    return array (
                        'message' => array(
                            'instrument_name' => $classInfo['instrument_name'],
                            'time_class' => $classInfo['time_class'],
                            'time_end' => $classInfo['time_end'],
                            'deleted_time' => time(),
                            'teacher_id' => $classInfo['teacher_id']

                        ),
                        'data' =>
                            array(
                                'error' => '',
                                'data' => array(
                                    'student_id' => $classInfo['student_id'],
                                    'date_1' => date('Y/m/d', $classInfo['time_class']),
                                    'date_2' => date('Y-m', $classInfo['time_class'])
                                )));
                } else {
                    $transaction->rollback();
                    return json_encode(array('error' => '删除失败'));
                }
            } else {
                $transaction->rollback();
                return json_encode(array('error' => 'update_history_failed'));
            }
        } else {
            $transaction->rollback();
            return json_encode(array('error' => 'delete_class_failed'));
        }
    }



    /**
     * 添加赠送课程
     */
    public function doGiveClass($request, $logid)
    {
        $userId = Yii::$app->user->identity->id;
        $role = 2;

        $student = $this->RStudentAccess->getWechatRowByOpenId($request['open_id']);

        if(!empty($student))
        {
            $historyId = $this->WClassAccess->addBuyClassGoods($userId, $role, $student['uid'], $request['instrument_id'], 0, 1, $request['amount'], $request['class_type'], 1, 1, 0, 0, $request['reason_type'], 0, $request['reason']);
            if ($historyId > 0)
            {
                $price = 4;

                $count = $this->RClassAccess->getGivClassCount($student['uid'], $request['instrument_id'], $request['class_type']);

                $give_class_count = empty($count) ? '' : $count;


                if ($this->WClassAccess->addGiveClassTimes($student['uid'], $request['instrument_id'], $request['class_type'], $price, $request['amount'], $give_class_count)) {
                    LogService::OutputLog($logid, 'insert', '', '赠送课程');
                    return array('error' => '', 'data' => array('uid' => $student['uid']));
                } else {
                    return json_encode(array('error' => '增加课时失败'));
                }
            } else {
                return json_encode(array('error' => '添加送课历史失败'));
            }
        }else {
            return json_encode(array('error' => '该用户还未注册'));
        }
    }
/*
    private function getClassLengthByClassType($type)
    {
        switch ($type) {
            case 1 :
                return '25分钟';
            case 2 :
                return '45分钟';
            case 3 :
                return '50分钟';
        }
    }

    private function buildMessage($openid, $templateId, $firstValue, $key1word, $key2word, $key3word, $remark)
    {
        $data = array(
            'first' => array('value' => $firstValue),
            'keyword1' => $key1word,
            'keyword2' => $key2word,
            'keyword3' => $key3word,
            'remark' => array('value' => $remark)
        );

        $message = array(
            'touser' => $openid,
            'template_id' => $templateId,
            'url' => 'http://wx.pnlyy.com/weixin/class-redirect',
            'data' => $data
        );
        return $message;
    }
*/

    public function getDoStudentFixTime($openId, $fixInfo = '', $logid)
    {
        $student = $this->RChatAccess->getWechatAccByExist($openId);

        if(!empty($student))
        {
            //添加学生固定时间
            $error = $this->doStudentFixTime($student->uid, $fixInfo);

            if (!empty($error))
            {
                return json_encode(array('error' => $error));
            }
        }else {
            return json_encode(array('error' => '该学生未注册或未绑定微信'));
        }

        LogService::OutputLog($logid, 'add', '', '学生固定时间');

        return json_encode(array('error' =>''));
    }

    public function getDoStudentFixTimeClass($openId, $fixInfo = '', $logid)
    {
        $student = $this->RChatAccess->getWechatAccByExist($openId);

        if(!empty($student))
        {
            if (empty($fixInfo))
            {
                return json_encode(array('error' => '请选择仅保存'));

            }else{

                $transaction = Yii::$app->db->beginTransaction();

                //添加学生固定时间
                $error = $this->doStudentFixTime($student->uid, $fixInfo);

                if (!empty($error))
                {
                    $transaction->rollback();

                    return json_encode(array('error' => $error));
                }

                //排课
                $error = $this->addFixClass($student->uid, $fixInfo);

                if (!empty($error))
                {
                    $transaction->rollback();
                    return json_encode(array('error' => $error));
                }

                $transaction->commit();

                LogService::OutputLog($logid, 'add', '', '学生固定时间并排课');

                return json_encode(array('error' => ''));

            }
        }else {
            return json_encode(array('error' => '该学生未注册或未绑定微信'));
        }
    }

    /**
     * @author xl
     * 编辑学生固定时间
     */
    public function doStudentFixTime($student_id, $fixInfo='')
    {
        if (!empty($fixInfo))
        {
            foreach ($fixInfo as &$row)
            {
                if (empty($row['teacher_id']))
                {
                    $error_message = '周'.$row['week'].' '.$row['time'].' '.'请选择老师';
                    return $error_message;
                }

                $timeArr = explode(':', $row['time']);

                if ($timeArr[0] < 0 || $timeArr[0] >= 24 || ($timeArr[1] != '00' && $timeArr[1] != '30')) {
                    return '时间错误';
                }

                $row['time_bit'] = BinaryDecimal::getStudentFixTimeBit($timeArr[0], $timeArr[1], $row['class_type']);

                //锁表
                $this->WClassAccess->lockStudentFixTime();

                $student_time = $this->RClassAccess->studentTimeExit($row['teacher_id'], $row['week'], $student_id);

                foreach($student_time as $bit)
                {
                    $num = 1;
                    for($i = 1; $i <= 49; $i++)
                    {
                        $bit_per = ($bit & $num) == $num ? 1 : 0;
                        $time_per = ($row['time_bit'] & $num) == $num ? 1 : 0;

                        if($bit_per == 1 && $time_per == 1)
                        {
                            $error_message = '周'.$row['week'].' '.$row['time'].' '.'已有学生';
                            return $error_message;
                        }

                        $num = $num << 1;
                    }
                }

                $time_class = strtotime(date('Y-m-d',time()).' '.$row['time']);

                if ($row['class_type'] == 1)
                {
                    $time_end = $time_class + 1500;
                }elseif ($row['class_type'] == 2)
                {
                    $time_end = $time_class + 2700;
                }else{
                    $time_end = $time_class + 3000;
                }

                $av_list = $this->salaryCompute->getAvailableFixWeek($row['week'], $time_class, $time_end)['data'];

                if (!in_array($row['teacher_id'], $av_list))
                {
                    $error_message = '周'.$row['week'].' '.$row['time'].' '.'老师休息';

                    return $error_message;
                }
            }

            $this->WClassAccess->deleteStudentFixTime($student_id);
            $this->WClassAccess->addStudentFixTime($student_id, $fixInfo);

            //解表
            $this->WClassAccess->unLockStudentFixTime();

            return '';

        } else {
            $this->WClassAccess->deleteStudentFixTime($student_id);

            return '';
        }
    }

    public function addFixClass($student_id, $fixInfo)
    {
        foreach ($fixInfo as $k => $fix)
        {
            $num1[$k] = $fix['week'];
            $num2[$k] = strtotime('2017-4-1'.' '.$fix['time']);
        }

        array_multisort($num1, SORT_ASC, $num2, SORT_ASC, $fixInfo);

        $fixInfo_new = array();

        foreach ($fixInfo as $row)
        {
            $fixInfo_new[$row['class_type'].$row['instrument_type']][] = $row;
        }

        $left_list = $this->RClassAccess->getBuyRemainAmount($student_id);

        $time_type = 0;

        $instrument_type = 0;

        $last_class = 0;

        $last_key = 0;

        $tag = false;

        if (!empty($left_list))
        {
//            $transaction = Yii::$app->db->beginTransaction();

            foreach ($left_list as $left)
            {
                if (!isset($fixInfo_new[$left['time_type'].$left['instrument_id']]))
                {
                    continue;
                }else{

                    $tag = true;

                    $count = count($fixInfo_new[$left['time_type'].$left['instrument_id']]);

                    if (($left['time_type'] == $time_type) && ($left['instrument_id'] == $instrument_type))
                    {
                        while ($last_key < $count-1)
                        {
                            if ($left['amount'] == 0)
                            {
                                break;
                            }else{

                                if ($fixInfo_new[$left['time_type'].$left['instrument_id']][$last_key+1]['week'] == date('w', $last_class))
                                {
                                    $last_class = strtotime(date('Y-m-d', $last_class). '' .$fixInfo_new[$left['time_type'].$left['instrument_id']][$last_key+1]['time']);
                                }else{
                                    $week_string = $this->getWeekString($fixInfo_new[$left['time_type'].$left['instrument_id']][$last_key+1]['week']);

                                    $last_class = strtotime(date('Y-m-d',strtotime("next $week_string", $last_class)) . '' .$fixInfo_new[$left['time_type'].$left['instrument_id']][$last_key+1]['time']);
                                }

                                $time_end1 = $last_class + $this->getClassLong($left['time_type']);

                                //添加课程
                                $error = $this->getDoFixClass($left['id'], $student_id, $fixInfo_new[$left['time_type'].$left['instrument_id']][$last_key+1]['teacher_id'], $left['instrument_id'], $last_class, $time_end1, $left['time_type']);
                                if (!empty($error))
                                {
//                                    $transaction->rollback();

                                    return $error;
                                }

                                $last_key ++;
                                $left['amount'] --;
                            }
                        }

                        $current_day = $last_class;
                        $time_type = $left['time_type'];
                        $instrument_type = $left['instrument_id'];
                    }else{
                        $current_day = time();
                        $time_type = $left['time_type'];
                        $instrument_type = $left['instrument_id'];
                    }
                    
                    foreach ($fixInfo_new[$left['time_type'].$left['instrument_id']] as $key => $item)
                    {
                        $round = intval($left['amount'] / $count);   //取整
                        $over = $left['amount'] % $count;    //取余

                        $week_string = $this->getWeekString($item['week']);
                        $time_class = strtotime(date('Y-m-d', strtotime("next $week_string", $current_day)) . '' . $item['time']);

                        if ($time_class > $last_class) {
                            $last_class = $time_class;
                            $last_key = $key;
                        }

                        $time_end = $time_class + $this->getClassLong($item['class_type']);

                        //添加课程
                        $error = $this->getDoFixClass($left['id'], $student_id, $item['teacher_id'], $left['instrument_id'], $time_class, $time_end, $left['time_type']);
                        if (!empty($error))
                        {
//                            $transaction->rollback();

                            return $error;
                        }

                        if ($key < $over) {
                            $num = $round + 1;

                            for ($i = 1; $i < $num; $i++) {
                                $time_class += 604800;

                                if ($time_class > $last_class) {
                                    $last_class = $time_class;
                                    $last_key = $key;
                                }

                                $time_end += 604800;

                                //添加课程
                                $error = $this->getDoFixClass($left['id'], $student_id, $item['teacher_id'], $left['instrument_id'], $time_class, $time_end, $left['time_type']);
                                if (!empty($error))
                                {
//                                    $transaction->rollback();

                                    return $error;
                                }
                            }

                        } else {
                            $num = $round;

                            for ($i = 1; $i < $num; $i++) {
                                $time_class += 604800;

                                if ($time_class > $last_class) {
                                    $last_class = $time_class;
                                    $last_key = $key;
                                }

                                $time_end += 604800;

                                //添加课程
                                $error = $this->getDoFixClass($left['id'], $student_id, $item['teacher_id'], $left['instrument_id'], $time_class, $time_end, $left['time_type']);
                                if (!empty($error))
                                {
//                                    $transaction->rollback();

                                    return $error;
                                }
                            }
                        }
                    }
                }
            }

            if ($tag === false)
            {
//                $transaction->rollback();

                return '没有套餐可排课';
            }else{

//                $transaction->commit();

                return '';
            }
        }else{
            return '没有剩余套餐可排课';
        }
    }

    public function getClassLong($class_type)
    {
        if ($class_type == 1)
        {
            return 1500;
        }elseif ($class_type == 2)
        {
            return 2700;
        }else{
            return 3000;
        }
    }

    public function getDoFixClass($left_id, $student_id, $teacher_id, $instrument_id, $time_class, $time_end, $class_type)
    {
        $remain_amount = $this->RClassAccess->getRemainAmountByLeftId($left_id);

        if ($remain_amount != 0)
        {
            $role =  Yii::$app->user->identity->role;
            $userId = Yii::$app->user->identity->id;

//            $transaction = Yii::$app->db->beginTransaction();

            //将套餐内的课程的数量-1
            if (!$this->WClassAccess->reduceClassTimes($left_id, 1)) {
//                $transaction->rollback();//事务回滚
                return '更新课时失败';
            }

            //添加课程信息
            $classId = $this->WClassAccess->addClassTime($left_id, $instrument_id, 0, $time_class, $time_end, $student_id, $teacher_id, '', 0);
            if (!($classId > 0)) {
//                $transaction->rollback();
                return '添加课程失败';
            }

            //序列化这个课程id
            $detail = serialize(array('class_id' => $classId));
            //添加课程编辑历史记录
            $historyId = $this->WClassAccess->addHistory($userId, $role, $student_id, $instrument_id, 1, 0, $class_type, 0, 1, 1, '', $detail);
            if (!($historyId > 0)) {
//                $transaction->rollback();
                return '添加历史失败';
            }

            if (!$this->WClassAccess->updateHistoryIdByClassId($classId, $historyId)) {
//                $transaction->rollback();
                return '更新历史失败';
            }


            if (!$this->WClassAccess->addCounts(strtotime(date('Y-m-d', $time_class)), $class_type, 1, $student_id)) {
//                $transaction->rollback();
                return '添加统计失败';
            }

            $timeStart = strtotime(date('Y-m-d', $time_class));
            $timeEnd = $timeStart + 86400;

            $classFailInfo = $this->RClassAccess->getClassFailByStudentFix($teacher_id, $student_id, $timeStart, $timeEnd, $classId);

            foreach ($classFailInfo as $classFail)
            {
                $class_fail_bit = BinaryDecimal::getClassBit($classFail['time_class'], $classFail['time_end']);

                $class_bit = BinaryDecimal::getClassBit($time_class,  $time_end);

                if (($class_fail_bit & $class_bit) > 0)
                {
                    //将原本假删除的课程，由isdelete=1；改为老师的teacherid=0
                    $this->WClassAccess->updateClassInfoTeacherByFix($classFail['id']);

                    //将被占的课程添加到错误课表日志中去
                    $this->WClassAccess->intoFailLog($classFail['id'], $classFail['student_id'], $classFail['teacher_id'], $userId, $role, 4);
                }
            }

//            $transaction->commit();

            return '';
        }

        return '';
    }

    public function getWeekString($week)
    {
        switch ($week)
        {
            case 1:
                return "Monday";
                break;
            case 2:
                return "Tuesday";
                break;
            case 3:
                return "Wednesday";
                break;
            case 4:
                return "Thursday";
                break;
            case 5:
                return "Friday";
                break;
            case 6:
                return "Saturday";
                break;
            default:
                return "Sunday";
        }
    }
}