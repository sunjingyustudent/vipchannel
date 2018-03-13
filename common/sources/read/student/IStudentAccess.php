<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/12
 * Time: 下午5:39
 */
namespace common\sources\read\student;

use common\models\music\UserAccount;
use Yii;
use yii\db\ActiveRecord;

interface IStudentAccess
{

    /**
     * @param $userId
     * @return mixed
     * @created by Jhu
     * 获取学生信息
     */
    public function getUserRowById($userId);

    public function getAllSales();
    /**
     * @return mixed
     * created by hujiyu
     * 根据登陆销售ID获取分配名单用户数
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
     * @param $student_id
     * @return mixed
     * created by hujiyu
     * 根据学生ID获取老渠道ID和主课老师ID
     */
    public function getChannelIdsByStudentId($studentId);

    /**
     * @param $channelId
     * @return mixed
     * created by hujiyu
     * 根据换课渠道ID获取学生ID
     */
    public function getUserIdByChannelIdSelf($channelId);

    /**
     * @param $userId
     * @return mixed
     * created by hujiyu
     * 根据用户ID获取绑定顾问
     */
    public function getSalesByStudntId($userId);

    /**
     * @param $userId
     * @return mixed
     * created by hujiyu
     * 根据学生ID获取学生回访基本信息
     */
    public function getStudentVisitInfoById($userId);

    /**
     * @param $student_id
     * @return mixed
     * created by hujiyu
     * 根据学生ID获取用户回访总次数
     */
    public function countVisitByStudentId($studentId);

    /**
     * @param $student_id
     * @param $num
     * @return mixed
     * created by hujiyu
     * 获取学生回访列表
     */
    public function getVisitHistoryList($studentId, $num);
    
    /**
     * @param $request
     * @return mixed
     * created by hujiyu
     * 获取回访用户基本信息
     */
    public function countUserArchiveInfo($studentId);

    /**
     * @return mixed
     * created by hujiyu
     * 体验用户名单总数
     */
    public function countExUser($keyword, $time);

    /**
     * @param $timeDay
     * @return mixed
     * 带跟进名单列表
     */
    public function getTodoList($timeDay);

    /**
     * @param $class_id
     * @return mixed
     * created by xl
     * 根据课程ID获取学生信息
     */
    public function getUserInfoByClassId($classId);

    /**
     * @param $class_id
     * @return mixed
     * created by xl
     * 通过课程ID获取学生的OPENID
     */
    public function getOpenIdByClassId($classId);

    
    /**
     * 查询所有未付费的信息条数
     */
    public function getNotPayAllUsersCount($kefuId, $indention, $area, $exClass, $beforekeyword, $keyword);
    
    /**
     * 获得搜有的未付费用户
    */
    public function getNotPayAllUsers($kefuId, $num, $indention, $area, $exClass, $beforekeyword, $keyword);

    /**
     * 查询一条未付费用户的详细信息
     *@author 王可
     * */
    public function getNotPayUserDetailInfo($userId);

    /**
     * 查询未付费回访记录条数
     *@author 王可
     * */
    public function getNotPayUserVisitListCount($studentId);


    /**
     * 获取已付款用户信息,并进行搜索
     * @User：王锴
     * @Time: 16/12/13 21:38
     * @param bodySql    string  查询条件
     * @param num        num     开始位置
     * @param limit      num     长度
     */
    public function getAllPayUserList($bodySql = null, $num = 1, $limit = 8);

    /**
     * 统计用户数量
     * @User：王锴
     * @Time: 16/12/13 21:38
     */
    public function getPayUserInfo($bodySql = null, $bodyParams = null);

    /**
     * 获取新签用户的信息
     * @User：王锴
     * @Time: 16/12/13 21:38
     * @param bodySql    string  查询条件
     * @param num        num     开始位置
     * @param limit      num     长度
     */
    public function getAllNewPayUserList($bodySql = null, $num = 1, $limit = 8);

    /**
     * @param $id
     * @return mixed
     * create by wangke
     *
     * 根据id 查询学生信息
     */
    public function getUserById($id);

    /**
     * @param $openId
     * @return mixed
     * @created by Jhu
     * 获取微信绑定关系
     */
    public function getWechatRowByOpenId($openId);

    /**
     * @param $openId
     * @return mixed
     * @created by Jhu
     */
    public function countUserInitByOpenid($openId);
    
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
     *查询待排课新签
     */
    public function getPayToClassUserList($num, $keyword);

    /**
     * @param $openId
     * @return mixed
     * @created by Jhu
     * 获取关注用户信息
     */
    public function getUserInitInfoByOpenid($openId);

    /**
     * @param $openId
     * @return mixed
     * @created by Jhu
     * 获取销售渠道的关注用户信息
     */
    public function getSalesChannelInfo($openId);

    /**
     * @param $student_id
     * @return mixed
     * create by wangke
     * 获得添加回访中的用户意向
     */
    public function getUserIntentionInAddVisit($studentId);

    /**
     * @param string $keyword
     * @return mixed
     * create by wangke
     * 查询全部复购的条数
     */
    public function countAllPurchasePage($keyword, $isFixTime);

    /**
     * @param $keyword
     * @param $num
     * @return mixed
     * create by wangke
     * 查询复购的全部客户列表
     */
    public function queryAllPurchaseList($keyword, $isFixTime, $num);
    
    /**
     * @param $filter
     * @return mixed
     * @author xl
     * 通过关键字获取学生列表
     */
    public function getStudentList($filter);

    /**
     * @param $student_id
     * @return mixed
     * create by wangke
     * 复购投诉页面的条数
     */
    public function countPurchaseComplain($keyword);

    /**
     * @param $keyword
     * @param $num
     * @return mixed
     * create by wangke
     * 复购投诉页面的列表信息
     */
    public function getPurchaseComplainList($keyword, $num);
    
    /**
     * @author xl
     * 通过学生ID获取学生OPEN_ID
     */
    public function getStudentOpenId($studentId);

    /**
     * @param $student_id
     * @return mixed
     * create by wangke
     * 得到回访组件的排课信息1
     */
    public function getStudentRemark($studentId);

    /**
     * @param $student_id
     * @return mixed
     * create by wangke
     * 得到回访组件的排课信息2
     */
    public function getStudentFixTimeById($studentId);

    /**
     * @param $user_id
     * @return mixed
     * create by wangke
     * 购买信息的退费所需class_left信息
     */
    public function getUserByLeftId($userId);

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
     * 当前接待顾问已绑定条数
     */
    public function countUserAccountDistribute($kefuId);

    /**
     * @param $userId
     * @return mixed
     * create by wangke
     * 得到一条客户信息 用于绑定复购客服
     */
    public function getUserPublicInfoKefuid($userId);

    /**
     * @param $uid
     * @return mixed
     * @created by Jhu
     * 根据学生ID获取openid
     */
    public function getOpenidByUid($uid);

    /**
     * @param $openId
     * @return mixed
     * @created by Jhu
     * 根据openid湖区uid
     */
    public function getUidByOpenid($openId);

    /**
     * @param $userId
     * @return mixed
     * @created by Jhu
     * 获取学生是否是高危
     */
    public function getStudentIsDanger($userId);

    /**
     * @param $openId
     * @return mixed
     * @created by Jhu
     * 获取用户姓名
     */
    public function getUserName($openId);
    
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
     * 管理视角  查询新用户列表
     */
    public function getAllotNewUserList($introduce, $start, $end, $num);

    /**
     * @param $keyword
     * @return mixed
     * create by wangke
     * 管理视角 未付费再分配条数
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
     * @param $keyword
     * @param $start
     * @param $end
     * @return mixed
     * create by wangke
     * 管理视角 未跟进再分配 分配未跟进的条数
     */
    public function countAgainAllotNotFollow($keyword, $start, $end, $kefuId);

    /**
     * @param $keyword
     * @param $start
     * @param $end
     * @param $num
     * @return mixed
     * create by wangke
     * 管理视角 未跟进再分配 分配未跟进的列表信息
     */
    public function getAgainAllotNotFollowList($keyword, $start, $end, $num, $kefuId);

    /**
     * @param $keyword
     * @param $start
     * @param $end
     * @return mixed
     * create by wangke
     * 管理视角 未跟进再分配 体验课前未跟进的条数
     */
    public function countAgainAllotNotFollowExperienceClassBefore($keyword, $start, $end, $kefuId);


    /**
     * @param $keyword
     * @param $start
     * @param $end
     * @param $num
     * @return mixed
     * create by wangke
     * 管理视角 未跟进再分配 体验课前未跟进的列表信息
     */
    public function getAgainAllotNotFollowExperienceClassBeforeList($keyword, $start, $end, $num, $kefuId);

    /**
     * @param $keyword
     * @param $start
     * @param $end
     * @return mixed
     * create by wangke
     * 管理视角 未跟进再分配 体验课后未跟进的条数
     */
    public function countAgainAllotNotFollowExperienceClassLater($keyword, $start, $end, $kefuId);

    /**
     * @param $keyword
     * @param $start
     * @param $end
     * @param $num
     * @return mixed
     * create by wangke
     * 管理视角 未跟进再分配 体验课后未跟进的列表信息
     */
    public function getAgainAllotNotFollowExperienceClassLaterList($keyword, $start, $end, $num, $kefuId);


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
    public function countPublicUserPage($type, $kefuId, $area, $keyword, $intention, $timeType, $start, $end);
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
    public function getPublicUserList($type, $kefuId, $area, $keyword, $intention, $timeType, $start, $end, $num);

    /**
     * @param $kefuId
     * @return mixed
     * create by wangke
     * 管理 公盘用户修改客服所用客服信息
     */
    public function countUserPublicInfoKefutmp($kefuId);


    /**
     * @param $user_id
     * @return mixed
     * create by wangke
     * 判断用户的新签客服是否为空
     */
    public function isNullPublicUserKefu($userId);
    /**
     * 根据学生ID 获取学生的id， 客服id， 客服昵称
     * @param  $studentId
     * @return array
     */
    public function getUserPublicByUserId($studentId);

    /**
     * 根据学生ID获取用户的open_id
     * @param  $studentId
     * @return str
     */
    public function getUserOpenId($studentId);

    /**
     * 获取某课程中用的乐器
     * @param  $class_info
     * @return array
     */
    public function getUserInstrumentClassTime($classInfo);

    /**
     * 用于导出EXCEL表
     * @User   wk
     * @param  array
     */
    public function getRemainClass();

    /**
     * 获取权限非0的用户
     */
    public function getNoZeroUserAccount();

    /**
     * 用户海报
     * @User：王锴
     * @param   $openid
     * @return  str
     */
    public function getWechatChannelId($openid);

    /**
     * 获取新用户的信息
     * @User：王锴
     * @param  $openid
     * @return array
     */
    public function getInitAndWechatByOpenId($openid);

    /**
     * 获取学生修改时间
     * @User：王锴
     * @param   $student_id  int
     * @return  array
     */
    public function getStudentFixTimeInfo($studentId);

    /**
     * 获取微信用户列表信息
     * @User：王锴
     * @param  $request
     * @return array
     */
    public function getWechatUserList($request);

    /**
     * 获取微信用户信息列表
     * @User：王锴
     * @param  $keyword
     * @param  $type
     * @return int
     */
    public function getWechatUserCount($keyword, $type);

    /**
     * 用户数量
     * @User：王锴
     * @return int
     */
    public function getAllUserInitCount();

    /**
     * 用户初始化的昵称信息
     * @User：王锴
     * @param $openId
     * @return array
     */
    public function getUserInitByOpenId($openId);

    /**
     * 查询客服信息
     * @User：王锴
     * @param  $id
     * @return array
     */
    public function getUserAccountById($id);

    /**
     * 查询微信通道进入用户列表
     * @param   $open_id
     * @return  array
     */
    public function getWeChatAccInfo($openId);

    /**
     * 查询高位用户的数量
     * @param  $id
     * @return int
     */
    public function getHighUserCount($uid);

    /**
     * 寻找所有类型与crm相似的客服 可以搜索
     * @param  $name str
     * @return array
     */
    public function getLikeCrmKefuByName($name);

    /**
     * 寻找所有类型与crm相似的客服
     * @return array
     */
    public function getLikeCrmKefu();

    /**
     * 根据id查找微信端申请的体验名额
     */
    public function getUserEventWeixinById($id);

    /**
     * 获取微信用户列表
     * @param  $status
     * @param  $search
     * @param  $page
     * @return array
     */
    public function getAppsList($status, $search, $page);

    /**
     * 获取微信用户列表数量
     * @param  $status
     * @param  $search
     * @return int
     */
    public function getAppsCount($status, $search);

    /**
     * 获取关注微信公众号的用户信息
     * @param   $applyId
     * @return  array
     */
    public function getUserPreInfo($applyId);

    /**
     * 申请体验的用户列表
     * @param   $is_called
     * @param   $search
     * @param   $page
     * @return  count
     */
    public function getApplysList($isCalled, $search, $page);

    /**
     * 申请体验的用户数量
     * @param   $is_called
     * @param   $search
     * @return  count
     */
    public function getApplysCount($isCalled, $search);

    /**
     * 查找具体学生的信息（不查找是否课程信息）
     * @param $class_id
     * @return int
     */
    public function getStundentInfoByClassId($classId);

    /**
     * 查找客服信息
     * @param  $kefu_id int
     * @return array
     */
    public function getKefuInfo($kefuId);

    /**
     * 获取权限是新签和复购的客服列表
     */
    public function getKefuWithPower();

    /**
     * 获取销售昵称
     * @param  $keyword
     * @return array
     */
    public function getUserAccountByKeyword($keyword);

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
     * @param $uid
     * @param $keyword
     * @param $offset
     * @param $limit
     * @return mixed
     * @created by Jhu
     * 复购销售获取绑定自己的所有用户
     */
    public function getAllUsersByKefuIdRe($uid, $keyword, $offset, $limit);

    /**
     *  根据OpenId找到用户昵称
     * @param   $open_id
     * @return  str
     * create by  wangkai
     */
    public function getNickByOpenId($openId);

    /**
     * @param $telephone
     * @return mixed
     * create by wangke
     * 根据手机号查询学生ID
     */
    public function getUseridByTelephone($telephone);

    
    /**
     * @return mixed
     * create by sjy
     * 查询用户的体验课信息
     */
    public function selectClassleftIsex($sid);
    
    /**
     * @return mixed
     * create by sjy
     * 查询未上课的体验课信息
     */
    public function selectIsexClassinfo($sid, $classleftid);

    /**
     * 获取转介绍的ID 用于判断是否存在转介绍的用户
     * @param $id
     * @return  array
     * create by  wangkai
     */
    public function getReferralChannel($id);

    /**
     * 获取用户信息OPenid
     * @param   $id
     * @return  array
     * create by  wangkai
     */
    public function getOpenIdByStudentId($id);

    /**
     * @param $openId
     * @return mixed
     * @created by Jhu
     * 通过openid获取unionid
     */
    public function getUnionIdByOpenId($openId);

    /**
     * @param $wclass_id
     * @return mixed
     * create by wangke
     * VIP陪练微课分享ID查询user_init的channel_id
     */
    public function getChannelIdByWechatClassId($sshareId);

    /**
     * 获取未跟进再分配（复购）
     * @param  $data
     * @return  array
     * create by  wangkai
     */
    public function getPurchaseUserAgainAllotNotFollowPage($data);

    /**
     * 获取未跟进再分配列表（复购）
     * @param  $data
     * @return  array
     * create by  wangkai
     */
    public function getPurchaseUserAgainAllotNotFollowList($data);

    /**
     * 根据学生ID获取学生姓名和渠道ID
     * @param   $uid
     * @return  array
     * create by  wangkai
     */
    public function getUserSaleidAndNick($uid);

    /**
     * 根据Open_Id获取用户的姓名和渠道
     * @param $open_id
     * @return  array
     * create by  wangkai
     * create time  2017/4/17
     */
    public function getUserSaleidAndNameByOpenid($openId);

    //根据渠道id去查询同一个推荐人的推荐数
    public function getChannelCount($channelId);

    //查询推荐人的信息
    public function getChannelChannelIdSelf($channelId);

    /**
     * 获取转介绍数量
     * @param   $type
     * @param   $keyword
     * @param   $account_id
     * @param   $start
     * @param   $end
     * @param   $kefu
     * @param   $isCheck   是否买单
     * @return  string
     * create by  wangkai
     * create time  2017/5/8
     */
    public function getUserIntroduceCount($type, $keyword, $accountId, $start, $end, $kefu, $isCheck);

    /**
     *  获取转介绍列表
     * @param   $type
     * @param   $keyword
     * @param   $account_id
     * @param   $num
     * @param   $start
     * @param   $end
     * @param   $kefu
     * @param   $isCheck
     * @return  array
     * create by  wangkai
     * create time  2017/5/8
     */
    public function getUserIntroduceList($type, $keyword, $accountId, $num, $start, $end, $kefu, $isCheck);

    /**
     * 获取没有排课的付费用户页面
     * @param  $key
     * @param  $value
     * @return int
     */
    public function getNoClassPurchasePage($key, $value);
}
