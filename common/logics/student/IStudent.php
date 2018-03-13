<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/19
 * Time: 下午2:34
 */
namespace common\logics\student;

use Yii;
use yii\base\Object;

interface IStudent {

    /**
     * @param $keyword
     * @return mixed
     * @created by Jhu
     * 搜索渠道
     */
    public function getChannelListByKeyword($keyword);

    /**
     * @param $channeId
     * @param $openId
     * @return mixed
     * @created by Jhu
     * 绑定渠道
     */
    public function bindChannel($logid, $channelId, $openId);

    /**
     * @param $xml
     * @return mixed
     * @created by Jhu
     * 用户关注
     */
    public function dealStudentSubscribe($xml);

    /**
     * @param $xml
     * @return mixed
     * @created by Jhu
     * 用户扫码分销渠道统计
     */
    public function dealStudentScan($xml);

    /**
     * @param string $keyword
     * @return mixed
     * create by wangke
     * 查询全部复购的条数
     */
    public function countAllPurchasePage($keyword, $type);

    /**
     * @param $keyword
     * @param $num
     * @return mixed
     * create by wangke
     * 查询复购的全部客户列表
     */
    public function queryAllPurchaseList($keyword, $type, $num);

    /**
     * @param $keyword
     * @param $start
     * @param $end
     * @return mixed
     * create by wangke
     * 统计分配复购的条数
     */
    public function countAllotPurchase($keyword, $start, $end);

    /**
     * @param $keyword
     * @param $start
     * @param $end
     * @param $num
     * @return mixed
     * create by wangke
     * 得到分配复购的信息
     */
    public function getAllotPurchaseList($keyword, $start, $end, $num);

    /**
     * @param $kefuId
     * @return mixed
     * create by wangke
     * 为复购组分配复购客服
     */
    public function distributeUserAccountOne($logid, $userId,$kefuId);

    /**
     * @param $introduce
     * @param $start
     * @param $end
     * @return mixed
     * create by wangke
     * 管理视角 查询新用户的条数
     */
    public function countAllotNewUser($introduce, $start, $end);

    /**
     * @param $introduce
     * @param $start
     * @param $end
     * @param $num
     * @return mixed
     * create by wangke
     * 管理视角  新用户列表
     */
    public function getAllotNewUserList($introduce, $start, $end, $num);

    /**
     * @param $logid
     * @param $userId
     * @param $kefuId
     * @return mixed
     * create by wangke
     * 为新用户分配新签客服
     */
    public function distributeNewUser($logid,$userId,$kefuId);

    /**
     * @param $keyword
     * @return mixed
     * create by wangke
     * 管理视角 未付费再分配
     */
    public function countAgainAllotNotPay($keyword);

    /**
     * @param $keyword
     * @param $num
     * @return mixed
     * create by wangke
     * 管理视角 未付费再分配列表
     */
    public function getAgainAllotNotPayList($keyword, $num);

    /**
     * @param $keyword
     * @return mixed
     * create by wangke
     * 管理视角 未复购再分配条数
     */
    public function countAgainAllotNotPurchase($keyword);

    /**
     * @param $keyword
     * @param $num
     * @return mixed
     * create by wangke
     * 管理视角 未复购再分配列表
     */
    public function getAgainAllotNotPurchaseList($keyword, $num);

    /**
     * @param $logid
     * @param $userId
     * @param $kefuId
     * @return mixed
     * create by wangke
     * 管理视角 未复购再分配 分配新签客服
     */
    public function distributeNotPurchase($logid,$userId,$kefuId);

    /**
     * @param $keyword
     * @param $btn
     * @param $start
     * @param $end
     * @return mixed
     * create by wangke
     * 管理视角 未跟进再分配的条数
     */
    public function countAgainAllotNotFollow($btn, $keyword, $start, $end, $kefu_id);

    /**
     * @param $keyword
     * @param $btn
     * @param $start
     * @param $end
     * @param $num
     * @return mixed
     * create by wangke
     * 管理视角 未跟进再分配的列表信息
     */
    public function getAgainAllotNotFollowList($btn, $keyword, $start, $end, $num,$kefu_id);

    /**
     * @param $type
     * @param $kefuId
     * @param $area
     * @param $keyword
     * @param $intention
     * @param $time_type
     * @param $start
     * @param $end
     * @return mixed
     * create by wangke
     * 管理视角 公盘用户的条数
     */
    public function countPublicUserPage($type, $kefuId, $area , $keyword , $intention , $time_type, $start , $end);

    /**
     * @param $type
     * @param $kefuId
     * @param $area
     * @param $keyword
     * @param $intention
     * @param $time_type
     * @param $start
     * @param $end
     * @param $num
     * @return mixed
     * create by wangke
     * 管理视角 公盘用户的列表信息
     */
    public function getPublicUserList($type, $kefuId, $area, $keyword, $intention, $time_type, $start, $end,$num);

    /**
     * @param $userId
     * @param $kefuId
     * @return mixed
     * create by wangke
     * 管理者视角 公盘用户分配客服
     */
    public function distributePublicUserKefu($logid,$userId,$kefuId);

    /**
     * @param $logid
     * @param $userId
     * @param $kefuId
     * @return mixed
     * create by wangke
     * VIP微课 全部用户 分配客服
     */
    public function distributeAllUserKefu($userId,$kefuId);

    /**
     * 绑定客服
     */
    public function doBindKefu($request, $logid);

    /*
     * 导出excel
     */
    public function getAccountExport($request);

    /**
     * 用户海报
     * @param  $openid
     * @return array
     */
    public function getHaibao($openid);

    /**
     * 获取所有初始化用户
     */
    public function getAllUserIndex();

    /**
     * 获取所有初始化用户页面
     * @param  $keyword
     * @param  $type
     * @return int
     */
    public function getAllUserPage($keyword, $type);

    /**
     * 获取所有初始化用户列表
     * @param  $request
     * @return array
     */
    public function getAllUserList($request);

    /**
     * APP 页面
     * @param  $status
     * @param  $search 
     * @return int
     */
    public function getAppsCount($status, $search);
    /**
     * APP 列表
     * @param  $status
     * @param  $search
     * @param  $page
     * @return array
     */
    public function getAppsList($status, $search, $page);

    /**
     * 处理APP端信息
     * @param $request
     * @param $logid
     * @return int
     */ 
    public function editAppsDeal($request, $logid);

    /**
     * 删除体验
     * @param $applyId
     * @param $logid
     * @return int
     */
    public function deleteExperience($applyId, $logid);

    /**
     * 申请体验的用户数量
     * @param   $is_called
     * @param   $search
     * @return  count
     */
    public function getApplysCount($is_called, $search);

    /**
     * 申请体验的用户列表
     * @param   $is_called
     * @param   $search
     * @param   $page
     * @return  array
     */
    public function getApplysList($is_called, $search, $page);

    /**
     * 标记用户
     * @param $applyId
     * @param $logid
     * @return int
     */
    public function experienceMark($applyId, $logid);


    /**
     * 查看空闲老师
     * @param  $request array
     */
    public function actionTeacherAvailable($request);

    /**
     * 查询学生偏好老师
     */
    public function userLikeTeacher($student_id);

    /**
     * 删除用户
     */
    public function deleteUser($studentId, $logid);

    /**
     * 标记高位用户
     * @param $uid
     * @param $high 
     * @param $logid
     * 标记高位用户
     */
    public function markHighRiskUser($uid,$high,$logid);

    /**
     * 获的城市列表
     * @param $pid
     * @return array
     */
    public function getCity($pid);

    /**
     *  编辑学生信息(后)
     *  @param  $request  array
     *  @return int
     */
    public  function  editStudentInfo($request='', $logid='');

    /** 
     * 编辑学生信息页面
     * @param    $openID  str
     * @return   array
     */
    public function editStudentPage($openID);

    /**
     * 学生详细信息
     * @param $request
     * @return array
     */
    public function getStudentList($request);


    /**
     * 学生主页
     * @param   $request
     * @return  int
     */
    public function getStudentPage($request);

    /**
     * @return mixed
     * create by wangke
     * 学生档案中所有学生的固定课条数
     */
    public function getStudentALLFixTimes();


    /**
     * 未排课名单
     * @param  $type
     * @return int
     */
    public function getNoClassPurchasePage($type);

    /**
     * 待跟进名单
    */   
    public function getTodoPurchaseList($keyword,$timeDay);

    /**
     * @param $class_id
     * @return mixed
     * create by wangke
     * 得到课程信息
     */
    public function getClassRoomInfoByClassId($class_id);

    /**
     * @param $class_id
     * @param $ahead
     * @param $defer
     * @return mixed
     * create by wangke
     * 回访组合弹窗 排课信息 调整时间
     */
    public function doChangeClassTime($class_id,$ahead,$defer);
    
    /**
     * @param $uid
     * @param $keyword
     * @param $offset
     * @param $limit
     * @return mixed
     * @created by Jhu
     * 复购销售获取绑定自己的所有用户
     */
    public function getAllUsersByKefuId($uid,$keyword,$offset,$limit);


    /**
     * 获取未跟进再分配（复购）
     * @param  $type
     * @param  $studentName
     * @param  $distributionTime
     * @param  $saleId
     * @return  array
     * create by  wangkai
     */
    public function getPurchaseUserAgainAllotNotFollowPage($type, $studentName, $distributionTime, $saleId);

    /**
     * 获取未跟进再分配列表（复购）
     * @param  $type
     * @param  $studentName
     * @param  $distributionTime
     * @param  $saleId
     * @param  $num
     * @return  array
     * create by  wangkai
     */
    public function getPurchaseUserAgainAllotNotFollowList($type, $studentName, $distributionTime, $saleId, $num);

}