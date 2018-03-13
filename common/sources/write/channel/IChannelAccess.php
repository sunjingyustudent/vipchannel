<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/13
 * Time: 上午11:16
 */
namespace common\sources\write\channel;

use Yii;
use yii\db\ActiveRecord;

interface IChannelAccess
{

    /**
     * @param $openid
     * @param $salesId
     * @return mixed
     * @created by Jhu
     * 添加分销扫码统计
     */
    public function addSalesChannelScan($openid, $salesId);

    /**
     * @param $price
     * @param $studentInfo
     * @return mixed
     * @created by Jhu
     * 用户购买套餐添加不可提现金额
     */
    public function addSalesTradeUncashoutByPurchase($price, $studentInfo);

    /**
     * @param $price
     * @param $studentInfo
     * @return mixed
     * @created by Jhu
     * 用户更换套餐添加不可提现金额
     */
    public function addSalesTradeUncashoutByChange($price, $studentInfo);
    
    /**
     * @param $userInfo
     * @param $leftInfo
     * @return mixed
     * create by wangke
     * 退费 保存销售提成表
     */
    public function saveSalesTrade($userInfo, $leftInfo);

    /**
     * @param $data
     * @return mixed
     * @created by Jhu
     * 用户上完课程给渠道分成
     */
    public function addSalesTrade($data);

    /**
     * @param $data
     * @return mixed
     * @created by Jhu
     * 学生上完体验课解锁注册佣金
     */
    public function updateRegisterSalesTrade($data);

    /**
     * @param $data
     * @return mixed
     * @created by Jhu
     * 给乐宝推送分成消息
     */
    public function addChannelAppPush($data);

    /**
     * 修改销售渠道的用户信息
     * @param $open_id
     * @param $nickname
     * @param $phone
     * @param $status
     * @param $worth
     * create by  wangkai
     */
    public function doEditUser($openId, $nickname, $phone, $worth, $remark, $instrument);

    /**
     * 修改销售渠道的用户是否具备推广价值
     * @param $id
     * @param $worth
     * create by  wangkai
     */
    public function doUpdateSaleChannelWorth($id, $worth);

    /**
     * @param $userInfo
     * @param $private_code
     * @param $from_code
     * @param $From_openid
     * @return mixed
     * create by wangke
     * 在新的销售渠道中插入一条数据
     */
    public function insertSalesChannlWithWewhat($userInfo, $privateCode, $fromCode, $fromOpenid);

    /**
     * @param $id
     * @return mixed
     * create by wangke
     * 修改直播课分享表，拉取量+1
     */
    public function updateUserShareWithPullNum($share);

    /**
     * 添加渠道权限
     * @param  $uid
     * create by  wangkai
     */
    public function doOpenPremission($uid);


    /**
     * 添加历史发送记录
     * @param  $uid
     * @param  $trade_id
     * @param  $payable_money
     * @param  $reward_money
     * @param  $total_money
     * create by  wangkai
     */
    public function addHistoryTrade($tradeId, $uid, $payableMoney, $rewardMoney, $totalMoney);

    /**
     * 删除渠道用户
     * @param   $id
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
     * 绑定推广大使的客服
     * @param $uid
     * @param $kefu_id
     * @return  array
     * create by  wangkai
     */
    public function bindChannelKefu($uid, $kefuId);
    
    /**
     * @param $path
     * @param $insert_id
     * @return mixed
     * create by wangke
     * 根据id修改sales_channel的weichatpsh二维码信息
     */
    public function updateSalesChannelWithWpathById($path, $insertId);

    /**
     * @param $data
     * @return mixed
     * @created by Jhu
     * 学生买单给父渠道提成
     */
    public function addFatherSalesTradePurchase($data);


    /**
     * 添加用户听课权限(针对较真的用户)
     * @param  $open_id
     * @param  $class_id
     * @param  $back_type
     * create by  wangkai
     */
    public function doAddUserShareInfo($openId, $classId, $backType);

    /**
     * 修改课程权限
     * @param
     * @return  array
     * create by  wangkai
     */
    public function doUpdateUserShareInfo($openId, $classId, $backType);

    /**
     * 修改所有这个人的渠道是否被提取 并且提示是那一次被提取的
     * @param
     * @return  array
     * create by  wangkai
     */
    public function updateSalesTradeStatus($id, $historyId);

    /**
     *  添加体现记录
     * @param   $transaction
     * @param   $uid
     * @param   $money
     * @return  array
     * create by  wangkai
     */
    public function addSalesTradeInfo($uid, $transaction, $money);
    /*
     * create by sjy 2017-03-24
     * 添加红包活动记录
     */
    public function addRedpackageRecord($redlistinfo);

    /**
     * @param $arr
     * @return mixed
     * create by wangke
     * 将发送海报的消息记录在poster_push_statistic中
     */
    public function savePosterPushStatistic($arr);

    /**
     * @param $msg
     * @return mixed
     * create by wangke
     * 将发送模板的消息记录在template_push_statistic中
     */
    public function saveTemplatePushStatistic($msg);

    /**
     * 学生购买套餐走的提成
     * @param   $uid
     * @param   $studentID
     * @param   $studentName
     * @param   $money
     * @return  array
     * create by  wangkai
     */
    public function addBuyOrderTrade($uid, $studentID, $studentName, $money);

    /**
     * 变更渠道时候把这个人所有的奖励都给这个渠道
     * @param   $uid
     * @param   $studentID
     * @return  array
     * create by  wangkai
     */
    public function doChangeChannel($uid, $studentID, $salesId);

    /**
     * 变更渠道为空的时候删除原来的订单
     * @param   $studentID
     * @param   $sales_id
     * @return  array
     * create by  wangkai
     */
    public function doChangeChannelIsNull($studentID, $salesId);

    public function addRedpackChannel($redlistinfo);

    /**
     * VIP微课 点亮用户
     * @param $open_id
     * @param $lighten_status
     * @return mixed
     * create by wangke
     */
    public function lightenUser($openId, $lightenStatus);
    
    /**
     * 一键开启所有听课权限
     * create by sjy
     */
    public function saveSuperClass($insertData);
    
    /*
     * 更改用户授权信息
     * create by sjy 
     * @param $openId 用户openid
     */
    public function getChannelAuth($openId);
    
    /*
     * 新版更新用户分享
     * create by sjy
     */
    public function doUserShareInfo($userid, $openId, $classId, $backType);
}
