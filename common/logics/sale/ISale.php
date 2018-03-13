<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/12
 * Time: 下午4:10
 */
namespace common\logics\sale;

use Yii;
use yii\base\Object;

interface ISale
{

    public function getAllSales();

    /**
     * @return mixed
     * created by hujiyu
     * 根据销售ID获取分配名单总数
     */
    public function countDistributeUser($keyword);

    /**
     * @param $num
     * @return mixed
     * created by hujiyu
     * 根据销售ID获取分配名单单页用户列表
     */
    public function getDistributeUserList($num, $keyword);

    /**
     * @param $studentid
     * @return mixed
     * created by hujiyu
     * 根据学生ID获取渠道名
     */
    public function getChannelNameByStudentId($studentid);

    /**
     * @param $request
     * @return mixed
     * created by hujiyu
     * 添加回访纪录
     */
    public function addUserHistory($request);

    /**
     * @param $request
     * @return mixed
     * created by hujiyu
     * 添加回访用户基本信息
     */
    public function addUserArchive($request);

    /**
     * @return mixed
     * created by hujiyu
     * 体验用户名单总数
     */
    public function countExUser($keyword, $time);

    /**
     * @return mixed
     * created by hujiyu
     * 体验用户名单列表
     */
    public function getExUserList($num, $keyword, $time);

    /**
     * @param $time
     * @return mixed
     * created by hujiyu
     * 带跟进名单列表
     */
    public function getTodoList($time);

    /**
     * 所有未付费用户的条数
     * @author 王可
     * */
    public function getNotPayALlUsersCount($kefuid, $indention, $area, $exclass, $beforekeyword, $keyword);

    /**
     * 获得搜有的未付费用户
     * @author   王可
     */
    public function getNotPayAllUsers($kefuid, $num, $indention, $area, $exclass, $beforekeyword, $keyword);

    /**
     * 一条未付费用户的详细信息
     * @author 王可
     * */
    public function getNotPayUserDetailInfo($userId);

    /**
     * 回访记录条数处理
     * @author 王可
     * */
    public function getNotPayUserVisitListCount($studentid);

    /**
     * 修饰已付款的用户列表，并且传递参数(配合分页+搜索)
     * @User：王锴
     * @Time: 16/12/13 21:38
     * @param   $type
     * @param   $keyword
     * @param   $num
     * @param   $datanum
     * @return array
     */
    public function getAllPayUserList($type, $keyword, $num, $datanum);

    /**
     * 统计用户数量(搜索，查询都将统计)
     * @User：王锴
     * @Time: 16/12/13 21:38
     * @param   $keyword
     * @param   $type
     * @param   $datanum
     * @return  count
     */
    public function selfPayUserPage($keyword = ' ', $type = 0, $datanum = '');


    /**
     * 计算新签用户
     * @User：王锴
     * @Time: 16/12/13 21:38
     * @param   $keyword
     * @param   $num
     * @param   $datanum
     * @return  array
     */
    public function getAllNewPayUserList($keyword, $num, $datanum);

    /**
     * 搜索，剩余数量充当条件
     * @User：王锴
     * @Time: 16/12/13 21:38
     * @param   $keyword
     * @param   $num
     * @param   $datanum
     */
    public function getSelfPayUserSql($keyword, $type, $datanum);

    /**
     * @param $keyword
     * @return mixed
     * create by wangke
     * 计算待排课的条数
     */
    public function countPayToClassUser($keyword);

    /**
     * @param $num
     * @param $keyword
     * @return mixed
     * create by wangke
     * 查询待排课新签列表
     */
    public function getPayToClassUserList($num, $keyword);

    /**
     * @param $studentid
     * @return mixed
     * create by wangke
     * 获得添加回访中的用户意向
     */
    public function getUserIntentionInAddVisit($studentid);

    /**
     * @return string
     * create by wangke
     * 获取购买记录
     */
    public function getBuyInfoNew($studentid);

    /**
     * @param $userName
     * @param $nick
     * @param $email
     * @param $type
     * @return mixed
     * create by wangke
     * 销售管理中 添加一个客服
     */
    public function addCourseKefuInKefuManagement($logid, $userName, $nick, $email, $type, $telephonename, $telephonepwd);

    /**
     * @param $userName
     * @param $nick
     * @param $email
     * @param $type
     * @param $telephonename
     * @param $telephonepwd
     * @return mixed
     * create by wangke
     * VIP微课 员工管理 添加员工操作
     */
    public function addEmployeManagement($request);

    /**
     * @param $logid
     * @param $kefuid
     * @param $nick
     * @param $email
     * @param $type
     * @param $telephonename
     * @param $telephonepwd
     * @return mixed
     * create by wangke
     * 销售管理中 修改一个客服
     */
    public function updateCourseKefuInKefuManagement($request);

    /**
     * @param $keyword
     * @param $timestart
     * @param $timeend
     * @return mixed
     * create by wangke
     * 销售管理的条数
     */
    public function countSalesKefu($keyword, $timestart, $timeend);

    /**
     * @param $keyword
     * @param $timestart
     * @param $timeend
     * @param $num
     * @return mixed
     * create by wangke
     * 销售管理的列表
     */
    public function getSalesKefuList($keyword, $timestart, $timeend, $num);

    /**
     * @param $keyword
     * @param $timestart
     * @param $timeend
     * @param $num
     * @return mixed
     * create by wangke
     * VIP 微课的员工列表
     */
    public function getEmployeList($keyword, $status, $num);

    /**
     * @param $logid
     * @param $kefuid
     * @return mixed
     * create by wangke
     * 销售管理/VIP微课 删除客服
     */
    public function deleteKefu($logid, $kefuid, $deltype);

    /**
     * @param $kefuid
     * @return mixed
     * create by wangke
     * VIP微课  开启
     */
    public function openEmploye($kefuid);

    /**
     * @param $uid
     * @param $keyword
     * @param $offset
     * @param $limit
     * @return mixed
     * @created by Jhu
     * 新钱销售获取绑定自己的所有用户
     */
    public function getAllUsersByKefuId($uid, $keyword, $offset, $limit);

    /**
     * @param $telephone
     * @return mixed
     * create by wangke
     * 根据手机号查询学生ID
     */
    public function getUseridByTelephone($telephone);

    /**
     * @param $kefuid
     * @return mixed
     * create by wangke
     * 根据客服id得客服信息
     */
    public function getAccountInfoByKefuId($kefuid);

    /**
     * 销售渠道待跟进名单
     * @param $start
     * @param $end
     * @return  array
     * create by  wangkai
     */
    public function getChannelTodoList($start, $end);

    /**
     * @param $keyword
     * @param $timestart
     * @param $timeend
     * @return mixed
     * create by wangke
     * VIP微课的员工管理
     */
    public function countEmploye($keyword, $status);

    /**
     * 获取推广效果数量
     * @param $start
     * @param $end
     * @param $uid
     * @return  str
     * create by  wangkai
     * create time  2017/4/11
     */
    public function getPromotionEffectPage($start, $end, $uid);

    /**
     * 获取推广效果列表
     * @param $start
     * @param $end
     * @param $uid
     * @return  array
     * create by  wangkai
     * create time  2017/4/11
     */
    public function getPromotionEffectList($start, $end, $num, $uid);

    /**
     * 转介绍名单数量
     * @param   $type
     * @param   $keyword
     * @param   $kefuId
     * @param   $time
     * @param   $kefu
     * @param   $isCheck
     * @return  string
     * create by  wangkai
     * create time  2017/5/10
     */
    public function getUserIntroduceCount($type, $keyword, $kefuId, $start, $end, $kefu, $isCheck);

    /**
     * 转介绍名单列表
     * @param   $type    判断是否是管理员
     * @param   $keyword
     * @param   $kefuId
     * @param   $time
     * @param   $kefu
     * @param   $isCheck 是否买单  0 无视 1 买单  2 没有买单
     * @return  array
     * create by  wangkai
     * create time  2017/5/10
     */
    public function getUserIntroduceList($type, $num, $keyword, $kefuId, $start, $end, $kefu, $isCheck);

    /**
     * 获取所有新签用户列表
     * @return  array
     * create by  wangkai
     * create time  2017/5/11
     */
    public function getNewAccountInfo();
}
