<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/10
 * Time: 下午7:49
 */
namespace common\logics\account;

use Yii;
use yii\base\Object;

interface IAccount
{
    /**
     * @return mixed
     * create by wangke
     * 分配复购组 显示客服列表
     */
    public function getKefuList();

    /**
     * @return mixed
     * create by wangke
     * 分配新签用户 获取新签客服列表
     */
    public function getNewSignKefuList();

    /**
     * @return mixed
     * create by wangke
     * 管理视角 公盘用户 获取需要显示的客服信息
     */
    public function getPublicUserKefuInfo();

    /**
     * @return mixed
     * create by wangke
     * VIP微课 所有用户 所有role=5微课客服
     */
    public function getAllUserKefuInfo();

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
     * VIP微课 员工管理  账号唯一性
     * @param $userName
     * @return mixed
     * create by wangke
     */
    public function validateUniqueUsername($userName);

    /**
     * VIP微课 员工管理 邮箱唯一性
     * @param $userName
     * @return mixed
     * create by wangke
     */
    public function validateUniqueEmail($email);


    /**
     * @param $userName
     * @param $nick
     * @param $email
     * @param $type
     * @param $telephonename
     * @param $telephonepwd
     * @return mixed
     * create by wangke
     * VIP微课 添加员工操作
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
     * VIP微课 员工列表
     */
    public function getEmployeList($keyword, $status, $num);

    /**
     * @param $logid
     * @param $kefuid
     * @return mixed
     * create by wangke
     * 销售管理 删除客服
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
     * 根据客服ID修改客服自动接待的人数
     * @param   $kefuId
     * @return  array
     * create by  wangkai
     */
    public function editKefuReceptionGrowth($kefuId);

    /**
     * @param $kefuid
     * @return mixed
     * create by wangke
     * 根据客服id得客服信息
     */
    public function getAccountInfoByKefuId($kefuid);

    /**
     * @param $req
     * @return mixed
     * @created by Jhu
     * 渠道系统登录
     */
    public function doChannelLogon($req);

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
     * @param $kefuid
     * @param $time
     * @param $type
     * @return mixed
     * create by wangke
     * VIP微课 员工管理 显示日课表
     */
    public function getEmployeWorkTime($kefuid, $time, $type);

    /**
     * @param $kefuid
     * @param $time
     * @param $type
     * @return mixed
     * create by wangke
     * VIP微课 员工管理 显示周课表
     */
    public function getEmployeWeekTable($kefuid);

    /**
     * @param $request
     * @return mixed
     * create by wangke
     * 员工管理添加日课表
     */
    public function addEmployeWorkTime($request);

    /**
     * @param $request
     * @return mixed
     * create by wangke
     * 员工管理添加周课表
     */
    public function addEmployeWeekTable($request);

    /**
     * 体验课报表的微课客服列表
     * @return mixed
     * create by wangke
     */
    public function getExClassReportKefuInfo();
}
