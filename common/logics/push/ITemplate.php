<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/10
 * Time: 下午2:22
 */
namespace common\logics\push;


interface ITemplate {

    /**
     * @param $msg
     * @return mixed
     * @created by Jhu
     * 给学生公众号发送模版消息
     */
    public function sendStudentTemplate($msg);

    /**
     * @param $msg
     * @return mixed
     * @created by Jhu
     * 给推广大使公众号发送模版消息
     */
    public function sendChannelTemplate($msg);

    /**
     * @param $msg
     * @return mixed
     * @created by Jhu
     * 给妙克人才基地公众号发送模版消息
     */
    public function sendTeacherTemplate($msg);

    /**
     * @param $msg
     * @return mixed
     * @created by Jhu
     * 处理发送课单模版消息
     */
    public function dealStudentRecordTemplate($msg);

    /**
     * @param $name
     * @param $salesId
     * @return mixed
     * @created by Jhu
     * 处理用户购买渠道分成发送模版消息
     */
    public function dealChannelPurchaseTemplate($data);


    /**
     * @param $name
     * @param $salesId
     * @return mixed
     * @created by Jhu
     * 处理用户购买渠道分成发送给二级渠道的模版消息
     */
    public function dealTwoChannelPurchaseTemplate($data);

    /**
     * @param $message
     * @return mixed
     * @created by Jhu
     * 处理渠道提成发送模版消息
     */
    public function dealChannelIncomeTemplate($message);

    /**
     * @param $message
     * @return mixed
     * @created by Jhu
     * 处理取消课给用户发送模版消息
     */
    public function dealCancelClassTemplate($request);

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 给老师公众号发送课程表
     */
    public function sendTeacherClass($request);

    /**
     * @param $request
     * @return mixed
     * @created by Jhu
     * 处理发送学生模版消息
     */
    public function dealSendStudentTemplate($request);

    /**
     * @param $classId
     * @return mixed
     * @created by Jhu
     * 发送排课
     */
    public function sendClassMessage($classId);

    /**
     * @param $request
     * @return mixed
     * @created by Jhu
     * 赠送课发送模版消息
     */
    public function dealSendGiveClass($request, $uid);

    /**
     * @param $data
     * @return mixed
     * @created by Jhu
     * 客服处理投诉消息发送模版消息
     */
    public function dealSendComplainMessage($data);

    /**
     * @param $data
     * @return mixed
     * @created by Jhu
     * 学生注册成功发送渠道收入模版消息
     */
    public function dealSendMoneyMessage($data);

    /**
     * 推广大使针对没有关注自己的用户发送模板消息
     * @param   $request
     * @return  array
     * create by  wangkai
     */
    public function dealSendChannelTemplate($request);

    /**
     * @param $msg
     * @return mixed
     * @created by Jhu
     * 群发VIP微课模版消息功能
     */
    public function sendChannelTemplateAll($msg);

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 两小时内取消课给老师发送模板消息
     */
    public function sendTeacherCancelClass($msg, $teacher_name, $open_id);

    /**
     * @param $msg
     * @param $open_id
     * @return mixed
     * @author xl
     * 两小时内添加课给老师发送模板消息
     */
    public function sendTeacherAddClass($time_class, $time_end, $teacher_name, $open_id);

    /**
     * @param $time_class
     * @param $time_end
     * @param $time_class_new
     * @param $time_end_new
     * @param $teacher_name
     * @param $open_id
     * @return mixed
     * @author xl
     * 两小时内修改课程信息给老师发送模板
     */
    public function sendTeacherEditClass($time_class, $time_end, $time_class_new, $time_end_new, $teacher_name, $open_id);

    /**
     * @param $month
     * @return array
     * @created by YH
     * 老师服务号薪资明细推送
     */
    public function sendTeacherSalaryDetail($month);

    /**
     * @param $month
     * @return array
     * @created by HL
     * 老师服务号薪资明细推送黄龙
     */
    public function sendTeacherSalaryDetailById($month,$push_ids);

    /** 发送学生通过海报关注VIP陪练,把模板消息发送给微课老师
     * @param  $open_id
     * @return  array
     * create by  wangkai
     * create time  2017/4/17
     */
    public function sendStudentSubscribeTemplate($open_id);

    /**
     * 发送预约体验课发送的模板消息
     * @param $open_id
     * @param $class_time  上课时间
     * @return  array
     * create by  wangkai
     * create time  2017/4/12
     */
    public function sendAttendExClassTemplate($open_id, $class_time);

    /**
     * 用户推荐成功购买发送模板消息
     */
    public function dealChannelGiveTemplate($data);

    /**
     * 用户推荐成功首次体验课完成发送红包
     */
    public function dealChannelGiveRedpack($msg);

    /**
     * 发送赠送课程模板
     * @param   $data array
     * @return  array
     * create time  2017/5/9
     */
    public function sendGiveClassMessage($data);

    /**
     * 修改课程发送的模板消息
     * @param   $data
     * @return  array
     * create by  wangkai
     * create time  2017/5/4
     */
    public function sendCurriculumModificationToTeacher($data);


    /**
     * 添加课程之后发送的模板消息
     * @param  $data
     * @return  array
     * create by  wangkai
     * create time  2017/5/8
     */
    public function sendCurriculumAddToTeacher($data);

    /**
     * 取消课程之后发送的模板消息
     * @param  $data
     * @return  array
     * create by  wangkai
     * create time  2017/5/8
     */
    public function sendCurriculumCancelToTeacher($data);
}