<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 16/12/29
 * Time: 下午3:29
 */
namespace common\logics\order;

use common\widgets\Queue;
use common\services\LogService;
use Yii;
use yii\base\Object;
use yii\db\Exception;
use yii\db\Query;

class OrderLogic extends Object implements IOrder {

    /** @var  \common\sources\read\order\OrderAccess  $ROrderAccess */
    private $ROrderAccess;
    /** @var  \common\sources\write\order\OrderAccess  $ROrderAccess */
    private $WOrderAccess;
    /** @var  \common\sources\read\product\ProductAccess  $RProductAccess */
    private $RProductAccess;
    /** @var  \common\sources\write\product\ProductAccess  $WProductAccess */
    private $WProductAccess;
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
    /** @var  \common\sources\read\channel\ChannelAccess  $RChannelAccess */
    private $RChannelAccess;

    public function init()
    {
        $this->RStudentAccess = Yii::$container->get('RStudentAccess');
        $this->WStudentAccess = Yii::$container->get('WStudentAccess');
        $this->RClassAccess = Yii::$container->get('RClassAccess');
        $this->WClassAccess = Yii::$container->get('WClassAccess');
        $this->ROrderAccess = Yii::$container->get('ROrderAccess');
        $this->WOrderAccess = Yii::$container->get('WOrderAccess');
        $this->RProductAccess = Yii::$container->get('RProductAccess');
        $this->WProductAccess = Yii::$container->get('WProductAccess');
        $this->WChannelAccess = Yii::$container->get('WChannelAccess');
        $this->RChannelAccess = Yii::$container->get('RChannelAccess');
        $this->RClassAccess = Yii::$container->get('RClassAccess');
        parent::init();
    }
/*
    public function dealTradeNotify($request)
    {

        $returnData = array (
            'error' => 0,
            'data' => []
        );
        $orderNo = $request['trade_id'];
        $price = 0;
        $orderIdOld = isset($request['order_id_old'])
            ? $request['order_id_old']
            : 0;

        $order = $this->ROrderAccess
            ->getProductOrderRowByOrderNo($orderNo);
        
        $product = $this->RProductAccess
            ->getProductRowById($order['pid']);

        $studentInfo = $this->RStudentAccess
            ->getUserRowById($order['uid']);

        if ($order['coupon'] != '000000')
        {
            $couponPrice = $this->RProductAccess
                ->getPriceByCoupon($order['coupon']);

            $order['actual_fee'] = $order['actual_fee'] - $couponPrice;
        }

        //事务性插入
        $transaction = Yii::$app->db->beginTransaction();

        try {

            //添加课时
            $data = [
                'uid' => $order['uid'],
                'order_id' => $order['id'],
                'pid' => $order['pid'],
                'p_amount' => 1,
                'amount' => $order['class_num'],
                'price' => $order['actual_fee'],
                'instrument_id' => $order['instrument'],
                'time_type' => $product['time_type']
            ];

            $historyId = $this->WClassAccess
                ->addClassHistoryFromPurchase($data);

            //换套餐
            if (!empty($orderIdOld))
            {
                //获取原来的课程的消耗的数量和删除原有的预约课程
                $amountInfo = $this->RClassAccess
                    ->getClassLeftRowByOrder($orderIdOld);

                $historyInfo = $this->RClassAccess
                    ->getAddClassHistoryRowByOrderId($orderIdOld);

                if ($amountInfo['amount'] < $amountInfo['ac_amount'])
                {
                    $this->WClassAccess
                        ->deleteClassFromChangeGoods($amountInfo['id']);

                    $this->WClassAccess
                        ->deleteClassHistoryFromChageGoods($orderIdOld);
                }

                $data = [
                    'uid' => $order['uid'],
                    'order_id' => $order['id'],
                    'order_id_old' => $orderIdOld,
                    'pname' => $product['name'],
                    'class_type' => 3,
                    'instrument' => $order['instrument'],
                    'time_type' => $product['time_type'],
                    'class_num' => $order['class_num'],
                    'actual_fee' => $order['actual_fee'],
                    'price' => round( $order['actual_fee'] / $order['class_num'], 2),
                    'ac_amount' => $order['class_num'] - ($amountInfo['total_amount'] - $amountInfo['ac_amount'])
                ];

                $this->addClassTimes($data);

                if(!empty($studentInfo['sales_id']))
                {
                    $price = $order['actual_fee'] * 0.08;
                    $this->WChannelAccess->addSalesTradeUncashoutByChange($price, $studentInfo);

                    $info = $this->RChannelAccess
                        ->getSalesChannelFromcodeById($studentInfo['sales_id']);

                    if (!empty($info['from_code']))
                    {
                        $salesInfo = $this->RChannelAccess->getSalesChannelInfo($info['from_code']);
                        $data = [
                            'uid' => $salesInfo['id'],
                            'from_uid' => $studentInfo['sales_id'],
                            'student_id' => $studentInfo['id'],
                            'student_name' => $salesInfo['nickname'],
                            'money' => $price * 0.5
                        ];

                        $this->WChannelAccess->addFatherSalesTradePurchase($data);
                    }
                }
            }else {

                $data = [
                    'uid' => $order['uid'],
                    'order_id' => $order['id'],
                    'pname' => $product['name'],
                    'class_type' => 3,
                    'instrument' => $order['instrument'],
                    'time_type' => $product['time_type'],
                    'class_num' => $order['class_num'],
                    'actual_fee' => $order['actual_fee'],
                ];

                $this->addClassTimes($data);

                if (!empty($studentInfo['sales_id']))
                {
                    $price = $order['actual_fee'] * 0.08;

                    $this->WChannelAccess->addSalesTradeUncashoutByPurchase($price, $studentInfo);

                    $info = $this->RChannelAccess
                        ->getSalesChannelFromcodeById($studentInfo['sales_id']);

                    if (!empty($info['from_code']))
                    {
                        $salesInfo = $this->RChannelAccess->getSalesChannelInfo($info['from_code']);

                        $data = [
                            'uid' => $salesInfo['id'],
                            'from_uid' => $studentInfo['sales_id'],
                            'student_id' => $studentInfo['id'],
                            'student_name' => $info['nickname'],
                            'money' => $price * 0.5
                        ];

                        $this->WChannelAccess->addFatherSalesTradePurchase($data);
                    }
                }

                $this->WStudentAccess->updateUserBuyTimes($order['uid']);
            }

            $transaction->commit();

            $returnData['data'] = array (
                'student_name' => $studentInfo['nick'],
                'sales_id' => $studentInfo['sales_id'],
                'price' => $price
            );

            return $returnData;

        }catch (Exception $ex){
            $transaction->rollBack();
            return false;
        }
    }
*/

    public function dealTradeNotify($request)
    {

        $returnData = array (
            'error' => 0,
            'data' => []
        );
        $orderNo = $request['trade_id'];
        $price = 0;
        $orderIdOld = isset($request['order_id_old'])
            ? $request['order_id_old']
            : 0;

        $order = $this->ROrderAccess
            ->getProductOrderRowByOrderNo($orderNo);

        $product = $this->RProductAccess
            ->getProductRowById($order['pid']);

        $studentInfo = $this->RStudentAccess
            ->getUserRowById($order['uid']);

        if ($order['coupon'] != '000000')
        {
            $couponPrice = $this->RProductAccess
                ->getPriceByCoupon($order['coupon']);

            $order['actual_fee'] = $order['actual_fee'] - $couponPrice;
        }

        $price = $order['actual_fee'];

        //事务性插入
        $transaction = Yii::$app->db->beginTransaction();

        try {

            //添加课时
            $data = [
                'uid' => $order['uid'],
                'order_id' => $order['id'],
                'pid' => $order['pid'],
                'p_amount' => 1,
                'amount' => $order['class_num'],
                'price' => $order['actual_fee'],
                'instrument_id' => $order['instrument'],
                'time_type' => $product['time_type']
            ];

            $historyId = $this->WClassAccess
                ->addClassHistoryFromPurchase($data);

            //换套餐
            if (!empty($orderIdOld))
            {
                //获取原来的课程的消耗的数量和删除原有的预约课程
                $amountInfo = $this->RClassAccess
                    ->getClassLeftRowByOrder($orderIdOld);

                $historyInfo = $this->RClassAccess
                    ->getAddClassHistoryRowByOrderId($orderIdOld);

                if ($amountInfo['amount'] < $amountInfo['ac_amount'])
                {
                    $this->WClassAccess
                        ->deleteClassFromChangeGoods($amountInfo['id']);

                    $this->WClassAccess
                        ->deleteClassHistoryFromChageGoods($orderIdOld);
                }

                $data = [
                    'uid' => $order['uid'],
                    'order_id' => $order['id'],
                    'order_id_old' => $orderIdOld,
                    'pname' => $product['name'],
                    'class_type' => 3,
                    'instrument' => $order['instrument'],
                    'time_type' => $product['time_type'],
                    'class_num' => $order['class_num'],
                    'actual_fee' => $order['actual_fee'],
                    'price' => round( $order['actual_fee'] / $order['class_num'], 2),
                    'ac_amount' => $order['class_num'] - ($amountInfo['total_amount'] - $amountInfo['ac_amount'])
                ];

                $this->addClassTimes($data);
            }else {

                $data = [
                    'uid' => $order['uid'],
                    'order_id' => $order['id'],
                    'pname' => $product['name'],
                    'class_type' => 3,
                    'instrument' => $order['instrument'],
                    'time_type' => $product['time_type'],
                    'class_num' => $order['class_num'],
                    'actual_fee' => $order['actual_fee'],
                ];

                $this->addClassTimes($data);

                $this->WStudentAccess->updateUserBuyTimes($order['uid']);
            }

            $transaction->commit();

            $returnData['data'] = array (
                'student_info' => $studentInfo,
                'price' => $price,
                'order_id_old' => $orderIdOld,
                'order_id' => $order['id']
            );

            return $returnData;

        }catch (Exception $ex){
            $transaction->rollBack();
            return false;
        }
    }

    private function addClassTimes($data)
    {
        if(!isset($data['order_id_old']) && !empty($data['order_id']))
        {
            $this->WClassAccess->addClassByPurchase($data);
        }

        if(isset($data['order_id_old']))
        {
//            $data['price'] = round(($data['price'] * $data['ac_amount'] + $data['actual_fee']) / $data['class_num'], 2);
            $this->WClassAccess->updateClassByChangeProduct($data);
        }

        if(empty($data['order_id']))
        {
            $param = [
                'student_id' => $data['uid'],
                'instrument' => $data['instrument'],
                'time_type' => $data['time_type']
            ];
            
            $counts = $this->RClassAccess->countStudentGiftClass($param);

            if (!empty($counts))
            {
                $param = [
                    'class_num' => $data['class_num'],
                    'student_id' => $data['uid'],
                    'instrument' => $data['instrument'],
                    'time_type' => $data['time_type'],
                ];
                
                $this->WClassAccess->addGiftClassAmount($param);
   
            } else {
                
                $param = [
                    'student_id' => $data['uid'],
                    'type' => 2,
                    'instrument' => $data['instrument'],
                    'time_type' => $data['time_type'],
                    'name' => $data['pname'],
                    'price' => 4,
                    'class_num' => $data['class_num']
                ];
                
                $this->WClassAccess->addGiftClass($param);
        
            }
        }
    }

    public function getBuyInfoNew($student_id)
    {
        $ex = $this->RClassAccess->getBuyClassRoomInfo($student_id);
        $bill = $this->RClassAccess->getBuyClassEditHistoryInfo($student_id);
        $leftInfo = $this->RClassAccess->getBuyClassLeftInfo($student_id);
        $openId = $this->RStudentAccess->getOpenIdByStudentId($student_id);

        return [
            'ex' => $ex,
            'bill' => $bill,
            'leftInfo' => $leftInfo,
            'openId' => $openId
        ];
    }

    public function editBuyInfo($leftId)
    {

        $leftInfo = $this->RClassAccess->getClassLeftByLeftId($leftId);

        $userInfo = $this->RStudentAccess->getUserByLeftId($leftInfo->user_id);

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $this->WClassAccess->updateClassRoomByLeftId($leftId);

            $this->WClassAccess->updateClassEditHistoryByOrderId($leftInfo->order_id);

            $this->WClassAccess->saveClassEditHistoryByLeftInfo($leftInfo);

            $this->WClassAccess->saveRefundByLeftInfo($leftInfo);

            $this->WClassAccess->updateClassLeftInfoByLeftInfo($leftInfo);

            $this->WStudentAccess->editRefundUser($userInfo['id']);

            if (!empty($userInfo['sales_id'])) {
                $this->WChannelAccess->saveSalesTrade($userInfo, $leftInfo);
            }

            $transaction->commit();

            return json_encode(array('error' => ''));
        } catch (Exception $e) {
            $transaction->rollBack();
            return json_encode(array('error' => '执行失败,请联系管理员'));
        }
    }

    public function getOrderCount($request)
    {
        return $this->ROrderAccess->getOrderCount($request);
    }

    public  function getOrderList($request)
    {
        return $this->ROrderAccess->getOrderList($request);
    }

    public function getEditOrderPage($id)    
    {
        return $this->ROrderAccess->getEditOrderPage($id);
    }
    
    public function doUpdateCharge($request,$logid)
    {
        $item = $this->ROrderAccess->getProductById($request['productID']);

        $order = $this->WOrderAccess->addProductOrder($request['uid'], $request['openid'], $request['productID'], $item['name'], $item['newprice'], $request['type'], $item['class_num']);

        if($order > 0){
            $arr = array (
                'trade_id' => $order,
                'order_id_old'=> 0
            );

            Queue::produce($arr, 'async', 'trade_notify');

            LogService::OutputLog($logid,'insert',serialize($order),'课时充值');

            return 1;

        }else{

            return 0;
        }
    }

    public function doUpgradePackage($request, $logid)
    {
        $item = $this->ROrderAccess
            ->getProductById($request['productID']);

        $order = $this->WOrderAccess
            ->addUpgradeProductOrder($request['uid'], $request['openid'], $request['productID'], $item['name'], $item['newprice'], $request['type'], $item['class_num'], $request['orderID'], $request['money']);

        if ( $order > 0 )
        {
            $arr = array (
                'trade_id' => $order,
                'order_id_old' => $request['orderID']
            );

            Queue::produce($arr, 'async', 'trade_notify');
            LogService::OutputLog($logid,'insert',serialize($order),'套餐升级');

            return 0;

        } else {

            return 1;
        }
    }


    public function getMaker($money)
    {
        $code = '000000';
        for($i=0;$i<30;$i++){
            $code = mt_rand(100000,999999);
            $query = $this->ROrderAccess->getProductCoupon($code);
            if($query){
                break;
            }
        }

        if($this->WOrderAccess->addProductCoupon($code, $money))
        {
            return $code;
        }

        return 0;
    }


    public function changeProductPackage($id)
    {
        $data = $this->ROrderAccess->getAProductOrder($id);
        $list = $this->ROrderAccess->getProductOrderInfo($data['uid']);

        return [$data, $list];
    }

    public function doCancelOrder($request, $logid)
    {
        $data = $this->WOrderAccess->doCancelOrder($request);

        LogService::OutputLog($logid,'cancel',serialize($data),'取消订单');

        return 1;
    }

    public function doUpdateDone($id, $logid)
    {
        if($this->WOrderAccess->doUpdateDone($id)){

            $data = $this->ROrderAccess->getAProductOrder($id);
            
            $arr = array (
                'trade_id' => $data['orderNo'],
                'order_id_old'=> $data['old_order_id']
            );

            Queue::produce($arr, 'async', 'trade_notify');

            LogService::OutputLog($logid,'update',serialize($data),'更新订单');
        }

        return 1;
    }

    public function doUpdatePrice($request, $logid)
    {
        $data = $this->ROrderAccess->getAProductOrder($request['id']);

        if($this->WOrderAccess->doupdatePrice($data['id'], $request['price']))
        {
            //这里发送用户通知
//            $wechat = Yii::$app->wechat;
//            $arr = [
//                'touser'=>$data['open_id'],
//                'msgtype'=>'text',
//                'text'=>[
//                    'content'=>"您购买的课程套餐[" . $data["pname"]
//                        . "]已经修改了价格!\n\n"
//                        . "当前价格为:" . $data['actual_fee'] ."元\n\n"
//                        . "<a href='" . Yii::$app->params['base_url'] . "product/pay?pid=" . $data['pid'] . "&orderID=" . $data['orderNo'] . "'>点击立即支付</a>"
//                ],
//            ];
//
//            $wechat->sendMessage($arr);

            LogService::OutputLog($logid,'update',serialize($data),' 修改商品价格');

        }else{
            return 0;
        }

        $data['price'] = $request['price'];
        return $data;
    }

    public function doUpdateProducts($request, $logid)
    {
        $order = $this->ROrderAccess->getAProductOrder($request['order_id']);

        if($this->WOrderAccess->doUpdateProducts($request['order_id'], $request['old_id'],$request['class_num'], $request['actual_fee'])){
            
            LogService::OutputLog($logid,'update',serialize($order),'变更套餐信息');

            return 1;
        }
    }

    public function getProductType()
    {
        $data = $this->ROrderAccess->getProductOrderName();

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public function doUpProduct($request)
    {
        $class_info = $this->RClassAccess->getClassInfoByClassId($request['class_id']);

        if($class_info['type'] == 1){
            $long = 25 * 60;
        }elseif($class_info['type'] == 2){
            $long = 45 * 60;
        }else{
            $long = 50 * 60;
        }

        $remain_ids = $this->RClassAccess->getClassRoomId($class_info, $long);

        $remain_counts = count($remain_ids);

        $remain2_id = $this->RStudentAccess->getUserInstrumentClassTime($class_info);


        $transaction = Yii::$app->db->beginTransaction();

        try {

            if(!empty($remain_ids)){
                $this->WClassAccess->updateIsDeleted($remain_ids);
            }

            if(!empty($remain2_id)){
                $re = $this->WClassAccess->updateClassTimes($remain2_id['user_id']);
            }

            $this->WClassAccess->updateClassHistoryStatus(0,$request['class_id']);

            $amount = '-'.($remain_counts + $remain2_id['class_times']);

            $detail = serialize(array('class_id'=>$request['class_id']));

            $this->WClassAccess->insertClassEditHistory($class_info['student_id'],$amount,$class_info['instrument_id'],$class_info['type'],$detail);

            $transaction->commit();

//            LogService::OutputLog($logid, 'insert', '', '升级套餐');

            $data = array('error'=>'');

        } catch(Exception $e) {
            $transaction->rollBack();

            $data = array('error'=>'异常');

        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public function getUserRefund($leftId)
    {
        $leftInfo = $this->RClassAccess->getClassLeftByLeftId($leftId);
        $userInfo = $this->RStudentAccess->getUserByLeftId($leftInfo['user_id']);

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $this->WClassAccess->deleteUserRoom($leftId);

            $this->WClassAccess->deleteClassEditHistory($leftInfo['order_id']);

            $this->WClassAccess->addRefundClassEditHistory($leftInfo['user_id'], $leftInfo['order_id'], $leftInfo['ac_amount'], $leftInfo['price'], $leftInfo['instrument_id'], $leftInfo['time_type']);
            
            $this->WClassAccess->saveRefundByLeftInfo($leftInfo);

            $this->WStudentAccess->editRefundUser($leftInfo['user_id']);

            $this->WClassAccess->updateClassLeftInfoByLeftInfo($leftInfo);


            if (!empty($userInfo['sales_id']))
            {
                $this->WChannelAccess->saveSalesTrade($userInfo, $leftInfo);
            }

            $transaction->commit();
            return json_encode(array('error' => ''));

        } catch (Exception $e) {
            $transaction->rollBack();
            return json_encode(array('error' => '执行失败,请联系管理员'));
        }
    }

    public function delayProcessSalesTrade($msg)
    {
        $returnData = array (
            'error' => 0,
            'data' => []
        );

        $isSuccess = $this->ROrderAccess->checkOrderIsSuccess($msg['order_id']);

        if ($isSuccess > 0)
        {
            $price = $msg['price'] * 0.08;

            if (!empty($msg['order_id_old']))
            {
                $this->WChannelAccess
                    ->addSalesTradeUncashoutByChange($price, $msg['student_info']);

            } else {

                $this->WChannelAccess
                    ->addSalesTradeUncashoutByPurchase($price, $msg['student_info']);
            }

            $info = $this->RChannelAccess
                ->getSalesChannelFromcodeById($msg['student_info']['sales_id']);

            if (!empty($info['from_code'])) {
                $salesInfo = $this->RChannelAccess->getSalesChannelInfo($info['from_code']);

                $data = [
                    'uid' => $salesInfo['id'],
                    'from_uid' => $msg['student_info']['sales_id'],
                    'student_id' => $msg['student_info']['id'],
                    'student_name' => $info['nickname'],
                    'money' => $price * 0.5
                ];

                $this->WChannelAccess->addFatherSalesTradePurchase($data);
            }

            $returnData['data'] = [
                'sales_id' => $msg['student_info']['sales_id'],
                'student_name' => $msg['student_info']['nick'],
                'price' => $price
            ];

            return $returnData;
        } else {
            $returnData['error'] = '订单不存在';
            return $returnData;
        }
    }

    /**
     * 添加赠送课程
     */
    public function doGiveClass($message)
    {
        $RChannelAccess_rs = $this->RChannelAccess->getUserChannelInfoById($message['student_info']['channel_id']);
        if($RChannelAccess_rs['type'] == 2) {
            $userid = 0;
            $role = 2;
            $instrumentid = '';
            $goodsamount = 1;

            $studentid = $message['student_info']['id'];
            $class_info = $this->RClassAccess->getSendClassLeftInfo($studentid);
            $isRebuy = $this->ROrderAccess->checkUserIsBuy($studentid);
            if ($class_info && $isRebuy == 1) {
                //给被推荐人赠送课程
                //$goodsamount = 1;//赠送一课
                $historyId = $this->WClassAccess->addBuyClassGoods($userid, $role, $studentid, $class_info['instrument_id'], 0, 1, $goodsamount, $class_info['time_type'], 1, 1, 0, 0, 6, 0, '渠道送课');
                $instrumentid = $class_info['instrument_id'];
                $add_rs = $this->addClassHistory($historyId,$studentid,$instrumentid,$class_info['time_type'],$goodsamount);
                //给推荐人赠送课程,按照1/2/3/5/9规则赠送
                $channelinfo = $this->RStudentAccess->getChannelChannelIdSelf($message['student_info']['channel_id']);//获取推荐人的信息
                $channelcount = $this->RStudentAccess->getChannelCount($message['student_info']['channel_id']);//获取推荐人推荐成功并付款的个数
                if ($channelcount > 0) {
                    $m_amount = $channelcount % 5;
                    switch ($m_amount) {
                        case 1:
                            $goodsamount = 1;
                            break;
                        case 2:
                            $goodsamount = 2;
                            break;
                        case 3:
                            $goodsamount = 3;
                            break;
                        case 4:
                            $goodsamount = 5;
                            break;
                        case 0:
                            $goodsamount = 9;
                            break;
                    }
                }
                //开始给推荐人赠送
                $class_info_channel = $this->RClassAccess->getSendClassLeftInfo($channelinfo['id']);
                if($goodsamount > 0){
                    if ($class_info_channel) {
                        $historyId = $this->WClassAccess->addBuyClassGoods($userid, $role, $channelinfo['id'], $class_info_channel['instrument_id'], 0, 1, $goodsamount, $class_info_channel['time_type'], 1, 1, 0, 0, 6, 0, '渠道送课');
                        $instrumentid = $class_info_channel['instrument_id'];
                        $add_rss = $this->addClassHistory($historyId,$channelinfo['id'],$instrumentid,$class_info_channel['time_type'],$goodsamount);
                        //$add_rss = json_decode($add_rss,true);
                        if($add_rss['error'] == 0){
                            $add_rss['data']['channelcount'] = $channelcount;
                            $add_rss['data']['student_id_re'] = $studentid;
                        }
                        return array('error' => 0, 'data' => array('data1'=>$add_rs,'data2'=>$add_rss));
                    } else {
                        //如果没有查到体验课数据，就默认赠送45分钟钢琴课
                        $historyId = $this->WClassAccess->addBuyClassGoods($userid, $role, $channelinfo['id'], $instrumentid, 0, 1, $goodsamount, 2, 1, 1, 0, 0, 6, 0, '渠道送课');
                        $instrumentid = 1;
                        $add_rss = $this->addClassHistory($historyId,$channelinfo['id'],$instrumentid,$class_info_channel['time_type'],$goodsamount);
                        if($add_rss['error'] == 0){
                            $add_rss['data']['channelcount'] = $channelcount;
                            $add_rss['data']['student_id_re'] = $studentid;
                        }
                        return array('error' => 0, 'data' => $add_rss);
                    }
                }
            } 
        }
    }

    /**
     *
     */
    public function addClassHistory($historyId,$studentid,$instrumentid,$class_type,$amount)
    {
        if ($historyId > 0)
        {
            $price = 4;

            $count = $this->RClassAccess->getGivClassCount($studentid, $instrumentid, $class_type);
            $give_class_count = empty($count) ? '' : $count;


            if ($this->WClassAccess->addGiveClassTimes($studentid, $instrumentid, $class_type, $price, $amount, $give_class_count)) {

                $data['studentid'] = $studentid;
                $data['instrumentid'] = $instrumentid;
                $data['class_type'] = $class_type;
                $data['amount'] = $amount;
                return array('error' => 0, 'data' => $data);
            } else {
                return array('error' => '赠送课程失败');
            }
        } else {
            return array('error' => '添加送课历史失败');
        }
    }
}