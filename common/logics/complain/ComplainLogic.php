<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 17/1/3
 * Time: 10:17
 */
namespace common\logics\complain;

use Yii;
use yii\base\Object;
use yii\data\Pagination;


class ComplainLogic extends Object implements IComplain
{
    /** @var  \common\sources\read\complain\ComplainAccess  $RComplainAccess */
    private $RComplainAccess;
    /** @var  \common\sources\write\complain\ComplainAccess  $WComplainAccess */
    private $WComplainAccess;
    /** @var  \common\sources\read\classes\ClassAccess  $RClassAccess */
    private $RClassAccess;
    /** @var  \common\sources\read\student\StudentAccess  $RStudentAccess */
    private $RStudentAccess;
    private $salaryCompute;


    public function init()
    {
        $this->RComplainAccess = Yii::$container->get('RComplainAccess');
        $this->WComplainAccess = Yii::$container->get('WComplainAccess');
        $this->RStudentAccess = Yii::$container->get('RStudentAccess');
        $this->RClassAccess = Yii::$container->get('RClassAccess');
        $this->salaryCompute = Yii::$container->get('salaryCompute');

        parent::init();
    }

    public function complainPage($timeStart,$timeEnd,$status)
    {
        $count = $this->RComplainAccess->getComplainCount($timeStart,$timeEnd,$status);

        return $count;
    }

    public function complainList($timeStart, $timeEnd, $status, $page_num)
    {

        $info = $this->RComplainAccess->getComplainInfo($timeStart,$timeEnd,$status,$page_num);

//        print_r($info);exit;

        foreach($info as $k => &$item)
        {
            if (!empty($item['class_id']))
            {
                $new = $this->RStudentAccess->getUserInfoByClassId($item['class_id']);

//                print_r($new);exit;
                if(empty($item['open_id']))
                {
                    $item['mobile'] = $new['mobile'];
                    $item['nick'] = $new['nick'];
                    $item['head'] = $new['head'];
                    $item['wename'] = $new['wename'];
                }

                $info[$k]['teacher_name'] = $new['teacher_name'];
                $info[$k]['time_class'] = $new['time_class'];
                $info[$k]['time_end'] = $new['time_end'];
                $class_type = empty($new['is_ex_class']) ? '购买课' : '体验课';
                $info[$k]['class_type'] = $class_type . ' '. (($new['time_end'] - $new['time_class']) / 60) . '分钟课';
            }

            if (!empty($item['reward_record_id']))
            {
                if (empty($item['prefix']))
                {
                    $item['money'] = '-'.$item['money'];
                }else{
                    $item['money'] = '+'.$item['money'];
                }
            }
        }

//        print_r($info);exit;

        return $info;
    }

    public function getRelateClass($timeStart, $timeEnd, $class_filter, $student_id)
    {
        $classes = $this->RClassAccess->getRelateClass($timeStart, $timeEnd, $class_filter, $student_id);

        foreach ($classes as $k =>$item)
        {
            $classes[$k]['class_type'] = ($item['time_end'] - $item['time_class']) / 60;
        }

        return $classes;
    }

    public function relateClass($complain_id, $class_id)
    {
        $re = $this->WComplainAccess->relateClass($complain_id,$class_id);

        return $re;
    }

    public function updateComplainStatus($request)
    {
        $info = $this->RComplainAccess->getComplainById($request['id']);

        if(empty($info['open_id']) && !empty($info['class_id']))
        {
            $info['open_id'] = $this->RStudentAccess->getOpenIdByClassId($info['class_id']);
        }

        $re = $this->WComplainAccess->updateComplainStatus($request);

        if ($re)
        {
            return array(
                'error' => '',
                'complain_info' => $info,
            );
        }else {
            return array(
                'error' => '受理失败',
                'complain_info' => '',
                );
        }
    }

    public function noDealComplain($request)
    {
//        $info = $this->RComplainAccess->getComplainById($request['id']);

        $request['teacher_context'] = '无';
        $request['teacher_remark'] = '无';

        $re = $this->WComplainAccess->updateComplainStatus($request);

        if($re)
        {
            return 1;
        }else{
            return 0;
        }
    }


    public function getStudentList($filter)
    {
        return $this->RStudentAccess->getStudentList($filter);
    }

    public function doAddComplain($request)
    {
        $student_id = $request['student_id'];

        $openid = $this->RStudentAccess->getStudentOpenId($student_id);

        return $this->WComplainAccess->doAddComplain($openid,$request);
    }

    public function transfer($complain_id)
    {
        $re = $this->WComplainAccess->updateComplainTag($complain_id,2);

        return $re;
    }

    public function countComplainPage($student_id)
    {
        return $this->RComplainAccess->countComplainPage($student_id);
    }

    public function getComplainList($student_id , $num)
    {
        $list = $this->RComplainAccess->getComplainList($student_id , $num);
        return $list;
    }

    public function countPurchaseComplain($keyword)
    {
        return $this->RStudentAccess->countPurchaseComplain($keyword);
    }

    public function getPurchaseComplainList($keyword , $num){
        $list = $this->RStudentAccess->getPurchaseComplainList($keyword , $num);

        $week=['日','一','二','三','四','五','六'];
        if (!empty($list))
        {
            foreach ($list as &$row)
            {
                if (!empty($row['channelname']))
                {
                    $row['channel_type'] = '主课';
                    $row['qudao'] = $row['channelname'];
                } elseif (!empty($row['channelname_2']))
                {
                    $row['qudao'] = $row['channelname_2'];
                    if ($row['channel_type'] == 2)
                    {
                        $row['channel_type'] = '家长';
                    } elseif ($row['channel_type'] == 5)
                    {
                        $row['channel_type'] = '活动';
                    } else {
                        $row['channel_type'] = '其他';
                    }
                } else {
                    $row['channel_type'] = '无';
                    $row['qudao'] = '无';
                }

                $time_class=$row['time_class'];
                $row['time_class'] =empty($time_class) ? '无':"周".$week[date('w',$time_class)]." ".date('Y-m-d H:i',$time_class);

                $row['course_info'] = unserialize($row['course_info']);
                $row['have_image'] = !empty($row['course_info']) || !empty($row['ci_class_id']) ? 1 : 0;

                if(empty($time_class)){
                    $row['have_image'] = 2;
                }
            }
        }

        return empty($list) ? [] : $list;
    }


    /**
     * 家长投诉列表
     * @param  $status int 是否处理
     * @param  $page
     * @return array
     */
    public function getParentComplaintsList($status,$page)
    {
        $info = $this->RComplainAccess->getParentComplaintsList($status,$page);
        foreach($info as &$item)
        {
            if(empty($item['open_id']) && !empty($item['class_id']))
            {
                $new = $this->RStudentAccess->getStundentInfoByClassId($item['class_id']);

                $item['mobile'] = $new['mobile'];
                $item['nick'] = $new['nick'];
                $item['head'] = $new['head'];
                $item['wename'] = $new['wename'];
            }
        }

        return $info;
    }

    /**
     * 家长投诉页面
     * @param  $status int 是否处理
     * @return array
     */
    public function getParentComplaintsPage($status)
    {
        return $this->RComplainAccess->getParentComplaintsPage($status);
    }



    /**
     * 处理投诉页面
     * @param  $cid
     * @return array
     */
    public function dealComplaintsPage($cid)
    {
        return $this->RComplainAccess->dealComplaintsInfo($cid);
    }


    /**
     * 无需处理
     * @param $request
     * @return int
     */
    public function noDealComplaints($request = '')
    {
        if(empty($request)){
            return 0;
        }

        $info = $this->RComplainAccess->getComplainById($request['id']);

        $request['kefu_remark'] = '无';
        $request['kefu_context'] = '无';
        
        $re = $this->WComplainAccess->doUpdateComplaintInfo($request,$info['teacher_context']);

        if($re)
        {
            return 1;
        }else{
            return 0;
        }
    }


    /**
     * 处理投诉
     * @param  $request array
     * @return int
     */
    public  function doUpdateStatus($request='')
    {
        if (empty($request))
        {
            return 0;
        }

        $info = $this->RComplainAccess->getComplainById($request['id']);


        if(empty($info['open_id']) && !empty($info['class_id']))
        {
            $info['open_id'] = $this->RStudentAccess->getOpenIdByClassId($info['class_id']);
        }

        $re = $this->WComplainAccess->doUpdateComplaintInfo($request, $info['teacher_context']);

        if ($re == 1)
        {
            $data = array (
                'open_id' => $info['open_id'],
                'content' => $info['content'],
                'time_created' => $info['time_created'],
                'kefu_context' => $request['kefu_context']
            );
            
            return array('error' => 0, 'data' => $data);
        }
    }

/*
    public function SendComplainMessage($request,$info)
    {
        $wechat = Yii::$app->wechat;
        $arr = [
            'touser' => $info['open_id'],
            'template_id' => Yii::$app->params['student_template_feedback'],
            'data' => [
                'first' => [
                    'value' => "您好，您的反馈已收到:\n".$info['content'],
                    'color' => '#0F0F0F'
                ],
                'CreateDate' => [
                    'value' => date('Y-m-d',$info['time_created']),
                    'color' => '#0F0F0F'
                ],
                'ProcessingResults' => [
                    'value' => $request['kefu_context'],
                    'color' => '#c9302c'
                ],
                'remark' => [
                    'value' => "感谢您的反馈，我们会不断改进，精益求精。",
                    'color' => '#707070'
                ],
            ],
        ];
        $wechat->sendTemplateMessage($arr);
    }
*/
    public function computeComplainReward($class_id, $reward_id)
    {
        $class_info = $this->RClassAccess->getClassByClassid($class_id);

        $long = ($class_info['time_end'] - $class_info['time_class']) / 60;


        return $this->salaryCompute->calculateSalary($class_info['teacher_id'], $class_info['time_class'], $long, $class_id, $reward_id);

    }

}