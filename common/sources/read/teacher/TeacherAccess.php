<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 16/12/16
 * Time: 10:01
 */
namespace common\sources\read\teacher;

use common\models\music\ClassRoom;
use common\models\music\Complain;
use common\models\music\RewardRuleId;
use common\models\music\SalesFeedback;
use common\models\music\StatisticsTeacherRest;
use common\models\music\TeacherInfo;
use common\models\music\TeacherRewardRule;
use common\models\music\Timetable;
use common\models\music\UserTeacher;
use common\models\music\UserTeacherInstrument;
use common\models\music\TeacherInstrument;
use Yii;
use yii\db\ActiveRecord;
use common\models\music\BasePlace;
use crm\models\teacher\TeacherBean;
use crm\models\course\UserTeacherBean;
use common\models\music\RewardRecord;
use common\models\music\SalaryChangeLog;
use common\models\music\TeacherOvertime;
use common\models\music\StudentFixTime;
use common\models\music\School;
Class TeacherAccess implements ITeacherAccess
{

    /**
     * 查询课程信息的进入界面的可选老师信列表
     * @author 王可
     */
    public function  getCourseIndex(){
        $all_arr = TeacherBean::find()
            ->select('id,nick')
            ->where('is_disabled = 0')
            ->asArray()
            ->all();

        return $all_arr;
    }


    /**
     * 搜索老师输入框中选择老师
     * @author 王可
     */
    public function searchTeacherName($name){
        $query = TeacherBean::find()
            ->select('id,nick')
            ->where('is_disabled = 0')
            ->andWhere("nick like '%".$name."%'")
            ->asArray()
            ->all();

        return $query;
    }

    public function queryTeacherTodayclass($startTime,$endTime){
        $query = UserTeacherBean::find()
            ->select('user_teacher.id,user_teacher.nick,c.counts')
            ->leftJoin('(select teacher_id, COUNT(id) as counts from class_room where status != 2 and is_deleted = 0 AND time_class >= ' . $startTime . ' AND time_class < ' . $endTime . ' GROUP BY teacher_id) as c','c.teacher_id = user_teacher.id')
            ->where('user_teacher.is_disabled = 0 and c.counts > 0')
            ->orderBy('c.counts desc')
            ->asArray()
            ->all();

        return $query;
    }

    public function geTeacherById($id){
        $data = UserTeacher::findOne($id);
        return $data;
    }


    public function getInstrumentByTeacherId($teacher_id)
    {
        return UserTeacherInstrument::find()
            ->select('user_teacher_instrument.*, instrument.name as name ')
            ->leftJoin('instrument','user_teacher_instrument.instrument_id = instrument.id')
            ->where('user_teacher_instrument.user_id = :userID',[':userID' => $teacher_id])
            ->asArray()
            ->all();
    }

    public function getTeacherMaxSalaryTime($teacher_id, $timeEnd)
    {
        $sql = "SELECT max(salary_time) FROM salary_change_log WHERE teacher_id = :teacher_id AND salary_time < :timeEnd";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':teacher_id' => $teacher_id, ':timeEnd' => $timeEnd])
            ->queryScalar();
    }

    public function getTeacherInfo($type, $level)
    {
        $teacher = UserTeacher::find()
            ->alias('u')
            ->select('u.id, u.nick, u.mobile')
            ->leftJoin('user_teacher_instrument as ui','ui.user_id = u.id')
            ->where('u.is_disabled = 0 and u.is_formal = 1 and is_test = 0')
            ->andWhere('ui.type = 1 and ui.instrument_id = :type',[':type' => $type]);

        if($level == 1)
        {
            $teacher ->andWhere('(ui.level = 1)');

        }elseif($level == 2)
        {
            $teacher ->andWhere('(ui.level = 2 )');

        }elseif ($level == 3)
        {
            $teacher ->andWhere('(ui.level = 3)');
        }else{
            $teacher ->andWhere('(ui.level = 4)');

        }

        return $teacher->groupBy('u.id')->all();

    }

    public function getTeacherFixTimeByWeek($teacher_id, $week)
    {
        $sql = "SELECT CONV(time_bit,2,10) AS time_bit FROM teacher_info WHERE teacher_id = :teacher_id AND week = :week";

        return Yii::$app->db->createCommand($sql)
                ->bindValues([
                    ':teacher_id' => $teacher_id,
                    ':week' => $week,
                ])
            ->queryScalar();
    }


    public function getTeacherDayTime($teacher_id, $time)
    {
        $sql = "SELECT CONV(time_bit,2,10) AS time_bit FROM timetable WHERE user_id = :teacher_id AND time_day = :time";

        return Yii::$app->db->createCommand($sql)
                ->bindValues([
                    ':teacher_id' =>$teacher_id,
                    ':time' => $time])->queryScalar();
    }

    // 获取老师工资昵称
    public  function getTeacherWagesList()
    {
        return UserTeacher::find()
            ->alias('u')
            ->select('id,nick')
            ->where('is_formal = 1 AND is_disabled = 0 and is_test = 0')
            ->asArray()
            ->all();
    }


    // 统计老师数量
    public function getTeacherCount()
    {
        return UserTeacher::find()
            ->where('is_formal = 1 AND is_disabled = 0 and is_test = 0')
            ->count();
    }

    public function getTeacherFixTime($teacher_id)
    {
        $sql = "SELECT week, CONV(time_bit,2,10) AS time_bit FROM teacher_info WHERE teacher_id = :teacher_id";

        return Yii::$app->db->createCommand($sql)
                ->bindValue('teacher_id',$teacher_id)
                ->queryAll();
    }

    public function getTeacherSalary($teacher_id, $salary_time)
    {
        $sql = "SELECT salary_after FROM salary_change_log WHERE salary_time = :salary_time AND teacher_id = :teacher_id ORDER BY time_created DESC LIMIT 1";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':teacher_id' => $teacher_id,
                ':salary_time' => $salary_time
            ])->queryScalar();
    }

    public function getMonthSalaryList($teacher_id, $timeEnd)
    {
        $sql = "SELECT DISTINCT salary_time FROM salary_change_log WHERE teacher_id = :teacher_id  AND salary_time < :timeEnd ORDER BY salary_time DESC ";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':teacher_id' => $teacher_id, ':timeEnd' => $timeEnd])
            ->queryColumn();
    }


    public function getTeacherPageByName($filter)
    {
        return UserTeacher::find()
            ->select('id, nick')
            ->where('is_disabled = 0 and is_formal = 1 and is_test = 0')
            ->andWhere((empty($filter) ? "" : "(mobile like '%{$filter}%' OR nick like '%{$filter}%')"))
            ->asArray()
            ->all();
    }

    public function getTeacherListByName($filter, $page_num)
    {
        return UserTeacher::find()
            ->select('id, nick')
            ->where('is_disabled = 0 and is_formal = 1 and is_test = 0')
            ->andWhere((empty($filter) ? "" : "(mobile like '%{$filter}%' OR nick like '%{$filter}%')"))
            ->offset(($page_num-1) * 10)
            ->limit(10)
            ->asArray()
            ->all();
    }

    // 获取月爽约数量
    public function getMonthMissCount($start,$end,$name,$type)
    {
        return  UserTeacher::find()
            ->alias('t')
            ->leftJoin('(SELECT text,teacher_id,money FROM reward_record WHERE month_time >=:start AND month_time <=:end AND type = 6) AS r','r.teacher_id = t.id')
            ->where($type.(empty($name) ? '' : " AND t.nick LIKE '%$name%'"),[':start' => $start,':end' => $end])
            ->count();
    }


    // 获取月爽约内容
    public function getMonthMissList($start,$end,$name,$type,$num)
    {
        return  UserTeacher::find()
            ->alias('t')
            ->select('t.id,t.nick,t.mobile,text,money,prefix')
            ->leftJoin('(SELECT teacher_id,text,money,prefix FROM reward_record WHERE month_time >=:start AND month_time <=:end AND type = 6) AS r','r.teacher_id = t.id')
            ->where($type.(empty($name) ? '' : " AND t.nick LIKE '%$name%'"),[':start' => $start,':end' => $end])
            ->offset(($num - 1)*8)
            ->limit(8)
            ->asArray()
            ->all();
    }


    /**
     * 周爽约的课程统计
     */
    public function getMonthMissRoomCount($teacher_id,$start,$end)
    {
        return  ClassRoom::find()
            ->alias('ro')
            ->select('teacher_id,count(ro.id) as count')
            ->leftJoin('class_record AS re','re.class_id = ro.id')
            ->where('teacher_id = :teacher_id AND ro.is_deleted = 1  AND ro.time_class - re.time_created <=3600 AND time_class >=:start AND time_class <=:end',['teacher_id' => $teacher_id,':start' => $start,':end' => $end])
            ->groupBy('teacher_id')
            ->asArray()
            ->all();
    }

    // 查询是否存在内容
    public function getTeacherWagesId($month_time,$type)
    {
        return RewardRecord::find()
            ->select('id')
            ->where('month_time=:month_time AND type=:type',[':month_time'=>$month_time,':type'=>$type])
            ->asArray()
            ->one();
    }

    public function getRestMount($teacher_id, $timeStart, $timeEnd)
    {
        return StatisticsTeacherRest::find()
                                ->select('id')
                                ->where('teacher_id = :teacher_id',[':teacher_id' => $teacher_id])
                                ->andWhere('time_day >= :timeStart',[':timeStart' => $timeStart])
                                ->andWhere('time_day < :timeEnd',[':timeEnd' => $timeEnd])
                                ->andWhere('!(tmp_leave = 0 and all_leave = 0 and pause = 0)')
                                ->count();
    }


    /**
     * 获取运营统计信息
     * @param  $start  int  开始时间
     * @param  $end    int  结束时间
     * @return $list   array
     */

    public function getOperationStatisticalList($start,$end)
    {
         // 老师数目
        $list['teacher_count'] = ClassRoom::find()
                                ->select('COUNT(DISTINCT teacher_id)')
                                ->where('time_class >=:start AND time_class<=:end AND status = 1',[':start' => $start,':end' => $end])
                                ->scalar();

        // 体验课数目
        $exclassCount = ClassRoom::find()
                            ->where('time_class >=:start AND time_class<=:end AND is_ex_class = 1 AND status = 1',[':start' => $start,':end' => $end])
                            ->count();

        $list['exclass_count'] = $exclassCount*1440;


        // 付费数目
        $classCount = ClassRoom::find()
                            ->where('time_class >=:start AND time_class<=:end AND is_ex_class = 0 AND status = 1',[':start' => $start,':end' => $end])
                            ->count();

        $list['class_count']   = $classCount*1440;


        // 底薪合计 课时费合计
        $money  = SalaryChangeLog::find()
                            ->alias('s')
                            ->select("SUM('salary_after') AS salary_after,SUM('class_hour_first')+ SUM('class_hour_second')+SUM('class_hour_third') AS class_money")
                            ->leftJoin('(SELECT teacher_id,Max(time_created) AS time FROM salary_change_log WHERE salary_time >=:start AND salary_time <=:end  GROUP BY teacher_id )AS t','t.teacher_id = s.teacher_id')
                            ->where('t.time IS NOT NULL',[':start' => $start,':end' => $end])
                            ->asArray()
                            ->one();
       $list['salary_after']       = $money['salary_after'];
       $list['class_money']        = $money['class_money'];



       // 老师奖励和惩罚合计
       $list['reward']  =   RewardRecord::find()
                                ->select('SUM(money) AS reward')
                                ->where('time_created >=:start AND time_created <=:end AND prefix = 1',[':start' => $start,':end' => $end])
                                ->scalar();

        $list['fine']   =   RewardRecord::find()
                                ->select('SUM(money) AS fine')
                                ->where('time_created >=:start AND time_created <=:end AND prefix = 0',[':start' => $start,':end' => $end])
                                ->scalar();

        $num                  = ($list['salary_after']+$list['class_money']+$list['reward']-$list['fine'])/($list['exclass_count']*$list['class_count']);
       //  $list['cost']         = round($num,2);


        return $list;
    }
    public function queryOverTimeCount($timeStart,$timeEnd,$name, $type){
        $where_sql=" ut.is_formal = 1 AND ut.is_disabled = 0 ";

        if($type ==1){//未处理
            $where_sql .= " AND rr.cou is   null  ";
        }elseif ($type ==2){//已处理
            $where_sql .= " AND rr.cou is not  null  ";
        }

        if(!empty($name)){
            $where_sql .= " AND nick like '%".$name."%'";
        }
        $teacherInfoCount=UserTeacher::find()
            ->alias('ut')
            ->distinct()
            ->leftJoin('(SELECT teacher_id, count(*) as cou  FROM reward_record   where type=5 and month_time BETWEEN   '.
                $timeStart.'  and  '.$timeEnd.' GROUP BY teacher_id)  as rr ','rr.teacher_id = ut.id')
            ->where($where_sql)
            ->count();

        return $teacherInfoCount;
    }


    public function queryTeacherList($timeStart,$timeEnd,$name,$type,$page_num){
        $where_sql=" ut.is_formal = 1 AND ut.is_disabled = 0 ";

        if($type ==1){//未处理
            $where_sql .= " AND rr.cou is   null  ";
        }elseif ($type ==2){//已处理
            $where_sql .= " AND rr.cou is not  null  ";
        }

        if(!empty($name)){
            $where_sql .= " AND nick like '%".$name."%'";
        }
        $teacherInfo=UserTeacher::find()
                ->alias('ut')
                ->distinct()
                ->select('ut.id,ut.mobile, ut.nick, ut.head_icon')
                ->leftJoin('(SELECT teacher_id, count(*) as cou  FROM reward_record   where type=5 and month_time BETWEEN   '.
                    $timeStart.'  and  '.$timeEnd.' GROUP BY teacher_id)  as rr ','rr.teacher_id = ut.id')
                ->where($where_sql)
                ->offset(($page_num - 1)*10)
                ->limit(10)
                ->asArray()
                ->all();

        return $teacherInfo;
    }

    public function queryTeacherWeekInfo($teacherid){
        $teacherWeekInfo=TeacherInfo::find()
            ->select('teacher_id,week,time_bit')
            ->where('teacher_id =:tid',[":tid"=>$teacherid])
            ->asArray()
            ->all();
        return $teacherWeekInfo;
    }

    public function queryTeacherDayInfo($teacherid){
        $teacherDayInfo=Timetable::find()
            ->select('user_id,time_day,time_bit')
            ->where('user_id =:tid',[":tid"=>$teacherid])
            ->asArray()
            ->all();
        return $teacherDayInfo;
    }

    public function queryTeacherClassList($day_firsttime,$day_endtime,$teacherid){
        $classinfo=ClassRoom::find()
            ->select("teacher_id,time_class,time_end")
            ->where("time_class  BETWEEN '".$day_firsttime."' AND '".$day_endtime."' AND teacher_id=".$teacherid)
            ->asArray()
            ->all();

        return $classinfo;
    }

    public function queryTeacherOvertimeList($timeStart, $timeEnd){
        $overlist=TeacherOvertime::find()
            ->where("  daytime  BETWEEN  ".$timeStart." and ".$timeEnd)
            ->asArray()
            ->all();
        return $overlist;
    }

    public function getOvertimeRewardInfo($timeStart,$timeEnd,$teacher_id){
        return RewardRecord::find()
            ->select('id as reward_record_id,text, reward_id,money,month_time ,prefix')
            ->where(" type=5 AND teacher_id= ".$teacher_id." AND reward_record.month_time BETWEEN ".$timeStart." and ".$timeEnd)
            ->asArray()
            ->one();
    }

    public function getSalaryTeacher($base, $work, $filter)
    {
        return UserTeacher::find()
                    ->select('id, nick, mobile')
                    ->where('type <> 3 and is_disabled = 0')
                    ->andWhere(empty($base) ? "" : "place_id = $base")
                    ->andWhere(empty($work) ? "" : "work_type = $work")
                    ->andWhere(empty($filter) ? "" : "(nick LIKE '%$filter%' or mobile LIKE '%$filter%')")
                    ->asArray()
                    ->all();
    }

    public function getUnPushTeacher($timeStart, $timeEnd, $base, $work, $filter)
    {
        return UserTeacher::find()
            ->alias('ut')
            ->select('ut.id, ut.nick, ut.mobile, ut.teacher_type, ut.open_id')
            ->leftJoin("teacher_basic_salary as tb", "tb.teacher_id = ut.id")
            ->where("ut.is_disabled = 0 AND ut.type!=3 AND tb.is_push = 0 AND tb.time_day >=:time_start AND tb.time_day <:time_end", [':time_start' => $timeStart, ':time_end' => $timeEnd])
            ->andWhere(empty($base) ? "" : "ut.place_id = $base")
            ->andWhere(empty($work) ? "" : "ut.work_type = $work")
            ->andWhere(empty($filter) ? "" : "(ut.nick LIKE '%$filter%' or ut.mobile LIKE '%$filter%')")
            ->asArray()
            ->all();
    }

    /**
     * 获取老师昵称
     * @return  array
     */
    public  function getTeacherByName()
    {
        return UserTeacher::find()
                    ->select('id, nick')
                    ->where(['is_disabled' => 0])
                    ->asArray()
                    ->all();
    }



    /**
     * 获取老师昵称根据条件
     * @param  $return  str
     * @return array
     */
    public  function getTeacherByConditionName($keyword)
    {
        return  UserTeacher::find()
                    ->select('id, nick')
                    ->where(['is_disabled' => 0])
                    ->andWhere('nick LIKE "%' . $keyword . '%"')
                    ->asArray()
                    ->all();
    }


    /**
     * 获取老师基本信息
     * @param  $teacherId  int
     * @return array
     */
    public  function getTeacherBaseInfo($teacherId)
    {
        return  UserTeacher::find()
                    ->select('id, mobile, nick, teacher_level, head_icon, password')
                    ->where(['id' => $teacherId])
                    ->asArray()
                    ->one();
    }



    public function getAllTeacherList($keyword = '')
    {
        $timeStart = strtotime(date('Y-m-1', time()));
        $timeEnd = strtotime(date('Y-m-1', time()) . ' + 1 month');

        //count_1 体验课数量 count_2：赠送课和购买课数量
        $sql = "SELECT u.id, u.nick, c.counts as count_1, cr.counts as count_2 FROM user_teacher AS u"
            . " LEFT JOIN (SELECT teacher_id, COUNT(id) as counts FROM class_room WHERE status != 2 AND time_class >= :time_1 AND time_class < :time_2 AND time_end - time_class = 1500 GROUP BY teacher_id) AS c ON c.teacher_id = u.id"
            . " LEFT JOIN (SELECT teacher_id, COUNT(id) as counts FROM class_room WHERE status != 2 AND time_class >= :time_3 AND time_class < :time_4 AND time_end - time_class > 1500 GROUP BY teacher_id) AS cr ON cr.teacher_id = u.id"
            . " WHERE is_disabled = 0"
            . (empty($keyword) ? "" : " AND u.nick LIKE '%$keyword%'");

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':time_1' => $timeStart,
                ':time_2' => $timeEnd,
                ':time_3' => $timeStart,
                ':time_4' => $timeEnd
            ])->queryAll();
    }

    public function getTeacherTimetable($teacherId,$timeDay) 
    {
        $sql = "SELECT CONV(time_bit,2,10) AS time_bit FROM timetable WHERE user_id = :user_id AND time_day = :time_day";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':user_id' => $teacherId, ':time_day' => $timeDay])
            ->queryScalar();
    }


    public function getTeacherFixedTimeRow($teacherId,$week) 
    {
        $sql = "SELECT CONV(time_bit,2,10 ) AS time_bit, time_execute FROM teacher_info WHERE teacher_id = :teacher_id AND week = :week";
        return Yii::$app->db->createCommand($sql)
            ->bindValues([':teacher_id' => $teacherId, ':week' => $week])
            ->queryOne();
    }

    /**
     * 获取老师时段信息按执行时间排序
     * @param $teacherId
     * @param $week
     * @return array
     */
    public function getTeacherFixedTimeRowOrderByExeTime($teacherId,$week)
    {
        $sql = "SELECT CONV(time_bit,2,10 ) AS time_bit, time_execute FROM teacher_info WHERE teacher_id = :teacher_id AND week = :week "
            . " ORDER BY time_execute DESC";
        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':teacher_id' => $teacherId,
                ':week' => $week
            ])
            ->queryAll();
    }

    /**
     * 查询老师档案列表数量
     */
    public function getTeacherRecordCount($search)
    {
        $query = UserTeacher::find()
            ->select('user_teacher.*,base_place.name as place_name ')
            ->leftJoin('base_place','`base_place`.`id` =`user_teacher`.`place_id`')
            ->where('user_teacher.is_disabled = 0');

        if(!empty($search)){
            $query->andWhere(("user_teacher.nick ='".$search."'"));
        }

        return  $query->count();
    }

    /**
     * 查询老师档案列表
     */
    public function getTeacherRecordList($search, $page)
    {
        $query = TeacherBean::find()
            ->select('user_teacher.*,base_place.name as place_name ')
            ->leftJoin('base_place','`base_place`.`id` =`user_teacher`.`place_id`')
            ->where('user_teacher.is_disabled = 0');


        if(!empty($search)){
            $query->andWhere(("user_teacher.nick = '".$search."'"));
        }

        return  $query->offset(($page-1)*8)
                        ->limit(8)
                        ->asArray()
                        ->all();
    }


    public function getUserTeacherInstrument($item)
    {
        return UserTeacherInstrument::find()
                ->select('user_teacher_instrument.*,instrument.name as name ')
                ->leftJoin('instrument','`user_teacher_instrument`.`instrument_id` =`instrument`.`id`')
                ->where('user_teacher_instrument.user_id = :userID',[':userID' => $item['id']])
                ->asArray()
                ->all();
    }

    public  function getDayTimeBit($user_id, $timeDay)
    {
        return TimeTable::find()
                    ->select('time_bit')
                        ->where('user_id=:uid',[':uid'=>$user_id])
                        ->andWhere('time_day=:tday',[':tday'=>$timeDay])
                        ->asArray()
                        ->one();
    }

    public  function getFixedTimeBit($teacher_id, $weekDay)
    {
        return TeacherInfo::find()->select('time_bit')
                            ->where('teacher_id=:tid',[':tid'=>$teacher_id])
                            ->andWhere('week=:week',[':week'=>$weekDay])
                            ->asArray()
                            ->one();
    }

    public  function getTimeBitClassList($i,$teacher_id)
    {
        return  StudentFixTime::find()
                        ->select('time_bit')
                        ->where('week=:week',[':week'=>$i])
                        ->andWhere('teacher_id=:tid',[':tid'=>$teacher_id])
                        ->column();
    }

    public  function getStundentFixTimeName($teacher,$weekDay, $num)
    {
        return  StudentFixTime::find()
                            ->select('user.nick')
                            ->leftJoin('user','`student_fix_time`.`student_id` =`user`.`id`')
                            ->where('student_fix_time.teacher_id=:teacher',[':teacher'=>$teacher])
                            ->andWhere('week=:week',[':week'=>$weekDay])
                            ->andWhere("student_fix_time.time_bit & $num = $num")
                            ->asArray()
                            ->one();
    }


    public  function getTeacherClassBeanName($teacher,$weekDay, $num)
    {
        return  StudentFixTime::find()
                            ->select('user.nick')
                            ->leftJoin('user','`student_fix_time`.`student_id` =`user`.`id`')
                            ->where('student_fix_time.teacher_id=:teacher',[':teacher'=>$teacher])
                            ->andWhere('week=:week',[':week'=>$weekDay])
                            ->andWhere("student_fix_time.time_bit & $num = $num")
                            ->asArray()
                            ->one();
    }


    public  function getTeacherResumeInfo($id)
    {
        return TeacherBean::find()->select("resume")->where(["id"=>$id])->asArray()->one();
    }

    public function getTeacherList($keyword = '')
    {
        $timeStart = strtotime(date('Y-m-1', time()));
        $timeEnd = strtotime(date('Y-m-1', time()) . ' + 1 month');
        
        $sql = "SELECT u.id, u.nick, c.counts as count_1, cr.counts as count_2 FROM user_teacher AS u"
            . " LEFT JOIN (SELECT teacher_id, COUNT(id) as counts FROM class_room WHERE status != 2 AND time_class >= :time_1 AND time_class < :time_2 AND time_end - time_class = 1500 GROUP BY teacher_id) AS c ON c.teacher_id = u.id"
            . " LEFT JOIN (SELECT teacher_id, COUNT(id) as counts FROM class_room WHERE status != 2 AND time_class >= :time_3 AND time_class < :time_4 AND time_end - time_class > 1500 GROUP BY teacher_id) AS cr ON cr.teacher_id = u.id"
            . " WHERE is_disabled = 0"
            . (empty($keyword) ? "" : " AND u.nick LIKE '%$keyword%'");

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':time_1' => $timeStart,
                ':time_2' => $timeEnd,
                ':time_3' => $timeStart,
                ':time_4' => $timeEnd
            ])->queryAll();
    }
    
    public function getTeacherByKeyCount($word_key, $work_type, $base_type)
    {
        return UserTeacher::find()
            ->select('id')
            ->where('user_teacher.is_disabled = 0')
            ->andWhere(empty($word_key) ? "" : "user_teacher.nick like '%".$word_key."%'")
            ->andWhere(empty($work_type) ? "" : "user_teacher.work_type = ".$work_type)
            ->andWhere(empty($base_type) ? "" : "user_teacher.place_id = ".$base_type)
            ->count();
    }

    public function getTeacherByKeyInfo($word_key, $work_type, $place_type, $page_num)
    {
        return UserTeacher::find()
            ->select('user_teacher.*, base_place.name as place_name, teacher_work_type.name as work_name')
            ->leftJoin('base_place','base_place.id = user_teacher.place_id')
            ->leftJoin('teacher_work_type','teacher_work_type.id = user_teacher.work_type')
            ->where('user_teacher.is_disabled = 0')
            ->andWhere(empty($word_key) ? '' : "user_teacher.nick like '%".$word_key."%'")
            ->andWhere(empty($work_type) ? "" : "user_teacher.work_type=".$work_type)
            ->andWhere(empty($place_type) ? "" : "user_teacher.place_id=".$place_type)
            ->offset(($page_num-1)*8)
            ->limit(8)
            ->asArray()
            ->all();
    }

    public function getTeacherInstrument($teacher_id)
    {
        return UserTeacherInstrument::find()
            ->select('user_teacher_instrument.*, instrument.name as name ')
            ->leftJoin('instrument','user_teacher_instrument.instrument_id = instrument.id')
            ->where('user_teacher_instrument.user_id = :userID',[':userID' => $teacher_id])
            ->asArray()
            ->all();
    }

    public function getTeacherInfoById($teacher_id)
    {
        return UserTeacher::find()
            ->where('id = :teacher_id',[':teacher_id'=>$teacher_id])
            ->asArray()
            ->one();
    }

    public function getTeacherSchool($teacher_school_id)
    {
        return School::find()
            ->where('id = :teacher_school_id',[':teacher_school_id'=>$teacher_school_id])
            ->asArray()
            ->one();
    }

    public function getTeacherListByKey($filter)
    {
        return UserTeacher::find()
            ->select('id, nick')
            ->where('is_disabled = 0')
            ->andWhere(empty($filter) ? "" : "nick LIKE '%{$filter}%'")
//            ->andWhere((empty($filter) ? "" : "(mobile like '%{$filter}%' OR nick like '%{$filter}%')"))
            ->asArray()
            ->all();
    }
    
    /**
     * @param $teacher_id
     * @return mixed
     * @author sjy
     * 获取当前老师薪资生效时间
     */
    public function getSalaryTime($teacher_id){
        
        return UserTeacher::find()
            ->select('hour_time, salary_time ,work_type,salary_after,class_hour_first,class_hour_second,class_hour_third,allduty_award_rates,absence_punished_rates,employedtime,allduty_time,absence_time')
            ->where('is_disabled = 0  and id = '.$teacher_id)
            ->asArray()
            ->one();
    }
    
    /**
     * @param $teacher_id
     * @return mixed
     * @author sjy
     * 获取乐器种类信息
     */
    public function getInstrument(){
         $sql = "SELECT * FROM instrument ";

        return Yii::$app->db->createCommand($sql)
            ->queryAll();
    }

    public function getResumeById($teacher_id)
    {
        return UserTeacher::find()
            ->select('resume')
            ->where('id = :teacher_id',[':teacher_id'=>$teacher_id])
            ->scalar();
    }

    public function getShowNameById($teacher_id)
    {
        return UserTeacher::find()
            ->select('show_name')
            ->where('id = :teacher_id',[':teacher_id'=>$teacher_id])
            ->scalar();
    }

    public function getResponsibleSchoolById($teacher_id)
    {
        return UserTeacher::find()
            ->select('responsible_school')
            ->where('id = :teacher_id',[':teacher_id'=>$teacher_id])
            ->scalar();
    }
    
    /**
     * @param $teacher_id
     * @return mixed
     * @author sjy
     * 查找手机号是否存在
     */
    public function checkMobile($mobile, $userId, $role){
         $sqlWhere = empty($userId) ? '' : " AND id != $userId";

        $sql = "SELECT COUNT(*) FROM user_teacher WHERE mobile = :mobile AND is_disabled = 0".($role == 1 ? $sqlWhere : '');
        $resultTeacher = Yii::$app->db->createCommand($sql)
                        ->bindValue(':mobile',$mobile)
                        ->queryScalar();
        $resultTeacher = empty($resultTeacher) ? true : false;

        return $resultTeacher;
    }

    public function getTeacherInstrumentInfo($teacher_id)
    {
        return TeacherInstrument::find()
            ->alias('ti')
            ->select("ti.grade , ti.level , i.name")
            ->leftJoin("instrument as i" , "i.id = ti.instrument_id")
            ->where("ti.teacher_id = :teacher_id" , [':teacher_id' => $teacher_id])
            ->asArray()
            ->all();
    }

    public function getTeacherInstrumentNew($teacher_id)
    {
        return TeacherInstrument::find()
                        ->alias('ti')
                        ->select("ti.* , i.name as instrument_name")
                        ->leftJoin("instrument as i" , "i.id = ti.instrument_id")
                        ->where("ti.teacher_id = :teacher_id" , [':teacher_id' => $teacher_id])
                        ->asArray()
                        ->all();
    }

    public function getWeiSalaryByInstrument($teacher_id, $instrument_id)
    {
        $sql = "SELECT grade, `level`, hour_first, `hour_second`, hour_third FROM `teacher_instrument` WHERE instrument_id = :instrument_id AND teacher_id = :teacher_id";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':instrument_id' => $instrument_id,
                ':teacher_id' => $teacher_id
            ])->queryOne();
    }

    public function getGoodAnalysisTeacherCount($filter)
    {
        return UserTeacher::find()
                    ->select('id')
                    ->where('type = 1 and teacher_type = 1 and work_id != 4 AND is_disabled = 0')
                    ->andWhere((empty($filter) ? "" : " nick LIKE '%$filter%'"))
                    ->count();
    }

    public function getGoodAnalysisTeacherIds($filter, $page_num)
    {
//        return UserTeacher::find()
//            ->alias('u')
//            ->select('u.id, u.nick, i.instrument_id, i.grade, i.level')
//            ->leftJoin('teacher_instrument as i', 'i.teacher_id = u.id')
//            ->where('u.type = 1 and u.teacher_type = 1 and u.work_id != 4')
//            ->andWhere((empty($filter) ? "" : " u.nick LIKE '%$filter%'"))
//            ->asArray()
//            ->all();

        $sql = "SELECT u.id, u.nick, u.mobile, p.name as place_name FROM user_teacher AS u"
            . " LEFT JOIN base_place AS p ON p.id = u.place_id"
            . " WHERE u.type = 1 AND u.teacher_type = 1 AND u.work_id != 4 AND u.is_disabled = 0"
            . (empty($filter) ? "" : " AND u.nick LIKE '%$filter%'")
            . (empty($page_num) ? "" : " limit ". (($page_num-1)*10) . ", 10");

        return Yii::$app->db->createCommand($sql)
                        ->queryAll();
    }
    
    public function getTeacherTypeOpenidById($teacher_id)
    {
        return UserTeacher::find()
                    ->select('teacher_type, open_id, nick')
                    ->where('is_disabled = 0  and id = :teacher_id', [':teacher_id' => $teacher_id])
                    ->asArray()
                    ->one();
    }

    public function getTeacherNameByCondition($av_list, $student_teacher_fix_exit, $instrument_type, $filter_name)
    {
        $sql = "SELECT t.id, t.nick, CASE t.gender WHEN 0 THEN '男' ELSE '女' END AS gender,"
            . " CASE WHEN ti.grade = 1 THEN '启蒙' WHEN ti.grade = 2 THEN '初级' WHEN ti.grade = 3 THEN '中级' ELSE '高级' END AS grade, ti.level FROM user_teacher AS t"
            . " LEFT JOIN teacher_instrument AS ti ON t.id = ti.teacher_id"
            . " WHERE ti.instrument_id = :instrument_type AND t.is_disabled = 0 AND t.type != 3"
            . (empty($av_list) ? " AND t.id = -1" : " AND t.id IN(".implode(',',$av_list).")")
            . (empty($student_teacher_fix_exit) ? "" : " AND t.id NOT IN (".implode(',',$student_teacher_fix_exit).")")
            . (empty($filter_name) ? "" : " AND t.nick LIKE '%$filter_name%'");

        return Yii::$app->db->createCommand($sql)
                            ->bindValue(':instrument_type', $instrument_type)
                            ->queryAll();
    }

    public function getTeacherClassMoneyById($time_start,$time_end,$teacher_id)
    {
        // 所有有薪资信息的校招老师的openid
        $sql = " SELECT SUM(class_money) AS total FROM teacher_class_money WHERE time_class >= :time_start AND time_class < :time_end AND teacher_id = :teacher_id";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':time_start' => $time_start,
                ':time_end' => $time_end,
                ':teacher_id' => $teacher_id
            ])
            ->queryOne();
    }

    public function getTeacherClassMoneyList($time_start,$time_end)
    {

        // 所有有薪资信息的校招老师的openid
        $sql = " SELECT teacher_id, ut.nick, ut.open_id, SUM(class_money) AS total "
            . " FROM teacher_class_money AS tcm "
            . " LEFT JOIN user_teacher AS ut ON ut.id = tcm.teacher_id "
            . " WHERE ut.teacher_type = 2 AND ut.is_disabled = 0 AND ut.open_id <> '' AND time_class >= :time_start AND time_class < :time_end "
            . " GROUP BY tcm.teacher_id ";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':time_start' => $time_start,
                ':time_end' => $time_end
            ])
            ->queryAll();
    }

    public function getTeacherGradeByInstrument($teacher_id, $instrument_id)
    {
        $sql = "SELECT CASE WHEN grade = 1 THEN '启蒙' WHEN grade = 2 THEN '初级' WHEN grade = 3 THEN '中级' ELSE '高级' END AS grade, level"
            . " FROM teacher_instrument WHERE teacher_id = :teacher_id AND instrument_id = :instrument_id";

        return Yii::$app->db->createCommand($sql)
                    ->bindValues([
                        ':teacher_id' => $teacher_id,
                        ':instrument_id' => $instrument_id
                    ])->queryOne();
    }

    public function isWorkTeacher($teacher_id, $week, $bit)
    {

        $sql = "SELECT  CONV(time_bit,2,10) as bit  FROM teacher_info WHERE teacher_id = :teacher_id AND week = :week ORDER BY time_execute DESC ";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':teacher_id' => $teacher_id,
                ':week' => $week,
            ])
            ->queryScalar();

    }
}
