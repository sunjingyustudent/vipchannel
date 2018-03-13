<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/16
 * Time: 上午11:52
 */
namespace common\logics\classes;

use Yii;
use yii\base\Object;
use yii\db\Exception;
use yii\db\Query;
use common\widgets\NeteaseAPI;

class MonitorLogic extends Object implements IMonitor
{
    /** @var  \common\sources\read\classes\ClassAccess  $RClassAccess */
    private $RClassAccess;
    /** @var  \common\sources\write\classes\ClassAccess  $WClassAccess */
    private $WClassAccess;
    /** @var  \common\sources\read\student\StudentAccess  $RStudentAccess */
    private $RStudentAccess;
    /** @var  \common\sources\read\teacher\TeacherAccess  $RTeacherAccess */
    private $RTeacherAccess;


    public function init()
    {
        $this->RClassAccess = Yii::$container->get('RClassAccess');

        $this->WClassAccess = Yii::$container->get('WClassAccess');   
        $this->RStudentAccess = Yii::$container->get('RStudentAccess');
        $this->RTeacherAccess = Yii::$container->get('RTeacherAccess');
        parent::init();
    }

    public  function getMonitorCount($type, $date, $keyword, $kefu_id,$monitor_courseType)
    {
        $kefu = $this->RStudentAccess->getKefuInfo($kefu_id);

        //今天的课程
        $current = strtotime(date("Y-m-d H:i",time()));
        $currentDate = strtotime(date("Y-m-d",time()));
        $timeStart = strtotime($date);
        $timeEnd = $timeStart + 86400;

        if($currentDate > $timeStart){
            //今天之前的某一天
            $current = $timeEnd;

        }else if($currentDate < $timeStart){
            //今天之后的某一天
            $current = $timeStart;
        }

	    return  $this->RClassAccess->getClassMonitorCount($kefu, $current, $timeStart, $timeEnd, $keyword, $type, $monitor_courseType);

    }

    public function MlistList($page, $type,$date, $keyword, $kefu_id, $monitor_courseType)
    {
       $kefu = $this->RStudentAccess->getKefuInfo($kefu_id);

        //今天的课程
        $current = strtotime(date("Y-m-d H:i",time()));
        $currentDate = strtotime(date("Y-m-d",time()));
        $timeStart = strtotime($date);
        $timeEnd = $timeStart + 86400;

        if($currentDate > $timeStart){
            //今天之前的某一天
            $current = $timeEnd;

        }else if($currentDate < $timeStart){
            //今天之后的某一天
            $current = $timeStart;
        }

       	$data = $this->RClassAccess->getClassMonitorList($kefu, $current, $timeStart, $timeEnd, $keyword, $type, $page, $monitor_courseType);

        foreach ($data as &$item){
            $stu=$this->RClassAccess->getClassNetBeanByStudentid($item);
            $tec=$this->RClassAccess->getClassNetBeanByTeacherid($item);

            $item["student_net"] = $stu["net_desc"];
            $item["teacher_net"] = $tec["net_desc"];

            $stuStatus = $this->RClassAccess->getClassStudentStatus($item);

            $tecStatus = $this->RClassAccess->getClassTecStatus($item);

            $item["student_status"] = $stuStatus["name"];
            $item["teacher_status"] = $tecStatus["name"];

            $item["student_class"] = "";
            if($stuStatus["id"] == 1){
                $item["student_class"] = "yellow";
            }else if($stuStatus["id"] == 3){
                $item["student_class"] = "green";
            }else{
                $item["student_class"] = "red";
            }

            $item["teacher_class"] = "";
            if($tecStatus["id"] == 1){
                $item["teacher_class"] = "yellow";
            }else if($tecStatus["id"] == 3){
                $item["teacher_class"] = "green";
            }else{
                $item["teacher_class"] = "red";
            }

            $classType = $item["time_end"] - $item["time_class"];
            if($classType == 1500){
                $item["classType"] = "[25min]";
            }else if($classType == 2700){
                $item["classType"] = "[45min]";
            }else{
                $item["classType"] = "[50min]";
            }

            if($stuStatus["id"]==3 && $tecStatus["id"] == 3){
                $item["bg"] = "greenbg";
            }elseif ($stuStatus["id"]==1 || $tecStatus["id"] == 1){
                $item["bg"] = "yellowbg";
            } else{
                $item["bg"] = "";
            }

            $time_class = $item['time_class'];
            $formal_min_time_class = $item['formal_min_time_class'];
            $item['is_first_pay_class'] = $formal_min_time_class == $time_class ? 1:0;
        }

        return $data;
    }


    public function synAccount($req)
    {
        if($req->isPost){
            $AppKey = 'ff0f9a72db5b719dad88ce9dd23c16b7';
            $AppSecret = 'afcc2f923f42';
            $type=$req->post("type");
            $id = $req->post("id");
            $phone = $req->post("phone");

            if($type == 1){ //学生
                $data = $this->RStudentAccess->getUserById($id);
                if($data && !empty($data->chat_token)){
                    return 1;
                }
            }else{ //老师
                $data = $this->RTeacherAccess->geTeacherById($id);
                if($data && !empty($data->chat_token)){
                    return 1;
                }
            }

            $api = new NeteaseAPI($AppKey,$AppSecret,'curl');
            $res = $api->createUserId($phone,"miaoke");

            if($res["code"] == 200){
                $token =$res["info"]["token"];

            }else{
                $update = $api->updateUserToken($phone);
                if($update["code"] == 200){
                    $token =$update["info"]["token"];
                }else{
                    return $res["desc"];
                }
            }

            if($type == 1){ //学生
                $data = $this->RStudentAccess->getUserById($id);
                if($data){
                    $chat_token = $token;
                    $accessToken = $phone . $token;

                    $this->WClassAccess->updateClassSyn($data,$chat_token,$accessToken);

                    return 1;
                }
            }else{ //老师
                $data = $this->RTeacherAccess->geTeacherById($id);
                if($data){
                    $chat_token = $token;
                    $accessToken = $phone . $token;

                    $this->WClassAccess->updateClassSyn($data,$chat_token,$accessToken);

                    return 1;
                }
            }
        }

        return "Sys Error";
    }


}
