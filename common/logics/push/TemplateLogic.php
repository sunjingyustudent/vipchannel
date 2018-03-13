<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/10
 * Time: 下午2:22
 */
namespace common\logics\push;

use common\services\ErrorService;
use common\widgets\Queue;
use common\widgets\TimeFormatHelper;
use Yii;
use common\widgets\Request;
use yii\base\Object;
use common\widgets\Message;

class TemplateLogic extends Object implements ITemplate {

    /** @var  \common\sources\read\channel\ChannelAccess  $RChannelAccess */
    private $RChannelAccess;
    /** @var  \common\sources\read\classes\ClassAccess  $RClassAccess */
    private $RClassAccess;
    /** @var  \common\sources\write\classes\ClassAccess  $WClassAccess */
    private $WClassAccess;
    /** @var  \common\sources\read\student\StudentAccess  $RStudentAccess */
    private $RStudentAccess;
    /** @var  \common\sources\write\channel\ChannelAccess  $WChannelAccess */
    private $WChannelAccess;
    /** @var  \common\sources\read\teacher\TeacherAccess  $RTeacherAccess */
    private $RTeacherAccess;
    /** @var  \common\sources\write\teacher\TeacherAccess  $WTeacherAccess */
    private $WTeacherAccess;
    /** @var  \common\sources\read\salary\BasepayAccess $RBasepayAccess */
    private $RBasepayAccess;
    /** @var  \common\sources\read\salary\WorkhourAccess $RWorkhourAccess */
    private $RWorkhourAccess;
    /** @var  \common\sources\read\salary\RewardAccess $RRewardAccess */
    private $RRewardAccess;

    public function init()
    {
        $this->RClassAccess = Yii::$container->get('RClassAccess');
        $this->RChannelAccess = Yii::$container->get('RChannelAccess');
        $this->RStudentAccess = Yii::$container->get('RStudentAccess');
        $this->WClassAccess = Yii::$container->get('WClassAccess');
        $this->WChannelAccess = Yii::$container->get('WChannelAccess');
        $this->RTeacherAccess = Yii::$container->get('RTeacherAccess');
        $this->WTeacherAccess = Yii::$container->get('WTeacherAccess');
        $this->RBasepayAccess = Yii::$container->get('RBasepayAccess');
        $this->RWorkhourAccess = Yii::$container->get('RWorkhourAccess');
        $this->RRewardAccess = Yii::$container->get('RRewardAccess');
    }

    public function sendStudentTemplate($msg)
    {
        $wechat = Yii::$app->wechat;

        if (!$wechat->sendTemplateMessage($msg))
        {
            //ErrorService::AddStudentTemplateError($message, '发送失败');
        }

        return true;
    }

    public function sendChannelTemplate($msg)
    {
        $wechat = Yii::$app->wechat_new;

        if (!$wechat->sendTemplateMessage($msg))
        {
            //ErrorService::AddStudentTemplateError($msg, '发送失败');
        }

        return true;
    }

    public function sendTeacherTemplate($msg)
    {
        $wechat = Yii::$app->wechat_teacher;

        if (!$wechat->sendTemplateMessage($msg))
        {
            //ErrorService::AddStudentTemplateError($msg, '发送失败');
        }

        return true;
    }

    public function dealStudentRecordTemplate($msg)
    {
        $classInfo = $this->RClassAccess->getClassTimeById($msg['class_id']);

        $param = array (
            'template_id' => 'vK5I2V6BiShuCsceDbJjb38ssKjf6y9a6AblZCbnCeM',
            'firstValue' => '您本次课后记录',
            'key1word' => 'VIP钢琴课程',
            'key2word' => date('m-d H:i', $classInfo['time_class']) . '-' . date('H:i', $classInfo['time_end']),
            'remark' => '点击查看课后单，时刻掌握宝宝练习进度，评价并分享，更有机会轻松得福利哦~',
            'url' => 'http://yii.pnlyy.com/class/record-detail-share?class_id=' . $msg['class_id'],
            'keyword_num' => 2
        );

        return Queue::produce(
            $this->buildMessage($param),
            'template',
            'student_template'
        );
    }

    public function dealChannelPurchaseTemplate($data)
    {
        $openId = $this->RChannelAccess->getChannelBindOpenid($data['sales_id']);

        if (!empty($openId))
        {
            /*
            $param = array (
                'template_id' => 'qEv7BQgG0e5sPKFG4OpfdwMlicHpOn1cIv9VYshcp6I',
                'firstValue' => "谢谢老师，您的学生（" . $name . "）已购买了我们的陪练套餐，相应推广奖励已放入您的账户中，每次上完课程会进行解锁。我们将与您协助，共同让孩子练琴更优秀！\n",
                'key1word' => 'VIP陪练',
                'key2word' => date('Y-m-d H:i', time()) . "\n",
                'remark' => '感谢您对VIP陪练的支持！希望我们的陪练服务能使您的教学更加轻松！',
                'url' => 'http://channel.pnlyy.com/mine/account-index',
                'keyword_num' => 2
            );
            */

            $param = array (
                'first' => array('value' => '您好，您有一笔推广奖励可领取！'),
                'keyword1' => array('value' =>'推广收入'),
                'keyword2' => array('value' => $data['price'] . '元' , 'color' => '#FF0000'),
                'keyword3' => array('value' => date('Y年m月d日 H:i', time())),
                'remark' => array('value' => '您邀请的用户(' . $data['student_name'] . ')购买VIP陪练课程。您获得一笔奖励，请在公众号输入"提现"，领取您的奖励.'),
            );

            $message = array (
                'touser' => $openId,
                'template_id' => Yii::$app->params['channel_template_income'],
                'url' => Yii::$app->params['channel_base_url'] . 'live/show-my-harvest',
                'data' => $param
            );


            return Queue::produce(
                $message,
                'template',
                'channel_template'
            );

        }else {
            return false;
        }
    }

    public function dealTwoChannelPurchaseTemplate($data)
    {
        $info = $this->RChannelAccess->getChannelFromCode($data['sales_id']);

        if (empty($info['from_code']))
        {
            return false;
        }

        $openId = $this->RChannelAccess->getChannelBindOpenidByPrivateCode($info['from_code']);

        if (!empty($openId))
        {
            $param = array (
                'first' => array('value' => '您好，您有一笔推广奖励可领取！'),
                'keyword1' => array('value' =>'推广收入'),
                'keyword2' => array('value' => $data['price'] * 0.5 . '元' , 'color' => '#FF0000'),
                'keyword3' => array('value' => date('Y年m月d日 H:i', time())),
                'remark' => array('value' => '您邀约的用户（'.$info['wechat_name'].'），TA的学生购买VIP陪练课程。您获得二级奖励，请在公众号输入“提现”，领取您的奖励。'),
            );

            $message = array (
                'touser' => $openId,
                'template_id' => Yii::$app->params['channel_template_income'],
                'url' => Yii::$app->params['channel_base_url'] . 'live/show-my-harvest',
                'data' => $param
            );

            return Queue::produce(
                $message,
                'template',
                'channel_template'
            );

        }else {
            return false;
        }
    }


    public function dealChannelIncomeTemplate($message)
    {
        return Queue::produce(
            $this->buildMessage($message, $message['open_id']),
            'template',
            'channel_template'
        );
    }

    public function dealCancelClassTemplate($request)
    {
        $firstValue = '您好，您有新的消息！';
        $key1word = '课程取消';
        $key2word = date('m-d H:i', $request['time_cancel']);

        $classInfo = $this->RClassAccess->getRowByClassId($request['class_id']);
        
        $studentInfo = $this->RStudentAccess->getUserRowById($classInfo['student_id']);

        $openid = $this->RStudentAccess->getOpenidByUid($classInfo['student_id']);

        if (empty($openid))
        {
            return true;
        }

        if($request['time_cancel'] + 3600 <= $classInfo['time_class'])
        {
            $key3word = "亲爱的{$studentInfo['nick']}家长，您的课程：\n"
                . date('m-d H:i', $classInfo['time_class']) . "\n\n"
                . "该课程已进行了取消。";
        }elseif($request['time_cancel'] + 3600 > $classInfo['time_class'] && empty($request['is_reduce']))
        {
            $times = (2 - $request['cancel_count']) <= 0 ? 0 : 2 - $request['cancel_count'];
            $key3word = "您好，您的课时已被您临时取消/迟到取消。\n"
                . "您每个月有三次机会在上课时间之前1小时内临时取消，目前还剩余" . $times . "次机会。\n"
                . "如需要取消，请提前通知我们，不然老师已经安排给您时间，会造成老师和之后学生的时间浪费。";
        }elseif($request['time_cancel'] + 3600 > $classInfo['time_class'] && !empty($request['is_reduce']))
        {
            $key3word = "您好，您的课时已被您临时取消/迟到取消。\n"
                . "您本月已超过3次临时取消/迟到取消，我们将扣除您1次课时。\n"
                . "如需取消，请提前通知我们，不然老师已经安排给您时间，会造成老师和之后学生的时间浪费。";
        }

        $param = array (
            'template_id' => Yii::$app->params['student_template_personal'],
            'firstValue' => $firstValue,
            'key1word' => $key1word,
            'key2word' => $key2word,
            'remark' => '',
            'url' => '',
            'keyword_num' => 2
        );

        return Queue::produce(
            $this->buildMessage($param, $openid),
            'template',
            'student_template'
        );
    }

    public function dealSendStudentTemplate($request)
    {
        $user = $this->RStudentAccess->getUserInitByOpenId($request['open_id']);

        $firstValue = "您有一条新消息";
        $key1word = $user['name'];
        $key2word = 'VIP陪练客服';
        $key3word = $request['content'];
        $key4word = date('Y-m-d H:i', time());
        $remark = "回复本条消息我们的客服才能联系到您哦";

        $param = array (
            'template_id' => Yii::$app->params['student_template_kefu_message'],
            'firstValue' => $firstValue,
            'key1word' => $key1word,
            'key2word' => $key2word,
            'key3word' => $key3word,
            'key4word' => $key4word,
            'remark' => $remark,
            'url' => '',
            'keyword_num' => 4
        );

        return Queue::produce(
            $this->buildMessage($param, $request['open_id']),
            'template',
            'student_template'
        );
    }

    public function dealSendChannelTemplate($request)
    {
        $user = $this->RStudentAccess->getUserInitByOpenId($request['open_id']);

        $firstValue = "您有一条新消息";
        $key1word = $user['name'];
        $key2word = 'VIP陪练客服';
        $key3word = $request['content'];
        $key4word = date('Y-m-d H:i', time());
        $remark = "回复本条消息我们的客服才能联系到您哦";

        $param = array (
            'template_id' => Yii::$app->params['student_template_kefu_message'],
            'firstValue' => $firstValue,
            'key1word' => $key1word,
            'key2word' => $key2word,
            'key3word' => $key3word,
            'key4word' => $key4word,
            'remark' => $remark,
            'url' => '',
            'keyword_num' => 4
        );

        return Queue::produce(
            $this->buildMessage($param, $request['open_id']),
            'template',
            'channel_template'
        );
    }



    public function sendClassMessage($classId)
    {
        $wechat = Yii::$app->wechat;

        $classInfo = $this->RClassAccess->getRowById($classId);
        $openId = $this->RStudentAccess->getUserOpenId($classInfo['student_id']);

        if (!empty($openId)) 
        {
            $classType = TimeFormatHelper::getClassLengthByClassType($classInfo['type']);;
            $classTime = TimeFormatHelper::timeClassFormatAll($classInfo['time_class']);

            $remark = "请提前将本周需要陪练的乐谱发送给客服，让老师提前准备可以给您更好的服务质量。\n上课前5分钟，请打开您的上课软件并保持网络畅通，老师将准时呼叫过来。";

            $param = array (
                'template_id' => Yii::$app->params['student_template_class_alarm'],
                'firstValue' => "您好,您的陪练课程已为您预约:\n",
                'key1word' => "VIP钢琴陪练 $classType",
                'key2word' => $classTime . "\n",
                'remark' => $remark,
                'url' => '',
                'keyword_num' => 2
            );

            return Queue::produce(
                $this->buildMessage($param, $openId),
                'template',
                'student_template'
            );
        }
    }

    public function dealSendGiveClass($request, $uid)
    {
        $wechat = Yii::$app->wechat;

        $studentInfo = $this->RClassAccess->getMessageRowById($uid);
        
        $param = array (
            'template_id' => Yii::$app->params['student_template_class_income'],
            'firstValue' => "亲爱的{$studentInfo['nick']}家长，您的{$request['amount']}节赠送课程已添加到您的账户中。\n",
            'key1word' => $request['amount'],
            'key2word' => TimeFormatHelper::getClassLengthByClassType($request['class_type']),
            'key3word' => 'VIP钢琴赠送课',
            'remark' => "点击详情查看剩余课时及历次陪练单。",
            'url' => 'http://wx.pnlyy.com/weixin/class-redirect',
            'keyword_num' => 3
        );

        return Queue::produce(
            $this->buildMessage($param, $request['open_id']),
            'template',
            'student_template'
        );
    }

    public function dealSendComplainMessage($data)
    {
        if (!empty($data['open_id']))
        {
            $param = array (
                'template_id' => Yii::$app->params['student_template_feedback'],
                'firstValue' => "您好，您的反馈已收到:\n".$data['content'],
                'key1word' => date('Y-m-d',$data['time_created']),
                'key2word' => $data['kefu_context'],
                'remark' => "感谢您的反馈，我们会不断改进，精益求精。",
                'url' => '',
                'keyword_num' => 2
            );

            return Queue::produce(
                $this->buildMessage($param, $data['open_id']),
                'template',
                'student_template'
            );
        }

        return true;
    }

    public function dealSendMoneyMessage($data)
    {
        $sale = $this->RStudentAccess->getSalesChannelId($data['sales_id']);

        if (!empty($sale))
        {
            if ($data['money'] == 188) {
                $money_comment = '哇！一次注册就带来了超大红包！奖励188元，已放入您的账户，点击进行提现';
            } else {
                $money_comment = '有人注册了，奖励' . $data['money'] . '元已放入您的账户，点击进行提现';
            }

            $param = array (
                'template_id' => Yii::$app->params['channel_template_income'],
                'firstValue' => '您好，您有一笔收入到账！',
                'key1word' => "学生注册红包收入",
                'key2word' => $data['money'] . "元",
                'key3word' => date("Y-m-d H:s", time()),
                'remark' => $money_comment,
                'url' => '',
                'keyword_num' => 3
            );

            return Queue::produce(
                $this->buildMessage($param, $sale['bind_openid']),
                'template',
                'channel_template'
            );
        }
    }


    private function buildMessage($param, $openId = '')
    {

        if (!empty($param))
        {
            $data = $this->getTemplateData($param);

            $message = array (
                'touser' => $openId,
                'template_id' => $param['template_id'],
                'url' => $param['url'],
                'data' => $data
            );

            return $message;
        }else
        {
            ErrorService::AddStudentTemplateError('', '错误的type类型');
        }
    }

    private function getTemplateData($param)
    {
        switch ($param['keyword_num'])
        {
            case 1 :
                $data = array(
                    'first' => array('value' => $param['firstValue']),
                    'keyword1' => array('value' => $param['key1word']),
                    'remark' => array('value' => $param['remark'])
                );

                break;

            case 2 :
                $data = array(
                    'first' => array('value' => $param['firstValue']),
                    'keyword1' => array('value' => $param['key1word']),
                    'keyword2' => array('value' => $param['key2word']),
                    'remark' => array('value' => $param['remark'])
                );

                break;

            case 3 :
                $data = array(
                    'first' => array('value' => $param['firstValue']),
                    'keyword1' => array('value' => $param['key1word']),
                    'keyword2' => array('value' => $param['key2word']),
                    'keyword3' => array('value' => $param['key3word']),
                    'remark' => array('value' => $param['remark'])
                );

                break;

            case 4 :
                $data = array(
                    'first' => array('value' => $param['firstValue']),
                    'keyword1' => array('value' => $param['key1word']),
                    'keyword2' => array('value' => $param['key2word']),
                    'keyword3' => array('value' => $param['key3word']),
                    'keyword4' => array('value' => $param['key4word']),
                    'remark' => array('value' => $param['remark'])
                );

                break;
        }

        return $data;
    }

    public function sendTeacherClass($request)
    {
        $timeStart = strtotime($request['timeDay']);
        $timeEnd = $timeStart + 86400;

        $week = array(' 周日',' 周一',' 周二',' 周三',' 周四',' 周五',' 周六');
        $time = date('m-d', $timeStart). $week[date('w', $timeStart)];

        list($urlSend, $urlUser) = $this->sendTeacherPrepare();

        $wechatUserInfo = json_decode(Request::httpGet($urlUser), true);

        $teacherInfo = $this->RClassAccess->getNextDayClassTeacher($timeStart,$timeEnd);

        $classIdList = array();
        $failList = array();

        foreach($teacherInfo as $teacher) {
            $touser = 0;
            foreach($wechatUserInfo['userlist'] as $user) {
                if($teacher['mobile'] == $user['mobile']) {
                    $touser = $user['userid'];
                    break;
                }
            }
            $classInfo = $this->RClassAccess->getClassDayByTeacherId($teacher['teacher_id'],$timeStart,$timeEnd);
            $text = $teacher['nick'] . "老师您好，这是您(".$time.")的课表，请查收。\n\n";
            foreach($classInfo as $class) {
                $text .= "{$class['start']}-{$class['end']}\n{$class['student_name']} {$class['is_ex']}\n\n";
                $classIdList[] = $class['id'];
            }
            $text .= "此消息为系统自动发送，请勿回复";
            $content = array (
                'touser' => $touser,
                'msgtype' => 'text',
                'agentid' => Yii::$app->params['corp_id'],
                'text' => array('content' => $text)
            );
            $result = json_decode(Request::httpPost($urlSend,json_encode($content,JSON_UNESCAPED_UNICODE)),true);

            if($result['errcode'] != 0) {
                $failList[] = array('name' => $teacher['nick'], 'errmsg' => $result['errmsg']);
                continue;
            }
        }

//        LogService::OutputTeacherLog($this->logid, 'Send', '', '发送课程表');

        if (!empty($classIdList))
        {
            $this->WClassAccess->updateClassTimeSend($classIdList);
        }

        return json_encode(array('error' => '','data' => $failList));
    }

    private function sendTeacherPrepare() {
        $tokenInfo = json_decode(Request::httpGet("https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=".Yii::$app->params['corp_id']."&corpsecret=".Yii::$app->params['corp_secret']),true);

        if(isset($tokenInfo['errcode']) && $tokenInfo['errcode'] > 0) {
            die(json_encode(array('error' => $tokenInfo['errcode']), JSON_UNESCAPED_SLASHES));
        }

        $urlSend = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=".$tokenInfo['access_token'];
        $urlUser = "https://qyapi.weixin.qq.com/cgi-bin/user/list?access_token={$tokenInfo['access_token']}&department_id=".Yii::$app->params['department_teacher_id']."&status=0";
        return [$urlSend, $urlUser];
    }

    public function sendTeacherReward($open_id, $class_id)
    {
        $classInfo = ClassRoom::find()
            ->alias('c')
            ->select('c.time_class, c.time_end, u.nick')
            ->leftJoin('user AS u', 'u.id = c.student_id')
            ->where(['c.id' => $class_id])
            ->asArray()
            ->one();

        $param = array(
            'template_id' => Yii::$app->params['student_template_personal'],
            'firstValue' => '您好，您有新的消息！',
            'key1word' => 'VIP钢琴课程',
            'key2word' => date('m-d H:i', $classInfo['time_class']) . ' - ' . date('H:i', $classInfo['time_end']),
            'remark' => $classInfo['nick'] . "老师,这是您本次的课后陪练单,请查看并给予老师评价\n评价对我们很重要,感谢您的帮助,我们的服务才会越来越好\n点击查看课后单",
            'url' => Yii::$app->params['record_path'] . $request['record_id'],
            'keyword_num' => 2
        );

        return Queue::produce(
            $this->buildMessage($param, $open_id),
            'template',
            'channel_template'
        );
    }
    
    public function sendChannelTemplateAll($msg)
    {
        //头部数组
        $param_head = array(
            'template_id' => $msg['template_info']['template_id'],
            'firstValue' => $msg['template_info']['title'],
        );
        //中部数组
        $content_arr = json_decode($msg['template_info']['content']);
        foreach ($content_arr as $key => $v){
            $key = 'key' . ($key+1) . 'word';
            $param_head[$key] = $v;
        }

        //底部数组
        $param_head['remark'] = $msg['template_info']['remark'];
        $param_head['url'] = $msg['template_info']['url'];
        $param_head['keyword_num'] = $msg['template_info']['content_num'];

        $this->WChannelAccess->saveTemplatePushStatistic($msg);
        $wechat = Yii::$app->wechat_new;
        $wechat->sendTemplateMessage($this->buildMessage($param_head,$msg['touser']));
    }

    public function sendTeacherCancelClass($msg, $teacher_name, $open_id)
    {
        $param = array(
            'template_id' => Yii::$app->params['teacher_template_class_cancel'],
            'firstValue' => '亲爱的'.$teacher_name.',您有一节课临时取消了',
            'key1word' => $msg['instrument_name'],
            'key2word' => date('m月d日 H:i', $msg['time_class']).'-'.date('H:i', $msg['time_end']),
            'key3word' => (($msg['time_end']-$msg['time_class'])/60).'分钟',
            'key4word' => 'VIP陪练上课端',
            'remark' => '如有疑问，请咨询VIP陪练客服 021-61485688',
            'url' => '',
            'keyword_num' => 4
        );

        return Queue::produce(
            $this->buildMessage($param, $open_id),
            'template',
            'teacher_template'
        );
    }

    public function sendTeacherAddClass($time_class, $time_end, $teacher_name, $open_id)
    {
        $param = array(
            'template_id' => Yii::$app->params['teacher_template_class_edit'],
            'firstValue' => '亲爱的'.$teacher_name.',给您临时安排了一节课',
            'key1word' => '无',
            'key2word' => date('m月d日 H:i', $time_class).'-'.date('H:i', $time_end),
            'remark' => "请记得准时上课哦！如有疑问，请咨询客服\n021-61485688",
            'url' => '',
            'keyword_num' => 2
        );

        return Queue::produce(
            $this->buildMessage($param, $open_id),
            'template',
            'teacher_template'
        );
    }

    public function sendTeacherEditClass($time_class, $time_end, $time_class_new, $time_end_new, $teacher_name, $open_id)
    {
        $param = array(
            'template_id' => Yii::$app->params['teacher_template_class_edit'],
            'firstValue' => '亲爱的' . $teacher_name . ',您有一节课临时做了调整',
            'key1word' => date('m月d日 H:i', $time_class) . '-' . date('H:i', $time_end),
            'key2word' => date('m月d日 H:i', $time_class_new) . '-' . date('H:i', $time_end_new),
            'remark' => "请记得准时上课哦！如有疑问，请咨询客服\n021-61485688",
            'url' => '',
            'keyword_num' => 2
        );

        return Queue::produce(
            $this->buildMessage($param, $open_id),
            'template',
            'teacher_template'
        );
    }

    /**
     * 獲取一些定制化的时间
     * @param
     * @return  array
     * create by  wangkai
     * create time  2017/5/11
     */
    private function getThatDayInit($time_class)
    {
        // 初始化关于时间的问题
        $time = date('H:i',$time_class);
        $timeFormat = explode(':', $time);
        $index = 2*$timeFormat[0] + ($timeFormat[1] < 30 ? 0 : 1);

        $thatDayInit[0] = time();
        $thatDayInit[1] = strtotime('0:00');
        $thatDayInit[2] = strtotime('22:00');
        $thatDayInit[3] = strtotime('23:59');
        $thatDayInit[4] = date('N', $time_class);
        $thatDayInit[5] = pow(2, $index);
        $thatDayInit[6] =  $thatDayInit[3] + 86400;

        return $thatDayInit;
    }

    public function sendCurriculumAddToTeacher($data)
    {
        // 初始化关于时间的问题
        $thatDayInit = $this->getThatDayInit($data['time_class']);

        // 获取学生姓名
        $student = $this->RStudentAccess->getUserRowById($data['student_id']);

        // 获取老师信息
        $teacher = $this->RTeacherAccess->getTeacherTypeOpenidById($data['teacher_id']);
        $isWorkTeacher =  $this->RTeacherAccess->isWorkTeacher($data['teacher_id'], $thatDayInit[4],  $thatDayInit[5]);
        // 获取乐器 乐器类型 1:钢琴 2小提琴  3手风琴 4古筝
        switch ($data['instrument_name']) {
            case 1:
                $instrument_name = '钢琴';
                break;
            case 2:
                $instrument_name = '小提琴';
                break;
            case 3:
                $instrument_name = '手风琴';
                break;
            case 4:
                $instrument_name = '古筝';
                break;
            default:
                $instrument_name = '未知';
                break;
        }

        //这节课属于当天就发
        if ( $thatDayInit[1] <= $data['time_class'] && $data['time_class'] <= $thatDayInit[3] )
        {
            $timeShow = '今天';

        } elseif ( $thatDayInit[0] > $thatDayInit[2] && $thatDayInit[0] <= $thatDayInit[3] && $data['time_class'] <= $thatDayInit[6] ) {

            // 条件2 10点之后去拍明天的课程要发布
            $timeShow = '明天';
        } else {
            return  0;
        }

        $param = array(
            'template_id' => Yii::$app->params['teacher_template_curriculum_add'],
            'firstValue' => $teacher['nick'] . '老师，' . $timeShow . '您有一节新增课。',
            'key1word' => '1节',
            'key2word' => "\n" . date('m月d日 H:i',$data['time_class']) . '-'
                . date('H:i',$data['time_end']) . ' ' .$student['nick']
                . '[' .$instrument_name . ']',
            'remark' => '上课前，请注意网络环境、设备电量！如有紧急情况，请及时留言。',
            'url' => '',
            'keyword_num' => 2
        );

        // 是否是工作時間外
        if ( ($isWorkTeacher &  $thatDayInit[5]) != 0 )
        {
            $param['firstValue'] .= "请提前安排好时间哦！";
        }

        return Queue::produce(
            $this->buildMessage($param, $teacher['open_id']),
            'template',
            'teacher_template'
        );
    }

    public function sendCurriculumModificationToTeacher($data)
    {

        // 初始化关于时间的问题
        $time = date('H:i',$data['time_class_new']);
        $timeFormat = explode(':', $time);
        $index = 2*$timeFormat[0] + ($timeFormat[1] < 30 ? 0 : 1);

        $thatDayInit[0] = strtotime('0:00');
        $thatDayInit[1] = strtotime('23:59');
        $thatDayInit[2] = date('N', $data['time_class_new']);
        $thatDayInit[3] = pow(2,$index);

        $time2 = date('H:i',$data['time_class_old']);
        $timeFormat2 = explode(':', $time2);
        $index2 = 2*$timeFormat2[0] + ($timeFormat2[1] < 30 ? 0 : 1);
        $thatDayInit[4] = date('N', $data['time_class_old']);
        $thatDayInit[5] = pow(2,$index2);
        $thatDayInit[6] = $thatDayInit[1] + 86400;
        $thatDayInit[7] =  strtotime('22:00');

        // 获取学生姓名
        $student = $this->RStudentAccess->getUserRowById($data['student_id']);

        // 原来的老师信息
        $oldTeacher = $this->RTeacherAccess->getTeacherTypeOpenidById($data['teacher_id_old']);
        $isWorkOldTeacher =  $this->RTeacherAccess->isWorkTeacher($data['teacher_id_old'], $thatDayInit[4],  $thatDayInit[5]);

        // 获取乐器 乐器类型 1:钢琴 2小提琴  3手风琴 4古筝
        switch ($data['instrument_name_new']) {
            case 1:
                $instrument_name = '钢琴';
                break;
            case 2:
                $instrument_name = '小提琴';
                break;
            case 3:
                $instrument_name = '手风琴';
                break;
            case 4:
                $instrument_name = '古筝';
                break;
            default:
                $instrument_name = '未知';
                break;
        }

        $cancelReason = array(
            0 => '孩子临近考试',
            1 => '孩子身体不适',
        );
        $key = array_rand($cancelReason, 1);

        // 是否更换老师的条件
        if ( $data['teacher_id_new'] != $data['teacher_id_old']) {


            // 默认条件 取消当天并且工作时间外
            $oldParam =  array(
                'template_id' => Yii::$app->params['teacher_template_curriculum_cancel'],
                'firstValue' =>  $oldTeacher['nick'] . '老师，很抱歉，您有一节课被取消了',
                'key1word' => date('m月d日 H:i',$data['time_class_old']) . ' -'
                    . date('H:i',$data['time_end_old']) . ' ' .$student['nick']
                    . '[' . $data['instrument_name_old'] . ']',
                'key2word' => $cancelReason[$key],
                'remark' => '感谢老师辛勤的付出和等待。',
                'url' => '',
                'keyword_num' => 2
            );

            // 取消当天课必须发
            if (($thatDayInit[0] <= $data['time_class_old'] && $data['time_class_old'] <= $thatDayInit[1]) )
            {
                //  取消当天并且工作时间内
                if ( ($isWorkOldTeacher &  $thatDayInit[5]) == 0 )
                {
                    // 条件1  取消当天并且在工作时间内 而且  这节课正在被上课
                    if ($data['time_class_old'] <= time() && time() <= $data['time_end_old'] ) {
                        $oldParam['firstValue'] = $oldTeacher['nick'] . "老师，刚刚您有一节课被取消了，实属无奈，我们深感抱歉";
                        $oldParam['remark'] = '非常感谢老师这次的谅解和辛勤的付出。';
                        // 插入队列
                        $templateList[] = $this->buildMessage($oldParam, $oldTeacher['open_id']);
                    } else {
                        // 条件2  取消当天并且在工作时间内 不过这节课没有在上课时间
                        $oldParam['firstValue'] = $oldTeacher['nick'] . "老师，很抱歉，今天您有一节课被取消了";
                        $oldParam['remark'] = '感谢老师辛勤的付出和等待，请稍作休息，若有临时课程，请留意通知。';
                        // 插入队列
                        $templateList[] = $this->buildMessage($oldParam, $oldTeacher['open_id']);
                    }

                } else {
                    // 条件3 取消当天并且在工作时间外
                    $oldParam['firstValue'] = $oldTeacher['nick'] . "老师，非常抱歉，今天您有一节课被临时取消了";
                    $oldParam['remark'] = "感谢老师的谅解和等待。";
                    // 插入队列
                    $templateList[] = $this->buildMessage($oldParam, $oldTeacher['open_id']);
                }

            } elseif ( time() <= $thatDayInit[1] && time() >= $thatDayInit[7] &&
                ($data['time_class_old'] <= $thatDayInit[6] &&  $data['time_class_old'] >=  $thatDayInit[1])
            ) {
                // 超过10点明天发布名堂的课程
                // 条件4 取消非当天并且在工作时间内
                if (  ($isWorkOldTeacher &  $thatDayInit[5]) == 0 ) {
                    $oldParam['firstValue'] = $oldTeacher['nick'] . "老师，您有一节课被取消了";
                    $oldParam['remark'] = "感谢老师辛勤的付出和等待，若有其他临时课程安排，请留意通知和课表更新。";
                }
                // 插入队列
                $templateList[] = $this->buildMessage($oldParam, $oldTeacher['open_id']);
            }

            // 获取新老师的信息
            $newTeacher = $this->RTeacherAccess->getTeacherTypeOpenidById($data['teacher_id_new']);
            $isWorkNewTeacher = $this->RTeacherAccess->isWorkTeacher($data['teacher_id_new'], $thatDayInit[2],  $thatDayInit[3]);
            if ( $data['time_class_new'] >= $thatDayInit[0] && $data['time_class_new'] <= $thatDayInit[1] )
            {
                $timeShow = '今天';
            } elseif ( $data['time_class_new'] >  $thatDayInit[1] && $data['time_class_new']  <= $thatDayInit[1] + 86400 ) {
                $timeShow = '明天';
            } else {
                $timeShow = date('m月d日 H:i',$data['time_class_new']);
            }

            $newParam = array(
                'template_id' => Yii::$app->params['teacher_template_curriculum_add'],
                'firstValue' => $newTeacher['nick'] . '老师，'.$timeShow.'您有一节新增课。',
                'key1word' => '1节',
                'key2word' => "\n" . date('m月d日 H:i',$data['time_class_new']) . '-'
                    . date('H:i',$data['time_end_new']) . ' ' .$student['nick']
                    . '[' .$instrument_name . ']',
                'remark' => '上课前，请注意网络环境、设备电量！如有紧急情况，请及时留言。',
                'url' => '',
                'keyword_num' => 2
            );

            if ( ($isWorkNewTeacher &  $thatDayInit[3]) != 0)
            {
                $newParam['firstValue'] .= "请提前安排好时间哦！";
            }


            $templateList[] = $this->buildMessage($newParam, $newTeacher['open_id']);
            return Queue::batchProduce(
                $templateList,
                'template',
                'teacher_template'
            );


        } else {
            // 调课涉及到当天
            if (($data['time_class_old'] != $data['time_class_new']) && (($thatDayInit[0] <= $data['time_class_old'] && $data['time_class_old'] <= $thatDayInit[1])
                    || ($thatDayInit[0] <= $data['time_class_new'] && $data['time_class_new'] <= $thatDayInit[1]))
            ) {
                {
                    // 如果是更换课程信息不更换老师
                    $param = array(
                        'template_id' => Yii::$app->params['teacher_template_curriculum_modification'],
                        'firstValue' => $oldTeacher['nick'] . '老师，' . date('m月d日', $data['time_class_old']) . '您有一节' . date('H:i', $data['time_class_old']) . '-'
                            . date('H:i', $data['time_end_old']) . '的课调整了时间。',
                        'key1word' => $data['instrument_name_old'],
                        'key2word' => '学生临时有事',
                        'key3word' => date('m月d日', $data['time_class_new']) . date('H:i', $data['time_class_new']) . ' ' . $student['nick']
                            . '[' . $instrument_name . ']',
                        'remark' => '请注意更新的上课时间，课前检查网络环境、设备电量！如有疑问，请留言。',
                        'url' => '',
                        'keyword_num' => 3
                    );

                    return Queue::produce(
                        $this->buildMessage($param, $oldTeacher['open_id']),
                        'template',
                        'teacher_template'
                    );
                }
            }
        }
    }

    public function sendCurriculumCancelToTeacher($data)
    {
        // 初始化关于时间的问题
        $thatDayInit = $this->getThatDayInit($data['time_class']);

        // 获取学生姓名
        $student = $this->RStudentAccess->getUserRowById($data['student_id']);

        // 获取老师信息
        $teacher = $this->RTeacherAccess->getTeacherTypeOpenidById($data['teacher_id']);
        $isWorkTeacher =  $this->RTeacherAccess->isWorkTeacher($data['teacher_id'], $thatDayInit[4],  $thatDayInit[5]);

        // 取消原因随机
        $cancelReason = array(
            0 => '孩子临近考试',
            1 => '孩子身体不适',
        );
        $key = array_rand($cancelReason, 1);

        // 获取乐器 乐器类型 1:钢琴 2小提琴  3手风琴 4古筝
        switch ($data['instrument_name']) {
            case 1:
                $instrument_name = '钢琴';
                break;
            case 2:
                $instrument_name = '小提琴';
                break;
            case 3:
                $instrument_name = '手风琴';
                break;
            case 4:
                $instrument_name = '古筝';
                break;
            default:
                $instrument_name = '未知';
                break;
        }

        // 默认条件 取消当天并且工作时间外
        $param =  array(
            'template_id' => Yii::$app->params['teacher_template_curriculum_cancel'],
            'firstValue' =>  $teacher['nick'] . '老师，很抱歉，您有一节课被取消了',
            'key1word' => date('m月d日 H:i',$data['time_class']) . ' -'
                . date('H:i',$data['time_end']) . ' ' .$student['nick']
                . '[' . $instrument_name . ']',
            'key2word' => $cancelReason[$key],
            'remark' => '感谢老师辛勤的付出和等待。',
            'url' => '',
            'keyword_num' => 2
        );

        // 取消当天课必须发
        if ($thatDayInit[1] <= $data['time_class'] && $data['time_class'] <= $thatDayInit[3])
        {
            //  取消当天并且工作时间内
            if ( ($isWorkTeacher &  $thatDayInit[5]) == 0 )
            {
                // 条件1  取消当天并且在工作时间内 而且  这节课正在被上课
                if ($data['time_class'] <= $thatDayInit[0] && $thatDayInit[0] <= $data['time_end'] ) {
                    $param['firstValue'] = $teacher['nick'] . "老师，刚刚您有一节课被取消了，实属无奈，我们深感抱歉";
                    $param['remark'] = '非常感谢老师这次的谅解和辛勤的付出。';
                } else {
                    // 条件2  取消当天并且在工作时间内 不过这节课没有在上课时间
                    $param['firstValue'] = $teacher['nick'] . "老师，很抱歉，今天您有一节课被取消了";
                    $param['remark'] = '感谢老师辛勤的付出和等待，请稍作休息，若有临时课程，请留意通知。';
                }

            } else {
                // 条件3 取消当天并且在工作时间外
                $param['firstValue'] = $teacher['nick'] . "老师，非常抱歉，今天您有一节课被临时取消了";
                $param['remark'] = "感谢老师的谅解和等待。";
            }

        } elseif ( $thatDayInit[0] > $thatDayInit[2] && $thatDayInit[0] <= $thatDayInit[3] && $data['time_class'] <= $thatDayInit[6] && $data['time_class'] > $thatDayInit[0] ) {
            // 超过10点明天发布名堂的课程
            // 条件4 取消非当天并且在工作时间内
            if (  ($isWorkTeacher &  $thatDayInit[5]) == 0 ) {
                $param['firstValue'] = $teacher['nick'] . "老师，您有一节课被取消了";
                $param['remark'] = "感谢老师辛勤的付出和等待，若有其他临时课程安排，请留意通知和课表更新。";
            }
        } else {
            return  0;
        }

        return Queue::produce(
            $this->buildMessage($param, $teacher['open_id']),
            'template',
            'teacher_template'
        );
    }

    /**
     * @param string
     * @return array
     * @created by YH
     * 老师薪资明细推送
     */
    public function sendTeacherSalaryDetail($month)
    {
        $time_start = strtotime(date('Y-m-01', strtotime($month)));
        $time_end = strtotime(date('Y-m-01', strtotime('+1 month', $time_start)));
//
//        // 所有有薪资信息的校招老师的openid
//        $sql = " SELECT teacher_id, ut.nick, ut.open_id, SUM(class_money) AS total "
//            . " FROM teacher_class_money AS tcm "
//            . " LEFT JOIN user_teacher AS ut ON ut.id = tcm.teacher_id "
//            . " WHERE ut.teacher_type = 2 AND ut.is_disabled = 0 AND ut.open_id <> '' AND time_class >= :time_start AND time_class < :time_end "
//            . " GROUP BY tcm.teacher_id ";
//
//        $teacher_info = Yii::$app->db->createCommand($sql)
//            ->bindValues([
//                ':time_start' => $time_start,
//                ':time_end' => $time_end
//            ])
//            ->queryAll();

        $teacher_info = $this->RTeacherAccess->getTeacherClassMoneyList($time_start,$time_end);

        $url = Yii::$app->params['teacher_zh_domain'] . 'salary/salary-detail?time=' . $time_end;
        $error = 0;

        if (!empty($teacher_info))
        {

            $templateList = [];

            foreach ($teacher_info as $teacher)
            {

                $first_value = "亲爱的" . $teacher['nick'] . "，本月薪资到啦";

                $param = [
                    'template_id' => Yii::$app->params['teacher_template_salary_confirm'],
                    'firstValue' => $first_value,
                    'key1word' => date('Y', $time_start) . '年' . date('m', $time_start) . '月',
                    'key2word' => $teacher['total'] . '元',
                    'remark' => "点击查看薪资详情",
                    'url' => $url . '&openid=' . $teacher['open_id'],
                    'keyword_num' => 2
                ];
                // var_dump($param);
                $each = Message::buildMessage($param, $teacher['open_id']);

                $templateList[] = $each;

            }
            //var_dump($templateList);die;
            $ret = Queue::batchProduce($templateList, 'template', 'teacher_template');

            if (!$ret)
            {
                $error = 'Produce error.';
            }
        }

        // var_dump($teacher_info);
        return array('error' => $error, 'data' => '');
    }

    /**
     * @param string
     * @return array
     * @created by YH
     * 老师薪资明细推送
     */
    public function sendTeacherSalaryDetailById($month,$push_ids)
    {
        $timeStart = strtotime($month);
        $timeEnd = strtotime('+1 month', $timeStart);
        $count = count($push_ids);

        $url = Yii::$app->params['teacher_zh_domain'] . 'salary/salary-detail?time=' . $timeStart;

        $error = 0;
        $templateList = [];

        for ($i = 0; $i < $count; $i++)
        {
            $teacher_id = $push_ids[$i];
            $teacher = $this->RTeacherAccess->getTeacherTypeOpenidById($teacher_id);

            $teacher_type = $teacher['teacher_type'];
            if ($teacher_type == 1) {
                $salary = $this->RBasepayAccess->getTeacherMonthSalary($teacher_id, $timeStart, $timeEnd)['salary'];
                $class_commission = $this->RWorkhourAccess->getClassCommission($teacher_id, $timeStart, $timeEnd)['class_commission'];
                $reward = $this->RRewardAccess->getTeacherRewardPunishment($teacher_id, $timeStart, $timeEnd)['salary_reward'];
                $punishment = $this->RRewardAccess->getTeacherRewardPunishment($teacher_id, $timeStart, $timeEnd)['salary_punish'];
                $total_salary = $salary + $class_commission + $reward - $punishment;
                $new_total = number_format($total_salary, 2, '.', '');

                $first_value = "亲爱的" . $teacher['nick'] . "，本月薪资到啦";

                $param = [
                    'template_id' => Yii::$app->params['teacher_template_salary_confirm'],
                    'firstValue' => $first_value,
                    'key1word' => date('Y', $timeStart) . '年' . date('m', $timeStart) . '月',
                    'key2word' => $new_total . '元',
                    'remark' => "点击查看薪资详情",
                    'url' => $url . '&openid=' . $teacher['open_id'],
                    'keyword_num' => 2
                ];
                // var_dump($param);
                $each = Message::buildMessage($param, $teacher['open_id']);

                $this->WTeacherAccess->editTeacherPush($timeStart,$timeEnd,$teacher_id);

                $templateList[] = $each;
            } else {
                $teacher_info = $this->RTeacherAccess->getTeacherClassMoneyById($timeStart, $timeEnd, $teacher_id);

                $first_value = "亲爱的" . $teacher['nick'] . "，本月薪资到啦";

                $param = [
                    'template_id' => Yii::$app->params['teacher_template_salary_confirm'],
                    'firstValue' => $first_value,
                    'key1word' => date('Y', $timeStart) . '年' . date('m', $timeStart) . '月',
                    'key2word' => $teacher_info['total'] . '元',
                    'remark' => "点击查看薪资详情",
                    'url' => $url . '&openid=' . $teacher['open_id'],
                    'keyword_num' => 2
                ];
                // var_dump($param);
                $each = Message::buildMessage($param, $teacher['open_id']);

                $this->WTeacherAccess->editTeacherPush($timeStart,$timeEnd,$teacher_id);

                $templateList[] = $each;
            }
        }
        $ret = Queue::batchProduce($templateList, 'template', 'teacher_template');
        if (!$ret) {
            $error = 'Produce error.';
        }
        return array('error' => $error, 'data' => '');
    }

    public function sendStudentSubscribeTemplate($open_id)
    {
        $userInfo = $this->RStudentAccess->getUserSaleidAndNameByOpenid($open_id);

        if (!empty($userInfo['sales_id']))
        {

            $bind_openid = $this->RChannelAccess->getSalesChannelOpenidById($userInfo['sales_id']);

            if ( !empty($bind_openid) )
            {
                $param = array (
                    'template_id' => Yii::$app->params['channel_template_todo'],
                    'firstValue' => '您好，您邀请新的用户加入！',
                    'key1word' => "邀请好友关注VIP陪练",
                    'key2word' => "您邀请的用户（".$userInfo['name']."）已关注VIP陪练。TA完成体验课您将可以获得10-12元奖励。",
                    'key3word' => date('Y年m月d日 H:i', time()),
                    'remark' => '',
                    'url' => '',
                    'keyword_num' => 3
                );

                return Queue::produce(
                    $this->buildMessage($param, $bind_openid),
                    'template',
                    'channel_template'
                );
            }
        }
    }

    public function sendAttendExClassTemplate($uid, $class_time)
    {

        $userInfo = $this->RStudentAccess->getUserSaleidAndNick($uid);
        if (!empty($userInfo))
        {
            if (!empty($userInfo['sales_id']))
            {
                $bind_openid = $this->RChannelAccess->getSalesChannelOpenidById($userInfo['sales_id']);

                if ( !empty($bind_openid) )
                {
                    $param = array (
                        'template_id' => Yii::$app->params['channel_template_todo'],
                        'firstValue' => '您好，你分享的海报有琴童预约体验课！',
                        'key1word' => "预约体验课通知",
                        'key2word' => "完成体验课可获得体验课奖励",
                        'key3word' => date('Y年m月d日 H:i', $class_time),
                        'remark' => "您邀请的用户（".$userInfo['name']."）已预约VIP陪练体验课。体验完成您将可以获得10-12元奖励。",
                        'url' => '',
                        'keyword_num' => 3
                    );
                    return Queue::produce(
                        $this->buildMessage($param, $bind_openid),
                        'template',
                        'channel_template'
                    );
                }
            }
        }
    }

    public function dealChannelGiveTemplate($data)
    {
        $openId = $this->RStudentAccess->getStudentOpenId($data['data']['studentid']);
        $userInfo = $this->RStudentAccess->getUserSaleidAndNick($data['data']['student_id_re']);
        //获取奖励的乐器名称
        $instrument_rs = $this->RClassAccess->getInstrumentById($data['data']['instrumentid']);
        //print_r($instrument_rs);exit;
        //print_r($userInfo);exit;
        //$class_time = '';
//        switch ($data['data']['class_type']) {
//            case 1 :
//                $class_time =  '25分钟';
//            case 2 :
//                $class_time =  '45分钟';
//            case 3 :
//                $class_time =  '50分钟';
//        }
        $amount = $data['data']['amount'];
        $channelcount = $data['data']['channelcount'];

        if (!empty($openId))
        {
            $key2word = '完成购买：感谢您的强力推荐，您推荐的客户（'.$userInfo['nick'].'），已购买套餐，这是您推荐的第'.$channelcount.'位家长，赠送您'.$amount.'节课，系统已为您自动充值，请注意查收，如有问题，欢迎您随时联系您的专属顾问';
            $param = array(
                'template_id' => Yii::$app->params['student_template_personal'],
                'firstValue' => '您好，您有新的消息！',
                'key1word' => 'VIP'.$instrument_rs['name'].'课程',
                'key2word' => date('Y-m-d H:i', time()),
                'key3word' => $key2word,
                'remark' => "",
                'url' => '',
                'keyword_num' => 3
            );

            $message = Message::buildMessage($param, $openId);

            Queue::produce($message, 'template', 'student_template');
        }else {
            return false;
        }
    }

    public function dealChannelGiveRedpack($msg) {
        $openId = $this->RChannelAccess->getChannelBindOpenid($msg['studentid']);
        $userInfo = $this->RStudentAccess->getUserSaleidAndNick($msg['studentid']);

        if (!empty($openId))
        {
            $key2word = '完成体验：感谢您的强力推荐，您邀约的用户（'.$userInfo['nick'].'），已完成体验，18元鼓励金已发送到您的账户，请联系您的专属顾问领取';
            $param = array(
                'template_id' => Yii::$app->params['student_template_personal'],
                'firstValue' => '您好，您有新的消息！',
                'key1word' => 'VIP钢琴课程',
                'key2word' => $key2word,
                'remark' => "",
                'url' => Yii::$app->params['sales_url'],
                'keyword_num' => 2
            );

            $message = Message::buildMessage($param, $openId);

            Queue::produce($message, 'template', 'student_template');
        }else {
            return false;
        }
    }

    public function sendGiveClassMessage($data)
    {
        $openId = $this->RChannelAccess->getChannelBindOpenid($data['data']['studentid']);
        switch ($data['data']['class_type']) {
            case 1:
                $class_time = '25分钟';
                break;
            case 2:
                $class_time = '45分钟';
                break;
            case 3:
                $class_time = '50分钟';
                break;

        }
        $amount = $data['data']['amount'];

        $nick = $this->RStudentAccess->getNickByOpenId($openId);

        $message = $nick."家长，这是本次赠送给您的课程：\n"
            .'课程时间：'.$class_time."\n"
            .'课程数量：'.$amount."节\n\n"
            ."<a href='".Yii::$app->params['myclass_url']."'>点击查看剩余课时</a>";

        $data = array(
            'open_id' => $openId,
            'message' => $message
        );

        return Queue::produce(
            $this->buildMessage($message, $openId),
            'async',
            'kefu_msg'
        );
    }
}