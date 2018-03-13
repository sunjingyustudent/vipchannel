<?php
/**
 * Created by phpStorm.
 * User: xl
 * Date: 2017/2/23
 * Time: 18:20
 */
namespace common\logics\teacher;

use common\models\music\UserTeacherSchool;
use yii\base\Object;
use Yii;

class TrainLogic extends Object implements ITrain
{
    /** @var  \common\sources\read\teacher\TrainAccess $RTrainAccess */
    private $RTrainAccess;
    /** @var  \common\sources\write\teacher\TrainAccess $WTrainAccess*/
    private $WTrainAccess;

    public function init()
    {
        $this->RTrainAccess = Yii::$container->get('RTrainAccess');
        $this->WTrainAccess = Yii::$container->get('WTrainAccess');

        parent::init();
    }

    /**
     * @param $filter
     * @return array
     */

    public function selectTraceList($filter)
    {
        return $this->RTrainAccess->selectTraceList($filter);
    }

    /**
     * @param $filter
     * @return array
     */

    public function selectFailedList($filter){

        return $this->RTrainAccess->selectFailedList($filter);
    }

    /**
     * @param $filter
     * @return array
     */

    public function getFailedList($filter, $page_num){
        return $this->RTrainAccess->getFailedList($filter,$page_num);
    }

    /**
     * @param $filter string
     * @param $page_num int
     * @return array
     */
    public function getTraceList($filter, $page_num)
    {
        return $this->RTrainAccess->getTraceList($filter, $page_num);
    }

    /**
     * @param $filter
     * @return array
     */

    public function selectTraceTeacherList($filter)
    {
        return $this->RTrainAccess->selectTraceTeacherList($filter);
    }

    /**
     * @param $filter string
     * @param $page_num int
     * @return array
     */
    public function getTraceTeacherList($filter, $page_num)
    {
        $user_list = $this->RTrainAccess->getTraceTeacherList($filter, $page_num);

//        print_r($user_list);exit();

        if (!empty($user_list))
        {
            foreach ($user_list as $key => $item)
            {
//                print_r($item);exit();

                if (empty($item['class_id']))
                {
                    $user_list[$key]['class_name'] = '空';
                }else{
                    $user_list[$key]['class_name'] = $this->RTrainAccess->getClassInfoById($item['class_id'])['name'];
                }
            }
        }

        return $user_list;
    }

    /**
     * @param $user_id int
     * @return array
     * 根据用户id获取乐谱
     */
    public function getSchoolInstrument($user_id)
    {
        return $this->RTrainAccess->getSchoolInstrument($user_id);
    }


    /**
     * @param $filter
     * @return array
     */

    public function selectTraceQuitList($filter)
    {
        return $this->RTrainAccess->selectTraceQuitList($filter);
    }

    /**
     * @param $filter string
     * @param $page_num int
     * @return array
     */
    public function getTraceQuitList($filter, $page_num)
    {
        return $this->RTrainAccess->getTraceQuitList($filter, $page_num);
    }

    /**
     * @param $user_id int
     * @param $instrumentLevel array
     * @param $status int
     * @param $listen_score
     * @param $line_score
     * @param $age_score
     * @param $num_score
     * @param $skill_score
     * @param $type_score
     * @param $opern_score
     * @return json
     */
    public function addTraceInstrument($user_id, $instrumentLevel, $status, $listen_score, $line_score, $age_score, $num_score, $skill_score, $type, $opern, $opern_score, $command, $command_score)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try{

            foreach($instrumentLevel as $instrument => $level) {
                $this->WTrainAccess->addSchoolInstrument($user_id, $level['instrument_id'],  $level['grade'], $level['level']);
            }
            //$this->WTrainAccess->updateTrace($user_id, $status);
            $this->WTrainAccess->updateTraceByScore($user_id, $status,$listen_score, $line_score, $age_score, $num_score, $skill_score, $type, $opern, $opern_score, $command, $command_score);
            $transaction->commit();
        }catch (Exception $e) {
            $transaction->rollBack();
            return json_encode(array('error' => '添加失败'));
        }
        return json_encode(array('error' => ''));

    }


    /**
     * @param $user_id int
     * @param $instrumentLevel array
     *
     * @return json
     * 修改等级
     */
    public function editTraceInstrument($user_id, $instrumentLevel)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try{
            //删除
            $this->WTrainAccess->deleteSchoolInstrument($user_id);

            foreach($instrumentLevel as $instrument => $level) {
                $this->WTrainAccess->addSchoolInstrument($user_id, $level['instrument'],  $level['level_out']);
            }

            $transaction->commit();
        }catch (Exception $e) {
            $transaction->rollBack();
            return json_encode(array('error' => '添加失败'));
        }
        return json_encode(array('error' => ''));

    }

    /*
     * @param $teacher_id int
     * @return array
     */
    public function getSchoolTeacherInfoById($teacher_id)
    {
        return UserTeacherSchool::find()
            ->where('id = :teacher_id',[':teacher_id'=>$teacher_id])
            ->asArray()
            ->one();
    }

    /**
     * @param $trace_id int
     * @return array
     */
    public function selectTraceDetail($trace_id)
    {
        return $this->RTrainAccess->selectTraceDetail($trace_id);
    }

    /**
     * @param $trace_id int
     * @param $type 1删除 2废弃
     * @return array
     */
    public function updateTrace($trace_id, $type)
    {
        return $this->WTrainAccess->updateTrace($trace_id, $type);
    }

    public function getAddUserList()
    {
        $list1 = $this->RTrainAccess->getAddUserList();

        $list2 = $this->RTrainAccess->getEndClassUserList(0);

        $list = array_merge($list1, $list2);

        return array('error' => 0, 'data' => $list);
    }

    public function showMembersEdit($class_id)
    {
        $list1 = $this->RTrainAccess->getAddUserList();
        $list2 = $this->RTrainAccess->getEndClassUserList($class_id);
        $list3 = $this->RTrainAccess->getClassMembersById($class_id);

        $list = array_merge($list1,$list2,$list3);

        return array('error' => 0, 'data' => $list);
    }

    public function addClass($request)
    {
        $time_start = strtotime($request['time_start']);
        $time_end = strtotime($request['time_end']);
        $name = $request['name'];

        $member_list = $request['member_list'];

        if ($time_start <= time())
        {
            return array('error' => '开始时间不能小于或等于创建时间', 'data' => '');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {

            $last_id = $this->WTrainAccess->addClass($name, $time_start, $time_end);


            if (!empty($member_list))
            {
                foreach ($member_list as $item)
                {
                    $this->WTrainAccess->updateUserTeacherClassId($item, $last_id);
                }
            }

            $transaction->commit();

            return array('error' => 0, 'data' => '');

        } catch (Exception $e) {

            $transaction->rollBack();
            return array('error' => '添加失败', 'data' => '');
        }
    }

    public function getClassCount($filter)
    {
        $count = $this->RTrainAccess->getClassCount($filter);

        return array('error' => 0, 'data' => $count);
    }

    public function getClassList($filter, $page_num)
    {
        $list = $this->RTrainAccess->getClassList($filter, $page_num);

        foreach ($list as $key => $item)
        {
            if ($item['time_end'] + 86400 < time())
            {
                $list[$key]['is_end'] = 1;
            }else{
                $list[$key]['is_end'] = 0;
            }
        }

        return array('error' => 0, 'data' => $list);
    }

    /**
     * @param $request
     * @return json
     */
    public function editTrace($request)
    {
        $trace_id = $request['id'];
        $name = $request['name'];
        $mobile = $request['mobile'];
        $school = $request['school'];
        $major = $request['major'];
        $grade = $request['grade'];
        $hsPad = $request['hasPad'];

        $instrument = empty($request['instrument'])? '' : $request['instrument'];

        $transaction = Yii::$app->db->beginTransaction();
        try{

            $this->WTrainAccess->editTrace($trace_id, $name, $mobile, $school, $major, $grade, $hsPad);
            //新增
            if (!empty($instrument)) {
                //删除
                $this->WTrainAccess->deleteSchoolInstrument($trace_id);
                foreach($instrument as $instrument => $level) {
                    $this->WTrainAccess->addSchoolInstrument($trace_id, $level['instrument'], $level['level_out']);
                }
            }

            $transaction->commit();
        }   catch (Exception $e) {
            $transaction->rollBack();
            return 0;
        }
        return 1;
    }

    /**
     * @param $request
     * @return json
     */
    public function editTraceNew($request)
    {
        $trace_id = $request['id'];
        $name = $request['name'];
        $mobile = $request['mobile'];
        $school = $request['school'];
        $major = $request['major'];
        $grade = $request['grade'];
        $hsPad = $request['hasPad'];
        $type = $request['type'];
        $opern = $request['opern'];
        $opern_score = $request['opern_score'];
        $command = $request['command'];
        $command_score = $request['command_score'];
        $listen_score = $request['listen_score'];
        $line_score = $request['line_score'];
        $age_score = $request['age_score'];
        $num_score = $request['num_score'];
        $skill_score = $request['skill_score'];
        $instrumentLevel = $request['instrumentLevel'];

        $transaction = Yii::$app->db->beginTransaction();
        try{

            $this->WTrainAccess->editTraceNew($trace_id, $name, $mobile, $school, $major, $grade, $hsPad, $type, $opern, $opern_score, $command, $command_score, $listen_score, $line_score, $age_score, $num_score, $skill_score);
            //新增
            if (!empty($instrumentLevel)) {
                //删除
                $this->WTrainAccess->deleteSchoolInstrument($trace_id);
                foreach($instrumentLevel as $instrument => $level) {
                    $this->WTrainAccess->addSchoolInstrument($trace_id, $level['instrument_id'], $level['grade'],$level['level']);
                }
            }

            $transaction->commit();
        }   catch (Exception $e) {
            $transaction->rollBack();
            return 0;
        }
        return 1;
    }

    public function getClassInfoById($id)
    {
        $class_info = $this->RTrainAccess->getClassInfoById($id);

        $check_list = $this->RTrainAccess->getClassMembersById($id);

        $list1 = $this->RTrainAccess->getAddUserList();

        $list2 = $this->RTrainAccess->getEndClassUserList($id);

        $no_check_list = array_merge($list1,$list2);

        return array('class_info' => $class_info, 'check_list' => $check_list, 'no_check_list' => $no_check_list);
    }

    public function editClass($request)
    {
        $cid = $request['cid'];
        $time_start = strtotime($request['time_start']);
        $time_end = strtotime($request['time_end']);
        $name = $request['name'];

        $member_list = $request['member_list'];

        $time_created = $this->RTrainAccess->getClassCreatedTime($cid);

        if ($time_start <= $time_created)
        {
            return array('error' => '开始时间不能小于或等于创建时间', 'data' => '');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {

            $this->WTrainAccess->editClassById($cid, $name, $time_start, $time_end);

            $this->WTrainAccess->updateAllClassIdByClassId($cid);

            if (!empty($member_list))
            {
                foreach ($member_list as $item)
                {
                    $this->WTrainAccess->updateUserTeacherClassId($item, $cid);
                }
            }

            $transaction->commit();

            return array('error' => 0, 'data' => '');

        } catch (Exception $e) {

            $transaction->rollBack();
            return array('error' => '添加失败', 'data' => '');
        }
    }

    public function deleteClass($class_id)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {

            $this->WTrainAccess->deleteClass($class_id);

            $this->WTrainAccess->updateAllClassIdByClassId($class_id);

            $transaction->commit();

            return array('error' => 0, 'data' => '');

        } catch (Exception $e) {

            $transaction->rollBack();
            return array('error' => '删除失败', 'data' => '');
        }
    }

    public function getAllotClass($uid)
    {
        $class_list = $this->RTrainAccess->getAllotClass($uid);

        return $class_list;
    }

    public function allotClass($uid, $class_id)
    {
        $re = $this->WTrainAccess->allotClass($uid, $class_id);

        if ($re > 0 )
        {
            return array('error' => 0, 'data' => '');
        }else{
            return array('error' => '分配失败', 'data' => '');
        }
    }

    public function showMemberDetail($cid)
    {
        $list = $this->RTrainAccess->getClassMembersById($cid);

        return $list;
    }

    public function getSchool($schoolId=0)
    {
        if($schoolId == 0){
            $schoolList = $this->RTrainAccess->getSchoolList();
        }
        else{
            $schoolList = $this->RTrainAccess->getSchoolById($schoolId);
        }

        return $schoolList;
    }

    public function getSchoolInfo($schoolId=0)
    {
        if($schoolId == 0){
            $schoolList = $this->RTrainAccess->getSchoolList();
        }
        else{
            $schoolList = $this->RTrainAccess->getSchoolById($schoolId);
        }

        return $schoolList;
    }

    public function editSchool($request)
    {
        $cid = $request['school_id'];
        $name = $request['name'];
        $time_updated = time();
        try {

            $this->WTrainAccess->editSchoolById($cid, $name, $time_updated);

            return array('error' => 0, 'data' => '');

        } catch (Exception $e) {

            return array('error' => '编辑失败', 'data' => '');
        }

    }

    public function addSchool($request)
    {
        $name = $request['name'];
        $time_created = time();
        $time_updated = time();
        try {

            $this->WTrainAccess->addSchool($name, $time_created, $time_updated);

            return array('error' => 0, 'data' => '');

        } catch (Exception $e) {

            return array('error' => '添加失败', 'data' => '');
        }
    }
}