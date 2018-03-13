<?php

/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/19
 * Time: 下午2:38
 */

namespace common\logics\classes;

use common\widgets\RecordComment;
use common\widgets\TimeFormatHelper;
use Yii;
use yii\base\Object;
use yii\db\Exception;
use yii\db\Query;
use common\services\LogService;
use common\widgets\Request;
use common\widgets\NeteaseAPI;
use crm\models\student\ClassLeft;

class ClassesLogic extends Object implements IClasses
{
    /** @var  \common\sources\read\classes\ClassAccess  $RClassAccess */
    private $RClassAccess;

    /** @var  \common\sources\write\classes\ClassAccess  $WClassAccess */
    private $WClassAccess;

    /** @var  \common\sources\read\student\StudentAccess  $RStudentAccess */
    private $RStudentAccess;

    /** @var  \common\sources\read\teacher\TeacherAccess  $RTeacherAccess */
    private $RTeacherAccess;

    /** @var  \common\logics\teacher\WorktimeLogic  $workhourService */
    private $workhourService;

    /** @var  \common\sources\read\chat\ChatAccess  $RChatAccess */
    private $RChatAccess;

    /**
     * 初始化
     * @author 王可
     */
    public function init()
    {
        $this->RClassAccess = Yii::$container->get('RClassAccess');
        $this->RTeacherAccess = Yii::$container->get('RTeacherAccess');
        $this->WClassAccess = Yii::$container->get('WClassAccess');
        $this->RStudentAccess = Yii::$container->get('RStudentAccess');
        $this->workhourService = Yii::$container->get('workhourService');
        $this->RChatAccess = Yii::$container->get('RChatAccess');

        parent::init();
    }

    
    /**
     * 获得课程信息的进入界面
     * @author 王可
     */
    public function getCourseIndex()
    {

        $query = $this->RTeacherAccess->getCourseIndex();

        array_unshift($query, ['id' => 0, 'nick' => '全部']);
        return $query;
    }

    /*
     * 获得课程的列表信息的条数
     * @author 王可
     */

    public function getClassesListCount($day = 0, $type = 0, $teacher = 0)
    {
        $timeStart = strtotime($day);
        $timeEnd = $timeStart + 86400;

        $totalCount = $this->RClassAccess->getClassesListCount($day, $type, $teacher, $timeStart, $timeEnd);

        return $totalCount;
    }

    public function getClassesListInfo($page = 1, $day = 0, $type = 0, $teacher = 0)
    {

        $time = $day;
        $firstday = strtotime($time);
        $lastday = $firstday + 24 * 60 * 60;
        $lastweek = strtotime("$time Sunday") + 86400;
        $firstweek = $lastweek - 7 * 24 * 60 * 60;
        $timeStart = strtotime($day);
        $timeEnd = $timeStart + 86400;

        $courseData = $this->RClassAccess->getCourseData($timeStart, $timeEnd, $teacher, $page, $type);
        $countday = $this->RClassAccess->getCountDay($firstday, $lastday);
        $countweek = $this->RClassAccess->getCountWeek($lastweek, $firstweek);


        foreach ($courseData as &$row) {
            $row['course_info'] = unserialize($row['course_info']);
            $row['have_image'] = !empty($row['course_info']) || !empty($row['class_id']) ? 1 : 0;
            $row['isRed'] = ($row['status_bit'] & 8) == 8 ? 1 : 0;
            $row['is_contact'] = ($row['status_bit'] & 64) == 64 ? 1 : 0;

            $time_class = $row['time_class'];
            $formal_min_time_class = $row['formal_min_time_class'];
            $row['is_first_pay_class'] = $formal_min_time_class == $time_class ? 1 : 0;
        }

        return ['courseData' => $courseData,
            'countday' => $countday,
            'countweek' => $countweek,];
    }

    public function searchTeacherName($name)
    {

        $query = $this->RTeacherAccess->searchTeacherName($name);

        if (empty($name)) {
            array_unshift($query, ['id' => 0, 'nick' => '全部']);
        }

        return $query;
    }

    public function queryTeacherTodayclass($day = 0)
    {
        $startTime = strtotime($day);
        $endTime = $startTime + 86400;

        $query = $this->RTeacherAccess->queryTeacherTodayclass($startTime, $endTime);

        return $query;
    }

    public function queryViewclass($classid = 0)
    {
        $data = $this->RClassAccess->queryViewclassData($classid);

        $img_id = $data->id;

        $arr['images'] = $this->RClassAccess->queryViewclassImages($img_id);
        $arr['data'] = $data->toArray();
        return $arr;
    }

    public function delFailClass($req, $classid, $logid)
    {
        if ($req->isPost) {
            $this->WClassAccess->UpdateFailClass($classid);
            LogService::OutputLog($logid, 'update', '', '单个删除错误课');
            return 1;
        }
        return 0;
    }

    public function delFailClassALL($classIds, $logid)
    {
        $classIds_str = implode(',', $classIds);

        $this->WClassAccess->delFailClassALL($classIds_str);
        LogService::OutputLog($logid, 'delete', serialize($classIds), '批量删除当页错误课表');
    }

    public function queryNeedChangeFailclass($id = 0, $time)
    {
        $data = $this->RClassAccess->queryClassRoomById($id);
        $tag = ($data['status_bit'] & 8) == 8 ? 1 : 0;
        $time = date('Y-m-d', $time) . 'T' . date('H:i', $time);

        $userid = $data['student_id'];
        $leftList = $this->RClassAccess->queryClassLeftinfo($userid);

        $arr['data'] = $data;
        $arr['tag'] = $tag;
        $arr['time'] = $time;
        $arr['leftList'] = $leftList;

        return $arr;
    }

    public function queryExclassList()
    {
        $timeStart = strtotime(date('Y-m-d', time()));
        $timeEnd = $timeStart + 86400;

        $courseData = $this->RClassAccess->queryExclassList($timeStart, $timeEnd);
        foreach ($courseData as &$course) {
            $course['undetermined'] = ($course['status_bit'] & 128) == 128 ? 1 : 0;
        }


        return $courseData;
    }

    public function getCourseImage($courseId)
    {
        $url = "http://api.pnlyy.com/book/courses-detail?id=$courseId";
        $data = json_decode($this->httpGet($url), true);
        return $data;
    }

    function httpGet($url, $headers = [])
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, @$_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_HTTPGET, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            return false;
        }
        curl_close($curl);
        return $tmpInfo; // 返回数据
    }

    public function monitorClassCount($type = 1, $date = "", $keyword = "")
    {
        //今天的课程
        $current = strtotime(date("Y-m-d H:i", time()));
        $currentDate = strtotime(date("Y-m-d", time()));
        $timeStart = strtotime($date);
        $timeEnd = $timeStart + 86400;

        $totalCount = $this->RClassAccess->monitorClassCount($currentDate, $timeStart, $timeEnd, $current, $keyword, $type);

        return $totalCount;
    }

    public function monitorClassList($page = 1, $type = 1, $date = "", $keyword = "")
    {
        $current = strtotime(date("Y-m-d H:i", time()));
        $currentDate = strtotime(date("Y-m-d", time()));
        $timeStart = strtotime($date);
        $timeEnd = $timeStart + 86400;


        $data = $this->RClassAccess->monitorClassList($page, $currentDate, $timeStart, $timeEnd, $current, $keyword, $type);



        foreach ($data as &$item) {
            $stu = $this->RClassAccess->getClassNetBeanByStudentid($item);
            $tec = $this->RClassAccess->getClassNetBeanByTeacherid($item);

            $item["student_net"] = $stu["net_desc"];
            $item["teacher_net"] = $tec["net_desc"];

            $stuStatus = $this->RClassAccess->getClassStudentStatus($item);
            $tecStatus = $this->RClassAccess->getClassTecStatus($item);

            $item["student_status"] = $stuStatus["name"];
            $item["teacher_status"] = $tecStatus["name"];

            $item["student_class"] = "";
            if ($stuStatus["id"] == 1) {
                $item["student_class"] = "yellow";
            } else if ($stuStatus["id"] == 3) {
                $item["student_class"] = "green";
            } else {
                $item["student_class"] = "red";
            }

            $item["teacher_class"] = "";
            if ($tecStatus["id"] == 1) {
                $item["teacher_class"] = "yellow";
            } else if ($tecStatus["id"] == 3) {
                $item["teacher_class"] = "green";
            } else {
                $item["teacher_class"] = "red";
            }

            $classType = $item["time_end"] - $item["time_class"];
            if ($classType == 1500) {
                $item["classType"] = "[25min]";
            } else if ($classType == 2700) {
                $item["classType"] = "[45min]";
            } else {
                $item["classType"] = "[50min]";
            }

            if ($stuStatus["id"] == 3 && $tecStatus["id"] == 3) {
                $item["bg"] = "greenbg";
            } elseif ($stuStatus["id"] == 1 || $tecStatus["id"] == 1) {
                $item["bg"] = "yellowbg";
            } else {
                $item["bg"] = "";
            }
        }

        return $data;
    }

    public function editClassContact($classId, $type)
    {
        $class = $this->RClassAccess->queryClassContactByClassid($classId);

        $status_bit = $class->status_bit;

        if (empty($type)) {
            $status_bit = $status_bit | 64;
        } else {
            $status_bit = $status_bit & 191;
        }

        $time_updated = time();

        $u_flag = $this->WClassAccess->updateClassRoomContact($class, $status_bit, $time_updated);

        if ($u_flag) {
            return 1;
        } else {
            return 0;
        }
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
                $data = $this->RStudentAccess->getUserById($id);
                if ($data && !empty($data->chat_token)) {
                    return 1;
                }
            } else { //老师
                $data = $this->RTeacherAccess->geTeacherById($id);
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
                $data = $this->RStudentAccess->getUserById($id);
                if ($data) {
                    $chat_token = $token;
                    $accessToken = $phone . $token;

                    $this->WClassAccess->updateClassSyn($data, $chat_token, $accessToken);

                    return 1;
                }
            } else { //老师
                $data = $this->RTeacherAccess->geTeacherById($id);
                if ($data) {
                    $chat_token = $token;
                    $accessToken = $phone . $token;

                    $this->WClassAccess->updateClassSyn($data, $chat_token, $accessToken);

                    return 1;
                }
            }
        }

        return "Sys Error";
    }

    public function sendMessageWechat($classid, $openid)
    {

        $wechat = Yii::$app->wechat;

        $classInfo = $this->RClassAccess->getSendClassInfo($classid);

        if (empty($classInfo['is_ex_class'])) {
            $classLeftModel = new ClassLeft();
            $leftInfo = $classLeftModel->countLeftAmount($classInfo['student_id']);
        } else {
            $leftInfo = $this->RClassAccess->getSendClassLeftInfo($classInfo['student_id']);
        }

        $recordInfo = $this->RClassAccess->queryRecordinfo($classid);

        $class_time = date('m-d H:i', $classInfo['time_class'])
                . '-'
                . date('H:i', $classInfo['time_end']);

        $arr = [
            'touser' => $openid,
            'template_id' => Yii::$app->params['student_template_class_alarm'],
            'url' => Yii::$app->params['class_url'] . $classid,
            'data' => [
                'first' => [
                    'value' => '您本次课后记录',
                    'color' => '#000000'
                ],
                'keyword1' => [
                    'value' => 'VIP钢琴课程',
                    'color' => '#000000'
                ],
                'keyword2' => [
                    'value' => $class_time, //时间
                    'color' => '#000000'
                ],
                'remark' => [
                    'value' => '',
                    'color' => '#173177'
                ],
            ],
        ];

        $result = $wechat->sendTemplateMessage($arr);

        $time_send = time();
        $class_left = $leftInfo['amount'];
        $class_used = $leftInfo['total'] - $leftInfo['amount'];

        $record_save_flag = $this->WClassAccess->saveClassRecord($recordInfo, $time_send, $class_left, $class_used);

        if ($result == true && $record_save_flag) {
            return 'y';
        } else {
            return 'n';
        }
    }

    public function getClassRecordInfo($classId)
    {
        $tagList = $this->RClassAccess->getTeacherTagList();

        $process = $this->RClassAccess->getRecordInfoByClassInfo($classId);
        $recordId = $process['id'];

        list(
                $process['performance'],
                $process['note_accuracy'],
                $process['rhythm_accuracy'],
                $process['coherence']
                ) = RecordComment::getRecordStudentDes($process);

        $audio = $this->RClassAccess->getRecordAudioList($recordId);

        $image = $this->RClassAccess->getRecordImageList($recordId);

        $process['self_audio'] = empty($process['self_audio']) ? '' : Yii::$app->params['vip_video_path'] . $process['self_audio'];

        switch ($process['teacher_grade']) {
            case 1:
                //$process['grade_img'] = '/images/grade_active_1.png';
                $process['grade_dec'] = '非常满意, 无可挑剔';
                break;
            case 2:
                //$process['grade_img'] = '/images/grade_active_2.png';
                $process['grade_dec'] = '较满意, 但仍可改善';
                break;
            case 3:
                //$process['grade_img'] = '/images/grade_active_3.png';
                $process['grade_dec'] = '一般, 需要改善';
                break;
            default:
                //$process['grade_img'] = '';
                $process['grade_dec'] = '';
                break;
        }

        $each = [];
        $selectList = [];

        for ($i = 0; $i < count($tagList); $i ++) {
            $match = pow(2, $i);
            if (($process['tag_bit'] & $match) == $match) {
                $each['name'] = $tagList[$i]['name'];
                $each['is_good'] = $tagList[$i]['is_good'];
                $each['image'] = $tagList[$i]['image'];
                $selectList[] = $each;
                $each = [];
            }
        }

        if (!empty($audio)) {
            foreach ($audio as &$row) {
                $row['file_path'] = Yii::$app->params['vip_video_path']
                        . $row['file_path'];
            }
        }

        $imageList = [];

        if (!empty($image)) {
            $name = '';
            $eachCourse = [];

            foreach ($image as &$row) {
                $row['file_path'] = Yii::$app->params['vip_static_path']
                        . $row['file_path'];

                if ($row['name'] == $name) {
                    $eachCourse['path'][] = $row['file_path'];
                } else {
                    $imageList[] = $eachCourse;
                    $eachCourse = [];
                    $eachCourse['name'] = $row['name'];
                    $eachCourse['path'][] = $row['file_path'];
                }

                $name = $row['name'];
            }

            $imageList[] = $eachCourse;
            array_shift($imageList);
        }

        return [
            'content' => $process,
            'tag_list' => $selectList,
            'audio' => $audio,
            'image' => $imageList,
        ];
    }

    // 复购视角 之正在上课名单
    public function getClassCheckPage($keyword)
    {
        $count = $this->RClassAccess->getClassCheckPage($keyword);
        return $count;
    }

    public function getClassCheckList($keyword = '', $num = 1)
    {
        $list = $this->RClassAccess->getClassCheckList($keyword, $num);
        foreach ($list as &$v) {
            //色彩调试
            if (empty($v['is_ex_class'])) {
                $v['class'] = '购买课';
                $v['color'] = 'green';
                $v['border'] = 'green_border';
            } else {
                $v['class'] = '体验课';
                $v['color'] = 'yellow';
                $v['border'] = 'yellow_border';
            }

            // 课程时间
            $time = $v['end'] - $v['start'];
            switch ($time) {
                case '1200':
                    $v['time'] = '[25分钟]';
                    break;
                case '2700':
                    $v['time'] = '[45分钟]';
                    break;
                case '3000':
                    $v['time'] = '[50分钟]';
                    break;
                default:
                    $v['time'] = '[不晓得]';
                    break;
            }

            $v['time_course'] = date('H:i', $v['start']) . "-" . date('H:i', $v['end']);

            if ($v['course_info'] == 'a:0:{}') {
                $v['course_info'] = 0;
            }
            $v['have_img'] = !empty($v['course_info']) || !empty($v['if_class_id']) ? 1 : 0;



            if ($v['t_net'] == '很差') {
                $v['t_color'] = "style='color:red'";
            } else {
                $v['t_color'] = " ";
            }

            if ($v['s_net'] == '很差') {
                $v['s_color'] = "style='color:red'";
            } else {
                $v['s_color'] = " ";
            }


            $v['s_net'] = !empty($v['s_net']) ? $v['s_net'] : '无';
            $v['t_net'] = !empty($v['t_net']) ? $v['t_net'] : '无';

            //状态
            $s_status = $this->RClassAccess->getClassQuitDicUserName($v['student_id'], $v['id']);
            if (!empty($s_status['s_status'])) {
                if ($s_status['s_status'] == '进入准备教室') {
                    $v['s_status_color'] = "style=color:#FF8C01";
                } elseif ($s_status['s_status'] == '进入上课教室') {
                    $v['s_status_color'] = "style=color:#5CB85C";
                } else {
                    $v['s_status_color'] = "style='color:red'";
                }
                $v['s_status'] = $s_status['s_status'];
            } else {
                $v['s_status_color'] = "style='color:red'";
                $v['s_status'] = " 未进入教室";
            }

            $t_status = $this->RClassAccess->getClassQuitDicTeacherName($v['teacher_id'], $v['id']);
            if (!empty($t_status['t_status'])) {
                if ($t_status['t_status'] == '进入准备教室') {
                    $v['t_status_color'] = "style=color:#FF8C01";
                } elseif ($t_status['t_status'] == '进入上课教室') {
                    $v['t_status_color'] = "style=color:#5CB85C";
                } else {
                    $v['t_status_color'] = "style='color:red'";
                }
                $v['t_status'] = $t_status['t_status'];
            } else {
                $v['t_status_color'] = "style='color:red'";
                $v['t_status'] = "未进入教室";
            }


            //是否投诉
            $v['complaint'] = $this->RClassAccess->getComplainContent($v['open_id']);
            $v['complaint'] = empty($v['complaint']) ? '' : "<span style='color:red' class='fa fa-exclamation-circle'> 投诉</span>";
        }
        return $list;
    }

    public function countPurchaseCourse($day)
    {
        $timeStart_1 = date('Y-m-d', time());

        if ($day == 0) {//昨天
            $timeStart = strtotime("$timeStart_1 -1 day ");
            $timeEnd = strtotime($timeStart_1);
        } elseif ($day == 1) {//今天
            $timeStart = strtotime($timeStart_1);
            $timeEnd = strtotime("$timeStart_1 +1 day ");
        } elseif ($day == 2) {//明天
            $timeStart = strtotime("$timeStart_1 +1 day ");
            $timeEnd = strtotime("$timeStart_1 +2 day ");
        } else {//某一天
            $timeStart_1 = date('Y-m-d', strtotime($day));

            $timeStart = strtotime($timeStart_1);
            $timeEnd = strtotime("$timeStart_1 +1 day ");
        }

        return $this->RClassAccess->countPurchaseCourse($timeStart, $timeEnd);
    }

    public function getPurchaseCourseList($day, $num)
    {
        $timeStart_1 = date('Y-m-d', time());

        if ($day == 0) {//昨天
            $timeStart = strtotime("$timeStart_1 -1 day ");
            $timeEnd = strtotime($timeStart_1);
        } elseif ($day == 1) {//今天
            $timeStart = strtotime($timeStart_1);
            $timeEnd = strtotime("$timeStart_1 +1 day ");
        } elseif ($day == 2) {//明天
            $timeStart = strtotime("$timeStart_1 +1 day ");
            $timeEnd = strtotime("$timeStart_1 +2 day ");
        } else {
            $timeStart_1 = date('Y-m-d', strtotime($day));

            $timeStart = strtotime($timeStart_1);
            $timeEnd = strtotime("$timeStart_1 +1 day ");
        }

        $list = $this->RClassAccess->getPurchaseCourseList($timeStart, $timeEnd, $num);

        $week = ['日', '一', '二', '三', '四', '五', '六'];
        if (!empty($list)) {
            foreach ($list as &$row) {
                $time_class = $row['time_class'];
                $row['time_class'] = empty($time_class) ? '无' : "周" . $week[date('w', $time_class)] . " " . date('Y-m-d H:i', $time_class);

                $row['have_image'] = $row['course_info'] != 'a:0:{}' || !empty($row['ci_class_id']) ? 1 : 0;
                if (empty($time_class)) {
                    $row['have_image'] = 2;
                }
                $row['complain_time'] = empty($row['complain_time']) ? 0 : $row['complain_time'];
                $row['class_finish_time'] = empty($row['class_finish_time']) ? 0 : $row['class_finish_time'];

                $formal_min_time_class = $row['formal_min_time_class'];
                $row['is_first_pay_class'] = $formal_min_time_class == $time_class ? 1 : 0;
            }
        }
        return empty($list) ? [] : $list;
    }

    public function getTeacherByName($keyword = '')
    {
        return $this->RTeacherAccess->getTeacherByConditionName($keyword);
    }

    public function unfinishedClass($studentId)
    {
        $list = $this->RClassAccess->unfinishedClass($studentId);

        if (!empty($list)) {
            foreach ($list as &$row) {
                $row['time'] = TimeFormatHelper::timeFormat($row['time_class'], $row['time_end']);
            }
        }

        return $list;
    }

    public function getCalendar($request)
    {
        $dateMonth = !isset($request['time_start']) ? date('Y-m-01', time()) : date($request['time_start'] . '-01');

        $timeStart = strtotime($dateMonth);
        $timeEnd = strtotime("$dateMonth + 1 month -1 day");
        $week = date('w', $timeStart);
        $week = $week == 0 ? 7 : $week;

        $list = $this->RClassAccess->getClassList($request['student_id'], $timeStart, $timeEnd + 86400);
        $failList = $this->RClassAccess->getClassFailList($request['student_id'], $timeStart, $timeEnd + 86400);

        $time = ['start' => $timeStart, 'end' => $timeEnd, 'weekStart' => $week];

        $dayList = array();
        $dayFail = array();
        $dayRed = array();

        foreach ($list as $class) {
            if ($class['is_deleted'] == 1 && ($class['status_bit'] & 8) == 8) {
                $dayRed[] = '';
            }

            if ($class['status'] == 0 || $class['status'] == 1) {
                $dayList[] = date('d', $class['time_class']);
            }

            if (($class['status_bit'] & 8) == 8) {
                $dayRed[] = date('d', $class['time_class']);
            }
        }

        foreach ($failList as $fail) {
            $dayFail[] = date('d', $fail);
        }

        return [$time, $dayList, $dayFail, $dayRed];
    }

    public function addClassPage($request)
    {
        $request['time'] = strtotime($request['date']);
        $request['class_id'] = isset($request['class_id']) ? $request['class_id'] : 0;

        $class = $this->RClassAccess->getClassLeftInfo($request['student_id']);

        foreach ($class as &$row) {
            if ($row['type'] == 2 || $row['type'] == 1) {
                $row['name'] = $row['name'] . $this->getClassLengthByClassType($row['time_type']);
            }
            $row['name'] = $row['name'] . "/" . $row['instrument_name'];
        }
        return [$class, $request];
    }

    public function getClassLengthByClassType($type)
    {
        switch ($type) {
            case 1:
                return '25分钟';
            case 2:
                return '45分钟';
            case 3:
                return '50分钟';
        }
    }

    public function getClassEditInfo($studentId)
    {
        $remark = $this->RStudentAccess->getStudentRemark($studentId);
        $fixTime = $this->RStudentAccess->getStudentFixTimeById($studentId);

        $remark['remark'] = explode("\n", $remark['remark']);

        if (!empty($fixTime)) {
            foreach ($fixTime as &$time) {
                $stamp = 0;

                if ($time['class_type'] == 1) {
                    $stamp = 1500;
                } elseif ($time['class_type'] == 2) {
                    $stamp = 2700;
                } elseif ($time['class_type'] == 3) {
                    $stamp = 3000;
                }

                $tmp = '2014-1-1 ' . $time['time'];
                $time['end_time'] = date('H:i', strtotime($tmp) + $stamp);
                $time['week'] = $this->timeClassFormat($time['week']);
            }
        }

        $data['remark'] = $remark;
        $data['fixTime'] = $fixTime;
        return $data;
    }

    public function timeClassFormat($week)
    {
        switch ($week) {
            case 7:
                $week = '周日';
                break;
            case 1:
                $week = '周一';
                break;
            case 2:
                $week = '周二';
                break;
            case 3:
                $week = '周三';
                break;
            case 4:
                $week = '周四';
                break;
            case 5:
                $week = '周五';
                break;
            case 6:
                $week = '周六';
                break;
        }
        return $week;
    }

    public function getCancelClassPage($classId)
    {
        $student = $this->RClassAccess->getSendClassInfo($classId);

        $timeLong = time() + 1800 > $student['time_class'] ? 1 : 0;

        $current_time = time() - 300 > $student['time_class'] ? 1 : 0;

        $timeStart = strtotime(date('Y-m-01', $student['time_class']));
        $timeEnd = strtotime(date('Y-m-01', $student['time_class']) . ' + 1 month');

        $count = $this->RClassAccess->getClassRoomByMounth($student['student_id'], $timeStart, $timeEnd);

        $data = array(
            'class_id' => $classId,
            'count' => $count,
            'time_long' => $timeLong,
            'current_time' => $current_time
        );
        return ['error' => 0, 'data' => $data];
    }

    public function timetableEditClass($classId)
    {

        $request = $this->RClassAccess->getClassInfoByIds($classId);

        $request['is_red'] = ($request['status_bit'] & 8) == 8 ? 1 : 0;

        $leftList = $this->RClassAccess->getClassLeftInfo($request['student_id']);
        foreach ($leftList as &$row) {
            if ($row['type'] == 2 || $row['type'] == 1) {
                $row['name'] = $row['name'] . $this->getClassLengthByClassType($row['time_type']);
            }
            $row['name'] = $row['name'] . "/" . $row['instrument_name'];
        }

        return [$request, $leftList];
    }

    /*
      默认老师有空时间
     */

    public function teacherIndex()
    {
        $teacherInfo = $this->RTeacherAccess->getAllTeacherList();

        foreach ($teacherInfo as &$row) {
            $row['utilization'] = $row['count_1'] / 2 + $row['count_2'];
        }

        for ($i = 0; $i < count($teacherInfo); $i ++) {
            for ($j = 0; $j < count($teacherInfo); $j ++) {
                if ($teacherInfo[$i]['utilization'] < $teacherInfo[$j]['utilization']) {
                    $tmp = $teacherInfo[$i];
                    $teacherInfo[$i] = $teacherInfo[$j];
                    $teacherInfo[$j] = $tmp;
                }
            }
        }

        return $teacherInfo;
    }

    public function getTeacherByTime($teacherId, $time)
    {
        $timeStart = strtotime($time);
        $timeEnd = $timeStart + 86400;
        $week = date('w', $timeStart);
        $week = $week == 0 ? 7 : $week;
        $classList = $this->RClassAccess->getTeacherClassList($teacherId, $timeStart, $timeEnd);
        $dayTimeBit = $this->RTeacherAccess->getTeacherTimetable($teacherId, $timeStart);
        $fixedTimeRow = $this->RTeacherAccess->getTeacherFixedTimeRow($teacherId, $week);

        if (!empty($fixedTimeRow) && $fixedTimeRow['time_execute'] <= $timeStart) {
            $dayTimeBit = empty($dayTimeBit) ? $fixedTimeRow['time_bit'] : $dayTimeBit;
        } else {
            $dayTimeBit = empty($dayTimeBit) ? 281474976710656 : $dayTimeBit;
        }

        $flag = false;
        $each = array();
        $timeList = array();
        $num = 1;

        for ($i = 1; $i <= 49; $i ++) {
            if (($dayTimeBit & $num) == 0 && !$flag) {
                $flag = true;
                $tmp = $i / 2;
                $tmp = is_int($tmp) ? ($tmp - 1) . ":30" : floor($tmp) . ":00";
                $each['start'] = $tmp;
            } elseif (($dayTimeBit & $num) == $num && $flag) {
                $flag = false;
                $tmp = $i / 2;
                $tmp = is_int($tmp) ? ($tmp - 1) . ":30" : floor($tmp) . ":00";
                $each['end'] = $tmp;
                $timeList[] = $each;
                $each = [];
            }

            $num = $num << 1;
        }
        return [$classList, $timeList, $timeStart];
    }

    public function getEditClassPage($request)
    {
        $classInfo = $this->RClassAccess->getClassByClassid($request['class_id']);

        $leftList = $this->RClassAccess->getClassLeftInfo($classInfo['student_id']);

        return $leftList;
    }

    /**
     * 发送课程留言
     * @param  $classId
     * @param  $logid
     * @return array
     */
    /*
      public function getSendClassMessage($classId, $logid)
      {
      $templateId = Yii::$app->params['student_template_class_alarm'];

      $wechat = Yii::$app->wechat;

      $classInfo = $this->RClassAccess->getRowById($classId);
      $openId = $this->RStudentAccess->getUserOpenId($classInfo['student_id']);

      if (!empty($openId)) {
      $classType = $this->getClassLengthByClassType($classInfo['type']);;
      $classTime = $this->timeClassFormatAll($classInfo['time_class']);
      $firstValue = "您好,您的陪练课程已为您预约:\n";
      $key1word = array('value' => "VIP钢琴陪练 $classType");
      $key2word = array('value' => $classTime . "\n");
      $remark = "请提前将本周需要陪练的乐谱发送给客服，让老师提前准备可以给您更好的服务质量。\n上课前5分钟，请打开您的上课软件并保持网络畅通，老师将准时呼叫过来。";

      $message = $this->buildMessage($openId, $templateId, $firstValue, $key1word, $key2word, $remark);

      $wechat->sendTemplateMessage($message);

      LogService::OutputLog($logid, 'update', '', '发送排课');

      return json_encode(array('error' => ''));
      } else {
      return json_encode(array('error' => 'not_bind_wechat'));
      }
      }
     */
    /*
      private function timeClassFormatAll($timeClass)
      {
      $week = date('w', $timeClass);
      switch ($week) {
      case 0 :
      $week = '周日';
      break;
      case 1 :
      $week = '周一';
      break;
      case 2 :
      $week = '周二';
      break;
      case 3 :
      $week = '周三';
      break;
      case 4 :
      $week = '周四';
      break;
      case 5 :
      $week = '周五';
      break;
      case 6 :
      $week = '周六';
      break;

      }
      return date('m月d日 ', $timeClass) . $week . date('H:i', $timeClass);
      }
     */
    /*
      private function buildMessage($openid, $templateId, $firstValue, $key1word, $key2word, $remark)
      {
      $data = array(
      'first' => array('value' => $firstValue),
      'keyword1' => $key1word,
      'keyword2' => $key2word,
      'remark' => array('value' => $remark)
      );

      $message = array(
      'touser' => $openid,
      'template_id' => $templateId,
      'url' => '',
      'data' => $data
      );
      return $message;
      }
     */

    /**
     * @param $student_id
     * @return mixed
     * create by sjy
     * 获取课表信息数量
     */
    public function getCourseCount($type, $timeStart, $timeEnd, $passId, $tag, $filter)
    {
        $count = $this->RClassAccess->getCourseCount($type, $timeStart, $timeEnd, $passId, $tag, $filter);
        return $count;
    }

    /**
     * @param
     * @return mixed
     * create by sjy
     * 获取课表信息list
     */
    public function getCourseInfo($type, $timeStart, $timeEnd, $passId, $pageNum, $tag, $filter)
    {
        $courseInfo = $this->RClassAccess->getCourseInfo($type, $timeStart, $timeEnd, $passId, $pageNum, $tag, $filter);
        foreach ($courseInfo as $key => &$val) {
            $have_image = unserialize($val['course_info']);
            $courseInfo[$key]['have_image'] = !empty($have_image) || !empty($val['class_id']) ? 1 : 0;

            $long = ($val['time_end'] - $val['time_class']) / 60;

            $courseInfo[$key]['money'] = $this->workhourService->getClassMoney($long, $val['time_class'], $val['teacher_id']);
        }

        return $courseInfo;
    }

    /**
     * @param
     * @return mixed
     * create by sjy
     * 获取课程信息
     */
    public function getClassInfo($classId)
    {
        $classinfo = $this->RClassAccess->getClassInfo($classId);
        return $classinfo;
    }

    public function teacherClassRecordPage($teacherId, $courseFilter, $statusFilter)
    {
        return $this->RClassAccess->teacherClassRecordCount($teacherId, $courseFilter, $statusFilter);
    }

    public function teacherClassRecordList($teacherId, $courseFilter, $statusFilter, $pageNum)
    {
        return $this->RClassAccess->teacherClassRecordList($teacherId, $courseFilter, $statusFilter, $pageNum);
    }

    public function getClassHistoryPage($studentId)
    {
        return $this->RClassAccess->getClassHistoryPage($studentId);
    }

    public function getClassHistoryList($studentId, $num)
    {
        return $this->RClassAccess->getClassHistoryList($studentId, $num);
    }

    public function getClassRecordPage($studentId)
    {
        return $this->RClassAccess->getClassRecordPage($studentId);
    }

    public function getClassRecordList($studentId, $num)
    {
        return $this->RClassAccess->kefuGetClassRecordList($studentId, $num);
    }

    public function getCancelClassCount($start, $end, $teacherinfo, $studentinfo, $cancel = 4)
    {
        $start = strtotime($start);
        $end = strtotime($end) + 86400;
        return $this->RClassAccess->getCancelClassCount($start, $end, $teacherinfo, $studentinfo, $cancel);
    }

    public function getCancelClassList($start, $end, $teacherinfo, $studentinfo, $num, $cancel = 4)
    {
        $start = strtotime($start);
        $end = strtotime($end) + 86400;

        $list = $this->RClassAccess->getCancelClassList($start, $end, $teacherinfo, $studentinfo, $num, $cancel);
        foreach ($list as &$v) {
            if ($v['is_ex_class'] == 1) {
                $v['class_type'] = '体验课程';
                $v['color'] = 'yellow';
            } else {
                $v['class_type'] = '购买课程';
                $v['color'] = 'green';
            }

            $time = round(($v['time_end'] - $v['time_class']) / 60);

            if ($time > 45) {
                $v['time_color'] = 'green';
            } elseif ($time == 45) {
                $v['time_color'] = 'yellow';
            } else {
                $v['time_color'] = 'red';
            }
            $v['class_status'] = $time . '分钟课程';

            if ($v['is_teacher_cancel'] == 0) {
                $v['cancel_type'] = '家长取消';
            } elseif ($v['is_teacher_cancel'] == 1) {
                $v['cancel_type'] = '老师取消';
            } elseif ($v['is_teacher_cancel'] == 2) {
                $v['cancel_type'] = '乐谱原因';
            } elseif ($v['is_teacher_cancel'] == 3) {
                $v['cancel_type'] = '上课端原因';
            } else {
                $v['cancel_type'] = '未知原因';
            }

            // $v['class_time'] = date('Y-m-d H:i',$v['time_class']).' - '.date('Y-m-d H:i',$v['time_end']);
            $v['class_time'] = date('Y-m-d H:i', $v['time_class']);
        }
        return $list;
    }

    /**
     * 获取无老师列表条数
     * @param  $day
     * @param  $name
     * @return array
     */
    public function getNoTeacherCount($day, $name, $type)
    {
        $returnData = array(
            'error' => 0,
            'data' => []
        );

        $dayNow = strtotime(date('Y-m-d', time()));
        $dayNow - 86400 * 5;
        $timeLimit = $dayNow + 86400 * 5;

        if (empty($day) || $day == "undefined") {
            $day = date('Y-m-d', $dayNow);
        }

        $timeStart = strtotime($day);
        $timeEnd = $timeStart + 86400 * 7;

        if ($timeStart < $timeStart || $timeStart > $timeLimit) {
            $returnData['error'] = '超出查询范围, 查询开始时间只能是前后5天';
            return $returnData;
        }

        $totalCount = $this->RClassAccess->getNoTeacherCount($timeStart, $timeEnd, $name, $type);

        $returnData['data']['count'] = $totalCount;
        return $returnData;
    }

    /**
     * 获取无老师列表list
     * @param  $day
     * @param  $name
     * @return array
     */
    public function getNoTeacherList($page, $day, $name, $type)
    {
        if (empty($day) || $day == "undefined") {
            $day = date('Y-m-d', time());
        }
        $timeStart = strtotime($day);
        $timeEnd = $timeStart + 86400 * 7;
        $failData = $this->RClassAccess->getNoTeacherList($page, $timeStart, $timeEnd, $name, $type);
        return $failData;
    }

    public function getClassRoomInfoByClassId($classId)
    {
        $list = $this->RClassAccess->getClassRoomInfoByClassId($classId);

        if (!empty($list)) {
            foreach ($list as &$row) {
                $time_class = $row['time_class'];
                $class_time_start = empty($time_class) ? '无' : date('Y-m-d H:i', $time_class);
                $time_end = $row['time_end'];
                $clastime_time_end = empty($time_end) ? '无' : date('H:i', $time_end);
                $row['class_rang'] = $class_time_start . "--" . $clastime_time_end;
            }
        }

        return empty($list) ? [] : $list;
    }

    public function doChangeClassTime($classId, $ahead, $defer)
    {
        $ahead = $ahead * 60;
        $defer = $defer * 60;
        $flag = $this->WClassAccess->doChangeClassTime($classId, $ahead, $defer);
        if ($flag) {
            $list = $this->RClassAccess->getClassRoomInfoByClassId($classId);
            return json_encode(['error' => '',
                'student_id' => $list[0]['student_id'],
                'date' => date('Y-m-d', $list[0]['time_class'])
            ]);
        }
        return json_encode(['error' => '操作失败!!']);
    }
    
    public function getClassTimeBySaleIdCount($saleId, $keyword, $type, $start = 0, $end = 0)
    {
        if ($type == 3) {
            //注册并没有课的学生生user
            $user_init_ids_1 = $this->RClassAccess->getNotExclassInUser($saleId);
            //所有关注但没注册的学生
            $user_init_ids_2 = $this->RClassAccess->getNotInUserButInUserInit($saleId);
            $ui_ids = array_merge($user_init_ids_1, $user_init_ids_2);

            $count= $this->RClassAccess->getStudentNotExperienceCount($ui_ids, $keyword, $start, $end);
        } else {
            //获取已经完成体验课的用户
            $useridHaveEx = $this->RClassAccess->getUserHaveExByClass($saleId);
            $count = $this->RClassAccess->getClassTimeBySaleIdCount($saleId, $keyword, $type, $start, $end, $useridHaveEx);
        }
        return ['error' => 0, 'data' => $count];
    }

    public function getClassTimeBySaleId($saleId, $keyword, $type, $num = 0, $start = 0, $end = 0)
    {
        if ($type == 3) {
            //注册并没有课的学生生user
            $user_init_ids_1 = $this->RClassAccess->getNotExclassInUser($saleId);
            //所有关注但没注册的学生
            $user_init_ids_2 = $this->RClassAccess->getNotInUserButInUserInit($saleId);
            $ui_ids = array_merge($user_init_ids_1, $user_init_ids_2);

            $list= $this->RClassAccess->getStudentNotExperienceList($ui_ids, $num, 10, $keyword, $start, $end);

            foreach ($list as &$item) {
                $item["mobile"] = empty($item["mobile"]) ? "未注册 " : $item["mobile"];
                $item["nick"] = empty($item["nick"]) ? "未填写" : $item["nick"];
                $item["name"] = empty($item["name"]) ? "未填写" : $item["name"];
            }
        } else {
            $useridHaveEx = $this->RClassAccess->getUserHaveExByClass($saleId);
            $list = $this->RClassAccess->getClassTimeBySaleId($saleId, $keyword, $type, $num, $start, $end, $useridHaveEx);
        }
        $data = array(
            'list' => $list,
            'type' => $type,
            'keyword' => $keyword
        );
        return ['error' => 0, 'data' => $data];
    }

    
    public function getClassTimeAndStudentName($classId)
    {
        $data = $this->RClassAccess->getClassTimeAndStudentName($classId);
        return array('error' => 0, 'data' => $data);
    }

    public function getLeftClassType($openId)
    {
        $student = $this->RChatAccess->getWechatAccByExist($openId);
        $classType = $this->RClassAccess->getLeftClassType($student['uid']);
        if (empty($classType)) {
            return array('error' => '该用户没有购买套餐');
        } else {
            $instrumentType = $this->RClassAccess->getLeftInstrument($student['uid']);
            return array('error' => 0, 'data' =>
                array('class_type' => $classType, 'instrument_type' => $instrumentType));
        }
    }
}
