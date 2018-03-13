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
use common\widgets\Queue;
use common\widgets\Request;
use common\services\QiniuService;
use yii\helpers\ArrayHelper as Y;

class ChannelLogic extends Object implements IChannel
{

    /** @var  \common\sources\read\classes\ClassAccess $RClassAccess */
    private $RClassAccess;
    /** @var  \common\sources\read\classes\RecordAccess $RRecordAccess */
    private $RRecordAccess;
    /** @var  \common\sources\read\student\StudentAccess $RStudentAccess */
    private $RStudentAccess;
    /** @var  \common\sources\read\channel\ChannelAccess $RChannelAccess */
    private $RChannelAccess;
    /** @var  \common\sources\write\channel\ChannelAccess $WChannelAccess */
    private $WChannelAccess;
    /** @var  \common\sources\write\push\PushAccess $WPushAccess */
    private $WPushAccess;
    /** @var  \common\sources\write\student\StudentAccess $WStudentAccess */
    private $WStudentAccess;
    /** @var  \common\sources\read\chat\ChatAccess $RChatAccess */
    private $RChatAccess;
    /** @var  \common\sources\read\account\AccountAccess $RAccountAccess */
    private $RAccountAccess;

    /** @var  \common\sources\write\account\AccountAccess $WAccountAccess */
    private $WAccountAccess;

    public function init()
    {
        $this->RClassAccess = Yii::$container->get('RClassAccess');
        $this->RRecordAccess = Yii::$container->get('RRecordAccess');
        $this->RStudentAccess = Yii::$container->get('RStudentAccess');
        $this->RChannelAccess = Yii::$container->get('RChannelAccess');
        $this->WChannelAccess = Yii::$container->get('WChannelAccess');
        $this->WPushAccess = Yii::$container->get('WPushAccess');
        $this->WStudentAccess = Yii::$container->get('WStudentAccess');
        $this->RChatAccess = Yii::$container->get('RChatAccess');
        $this->RAccountAccess = Yii::$container->get('RAccountAccess');
        $this->WAccountAccess = Yii::$container->get('WAccountAccess');
    }

    public function getSaleChannelUserCount($type, $keyword, $time, $studentPhone)
    {

        list($worth, $info) = $this->getType($type, $time);
        if (1 == 2) {
            return $info;
        }
        $keyword = addslashes($keyword);
        $count = $this->RChannelAccess->getSaleChannelUserCount($worth, $keyword, $studentPhone);
        $data = array(
            'count' => $count
        );

        return ['error' => 0, 'data' => $data];
    }

    public function getAllSaleChannelUserCount($type, $keyword, $time, $kefutype, $studentPhone = '')
    {
        list($worth, $info) = $this->getaAllUserType($type, $time);
        //$worth = $this->getaAllUserType($type, $time);
        if (1 == 2) {
            return $info;
        }
        $keyword = addslashes(trim($keyword));
        $studentPhone = trim($studentPhone);
        $count = $this->RChannelAccess->getAllSaleChannelUserCount($worth, $keyword, $kefutype, $studentPhone);
        return ['error' => 0, 'data' => ['count' => $count]];
    }

    public function getRewardUserCount($time, $keyword, $rewardtype)
    {
        $time = strtotime($time);
        $count = $this->RChannelAccess->getRewardUserCount($time, $keyword, $rewardtype);
        
        $data = array(
            'count' => $count
        );

        return ['error' => 0, 'data' => $data];
    }

    public function getRewardUserList($num, $time, $keyword, $rewardtype)
    {

        $time = strtotime($time);
        $list = $this->RChannelAccess->getRewardUserList($num, $time, $keyword, $rewardtype);

        foreach ($list as &$v) {
            $v['status'] = '【该日可提取 额度：' . $v['money'] . '元】';
            $v['money_color'] = '#04DD98';
            switch ($v['message_type']) {
                case 1:
                    $v['worth'] = '新用户';
                    break;
                case 2:
                    $v['worth'] = '有推广价值的用户';
                    break;
                case 3:
                    $v['worth'] = '无推广价值的用户';
                    break;
            }

            $chat_time = $this->RChatAccess->getChannelChatLinkTime($v['bind_openid'], $v['kefu_id']);
            $v['kefu_name'] = !empty($v['kefu_id']) ? $this->RAccountAccess->getNewSignKefuNick($v['kefu_id'])
                : '暂无';


            if ($chat_time) {
                $v['day'] = date('m-d H:i:s', $chat_time);
            } else {
                $v['day'] = '没有进行聊天沟通过';
            }

            if (empty($v['from_code'])) {
                $v['code'] = '自然关注';
            } else {
                $v['private'] = $this->RChannelAccess->getSaleChannelCode($v['from_code']);
                $v['code'] = empty($v['private']) ? '是转介绍过来但是介绍用户已被删除' : $v['private'] . '转介绍';
            }

            $v['follow_time'] = date('Y-m-d H:i:s', $v['created_at']);

            switch ($v['user_type']) {
                case 1:
                    $v['user_type'] = '任课老师';
                    break;
                case 2:
                    $v['user_type'] = '家长';
                    break;
                case 3:
                    $v['user_type'] = '其他';
                    break;
            }
        }

        $data = array(
            'data' => $list
        );

        return ['error' => 0, 'data' => $data];
    }

    public function getSaleChannelUserList($type, $keyword, $num, $time, $studentPhone)
    {
        list($worth, $info) = $this->getType($type, $time);

        $list = $this->RChannelAccess->getSaleChannelUserList($worth, $keyword, $num, $info, $studentPhone);

        foreach ($list as &$v) {
            if (!empty($v['money']) && $v['money'] > 0) {
                $v['status'] = '【提取金钱 额度为' . $v['money'] . '元】';
                $v['money_color'] = '#04DD98';
            } else {
                $v['status'] = ' 【无提取金钱】';
                $v['money_color'] = '#EB3F2F';
            }

            $v['kefu_name'] = !empty($v['kefu_id']) ? $this->RAccountAccess->getNewSignKefuNick($v['kefu_id'])
                : '暂无';

            switch ($v['message_type']) {
                case 1:
                    $v['worth'] = '新用户';
                    break;
                case 2:
                    $v['worth'] = '有推广价值的用户';
                    break;
                case 3:
                    $v['worth'] = '无推广价值的用户';
                    break;
            }

            $chat_time = $this->RChatAccess->getChannelChatLinkTime($v['bind_openid'], $v['kefu_id']);

            if ($chat_time) {
                $v['day'] = date('m-d H:i:s', $chat_time);
            } else {
                $v['day'] = '没有进行聊天沟通过';
            }

            if (empty($v['from_code'])) {
                $v['code'] = '自然关注';
            } else {
                $v['private'] = $this->RChannelAccess->getSaleChannelCode($v['from_code']);
                $v['code'] = empty($v['private']) ? '是转介绍过来但是介绍用户已经被删除' : $v['private'] . '转介绍';
            }

            $v['follow_time'] = date('Y-m-d H:i:s', $v['created_at']);
        }

        $data = array(
            'list' => $list
        );

        return ['error' => 0, 'data' => $data];
    }

    public function getAllSaleChannelUserList($type, $keyword, $num, $time, $kefutype, $studentPhone)
    {
        list($worth, $info) = $this->getaAllUserType($type, $time);
        $list = $this->RChannelAccess->getAllSaleChannelUserList($num, $studentPhone, $worth, $keyword, $info, $kefutype);

        foreach ($list as &$v) {
            if (!empty($v['money']) && $v['money'] > 0) {
                $v['status'] = '【提取金钱 额度为' . $v['money'] . '元】';
                $v['money_color'] = '#04DD98';
            } else {
                $v['status'] = ' 【无提取金钱】';
                $v['money_color'] = '#EB3F2F';
            }

            switch ($v['message_type']) {
                case 1:
                    $v['worth'] = '新用户';
                    break;
                case 2:
                    $v['worth'] = '有推广价值的用户';
                    break;
                case 3:
                    $v['worth'] = '无推广价值的用户';
                    break;
            }

            $chat_time = $this->RChatAccess->getAllChannelChatLinkTime($v['bind_openid'], $v['kefu_id']);

            if ($chat_time) {
                $v['day'] = date('m-d H:i:s', $chat_time);
            } else {
                $v['day'] = '没有进行聊天沟通过';
            }

            if (empty($v['from_code'])) {
                $v['code'] = '自然关注';
            } else {
                $v['private'] = $this->RChannelAccess->getAllSaleChannelCode($v['from_code']);
                $v['code'] = empty($v['private']) ? '是转介绍过来但是介绍用户已经被删除' : $v['private'] . '转介绍';
            }

            $v['follow_time'] = date('Y-m-d H:i:s', $v['created_at']);

            switch ($v['user_type']) {
                case 1:
                    $v['user_type'] = '任课老师';
                    break;
                case 2:
                    $v['user_type'] = '家长';
                    break;
                case 3:
                    $v['user_type'] = '其他';
                    break;
            }
        }

        return ['error' => 0, 'data' => ['list' => $list]];
    }


    public function getSaleChannelUserInfo($openId)
    {
        $instrument_ids = [];

        $list = $this->RChannelAccess->getSaleChannelUserInfo($openId);

        $list['subscribe'] = $list['subscribe']?'<font color="green">(已关注)</font>':'<font color="red">(已取关)</font>';
        $list['follow_time'] = date('Y-m-d H:i:s', $list['created_at']);
        $list['auth_time'] = $list['auth_time'] ? date('Y-m-d H:i:s', $list['auth_time']):'未授权';
        $list['reqrcode_time'] = $list['weicode_path'] ? ($list['reqrcode_time']? '临时':'永久'):'无';
        $kefu_name = $list ? $this->RAccountAccess->getNewSignKefuNick($list['kefu_id']) : '';

        $instrument = $this->RChannelAccess->getInstrumentByIds();

        if (!empty($list['from_code'])) {
            $introduce_nick = $this->RChannelAccess->getSalesChannelNickByPrivate($list['from_code']);
            $list['from_nick'] = $introduce_nick . '转介绍的';
            $list['private_code'] = '渠道ID：' . $list['from_nick'];
        } else {
            $list['from_nick'] = '自然关注的用户';
            $list['private_code'] = '渠道ID：' . $list['private_code'];
        }
        //用户身份
        $statusList = Yii::$app->params['user_type'];
        //用户状态
        $worthList = Yii::$app->params['message_type'];

        if (!empty($list['instrument'])) {
            $instrument_ids = explode(',', $list['instrument']);
            $instrument_ids = array_filter($instrument_ids);
            $instrument_ids = array_values($instrument_ids);
        }
        $data = array(
            'list' => $list,
            'statusList' => $statusList,
            'worthList' => $worthList,
            'kefu' => $kefu_name,
            'instrument' => $instrument,
            'instrument_ids' => $instrument_ids,
        );

        return ['error' => 0, 'data' => $data];
    }

    private function getType($type, $time)
    {
        $info = '';
        switch ($type) {
            case 1:
                $worth = ' AND message_type = 1';
                break;
            case 2:
                $worth = ' AND message_type = 2';
                break;
            case 3:
                $worth = ' AND message_type = 3';
                break;
            case 4:
                $worth = 'AND u.kefu_id = ' . Yii::$app->user->identity->id;
                $info = 1;
                break;
            case 6:
                if (!empty($time)) {
                    $start = strtotime($time);
                    $end = $start + 86400;
                    $worth = ' AND u.kefu_id = ' . Yii::$app->user->identity->id . ' AND message_type = 2 AND updated_at >= ' . $start . ' AND updated_at < ' . $end;
                } else {
                    $worth = ' AND u.kefu_id = ' . Yii::$app->user->identity->id . ' AND message_type = 2';
                }

                break;
            default:
                $worth = '';
                break;
        }
        return [$worth, $info];
    }

    private function getaAllUserType($type, $time)
    {
        $info = '';
        switch ($type) {
            case 1:
                $worth = ' AND message_type = 1';
                break;
            case 2:
                $worth = ' AND message_type = 2';
                break;
            case 3:
                $worth = ' AND message_type = 3';
                break;
            case 4:
                $worth = ' AND money > 0';
                $info = 1;
                break;
            case 6:
                $start = strtotime($time);
                $end = $start + 86400;
                $worth = ' AND message_type = 2 AND updated_at >= ' . $start . ' AND updated_at < ' . $end;
                break;
            default:
                $worth = '';
                break;
        }
        return [$worth, $info];
    }

    public function doEditUser($request)
    {
        $instrument = '';
        if (!empty($request['phone'])) {
            $phone = $this->RChannelAccess->getSalesChannelPhone($request['phone'], $request['open_id']);
            if (!empty($phone)) {
                return ['error' => '手机号已存在', 'data' => ''];
            }
        }
        if (!empty($request['instrument'])) {
            $instrument = implode(',', $request['instrument']);
            $instrument = ',' . $instrument . ',';
        }
        $this->WChannelAccess->doEditUser($request['open_id'], $request['name'], $request['phone'], $request['worth'], $request['remark'], $instrument);

        return ['error' => 0, 'data' => ''];
    }

    public function getWechatClassCount($keyword)
    {
        $count = $this->RChannelAccess->getWechatClassCount($keyword);

        $data = array(
            'count' => $count
        );

        return ['error' => 0, 'data' => $data];
    }

    public function getWechatClassList($openId, $keyword, $num)
    {
        $list = $this->RChannelAccess->getWechatClassList($openId, $keyword, $num);

        foreach ($list as &$v) {
            $v['class_time'] = '上课时间：' . date('Y-m-d H:i:s', $v['class_time']);
            $v['url'] = Yii::$app->params['channel_frontend_url'] . 'liveshow/' . $v['id'].'?type=11';

            if ($v['is_top'] == 1) {
                $v['color'] = 'red';
                $v['info'] = '(课程被置顶)';
            } else {
                $v['color'] = '';
                $v['info'] = '';
            }

            $purview = $this->RChannelAccess->getUserShareIsBack($openId, $v['id'], 0);

            $v['back'] = empty($v['is_back']) ? ' ' : '（回顾课）';
            $v['purview'] = empty($purview) ? '(无权限)' : '(有权限)';
        }

        $data = array(
            'data' => $list
        );

        return ['error' => 0, 'data' => $data];
    }

    public function addSubscribeDelayTask($data)
    {
        $time = time();
        $hour = date('H', $time);

        $msg = array(
            'event' => 'MESSAGE',
            'uid' => $data['uid'],
            'open_id' => $data['open_id']
        );

        if ($hour > 18 && $hour < 24) {
            $sendTime = strtotime(date('Y-m-d 09:30', $time) . ' +1 day');
            $ttl = ($sendTime - $time) * 1000;
        } elseif ($hour <= 18) {
            $ttl = 10 * 60 * 1000;
        }

        Queue::produceTtl($msg, 'delay', 'delay_channel_subscribe_x', (string)$ttl);
    }

    public function getThisReward($salechannelid)
    {
        $money = $this->RChannelAccess->getThisSaleChannelReward($salechannelid);

        $history_money = $this->RChannelAccess->getHistorySaleChannelReward($salechannelid);

        $money = $money - $history_money;
        $data = array(
            'money' => $money
        );

        return ['error' => 0, 'data' => $data];
    }

    public function doOpenPremission($uid)
    {
        $have_premission = $this->RChannelAccess->getHavePremission($uid);

        if (!empty($have_premission)) {
            return ['error' => '用户已经开启权限', 'data' => ''];
        }

        if ($this->WChannelAccess->doOpenPremission($uid)) {
            return ['error' => 0, 'data' => ''];
        } else {
            return ['error' => '添加权限失败,请联系管理员', 'data' => ''];
        }
    }

    public function getOtherRewardRecordCount($channelId)
    {
        $data['count'] = $this->RChannelAccess->getThisSaleChannelCount($channelId);
        return ['error' => 0, 'data' => $data];
    }

    public function doDeleteUser($id)
    {
        if ($this->WChannelAccess->doDeleteUser($id)) {
            return ['error' => 0, 'data' => ''];
        } else {
            return ['error' => '删除失败请联系管理员', 'data' => ''];
        }
    }

    public function doDeleteAllUser($id)
    {
        if ($this->WChannelAccess->doDeleteAllUser($id)) {
            return ['error' => 0, 'data' => ''];
        } else {
            return ['error' => '删除失败请联系管理员', 'data' => ''];
        }
    }

    public function lightenUser($request)
    {
        $openId = $request->post('open_id');
        $salesChannel = $this->RChannelAccess->getChannelUserByOpenid($openId);
        if ($salesChannel) {
            $salesChannel->lighten_status = $salesChannel->lighten_status > 0 ? 0 : 1;
            $this->WChannelAccess->saveEditSaleChannelByObject($salesChannel);
            return json_encode(['error' => '']);
        } else {
            return json_encode(['error' => '用户没有找到']);
        }
    }

    public function getOtherRewardRecordList($channelId, $num)
    {
        $list = $this->RChannelAccess->getThisSaleChannelList($channelId, $num);

        foreach ($list as &$v) {
            if ($v['status']==-1) {
                $v['money'] = '<font color="red">-' . $v['money'] . '</font>';
            }
            if (!in_array($v['status'], [-1,3,4,5])) {
                $v['money'] = '<font color="green">+' . $v['money'] . '</font>';
            }
        }

        return ['error' => 0, 'data' => ['list' => $list]];
    }

    //生成微信二维码并上传到七牛
    public function makeChannelCode($cid)
    {
        //生成临时二维码
        $wechat = Yii::$app->wechat;
        $num = 2500000000;
        $senseid = $num + $cid;
        $qrcode = [
            'action_name'=>'QR_SCENE',
            'expire_seconds' => 2592000,
            'action_info' => ['scene' => ['scene_id' => $senseid]]
        ];
        $tickect = $wechat->createQrCode($qrcode);

        if (!empty($tickect) && is_array($tickect) && isset($tickect['ticket'])) {
            $imgUrl = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $tickect['ticket'];
            //存储到七牛
            $filename = $this->uploadWeicode(Request::httpGet($imgUrl), 'sales_channel/qr_code/');
            return $filename;
        }
    }

    public function getWechatClassId($openId, $classId, $backType)
    {
        if (!empty($this->RChannelAccess->getListenWechatClass($openId, $classId, $backType))) {
            return ['error' => 0, 'data' => ''];
        } else {
            $data = array(
                'class_id' => $classId,
                'open_id' => $openId
            );

            return ['error' => 2, 'data' => $data];
        }
    }

    public function doAddUserShareInfo($openId, $classId, $backType)
    {
        $user = $this->RChannelAccess->getChannelUserByOpenid($openId);
        $data = array(
            'error' => 0,
            'data' => ''
        );
        $usershare = $this->WChannelAccess->doUserShareInfo($user["id"], $openId, $classId, $backType);
        if (empty($usershare)) {
            $data["error"] = "添加失败请联系管理员";
        } else {
            $power = $this->WChannelAccess->getChannelAuth($openId);
            if (empty($power)) {
                $data["error"] = "授权失败";
            }
        }
        return $data;
    }

    private function buildMessage($openid, $templateId, $firstValue, $key1word, $key2word, $key3word, $url, $remark)
    {
        $data = array(
            'first' => array('value' => $firstValue),
            'keyword1' => array('value' => $key1word),
            'keyword2' => array('value' => $key2word, 'color' => '#c9302c'),
            'keyword3' => array('value' => $key3word),
            'remark' => array('value' => $remark)
        );

        $message = array(
            'touser' => $openid,
            'template_id' => $templateId,
            'url' => $url,
            'data' => $data
        );

        return $message;
    }

    public function getChannelInfo($id)
    {
        $list = $this->RChannelAccess->getChannelInfo($id);
        $register_count = $this->RChannelAccess->getRegisterCount($id);

        $private_code = $this->RChannelAccess->getPrivateCode($id);
        $new_count = $this->RChannelAccess->getNewWechatUserCount($private_code);

        $list['register_count'] = $register_count;
        $list['new_count'] = $new_count;

        $data = array(
            'list' => $list
        );

        return ['error' => 0, 'data' => $data];
    }

    public function getChannelInfoPage($id, $type)
    {
        switch ($type) {
            case 1:
                $private_code = $this->RChannelAccess->getPrivateCode($id);
                $count = $this->RChannelAccess->getNewWechatUserCount($private_code);
                break;
            case 2:
                $count = $this->RChannelAccess->getRegisterCount($id);
                break;
            case 3:
                $count = $this->RChannelAccess->getExUserCount($id);
                break;
            case 4:
                $count = $this->RChannelAccess->getBuyUserCount($id);
                break;
            case 5:
                $count = $this->RChannelAccess->getTwoBuyUserCount($id);
                break;
        }


        $data = array(
            'count' => $count
        );

        return ['error' => 0, 'data' => $data];
    }

    public function getChannelInfoList($id, $type, $num)
    {
        switch ($type) {
            case 1:
                $privatecode = $this->RChannelAccess->getPrivateCode($id);
                $list = $this->RChannelAccess->getNewWechatUserList($privatecode, $num);
                break;
            case 2:
                $list = $this->RChannelAccess->getRegisterList($id, $num);
                break;
            case 3:
                $list = $this->RChannelAccess->getExUserList($id, $num);
                break;
            case 4:
                $list = $this->RChannelAccess->getBuyUserList($id, $num);
                break;
            case 5:
                $list = $this->RChannelAccess->getTwoBuyUserList($id, $num);
                break;
        }

        if (!empty($list)) {
            foreach ($list as &$v) {
                $v['time_created'] = date('Y-m-d H:i:s', $v['time_created']);
            }
        }

        $data = array(
            'data' => $list
        );

        return ['error' => 0, 'data' => $data];
    }


    public function getPersonalServerPage($start, $end)
    {
        if ($start != 0 && $end != 0) {
            $sdate = strtotime($start);
            $edate = strtotime($end) + 3600 * 24;
        } else {
            $now = date('Y/m/d', time());
            $sdate = strtotime($now);
            $edate = strtotime($now) + 3600 * 24;
        }
        return $this->RChannelAccess->getPersonalServerPage($sdate, $edate);
    }

    public function getPersonalServerList($num, $start, $end)
    {
        if ($start != 0 && $end != 0) {
            $sdate = strtotime($start);
            $edate = strtotime($end) + 3600 * 24;
        } else {
            $now = date('Y/m/d', time());
            $sdate = strtotime($now);
            $edate = strtotime($now) + 3600 * 24;
        }
        return $this->RChannelAccess->getPersonalServerList($num, $sdate, $edate);
    }


    public function getMonthGiftPage($start, $end, $userType, $kefuId)
    {
        if ($start != 0 && $end != 0) {
            $sdate = strtotime($start);
            $edate = strtotime($end) + 3600 * 24;
        } else {
            $now = date('Y/m/d', time());
            $sdate = strtotime($now) - 3600 * 24 * 13;
            $edate = strtotime($now) + 3600 * 24;
        }

        $data = $this->RChannelAccess->getMonthGiftPage($sdate, $edate, $userType, $kefuId);

        return $data;
    }

    public function getMonthGiftList($num, $start, $end, $userType, $kefuId)
    {
        if ($start != 0 && $end != 0) {
            $sdate = strtotime($start);
            $edate = strtotime($end) + 3600 * 24;
        } else {
            $now = date('Y/m/d', time());
            $sdate = strtotime($now) - 3600 * 24 * 13;
            $edate = strtotime($now) + 3600 * 24;
        }

        $data = $this->RChannelAccess->getMonthGiftList($num, $sdate, $edate, $userType, $kefuId);

        if (!empty($data)) {
            foreach ($data as &$row) {
                $row['username'] = empty($row['username']) ? '无' : $row['username'];

                switch ($row['status']) {
                    case 8:
                        $row['status'] = '首次体验奖';
                        break;
                    case 11:
                        $row['status'] = '微课拉新奖';
                        break;
                    case 13:
                        $row['status'] = '体验达人奖';
                        break;
                }
            }
        } else {
            return [];
        }
        return $data;
    }

    public function getExClassReportCount($type, $date, $status, $kefuid)
    {
        $stime = $date? strtotime($date):strtotime("today midnight");
        $etime = $stime + 60 * 60 * 24;
        //查找客服的老师
        $user_info = $kefuid?$this->RChannelAccess->getUserBySalesChannelKefu($kefuid):[];
        if ($kefuid && !$user_info) {
            return 0;
        }
        if (empty($type)) {
            $init_ids = Y::getColumn($user_info, 'init_id', false);
            return $this->RChannelAccess->getExClassReportCount($stime, $etime, $status, $init_ids);
        } else {
            $user_ids = Y::getColumn($user_info, 'id', false);
            return $this->RChannelAccess->getAnyDayExClassReportCount($stime, $etime, $status, $user_ids);
        }
    }

    public function getExClassReportList($type, $date, $status, $kefuid, $num)
    {
        $reason = ['家长取消', '老师取消', '乐谱原因', '上课端问题', '误操作', '批量取消', '公众号取消'];
        $stime = $date? strtotime($date):strtotime("today midnight");
        $etime = $stime + 60 * 60 * 24;

        $user_info = $kefuid?$this->RChannelAccess->getUserBySalesChannelKefu($kefuid):[];
        if ($kefuid && !$user_info) {
            return [];
        }
        if (empty($type)) {
            $class_status = [0,1];
            $init_ids = Y::getColumn($user_info, 'init_id', false);
            $data = $this->RChannelAccess->getExClassReportList($stime, $etime, $status, $init_ids, $num);
            //查找体验课
            $user_ids = Y::getColumn($data, 'id', false);
            $ex_class = $this->RChannelAccess->getExClassByUserId($user_ids, $class_status);
            $time_class = Y::map($ex_class, 'student_id', 'time_class');
        } else {
            $user_ids = Y::getColumn($user_info, 'id', false);
            $data = $this->RChannelAccess->getAnyDayExClassReportList($stime, $etime, $status, $user_ids, $num);
        }
        if (1==$type && 1==$status) {
            $cid = Y::getColumn($data, 'cid', false);
            $visit = $this->RChannelAccess->getChannelVisitHistoryByIds($cid);
            $visit = Y::map($visit, 'class_id', 'id');
        }
        //查找客服
        $kefu_ids = Y::getColumn($data, 'kefu_id', false);
        $kefu = $this->RChannelAccess->getUserAccountById($kefu_ids);

        $kefu_name = Y::map($kefu, 'id', 'nickname');
        foreach ($data as $key => $row) {
            $data[$key]['remark'] = '';
            $data[$key]['visit'] = '';
            $data[$key]['kefu_name'] = Y::getValue($kefu_name, $row['kefu_id']);
            $data[$key]['ex_class_time'] = Y::getValue($row, 'time_class')
                                           ?Y::getValue($row, 'time_class')
                                           :Y::getValue($time_class, $row['id']);
            if (1==$type) {
                if (2 == $status) {
                    $data[$key]['remark'] = $reason[$row['is_teacher_cancel']].":".$row['undo_reason'];
                }
                if (1 == $status) {
                    $data[$key]['visit'] = Y::getValue($visit, $row['cid'], 0);
                }
            }
        }
        return $data;
    }

    public function getNoRepayInfo()
    {
        $openStr = '';
        $result = '';
        $allOpenId = $this->RChatAccess->getAllonRepayOpenId();

        if (!empty($allOpenId)) {
            foreach ($allOpenId as $v) {
                if (time() - $v['time_created'] > 60) {
                    $openStr .= "'" . $v['open_id'] . "',";
                }
            }
            $openStr = trim($openStr, ',');
        }
        if ($openStr) {
            $result = $this->RChatAccess->getNoRepayInfo($openStr);
        }
        if ($result) {
            return json_encode([
                'error' => '',
                'data' => $result,
                'count' => count($result)
            ]);
        }
        return json_encode([
            'error' => '',
            'data' => '',
            'count' => 0
        ]);
    }

    public function channelCode($userid)
    {
        //二维码携带参数
        $sceneid = "qd" . $userid;

        $wechat = Yii::$app->wechat_new;
        $qrcode = $wechat->createQrCode([
//             'expire_seconds' => 604800,
            'action_name' => 'QR_LIMIT_STR_SCENE',
            'action_info' => ['scene' => ['scene_str' => $sceneid]]
        ]);
        $channelcode = $wechat->getQrCode($qrcode['ticket']);

        $result = $this->WAccountAccess->channelCode($channelcode, $userid);
        return $result;
    }

    public function getChannelCode($userid)
    {
        $returnData = array(
            'error' => 0,
            'data' => []
        );
        $result = $this->RAccountAccess->getChannelCode($userid);
        if (!empty($result)) {
            $returnData["data"] = $result;
        } else {
            $returnData["error"] = "查询失败";
        }
        return $returnData;
    }

    public function getTransferList($num, $params)
    {
        $type = Yii::$app->params['channel_type'];
        $num = intval($num);
        //处理筛选条件
        $result = $this->dealParams($params);
        $data = $this->RChannelAccess->getTransferList($num, $result);

        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $arr = str_split($value['type']);
                $data[$key]['type'] = $type[$arr[0]] . ' 转 ' . $type[$arr[1]];
                $data[$key]['old_name'] = $value['old_name'] ? $value['old_name'] : $value['old_w_name'];
                $data[$key]['new_name'] = $value['new_name'] ? $value['new_name'] : $value['new_w_name'];
            }
        }
        return $data;
    }

    public function getTransferCount($params)
    {
        $result = $this->dealParams($params);

        if (empty($result)) {
            return 0;
        }
        return $this->RChannelAccess->getTransferList(-1, $result);
    }

    public function getTransferNewChannelInfo($id)
    {
        $data = $this->RChannelAccess->getTransferNewChannelInfo($id);
        $data['uname'] = $data['uname'] ? $data['uname'] : $data['wechat_name'];
        return $data;
    }

    public function getAllChannel()
    {
        return $this->RAccountAccess->getAllUserKefuInfo();
    }

    private function dealParams($params)
    {
        $user_ids = '';
        $ckeyword = '';
        $account_id = '';
        $search_date = 0;
        $etime = 0;
        //陪练用户
        if (is_array_set($params, 'search_user')) {
            $keyword = is_array_set($params, 'search_user') ? addslashes($params['search_user']) : '';

            if ($keyword) {
                $ids = $this->RStudentAccess->getUserIdbyKeyword($keyword);
                if (empty($ids)) {
                    return [];
                }
                $user_ids = implode(',', $ids);
                $user_ids = trim($user_ids, ',');
            }
        }
        //渠道搜索
        if (is_array_set($params, 'search_channel')) {
            $ckeyword = is_array_set($params, 'search_channel') ? addslashes($params['search_channel']) : '';
        }
        //渠道经理
        if (is_array_set($params, 'search_account')) {
            $account_id = is_array_set($params, 'search_account');
        }
        //日期
        if (is_array_set($params, 'search_date')) {
            $search_date = is_array_set($params, 'search_date') ? strtotime($params['search_date']) : '';
            $etime = $search_date + 86400;
        }
        return [
            'uids' => $user_ids,
            'ckeyword' => $ckeyword,
            'aid' => $account_id,
            'stime' => $search_date,
            'etime' => $etime,
        ];
    }

    public function insertTransferReward($params)
    {
        $wechat = Yii::$app->wechat_new;
        $descp = is_array_set($params, 'descp');
        $status = is_array_set($params, 'type');
        $get_money = is_array_set($params, 'money');
        $channel_id = is_array_set($params, 'channel_id');
        $transfer_id = is_array_set($params, 'transfer_id');

        $money = intval($params['money']);
        $money = abs($money);
        $transfer_id = intval($transfer_id);
        $channel_id = intval($channel_id);
        if ($get_money != $money) {
            return json_encode(['error' => '金额错误！']);
        }
        if ($channel_id < 0) {
            return json_encode(['error' => '渠道数据错误']);
        }
        $info = $this->RChannelAccess->getChannelTransferTnfoById($transfer_id);
        $openId = $this->RChannelAccess->getSalesChannelOpenidById($info->new_channel_id);
        $student = $this->RStudentAccess->getUserRowById($info->student_id);

        if (empty($info) || $info['status'] == 1) {
            return json_encode(['error' => '转渠道数据错误']);
        }
        $data = [
            'uid' => $channel_id,
            'money' => $money,
            'descp' => $descp,
            'comment' => '转渠道奖励',
            'status' => $status,
            'studentID' => $info->student_id,
            'studentName' => $student['nick'] ? $student['nick'] : '',
        ];
        $info->status = 1;
        $info->reward_id = Yii::$app->user->identity->id;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $insert_id = $this->WChannelAccess->saveTransferChannleReward($data);
            $info->sales_trade_id = $insert_id;
            $this->WChannelAccess->saveChannelTransferInfo($info);
            $transaction->commit();
        } catch (\Exception $ex) {
            $transaction->rollBack();
            return json_encode(['error' => '奖励失败!请刷新后再试']);
        }

        //发送消息
        if ($money > 0) {
            $firstValue = '您好，您有一笔奖励可以领取！';
            $key1word = '转渠道收入';
            $key2word = $money . ' 元';
            $key3word = date('Y年m月d日 H:i', time());
            $url = '';
            $remark = '';
            $templateId = Yii::$app->params['channel_template_todo'];
            $message = $this->buildMessage($openId, $templateId, $firstValue, $key1word, $key2word, $key3word, $url, $remark);
            $wechat->sendTemplateMessage($message);
        }
        return json_encode(['error' => '']);
    }

    public function getBuyOrderCount($id)
    {
        return $this->RStudentAccess->getProductOrderData($id);
    }

    public function getBuyOrderList($sid, $num)
    {
        return $this->RStudentAccess->getBuyOrderList($sid, $num);
    }

    public function getWaitStatisticsPage($startTime, $endTime)
    {
        $data = $this->RChannelAccess->getWaitStatisticsPage($startTime, $endTime);
        $num = [];
        $time = [];
        for ($i = 0; $i < count($data); $i++) {
            $num[] = $data[$i]["num"];
            $time[] = date('H:i', $data[$i]["create_time"]);
        }
        return [$num, $time];
    }

    public function getAllChannelKefuInfo()
    {
        $data = $this->RAccountAccess->getExClassReportKefuInfo();

        array_unshift($data, ['id' => 0, 'nickname' => ' 选择渠道经理']);
        return $data;
    }

    public function getChannelExClassInfo($channelId)
    {
        $data = $this->RChannelAccess->getChannelExClassInfo($channelId);

        if (!empty($data)) {
            foreach ($data as $row) {
                $row_arr['id'] = $row['id'];
                $row_arr['exClassInfo'] = date('Y-m-d H:i', $row['time_class']) . ' | ' . (empty($row['nick']) ? '无名称' : $row['nick']);
                $infos[] = $row_arr;
            }
        } else {
            $infos = [];
        }
        array_unshift($infos, ['id' => 0, 'exClassInfo' => '选择需要关联的体验课']);
        return $infos;
    }

    public function getWoolPartyCount($kefuId)
    {
        $kefuId = $kefuId ? $kefuId : '';
        return $this->RChannelAccess->getWoolPartyCount($kefuId);
    }
    public function getWoolPartyList($num, $kefuId)
    {
        $kefu_arr           = []; //客服ID
        $channel_id_arr     = []; //渠道ID
        $private_code_arr   = []; //渠道邀请码
        $kefuId = $kefuId ? $kefuId : '';
        $message_type = Yii::$app->params['message_type'];

        //查用户信息
        $list = $this->RChannelAccess->getWoolPartyChannel($num, $kefuId);
        foreach ($list as $key => $value) {
            array_push($private_code_arr, $value['private_code']);
            array_push($channel_id_arr, $value['id']);
            array_push($kefu_arr, $value['kefu_id']);
        }

        //查找拉老师
        $channels = $this->RChannelAccess->getChannelNumBySalesId(array_unique($private_code_arr));
        foreach ($channels as $key => $value) {
            unset($channels[$key]);
            $channels[$value['from_code']] = $value['channel_num'];
        }
        //查找拉学生
        $students = $this->RStudentAccess->getStudentNumBySalesId(array_unique($channel_id_arr));
        foreach ($students as $key => $value) {
            unset($students[$key]);
            $students[$value['sales_id']] = $value['student_num'];
        }
        //查找客服
        $kefus = $this->RAccountAccess->getKefuByIds(array_unique($kefu_arr));
        foreach ($kefus as $key => $value) {
            unset($kefus[$key]);
            $kefus[$value['id']] = $value['nickname'];
        }
        //格式化数据
        foreach ($list as $key => $value) {
            $list[$key]['type']         = $message_type[$value['message_type']];
            $list[$key]['nickname']     = is_array_set($kefus, $value['kefu_id'], '');
            $list[$key]['username']     = $value['wechat_name']?$value['wechat_name']:$value['nickname'];
            $list[$key]['created_at']   = date('Y-m-d H:i:s', $value['created_at']);
            $list[$key]['student_num']  = is_array_set($students, $value['id'], 0);
            $list[$key]['channel_num']  = is_array_set($channels, $value['private_code'], 0);
        }
        return $list;
    }

    public function setTypeWoolParty($id)
    {
        $userInfo = $this->RChannelAccess->getSalesChannelMessageTypeById($id);
        if (!empty($userInfo) && $userInfo['message_type'] != 3) {
            if ($this->WChannelAccess->setTypeWoolParty($id)) {
                return json_encode(['error' => '', 'data' =>[]]);
            } else {
                return json_encode(['error' => '设置失败！', 'data' =>[]]);
            }
        } else {
            return json_encode(['error' => '无法设置！', 'data' =>[]]);
        }
    }

    public function updateQrcode($params)
    {
        if (2 != Yii::$app->user->identity->role) {
            return json_encode(['error' => '没有权限！']);
        }
        if (empty($params['type']) || empty($params['id'])) {
            return json_encode(['error' => '参数错误！请刷新后再操作']);
        }
        $type = $params['type'];
        $user = $this->RChannelAccess->getSalesChannelById($params['id']);
        if (!$user) {
            return json_encode(['error' => '用户不存在！请刷新后再操作']);
        }
        $qrcode = $this->RChannelAccess->getPnlQrCodeByEventKey($params['id']);
        //永久码是在池中
        if ($user['weicode_path']) {
            $is_have = $this->RChannelAccess->getPnlQrCodeByPath($user['weicode_path']);
        }
        if ('temp' == $type) {//改为临时二维码
            $filename = $this->makeChannelCode($params['id']);
            if ($filename) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if (0 == $user['reqrcode_time'] && $user['weicode_path']) {//当前为永久
                        if ($qrcode) {
                            //删除使用记录
                            $this->WChannelAccess->updatePnlCodeUsedById($qrcode['id']);
                        }
                        if (!empty($is_have)) {
                            //释放码池
                            $this->WChannelAccess->updatePnlQrCodeById($is_have['id']);
                        }
                    }
                    $this->WChannelAccess->updateSalesChannelWithWpathById($filename, $params['id'], time());
                    $transaction->commit();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    return json_encode(['error' =>$e->getMessage()]);
                }
                return json_encode(['error' => '']);
            }
            return json_encode(['error' => '更新失败']);
        } else {
            if (0 == $user['reqrcode_time'] && $user['weicode_path']) {
                return json_encode(['error' => '该用户已经是永久码无需再分配']);
            }
            //分配二维码
            $result = $this->assignQrcode(1, $user['id']);
            return json_encode($result);
        }
    }

    private function uploadWeicode($file, $path)
    {
        $filename = md5(uniqid());
        // 要上传文件的本地路径
        $filePath = "tmp/" . $filename;

        //保存到本地
        file_put_contents($filePath, $file);

        // 要上传的空间
        $bucket = Yii::$app->params['pnl_static_bucket'];

        // 上传到七牛后保存的文件名
        $key = $path . $filename;

        if (QiniuService::uploadToQiniu($bucket, $key, $filePath)) {
            unlink($filePath);
            return $key;
        }
        unlink($filePath);
    }

    private function assignQrcode($type, $mappedId)
    {
        if (!$type || !$mappedId) {
            return ['error' => '参数错误'];
        }
        $is_assign = $this->RChannelAccess->getPnlCodeUsedByMappedId($mappedId);
        if ($is_assign) {
            return ['error' => '该用户已经是永久码无需再分配'];
        }
        $num = $this->RChannelAccess->getQrCodeNumByType();
        $arr =$this->getPnlQrCode();
        if (!$num || !$arr) {
            return ['error' => '二维码不足'];
        }
        $pnlQrCode = $arr[0];//实例
        $original_id = $arr[1];
        //返回实例
        $model = $this->WChannelAccess->insertPnlCodeUsed($pnlQrCode->id, $original_id, $mappedId);
        $pnlQrCode->type = $type;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $pnlQrCode->save();
            $model->save();
            $this->WChannelAccess->updateSalesChannelWithWpathById($pnlQrCode->weicode_path, $mappedId);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['error' => '分配失败，请刷新后再尝试'];
        }
        return ['error' => ''];
    }

    //查找一个未分配的二维码
    private function getPnlQrCode($id = 0)
    {
        $pnlQrCode = $this->RChannelAccess->getOneNewQrcode($id);
        if (empty($pnlQrCode)) {
            return 0;
        }
        $exp = explode('_', $pnlQrCode->event_key);
        $original_id = end($exp);

        if ($pnlQrCode) {
            //此处做使用判断
            $isused = $this->RChannelAccess->getPnlCodeUsedByOriginalId($original_id);
            if ($isused) {
                $this->getPnlQrCode($pnlQrCode->id);
            } else {
                return [$pnlQrCode, $original_id];
            }
        }
    }
}
