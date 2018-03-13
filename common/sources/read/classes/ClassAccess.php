<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/13
 * Time: 上午11:21
 */
namespace common\sources\read\classes;

use classapi1\models\course\Student;
use common\compute\SalaryCompute;
use common\models\music\ClassEditHistory;
use common\models\music\ClassFail;
use common\models\music\ClassImage;
use common\models\music\ClassLeft;
use common\models\music\ClassLog;
use common\models\music\ClassNet;
use common\models\music\ClassRecord;
use common\models\music\ClassRoom;
use common\models\music\Complain;
use common\models\music\Instrument;
use common\models\music\RecordAudio;
use common\models\music\RecordImage;
use common\models\music\StudentFixTime;
use common\models\music\TeacherTag;
use common\models\music\User;
use common\models\music\UserInit;
use common\models\music\UserShare;
use common\models\music\StudentWechatClass;
use common\models\music\StudentUserShare;

use common\models\music\UserTeacher;
use common\widgets\BinaryDecimal;
use Yii;

class ClassAccess implements IClassAccess
{
    /**
     * 查询体验课学生的详细信息
     * @param $num
     * @param $keyword
     * @param $num
     */
    public function getExUserList($num, $keyword, $time)
    {
        return User::find()
            ->alias('u')
            ->select('c.*, u.nick as userName,u.mobile as userMobile,u.chat_token as userToken,u.is_first,t.is_firstLogin,t.nick as teacherName,t.mobile as teacherMobile,t.chat_token as teacherToken, i.birth, i.level, i.city, i.call_count, i.purchase, i.area, i.open_id, ci.class_id, c.course_info')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.id')
            ->leftJoin('(SELECT id, student_id, teacher_id, time_class, time_end, course_info FROM class_room WHERE is_ex_class = 1 AND status != 2 AND status != 3 AND is_deleted = 0 GROUP BY student_id) AS c', 'c.student_id = u.id')
            ->leftJoin('user_teacher AS t', 't.id = c.teacher_id')
            ->leftJoin('(select class_id from class_image group by class_id) as ci', 'ci.class_id = c.id')
            ->where(
                'i.kefu_id = :id AND u.is_disabled = 0 AND c.student_id > 0 AND c.time_class >= :start AND time_class < :end' . (empty($keyword) ? '' : " AND (u.nick LIKE '%$keyword%' OR u.mobile LIKE '%$keyword%')"),
                [
                    ':id' => Yii::$app->user->identity->id,
                    ':start' => $time,
                    ':end' => $time + 86400
                ]
            )
            ->orderBy('c.time_class ASC')
            ->offset(($num-1)*8)
            ->limit(8)
            ->asArray()
            ->all();
    }

    public function getStudentClassNet($classId, $studentId)
    {
        return ClassNet::find()
            ->select('net_desc')
            ->where([
                'role' => 0,
                'class_id' => $classId,
                'user_id' => $studentId
            ])
            ->orderBy('id DESC')
            ->scalar();
    }

    public function getTeacherClassNet($classId, $teacherId)
    {
        return ClassNet::find()
            ->select('net_desc')
            ->where([
                'role' => 1,
                'class_id' => $classId,
                'user_id' => $teacherId
            ])
            ->orderBy('id DESC')
            ->scalar();
    }

    public function getStudentClassStatus($classId, $studentId)
    {
        return ClassLog::find()
            ->alias("a")
            ->select("b.name, b.id")
            ->leftJoin("class_quit_dic as b", "a.type = b.id")
            ->where([
                "a.role" => 0,
                "a.class_id" => $classId,
                "a.user_id" => $studentId
            ])
            ->orderBy("a.id DESC")
            ->asArray()
            ->one();
    }

    public function getTeacherClassStatus($classId, $teacherId)
    {
        return ClassLog::find()
            ->alias("a")
            ->select("b.name, b.id")
            ->leftJoin("class_quit_dic as b", "a.type = b.id")
            ->where([
                "a.role" => 1,
                "a.class_id" => $classId,
                "a.user_id" => $teacherId
            ])
            ->orderBy("a.id DESC")
            ->asArray()
            ->one();
    }


    public function getClassesListCount($day, $type, $teacher, $timeStart, $timeEnd)
    {
        $query = ClassRoom::find()
                //->select('class_room.*,wechat_acc.openid,class_record.time_send,class_edit_history.type as class_type,ci.class_id,user.nick as userName,user.mobile as userMobile,user.remark,user_teacher.nick as teacherName')
                ->leftJoin('wechat_acc', 'wechat_acc.uid=class_room.student_id')
                ->leftJoin('class_edit_history', 'class_room.history_id =class_edit_history.id')
                ->leftJoin('user', 'class_room.student_id =user.id')
                ->leftJoin('user_teacher', 'class_room.teacher_id =user_teacher.id')
                ->leftJoin('class_record', 'class_record.class_id = class_room.id')
                ->leftJoin('(select class_id from class_image group by class_id) as ci', 'ci.class_id = class_room.id')
                ->leftJoin('(SELECT student_id, MIN(time_class) AS formal_min_time_class
            FROM class_room where is_ex_class = 0 AND is_deleted = 0 AND (status = 0 OR status = 1) GROUP BY student_id) AS mc', 'mc.student_id = class_room.student_id')
                ->where('class_room.is_deleted = 0 and class_room.status != 2 and class_room.status != 3 and class_room.time_class >= :stime', [':stime' => $timeStart])
                ->andWhere('class_room.time_class < :etime', [':etime' => $timeEnd]);

        if (!empty($teacher)) {
            $query->andWhere("user_teacher.id=:id", [':id' => $teacher]);
        }

        switch ($type) {
            case 1:
                $query->andWhere("class_room.is_ex_class = 1 and class_room.is_first_ex = 1");
                break;
            case 2:
                $query->andWhere("class_room.is_ex_class = 1 and class_room.is_first_ex = 0");
                break;
            case 3:
                $query->andWhere("class_room.is_ex_class = 0");
                break;
            case 4:
                $query->andWhere("class_room.course_info = 'a:0:{}' or class_room.marks = ''");
                break;
            case 5:
                $query->andWhere("class_room.teacher_id = 0");
                break;
            case 6:
                $query->andWhere("class_room.status_bit & 1 = 1");
                break;
            case 7:
                $query->andWhere("class_room.status_bit & 1 = 0");
                break;
            case 8:
                $query->andWhere("class_room.status_bit & 2 = 2");
                break;
            case 9:
                $query->andWhere("class_room.status_bit & 2 = 0");
                break;
            case 10:
                $query->andWhere("class_room.is_ex_class = 0 and (ci.class_id IS NOT NULL or class_room.course_info != 'a:0:{}')");
                break;
            case 11:
                $query->andWhere("class_room.is_ex_class = 0 and (ci.class_id IS NULL and class_room.course_info = 'a:0:{}')");
                break;
            case 12:
                $query->andWhere("class_room.time_class = mc.formal_min_time_class");
                break;
            default:
                break;
        }

        $totalCount = $query->count();
        return $totalCount;
    }


    public function getCountDay($firstday, $lastday)
    {
        //TODO 是否需要写模型类
        $sql="select count(id) from class_room where time_class between $firstday and $lastday and status != 2 and status != 3 and is_deleted=0";
        $countday=Yii::$app->db->createCommand($sql)->queryScalar();
        return  $countday;
    }

    public function getCountWeek($lastweek, $firstweek)
    {
        //TODO 是否需要写模型类
        $sql1="select count(id) from class_room where time_class between $firstweek and $lastweek and status != 2 and status != 3 and is_deleted = 0";
        $countweek=Yii::$app->db->createCommand($sql1)->queryScalar();

        return $countweek;
    }

    public function getCourseData($timeStart, $timeEnd, $teacher, $page, $type)
    {
        $query = ClassRoom::find()
                ->select('mc.formal_min_time_class,class_room.*,wechat_acc.openid,class_record.time_send,class_record.id as record_id,class_edit_history.type as class_type,ci.class_id,user.nick as userName,user.mobile as userMobile,user.remark,user_teacher.nick as teacherName')
                ->leftJoin('wechat_acc', 'wechat_acc.uid=class_room.student_id')
                ->leftJoin('class_edit_history', 'class_room.history_id =class_edit_history.id')
                ->leftJoin('user', 'class_room.student_id =user.id')
                ->leftJoin('user_teacher', 'class_room.teacher_id = user_teacher.id')
                ->leftJoin('class_record', 'class_record.class_id = class_room.id')
                ->leftJoin('(select class_id from class_image group by class_id) as ci', 'ci.class_id = class_room.id')
                ->leftJoin('(SELECT student_id, MIN(time_class) AS formal_min_time_class
            FROM class_room where is_ex_class = 0 AND is_deleted = 0 AND (status = 0 OR status = 1) GROUP BY student_id) AS mc', 'mc.student_id = class_room.student_id')
                ->where('class_room.is_deleted = 0 and class_room.status != 2 and class_room.status != 3 and class_room.time_class >= :stime', [':stime' => $timeStart])
                ->andWhere('class_room.time_class < :etime', [':etime' => $timeEnd]);

        if (!empty($teacher)) {
            $query->andWhere("user_teacher.id=:id", [':id' => $teacher]);
        }
        switch ($type) {
            case 1:
                $query->andWhere("class_room.is_ex_class = 1 and class_room.is_first_ex = 1");
                break;
            case 2:
                $query->andWhere("class_room.is_ex_class = 1 and class_room.is_first_ex = 0");
                break;
            case 3:
                $query->andWhere("class_room.is_ex_class = 0");
                break;
            case 4:
                $query->andWhere("class_room.course_info = 'a:0:{}' or class_room.marks = ''");
                break;
            case 5:
                $query->andWhere("class_room.teacher_id = 0");
                break;
            case 6:
                $query->andWhere("class_room.status_bit & 1 = 1");
                break;
            case 7:
                $query->andWhere("class_room.status_bit & 1 = 0");
                break;
            case 8:
                $query->andWhere("class_room.status_bit & 2 = 2");
                break;
            case 9:
                $query->andWhere("class_room.status_bit & 2 = 0");
                break;
            case 10:
                $query->andWhere("class_room.is_ex_class = 0 and (ci.class_id IS NOT NULL or class_room.course_info != 'a:0:{}')");
                break;
            case 11:
                $query->andWhere("class_room.is_ex_class = 0 and (ci.class_id IS NULL and class_room.course_info = 'a:0:{}')");
                break;
            case 12:
                $query->andWhere("class_room.time_class = mc.formal_min_time_class");
                break;
            default:
                break;
        }

        $courseData = $query->orderBy('class_room.time_class')
                ->offset(($page - 1) * 8)
                ->limit(8)
                ->asArray()
                ->all();

//getClassMonitorList
        return $courseData;
    }

    public function queryCourseData($id)
    {
        $courseData = ClassRoom::find()
            ->select('course_info')
            ->where('id=:id', [':id'=>$id])
            ->one();

        return $courseData;
    }
    public function queryImageList($id)
    {
        $imageList = ClassImage::find()
            ->select('id,file_path')
            ->where('class_id=:rid', [':rid'=>$id])
            ->asArray()
            ->all();

        return $imageList;
    }

    public function queryViewclassData($classId)
    {
        $data = ClassRecord::findOne(['class_id'=>$classId]);
        return $data;
    }
    public function queryViewclassImages($imgId)
    {
        $images = RecordImage::find()
            ->select('file_path')
            ->where('record_id = :rid', [":rid" => $imgId])
            ->asArray()
            ->all();
        return $images;
    }


    public function queryClassRoomById($id)
    {
        return ClassRoom::findOne($id)->toArray();
    }

    public function queryClassLeftinfo($userid)
    {
        $leftList = ClassLeft::find()
                ->where(['user_id' => $userid])
                ->andWhere('ac_amount > 0')
                ->orderBy('type ASC')
                ->all();

        return $leftList;
    }
    public function queryExclassList($timeStart, $timeEnd)
    {
        $query = ClassRoom::find()
            ->alias('c')
            ->select('c.id,c.status_bit,c.time_class,c.time_end,c.marks,c.student_id,c.instrument_id,user.nick,user.mobile')
            ->leftJoin('user', 'c.student_id =user.id')
            ->where('c.is_deleted = 0 and c.status = 0 and c.teacher_id = 0')
            ->andWhere('c.is_ex_class = 1')
            ->andWhere('c.time_class >= :stime', [':stime' => $timeStart])
            ->andWhere('c.time_class < :etime', [':etime' => $timeEnd]);

        $courseData = $query->orderBy('c.time_class')
            ->asArray()
            ->all();

        return $courseData;
    }

    public function getClassByClassid($classId)
    {
        $class = ClassRoom::findOne($classId);
        return $class;
    }

    public function getClassEditHistory($historyId)
    {
        $history =  ClassEditHistory::findOne($historyId);
        return $history;
    }

    public function getClassLeft($leftId)
    {
        $classleft=ClassLeft::findOne($leftId);
        return $classleft;
    }

    public function monitorClassCount($currentDate, $timeStart, $timeEnd, $current, $keyword, $type)
    {
        if ($currentDate > $timeStart) {
            //今天之前的某一天
            $current = $timeEnd;
        } else if ($currentDate < $timeStart) {
            //今天之后的某一天
            $current = $timeStart;
        }

        $query = ClassRoom::find()->alias('c')
            ->select('c.*,u.nick as userName,u.mobile as userMobile,t.nick as teacherName,t.mobile as teacherMobile')
            ->leftJoin('user as u', 'c.student_id =u.id')
            ->leftJoin('user_teacher as t', 'c.teacher_id =t.id')
            ->where('c.is_deleted = 0 and c.status < 2');

        if ($type == 1) { //待开始
            $query = $query->andWhere('c.time_class >= :stime and c.time_class < :etime', [':stime' => $current,':etime' => $timeEnd]);
        } else if ($type == 2) { //进行中
            $query = $query->andWhere('c.time_class < :stime and c.time_end > :etime', [':stime' => $current,':etime' => $current]);
        } else { //已结束
            $query = $query->andWhere('c.time_end < :stime and c.time_end > :etime', [':stime' => $current,':etime' => $timeStart]);
        }

        if (!empty($keyword)) {
            $query = $query->andWhere("u.nick like '%".$keyword."%' or t.nick like '%" .$keyword. "%' or u.mobile like '%" .$keyword. "%' or t.mobile like '%" .$keyword. "%'");
        }
        //Debug::debug($query->createCommand()->getRawSql());
        $totalCount = $query->count();
        return $totalCount;
    }

    public function monitorClassList($page, $currentDate, $timeStart, $timeEnd, $current, $keyword, $type)
    {
        if ($currentDate > $timeStart) {
            //今天之前的某一天
            $current = $timeEnd;
        } else if ($currentDate < $timeStart) {
            //今天之后的某一天
            $current = $timeStart;
        }

        $query = ClassRoom::find()->alias('c')
                ->select('c.*,u.nick as userName,u.mobile as userMobile,u.chat_token as userToken,u.is_first,t.is_firstLogin,t.nick as teacherName,t.mobile as teacherMobile,t.chat_token as teacherToken')
                ->leftJoin('user as u', 'c.student_id =u.id')
                ->leftJoin('user_teacher as t', 'c.teacher_id =t.id')
                ->where('c.is_deleted = 0 and c.status < 2');

        if ($type == 1) { //待开始
            $query = $query->andWhere('c.time_class >= :stime', [':stime' => $current]);
            $query = $query->andWhere('c.time_class < :etime', [':etime' => $timeEnd]);
        } else if ($type == 2) { //进行中
            $query = $query->andWhere('c.time_class < :stime', [':stime' => $current]);
            $query = $query->andWhere('c.time_end > :etime', [':etime' => $current]);
        } else { //已结束
            $query = $query->andWhere('c.time_end < :stime', [':stime' => $current]);
            $query = $query->andWhere('c.time_end > :etime', [':etime' => $timeStart]);
        }

        if (!empty($keyword)) {
            $query = $query->andWhere("u.nick like '%" . $keyword . "%' or t.nick like '%" . $keyword . "%' or u.mobile like '%" . $keyword . "%' or t.mobile like '%" . $keyword . "%'");
        }

        $data = $query->orderBy('c.time_class')
                ->offset(($page - 1) * 42)
                ->limit(42)
                ->asArray()
                ->all();

        return $data;
    }

    public function getClassNetBeanByStudentid($item)
    {
        $stu = ClassNet::find()->select("net_desc")
                        ->where(["role" => 0, "class_id" => $item["id"], "user_id" => $item["student_id"]])
                        ->orderBy("id desc")->asArray()->one();
        return $stu;
    }

    public function getClassNetBeanByTeacherid($item)
    {
        $tec = ClassNet::find()->select("net_desc")
                        ->where(["role" => 1, "class_id" => $item["id"], "user_id" => $item["teacher_id"]])
                        ->orderBy("id desc")->asArray()->one();
        return $tec;
    }

    public function getClassStudentStatus($item)
    {
        $stuStatus = ClassLog::find()->alias("a")->select("b.name,b.id")
                        ->leftJoin("class_quit_dic as b", "a.type=b.id")
                        ->where(["a.role" => 0, "a.class_id" => $item["id"], "a.user_id" => $item["student_id"]])
                        ->orderBy("a.id desc")->asArray()->one();

        return $stuStatus;
    }

    public function getClassTecStatus($item)
    {
        $tecStatus = ClassLog::find()->alias("a")->select("b.name,b.id")
                        ->leftJoin("class_quit_dic as b", "a.type=b.id")
                        ->where(["a.role" => 1, "a.class_id" => $item["id"], "a.user_id" => $item["teacher_id"]])
                        ->orderBy("a.id desc")->asArray()->one();
        return $tecStatus;
    }

    public function queryClassContactByClassid($classId)
    {
        $class = ClassRoom::findOne(['id' => $classId]);
        return $class;
    }

    public function synAccount($req)
    {
        if ($req->isPost) {
            $AppKey = 'ff0f9a72db5b719dad88ce9dd23c16b7';
            $AppSecret = 'afcc2f923f42';
            $type = $req->post("type");
            $id = $req->post("id");
            $phone = $req->post("phone");

            if ($type == 1) { //学生
                $data = User::findOne($id);
                if ($data && !empty($data->chat_token)) {
                    return 1;
                }
            } else { //老师
                $data = UserTeacher::findOne($id);
                if ($data && !empty($data->chat_token)) {
                    return 1;
                }
            }

            $api = new NeteaseAPI($AppKey, $AppSecret, 'curl');
            $res = $api->createUserId($phone, "miaoke");

            if ($res["code"] == 200) {
                $token = $res["info"]["token"];
            } else {
                $update = $api->updateUserToken($phone);
                if ($update["code"] == 200) {
                    $token = $update["info"]["token"];
                } else {
                    return $res["desc"];
                }
            }

            if ($type == 1) { //学生
                $data = User::findOne($id);
                if ($data) {
                    $data->chat_token = $token;
                    $data->accessToken = $phone . $token;
                    $data->save();

                    return 1;
                }
            } else { //老师
                $data = UserTeacher::findOne($id);
                if ($data) {
                    $data->chat_token = $token;
                    $data->accessToken = $phone . $token;
                    $data->save();

                    return 1;
                }
            }
        }
        return "Sys Error";
    }

    public function getSendClassInfo($classid)
    {
        $classInfo = ClassRoom::find()
            ->select('student_id, time_class, time_end, is_ex_class')
            ->where(['id' => $classid])
            ->asArray()
            ->one();

        return $classInfo;
    }

    public function getSendClassLeftInfo($studentId)
    {
        $leftInfo = ClassLeft::find()
            ->select('id,total_amount as total, ac_amount as amount,instrument_id,time_type')
            ->where([
                'user_id' => $studentId,
                'type' => 1
            ])->asArray()->one();

        return $leftInfo;
    }

    public function queryRecordinfo($classid)
    {
        $recordInfo = ClassRecord::findOne(['class_id' => $classid]);

        return $recordInfo;
    }
    public function getRecordInfoByClassInfo($classId)
    {
        return ClassRecord::findOne(['class_id' => $classId])->toArray();
    }

    public function getTeacherTagList()
    {
        return TeacherTag::find()->asArray()->all();
    }

    public function getRecordAudioList($recordId)
    {
        return RecordAudio::find()
            ->select('id, file_path')
            ->where(['record_id' => $recordId])
            ->asArray()
            ->all();
    }

    public function getRecordImageList($recordId)
    {
        return RecordImage::find()
            ->select('id, name, file_path')
            ->where(['record_id' => $recordId])
            ->asArray()
            ->all();
    }

    public function getClassByTime($timeStart, $type, $level)
    {
        $class = ClassRoom::find()
                    ->alias('c')
                    ->select('c.id')
                    ->leftJoin('user_teacher_instrument as ui', 'ui.user_id = c.teacher_id')
                    ->where('c.is_deleted = 0 and (c.status = 1 or c.status = 0) and c.teacher_id > 0')
                    ->andWhere('(c.time_class = :time_start or c.time_class = (:time_start + 1800))', [':time_start' => $timeStart])
                    ->andWhere('ui.type = 1 and ui.instrument_id = :type', [':type' => $type]);

        if ($level == 1) {
            $class ->andWhere('ui.level = 1');
        } elseif ($level == 2) {
            $class ->andWhere('ui.level = 2');
        } elseif ($level == 3) {
            $class ->andWhere('ui.level = 3');
        } else {
            $class ->andWhere('ui.level = 4');
        }
        return $class->count();
    }

    public function getRecordInfoById($recordId)
    {
        return ClassRecord::findOne($recordId);
    }

    public function getClassInfoById($classId)
    {
        return ClassRoom::findOne($classId);
    }
    
    public function getExperienceMonthByTeacher($teacherId, $timeStart, $timeEnd)
    {
        $sql = "SELECT count(id) from class_room WHERE teacher_id = :tid  AND is_ex_class = 1 AND `status` = 1 AND is_deleted = 0 AND time_class >= :timeStart AND  time_class < :timeEnd";

        return Yii::$app->db->createCommand($sql)
                        ->bindValues([
                            ':tid' => $teacherId,
                            ':timeStart' => $timeStart,
                            ':timeEnd' => $timeEnd
                        ])->queryScalar();
    }

    public function getExToBuyMonthByTeacher($teacherId, $timeStart, $timeEnd)
    {
        $sql = "SELECT count(h.student_id) FROM `class_edit_history` AS h"
            . " LEFT JOIN class_room AS c ON h.student_id = c.student_id"
            . " WHERE c.is_ex_class = 1 AND c.status = 1 AND c.is_deleted = 0 AND c.teacher_id = :tid"
            . " AND h.price >0 AND h.is_add = 1 AND h.is_success = 1 AND h.is_deleted = 0"
            . " AND h.time_created >= :timeStart AND h.time_created < :timeEnd"
            . " AND h.student_id NOT IN (select student_id from class_edit_history where price > 0 and is_add = 1 and is_success = 1 and is_deleted = 0 and time_created < :timeStart group by student_id)"
            . " GROUP BY h.student_id";

        return Yii::$app->db->createCommand($sql)
                    ->bindValues([':tid' => $teacherId, ':timeStart' => $timeStart, ':timeEnd' => $timeEnd])
                    ->queryScalar();
    }

    public function getCourseByTeacher($tid, $timeStart, $timeEnd, $type, $page)
    {
        $sql = "SELECT time_class, time_end FROM class_room WHERE time_class >= :timeStart AND time_class < :timeEnd"
            . " AND teacher_id = :tid AND status = 1 AND is_deleted = 0"
            . (empty($type) ? " " : " limit ".$page * 5 .",5");

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':tid' => $tid, ':timeStart' => $timeStart, ':timeEnd' => $timeEnd ])
            ->queryAll();
    }

    public function getClassLeftRowByOrder($orderIdOld)
    {
        return ClassLeft::findOne(['order_id' => $orderIdOld])
            ->toArray();
    }

    public function getAddClassHistoryRowByOrderId($orderIdOld)
    {
        return ClassEditHistory::find()
            ->where(['order_id' => $orderIdOld])
            ->andWhere('is_add = 1 AND is_success = 1 AND amount > 0')
            ->asArray()
            ->one();
    }

    public function getClassCheckPage($keyword)
    {
        $time  = time();
        $start = $time - 300;
        $end   = $time + 300;

        return  ClassRoom::find()
            ->alias('c')
            ->leftJoin('user AS p', 'p.id = c.student_id')
            ->leftJoin('user_public_info AS i', 'i.user_id = c.student_id')
            ->where("i.purchase > 0 AND i.kefu_id_re = :id  AND c.time_class <= :end AND c.time_end >= :time AND (c.time_class >= :start OR c.status_bit & 32 = 0 OR c.status_bit & 64 = 0 OR student_net = '很差'  OR teacher_net = '很差') AND (status = 0 OR status = 1)  " . (empty($keyword) ? '' : " AND (p.nick LIKE '%$keyword%' OR p.mobile LIKE '%$keyword%')"),
                [':id' => Yii::$app->user->identity->id, ':start' => $start,':end' => $end,':time' => $time]
            )
            ->count();
    }

    public function getClassCheckList($keyword, $num)
    {
        $time  = time();
        $start = $time - 300;
        $end   = $time + 300;


        return  ClassRoom::find()
            ->alias('c')
            ->select('c.id, c.student_id, c.teacher_id, c.time_class AS start, c.time_end AS end, c.is_ex_class, c.course_info, ci.if_class_id, i.open_id, p.nick AS user_nick, p.mobile AS user_mobile, t.nick AS teacher_nick, t.mobile AS teacher_mobile, is_firstLogin,  p.is_first, p.chat_token AS userToken, t.chat_token AS teacherToken, c.teacher_net AS t_net, c.student_net AS s_net')
            ->leftJoin('user AS p', 'p.id = c.student_id')
            ->leftJoin('user_teacher AS t', 't.id = c.teacher_id')
            ->leftJoin('user_public_info AS i', 'i.user_id = c.student_id')
            ->leftJoin('(SELECT class_id AS if_class_id FROM class_image GROUP BY class_id) as ci', 'ci.if_class_id = c.id')
            ->where("i.purchase > 0 AND i.kefu_id_re = :id AND c.time_class <= :end AND c.time_end >= :time AND (c.time_class >= :start OR c.status_bit & 32 = 0 OR c.status_bit & 64 = 0 OR student_net = '很差'  OR teacher_net = '很差') AND (status = 0 OR status = 1) " . (empty($keyword) ? '' : " AND (p.nick LIKE '%$keyword%' OR p.mobile LIKE '%$keyword%')"),
                [':id' => Yii::$app->user->identity->id, ':start' => $start,':end' => $end,':time' => $time]
            )
            ->offset(($num - 1) * 10)
            ->limit(10)
            ->asArray()
            ->all();
    }

    public function getBuyClassRoomInfo($studentId)
    {
        return ClassRoom::find()
                ->select('time_class')
                ->where('student_id = :student_id', [':student_id' => $studentId])
                ->andWhere(['is_ex_class' => 1])
                ->andWhere('status != 2 AND status != 3')
                ->andWhere(['is_deleted' => 0])
                ->orderBy('time_class ASC')
                ->column();
    }

    public function getBuyClassEditHistoryInfo($studentId)
    {
        return ClassEditHistory::find()
            ->select('price, amount, type, time_created')
            ->where('student_id = :student_id', [':student_id' => $studentId])
            ->andWhere('price >= 0')
            ->andWhere(['is_add' => 1, 'is_success' => 1, 'is_deleted' => 0])
            ->orderBy('time_created ASC')
            ->all();
    }

    public function getBuyClassLeftInfo($studentId)
    {
        $sql = 'SELECT id, type, CONV(left_bit,2,10) AS left_bit, name, total_amount, order_id, amount, ac_amount FROM class_left WHERE user_id = :student_id ORDER BY type ASC';

        return Yii::$app->db->createCommand($sql)
                        ->bindValues([':student_id' => $studentId])
                        ->queryAll();
    }

    public function getClassQuitDicTeacherName($teacherId, $classId)
    {
        return ClassLog::find()
                ->alias('l')
                ->select('q.name AS t_status')
                ->leftJoin('class_quit_dic AS q', 'q.id = l.type')
                ->where('user_id = :teacher_id AND class_id = :class_id AND role = 1 ', [':teacher_id' => $teacherId,':class_id' => $classId])
                ->orderBy('l.id  DESC')
                ->asArray()
                ->one();
    }

    public function getClassQuitDicUserName($userId, $classId)
    {
        return ClassLog::find()
                ->alias('l')
                ->select('q.name AS s_status')
                ->leftJoin('class_quit_dic AS q', 'q.id = l.type')
                ->where('user_id = :user_id AND class_id = :class_id AND role = 0', [':user_id' => $userId,':class_id' => $classId])
                ->orderBy('l.id  DESC')
                ->asArray()
                ->one();
    }

    public function getComplainContent($openId)
    {
        time()- 604800;
        return Complain::find()
                    ->select('id')
                    ->where('open_id = :open_id', [':open_id' => $openId])
                    ->one();
    }

    public function countPurchaseCourse($timeStart, $timeEnd)
    {
        $count = ClassRoom::find()
            ->alias('c')
            ->leftJoin('user_public_info AS i', 'i.user_id = c.student_id')
            ->where('i.kefu_id_re = :id AND c.is_deleted = 0 AND (c.status = 0 OR c.status = 1) AND c.time_class BETWEEN :timeStart AND :timeEnd',
                [
                    ':id' => Yii::$app->user->identity->id,
                    ':timeStart' => $timeStart,
                    ':timeEnd' => $timeEnd
                ])
            ->count();

        //var_dump($count);
        return $count;
    }

    public function getPurchaseCourseList($timeStart, $timeEnd, $num)
    {
        $sql = "SELECT mc.formal_min_time_class, ci.ci_class_id, c.course_info,`c`.`id` AS `class_id`, `c`.`student_id` AS `id`, `c`.`time_class`, `c`.`status`, `c`.`marks`,
              `u`.`nick`, `u`.`mobile`, `i`.`open_id`, `tec`.`nick` AS `tec_nick`, `c2`.`class_finish_time`, `cp`.`complain_time` 
              FROM `class_room` `c` 
              LEFT JOIN `user_public` `u` ON u.user_id = c.student_id 
              LEFT JOIN `user_public_info` `i` ON i.user_id = u.user_id 
              LEFT JOIN `user_teacher` `tec` ON tec.id = c.teacher_id 
              LEFT JOIN (SELECT student_id, COUNT(*) AS class_finish_time FROM class_room WHERE status = 1 AND is_deleted = 0 GROUP BY student_id) AS c2 ON c2.student_id = c.student_id 
              LEFT JOIN (SELECT open_id, COUNT(*) AS complain_time FROM complain GROUP BY open_id) AS cp ON cp.open_id = i.open_id 
              LEFT JOIN (SELECT class_id AS ci_class_id FROM class_image GROUP BY ci_class_id) AS ci ON ci.ci_class_id = c.id 
              LEFT JOIN (SELECT student_id, MIN(time_class) AS formal_min_time_class
                        FROM class_room where is_ex_class = 0 AND is_deleted = 0 AND (status = 0 OR status = 1) GROUP BY student_id) AS mc ON mc.student_id = c.student_id  
              WHERE i.kefu_id_re = :id AND c.is_deleted=0 AND (c.status = 0 OR c.status = 1) AND c.time_class BETWEEN :timeStart AND :timeEnd ORDER BY `c`.`time_class` LIMIT :offset, :limit";

        $list =  Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':id' => Yii::$app->user->identity->id,
                ':timeStart' => $timeStart,
                ':timeEnd' => $timeEnd,
                ':offset' => ($num - 1) * 8,
                ':limit' => 8
            ])->queryAll();

//        echo '<pre>';
//        var_dump($list);die();
        return $list;
    }

    public function getRelateClass($timeStart, $timeEnd, $classFilter, $studentId)
    {
        return ClassRoom::find()
            ->alias('c')
            ->select('c.id, c.time_class, c.time_end, c.is_ex_class, t.nick as tname, u.nick as uname')
            ->leftJoin('user as u', 'u.id = c.student_id')
            ->leftJoin('user_teacher as t', 't.id = c.teacher_id')
            ->where('c.time_class >= :timeStart', [':timeStart' => $timeStart])
            ->andWhere('c.time_class < :timeEnd', [':timeEnd' => $timeEnd])
            ->andWhere("(t.nick LIKE '%$classFilter%' OR t.mobile LIKE '%$classFilter%')")
            ->andWhere(empty($studentId) ? "" : "u.id = $studentId")
            ->asArray()
            ->all();
    }

    public function getStudentFixTimeInfo($studentId)
    {
        return  StudentFixTime::find()
                    ->alias('s')
                    ->select('s.id, s.week, s.time, s.class_type, s.teacher_id, t.nick as teacher_name, s.instrument_id, i.name as instrument_name, t.gender')
                    ->leftJoin('user_teacher as t', 't.id = s.teacher_id')
                    ->leftJoin('instrument as i', 'i.id = s.instrument_id')
                    ->where(['student_id' => $studentId])
                    ->asArray()
                    ->all();
    }

    public function studentTimeExit($teacherId, $week, $studentId)
    {
        $sql = "SELECT CONV(time_bit,2,10) AS time_bit FROM student_fix_time WHERE teacher_id = :teacher_id AND week = :week AND student_id != :student_id";

        return Yii::$app->db->createCommand($sql)
                        ->bindValues([
                            ':teacher_id' => $teacherId,
                            ':week' => $week,
                            ':student_id' => $studentId
                        ])->queryColumn();
    }

    public function getTeacherFixedTime($teacherId, $week)
    {
        $sql = "SELECT time_bit FROM teacher_info WHERE teacher_id = :teacher_id AND week = :week";

        return Yii::$app->db->createCommand($sql)
                    ->bindValues([
                        'teacher_id' => $teacherId,
                        ':week' => $week
                    ])->queryScalar();
    }

    public function unfinishedClass($studentId)
    {
        return  ClassRoom::find()
                    ->alias('s')
                    ->select('s.*,t.nick')
                    ->leftJoin('user_teacher AS t', 't.id = s.teacher_id')
                    ->where('s.student_id = :student_id', [':student_id' => $studentId])
                    ->andWhere('s.status = 0 AND s.is_deleted = 0')
                    ->orderBy('s.time_class DESC')
                    ->asArray()
                    ->all();
    }

    public function getLeftInfoByClassIds($classIds)
    {
        $sql = "SELECT left_id, history_id FROM class_room WHERE id IN (".implode(',', $classIds).")";

        return Yii::$app->db->createCommand($sql)->queryAll();
    }

    public function getClassList($studentId, $timeStart, $timeEnd)
    {
        $sql = "SELECT c.id, h.type, c.time_class, c.time_end, c.instrument_id, c.teacher_id, IFNULL(ut.nick,'') as teacher_name, c.marks, c.status, c.is_deleted, c.is_ex_class, c.is_problem, c.problem_marks, c.course_info, CONV(c.status_bit,2,10) AS status_bit FROM class_room AS c"
            . " LEFT JOIN user_teacher AS ut ON ut.id = c.teacher_id"
            . " LEFT JOIN class_edit_history AS h ON h.id = c.history_id"
            . " WHERE c.is_deleted = 0 AND c.student_id = :student_id"
            . (empty($timeStart) ? '' : " AND c.time_class >= $timeStart AND c.time_class < $timeEnd")
            . " ORDER BY c.time_class ASC";

        return Yii::$app->db->createCommand($sql)->bindValue(':student_id', $studentId)
            ->queryAll();
    }

    public function getClassFailList($studentId, $timeStart, $timeEnd)
    {
        $sql = "SELECT c.time_class FROM class_fail AS f"
            . " LEFT JOIN class_room AS c ON c.id = f.class_id"
            . " WHERE c.student_id = :student_id AND c.time_class >= :time_class AND c.time_class < :time_end AND f.is_deleted = 0";
        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':student_id' => $studentId,
                ':time_class' => $timeStart,
                ':time_end' => $timeEnd
            ])
            ->queryColumn();
    }

    public function getClassLeftInfo($studentId)
    {
        $sql = "SELECT class_left.*, CONV(left_bit,2,10) as left_bit,instrument.name as instrument_name FROM class_left "
               ."LEFT JOIN instrument ON instrument.id = class_left.instrument_id "
                . "WHERE user_id = :user_id AND ac_amount > 0 AND left_bit & 4 = 0 ORDER BY type ASC";
        
        return Yii::$app->db->createCommand($sql)
            ->bindValue(':user_id', $studentId)
            ->queryAll();
    }

    public function getClassEditHistoryCount($studentId)
    {
        return  ClassEditHistory::find()
                    ->where('student_id = :student_id', [':student_id' => $studentId])
                    ->andWhere('price > 0')
                    ->andWhere(['is_add' => 1, 'is_success' => 1, 'is_deleted' => 0])
                    ->count();
    }

    public function getClassLeftAmountCount($leftId)
    {
        return  ClassLeft::find()
                    ->select('amount')
                    ->where(['id' => $leftId])
                    ->scalar();
    }

    public function getRestTeacher($request)
    {
        //获取当前天的凌晨的时间戳
        $timeDay = floor(($request['time_start'] + 8*3600)/86400)*86400 - 28800;
        //根据开始时间，结束时间，排课周数，当天凌晨时间，获取sql条件（一周或几周）
        $sqlWhere = $this->getWeekClassSqlWhere($request['time_start'], $request['time_end'], $request['weeks'], $timeDay);
        
         //根据二进制获取不能排课的老师（当前时间有休息的老师）
        $teacherBitNotAvil = $this->getTeacherBitNotAvilinfo($request['time_start'], $request['time_end'], $timeDay, $sqlWhere, $request['weeks']);
        return $teacherBitNotAvil;
    }

    public function getTeacherAvailableList($request)
    {
        //获取当前天的凌晨的时间戳
        $timeDay = floor(($request['time_start'] + 8 * 3600) / 86400) * 86400 - 28800;
        //根据开始时间，结束时间，排课周数，当天凌晨时间，获取sql条件（一周或几周）
        $sqlWhere = $this->getWeekClassSqlWhere($request['time_start'], $request['time_end'], $request['weeks'], $timeDay);

        //获取选择时间已经排过课程的老师（当前时间的一周或几周的任意有课老师）
        $teacherClassNotAvil = $this->getTeacherClassNotAvil($request['time_start'], $request['time_end'], $sqlWhere, $request['class_id']);

        //根据二进制获取不能排课的老师（时间段不匹配）
        $teacherBitNotAvil = $this->getTeacherBitNotAvil($request['time_start'], $request['time_end'], $timeDay, $sqlWhere, $request['weeks']);

        // 如果套餐属于体验课  就要符合 1 当天有课的校招老师查询不到 2.有固定课安排的校招老师查询不到
        if ($request['class_type'] === 1) {
            $schoolTeacherNotAvil = $this->getschoolTeacherNotAvil($timeDay);
            // 所有不能排课的人
            $teacherNotAvil = array_merge($teacherClassNotAvil, $teacherBitNotAvil, $schoolTeacherNotAvil);
            // 根据二进制获取不能排课的老师和校招老师的条件限制

            if (!empty($teacherClassNotAvil)) {
                $teacherClassNotAvil = array_merge($teacherClassNotAvil, $schoolTeacherNotAvil);
            } else {
                $teacherClassNotAvil = $schoolTeacherNotAvil;
            }
        } else {
            //将不能排课的老师合并
            $teacherNotAvil = array_merge($teacherClassNotAvil, $teacherBitNotAvil);
        }

        //根据乐器id，获取使用当前乐器类型的老师
        $teacherInsMatch = $this->getTeacherInsMatch($request['instrument_id']);

        //获取当前时间不能上课的老师（时间上不允许）
        if (empty($request["ttype"])) {
            //可利用老师
            $sql = "SELECT u.teacher_type, u.mobile,u.id, u.nick as name, IFNULL(c.counts, 0) as counts, ti.grade as grade, ti.level as level, 0 as is_ex FROM user_teacher AS u"
                . " LEFT JOIN (SELECT teacher_id, COUNT(id) as counts FROM class_room WHERE time_class BETWEEN :start AND :end AND (status = 0 OR status = 1) AND is_deleted = 0 GROUP BY teacher_id) AS c ON c.teacher_id = u.id"
//            . " LEFT JOIN user_teacher_instrument AS uii ON uii.user_id = u.id AND uii.instrument_id = :instrument_1_id AND uii.type = 0"
//            . " LEFT JOIN user_teacher_instrument AS uio ON uio.user_id = u.id AND uio.instrument_id = :instrument_2_id AND uio.type = 1"
                . " LEFT JOIN teacher_instrument AS ti ON ti.teacher_id = u.id AND ti.instrument_id = :instrument_id"

                . " WHERE u.is_disabled = 0 and u.type != 3 "
                . (empty($request["tname"]) ? "" : " AND ( u.nick like '%{$request["tname"]}%')")
                . (empty($teacherInsMatch) ? "" : " AND u.id  IN (".implode(',', $teacherInsMatch).")")
                . (empty($teacherNotAvil) ? '' : " AND u.id NOT IN (".implode(',', $teacherNotAvil).")")
                //. (empty($teacherNotAvil) ? (empty($request["ttype"]) ?"":" and u.id = -100 ") : (empty($request["ttype"]) ? " AND u.id  NOT IN (".implode(',',$teacherNotAvil).")":" AND u.id  IN (".implode(',',$teacherNotAvil).")"))
                . " ORDER BY counts,u.id ASC";
        } else {
            if ($request["ttype"]==1) {
                $sql = "SELECT u.teacher_type, u.mobile,u.id, u.nick as name, IFNULL(c.counts, 0) as counts, ti.grade as grade, ti.level as level, 0 as is_ex FROM user_teacher AS u"
                    . " LEFT JOIN (SELECT teacher_id, COUNT(id) as counts FROM class_room WHERE time_class BETWEEN :start AND :end AND (status = 0 OR status = 1) AND is_deleted = 0 GROUP BY teacher_id) AS c ON c.teacher_id = u.id"
//                . " LEFT JOIN user_teacher_instrument AS uii ON uii.user_id = u.id AND uii.instrument_id = :instrument_1_id AND uii.type = 0"
//                . " LEFT JOIN user_teacher_instrument AS uio ON uio.user_id = u.id AND uio.instrument_id = :instrument_2_id AND uio.type = 1"
                    . " LEFT JOIN teacher_instrument AS ti ON ti.teacher_id = u.id AND ti.instrument_id = :instrument_id"
                    . " WHERE u.is_disabled = 0 and u.type != 3 "
                    . (empty($request["tname"]) ? "" : " AND ( u.nick like '%{$request["tname"]}%')")
                    . (empty($teacherInsMatch) ? " " : " AND u.id IN (".implode(',', $teacherInsMatch).")")
                    . (empty($teacherBitNotAvil) ? ' AND u.id = -100 ' : " AND u.id  IN (".implode(',', $teacherBitNotAvil).")")
                    . (empty($teacherClassNotAvil) ? '' : " AND u.id NOT IN (".implode(',', $teacherClassNotAvil).")")
                    . " ORDER BY counts,u.id ASC";
            } else {
                //返回可利用的测试老师
                $sql = "SELECT u.teacher_type, u.mobile,u.id, u.nick as name, IFNULL(c.counts, 0) as counts, ti.grade as grade, ti.level as level, 0 as is_ex FROM user_teacher AS u"
                    . " LEFT JOIN (SELECT teacher_id, COUNT(id) as counts FROM class_room WHERE time_class BETWEEN :start AND :end AND (status = 0 OR status = 1) AND is_deleted = 0 GROUP BY teacher_id) AS c ON c.teacher_id = u.id"
//            . " LEFT JOIN user_teacher_instrument AS uii ON uii.user_id = u.id AND uii.instrument_id = :instrument_1_id AND uii.type = 0"
//            . " LEFT JOIN user_teacher_instrument AS uio ON uio.user_id = u.id AND uio.instrument_id = :instrument_2_id AND uio.type = 1"
                    . " LEFT JOIN teacher_instrument AS ti ON ti.teacher_id = u.id AND ti.instrument_id = :instrument_id"
                    . " WHERE u.is_disabled = 0 and u.type = 3"
                    . (empty($request["tname"]) ? "" : " AND ( u.nick like '%{$request["tname"]}%')")
                    . (empty($teacherInsMatch) ? "" : " AND u.id  IN (".implode(',', $teacherInsMatch).")")
                    . (empty($teacherNotAvil) ? '' : " AND u.id NOT IN (".implode(',', $teacherNotAvil).")")
                    //. (empty($teacherNotAvil) ? (empty($request["ttype"]) ?"":" and u.id = -100 ") : (empty($request["ttype"]) ? " AND u.id  NOT IN (".implode(',',$teacherNotAvil).")":" AND u.id  IN (".implode(',',$teacherNotAvil).")"))
                    . " ORDER BY counts,u.id ASC";
            }
        }
        
        $teacherList = Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':start' => $timeDay,
                ':end' => $timeDay + 86400,
                ':instrument_id' => $request['instrument_id'],
            ])
            ->queryAll();
    

        return $teacherList;
    }

    public function getWeekClassSqlWhere($timeClass, $timeEnd, $weeks, $timeDay)
    {
        $sqlClassWhere = '';
        $sqlBitWhere = '';
        $sql_rest_where="";
        for ($i=1; $i<$weeks; $i++) {
            $timeClass += 604800;
            $timeEnd += 604800;
            $timeDay += 604800;
            $sqlClassWhere .= " OR (time_end >= $timeClass AND time_class <= $timeEnd)";
            $sqlBitWhere .= " OR time_day = $timeDay";
            $sql_rest_where=" OR (time_end >= $timeEnd AND time_start <= $timeClass)";
        }
        $sqlWhere = array('sql_class_where' => $sqlClassWhere, 'sql_bit_where' => $sqlBitWhere,'sql_rest_where'=>$sql_rest_where);
        return $sqlWhere;
    }

    public function getTeacherClassNotAvil($timeClass, $timeEnd, $sqlWhere, $classId)
    {
        $sql = "SELECT teacher_id FROM class_room WHERE is_deleted = 0 AND status != 2 AND ((time_end >= :time_end AND time_class <= :time_class)".$sqlWhere['sql_class_where'].")".(!empty($classId) ? " AND id != $classId" : "")." GROUP BY teacher_id";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':time_end' => $timeClass, ':time_class' => $timeEnd])
            ->queryColumn();
    }


    public function getschoolTeacherNotAvil($timeStart)
    {
        // 所有被安排固定课的校招老师
        $allSchoolTeacher = UserTeacher::find()
            ->select('id')
            ->where(['teacher_type' => 2])
            ->asArray()
            ->column();

        $fixTeacher = StudentFixTime::find()
            ->select('DISTINCT(teacher_id)')
            ->asArray()
            ->column();

        $list = array();
        if (!empty($allSchoolTeacher) && !empty($fixTeacher)) {
            $planFixClassSchoolTeacher = array();
            foreach ($allSchoolTeacher as $v) {
                foreach ($fixTeacher as $item) {
                    if ($v == $item) {
                        $planFixClassSchoolTeacher[] = $v;
                    }
                }
            }

            $remainTeacher = array_diff($allSchoolTeacher, $planFixClassSchoolTeacher);
            $haveClass = array();
            if (!empty($remainTeacher)) {
                foreach ($remainTeacher as $v) {
                    $isHaveClass = ClassRoom::find()
                        ->where('teacher_id =:id AND time_class > :time_start AND time_class < :time_end AND is_deleted = 0 ', [
                            ':id' => $v,
                            ':time_start' => $timeStart,
                            ':time_end' => $timeStart + 86400
                        ])
                        ->count();
                    if (!empty($isHaveClass)) {
                        $haveClass[] = $v;
                    }
                }
            }
            $list = array_merge($planFixClassSchoolTeacher, $haveClass);
        }

        return $list;
    }

    public function getTeacherBitNotAvil($timeClass, $timeEnd, $timeDay, $sqlWhere, $weeks)
    {
        //判断当前选择时间是周几
        $week = date('w', $timeClass);
        $week = $week == 0 ? 7 : $week;
        //获取加7天后的结束时间
        //$timeFinal = $timeEnd + (7 * 86400) * ($weeks - 1);
        $this->getTimeBit($timeClass, $timeEnd);
        $class_bit = BinaryDecimal::getClassBit($timeClass, $timeEnd);
        /*
        //获取日课表中当前时间不能排课的老师id，即日课表中，当前时间与老师日课表中时间重合，当前时间不能排课的老师
        $sql = "SELECT user_id FROM timetable WHERE time_bit & :time_bit > 0 AND (time_day = :time_day".$sqlWhere['sql_bit_where'].")";
        $teacherTimetable = Yii::$app->db->createCommand($sql)
            ->bindValues([':time_bit' => $num, ':time_day' => $timeDay])
            ->queryColumn();

        //获取当前天有日课表的老师
        $sql = "SELECT user_id FROM timetable WHERE time_day = :time_day".$sqlWhere['sql_bit_where'];
        $sqlNotIn = Yii::$app->db->createCommand($sql)
            ->bindValue(':time_day', $timeDay)
            ->queryColumn();

       */
        
        //获取所选则时间内，有休息的老师
//        $sql1 = "SELECT teacher_id FROM statistics_teacher_rest WHERE ((time_end >= :time_end AND time_start <= :time_start)".$sqlWhere['sql_rest_where'].") AND !(tmp_leave = 0 and all_leave = 0 and pause = 0 ) ";
//        $teacherTimetable = Yii::$app->db->createCommand($sql1)
//            ->bindValues([':time_start' => $timeClass, ':time_end' => $timeEnd])
//            ->queryColumn();
        
//        $sqlin = "SELECT teacher_id FROM statistics_teacher_rest WHERE time_day = :time_day AND !(tmp_leave = 0 and all_leave = 0 and pause = 0 ) ";
//        $sqlNotIn = Yii::$app->db->createCommand($sqlin)
//            ->bindValues([':time_day' => $timeDay])
//            ->queryColumn();
//
//        $sql2 = "SELECT teacher_id FROM teacher_info WHERE time_bit & :time_bit > 0 AND week = :week " . (empty($sqlNotIn) ? '' : " AND teacher_id NOT IN(".implode(',',$sqlNotIn).")");
//        $teacherFixedTime = Yii::$app->db->createCommand($sql2)
//            ->bindValues([':time_bit' => $num, ':week' => $week])
//            ->queryColumn();

        $salary_compute = new SalaryCompute();

        //所有上课时间当天请假的
        $sql1 = "SELECT teacher_id, time_start, time_end FROM statistics_teacher_rest WHERE (time_day = :time_day".$sqlWhere['sql_bit_where'].") AND !(tmp_leave = 0 and all_leave = 0 and pause = 0 ) ";
        $teacherRestList = Yii::$app->db->createCommand($sql1)
            ->bindValues([':time_day' => $timeDay])
            ->queryAll();
        $teacherTimetable = array();

        if (!empty($teacherRestList)) {
            foreach ($teacherRestList as $item) {
                $rest_bit = BinaryDecimal::getRestBit($item['time_start'], $item['time_end']);
                if (($rest_bit & $class_bit) > 0) {
                    $teacherTimetable[] = $item['teacher_id'];
                }
            }
        }
        $teacherFixedTime = $salary_compute->getTeacherNotAvailableByClass($timeClass, $timeEnd)['data'];

        for ($i=1; $i<$weeks; $i++) {
            $timeClass += 604800;
            $timeEnd += 604800;
            $teacher_fix_ids = $salary_compute->getTeacherNotAvailableByClass($timeClass, $timeEnd)['data'];
            $teacherFixedTime = array_merge($teacherFixedTime, $teacher_fix_ids);
        }
        return array_merge($teacherTimetable, $teacherFixedTime);
       // return $teacherFixedTime;
    }
    
    public function getTeacherBitNotAvilinfo($timeClass, $timeEnd, $timeDay, $sqlWhere, $weeks)
    {
        //判断当前选择时间是周几
        $week = date('w', $timeClass);
        $week = $week == 0 ? 7 : $week;
        //获取加7天后的结束时间
        //$timeFinal = $timeEnd + (7 * 86400) * ($weeks - 1);
       
        $num = $this->getTimeBit($timeClass, $timeEnd);

        //获取所选则时间内，有休息的老师
        $sql = "SELECT teacher_id,nick FROM statistics_teacher_rest "
                ." left join user_teacher  on user_teacher.id = statistics_teacher_rest.teacher_id "
                . "WHERE ((time_end >= :time_end AND time_start <= :time_start)".$sqlWhere['sql_rest_where'].") AND (tmp_leave != 0 and all_leave != 0 and pause != 0 ) ";
        $sqlNotIn = Yii::$app->db->createCommand($sql)
            ->bindValues([':time_start' => $timeClass, ':time_end' => $timeEnd])
            ->queryColumn();
        
        
        $sql = "SELECT teacher_id,nick FROM teacher_info "
                ." left join user_teacher  on user_teacher.id = teacher_info.teacher_id "
                . "WHERE time_bit & :time_bit > 0 AND week = :week " . (empty($sqlNotIn) ? '' : " AND teacher_id NOT IN(".implode(',', $sqlNotIn).")");
        $teacherFixedTime = Yii::$app->db->createCommand($sql)
            ->bindValues([':time_bit' => $num, ':week' => $week])
            ->queryColumn();

        //return array_merge($teacherTimetable, $teacherFixedTime);
        return $teacherFixedTime;
    }

    public function getTimeBit($timeClass, $timeEnd)
    {
        $timeStr = date('H:i', $timeClass);
        $timeArr = explode(':', $timeStr);
        $index = 2*$timeArr[0] + ($timeArr[1] === '00' ? 0 : 1);
        $num = pow(2, $index);
        $num += ($timeEnd - $timeClass == 1500 ? 0 : pow(2, $index+1));
        return $num;
    }

    //根据乐器id获取该乐器类型的老师
//    public function getTeacherInsMatch($instrumentId)
//    {
//        $sql = "SELECT user_id FROM user_teacher_instrument WHERE instrument_id = :instrument_id GROUP BY user_id";
//        return Yii::$app->db->createCommand($sql)
//            ->bindValue(':instrument_id', $instrumentId)
//            ->queryColumn();
//    }

    public function getTeacherInsMatch($instrumentId)
    {
        $sql = "SELECT teacher_id FROM teacher_instrument WHERE instrument_id = :instrument_id";
        return Yii::$app->db->createCommand($sql)
            ->bindValue(':instrument_id', $instrumentId)
            ->queryColumn();
    }

    public function getTeacherHaveExClass($request)
    {
        $timeDay = floor(($request['time_start'] + 8*3600)/86400)*86400 - 28800;
        $sqlWhere = $this->getWeekClassSqlWhere($request['time_start'], $request['time_end'], $request['weeks'], $timeDay);
        $sql = "SELECT id, teacher_id, is_ex_class, CONV(status_bit,2,10) as bit FROM class_room WHERE is_deleted = 0 AND status = 0 AND ((time_end >= :time_end AND time_class <= :time_class)".$sqlWhere['sql_class_where'].")".(!empty($classId) ? " AND id != $classId" : "");
        $classInfo = Yii::$app->db->createCommand($sql)
            ->bindValues([':time_end' => $request['time_start'], ':time_class' => $request['time_end']])
            ->queryAll();
        list($teacherEx, $redList) = $this->getTeacherEx($classInfo);

        if (!empty($teacherEx)) {
            $sql = "SELECT u.teacher_type,  u.mobile,u.id, u.nick as name, IFNULL(c.counts, 0) as counts, ti.grade as grade, ti.level as level, 1 as is_ex FROM user_teacher AS u"
                . " LEFT JOIN (SELECT teacher_id, COUNT(id) as counts FROM class_room WHERE time_class BETWEEN :start AND :end AND (status = 0 OR status = 1) AND is_deleted = 0 GROUP BY teacher_id) AS c ON c.teacher_id = u.id"
//                . " LEFT JOIN user_teacher_instrument AS uii ON uii.user_id = u.id AND uii.instrument_id = :instrument_1_id AND uii.type = 0"
//                . " LEFT JOIN user_teacher_instrument AS uio ON uio.user_id = u.id AND uio.instrument_id = :instrument_2_id AND uio.type = 1"
                . " LEFT JOIN teacher_instrument AS ti ON ti.teacher_id = u.id AND ti.instrument_id = :instrument_id"

                . " WHERE u.is_disabled = 0 AND u.id IN (".implode(',', $teacherEx).") AND ti.teacher_id IS NOT NULL"
                . (empty($request['tname']) ? '' : " AND u.nick LIKE '%{$request['tname']}%'")
                . " ORDER BY counts ASC";

            $teacherExList = Yii::$app->db->createCommand($sql)
                ->bindValues([
                    ':start' => $timeDay,
                    ':end' => $timeDay + 86400,
//                    ':instrument_1_id' => $request['instrument_id'],
                    ':instrument_id' => $request['instrument_id']
                ])
                ->queryAll();

            foreach ($teacherExList as &$t) {
                $t['is_red'] = in_array($t['id'], $redList) ? 1 : 0;
            }

            return $teacherExList;
        }
        return [];
    }

    private function getTeacherEx($classInfo)
    {
        $teacherEx = array();
        $teacherBuy = array();
        $teacher = array();
        $redList = array();

        foreach ($classInfo as $class) {
            if ($class['is_ex_class'] == 1) {
                $teacherEx[] = $class['teacher_id'];
                if (($class['bit'] & 8) == 8) {
                    $redList[] = $class['teacher_id'];
                }
            } else {
                $teacherBuy[] = $class['teacher_id'];
            }
        }

        $teacherEx = array_unique($teacherEx);
        $teacherBuy = array_unique($teacherBuy);

        if (!empty($teacherEx) && !empty($teacherBuy)) {
            foreach ($teacherEx as $ex) {
                $isFind = 0;
                foreach ($teacherBuy as $buy) {
                    if ($ex == $buy) {
                        $isFind = 1;
                        break;
                    }
                }
                if ($isFind == 0) {
                    $teacher[] = $ex;
                }
            }
            return [$teacher, $redList];
        } else {
            return [$teacherEx, $redList];
        }
    }

    public function countNoTeacher($timeClass, $timeEnd, $instrumentId)
    {
        $sql = "SELECT COUNT(id) FROM class_room WHERE teacher_id = 0 AND is_deleted = 0 AND status != 2 AND instrument_id = :instrument AND time_end >= :time_end AND time_class <= :time_class";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':time_end' => $timeClass,
                ':time_class' => $timeEnd,
                ':instrument' => $instrumentId
            ])
            ->queryScalar();
    }


    public function checkStudentAvailable($studentId, $timeStart, $timeEnd, $classId = 0)
    {
        $sql = "SELECT COUNT(id) FROM class_room WHERE student_id = :student_id AND status = 0 AND is_deleted = 0 AND time_end >= :time_end AND time_class <= :time_class".(empty($classId) ? "" : " AND id != $classId");
        $num = Yii::$app->db->createCommand($sql)
            ->bindValues([':student_id' => $studentId, ':time_end' => $timeStart, ':time_class' => $timeEnd])
            ->queryScalar();
        return empty($num) ? true : false;
    }



    public function checkIsFirstEx($studentId)
    {
        $sql = "SELECT COUNT(id) FROM class_room WHERE student_id = :student_id AND is_ex_class = 1 AND is_first_ex = 1 AND status != 2 AND is_deleted = 0";
        $result = Yii::$app->db->createCommand($sql)->bindValue(':student_id', $studentId)->queryScalar();
        return empty($result) ? true : false;
    }
    
    public function getClassFailInfo($teacherId, $timeStart, $timeEnd, $classId)
    {
        $sql = "SELECT c.id, c.left_id, c.history_id, c.student_id, c.instrument_id, h.type FROM class_room AS c"
            . " LEFT JOIN class_edit_history AS h ON h.id = c.history_id"
            . " WHERE c.teacher_id = :teacher_id AND c.is_ex_class = 1 AND c.time_end >= :time_end AND c.time_class <= :time_class AND c.status = 0 AND c.is_deleted = 0 and c.id != :id ";
        return Yii::$app->db->createCommand($sql)
            ->bindValues([':teacher_id' => $teacherId, ':time_end' => $timeStart, ':time_class' => $timeEnd,':id'=>$classId])
            ->queryAll();
    }

    public function getClassFailInfoByFix($teacherId, $timeStart, $timeEnd, $classId)
    {
        $sql = "SELECT c.id, c.left_id, c.history_id, c.student_id, c.instrument_id, h.type FROM class_room AS c"
            . " LEFT JOIN class_edit_history AS h ON h.id = c.history_id"
            . " WHERE c.teacher_id = :teacher_id AND c.is_ex_class = 0 AND c.time_end >= :time_end AND c.time_class <= :time_class AND c.status = 0 AND c.is_deleted = 0 and c.id != :id ";
        return Yii::$app->db->createCommand($sql)
            ->bindValues([':teacher_id' => $teacherId, ':time_end' => $timeStart, ':time_class' => $timeEnd,':id'=>$classId])
            ->queryAll();
    }

    public function getClassLeftTermId($id)
    {
        return ClassLeft::findOne(['id' => $id]);
    }

    public function getClassRoomByteacherId($id)
    {
        return  ClassRoom::find()
                        ->select('teacher_id')
                        ->where(['id' => $id])
                        ->one();
    }
        
    public function getClassTimeById($classId)
    {
        return ClassRoom::find()
            ->select('time_class, time_end')
            ->where(['id' => $classId])
            ->asArray()
            ->one();
    }

    public function getClassTimeAndStudentName($classId)
    {
        return ClassRoom::find()
            ->alias('c')
            ->select('time_class, nick, student_id, c.teacher_id')
            ->leftJoin('user_public as p', 'p.user_id = c.student_id')
            ->where(['c.id' => $classId])
            ->asArray()
            ->one();
    }

    public function getClassLeftByLeftId($leftId)
    {
        return ClassLeft::findOne($leftId)->toArray();
    }


    public function getRowById($classId)
    {
        $sql = "SELECT c.*, h.type, i.name AS instrument_name FROM class_room AS c LEFT JOIN class_edit_history AS h ON h.id = c.history_id"
            . " LEFT JOIN instrument AS i ON c.instrument_id = i.id WHERE c.id = :class_id";
        return Yii::$app->db->createCommand($sql)
            ->bindValue(':class_id', $classId)
            ->queryOne();
    }

    public function getMessageRowById($userId)
    {
        $sql = "SELECT id, nick, is_auth, username, ex_class_times, buy_class_times, head_icon, password, channel_id, channel_id_self FROM user WHERE id=:id";
        return Yii::$app->db->createCommand($sql)
            ->bindValue(':id', $userId)
            ->queryOne();
    }

    public function getMessageOpenId($classId)
    {
        return ClassRoom::find()
                        ->alias('c')
                        ->select('open_id')
                        ->leftJoin('user_public_info as u', 'u.user_id = c.student_id')
                        ->where(['c.id' => $classId])
                        ->scalar();
    }

    public function getRowByTeacherId($userId)
    {
        $sql = "SELECT id, mobile, nick, teacher_level, head_icon, password FROM user_teacher WHERE id = :id";
        return Yii::$app->db->createCommand($sql)->bindValue(':id', $userId)->queryOne();
    }

    public function getClassRoomByMounth($studentId, $timeStart, $timeEnd)
    {
        return ClassRoom::find()
                    ->alias('c')
                    ->leftJoin('class_record AS r', 'r.class_id = c.id')
                    ->where(['c.student_id' => $studentId, 'c.status' => 2])
                    ->andWhere('c.time_class >= :start AND c.time_class < :end AND r.time_created + 3600 > c.time_class', [
                        ':start' => $timeStart,
                        ':end' => $timeEnd
                    ])->count();
    }


    public function getCancelClassInfo($studentId, $timeStart, $timeEnd)
    {
        return ClassRoom::find()
            ->alias('c')
            ->select('c.time_class, r.time_created as cancel_time')
            ->leftJoin('class_record AS r', 'r.class_id = c.id')
            ->where(['c.student_id' => $studentId, 'c.status' => 2])
            ->andWhere('c.time_class >= :start AND c.time_class < :end AND r.time_created + 3600 > c.time_class', [
                ':start' => $timeStart,
                ':end' => $timeEnd
            ])
            ->limit(2)
            ->asArray()
            ->all();
    }

    public function getClassRoomInfo($id)
    {
        return ClassRoom::find()
                    ->alias('c')
                    ->select('c.*, i.name as instrument_name')
                    ->leftJoin('instrument as i', 'i.id = c.instrument_id')
                    ->where(['c.id' => $id])
                    ->asArray()
                    ->one();
    }

    public function getClassFailBaseInfo($classId)
    {
        return ClassFail::find()
                    ->where(['class_id' => $classId, 'is_deleted' => 0])
                    ->one();
    }

    public function getClassInfoByIds($classId)
    {
        $sql = "SELECT c.id as class_id, u.nick,c.student_id, c.left_id, h.type as time_type, c.time_class, c.time_end, c.instrument_id as instrument, c.teacher_id as teacher, c.marks, c.is_ex_class as class_type, CONV(c.status_bit,2,10) AS status_bit FROM class_room AS c"

            . " LEFT JOIN class_edit_history AS h ON h.id = c.history_id "
            ."LEFT JOIN user_teacher AS u ON u.id = c.teacher_id "
            . " WHERE c.id = :class_id";

        return Yii::$app->db->createCommand($sql)->bindValue(':class_id', $classId)
            ->queryOne();
    }


    public function getTeacherClassList($teacherId, $timeStart, $timeEnd)
    {
        $sql = "SELECT c.time_class, c.time_end, ut.nick as teacher_name, c.is_ex_class, us.nick as student_name, us.mobile, us.remark FROM class_room AS c"
            . " LEFT JOIN user_teacher AS ut ON ut.id = c.teacher_id"
            . " LEFT JOIN user AS us ON us.id = c.student_id"
            . " WHERE c.teacher_id = :teacher_id AND c.status != 2 AND c.is_deleted = 0 AND c.time_class >= :time_start AND c.time_class < :time_end"
            . " ORDER BY c.time_class ASC";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':teacher_id' => $teacherId, ':time_start' => $timeStart, ':time_end' => $timeEnd])
            ->queryAll();
    }

    public function getRowByClassId($classId)
    {
        return ClassRoom::find()
            ->alias('c')
            ->select('c.*, h.type')
            ->leftJoin('class_edit_history AS h', 'h.id = c.history_id')
            ->where(['c.id' => $classId])
            ->asArray()
            ->one();
    }

    public function getTotalExclass()
    {
        return ClassRoom::find()
            ->where('is_ex_class = 1 AND status = 1 AND is_deleted = 0 AND time_class > 1473817794')
            ->count();
    }

    public function getClassLeftRowById($leftId)
    {
        return ClassLeft::findOne($leftId)->toArray();
    }

    public function getStudentIsBuy($userId)
    {
        return ClassEditHistory::find()
            ->where('price > 0 AND is_add = 1 AND is_success = 1 AND is_deleted = 0 AND student_id = :student_id', [':student_id' => $userId])
            ->count();
    }

    public function getClassMonitorCount($kefu, $current, $timeStart, $timeEnd, $keyword, $type, $monitorCourseType)
    {
        $query = ClassRoom::find()
            ->alias('c')
            //->select('c.*,u.nick as userName,u.mobile as userMobile,t.nick as teacherName,t.mobile as teacherMobile')
            ->leftJoin('user as u', 'c.student_id =u.id')
            ->leftJoin('user_teacher as t', 'c.teacher_id =t.id')
            ->leftJoin('(SELECT student_id, MIN(time_class) AS formal_min_time_class
            FROM class_room where is_ex_class = 0 AND is_deleted = 0 AND (status = 0 OR status = 1) GROUP BY student_id) AS mc', 'mc.student_id = c.student_id');

        if (!empty($kefu)) {
            if ($kefu['role'] == 1) {
                $query = $query
                    ->leftJoin('user_public_info AS up', 'up.user_id = c.student_id')
                    ->where('c.is_deleted = 0 and c.status < 2 AND up.kefu_id = :kefu_id', [
                        ':kefu_id' => $kefu['id']
                    ]);
            } else {
                $query = $query
                    ->leftJoin('user_public_info AS up', 'up.user_id = c.student_id')
                    ->where('c.is_deleted = 0 and c.status < 2 AND up.kefu_id_re = :kefu_id_re', [
                        ':kefu_id_re' => $kefu['id']
                    ]);
            }
        } else {
            $query = $query->where('c.is_deleted = 0 and c.status < 2');
        }

        if ($type == 1) { //待开始
            $query = $query->andWhere('c.time_class >= :stime and c.time_class < :etime', [':stime' => $current,':etime' => $timeEnd]);
        } else if ($type == 2) { //进行中
            $query = $query->andWhere('c.time_class < :stime and c.time_end > :etime', [':stime' => $current,':etime' => $current]);
        } else { //已结束
            $query = $query->andWhere('c.time_end < :stime and c.time_end > :etime', [':stime' => $current,':etime' => $timeStart]);
        }

        if (!empty($keyword)) {
            $query = $query->andWhere("u.nick like '%".$keyword."%' or t.nick like '%" .$keyword. "%' or u.mobile like '%" .$keyword. "%' or t.mobile like '%" .$keyword. "%'");
        }

        switch ($monitorCourseType) {
            case 1:
                $query->andWhere("c.is_ex_class = 1 ");
                break;
            case 2:
                $query->andWhere("c.is_ex_class = 0");
                break;
            case 3:
                $query->andWhere("c.time_class = mc.formal_min_time_class");
                break;
            default:
                break;
        }

        //Debug::debug($query->createCommand()->getRawSql());

        return $query->count();
    }

    public function getClassMonitorList($kefu, $current, $timeStart, $timeEnd, $keyword, $type, $page, $monitorCourseType)
    {
        $query = ClassRoom::find()
            ->alias('c')
            ->select('mc.formal_min_time_class, c.*,u.nick as userName,u.mobile as userMobile,u.chat_token as userToken,u.is_first,u.version as u_version,t.is_firstLogin,t.nick as teacherName,t.mobile as teacherMobile,t.chat_token as teacherToken,t.version as t_version')
            ->leftJoin('user as u', 'c.student_id =u.id')
            ->leftJoin('user_teacher as t', 'c.teacher_id =t.id')
            ->leftJoin('(SELECT student_id, MIN(time_class) AS formal_min_time_class
            FROM class_room where is_ex_class = 0 AND is_deleted = 0 AND (status = 0 OR status = 1) GROUP BY student_id) AS mc', 'mc.student_id = c.student_id');

        if (!empty($kefu)) {
            if ($kefu['role'] == 1) {
                $query = $query
                    ->leftJoin('user_public_info AS up', 'up.user_id = c.student_id')
                    ->where('c.is_deleted = 0 and c.status < 2 AND up.kefu_id = :kefu_id', [
                        ':kefu_id' => $kefu['id']
                    ]);
            } else {
                $query = $query
                    ->leftJoin('user_public_info AS up', 'up.user_id = c.student_id')
                    ->where('c.is_deleted = 0 and c.status < 2 AND up.kefu_id_re = :kefu_id_re', [
                        ':kefu_id_re' => $kefu['id']
                    ]);
            }
        } else {
            $query = $query->where('c.is_deleted = 0 and c.status < 2');
        }

        if ($type == 1) { //待开始
            $query = $query->andWhere('c.time_class >= :stime', [':stime' => $current]);
            $query = $query->andWhere('c.time_class < :etime', [':etime' => $timeEnd]);
        } else if ($type == 2) { //进行中
            $query = $query->andWhere('c.time_class < :stime', [':stime' => $current]);
            $query = $query->andWhere('c.time_end > :etime', [':etime' => $current]);
        } else { //已结束
            $query = $query->andWhere('c.time_end < :stime', [':stime' => $current]);
            $query = $query->andWhere('c.time_end > :etime', [':etime' => $timeStart]);
        }

        if (!empty($keyword)) {
            $query = $query->andWhere("u.nick like '%" . $keyword . "%' or t.nick like '%" . $keyword . "%' or u.mobile like '%" . $keyword . "%' or t.mobile like '%" . $keyword . "%'");
        }

        switch ($monitorCourseType) {
            case 1:
                $query->andWhere("c.is_ex_class = 1 ");
                break;
            case 2:
                $query->andWhere("c.is_ex_class = 0");
                break;
            case 3:
                $query->andWhere("c.time_class = mc.formal_min_time_class");
                break;
            default:
                break;
        }
        return $query->orderBy('c.time_class')
            ->offset(($page - 1) * 42)
            ->limit(42)
            ->asArray()
            ->all();
    }

    public function getCourseCount($type, $timeStart, $timeEnd, $passId, $tag, $filter)
    {
        $class_room =  ClassRoom::find()
                        ->select('id')
                        ->leftJoin('user_teacher', 'class_room.teacher_id =user_teacher.id')
                        ->where('is_deleted = 0')
                        ->andWhere('time_class >= :time_start', [':time_start'=>$timeStart])
                        ->andWhere('time_class < :time_end', [':time_end'=>$timeEnd])
                        ->andWhere('status = :type', [':type' => $type]);
                       
        if ($pass_id == 1) {
            $class_room ->andWhere(':current_time - time_end >= 43200', [':current_time'=>time()]);
        }
        if ($pass_id == 2) {
            $class_room ->andWhere(':current_time - time_end >= 72000', [':current_time'=>time()]);
        }
        if ($pass_id == 3) {
            $class_room ->andWhere(':current_time - time_end >= 86400', [':current_time'=>time()]);
        }
        if (!empty($filter)) {
            $class_room ->andWhere("user_teacher.mobile like '%{$filter}%' OR user_teacher.nick like '%{$filter}%'");
        }

//        if ($tag == 0)
//        {
//            $class_room -> andWhere('tag = :tag', [':tag' => 0]);
//        }
//        if ($tag == 1)
//        {
//            $class_room -> andWhere('tag <> :tag', [':tag' => 0]);
//        }
        return $class_room->count();
    }

    public function getCourseInfo($type, $timeStart, $timeEnd, $passId, $pageNum, $tag, $filter)
    {
        $class_room= ClassRoom::find()
                        ->select('class_room.*,wechat_acc.openid,class_record.id as record_id,class_record.time_send,class_record.time_created as fill_time,ci.class_id,class_edit_history.type as class_type,user.nick as userName,user.mobile as userMobile,user.remark_out,user_teacher.nick as teacherName')
                        ->leftJoin('wechat_acc', 'wechat_acc.uid=class_room.student_id')
                        ->leftJoin('class_edit_history', 'class_room.history_id =class_edit_history.id')
                        ->leftJoin('user', 'class_room.student_id =user.id')
                        ->leftJoin('user_teacher', 'class_room.teacher_id =user_teacher.id')
                        ->leftJoin('class_record', 'class_record.class_id = class_room.id')
                        ->leftJoin('(select class_id from class_image group by class_id) as ci', 'ci.class_id = class_room.id')
                        ->leftJoin('teacher_reward_rule', 'teacher_reward_rule.id = class_room.tag')
                        ->where('class_room.is_deleted = 0 and class_room.status = :type and class_room.time_class >= :stime', [':stime' => $timeStart,':type'=>$type])
                        ->andWhere('class_room.time_class < :etime', [':etime' => $timeEnd]);
        if ($is_checked == 1) {
            $class_room ->andWhere(':current_time - time_end >= 43200', [':current_time'=>time()]);
        }
        if ($is_checked == 2) {
            $class_room ->andWhere(':current_time - time_end >= 72000', [':current_time'=>time()]);
        }
        if ($is_checked == 3) {
            $class_room ->andWhere(':current_time - time_end >= 86400', [':current_time'=>time()]);
        }
        if (!empty($filter)) {
            $class_room ->andWhere("user_teacher.mobile like '%{$filter}%' OR user_teacher.nick like '%{$filter}%'");
        }

        return $class_room ->orderBy('class_room.time_class desc')
                            ->offset(($pageNum-1)*8)
                            ->limit(8)
                            ->asArray()
                            ->all();
    }
    
    
    public function getClassInfo($classId)
    {
         return ClassRoom::findOne(['id'=>$classId]);
    }


    public function getClassCountByTeacher($teacherId, $timeStart, $timeEnd, $classType, $isEx)
    {
        $sql = "SELECT IFNULL(COUNT(id),0) FROM class_room WHERE teacher_id = :teacher_id AND status = 1 AND time_class >= :timeStart AND time_class < :timeEnd AND time_end - time_class = :type AND is_ex_class = :is_ex AND is_deleted = 0";

        return  Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':teacher_id'=>$teacherId,
                'timeStart'=>$timeStart,
                'timeEnd'=>$timeEnd,
                ':type'=>$classType,
                ':is_ex'=>$isEx
            ])
            ->queryScalar();
    }

    public function getClassProblemByTeacher($teacherId, $timeStart, $timeEnd)
    {
        $sql = "SELECT IFNULL(COUNT(id),0) FROM class_room WHERE teacher_id = :teacher_id AND is_problem = 1 AND time_class >= :timeStart AND time_class < :timeEnd AND is_deleted = 0";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':teacher_id'=>$teacherId,':timeStart'=>$timeStart,':timeEnd'=>$timeEnd])
            ->queryScalar();
    }

    public function teacherClassRecordCount($teacherId, $courseFilter, $statusFilter)
    {
        $count = ClassRoom::find()
                    ->select('id')
                    ->where('is_deleted = 0')
                    ->andWhere('teacher_id = :tid', [':tid'=>$teacherId]);

        if ($courseFilter == 1) {
            $count->andWhere('is_ex_class = 0');
        } elseif ($courseFilter == 2) {
            $count->andWhere('is_ex_class = 1');
        }

        if (empty($statusFilter)) {
            $count->andWhere('status != 3');
        } elseif ($statusFilter == 1) {
            $count->andWhere('status = 1');
        } elseif ($statusFilter == 2) {
            $count->andWhere('status = 2');
        } else {
            $count->andWhere('status = 0');
        }

        return $count->count();
    }

    public function teacherClassRecordList($teacherId, $courseFilter, $statusFilter, $pageNum)
    {
        $classRecord = ClassRoom::find()
                        ->alias('c')
                        ->select('c.id, c.time_class, c.time_end, c.student_id, u.nick as student_name, u.mobile, c.status, c.is_ex_class')
                        ->leftJoin('user as u', 'u.id = c.student_id')
                        ->where('c.is_deleted = 0')
                        ->andWhere('c.teacher_id = :teacher_id', [':teacher_id'=>$teacherId]);

        if ($courseFilter == 1) {
            $classRecord->andWhere('is_ex_class = 0');
        } elseif ($courseFilter == 2) {
            $classRecord->andWhere('is_ex_class = 1');
        }

        if (empty($statusFilter)) {
            $classRecord->andWhere('status != 3');
        } elseif ($statusFilter == 1) {
            $classRecord->andWhere('status = 1');
        } elseif ($statusFilter == 2) {
            $classRecord->andWhere('status = 2');
        } else {
            $classRecord->andWhere('status = 0');
        }

        return $classRecord->orderBy('c.time_class desc')
                    ->offset(($pageNum-1) * 8)
                    ->limit(8)
                    ->asArray()
                    ->all();
    }

    public function getClassEditHistoryCountByUid($uid)
    {
        return ClassEditHistory::find()
            ->where(['is_success' => 1, 'is_deleted' => 0, 'student_id' => $uid])
            ->count();
    }

    public function getAllInstrumentList()
    {
        return  Instrument::find()
                    ->asArray()
                    ->all();
    }


    public function getClassInfoByClassId($classId)
    {
        return ClassEditHistory::find()
                        ->select('id,student_id,instrument_id,type')
                        ->where('id = :class_id', [':class_id'=>$classId])
                        ->asArray()
                        ->one();
    }

    public function getClassRoomId($classInfo, $long)
    {
        return ClassRoom::find()
                        ->select('id')
                        ->where('student_id = :student_id', [':student_id'=>$classInfo['student_id']])
                        ->andWhere('instrument_id = :instrument_id', [':instrument_id'=>$classInfo['instrument_id']])
                        ->andWhere('(time_end - time_class) = :long', [':long'=>$long])
                        ->andWhere('is_deleted = 0')
                        ->andWhere('status = 0')
                        ->asArray()
                        ->column();
    }

    public function getClassImageInfo($imageId)
    {
        return ClassImage::find()
            ->where(['id' => $imageId])
            ->asArray()
            ->one();
    }


    public function getWeekClassByTeacherId($teacherId, $week, $timeExecute, $nextExecute)
    {
        $week = $week == 7 ? 0 : $week;

        $sql = "SELECT c.id, c.teacher_id, c.left_id, c.student_id, c.is_ex_class, c.instrument_id, c.time_class, c.time_end, c.history_id, h.type FROM class_room AS c"
            . " LEFT JOIN class_edit_history AS h ON h.id = c.history_id WHERE c.teacher_id = :teacher_id AND c.status = 0 AND c.is_deleted = 0"
            . " AND FROM_UNIXTIME(c.time_class,'%w') = :week"
            . " AND c.time_class >= :time_execute"
            . (empty($nextExecute) ? " AND c.time_class != :next_execute" : " AND c.time_class < :next_execute");

        return Yii::$app->db->createCommand($sql)
                            ->bindValues([
                                ':teacher_id' => $teacherId,
                                ':week' => $week,
                                ':time_execute' => $timeExecute,
                                ':next_execute' => $nextExecute
                            ])->queryAll();
    }

  
    public function getNextDayClassTeacher($timeStart, $timeEnd)
    {
        $sql = "SELECT c.teacher_id, u.nick, u.mobile FROM class_room AS c"
            . " LEFT JOIN user_teacher AS u ON u.id = c.teacher_id"
            . " WHERE c.is_deleted = 0 AND c.status != 2 AND c.status !=3 AND c.time_class >= :timeStart AND c.time_class < :timeEnd GROUP BY c.teacher_id";
        return Yii::$app->db->createCommand($sql)
            ->bindValues([':timeStart'=>$timeStart,':timeEnd'=>$timeEnd])
            ->queryAll();
    }

    public function getClassDayByTeacherId($teacherId, $timeStart, $timeEnd)
    {
        $sql = "SELECT c.id, us.nick as student_name, FROM_UNIXTIME(c.time_class, '%H:%i') as start, FROM_UNIXTIME(c.time_end,'%H:%i') as end, IF(c.is_ex_class=1,'体验课','购买课') as is_ex FROM class_room AS c"
            . " LEFT JOIN user AS us ON us.id = c.student_id"
            . " WHERE c.teacher_id = :teacher_id AND c.is_deleted = 0 AND c.status != 2 AND c.status != 3 AND time_class >= :timeStart AND time_class < :timeEnd"
            . " ORDER BY c.time_class ASC";

        return Yii::$app->db->createCommand($sql)
                            ->bindValues([':teacher_id'=>$teacherId,':timeStart'=>$timeStart,':timeEnd'=>$timeEnd])
                            ->queryAll();
    }

    public function getDayClassByTeacherId($teacherId, $timeDay)
    {
        $sql = "SELECT c.id, c.left_id, c.student_id, c.is_ex_class, c.instrument_id, c.time_class, c.time_end, c.history_id, h.type FROM class_room AS c"
            . " LEFT JOIN class_edit_history AS h ON h.id = c.history_id WHERE c.teacher_id = :teacher_id AND c.status = 0 AND c.is_deleted = 0 AND c.time_class >= :timeDay AND c.time_class < :timeDay+86400";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':teacher_id'=>$teacherId,':timeDay'=>$timeDay])
            ->queryAll();
    }

    public function countStudentGiftClass($param)
    {
        return ClassLeft::find()
            ->where([
                'user_id' => $param['student_id'],
                'type' => 2,
                'instrument_id' => $param['instrument'],
                'time_type' => $param['time_type']
            ])->count();
    }
/*
    private function getTeacherEx($classInfo) {
        $teacherEx = array();
        $teacherBuy = array();
        $teacher = array();

        foreach($classInfo as $class) {
            if($class['is_ex_class'] == 1) {
                $teacherEx[] = $class['teacher_id'];
            }else {
                $teacherBuy[] = $class['teacher_id'];
            }
        }

        $teacherEx = array_unique($teacherEx);
        $teacherBuy = array_unique($teacherBuy);

        if (!empty($teacherEx) && !empty($teacherBuy)) {
            foreach($teacherEx as $ex) {
                $isFind = 0;
                foreach($teacherBuy as $buy) {
                    if ($ex == $buy) {
                        $isFind = 1;
                        break;
                    }
                }
                if ($isFind == 0) {
                    $teacher[] = $ex;
                }
            }
            return $teacher;
        } else {
            return $teacherEx;
        }
    }
*/

    public function getClassHistoryPage($studentId)
    {
        return ClassEditHistory::find()
                    ->where('student_id = :student_id', [':student_id' => $studentId])
                    ->andWhere(['is_deleted' => 0, 'is_success' => 1])
                    ->count();
    }

    public function getClassHistoryList($studentId, $num)
    {
        return  ClassEditHistory::find()
                    ->select('id, student_id, amount, ex_old_amount, buy_old_amount, type, give_type, is_add,is_ex_class, comment, time_created, price')
                    ->where('student_id = :student_id', [':student_id' => $studentId])
                    ->andWhere(['is_deleted' => 0, 'is_success' => 1])
                    ->andWhere('amount > 0')
                    ->orderBy('time_created DESC')
                    ->offset(($num - 1) * 8)
                    ->limit(8)
                    ->asArray()
                    ->all();
    }

    public function getClassRecordPage($studentId)
    {
        return  ClassRoom::find()
                    ->where('student_id = :student_id AND is_deleted = :deleted', [':student_id' => $studentId,':deleted' => '0'])
                    ->andWhere('status = 1 OR status = 0 ')
                    ->count();
    }

    public function kefuGetClassRecordList($studentId, $num)
    {
          $sql = "SELECT c.id, c.name as room_name,c.time_class, c.time_end, c.is_ex_class, c.is_problem, c.problem_marks, c.time_created as created,c.time_updated as updated, u.nick as name, us.student_level, c.status, r.id as record_id, r.time_created, r.time_updated, r.time_send, r.content, r.target, r.process, r.remark, r.undo_reason, r.performance, r.note_accuracy, r.rhythm_accuracy, r.coherence, r.score, cc.status as comment_status FROM class_room AS c"
          . " LEFT JOIN class_record AS r ON c.id = r.class_id"
          . " LEFT JOIN user AS us ON us.id = c.student_id"
          . " LEFT JOIN user_teacher AS u ON u.id = c.teacher_id"
          . " LEFT JOIN class_comment AS cc ON cc.class_id = c.id"
          . " WHERE c.is_deleted = 0 AND c.student_id = :student_id AND (c.status = 0 OR c.status = 1 )"
          . " ORDER BY c.time_class DESC LIMIT :offset, :limit";

          return Yii::$app->db->createCommand($sql)
                           ->bindValues([':student_id' => $studentId, ':offset' => ($num-1)*4, ':limit' => 4])
                           ->queryAll();
    }

    public function getLastCourse($classId)
    {
        $sql = "SELECT c.course_info FROM class_room AS c"
                . " LEFT JOIN (select student_id, time_class  from class_room WHERE id = :class_id) AS s ON s.student_id = c.student_id"
                ." WHERE c.status = 1 AND  c.is_deleted = 0 AND c.time_class < s.time_class ORDER BY c.time_class DESC limit 1";

        return Yii::$app->db->createCommand($sql)
                            ->bindValue(':class_id', $classId)
                            ->queryScalar();
    }

    public function getClassImageInfoByClassid($classId)
    {
        return  ClassImage::find()
                    ->where(['class_id' => $classId])
                    ->asArray()
                    ->all();
    }

    public function getClassImageCount($classId)
    {
        return  ClassImage::find()
            ->where(['class_id' => $classId])
            ->count();
    }

    public function getClassId($classId)
    {
        $sql = "SELECT c.id FROM class_room AS c"
            . " LEFT JOIN (select student_id, time_class  from class_room WHERE id = :class_id) AS s ON s.student_id = c.student_id"
            . " WHERE c.status = 1 AND  c.is_deleted = 0 AND c.time_class < s.time_class"
            . " ORDER BY c.time_class DESC"
            . " LIMIT 1";

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':class_id', $classId)
            ->queryScalar();
    }

    public function getClassImage($classId)
    {
        return  ClassImage::find()
                    ->select('file_path')
                    ->where(['class_id' => $classId])
                    ->asArray()
                    ->all();
    }

    public function getCancelClassCount($start, $end, $teacherinfo = '', $studentinfo = '', $cancel = 4)
    {
        $teacherinfo = empty($teacherinfo) ? '':" AND (t.nick LIKE '%$teacherinfo%' OR t.mobile LIKE '%$teacherinfo%')";
        $studentinfo = empty($studentinfo) ? '':" AND (p.nick LIKE '%$studentinfo%' OR p.mobile LIKE '%$studentinfo%')";
        $canceltype =$cancel==4 ?'':" AND c.is_teacher_cancel = ".$cancel;

        

        return  ClassRecord::find()
            ->alias('re')
            ->leftJoin('class_room AS c', 'c.id = re.class_id')
            ->leftJoin('user_public AS p', 'p.user_id = c.student_id')
            ->leftJoin('user_teacher AS t', 't.id = c.teacher_id')
            ->where("re.undo_reason != ''  AND re.time_created >= :start AND re.time_created < :end". $teacherinfo. $studentinfo.$canceltype, [':start' => $start, ':end' => $end ])
            ->count();
    }

    public function getCancelClassList($start, $end, $teacherinfo = '', $studentinfo = '', $num, $cancel = 4)
    {
        $teacherinfo = empty($teacherinfo) ? '':" AND (t.nick LIKE '%$teacherinfo%' OR t.mobile LIKE '%$teacherinfo%')";
        $studentinfo = empty($studentinfo) ? '':" AND (p.nick LIKE '%$studentinfo%' OR p.mobile LIKE '%$studentinfo%')";
        $canceltype =$cancel==4 ?'':" AND c.is_teacher_cancel = ".$cancel;
        return ClassRecord::find()
            ->alias('re')
            ->select('re.id , re.undo_reason, c.is_ex_class, c.time_class, c.time_end, c.student_id, p.nick AS user_nick, p.mobile AS user_mobile, t.nick AS teacher_nick, t.mobile AS teacher_mobile, p1.open_id, c.is_teacher_cancel')
            ->leftJoin('class_room AS c', 'c.id = re.class_id')
            ->leftJoin('user_public AS p', 'p.user_id = c.student_id')
            ->leftJoin('user_public_info AS p1', 'p1.user_id = c.student_id')
            ->leftJoin('user_teacher AS t', 't.id = c.teacher_id')
            ->where("re.undo_reason != ''  AND re.time_created >= :start AND re.time_created < :end". $teacherinfo. $studentinfo.$canceltype, [':start' => $start, ':end' => $end ])
            ->offset(($num - 1) * 8)
            ->limit(8)
            ->asArray()
            ->all();
    }

    public function getHaveClassByTeacher($teacherId, $time)
    {
        $sql = "SELECT ifnull(COUNT(id),0) AS count FROM class_room"
            . " WHERE teacher_id = :teacher_id AND (status = 1 or status = 0) AND is_deleted = 0"
            . " AND (time_class = :time OR time_class = (:time + 1800))";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':teacher_id' => $teacherId, ':time' => $time])
            ->queryScalar();
    }
    
    /**
     * 获取无老师列表条数
     * @param  $day
     * @param  $name
     * 2017-2-10 sjy
     * @return array
     */
    public function getNoTeacherCount($timeStart, $timeEnd, $name, $type)
    {
        $sql="select cr.id from class_room as cr "
            ."left join user_teacher as ut on ut.id = cr.teacher_id "
            ."left join user as u on u.id = cr.student_id "
            ."left join class_edit_history as ceh on ceh.id = cr.history_id "
            ."left join class_fail_log as cfl on cfl.class_id = cr.id "
            . " WHERE time_class >= :timeStart  and time_class < :timeEnd and cr.teacher_id = 0 and u.is_disabled = 0 and status in (0,1) and cr.is_deleted = 0 and cr.is_ex_class = :is_ex_class and cr.id = cfl.class_id"
            .(empty($name)?"":" AND u.nick LIKE '%$name%'")
            ." group by cr.id ";

        $result= Yii::$app->db->createCommand($sql)
            ->bindValues([':timeStart' => $timeStart, ':timeEnd' => $timeEnd,':is_ex_class'=>$type])
            ->queryAll();
    
        
        return count($result);
    }
    
    /**
     * 获取无老师列表list
     * @param  $day
     * @param  $name
     * 2017-2-10 sjy
     * @return array
     */
    public function getNoTeacherList($page, $timeStart, $timeEnd, $name, $type)
    {
        if ($name=="undefined") {
            $name="";
        }

         $sql="select kefu.kefuname,kefu.kefu_re,cr.instrument_id,cr.id,cr.teacher_id,cr.student_id,cr.is_ex_class,cr.marks as remark,cr.time_class,cr.time_end,ut.nick as teacherName,u.nick as userName,u.mobile as userMobile,ceh.type as class_type,cfl.type from class_room as cr "
            ."left join user as u on u.id = cr.student_id "
            ."left join class_edit_history as ceh on ceh.id = cr.history_id "
            ."left join class_fail_log as cfl on cfl.class_id = cr.id "
            ."left join (select MAX(id) as fail_id, class_id from class_fail_log GROUP BY class_id) as cflm on cflm.class_id = cr.id "
            ."left join user_teacher as ut on ut.id = cfl.teacher_id "
            ."left join (select uakefu.nickname as kefuname,uakefu_re.nickname as kefu_re,upi.user_id from user_public_info as upi  
              LEFT JOIN user_account as uakefu on upi.kefu_id = uakefu.id 
              LEFT JOIN user_account as uakefu_re on upi.kefu_id_re = uakefu_re.id ) as kefu on kefu.user_id = cr.student_id "
            . " WHERE time_class >= :timeStart  and time_class < :timeEnd and cr.teacher_id = 0 and u.is_disabled = 0 and cr.status in (0,1) and cr.is_deleted = 0 and cr.is_ex_class = :is_ex_class and cfl.id = cflm.fail_id "
            .(empty($name)?"":" AND u.nick LIKE '%$name%'")
            . " ORDER BY cr.time_class asc "
            . " LIMIT ".(($page-1) * 10).",10";
        
        $result= Yii::$app->db->createCommand($sql)
            ->bindValues([':timeStart' => $timeStart, ':timeEnd' => $timeEnd,':is_ex_class'=>$type])
            ->queryAll();
       
         return $result;
    }


    public function getRecentCourseTime($studentId)
    {
        return ClassRoom::find()
                        ->select('time_class')
                        ->where('time_class  > :time AND student_id = :student_id  AND is_deleted = 0 AND status = 0', [
                            ':time' => time(),
                            ':student_id' => $studentId
                        ])
                        ->orderBy('time_class ASC')
                        ->scalar();
    }

    public function getGivClassCount($uid, $instrumentId, $timeType)
    {
        $sql = "SELECT COUNT(id) FROM class_left WHERE user_id = :uid AND type = 2 AND instrument_id = :instrument AND time_type = :type";

        Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':uid' => $uid,
                ':instrument' => $instrumentId,
                ':type' => $timeType
            ])->queryScalar();
    }

    public function getClassRoomInfoByClassId($classId)
    {
        return ClassRoom::find()
            ->select('id,time_class,time_end,student_id')
            ->where('id = :id', [':id' => $classId])
            ->limit(1)
            ->asArray()
            ->all();
    }
    public function getClassTimeBySaleIdCount($saleId, $keyword, $type, $start = 0, $end = 0, $useridHaveEx)
    {
        $list = ClassRoom::find()
                ->alias('c')
                ->select('c.id AS class_id, u.nick, c.time_end, cr.undo_reason, cr.time_created as cancel_time ')
                ->leftJoin('class_record AS cr', 'cr.class_id = c.id ')
                ->leftJoin('user AS u', 'u.id = c.student_id ')
                ->where('u.sales_id = :sale_id  AND c.is_deleted = 0 and time_class > :time_class and time_end <= :time_end'
                . (empty($keyword) ? '' : " AND (u.nick LIKE '%$keyword%' or u.mobile LIKE '%$keyword%')"), [
                    ':sale_id' => $saleId,
                    ':time_class' => $start,
                    ':time_end' => $end
                ]);
        if ($type == 1) {
            $list->andWhere(' c.status = 1 ');
        } elseif ($type == 2) {
            $list->andWhere(' c.status = 2 and c.is_ex_class = 1 ');
            $list->andWhere(empty($useridHaveEx) ? '' : "  c.student_id  not IN(" . implode(',', $useridHaveEx) . ")");
        }

        $data = $list->orderBy(' c.time_end DESC ')
                ->asArray()
                ->all();
        return count($data);
    }

    public function getClassTimeBySaleId($saleId, $keyword, $type, $num = 0, $start = 0, $end = 0, $useridHaveEx)
    {
        $list = ClassRoom::find()
                ->alias('c')
                ->select('c.id AS class_id, u.nick, c.time_end, cr.undo_reason, cr.time_created as cancel_time ')
                ->leftJoin('class_record AS cr', 'cr.class_id = c.id ')
                ->leftJoin('user AS u', 'u.id = c.student_id ')
                ->where('u.sales_id = :sale_id  AND c.is_deleted = 0 and time_class > :time_class and time_end <= :time_end'
                . (empty($keyword) ? '' : " AND (u.nick LIKE '%$keyword%'  or u.mobile LIKE '%$keyword%')"), [
                    ':sale_id' => $saleId,
                    ':time_class' => $start,
                    ':time_end' => $end
                ]);
        if ($type == 1) {
            $list->andWhere(' c.status = 1 ');
        } elseif ($type == 2) {
            $list->andWhere(' c.status = 2 and c.is_ex_class = 1 ');
            $list->andWhere(empty($useridHaveEx) ? '' : " c.student_id  not IN(" . implode(',', $useridHaveEx) . ")");
        }
        $data = $list->orderBy(' c.time_end DESC ')
                ->offset(($num - 1) * 10)
                ->limit(10)
                ->asArray()
                ->all();
        return $data;
    }

    public function getWechatClassName($bindOpenid)
    {
        return UserShare::find()
                    ->alias('u')
                    ->select('c.title')
                    ->leftJoin('wechat_class AS w', 'u.class_id = w.id')
                    ->where('u.open_id = :bind_openid AND status = 1', [':bind_openid' => $bindOpenid])
                    ->scalar();
    }


    public function getStudentTeacherFixIsExit($studentId, $week, $classBit)
    {
        $sql = "SELECT teacher_id FROM `student_fix_time` WHERE `week` = :week AND (time_bit & ".$classBit .") > 0";

        return Yii::$app->db->createCommand($sql)
                        ->bindValues([
                            ':week' => $week
                        ])->queryColumn();
    }

    public function getBuyRemainAmount($studentId)
    {
        $sql = "SELECT id, amount, time_type, instrument_id FROM class_left"
            . " WHERE (type = 2 OR type = 3) AND (left_bit&4) != 4 AND user_id = :student_id AND amount > 0 ORDER BY time_type, instrument_id";

        return Yii::$app->db->createCommand($sql)
                        ->bindValues([
                            ':student_id' => $studentId
                        ])->queryAll();
    }

    public function getRemainAmountByLeftId($leftId)
    {
        return ClassLeft::find()
                        ->select('amount')
                        ->where(['id' => $leftId])
                        ->scalar();
    }

    public function getFirstPayClass($studentId)
    {
        return ClassRoom::find()
            ->alias('c')
            ->select('MIN(time_class)')
            ->leftJoin('(SELECT uid, IFNULL(MAX(time_pay), 0) AS pay_time FROM product_order WHERE pay_status = 1 GROUP BY uid) AS p ',
                'p.uid = c.student_id')
            ->where('time_class > pay_time AND student_id = :student_id', [':student_id' => $studentId])
            ->scalar();
    }
    
    /*
     * 获取学生微课的信息by classid
     * create by sjy
     */
    public function getStudentWechatClass($classId)
    {
         $data = StudentWechatClass::find()
                ->select('title,class_time,url,is_back,is_free,id,poster_path')
                 ->where('id = :id ', [
                     ':id' => $classId
                 ])
                 ->asArray()
                 ->one();
         return $data;
    }
    
    /*
     * 获取分享记录信息
     * create by sjy
     */
    public function getShareRecord($classid, $isFree, $openid, $isBack)
    {
         //查询预约记录信息
         $ishave = StudentUserShare::find()
                 ->select('ui.head,ui.name,sus.share_time,sus.id')
                 ->alias('sus')
                 ->leftJoin('user_init as ui', 'ui.openid = sus.open_id')
                 ->where('sus.class_id = :class_id and sus.is_back_share = :is_back_share and sus.is_free = :is_free and sus.open_id = :open_id', [
                     ':class_id' =>$classid,
                     ':is_back_share' => $isBack,
                     ':is_free' => $isFree,
                     ':open_id' => $openid
                 ])
                 ->asArray()
                 ->one();
         return $ishave;
    }

    public function getClassRecordList($timeStart, $timeEnd, $filter)
    {
        $sql = "SELECT c.id, c.teacher_id, c.time_class, c.time_end, r.teacher_grade FROM class_room as c"
            . " LEFT JOIN class_record as r ON c.id = r.class_id"
            . " LEFT JOIN user_teacher AS t ON t.id = c.teacher_id"
            . " WHERE c.status = 1 AND c.time_class >= :timeStart AND c.time_class < :timeEnd"
            . " AND t.type = 1 AND t.teacher_type = 1 AND t.work_id != 4";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':timeStart' => $timeStart,
                ':timeEnd' => $timeEnd
            ])->queryAll();
    }

    public function getLeftClassType($studentId)
    {
        $sql = "SELECT 	time_type AS 'key',"
            . " CASE WHEN time_type = 1 THEN '25分钟'"
            . " WHEN time_type = 2 THEN '45分钟' ELSE '50分钟' END AS type"
            . " FROM class_left WHERE (type = 2 OR type = 3) AND (left_bit&4) != 4 AND user_id = :student_id GROUP BY time_type ORDER BY time_type";

        return Yii::$app->db->createCommand($sql)
                        ->bindValues([':student_id' => $studentId])
                        ->queryAll();
    }

    public function getLeftInstrument($studentUid)
    {
        $sql = "SELECT instrument_id as 'key', i.`name` as type"
            . " FROM class_left AS l LEFT JOIN instrument AS i ON i.id = l.instrument_id"
            . " WHERE (type = 2 OR type = 3) AND (left_bit&4) != 4 AND user_id = :student_id GROUP BY l.instrument_id";

        return Yii::$app->db->createCommand($sql)
                        ->bindValues([
                            ':student_id' => $studentUid,
                        ])->queryAll();
    }

    public function getClassFailByStudentFix($teacherId, $studentId, $timeStart, $timeEnd, $classId)
    {
        $sql = "SELECT c.id, c.left_id, c.history_id, c.student_id, c.teacher_id, c.instrument_id, h.type, c.time_class, c.time_end FROM class_room AS c"
            . " LEFT JOIN class_edit_history AS h ON h.id = c.history_id"
            . " WHERE ((c.teacher_id = :teacher_id AND c.student_id != :student_id) OR (c.teacher_id != :teacher_id AND c.student_id = :student_id) OR (c.teacher_id = :teacher_id AND c.student_id = :student_id))"
            . " AND c.time_class >= :timeStart AND c.time_class < :timeEnd AND c.status = 0 AND c.is_deleted = 0 and c.id != :id ";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':teacher_id' => $teacherId,
                ':student_id' => $studentId,
                ':timeStart' => $timeStart,
                ':timeEnd' => $timeEnd,
                ':id'=> $classId
            ])
            ->queryAll();
    }

    public function getClassWithinIntervalById($teacherId, $timeStart, $timeEnd)
    {
        //
        $sql = " SELECT id AS class_id ,time_class, time_end"
            . " FROM class_room"
            . " WHERE status = 1 AND is_deleted = 0 AND time_class >= :time_start AND time_class < :time_end AND teacher_id = :teacher_id ";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':time_start' => $timeStart,
                ':time_end' => $timeEnd,
                ':teacher_id' => $teacherId
            ])
            ->queryAll();
    }

    //根据乐器的id去查询名称
    public function getInstrumentById($id)
    {
        return  Instrument::find()->where('id='.$id)->asArray()->one();
    }
    
    public function getUserHaveEx($uid)
    {
         $list = ClassRoom::find()
                ->select('student_id')
                ->where('status in (0,1) AND is_deleted = 0 and is_ex_class = 1' . (empty($uid) ? '' : " AND student_id  IN(" . implode(',', $uid) . ")"))
                ->groupBy('student_id')
                ->asArray()
                ->column();
        return $list;
    }
    
    /*
     * 根据salesid和关键字查询注册的用户
     * create by sjy
     */
    public function getUserBySalesid($saleId, $keyword, $start, $end)
    {
        $data = UserInit::find()
                ->alias('ui')
                ->select('ui.id as ui_id, ui.name, u.nick, u.mobile,u.id as uid, ui.subscribe_time')
                ->leftJoin('wechat_acc as wa', 'wa.openid = ui.openid')
                ->leftJoin('user as u', 'u.id = wa.uid')
                ->where('u.sales_id = :sales_id and ui.subscribe_time > :start and ui.subscribe_time <= :end'
                        . (empty($keyword) ? '' : " AND (ui.name LIKE '%$keyword%' or u.nick LIKE '%$keyword%' or u.mobile LIKE '%$keyword%')"), [
                            ':sales_id' => $saleId,
                            ':start' => $start,
                            ':end' => $end
                        ])
                ->orderBy('ui.subscribe_time desc')
                ->asArray()
                ->all();

        return $data;
    }
    
    public function getUserInitBySalesid($saleId, $keyword, $start, $end)
    {
        $data = UserInit::find()
                ->alias('ui')
                ->select('ui.id as ui_id, ui.name, u.nick, u.mobile,u.id as uid, ui.subscribe_time')
                ->leftJoin('wechat_acc as wa', 'wa.openid = ui.openid')
                ->leftJoin('user as u', 'u.id = wa.uid')
                ->where('ui.sales_id = :sales_id and u.id is null and ui.is_deleted = 0 and ui.subscribe_time > :start and ui.subscribe_time <= :end '
                        . (empty($keyword) ? '' : " AND (ui.name LIKE '%$keyword%' or u.nick LIKE '%$keyword%' or u.mobile LIKE '%$keyword%')"), [
                            ':sales_id' => $saleId,
                            ':start' => $start,
                            ':end' => $end
                        ])
                ->orderBy('ui.subscribe_time desc')
                ->asArray()
                ->all();

        return $data;
    }

    public function getNotExclassInUser($salesid)
    {
        return User::find()
            ->alias('u')
            ->select('ui.id')
            ->leftJoin('user_init AS ui', 'u.init_id = ui.id')
            ->where('NOT EXISTS(SELECT id FROM class_room WHERE student_id = u.id AND is_ex_class = 1)')
            ->andWhere('u.sales_id = :sales_id AND ui.id IS NOT NULL', [
                ':sales_id' => $salesid
            ])
            ->groupBy('ui.id')
            ->column();
    }

    public function getNotInUserButInUserInit($saleId)
    {
        return UserInit::find()
            ->alias('ui')
            ->select('ui.id')
            ->where('ui.sales_id = :sales_id AND ui.is_bind=0 AND ui.is_deleted = 0', [
                ':sales_id' => $saleId,
            ])
            ->column();
    }

    public function getStudentNotExperienceCount($uiids, $keyword, $start, $end)
    {
        return UserInit::find()
            ->alias('ui')
            ->leftJoin('user as u', 'ui.id = u.init_id')
            ->where(['in', 'ui.id', $uiids])
            ->andWhere('ui.subscribe_time > :start AND ui.subscribe_time <= :end', [
                ':start' => $start,
                ':end' => $end
            ])
            ->andFilterWhere(['or', ['LIKE', 'ui.name', $keyword], ['LIKE', 'u.nick', $keyword], ['LIKE', 'u.mobile', $keyword]])
            ->count();
    }

    public function getStudentNotExperienceList($uiids, $page, $size, $keyword, $start, $end)
    {
        return UserInit::find()
            ->alias('ui')
            ->select('ui.name, u.nick, u.mobile, ui.subscribe_time')
            ->leftJoin('user as u', 'ui.id = u.init_id')
            ->where(['in', 'ui.id', $uiids])
            ->andWhere('ui.subscribe_time > :start AND ui.subscribe_time <= :end', [
                ':start' => $start,
                ':end' => $end
            ])
            ->andFilterWhere(['or', ['LIKE', 'ui.name', $keyword], ['LIKE', 'u.nick', $keyword], ['LIKE', 'u.mobile', $keyword]])
            ->orderBy('ui.id DESC')
            ->offset(($page -1) * $size)
            ->limit($size)
            ->asArray()
            ->all();
    }

    
    public function getUserHaveExByClass($saleId)
    {
        $list = ClassRoom::find()
                ->alias('c')
                ->select('c.student_id')
                ->leftJoin('user AS u', 'u.id = c.student_id ')
                ->where('u.sales_id = :sale_id  AND c.is_deleted = 0 and c.is_ex_class = 1 and c.status in (0,1)', [
                    ':sale_id' => $saleId
                ])
                ->groupBy('student_id')
                ->asArray()
                ->column();
        return $list;
    }
}
