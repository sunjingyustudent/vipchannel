<?php

/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/13
 * Time: 上午11:16
 */

namespace common\sources\read\channel;

use Yii;
use yii\db\ActiveRecord;

interface IChannelAccess
{

    /**
     * @param $salesId
     * @return mixed
     * @created by Jhu
     * 获取主课老师渠道信息
     */
    public function getRowBySalesId($salesId);

    /**
     * @param $salesId
     * @return mixed
     * created by hujiyu
     * 根据ID获取主课老师渠道名
     */
    public function getSalesChannelNameById($salesId);

    /**
     * @param $channelId
     * @return mixed
     * created by hujiyu
     * 根据ID获取老渠道
     */
    public function getUserChannelInfoById($channelId);

    /**
     * @param $keyword
     * @return mixed
     * @created by Jhu
     * 根据关键字获取主课老师渠道列表
     */
    public function getSalesChannelList($keyword);

    /**
     * @param $keyword
     * @return mixed
     * @created by Jhu
     * 根据关键字获取家长渠道列表
     */
    public function getStudentChannelList($keyword);

    /**
     * @param $salesId
     * @return mixed
     * @created by Jhu
     * 根据ID获取渠道openid
     */
    public function getChannelBindOpenid($salesId);

    /**
     * @param $openId
     * @return mixed
     * @created by Jhu
     * 根据openid获取二维码
     */
    public function getChannelWeicodeByOpenid($openId);

    public function getStudentWeicodeByOpenid($openId);

    /**
     * @return mixed
     * @created by Jhu
     * 获取最新的推广海报
     */
    public function getLastPictureInfo();

    /**
     * @param $fromcode
     * @return mixed
     * @created by Jhu
     * 根据private code获取主课老师渠道信息
     */
    public function getRowBySalesCode($fromcode);

    /**
     * 获取渠道姓名
     * @param  $channel_id
     * @return str
     */
    public function getUserChannelName($channelId);

    /**
     * 获取销售渠道用户数量
     * @param   $type
     * @param   $keyword
     * @return  array
     * create by  wangkai
     */
    public function getSaleChannelUserCount($type, $keyword, $studentPhone);

    /**
     * @param $type
     * @param $keyword
     * @return mixed
     * create by wangke
     * VIP微课 管理视角 全部用户条数
     */
    public function getAllSaleChannelUserCount($worth, $keyword, $kefuType, $studentPhone);

    /**
     * 获取销售渠道用户列表
     * @param   $type
     * @param   $keyword
     * @param   $info
     * @param   $num
     * @return  array
     * create by  wangkai
     */
    public function getSaleChannelUserList($type, $keyword, $num, $info, $studentPhone);

    /**
     * @param $type
     * @param $keyword
     * @param $num
     * @param $info
     * @return mixed
     * create by wangke
     * VIP微课 全部用户的list
     */
    public function getAllSaleChannelUserList($type, $keyword, $num, $info, $kefuType);

    /**
     * 查询是否有重复手机号码
     * @param  $phone
     * @param  $open_id
     * @return  str
     * create by  wangkai
     */
    public function getSalesChannelPhone($phone, $openId);

    /**
     * 查看转介绍的的销售渠道名字
     * @param $from_code
     * @return  array
     * create by  wangkai
     */
    public function getSalesChannelNickByPrivate($fromCode);

    /**
     * @param $openId
     * @return mixed
     * @created by Jhu
     * 渠道是否存在
     */
    public function countChannelByOpenid($openId);

    /**
     * @param $openId
     * @return mixed
     * @created by Jhu
     * 根据哦盆地获取渠道微信名
     */
    public function getChannelNameByOpenid($openId);

    /**
     * 获取用户信息
     * @param $code
     * @return mixed
     * create by wangke
     * 查询新渠道信息
     */
    public function getSaleChannelByPrivetaCode($code);

    /**
     * @param $id
     * @return mixed
     * create by wangke
     * 查询user_share直播课分享表
     */
    public function getUserShreByShareId($id);

    /**
     * @param $share
     * @return mixed
     * create by wangke
     * 上线Fromcode的查询
     */
    public function getFromCodeByOpenid($openId);

    /**
     * @param $From_openid
     * @return mixed
     * create by wangke
     * 查看在sales_channel中下线是否存在
     */
    public function getSaleChannelByLineDown($fromOpenid);

    /**
     * 获取销售信息
     * @param $openID
     * @return  array
     * create by  wangkai
     */
    public function getChannelUserByOpenid($openID);

    /**
     * 根据openid和时间来判断是否属于给出的时间之前注册的
     * @param  $bind_openid
     * @param  $time
     * @return  array
     * create by  wangkai
     */
    public function getSaleChannelTime($bindOpenid, $time);

    /**
     * 获取本次奖励
     * @param $sale_channel_id
     * @return  array
     * create by  wangkai
     */
    public function getThisSaleChannelReward($saleChannelId);

    /**
     * 获取历史奖励
     * @param $sale_channel_id
     * @return  array
     * create by  wangkai
     */
    public function getHistorySaleChannelReward($saleChannelId);

    /**
     * 根据openid 查询昵称
     * @param
     * @return  array
     * create by  wangkai
     */
    public function getUserInitByOpenId($openId);

    /**
     *  获取本次销售渠道奖励数量
     * @param  $sale_channel_id
     * @return  str
     * create by  wangkai
     */
    public function getThisSaleChannelCount($saleChannelId);

    /**
     * 获取历史销售渠道奖励数量
     * @param   $sale_channel_id
     * @return  str
     * create by  wangkai
     */
    public function getHistorySaleChannelCount($saleChannelId);

    /**
     *  获取本次销售渠道奖励数量列表
     * @param  $sale_channel_id
     * @param  $num
     * @return  array
     * create by  wangkai
     */
    public function getThisSaleChannelList($saleChannelId, $num);

    /**
     * 获取历史销售渠道奖励列表
     * @param   $sale_channel_id
     * @param   $num
     * @return  array
     * create by  wangkai
     */
    public function getHistorySaleChannelList($saleChannelId, $num);

    /**
     * 获取历史奖励中记录的奖励费用数量
     * @param  $sale_channel_id
     * @return  array
     * create by  wangkai
     */
    public function getHistoryTradeCount($saleChannelId);

    /**
     * 获取历史奖励中记录的奖励费用列表
     * @param  $sale_channel_id
     * @param  $num
     * @return  array
     * create by  wangkai
     */
    public function getHistoryTradeList($saleChannelId, $num);


    /**
     * @param $private_code
     * @return mixed
     * create by wangke
     * VIP微课 管理视角 通过 private_code得到用户昵称
     */
    public function getAllSaleChannelCode($privateCode);

    /**
     * @param $uid
     * @return mixed
     * @created by Jhu
     * 检查用户有没有分享过直播课
     */
    public function getClassShareByUid($uid);

    /**
     * @param $classId
     * @return mixed
     * @created by Jhu
     * 根据直播课ID查找名字
     */
    public function getLiveClassNameById($classId);

    /**
     * @param $salesId
     * @return mixed
     * @created by Jhu
     * 获取fromcode
     */
    public function getSalesChannelFromcodeById($salesId);

    /**
     * @param $fromcode
     * @return mixed
     * @created by Jhu
     * 根据privatecode获取渠道信息
     */
    public function getSalesChannelInfo($private);

    /**
     * 获取微信课程列表
     * @param $open_id
     * @param $keyword
     * @return  array
     * create by  wangkai
     */
    public function getWechatClassList($openId, $keyword, $num);

    /**
     * 获取微信课程数量
     * @param $open_id
     * @param $keyword
     * @return  array
     * create by  wangkai
     */
    public function getWechatClassCount($keyword);

    /**
     * 获取是否具有听课权限
     * @param  $open_id
     * @param  $class_id
     * @param  $is_back_share
     * @return  array
     * create by  wangkai
     */
    public function getListenWechatClass($openId, $classId, $isBackShare);

    /**
     * 查看是否具有权限
     * @param
     * @return  array
     * create by  wangkai
     */
    public function getUserShareIsBack($openId, $classId, $isBack);

    /**
     * 获取微课的课程ID
     * @param $title
     * @param $class_time
     * @return  str
     * create by  wangkai
     */
    public function getWechatClassId($title, $classTime);

    /**
     * 获取用户累计所有的金钱
     * @param  $uid
     * @return  array
     * create by  wangkai
     */
    public function getTotaltalAmount($uid);

    /**
     * 获取本次奖励
     * @param  $id
     * @return  array
     * create by  wangkai
     */
    public function getHistoryTradeTime($id);

    /**
     * 获取所有没有体现的钱
     * @param   $user_id
     * @return  array
     * create by  wangkai
     */
    public function getNoCashoutChannelId($userId);

    /**
     * 判断是否存在可以提现
     * @param  $uid
     * @return  string
     * create by  wangkai
     */
    public function doExistSaleTradeRecord($uid);

    /**
     * 查看存在该数据
     * @param  $open_id
     * @param  $class_id
     * @param  $back_type
     * @return  array
     * create by  wangkai
     */
    public function getListenPurviewWechatClass($openId, $classId, $backType);

    /**
     * 获取经过用户推广的信息
     * @param  $id
     * @return  array
     * create by  wangkai
     */
    public function getChannelInfo($id);

    /**
     * 获取经过这个转介绍过来的注册数量
     * @param   $id
     * @return  array
     * create by  wangkai
     */
    public function getRegisterCount($id);

    /**
     * 获取用户的使用code
     * @param   $private_code
     * @return  string
     * create by  wangkai
     */
    public function getPrivateCode($id);

    /**
     * 奖励名单提醒数量
     * @param  $time
     * @param  $keyword
     * @return  int
     * create by  wangkai
     */
    public function getRewardUserCount($time, $keyword, $rewardType);

    /**
     * @param $time
     * @param $keyword
     * @return mixed
     * create by wangke
     * VIP微课管理视角 全部用户 奖励提醒用户的条数
     */
    public function getRewardAllUserCount($time, $keyword);

    /**
     * 奖励名单提醒列表
     * @param  $time
     * @param  $keyword
     * @return  int
     * create by  wangkai
     */
    public function getRewardUserList($num, $time, $keyword, $rewardType);

    /**
     * @param $num
     * @param $time
     * @param $keyword
     * @return mixed
     * create by wangke
     * VIP微课 管理视角 奖励提醒用户的list
     */
    public function getRewardAllUserList($num, $time, $keyword);

    /**
     * 获取用户的微信拉辛数量
     * @param   $private_code
     * @return  str
     * create by  wangkai
     */
    public function getUserSharePullCount($id);

    /**
     * 获取微信拉辛列表
     * @param  $private_code
     * @param  $num
     * @return  array
     * create by  wangkai
     */
    public function getNewWechatUserList($privateCode, $num);

    /**
     * 获取属于渠道上体验课的用户列表
     * @param  $id
     * @param  $num
     * @return  array
     * create by  wangkai
     */
    public function getExUserList($id, $num);

    /**
     * 获取属于渠道上体验课的用户数量
     * @param  $id
     * @return  str
     * create by  wangkai
     */
    public function getExUserCount($id);

    /**
     * 获取属于渠道买单的用户数量
     * @param  $id
     * @return  str
     * create by  wangkai
     */
    public function getBuyUserCount($id);

    /**
     * 获取属于渠道上买单的用户列表
     * @param  $id
     * @param  $num
     * @return  array
     * create by  wangkai
     */
    public function getBuyUserList($id, $num);

    /**
     * 获取属于渠道二次买单的用户数量
     * @param  $id
     * @return  str
     * create by  wangkai
     */
    public function getTwoBuyUserCount($id);

    /**
     * 获取属于渠道上二次买单的用户列表
     * @param  $id
     * @param  $num
     * @return  array
     * create by  wangkai
     */
    public function getTwoBuyUserList($id, $num);

    /*
     * create by sjy 2017-03-24
     * 根据活动id获取本次活动用户已领红包总和
     */

    public function getRedpackageActiveSumById($activeId);

    /*
     * create by sjy 2017-03-24
     * 判断用户在本次活动中是否领取过红包
     */

    public function isReUser($openid, $activeId);

    /*
     * 获取用户二维码
     * create by sjy 2017-03-24
     */

    public function getUserWeicode($openid);

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
     * 获取From_code
     * @param $sales_id
     * @return  array
     * create by  wangkai
     * create time  2017/4/12
     */
    public function getChannelFromCode($salesId);

    /**
     * 根据私密的code 获取 open_id
     * @param
     * @return  array
     * create by  wangkai
     * create time  2017/4/12
     */
    public function getChannelBindOpenidByPrivateCode($fromCode);

    /**
     * 获取渠道open_id 并且没有被删除
     * @param $sales_id
     * @return  array
     * create by  wangkai
     * create time  2017/4/17
     */
    public function getSalesChannelOpenidById($salesId);

    /**
     * 体验课报表的条数 每日注册
     * @param $type
     * @param $date
     * @param $status
     * @param $kefuid
     * @return mixed
     * create by wangke
     */
    public function getExClassReportCount($stime, $etime, $status, $kefuid);

    /**
     * 体验课报表的列表信息 每日注册
     * @param $stime
     * @param $etime
     * @param $status
     * @param $kefuid
     * @param $num
     * @return mixed
     * create by wangke
     */
    public function getExClassReportList($stime, $etime, $status, $kefuid, $num);

    /**
     * 体验课报表的条数 每日体验
     * @param $stime
     * @param $etime
     * @param $status
     * @param $kefuid
     * @return mixed
     * create by wangke
     */
    public function getAnyDayExClassReportCount($stime, $etime, $status, $kefuid);

    /**
     * 体验课报表的列表 每日体验
     * @param $stime
     * @param $etime
     * @param $status
     * @param $kefuid
     * @param $num
     * @return mixed
     * create by wangke
     */
    public function getAnyDayExClassReportList($stime, $etime, $status, $kefuid, $num);

    /**
     * 获取所符合条件的用户
     * @param   $keyword
     * @return  array
     * create by  wangkai
     * create time  2017/5/8
     */
    public function getUserChannelId($keyword);

    /**
     * 查询点击专属服务的条数
     * @return mixed
     * create by wangke
     */
    public function getPersonalServerPage($sdate, $edate);

    /**
     * 查询点击专属服务的列表
     * @return mixed
     * create by wangke
     */
    public function getPersonalServerList($num, $sdate, $edate);

    /**
     * 月月活动奖励明细条数
     * @param $sdate
     * @param $edate
     * @param $user_type
     * @return mixed
     * create by wangke
     */
    public function getMonthGiftPage($sdate, $edate, $userType, $kefuId);

    /**
     * 月月活动奖励明细列表
     * @param $num
     * @param $sdate
     * @param $edate
     * @param $user_type
     * @return mixed
     * create by wangke
     */
    public function getMonthGiftList($num, $sdate, $edate, $userType, $kefuId);

    /*
     * 获取未读消息条数
     * create by sjy
     */
    public function getWaitStatisticsPage($startTime, $endTime);

    /*
     * 获取全部课程
     * create by sjy 2017-06-23
     */
    public function getallclass();

    /*
     * 获取用户分享过的课程
     * create by sjy 2017-06-23
     */
    public function getusershare($openid);
}
