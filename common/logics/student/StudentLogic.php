<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/19
 * Time: 下午2:34
 */
namespace common\logics\student;

use common\services\LogService;
use common\widgets\Json;
use Yii;
use yii\base\Object;
use yii\db\Exception;
use yii\db\Query;
use callmez\wechat\sdk;
use common\widgets\PhpExcel;

class StudentLogic extends Object implements IStudent
{
    /** @var  \common\sources\read\student\StudentAccess  $RStudentAccess */
    private $RStudentAccess;
    /** @var  \common\sources\write\student\StudentAccess  $WStudentAccess */
    private $WStudentAccess;
    /** @var  \common\sources\read\channel\ChannelAccess  $RChannelAccess */
    private $RChannelAccess;
    /** @var  \common\sources\write\channel\ChannelAccess  $WChannelAccess */
    private $WChannelAccess;
    /** @var  \common\sources\read\chat\ChatAccess  $RChatAccess */
    private $RChatAccess;
    /** @var  \common\sources\write\chat\ChatAccess  $RChatAccess */
    private $WChatAccess;
    /** @var  \common\sources\read\account\AccountAccess  $RAccountAccess */
    private $RAccountAccess;
    /** @var  \common\sources\read\teacher\TeacherAccess  $RTeacherAccess */
    private $RTeacherAccess;
    /** @var  \common\logics\classes\ClassesLogic  $RTeacherAccess */
    private $classesService;
    /** @var  \common\sources\read\classes\ClassAccess  $RClassAccess */
    private $RClassAccess;
    /** @var  \common\sources\read\visit\VisitAccess  $RVisitAccess*/
    private $RVisitAccess;
    /** @var  \common\sources\read\order\OrderAccess  $ROrderAccess*/
    private $ROrderAccess;


    public function init()
    {
        $this->RStudentAccess = Yii::$container->get('RStudentAccess');
        $this->WStudentAccess = Yii::$container->get('WStudentAccess');
        $this->RChannelAccess = Yii::$container->get('RChannelAccess');
        $this->WChannelAccess = Yii::$container->get('WChannelAccess');
        $this->RChatAccess = Yii::$container->get('RChatAccess');
        $this->WChatAccess = Yii::$container->get('WChatAccess');
        $this->RAccountAccess = Yii::$container->get('RAccountAccess');
        $this->RTeacherAccess = Yii::$container->get('RTeacherAccess');
        $this->classesService = Yii::$container->get('classesService');
        $this->RClassAccess = Yii::$container->get('RClassAccess');
        $this->RVisitAccess = Yii::$container->get('RVisitAccess');
        $this->ROrderAccess = Yii::$container->get('ROrderAccess');
        parent::init();
    }

    public function getChannelListByKeyword($keyword)
    {
        $salesChannelList = $this
            ->RChannelAccess
            ->getSalesChannelList($keyword);
        
        $studentChannelList = $this
            ->RChannelAccess
            ->getStudentChannelList($keyword);
        
        $list = [];
        $each = [];
        
        if (!empty($salesChannelList))
        {
            foreach ($salesChannelList as $sale)
            {
                $each['id'] = '1_' . $sale['id'];
                $each['name'] = '[主课] ' 
                    . $sale['nickname'] 
                    . ' (' . $sale['username'] .')';
                $list[] = $each;
                $each = [];
            }
        }
        
        if (!empty($studentChannelList))
        {
            foreach ($studentChannelList as $student)
            {
                $each['id'] = '2_' . $student['channel_id_self'];
                $each['name'] = '[家长] '
                    . $student['nick']
                    . ' (' . $student['mobile'] .')';
                $list[] = $each;
                $each = [];
            }
        }
        
        return $list;
    }

    public function bindChannel($logid, $channelId, $openId)
    {
        $wechatAccRow = $this->RStudentAccess->getWechatRowByOpenId($openId);
        $user_info = $this->RStudentAccess->getUserSaleidAndNick($wechatAccRow['uid']);

        if (!empty($wechatAccRow))
        {
            $info = explode('_', $channelId);

            $transaction = Yii::$app->db->beginTransaction();
            
            try {

                if (empty($channelId))
                {
                    if ( !empty($user_info['sales_id']) )
                    {
                        $this->WChannelAccess->doChangeChannelIsNull($wechatAccRow['uid'], $user_info['sales_id']);
                    }

                    $this->WStudentAccess->updateStudentChannelId(0, $wechatAccRow['uid']);
                }

                // 渠道sale_id
                if ($info[0] == 1) 
                {

                    if ( !empty($user_info['sales_id']) )
                    {
                        $this->WChannelAccess->doChangeChannel($info['1'], $wechatAccRow['uid'], $user_info['sales_id']);

                    } else {

                        $order_list = $this->ROrderAccess->getProductOrderFree($wechatAccRow['uid']);

                        if (!empty($order_list))
                        {
                            foreach ($order_list as $v)
                            {
                                $this->WChannelAccess->addBuyOrderTrade($info[1], $wechatAccRow['uid'], $user_info['nick'], $v * 0.08);
                            }

                        }
                    }

                    $this->WStudentAccess->updateStudentSalesId($info[1],$wechatAccRow['uid']);

                    $this->WStudentAccess->updateWechatSalesId($openId,$info[1]);

                } elseif ($info[0] == 2) {

                    if ( !empty($user_info['sales_id']) )
                    {
                        $this->WChannelAccess->doChangeChannelIsNull($wechatAccRow['uid'], $user_info['sales_id']);
                    }

                    $this->WStudentAccess->updateStudentChannelId($info[1], $wechatAccRow['uid']);
                }
                
                $transaction->commit();

                LogService::OutputLog($logid, 'update', '', '更改渠道');
                
                return Json::dieJson(true);
                
            }catch (\yii\base\Exception $e) {
                $transaction->rollBack();
                return Json::dieJson(array('error' => '绑定渠道失败'));
            }
        }else {
            return Json::dieJson(array('error' => '未注册用户'));
        }
    }

    public function dealStudentSubscribe($xml)
    {
        //$wechat_class_flag = 0;
        $wechat = Yii::$app->wechat;
        $userInfo = $wechat->getUserInfo($xml['FromUserName']);



        if ($userInfo)
        {
            $count = $this->RStudentAccess
                ->countUserInitByOpenid($xml['FromUserName']);

            if (empty($count))
            {
                $channelId = 0;
                $salesId = 0;

                if (isset($xml['EventKey'])
                    && !is_array($xml['EventKey'])
                    && !empty($xml['EventKey']))
                {
                    $keys = explode('_', $xml['EventKey']);

                    if ($keys[0] == 'qrscene' && !isset($keys[2]))
                    {
                        $id = $keys[1];
                        $channelId = 0;
                        $classId = 0;

                        if ($id > 2147483648)
                        {
                            //微课分享id
                            $share_id = $id - 2147483648;
                            $shareInfo = $this->RStudentAccess
                                ->getChannelIdByWechatClassId($share_id);

                            if (!empty($shareInfo))
                            {
                                $channelId = empty($shareInfo['channel_id_self']) ? 0 : $shareInfo['channel_id_self'];
                                $classId = $shareInfo['class_id'];
                            }
                        }else{
                            //换课渠道ID
                            $channelId = $keys[1];
                        }
                        $this->WStudentAccess
                            ->addUserInitFromChannelClass($userInfo, $channelId, $classId);


                    } elseif (isset($keys[1]) && $keys[1] == 'sales')
                    {

                        $salesId = $keys[2];

                        $this->WStudentAccess
                            ->addUserInitFromSale($userInfo, $salesId);
                        $this->WChannelAccess
                            ->addSalesChannelScan($userInfo['openid'], $salesId);

                    } else
                    {

                        $channelId = $keys[2];
                        $this->WStudentAccess
                            ->addUserInitFromChannel($userInfo, $channelId);
                    }
                } else
                {
                    $this->WStudentAccess
                        ->addUserInitFromSelf($userInfo);
                }



                $countLink = $this->RChatAccess
                    ->countLinkByOpenid($xml['FromUserName']);

                if (empty($countLink))
                {
                    $countWait = $this->RChatAccess
                        ->countStudentWaitByOpenid($xml['FromUserName']);

                    if (empty($countWait))
                    {
                        $this->WChatAccess
                            ->addChatWait($xml['FromUserName'], 1);
                    }
                }

                $this->WChatAccess
                    ->addChatMessagePre($xml['FromUserName'], '你好,我刚刚关注了公众号!', 1);
            }
        }

        $this->WStudentAccess->addUserAttention($userInfo['openid']);


        return ['error' => 0,'data' => ['open_id' => $xml['FromUserName']]];
    }

    public function dealStudentScan($xml)
    {
        if (isset($xml['EventKey'])
            && !is_array($xml['EventKey'])
            && !empty($xml['EventKey']))
        {
            $keys = explode('_', $xml['EventKey']);

            if ($keys[0] == 'sales')
            {
                $userInfo = $this->RStudentAccess
                    ->getUserInitInfoByOpenid($xml['FromUserName']);

                if (empty($userInfo->sales_id) && !empty($us))
                {
                    $this->WStudentAccess
                        ->updateUserInitSaleId($userInfo->id, $keys[1]);
                }

                $this->WChannelAccess
                    ->addSalesChannelScan($xml['FromUserName'], $keys[1]);
            }
        }

        return true;
    }

    public function countAllPurchasePage($keyword, $type)
    {
        return $this->RStudentAccess->countAllPurchasePage($keyword, $type);
    }


    public function getTodoPurchaseList($keyword,$timeDay)
    {
        if(empty($timeDay)){
            $start = strtotime('-1 month');
            $end   = time();
        }else{
            $start = $timeDay;
            $end   = $start + 86400;
        }
        $list = $this->RStudentAccess->getTodoPurchaseList($keyword,$start,$end);

        return $list;
    }


    public function getNoClassPurchasePage($type)
    {   
        list($key,$value) = $this->getNoClassPurchaseType($type);
        return $this->RStudentAccess->getNoClassPurchasePage($key,$value);

    }

    public function getNoClassPurchaseType($type)
    {
        switch ($type) {
            case '1':
                $key = '';
                $value = '';
                break;
            case '2':
                // $key =  ' AND u.purchase = :purchase';
                $key =  ' AND p.purchase = 1';
                $value = 1;
                break;
            case '3':
                $key = ' AND r.no_week IS NULL';
                $value = '';
                break;
            default:
                
                break;
        }

        return [$key,$value];

    }

    public function getNoClassPurchaseList($type,$num)
    {
        list($key,$value) = $this->getNoClassPurchaseType($type);
        
        $list = $this->RStudentAccess->getNoClassPurchaseList($num, $key, $value);
        foreach ($list as &$v) {
            $v['introduce'] = empty($v['introduce'])?'0':$v['introduce'];
            $v['max_class'] = empty($v['max_class'])?'该用户没有上过课程':date('Y-m-d H:i',$v['max_class']);
            $v['content'] = $this->RStudentAccess->getMaxVisitHistory($v['id']);
            $v['content'] = empty($v['content'])?'无回访记录':$v['content'];
        }
        return $list;
    }
    
    public function getRebuyPurchasePage($type, $number)
    {
        list($amount,$value) = $this->getRebuyPurchaseType($type,$number);

        return $this->RStudentAccess->getRebuyPurchasePage($amount,$value); 
    }


    public function getRebuyPurchaseList($type, $num, $number)
    {
        list($amount,$value) = $this->getRebuyPurchaseType($type,$number);
        
        $list = $this->RStudentAccess->getRebuyPurchaseList($num, $amount);
        foreach ($list as &$v) {
            $v['introduce'] = empty($v['introduce'])?'0':$v['introduce'];
            $v['max_class'] = empty($v['max_class'])?'该用户没有上过课程':date('Y-m-d H:i',$v['max_class']);


            $v['content'] = $this->RStudentAccess->getMaxVisitHistory($v['id']);
            $v['content'] = empty($v['content'])?'无回访记录':$v['content'];


            if($v['amount'] == 0){
                $v['endweek'] = '没有剩余课程了.';
            }elseif($v['class_consume'] == 0 || empty($v['class_consume'])){
                $v['endweek'] = '上周无安排课记录.';
            }elseif($v['class_consume'] == 1){
                $v['endweek'] = '预计要'.$v['amount'].'周';
                $number =  $v['amount'] * 7;
                $v['day'] = '还有'.$number.'天';
            }else{
                $numup   = floor($v['amount']/$v['class_consume']);
                $numdown = $numup+1;
                $v['endweek'] = '预计要'.$numup.'~'.$numdown.'周';
                $number = $numup * 7;
                $v['day'] = '还有'.$number.'天';
            }
        }
        return $list;
    }


    public function getRebuyPurchaseType($type,$number)
    {
        if(!empty($number)){
            $type = 4;
        }

        if(is_numeric($number)){
            $type = 3;
        }

        switch ($type) {
            case '1':
                $amount = '';
                break;
            case '2':
                $amount = ' AND l.amount = 0';
                break;
            case '3':
                $amount = " AND l.amount <= '{$number}'";
                break;
            case '4':
                $amount = " AND l.amount <= -1";
                break;

        }
        $value = '';
        return [$amount,$value];
    }

    public function queryAllPurchaseList($keyword, $type, $num){
        $list = $this->RStudentAccess->queryAllPurchaseList($keyword, $type, $num);

        $week = ['日','一','二','三','四','五','六'];
        if (!empty($list))
        {
            foreach ($list as &$row)
            {
                $time_pay = $row['time_pay'];
                if($time_pay){
                    $row['time_pay']="周".$week[date('w',$time_pay)]." ".date('Y-m-d H:i',$time_pay);
                    $before_pay=ceil((($time_pay-time())/(3600*24))*-1);
                    if($before_pay=='-0'){
                        $before_pay=0;
                    }
                    $row['before_pay']="距今：".$before_pay."天";
                }else{
                    $row['time_pay']='无';
                    $row['before_pay']='';
                }
            }
        }

        return empty($list) ? [] : $list;
    }

    public function getStudentPage($request)
    {
        // 返回0 成功
        $type = $this->getType($request);
        //判断时间是否一致
        list($request['time_start'], $request['time_end'], $request['visit_time']) = $this->getTime($request['time_start'], $request['time_end'], $request['visit_time']);

        return $this->RStudentAccess->kefuCountStudent($request, $type);
    }

    public function getStudentALLFixTimes(){
        $this->RClassAccess->getStudentALLFixTimes();
    }

    public function getStudentList($request)
    {

        $type = $this->getType($request);

        list($request['time_start'], $request['time_end'], $request['visit_time']) = $this->getTime($request['time_start'], $request['time_end'], $request['visit_time']);

        $list = $this->RStudentAccess->kefuGetStudentInfoList($request, $type);

        foreach ($list as &$row) {
            $row['instrument_level'] = $this->RStudentAccess->getInstrumentLevelByStudentId($row['user_id']);

            $row['fix_times'] = empty($row['fix_times']) ? '无' :'有';
            //            if($row['instrument_level']["level"]==0)
//            {
//                $row['instrument_level']["level"]="未设置";
//            }else if($row['instrument_level']["level"]==1){
//                $row['instrument_level']["level"]="启蒙";
//            }else if($row['instrument_level']["level"]==2){
//                $row['instrument_level']["level"]="初级";
//            }else if($row['instrument_level']["level"]==3){
//                $row['instrument_level']["level"]="中级";
//            }else{
//                 $row['instrument_level']["level"]="高级";
//            }
        }

        return $list;
    }   

    public function getType($request)
    {
        if ($request['btn_id'] == 'student_btn_0') {
            return 0;
        }
        if ($request['btn_id'] == 'student_btn_1') {
            switch ($request['student_type']) {
                case 0 :
                    return 2;
                case 1 :
                    return 3;
                case 2 :
                    return 4;
                case 3 :
                    return 5;
                case 4 :
                    return 20;
                case 5 :
                    return 21;
            }
        }
        if ($request['btn_id'] == 'student_btn_2') {
            switch ($request['student_type']) {
                case 0 :
                    return 6;
                case 1 :
                    return 7;
                case 2 :
                    return 8;
                case 3 :
                    return 9;
                case 4 :
                    return 10;
                case 5 :
                    return 12;
                case 6 :
                    return 13;
                case 7 :
                    return 14;
                case 8 :
                    return 15;
                case 9 :
                    return 16;
            }
        }
        if ($request['btn_id'] == 'student_btn_3') {
            switch ($request['student_type']) {
                case 0 :
                    return 17;
                case 1 :
                    return 18;
            }
        }
    }

    private function getTime($timeStart, $timeEnd, $visitTime)
    {
        $timeStart = empty($timeStart) ? 0 : strtotime($timeStart);
        $timeEnd = empty($timeEnd) ? 0 : strtotime($timeEnd) + 86400;
        $visitTime = empty($visitTime) ? 0 : strtotime($visitTime);

        return [$timeStart, $timeEnd, $visitTime];
    }


    // //购买/体验记录 转移
    // public function actionBuyHistory($request)
    // {

    //     $ex = 

    //     $ex = ClassRoom::find()->select('time_class')
    //         ->where('student_id = :student_id', [':student_id' => $request['student_id']])
    //         ->andWhere(['is_ex_class' => 1])
    //         ->andWhere('status != 2 AND status != 3')   
    //         ->andWhere(['is_deleted' => 0])
    //         ->orderBy('time_class ASC')
    //         ->column();

    //     $bill = ClassEditHistory::find()->select('price, amount, type, time_created')
    //         ->where('student_id = :student_id', [':student_id' => $request['student_id']])
    //         ->andWhere('price > 0')
    //         ->andWhere(['is_add' => 1, 'is_success' => 1, 'is_deleted' => 0])
    //         ->orderBy('time_created ASC')
    //         ->all();

    //     $leftInfo = ClassLeft::find()
    //         ->select('id, type, left_bit, name, total_amount, amount, ac_amount')
    //         ->where(['user_id' => $request['student_id']])
    //         ->orderBy('type ASC')
    //         ->asArray()
    //         ->all();

    //     return $this->renderPartial('buy-history', [
    //         'exClassList' => $ex,
    //         'billList' => $bill,
    //         'leftInfo' => $leftInfo,
    //     ]);
    // }    

    public function editStudentPage($openID)
    {
        //获取用户初始化信息
        $data = $this->RStudentAccess->getUserInitInfo($openID);

        //若用户未进行自注册，则判断该用户是否是从活动渠道来的，并且已填写信息
        if($data)
        {
            $eventUser = $this->RStudentAccess->getUserEventWeixinInfo($openID);
            if($eventUser)
            {
                $data['nick'] = $eventUser->username;
                $data['mobile'] = $eventUser->cellphone;
                $data['age'] = $eventUser->age;
            }
        }

        //获取省份
        $provinceList = $this->RStudentAccess->getProvincesList();
        //将id=0，请选择省份插入到数组前面
        array_unshift($provinceList, ['id' => 0, 'name' => '请选择省份']);

        $cityList = [];
        //判断用户信息中的城市信息是否存在
        if (empty($data['city'])) 
        {
            array_unshift($cityList, ['id' => 0, 'name' => '请选择城市']);

        } else {
            $cityList = $this->RStudentAccess->getCityList($data['province']);
            array_unshift($cityList, ['id' => 0, 'name' => '请选择城市']);
        }

        //等级
        $Userlevel = array(
            ['id' => '没有考级', 'level' => '没有考级'],
            ['id' => '1级', 'level' => '1级'],
            ['id' => '2级', 'level' => '2级'],
            ['id' => '3级', 'level' => '3级'],
            ['id' => '4级', 'level' => '4级'],
            ['id' => '5级', 'level' => '5级'],
            ['id' => '6级', 'level' => '6级'],
            ['id' => '7级', 'level' => '7级'],
            ['id' => '8级', 'level' => '8级'],
            ['id' => '9级', 'level' => '9级'],
            ['id' => '10级', 'level' => '10级'],
        );

        //乐器等级
//        $levelList = array(
//            ['id' => 0, 'level' => '未设置'],
//            ['id' => 1, 'level' => 1],
//            ['id' => 2, 'level' => 2],
//            ['id' => 3, 'level' => 3],
//            ['id' => 4, 'level' => 4],
//            ['id' => 5, 'level' => 5],
//            ['id' => 6, 'level' => 6],
//            ['id' => 7, 'level' => 7],
//            ['id' => 8, 'level' => 8]
//        );
        $levelList = array(
            ['id' => 0, 'level' => '未设置'],
            ['id' => 1, 'level' => "启蒙"],
            ['id' => 2, 'level' => "初级"],
            ['id' => 3, 'level' => "中级"],
            ['id' => 4, 'level' => "高级"]
        );

        //获取当前乐器信息
        $instrumentList = $this->RStudentAccess->getInstrumentList();

        foreach ($instrumentList as &$instru) {
            if (empty($data['id'])) {
                $instru['level'] = 0;
                $instru['isCheck'] = 0;
            } else {
                //获取用户当前乐器的等级
                $instru['level'] = $this->RStudentAccess->getUserInstrumentLevel($data['id'], $instru['id']);
                if (empty($instru['level'])) {
                    $instru['isCheck'] = 0;
                } else {
                    //如果当前用户当前乐器有等级，则选中按钮选中
                    $instru['isCheck'] = 1;
                }
            }

        }
        //返回 openid，用户信息，乐器信息，乐器等级信息，省列表，城市列表，用户等级列表
        return [$openID, $data, $instrumentList, $levelList, $provinceList, $cityList, $Userlevel];
    }

    public  function  editStudentInfo($request='', $logid='')
    {
        if($request){
            $request =  $request->post();
           
            //判断用户姓名，手机号，生日信息是否完整
            if (empty($request['name']) || empty($request['phone'])  || empty($request['birth'])) {

                return ['error' => '信息填写不完整', 'data' => ''];
            }
            $studentID = $request['studentID'];
            if(empty($studentID)) {
                //新增（userinit表中没有当前用户）
                //如果userinit中没有用户，去user表中用手机号查找该用户
                $hasMobile = $this->RStudentAccess->getUserPhoneExist($request['phone']);

                if(!empty($hasMobile)) {
                    return ['error' => '表示该手机号码已经存在', 'data' => ''];
                }

                // 开启事务
                $transaction = Yii::$app->db->beginTransaction();
                try{
                    //保存学生基本信息
                    list($if_save, $sid) = $this->WStudentAccess->addUserInfo($request['phone'], $request['name'], $request['birth'], $request['province'], $request['city'], $request['remark'], $request['age'], $request['level']);
                    //如果未保存成功，返回提示信息
                    if(!$if_save){
                        return ['error' => '学生信息保存失败', 'data' => ''];
                    }

                    $openID = $request['openID'];

                    //更新微信isBind
                    //获取初始化信息的微信openid
                    $userInit = $this->RStudentAccess->getUserInitInfoByOpenid($openID);

                    if(!$this->WStudentAccess->updateUserInitInfoByOpenid($openID)){
                        return ['error' => '学生信息保存失败', 'data' => ''];   //更新微信绑定失败
                    }

                 //更新乐器
                   $instrument = 1;
                    //更新古筝乐器
                    if (!empty($request['kotoCheck'])) {
                        //insert
                        //添加乐器信息
                        $kotoid = 4;
                        //查询是否存在。存在修改，不存在添加
                         if(!$this->WStudentAccess-> insertInstrumentLevel($sid, $request['kotoLevel'],$kotoid)){
                            return ['error' => '学生信息保存失败2', 'data' => ''];
                        }
                        $instrument = 4;
                    }else{
                        //删除
                        $kotoid = 4;
                        if(!$this->WStudentAccess-> deleteInsertInstrumentLevel($sid, $request['kotoLevel'],$kotoid)){
                            return ['error' => '学生信息保存失败d1', 'data' => ''];
                        }
                        
                    }
                    
                    //更新手风琴乐器
                    if (!empty($request['squeezeboxCheck'])) 
                    {
                        $squeezeboxid = 3;
                         if(!$this->WStudentAccess-> insertInstrumentLevel($sid, $request['squeezeboxLevel'],$squeezeboxid)){
                            return ['error' => '学生信息保存失败3', 'data' => ''];
                        }
                        $instrument = 3;
                    }else{
                        $squeezeboxid = 3;
                        if(!$this->WStudentAccess-> deleteInsertInstrumentLevel($sid, $request['squeezeboxLevel'],$squeezeboxid)){
                            return ['error' => '学生信息保存失败d2', 'data' => ''];
                        }
                    }
                    
                    //更新小提琴乐器
                     if (!empty($request['voilinCheck'])) {
                        //insert
                        if(!$this->WStudentAccess-> insertVoilinInstrumentLevel($sid, $request['voilinLevel'])){
                            return ['error' => '学生信息保存失败', 'data' => ''];
                        }
                        $instrument = 2;
                    }else{
                        $voilinid = 2;
                        if(!$this->WStudentAccess-> deleteInsertInstrumentLevel($sid, $request['voilinLevel'],$voilinid)){
                            return ['error' => '学生信息保存失败d3', 'data' => ''];
                        }
                    }
                    
                    //更新钢琴乐器
                     if (!empty($request['pianoCheck'])) 
                    {
                        if(!$this->WStudentAccess->insertPianoInstrumentLevel($sid, $request['pianoLevel'])){
                            return ['error' => '学生信息保存失败', 'data' => ''];
                        }
                        $instrument = 1;
                    }else{
                        $pianoid=1;
                        if(!$this->WStudentAccess-> deleteInsertInstrumentLevel($sid, $request['pianoLevel'],$pianoid)){
                            return ['error' => '学生信息保存失败d3', 'data' => ''];
                        }
                    }
                    
                    //更新用户关系表(更新openid)
                    if(!$this->WStudentAccess->updateWechatInfo($sid, $openID)){
                        return ['error' => '学生信息保存失败', 'data' => '']; //用户关系信息保存失败
                    }

                    //1.1 添加体验课时
                    if(!$this->WStudentAccess->insertClassLeftBean($sid, $instrument)){
                        return ['error' => '用户关系信息保存失败', 'data' => ''];  //用户关系信息保存失败
                    }

                     //查询体验课套餐信息
                    $userIsex=$this->RStudentAccess->selectClassleftIsex($studentID);
                    if($userIsex["ac_amount"]>0)
                    {
                        if($userIsex["instrument_id"]!=$instrument)
                        {
                            //更新体验课套餐信息
                        if(!$this->WStudentAccess->updateClassLeftIsex($studentID, $instrument))
                        {
                        return ['error' => '用户关系信息保存失败8', 'data' => ''];  //用户关系信息保存失败
                        }
                        
                       //更新未上体验课的课程信息(更新排过的未上课的体验课信息)
                        $userIsexClass=$this->RStudentAccess->selectIsexClassinfo($studentID,$userIsex["id"]);  
                        if(!empty($userIsexClass)){
                            if(empty($userIsexClass["status"]))
                        {
                            //更新课程信息中的乐器id
                            if(!$this->WStudentAccess->updateIsexClassinfo($userIsexClass["id"],$instrument))
                            {
                             return ['error' => '用户关系信息保存失败9', 'data' => ''];  //用户关系信息保存失败
                            }
                            //更新课程记录中的乐器id
                            if(!$this->WStudentAccess->updateIsexClasshistory($userIsexClass["history_id"],$instrument))
                            {
                             return ['error' => '用户关系信息保存失败10', 'data' => ''];  //用户关系信息保存失败
                            }
                            
                        }
                        }
                        }
                        
                        
                        
                    }
                    
                    //添加换课渠道
                    list($channel, $cid) = $this->WStudentAccess->insertUserChannelBean($request['phone'], $sid, $request['name']);
                    if(!$channel){
                        return ['error' => '更新微信绑定失败', 'data' => ''];//更新微信绑定失败
                    }

                    //更新渠道ID和自推广ID到学生表
                    $stu = $this->WStudentAccess->updateUserChannel($sid, $userInit['channel_id'], $cid, $userInit['sales_id']);

                    if(!$stu){
                        return ['error' => '添加换课渠道ID失败', 'data' => ''];//添加换课渠道ID失败
                    }

                    //添加渠道费用statistics_channel_info
                    $today = strtotime(date('Y-m-d', time()));
                    $info = $this->RStudentAccess->getStatisticsChannelInfo($userInit['channel_id'], $today);

                    if (empty($info)) {
                        //add
                        if (!empty($userInit['channel_id'])) {
                            if (!$this->WStudentAccess->addStatisticsChannelInfo($userInit['channel_id'], $today)) {
                                return ['error' => '添加statistics失败', 'data' => ''];//添加statistics失败
                            }
                        }

                    } else {
                        //update
                        if(!$this->WStudentAccess->editStatisticsChannelInfo($userInit['channel_id'], $today)){
                            return ['error' => '添加statistics失败', 'data' => ''];//添加statistics失败
                        }
                    }

                    //完成注册,给渠道钱:每500人,188元,其他3-4元
/*
                    if ($userInit['sales_id'] > 0)
                    {
                        $money = 2;
                        if(!$this->WStudentAccess->addSalesTrade($userInit['sales_id'], $request['name'], $sid, $money))
                        {
                            return ['error' => '用户关系信息保存失败', 'data' => ''];
                        }
                    } else {
                        $money = 0;
                    }
*/

                    //插入销售系统
                    if(!$this->WStudentAccess->updateUserPublicSale($sid, $request['name'], $request['phone'])){
                        return ['error' => '用户关系信息保存失败', 'data' => ''];
                    }


                    if(!$this->WStudentAccess->addUserPublicInfo($sid, $openID, strtotime($request['birth']), $request['level'], $request['province'], $request['city'])){
                        return ['error' => '用户关系信息保存失败', 'data' => ''];
                    }


                    if(!$this->WStudentAccess->addUserRegistration($sid, $openID)){
                        return ['error' => '用户关系信息保存失败', 'data' => ''];
                    }

                    $transaction->commit();
                    LogService::OutputLog($logid, 'Add', '', '添加新学生');

   
                    $data = array (
                        'sid' => $sid,
                        'sales_id' => $userInit['sales_id'],
                        //'money' => $money,
                        'is_add' => 0
                    );

                    return array('error' => 0, 'data' => $data);

                }catch (Exception $ex){
                    $transaction->rollBack();

                    return ['error' => '系统出错请联系管理员', 'data' => ''];
                }

            } else {
                //修改
                //查询手机号是否存在
                $hasMobile = $this->RStudentAccess->getEditUserInfo($studentID, $request['phone']);

                if (!empty($hasMobile)) {
                        return ['error' => '该手机号码已经存在', 'data' => ''];
                }

                $transaction = Yii::$app->db->beginTransaction();
                try{
                    //编辑用户基本信息
                    if(!$this->WStudentAccess->editUserInfo($studentID, $request['phone'], $request['name'], $request['birth'], $request['province'], $request['city'], $request['remark'], $request['age'], $request['level'])){
                        return ['error' => '用户关系信息保存失败1', 'data' => ''];
                    }

                    //更新乐器
                   $instrument = 1;
                    //更新古筝乐器
                    if (!empty($request['kotoCheck']))
                    {
                        //添加乐器信息
                        $kotoid = 4;
                        //查询是否存在。存在修改，不存在添加
                         if(!$this->WStudentAccess-> insertInstrumentLevel($studentID, $request['kotoLevel'],$kotoid))
                        {
                            return ['error' => '学生信息保存失败2', 'data' => ''];
                        }
                        $instrument = 4;
                    }else{
                        //删除
                        $kotoid=4;
                        if(!$this->WStudentAccess-> deleteInsertInstrumentLevel($studentID, $request['kotoLevel'],$kotoid))
                        {
                            return ['error' => '学生信息保存失败d1', 'data' => ''];
                        }
                    }
                    
                    //更新手风琴乐器
                    if (!empty($request['squeezeboxCheck'])) 
                    {
                        $squeezeboxid=3;
                        if(!$this->WStudentAccess-> insertInstrumentLevel($studentID, $request['squeezeboxLevel'],$squeezeboxid))
                        {
                            return ['error' => '学生信息保存失败3', 'data' => ''];
                        }
                        $instrument = 3;
                    }else{
                        $squeezeboxid = 3;
                        if(!$this->WStudentAccess-> deleteInsertInstrumentLevel($studentID, $request['squeezeboxLevel'],$squeezeboxid))
                        {
                            return ['error' => '学生信息保存失败d2', 'data' => ''];
                        }
                    }
                    
                     if (!empty($request['voilinCheck'])) 
                    {
                        $voilin = $this->RStudentAccess->getUserVoilinCheck($studentID);
                        if (empty($voilin))
                        {
                            //insert
                            if(!$this->WStudentAccess-> insertVoilinInstrumentLevel($studentID, $request['voilinLevel']))
                            {
                                return ['error' => '用户关系信息保存失败4', 'data' => ''];
                            }
                        } else {
                            //update
                           if(!$this->WStudentAccess->updateVoilinInstumentLevel($studentID, $request['voilinLevel']) )
                           {
                               return ['error' => '用户关系信息保存失败5', 'data' => ''];
                            }
                        }
                        $instrument = 2;
                    }else{
                        $voilinid=2;
                        if(!$this->WStudentAccess-> deleteInsertInstrumentLevel($studentID, $request['voilinLevel'],$voilinid))
                        {
                            return ['error' => '学生信息保存失败d3', 'data' => ''];
                        }
                    }
                    
                    if (!empty($request['pianoCheck'])) 
                    {
                        $piano = $this->RStudentAccess->getUserPianoCheck($studentID);
                        if (empty($piano))
                        {
                            //insert
                            if(!$this->WStudentAccess->insertPianoInstrumentLevel($studentID, $request['pianoLevel']))
                            {
                                return ['error' => '用户关系信息保存失败6', 'data' => ''];
                            }
                        } else {
                            //update
                            if(!$this->WStudentAccess->updatePianoInstumentLevel($studentID, $request['pianoLevel']) )
                            {
                                return ['error' => '用户关系信息保存失败7', 'data' => ''];
                            }
                        }
                        $instrument = 1;
                    }else{
                        $pianoid = 1;
                        if(!$this->WStudentAccess-> deleteInsertInstrumentLevel($studentID, $request['pianoLevel'],$pianoid))
                        {
                            return ['error' => '学生信息保存失败d3', 'data' => ''];
                        }
                    }

                    //查询体验课套餐信息
                    $userIsex = $this->RStudentAccess->selectClassleftIsex($studentID);
                    if($userIsex["ac_amount"] > 0)
                    {
                        if($userIsex["instrument_id"] != $instrument)
                        {
                            //更新体验课套餐信息
                        if(!$this->WStudentAccess->updateClassLeftIsex($studentID, $instrument))
                        {
                        return ['error' => '用户关系信息保存失败8', 'data' => ''];  //用户关系信息保存失败
                        }
                        
                       //更新未上体验课的课程信息(更新排过的未上课的体验课信息)
                        $userIsexClass = $this->RStudentAccess->selectIsexClassinfo($studentID,$userIsex["id"]);    
                        if(!empty($userIsexClass)){
                             if(empty($userIsexClass["status"]))
                        {
                            //更新课程信息中的乐器id
                            if(!$this->WStudentAccess->updateIsexClassinfo($userIsexClass["id"],$instrument))
                            {
                             return ['error' => '用户关系信息保存失败9', 'data' => ''];  //用户关系信息保存失败
                            }
                            //更新课程记录中的乐器id
                            if(!$this->WStudentAccess->updateIsexClasshistory($userIsexClass["history_id"],$instrument))
                            {
                             return ['error' => '用户关系信息保存失败10', 'data' => ''];  //用户关系信息保存失败
                            }
                        }
                        }

                        }
                    }
                   
                    //更新销售系统
                    if(!$this->WStudentAccess->updateUserPublicSales($studentID, $request['name'], $request['phone'])){
                        return ['error' => '用户关系信息保存失败', 'data' => ''];
                    }
                    if(!$this->WStudentAccess->updateUserPublicInfoSale($studentID, strtotime($request['birth']), $request['level'], $request['province'], $request['city'])){
                        return ['error' => '用户关系信息保存失败', 'data' => ''];
                    }

                    $transaction->commit();

                    LogService::OutputLog($logid, 'Update', '', '编辑学生');
//                    return 1; //表示成功
                    $data = array (
                        'sid' => $studentID,
                        'is_add' => 1
                    );
                    return ['error' => 0, 'data' => $data];
                }catch (Exception $ex) {
                    $transaction->rollBack();
                    return ['error' => '异常信息', 'data' => ''];
                }
            }
        }

        return ['error' => '系统出错请联系管理员', 'data' => ''];
    }

/*
    public function SendMoneyMessage($openid,$money)
    {
        if ($money == 188) {
            $money_comment = '哇！一次注册就带来了超大红包！奖励188元，已放入您的账户，点击进行提现';
        } else {
            $money_comment = '有人注册了，奖励' . $money . '元已放入您的账户，点击进行提现';
        }

        $wechat = Yii::$app->wechat_new;
        $arr = [
            'touser' => $openid,
            'template_id' => Yii::$app->params['channel_template_income'],
            'url' => "http://channel.pnlyy.com/mine/account-index",
            'data' => [
                'first' => [
                    'value' => '您好，您有一笔收入到账！',
                    'color' => '#c9302c'
                ],
                'keyword1' => [
                    'value' => "学生注册红包收入",
                    'color' => '#c9302c'
                ],
                'keyword2' => [
                    'value' => $money . "元",
                    'color' => '#c9302c'
                ],
                'keyword3' => [
                    'value' => date("Y-m-d H:s", time()),
                    'color' => '#c9302c'
                ],
                'remark' => [
                    'value' => $money_comment,
                    'color' => '#c9302c'
                ],
            ],
        ];

        $wechat->sendTemplateMessage($arr);
    }
*/
    public function doBindKefu($request, $logid)
    {
        $studentId = $request->post('student_id', 0);
        $kefuId = $request->post('kefu_id', 0);
        $role = Yii::$app->user->identity->role;

        if ($role == 2)
        {
            if (!empty($studentId)) {

                if($kefuId == 0){
                    //取消新签绑定
                    $kefu_update_flag = $this->WStudentAccess->doBindKefu($studentId, $kefuId);
                }elseif ($kefuId == -1){
                    //取消复购绑定
                    $kefuId = 0;
                    $kefu_update_flag = $this->WStudentAccess->doBindKefuRe($studentId, $kefuId);
                }else{
                    //判断客服类型  1 新签   4 复购
                    $kefu_role = $this->RAccountAccess->getKefuRoleByKefuid($kefuId);

                    if($kefu_role == 1){
                        $kefu_update_flag = $this->WStudentAccess->doBindKefu($studentId, $kefuId);
                    }elseif ($kefu_role == 4){
                        $kefu_update_flag = $this->WStudentAccess->doBindKefuRe($studentId, $kefuId);
                    }
                }

                $bind_flag = $this->WStudentAccess->updateCoursekefuBindHistoryKefu($studentId,$kefuId);
                if ($kefu_update_flag && $bind_flag) {
                    LogService::OutputLog($logid, 'update', serialize($request), '绑定客服');
                    return Json::dieJson(true);
                }

                return Json::dieJson(array('error' => '绑定失败'));
            }
        }else {
            return Json::dieJson(array('error' => '权限不足'));
        }

        return Json::dieJson(array('error' => '参数错误'));
    }

    public function getCity($pid)
    {
        return  $this->RStudentAccess->getCityList($pid);
    }

    public function markHighRiskUser($uid,$high,$logid)
    {
        $tag = 0;

        if($high == 0){
            $tag = 1;
        }

        if($this->WStudentAccess->markHighRiskUser($uid, $tag)){
            $this->WStudentAccess->markHighRiskUserPublic($uid, $tag);
            return 1;
        }else{
            return 0;
        }
    }

    public function deleteUser($studentId, $logid)
    {
        $this->WStudentAccess->deleteUser($studentId);

        $wechat = $this->RStudentAccess->getUserExist($studentId);

        if (!empty($wechat->uid)) {
            $this->WStudentAccess->deleteWechatInfo($wechat['id']);
            $this->WStudentAccess->deleteUserInit($wechat['openid']);
            $this->WStudentAccess->deleteUserPulicInfo($studentId);

            LogService::OutputLog($logid, 'delete', '', '删除学生');
        }

        return true;
    }

    public function userLikeTeacher($student_id)
    {
        $remark = $this->RStudentAccess->getStudentRemark($student_id);

        $fixTime = $this->RStudentAccess->getStudentFixTimeById($student_id);

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

        return [$remark, $fixTime];
    }

    private function timeClassFormat($week)
    {
        switch ($week) {
            case 7 :
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
        return $week;
    }

    private function getClassTimeEnd($type, $timeStart)
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

    private function checkTeacherAvailable($teacherList, $teacherId)
    {
        $teacher2List = array();
        if (empty($teacherList[0]['id'])) {
            return json_encode(array('error' => '该老师此时间段没空,请刷新页面,重新选择'));
        }
        foreach ($teacherList as &$row) {
            $teacher2List[] = $row['id'];
        }
        if (!in_array($teacherId, $teacher2List)) {
            return json_encode(array('error' => '该老师此时间段没空,请刷新页面,重新选择'));
        }
        return true;
    }

    public function actionTeacherAvailable($request)
    {
        $request['class_id'] = isset($request['class_id']) ? $request['class_id'] : 0;
        $request['time_start'] = $this->timeFormat($request['time_start']);

        if ($request['time_start'] == false) {
            return json_encode(array('error' => 'invalid_time'));
        }

        $classInfo = ClassLeft::findOne(['id' => $request['left_id']]);
        $request['type'] = $classInfo['time_type'];
        $request['instrument_id'] = $classInfo['instrument_id'];

        $request['time_end'] = $this->getClassTimeEnd($request['type'], $request['time_start']);

        $classModel = new ClassRoom();

        $isEx = ClassEditHistory::find()
            ->where('student_id = :student_id', [':student_id' => $request['student_id']])
            ->andWhere('price > 0')
            ->andWhere(['is_add' => 1, 'is_success' => 1, 'is_deleted' => 0])
            ->count();

        $teacher1List = $classModel->getTeacherAvailableList($request);
        $teacher2List = empty($isEx) ? [] : $classModel->getTeacherHaveExClass($request);

        $teacherList = array_merge($teacher1List, $teacher2List);
        $teacherNow = empty($request['class_id']) ? 0
            : ClassRoom::find()->select('teacher_id')->where(['id' => $request['class_id']])->one();

        foreach ($teacherList as &$row) {
            $row['is_current'] = $row['id'] == $teacherNow['teacher_id'] ? 1 : 0;
        }

        return json_encode(array('error' => '', 'data' => $teacherList), JSON_UNESCAPED_SLASHES);
    } 

    private function timeFormat($time)
    {
        $arr = explode('T', $time);
        return strtotime($arr[0] . ' ' . $arr[1]);
    }

    public function countAllotPurchase($keyword, $start, $end){
        $start = strtotime($start);
        $end = strtotime($end);

        return $this->RStudentAccess->countAllotPurchase($keyword, $start, $end);
    }

    public function getAllotPurchaseList($keyword, $start, $end, $num){
        $start = strtotime($start);
        $end = strtotime($end);
        $list = $this->RStudentAccess->getAllotPurchaseList($keyword, $start, $end, $num);

        $week=['日','一','二','三','四','五','六'];

        if (!empty($list))
        {
            foreach ($list as &$row)
            {
                $time_pay=$row['time_pay'];
                $row['time_pay']= empty($time_pay) ? '':"周".$week[date('w',$time_pay)]." ".date('Y-m-d H:i',$time_pay);
                $row['is_distribute'] =0;
            }
        }

        return empty($list) ? [] : $list;
    }

    public function distributeUserAccountOne($logid, $userId,$kefuId){
        $user = $this->RStudentAccess->getUserPublicInfoKefuid($userId);

        $kefu_flag = $this->WStudentAccess->bindUserPublicInfoBindKefu($user,$kefuId);

        $bind_flag = $this->WStudentAccess->updateCoursekefuBindHistoryKefu($userId,$kefuId);

        if ($kefu_flag && $bind_flag)
        {
            LogService::OutputLog($logid,'update','','绑定复购客服');

            return json_encode(array('error' => '','data' => array('user_id' => $userId)));
        }else {
            return json_encode(array('error' => '绑定失败,请联系管理员', 'data' => array('user_id' => $userId)));
        }
    }

    public function countAllotNewUser($introduce, $start, $end){
        $start = strtotime($start);
        $end = strtotime($end);
        return $this->RStudentAccess->countAllotNewUser($introduce, $start, $end);
    }

    public function getAllotNewUserList($introduce, $start, $end, $num){
        $start = strtotime($start);
        $end = strtotime($end);
        $list = $this->RStudentAccess->getAllotNewUserList($introduce, $start, $end, $num);

        $week=['日','一','二','三','四','五','六'];

        if (!empty($list))
        {
            foreach ($list as &$row)
            {
                if (!empty($row['channelname']))
                {
                    $row['channel_type'] = '主课';
                    $row['qudao'] = $row['channelname'];

                    $row['kefu_nick'] = '无';
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

                $time_created=$row['time_created'];
                $row['time_created']="周".$week[date('w',$time_created)]." ".date('Y-m-d H:i',$time_created);
                $row['kefu_nick'] = empty($row['kefu_nick']) ? '无':$row['kefu_nick'];
            }
        }
        return empty($list) ? [] : $list;
    }
    
    public function distributeNewUser($logid,$userId,$kefuId){
        $kefu_flag = $this->WStudentAccess->distributeNewUser($userId,$kefuId);
        $bind_flag = $this->WStudentAccess->updateCoursekefuBindHistoryKefu($userId,$kefuId);

        if ($kefu_flag && $bind_flag)
        {
            $kefu_nick = $this->RAccountAccess->getNewSignKefuNick($kefuId);
            LogService::OutputLog($logid,'Add','','新用户分配新签客服');

            return json_encode(array('error' => '','data' => array('user_id' => $userId,'kefu_nick' => $kefu_nick)));
        }else {
            return json_encode(array('error' => '绑定失败,请联系管理员', 'data' => array('user_id' => $userId)));
        }
    }
    public function countAgainAllotNotPay($keyword){
        return $this->RStudentAccess->countAgainAllotNotPay($keyword);
    }

    public function getAgainAllotNotPayList($keyword, $num){
        $list = $this->RStudentAccess->getAgainAllotNotPayList($keyword, $num);

        $week=['日','一','二','三','四','五','六'];

        if (!empty($list))
        {
            foreach ($list as &$row)
            {
                $latest_ex_time = $row['latest_ex_time'];
                $row['latest_ex_time']="周".$week[date('w',$latest_ex_time)]." ".date('Y-m-d H:i',$latest_ex_time);
                $row['nickname'] = empty($row['nickname']) ? '无':$row['nickname'];
                $row['complain_time'] = empty($row['complain_time']) ? '无':$row['complain_time'].'次';

            }

        }
        return empty($list) ? [] : $list;
    }

    public function countAgainAllotNotPurchase($keyword){
        return $this->RStudentAccess->countAgainAllotNotPurchase($keyword);
    }

    public function getAgainAllotNotPurchaseList($keyword, $num){
        $list = $this->RStudentAccess->getAgainAllotNotPurchaseList($keyword, $num);

        $week=['日','一','二','三','四','五','六'];

        if (!empty($list))
        {
            foreach ($list as &$row)
            {
                //最后上课时间
                $latest_actual_time = $row['latest_actual_time'];
                $row['latest_actual_time']="周".$week[date('w',$latest_actual_time)]." ".date('Y-m-d H:i',$latest_actual_time);
                if(!empty($latest_actual_time)){
                    $before_pay=ceil((time()-$latest_actual_time)/(24*3600));
                    $row['before_actual_class_time'] ="距今：".$before_pay."天";
                }else{
                    $row['before_actual_class_time']="";
                }

                $row['newsign_kefu'] = empty($row['newsign_kefu']) ? '无':$row['newsign_kefu'];
                $row['re_kefu'] = empty($row['re_kefu']) ? '无':$row['re_kefu'];
                $row['complain_time'] = empty($row['complain_time']) ? '无':$row['complain_time'].'次';
            }
        }
        return empty($list) ? [] : $list;
    }

    public function distributeNotPurchase($logid,$userId,$kefuId){
        $kefu_flag = $this->WStudentAccess->distributeNotPurchase($userId,$kefuId);
        $bind_flag = $this->WStudentAccess->updateCoursekefuBindHistoryKefu($userId,$kefuId);

        if ($kefu_flag && $bind_flag)
        {
            LogService::OutputLog($logid,'update','','未复购分配新签客服');

            return json_encode(array('error' => '','data' => array('user_id' => $userId)));
        }else {
            return json_encode(array('error' => '绑定失败,请联系管理员', 'data' => array('user_id' => $userId)));
        }
    }

    public function countAgainAllotNotFollow($btn, $keyword, $start, $end, $kefu_id)
    {

        $start = strtotime($start);
        $end = strtotime($end);

        //默认昨天和前天
        if(empty($start) && empty($end) ){
            $today= date('Y-m-d',time());

            $start = strtotime("$today -2 day ");
            $end = strtotime($today );
        }

        if($btn == 0){
            $count = $this->RStudentAccess->countAgainAllotNotFollow($keyword, $start, $end,$kefu_id);
        }elseif ($btn == 1){
            $count = $this->RStudentAccess->countAgainAllotNotFollowExperienceClassBefore($keyword, $start, $end,$kefu_id);
        }elseif ($btn == 2){
            $count = $this->RStudentAccess->countAgainAllotNotFollowExperienceClassLater($keyword, $start, $end,$kefu_id);
        }

        return $count;
    }

    public function getAgainAllotNotFollowList($btn, $keyword, $start, $end, $num,$kefu_id)
    {

        $start = strtotime($start);
        $end = strtotime($end);

        //默认昨天和前天
        if(empty($start) && empty($end) ){
            $today= date('Y-m-d',time());

            $start = strtotime("$today -2 day ");
            $end = strtotime($today );
        }

//        var_dump($start.",end 2= ". $end);
//        die();

        $week = ['日','一','二','三','四','五','六'];

        if($btn == 0){

            $list = $this->RStudentAccess->getAgainAllotNotFollowList($keyword, $start, $end, $num,$kefu_id);
            if (!empty($list))
            {
                foreach ($list as &$row)
                {
                    //分配时间
                    $time_operated = $row['time_operated'];
                    $row['time_operated']="周".$week[date('w',$time_operated)]." ".date('Y-m-d H:i',$time_operated);

                    //第一次跟进时间
                    $first_visit_time= $row['first_visit_time'];
                    if(empty($first_visit_time)){
                        $row['first_visit_time'] = '无';
                        $row['before_visit_time'] = '';
                    }else{
                        $row['first_visit_time'] = "周".$week[date('w',$first_visit_time)]." ".date('Y-m-d H:i',$first_visit_time);
                        $before_pay = ceil(($first_visit_time-$time_operated)/3600);
                        $row['before_visit_time'] = "距分配：".$before_pay."小时";
                    }

                    //体验课时间
                    $ex_class_time = $row['ex_class_time'];
                    $ex_class_time_start = empty($ex_class_time) ? '无':"周".$week[date('w',$ex_class_time)]." ".date('Y-m-d H:i',$ex_class_time);
                    //体验课结束时间时间
                    $ex_clastime_time_end = $row['ex_clastime_time_end'];
                    $ex_clastime_time_end = empty($ex_class_time) ? '无':date('H:i',$ex_clastime_time_end);
                    $row['ex_class_time'] = $ex_class_time_start."--".$ex_clastime_time_end;
                }
            }
        } elseif ($btn == 1){
            $list = $this->RStudentAccess->getAgainAllotNotFollowExperienceClassBeforeList($keyword, $start, $end, $num,$kefu_id);

            if (!empty($list))
            {
                foreach ($list as &$row)
                {
                    //体验课时间
                    $ex_class_time = $row['ex_class_time'];
                    $ex_class_time_start =empty($ex_class_time) ? '无':"周".$week[date('w',$ex_class_time)]." ".date('Y-m-d H:i',$ex_class_time);
                    //体验课结束时间时间
                    $ex_clastime_time_end = $row['ex_clastime_time_end'];
                    $ex_clastime_time_end =empty($ex_class_time) ? '无':date('H:i',$ex_clastime_time_end);
                    $row['ex_class_time'] = $ex_class_time_start."--".$ex_clastime_time_end;

                    //体验课前跟进时间
                    $before_visit_time = $row['before_visit_time'];
                    $row['before_visit_time'] =empty($before_visit_time) ? '无':"周".$week[date('w',$before_visit_time)]." ".date('Y-m-d H:i',$before_visit_time);
                    if(!empty($before_visit_time)){
                        $before_pay=ceil(($ex_class_time-$before_visit_time)/3600);
                        $row['class_before_visit_time'] ="距体验课：".$before_pay."小时";
                    }else{
                        $row['class_before_visit_time']="";
                    }

                    if($row['status'] == 0){
                        $row['status'] = '未上课';
                    }elseif ($row['status'] == 1){
                        $row['status'] = '已上课';
                    }else{
                        $row['status'] = '课程取消';
                    }
                }
            }
        } elseif ($btn == 2) {
            $list = $this->RStudentAccess->getAgainAllotNotFollowExperienceClassLaterList($keyword, $start, $end, $num,$kefu_id);

            if (!empty($list))
            {
                foreach ($list as &$row)
                {
                    //体验课时间
                    $ex_class_time = $row['ex_class_time'];
                    $ex_class_time_start =empty($ex_class_time) ? '无':"周".$week[date('w',$ex_class_time)]." ".date('Y-m-d H:i',$ex_class_time);
                    //体验课结束时间时间
                    $ex_clastime_time_end = $row['ex_clastime_time_end'];
                    $ex_clastime_time_end =empty($ex_class_time) ? '无':date('H:i',$ex_clastime_time_end);
                    $row['ex_class_time'] = $ex_class_time_start."--".$ex_clastime_time_end;

                    //体验课后跟进时间
                    $later_visit_time = $row['later_visit_time'];
                    $row['later_visit_time'] =empty($later_visit_time) ? '无':"周".$week[date('w',$later_visit_time)]." ".date('Y-m-d H:i',$later_visit_time);
                    if(!empty($later_visit_time)){
                        $before_pay=ceil(($later_visit_time - $ex_class_time)/3600);
                        $row['class_later_visit_time'] ="距体验课：".$before_pay."小时";
                    }else{
                        $row['class_later_visit_time']="";
                    }

                    if($row['status'] == 0){
                        $row['status'] = '未上课';
                    }elseif ($row['status'] == 1){
                        $row['status'] = '已上课';
                    }else{
                        $row['status'] = '课程取消';
                    }
                }
            }
        }

        return empty($list) ? [] : $list;
    }

    public function countPublicUserPage($type, $kefuId, $area , $keyword , $intention , $time_type, $start , $end)
    {
        $start = strtotime($start);
        $end = strtotime($end);
        $count = $this->RStudentAccess->countPublicUserPage($type, $kefuId, $area , $keyword , $intention , $time_type, $start , $end);

        return empty($count) ? 0 : $count;
    }

    public function getPublicUserList($type, $kefuId, $area, $keyword, $intention, $time_type, $start, $end,$num)
    {
        $start = strtotime($start);
        $end = strtotime($end);

        $list = $this->RStudentAccess->getPublicUserList($type, $kefuId, $area, $keyword, $intention, $time_type, $start, $end,$num);

        foreach ($list as &$row)
        {
            if (!empty($row['sales_id']))
            {
                $row['channel_type'] = '主课';
            } elseif (!empty($row['channel_id']))
            {
                if ($row['type'] == 2)
                {
                    $row['channel_type'] = '家长';
                } elseif ($row['type'] == 5)
                {
                    $row['channel_type'] = '活动';
                } else {
                    $row['channel_type'] = '其他';
                }
            } else {
                $row['channel_type'] = '无';
            }
           //echo 'new '.$row['user_id'].'='.date('Y', time()).' biirth='.date('Y', $row['birth']);
            $row['age'] = empty($row['age']) ? '未设置'
                :$row['age'];

            $row['area'] = empty($row['area']) ? '无'
                : $row['area'] . '类地区';

            $row['level'] = empty($row['level']) ? '未设置'
                : $row['level'] ;

            $row['once'] = empty($row['purchase']) ? '未付费' : '已付费';

            $row['is_distribute'] = (empty($row['kefu_id']) && empty($row['kefu_id_tmp']))
                ? 0 : 1;

            $row['kefu_nick'] = '无';

            switch ($row['intention'])
            {
                case 0 :
                    $row['intention'] = '未联系';
                    break;
                case 1 :
                    $row['intention'] = '无意向';
                    break;
                case 2 :
                    $row['intention'] = '有意向';
                    break;
                case 3 :
                    $row['intention'] = '高意向';
                    break;
                case 4 :
                    $row['intention'] = '彻底无意向';
                    break;
            }
        }

        return empty($list) ? [] : $list;
    }

//    public function distributePublicUserKefu($logid,$userId,$kefuId)
//    {
//        $kefuInfo = $this->RAccountAccess->getUserAccountOne($kefuId);
//
//
//        $dayCount = $kefuInfo->day_user;
//
//
//        $count = $this->RStudentAccess->countUserPublicInfoKefutmp($kefuId);
//
//        //$userinfo = $this->RStudentAccess->isNullPublicUserKefu();
//
//        if ($dayCount > $count)
//        {
//            //如果不存在任何客服则添加    存在则修改
//            $user_flag = $this->WStudentAccess->updateUserPublicInfoKefu($userId,$kefuId);
//
//            $bind_flag = $this->WStudentAccess->updateCoursekefuBindHistoryKefu($userId,$kefuId);
//            if ($user_flag && $bind_flag)
//            {
//                LogService::OutputLog($logid,'Add','','绑定销售');
//
//                return json_encode(array('error' => '', 'data' => array('user_id' => $userId, 'nick' => $kefuInfo->nickname)));
//            }else {
//                return json_encode(array('error' => '绑定失败,请联系管理员', 'data' => array('user_id' => $userId)));
//            }
//
//
//        }else {
//            return json_encode(array('error' => '绑定失败,超出当天最大分配额度', 'data' => array('user_id' => $userId)));
//        }
//    }

    public function distributePublicUserKefu($logid,$userId,$kefuId)
    {
        $kefuInfo = $this->RAccountAccess->getUserAccountOne($kefuId);

        $count = $this->RStudentAccess->countUserPublicInfoKefutmp($kefuId);

        //如果不存在任何客服则添加    存在则修改
        $user_flag = $this->WStudentAccess->updateUserPublicInfoKefu($userId,$kefuId);

        $bind_flag = $this->WStudentAccess->updateCoursekefuBindHistoryKefu($userId,$kefuId);
        if ($user_flag && $bind_flag)
        {
            LogService::OutputLog($logid,'Add','','绑定销售');

            return json_encode(array('error' => '', 'data' => array('user_id' => $userId, 'nick' => $kefuInfo->nickname)));
        }else {
            return json_encode(array('error' => '绑定失败,请联系管理员', 'data' => array('user_id' => $userId)));
        }

    }



    public function distributeAllUserKefu($userId,$kefuId)
    {
        $kefuInfo = $this->RAccountAccess->getAllUserUserAccountOne($kefuId);

        //如果不存在任何客服则添加    存在则修改
        $user_flag = $this->WStudentAccess->updateAllUserInfoKefu($userId,$kefuId);

//        $bind_flag = $this->WStudentAccess->updateCoursekefuBindHistoryKefu($userId,$kefuId);
        if ($user_flag)
        {
            return json_encode(array('error' => '', 'data' => array('user_id' => $userId, 'nick' => $kefuInfo->nickname)));
        }else {
            return json_encode(array('error' => '绑定失败,请联系管理员', 'data' => array('user_id' => $userId)));
        }

    }

    public function getApplysCount($is_called, $search)
    {
        return $this->RStudentAccess->getApplysCount($is_called, $search);
    }

    public function getApplysList($is_called, $search, $page)
    {
        return $this->RStudentAccess->getApplysList($is_called, $search, $page);
    }

    public function experienceMark($applyId, $logid)
    {
        $userApply = $this->RStudentAccess->getUserPreInfo($applyId);

        if($userApply){
            $this->WStudentAccess->experienceMark($applyId);

            LogService::OutputLog($logid,'update','0','标记已处理');
            return 1;
        }

        return 0;
    }


    public function deleteExperience($applyId, $logid)
    {
        $userApply = $this->RStudentAccess->getUserPreInfo($applyId);

        if($userApply){
            $this->WStudentAccess->deleteExperience($applyId);

            LogService::OutputLog($logid,'delete','0','删除体验');

            return 1;
        }

        return 0;
    }

    public function getAppsCount($status, $search)
    {
        return $this->RStudentAccess->getAppsCount($status, $search);
    }   

    public function getAppsList($status, $search, $page)
    {
        return $this->RStudentAccess->getAppsList($status, $search, $page);
    } 

    public function editAppsDeal($request, $logid)
    {
        if(empty($request)){
            return 0;
        }

        $query = $this->RStudentAccess->getUserEventWeixinById($request['id']);

        if($query){
            if($this->WStudentAccess->editWeixinStatus($query['id'],$request['result'])){

                LogService::OutputLog($logid,'update','0','标记已处理app');
                    return 1;
            }
        }

        return 0;
    }

    public function getAllUserIndex()
    {
        return  $this->RStudentAccess->getAllUserInitCount();
    }  

    public function getAllUserPage($keyword, $type)
    {
        return  $this->RStudentAccess->getWechatUserCount($keyword, $type);
    }
    
    public function getAllUserList($request)
    {
        return $this->RStudentAccess->getWechatUserList($request); 
    }



    public function getStudentFixTime($open_id)
    {
        $student = $this->RChatAccess->getWechatAccByExist($open_id);
        
        if(!empty($student))
        {
            $timeList = $this->RStudentAccess->getStudentFixTimeInfo($student['uid']);

            if (!empty($timeList))
            {
                foreach ($timeList as &$item)
                {
                    if ($item['gender'] == 0)
                    {
                        $gender = '男';
                    }else{
                        $gender = '女';
                    }

                    $grade_info = $this->RTeacherAccess->getTeacherGradeByInstrument($item['teacher_id'], $item['instrument_id']);

                    $item['teacher_name'] = $item['teacher_name'] . '[' . $gender . '-' . $grade_info['grade'] . '-' . $grade_info['level'] . ']';
                }
            }

            $weekList = array (
                ['key' => 1, 'week' => '周一'], ['key' => 2, 'week' => '周二'],
                ['key' => 3, 'week' => '周三'], ['key' => 4, 'week' => '周四'],
                ['key' => 5, 'week' => '周五'], ['key' => 6, 'week' => '周六'],
                ['key' => 7, 'week' => '周日']
            );

//            $classType = array (
//                ['key' => 1, 'type' => '25分钟'],
//                ['key' => 2, 'type' => '45分钟'],
//                ['key' => 3, 'type' => '50分钟']
//            );

            $classType = $this->RClassAccess->getLeftClassType($student['uid']);

//            $instrumentType = array (
//                ['key' => 1, 'type' => '钢琴'],
//                ['key' => 2, 'type' => '小提琴'],
//                ['key' => 3, 'type' => '手风琴'],
//                ['key' => 4, 'type' => '古筝'],
//            );

            $instrumentType = $this->RClassAccess->getLeftInstrument($student['uid']);

            $hourList = array();

            for($i = 0;$i < 24; $i ++)
            {
                $hourList[] = array('key' => $i, 'hour' => $i);
            }

            return [$weekList, $hourList, $classType, $timeList, $open_id, $instrumentType];
        }else {
            return ['is_auth' => 0];
            
        }
    }   

    public function getHaibao($openid)    
    {
        $data = $this->RStudentAccess->getWechatChannelId($openid);

        if(empty($data)){
            return 0;
        }

        //生成二维码
        $wechat = Yii::$app->wechat;
        $qrcode = [
            'expire_seconds'=>2592000,
            'action_name'=>'QR_SCENE',
            'action_info'=>[
                'scene'=>[
                    'scene_id'=>$data
                ],
            ],
        ];

        $tickect = $wechat->createQrCode($qrcode);
        $imgUrl = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $tickect['ticket'];

        $jpgName = 'tmp/' . uniqid() . '.jpg';

        $qrcodeImage = imagecreatefromjpeg($imgUrl);
        $qrcodeImageResized = imagecreate(190, 190);
        imagecopyresampled($qrcodeImageResized, $qrcodeImage, 0, 0, 0, 0, 190, 190, 430, 430);

        $posterImage = imagecreatefromjpeg('images/student_poster.jpg');
        imagecopy($posterImage, $qrcodeImageResized, 423, 833, 0, 0, 190, 190);

        imagejpeg($posterImage, $jpgName, 100);
        //unlink($jpgName);

        return [$jpgName];
    }

    public function getAccountExport($request)
    {
        $type = $this->getType($request);

        list($request['time_start'], $request['time_end'], $request['visit_time']) = $this->getTime($request['time_start'], $request['time_end'], $request['visit_time']);


        $kefuInfo =  $this->RStudentAccess->getNoZeroUserAccount();


        if($type == 21){

            $data = $this->RStudentAccess->getRemainClass();

            foreach($data as &$v)
            {
                $v['kefu_name'] = '无';
                foreach($kefuInfo as $va)
                {
                    if($va['id'] == $v['kefu_id']){
                        $v['kefu_name'] = $va['nickname'];
                    }
                }
                unset($v['kefu_id']);
            }

            $title = '剩余5节课时报表';
            $columnMap = array('姓名','手机号','渠道名称','微信名','剩余课时','最后一节课上课时间','销售顾问',);

            PhpExcel::getExcel($title, $data, $columnMap, $fileName = '', $is_excel = 1, $width=10);
        }else{

            $studentList = $this->RStudentAccess->kefuGetStudentInfoList($request, $type);

            foreach($studentList as &$v)
            {
                $v['kefu_name'] = '无';
                foreach($kefuInfo as $va)
                {
                    if($va['id'] == $v['kefu_id']){
                        $v['kefu_name'] = $va['nickname'];
                    }
                }
            }

            PhpExcel :: sendExcel($studentList);
        }

    }

    public function getClassRoomInfoByClassId($class_id){
        return $this->classesService->getClassRoomInfoByClassId($class_id);
    }

    public function doChangeClassTime($class_id,$ahead,$defer){
        return $this->classesService->doChangeClassTime($class_id,$ahead,$defer);
    }

    public function getAllUsersByKefuId($uid,$keyword,$offset,$limit)
    {
        $retuenData = array (
            'error' => 0,
            'data' => []
        );

        $data = $this->RStudentAccess->getAllUsersByKefuIdRe($uid,$keyword,$offset,$limit);

        foreach ($data as &$row)
        {
            $row['channel_name'] = $this->getChannelNameById($salesId, $channelId);
            $row['user_type'] = $this->getUserType($openId);
        }

        $retuenData['data'] = $data;

        return $retuenData;
    }


    public function getPurchaseUserAgainAllotNotFollowPage($type, $studentName, $distributionTime, $saleId)
    {
        $timeArray = $this->getDistributionTime($distributionTime);

        $info = array(
            'type' => $type,
            'start' => $timeArray['data']['start'],
            'end' => $timeArray['data']['end'],
            'studentName' => $studentName,
            'saleId' => $saleId
        );

        $count = $this->RStudentAccess->getPurchaseUserAgainAllotNotFollowPage($info);

        $data = array(
            'count' => $count
        );

        return ['error' => 0, 'data' => $data];
    }

    private function getDistributionTime($distributionTime)
    {
        $timeArray = explode('-', $distributionTime);

        $data = array(
            'start' => strtotime($timeArray['0']),
            'end' => strtotime($timeArray['1']) + 86400
        );

        return ['error' => 0, 'data' => $data];
    }

    public function getPurchaseUserAgainAllotNotFollowList($type, $studentName, $distributionTime, $saleId, $num)
    {
        $timeArray = $this->getDistributionTime($distributionTime);

        $info = array(
            'type' => $type,
            'start' => $timeArray['data']['start'],
            'end' => $timeArray['data']['end'],
            'studentName' => $studentName,
            'saleId' => $saleId,
            'num' => $num
        );

        $list = $this->RStudentAccess->getPurchaseUserAgainAllotNotFollowList($info);

        foreach ($list  as &$v)
        {
            $v['time_operated_re'] = date('Y-m-d H:i:s', $v['time_operated_re']);
            $v['kefu_nick'] = $this->RAccountAccess->getNewSignKefuNick($v['kefu_id_re']);

            $recent_visit = $this->RVisitAccess->getRecentVisitRecord($v['user_id']);
            $v['recent_visit_time'] = !empty($recent_visit['time_created']) ? date('Y-m-d H:i:s', $recent_visit['time_created'])
                : '没有跟进记录';
            $v['recent_visit_content'] = !empty($recent_visit['time_visit']) ? $recent_visit['content'] : '';
        }


        if ($type == 2)
        {
            foreach ($list as &$v)
            {
                $class_time = $this->RClassAccess->getFirstPayClass($v['user_id']);
                $v['class_time'] = !empty($class_time) ? date('Y-m-d H:i:s', $class_time) : '还未安排课程';
            }
        }

        $data = array(
            'list' => $list
        );

        return ['error' => 0, 'data' => $data];

    }

}
