<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/10
 * Time: 下午7:49
 */
namespace common\logics\channel;

use Yii;
use yii\base\Object;

interface IChannel
{
    /**
     * 获取销售渠道用户的数量
     * @param   $type   类型
     * @param   $keyword  搜索
     * @return  array
     * create by  wangkai
     */
    public function getSaleChannelUserCount($type, $keyword, $time, $studentPhone);

    /**
     * @param $type
     * @param $keyword
     * @param $time
     * @return mixed
     * create by wangke
     * VIP微课 管理视角 全部用户
     */
    public function getAllSaleChannelUserCount($worth, $keyword, $kefutype, $studentPhone);

    /**
     * 获取销售渠道用户的列表
     * @param   $type   类型
     * @param   $keyword  搜索
     * @param   $num  搜索
     * @return  array
     * create by  wangkai
     */
    public function getSaleChannelUserList($type, $keyword, $num, $time, $studentPhone);

    /**
     * @param $type
     * @param $keyword
     * @param $num
     * @param $time
     * @return mixed
     * create by wangke
     * VIP微课 管理视角 全部用户的list
     */
    public function getAllSaleChannelUserList($num, $studentPhone, $worth, $keyword, $info, $kefuType);

    /**
     * 获取销售渠道的用户信息
     * @param  $openid
     * @return  array
     * create by  wangkai
     */
    public function getSaleChannelUserInfo($openId);

    /**
     * 修改销售渠道的用户信息
     * @param   $openid
     * @return  array
     * create by  wangkai
     */
    public function doEditUser($request);


    /**
     * 奖励名单提醒数量
     * @param  $time
     * @param  $keyword
     * @return  int
     * create by  wangkai
     */
    public function getRewardUserCount($time, $keyword, $rewardtype);

    /**
     * 奖励名单提醒列表
     * @param  $time
     * @param  $keyword
     * @return  int
     * create by  wangkai
     */
    public function getRewardUserList($num, $time, $keyword, $rewardtype);

    /**
     * @param $data
     * @return mixed
     * @created by Jhu
     * 推广大使用户关注触发延迟消息
     */
    public function addSubscribeDelayTask($data);

    /**
     * 添加用户权限
     * @param  $uid
     * @return  array
     * create by  wangkai
     */
    public function doOpenPremission($uid);

    /**
     * 获取本次应该获得金额
     * @param  $salechannelid
     * @return  array
     * create by  wangkai
     */
    public function getThisReward($salechannelid);

    /**
     * 删除用户
     * create by  wangkai
     */
    public function doDeleteUser($id);

    /**
     * @param $id
     * @return mixed
     * create by wangke
     * 全部用户 删除用户
     */
    public function doDeleteAllUser($id);

    /**
     * 点亮用户
     * @param $request
     * @return mixed
     * create by wangke
     */
    public function lightenUser($request);

    /**
     * 获取奖励记录数量 (包括本次明细, 历史奖励, 历史明细)
     * @param   $channelid
     * @return  str
     * create by  wangkai
     */
    public function getOtherRewardRecordCount($channelId);

    /**
     * 获取奖励记录列表 (包括本次明细, 历史奖励, 历史明细)
     * @param   $channelid
     * @param   $num
     * @return  array
     * create by  wangkai
     */
    public function getOtherRewardRecordList($channelId, $num);

    /**
     * 获取可以上的微信课程
     * @param $openid
     * @param $keyword
     * @return  array
     * create by  wangkai
     */
    public function getWechatClassList($openId, $keyword, $num);

    /**
     * 获取可以上的微信课程数量
     * @param $keyword
     * @return  array
     * create by  wangkai
     */
    public function getWechatClassCount($keyword);


    /**
     * 添加分享信息
     * @param $openid
     * @param $classid
     * @param $backtype
     * @return  array
     * create by  wangkai
     */
    public function doAddUserShareInfo($openId, $classId, $backType);

    /**
     * 获取经过用户推广的信息
     * @param  id
     * @return  array
     * create by  wangkai
     */
    public function getChannelInfo($id);

    /**
     * 获取推广价值信息
     * @param  $type
     * @param  $num
     * @return  array
     * create by  wangkai
     */
    public function getChannelInfoList($id, $type, $num);

    /**
     * 获取推广价值信息页面
     * @param  $type
     * @return  array
     * create by  wangkai
     */
    public function getChannelInfoPage($id, $type);

    /**
     * 体验课报表的条数
     * @param $type
     * @param $date
     * @param $status
     * @param $kefuid
     * @return mixed
     * create by wangke
     */
    public function getExClassReportCount($type, $date, $status, $kefuid);

    /**
     * 查询点击专属服务的条数
     * @return mixed
     * create by wangke
     */
    public function getPersonalServerPage($start, $end);

    /**
     * 查询点击专属服务的列表
     * @return mixed
     * create by wangke
     */
    public function getPersonalServerList($num, $start, $end);

    /**
     * 月月活动奖励明细的条数
     * @param $start
     * @param $end
     * @param $usertype
     * @return mixed
     * create by wangke
     */
    public function getMonthGiftPage($start, $end, $userType, $kefuId);

    /**
     * 月月活动奖励明细的列表
     * @param $num
     * @param $start
     * @param $end
     * @param $usertype
     * @return mixed
     * create by wangke
     */
    public function getMonthGiftList($num, $start, $end, $userType, $kefuId);

    /**
     * 体验课报表的列表信息
     * @param $type
     * @param $date
     * @param $status
     * @param $kefuid
     * @param $num
     * @return mixed
     * create by wangke
     */
    public function getExClassReportList($type, $date, $status, $kefuid, $num);

    /*
     * 生成渠道经理的专属二维码
     * @param $userid useraccount 的id
     * create by sjy
     */
    public function channelCode($userid);

    /*
     * 获取用户渠道拉新二维码
     * create by sjy 
     */
    public function getChannelCode($userid);

    /*
     * 获取未读消息
     * create by sjy
     */
    public function getWaitStatisticsPage($startTime, $endTime);

    /**
     * 得到所有的渠道经理信息
     * @return mixed
     * create by wangke
     */
    public function getAllChannelKefuInfo();
}
