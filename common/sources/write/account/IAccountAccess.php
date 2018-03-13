<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/10
 * Time: 上午10:58
 */
namespace common\sources\write\account;

use Yii;
use yii\db\ActiveRecord;

interface IAccountAccess
{
    /**
     * @param $userName
     * @param $nick
     * @param $email
     * @param $type
     * @return mixed
     * create by wangke
     * 销售管理中 添加一个客服
     */
    public function addCourseKefuInKefuManagement($userName, $nick, $email, $type, $telephoneName, $telephonePwd);

    /**
     * @param $userName
     * @param $nick
     * @param $email
     * @param $type
     * @param $telephonename
     * @param $telephonepwd
     * @return mixed
     * create by wangke
     * VIP微课 添加员工
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
     * @param $kefuid
     * @return mixed
     * create by wangke
     * 销售管理 删除客服
     */
    public function deleteKefu($kefuid);

    /**
     * @param $kefuid
     * @return mixed
     * create by wangke
     * VIP微课  开启
     */
    public function openEmploye($kefuid);


    /**
     *  创建客服接待内容
     * @param $kefuid
     * @param $worktime
     * @param $automaticgrowth
     * create by  wangkai
     */
    public function doAddKefuReceptionInfo($kefuid, $worktime, $automaticgrowth);

    /**
     *  创建客服接待内容
     * @param $kefuid
     * @param $worktime
     * @param $automaticgrowth
     * create by  wangkai
     */
    public function doAddChannelKefuReceptionInfo($kefuid, $worktime, $automaticgrowth);

    /**
     *  修改客服的接待数量
     * @param $kefuid
     * @param $worktime
     * create by  wangkai
     */
    public function doEditKefuReceptionInfo($kefuid, $worktime);

    /**
     *  修改推广大使用户的客服的接待数量
     * @param $kefuid
     * @param $worktime
     * create by  wangkai
     */
    public function doEditChannelKefuReceptionInfo($kefuid, $worktime);

    /**
     * 创建客服接待信息，如果已经存在修改她的自动增长数量
     * @param $kefuid
     * @param $worktime
     * @param $automaticgrowth
     * create by  wangkai
     */
    public function doAddKefuReceptionGrowth($kefuid, $worktime, $automaticgrowth);

    /**
     * @param $kefuid
     * @param $timeDay
     * @param $timeBit
     * @return mixed
     * create by wangke
     * VIP微课 添加日课表
     */
    public function addKefuTimetable($kefuid, $timeDay, $timeBit);

    /**
     * @param $kefuId
     * @param $week
     * @param $timeBit
     * @param $timeExecute
     * @return mixed
     * create by wangke
     * VIP微课 添加周课表
     */
    public function addKfuFixedTime($kefuId, $week, $timeBit, $timeExecute);
    
    /*
     * 修改渠道经理的专属二维码
     * create by sjy
     */
    public function channelCode($channelcode, $userid);
}
