<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/10
 * Time: 上午10:58
 */
namespace common\sources\read\account;

use Yii;
use yii\db\ActiveRecord;

interface IAccountAccess
{
    /**
     * @return mixed
     * create by wangke
     * 得到客服列表
     */
    public function getKefuList();

    /**
     * @param $kefuId
     * @return mixed
     * create by wangke
     * 查询一条客服信息
     */
    public function getUserAccountOne($kefuId);

    /**
     * @param $kefuId
     * @return mixed
     * create by wangke
     * 得到
     */
    public function getAllUserUserAccountOne($kefuId);

    /**
     * @return mixed
     * create by wangke
     * 分配新签用户 获取新签客服列表
     */
    public function getNewSignKefuList();

    /**
     * @param $kefuId
     * @return mixed
     * create by wangke
     * 查询新签客服姓名
     */
    public function getNewSignKefuNick($kefuId);

    /**
     * @return mixed
     * create by wangke
     * 管理视角 获取新签和复购客服
     */
    public function getReKefuList();

    /**
     * @return mixed
     * create by wangke
     * VIP微课 所有用户 role=5的客服
     */
    public function getAllUserKefuInfo();


    /**
     * @param $kefuId
     * @return mixed
     * create by wangke
     * 根据客服id查询客服role
     */
    public function getKefuRoleByKefuid($kefuId);

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
     * VIP微课 员工信息列表
     */
    public function getEmployeList($keyword, $status, $num);


    /**
     * 获取客服列表ID和昵称
     * @return array
     * create by wk
     */
    public function getUserAccountNickName();


    /**
     *  获取日课表正在上班的客服ID
     * @param $time        时间范围
     * @return  array
     * create by  wangkai
     */
    public function getAtWorkKefuId($time);


    /**
     * 查询可以被分配的客服
     * @param $time        时间范围
     * @param $workinfo
     * @return  str
     * create by  wangkai
     */
    public function getAtWorkKefuInfo($time, $workinfo);

    /**
     * 查询可以被分配的客服
     * @param $time        时间范围
     * @param $workinfo
     * @return  str
     * create by  wangkai
     */
    public function getAtWorChannelKefuInfo($time, $workinfo);

    /**
     *  查看周可分配客户
     * @param   $week
     * @param   $time
     * @param   $exculudeid
     * @return  str
     * create by  wangkai
     */
    public function getAtWeekWorkCount($week, $time, $exculudeid);

    /**
     * 获取周正在上班的客服ID
     * @param $week
     * @param $timebit
     * @param $excludeid
     * @return  array
     * create by  wangkai
     */
    public function getAtWeekWorkKefuId($week, $timebit, $excludeid);

    /**
     * 获取周正在上班的渠道客服ID
     * @param $week
     * @param $timebit
     * @param $excludeid
     * @return  array
     * create by  wangkai
     */
    public function getAtWeekWorkChannelKefuId($week, $timebit, $excludeid);

    /**
     * 获取当天最大的增长数量
     * @param  $worktime  当天时间
     * @return  str
     * create by  wangkai
     */
    public function getMaxNumber($worktime);

    /**
     * 获取全部被安排日课表的客服信息
     * @param  $time
     * @return  array
     * create by  wangkai
     */
    public function getAllAtWorkKefuId($time);

    /**
     * 获取全部渠道被安排日课表的客服信息
     * @param  $time
     * @return  array
     * create by  wangkai
     */
    public function getAllAtWorkChannelKefuId($time);

    /**
     * 根据用户ID查找所属的客服昵称
     * @param  $openid
     * @return  str
     * create by  wangkai
     */
    public function getKefuNickByOpenId($openid);

    /**
     * 根据OpenId 查找复购用户昵称
     * @param   $openid
     * @return  str
     * create by  wangkai
     */
    public function getReKefuNickByOpenId($openid);

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
     * 获取所有复购销售信息
     * @param $keyword
     * @return  array
     * create by  wangkai
     */
    public function getPurChaseUserInfo($keyword);

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
     * @param $timeDay
     * @return mixed
     * create by wangke
     * VIP 微课 员工管理 日课表查询
     */
    public function getEmployeDayTime($kefuid, $timeDay);

    /**
     * @param $kefuid
     * @param $timeDay
     * @return mixed
     * create by wangke
     * VIP 微课 员工管理 周课表查询
     */
    public function getEmployeWeekTime($kefuid, $week);

    /**
     * @param $kefuid
     * @return mixed
     * create by wangke
     * 员工管里周课表显示 1
     */
    public function getFixedTimeBitList($kefuid);

    /**
     * @param $kefuid
     * @return mixed
     * create by wangke
     * 员工管里周课表显示 执行时间
     */
    public function getExecuteTime($kefuid);

    /**
     * 获取最后一次回访时间
     * @param   $id
     * @return  string
     * create by  wangkai
     * create time  2017/4/14
     */
    public function getEndVisitRecordTime($id);

    /**
     * 体验课报表的微课客服列表
     * @return mixed
     * create by wangke
     */
    public function getExClassReportKefuInfo();

    /**
     * 获取所有新签用户列表
     * @return  array
     * create by  wangkai
     * create time  2017/5/11
     */
    public function getNewAccountList();

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
     * VIP微课 员工管理  账号唯一性
     * @param $userName
     * @return mixed
     * create by wangke
     */
    public function validateUpdataUniqueEmail($id, $email);

    /*
     * 获取渠道经理的专属二维码
     * create sjy
     */
    public function getChannelCode($userid);
}
