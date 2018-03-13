<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/13
 * Time: 上午11:20
 */
namespace common\sources\write\classes;

use Yii;
use yii\db\ActiveRecord;

interface IClassAccess
{
    /**
     * @param $classid
     * @return mixed
     *
     * create by wangke
     * 根据$classid 修改错误课表的删除状态
     */
    public function UpdateFailClass($classid);

    /**
     * @param $classIds_str
     * @return mixed
     *
     * 根据$classIds_str（根据‘,’隔开）量删除错误课表的数据
     */
    public function delFailClassALL($classIds_str);

    /**
     * @param $request
     * @return mixed
     *
     * 将数据插入class_record表中 课表信息的未填写课单
     */
    public function addClassRecord($request, $time);

    /**
     * @param $class
     * @param $status_bit
     * @param $time_updated
     * @return mixed
     * create by wangke
     *
     * 修改联系乐谱的状态
     */
    public function updateClassRoomContact($class, $status_bit, $time_updated);

    /**
     * @param $data
     * @param $chat_token
     * @param $accessToken
     * @return mixed
     * create by wangke
     *
     * 监控课程 需要user.caht_token=null    同步信息
     */
    public function updateClassSyn($data, $chat_token, $accessToken);

    /**
     * @param $recordInfo
     * @param $time_send
     * @param $class_left
     * @param $class_used
     * @return mixed
     * create by wangke
     *
     * 发送消息到微信后的记录
     */
    public function saveClassRecord($recordInfo, $time_send, $class_left, $class_used);

    /**
     * @param $recordId
     * @return mixed
     * @created by Jhu
     * 初始化课单发送时间
     */
    public function initRecordTimeSendById($recordId);

    /**
     * @param $data
     * @return mixed
     * @created by Jhu
     * 购买课程添加历史
     */
    public function addClassHistoryFromPurchase($data);

    /**
     * @param $leftId
     * @return mixed
     * @created by Jhu
     * 更换套餐删除已排未上的课
     */
    public function deleteClassFromChangeGoods($leftId);

    /**
     * @param $orderIdOld
     * @return mixed
     * @created by Jhu
     * 更换套餐删除已排未上历史
     */
    public function deleteClassHistoryFromChageGoods($orderIdOld);

    /**
     * @param $data
     * @return mixed
     * @created by Jhu
     * 更换套餐减课历史
     */
    public function reduceClassHistoryFromChangeGoods($data);

    /**
     * @param $data
     * @return mixed
     * @created by Jhu
     * 用户购买套餐添加课时
     */
    public function addClassByPurchase($data);

    /**
     * @param $data
     * @return mixed
     * @created by Jhu
     * 换套餐
     */
    public function updateClassByChangeProduct($data);

    /**
     * @param $leftId
     * @return mixed
     * create by wangke
     * 退费  修改class_room表
     */
    public function updateClassRoomByLeftId($leftId);

    /**
     * @param $order_id
     * @return mixed
     * create by wangke
     * 退费 修改class_eidt_history修改
     */
    public function updateClassEditHistoryByOrderId($order_id);

    /**
     * @param $leftInfo
     * @return mixed
     * create by wangke
     * 退费  报存class_eidt_history 根据class_left的某一条信息
     */
    public function saveClassEditHistoryByLeftInfo($leftInfo);

    /**
     * @param $leftInfo
     * @return mixed
     * create by wangke
     * 退费  保存refund 根据class_left的某一条信息
     */
    public function saveRefundByLeftInfo($leftInfo);

    /**
     * @param $leftInfo
     * @return mixed
     * create by wangke
     */
    public function updateClassLeftInfoByLeftInfo($leftInfo);

    /**
     * @param $class_id
     * @return mixed
     * @author xl
     * 更新课程为删除课
     */
    public function updateClassTimeDelete($class_id);

    /**
     * @param $userId
     * @param $role
     * @param $historyId
     * @return mixed
     * @author xl
     * 更新课程历史记录
     */
    public function updateHistory($userId, $role, $historyId);

    /**
     * @param $leftId
     * @return mixed
     * @author xl
     * 进入错误课表后修改left
     */
    public function addClassTimesByLeftId($leftId);

    /**
     * @param $classId
     * @param $type
     * @return mixed
     * @author xl
     * 添加到错误课表
     */
    public function addClassFail($classId, $type);

    /**
     * @param $classIdList
     * @return mixed
     * @author xl
     * 更新课程为发送过
     */
    public function updateClassTimeSend($classIdList);

    /**
     * @param $param
     * @return mixed
     * @created by Jhu
     * 添加赠送课数量
     */
    public function addGiftClassAmount($param);

    /**
     * @param $param
     * @return mixed
     * @created by Jhu
     * 新增赠送课
     */
    public function addGiftClass($param);

    /**
     * @param $class_id
     * @return mixed
     * @author xl
     * 更新课程老师ID为0
     */
    public function updateClassTeacherId($class_id);

    /**
     * @param $class_id
     * @param $student_id
     * @param $teacher_id
     * @param $user_id
     * @param $role
     * @param $type
     * @param $time_created
     * @return mixed
     * @author xl
     * 进入错入课表日志
     */
    public function intoFailLog($class_id, $student_id, $teacher_id, $user_id, $role, $type);


    /**
     *  修改赠送课,如果不存在添加存在赠送
     * @param $uid
     * @param $instrumentId
     * @param $timeType
     * @param $price
     * @param $amount
     * @param $count
     * @return  mixed
     * create by  wangkai
     */
    public function addGiveClassTimes($uid, $instrumentId, $timeType, $price, $amount, $count);

    /**
     * @param $class_id
     * @param $ahead
     * @param $defer
     * @return mixed
     * create by wangke
     * 回访组合弹窗 排课信息 调整时间
     */
    public function doChangeClassTime($class_id, $ahead, $defer);
    
    /*
     *  添加学生分享记录
     * create by sjy
     * 
     */
    public function addStudentUserShare($class_id,$openid,$is_back,$is_free);

    /**
     * @return mixed
     * @author xl
     * 锁表
     */
    public function lockStudentFixTime();

    /**
     * @return mixed
     * @author xl
     * 解锁表
     */
    public function unLockStudentFixTime();
}