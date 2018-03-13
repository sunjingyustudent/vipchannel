<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/12
 * Time: 下午5:39
 */
namespace common\sources\write\student;

use common\models\music\UserAccount;
use Yii;
use yii\db\ActiveRecord;

interface IStudentAccess {

    /**
     * @param $request
     * @return mixed
     * created by hujiyu
     * 添加回访纪录
     */
    public function addUserVisitHistory($request);

    /**
     * @param $bit
     * @return mixed
     * created by hujiyu
     * 更新或状态
     */
    public function updateOrUserStatus($studentId, $bit);
    
     /**
     * @param $bit
     * @return mixed
     * created by hujiyu
     * 更新与非状态
     */
    public function updateOppositesUserStatus($studentId, $bit);

    /**
     * @param $studentId
     * @param $intention
     * @param $kefuId
     * @return mixed
     * created by hujiyu
     * 标记意向更新信息
     */
    public function updateIntentionInfo($studentId, $intention, $kefuId);

    /**
     * @param $request
     * @return mixed
     * created by hujiyu
     * 更新用户回访基本信息
     */
    public function UpdateUserArchiveInfo($request, $is_keep);

    /**
     * @param $request
     * @return mixed
     * created by hujiyu
     * 添加用户回访基本信息
     */
    public function addUserArchiveInfo($request, $is_keep);

    /**
     * @param $saleId
     * @param $uid
     * @return mixed
     * @created by Jhu
     * 绑定主课老师渠道
     */
    public function updateStudentSalesId($saleId, $uid);

    /**
     * @param $openId
     * @param $info
     * @return mixed
     * @created by Jhu
     * 更新微信基本信息主课渠道绑定
     */
    public function updateWechatSalesId($openId, $saleId);

    /**
     * @param $channelId
     * @param $uid
     * @return mixed
     * @created by Jhu
     * 绑定老渠道
     */
    public function updateStudentChannelId($channelId, $uid);

    /**
     * @param $userInfo
     * @param $channelId
     * @return mixed
     * @created by Jhu
     * 添加来自老渠道的关注用户
     */
    public function addUserInitFromChannel($userInfo, $channelId);

    /**
     * @param $userInfo
     * @param $channelId
     * @param $classId
     * @return mixed
     * @created by Jhu
     * 添加来自老渠道的关注用户
     */
    public function addUserInitFromChannelClass($userInfo, $channelId, $classId);

    /**
     * @param $userInfo
     * @param $salesId
     * @return mixed
     * @created by Jhu
     * 添加来自分销渠道的关注用户
     */
    public function addUserInitFromSale($userInfo, $salesId);
    

    /**
     * @param $userInfo
     * @return mixed
     * @created by Jhu
     * 添加自主关注用户
     */
    public function addUserInitFromSelf($userInfo);

    /**
     * @param $userInfo
     * @param $key
     * @return mixed
     * @created by Jhu
     * 更新关注信息分销渠道
     */
    public function updateUserInitSaleId($id, $saleId);

    /**
     * @param $uid
     * @return mixed
     * @created by Jhu
     * 增加购买次数
     */
    public function updateUserBuyTimes($uid);
    


    /**
     * @param $kefuId
     * @return mixed
     * create by wangke
     * 未复购客服绑定
     */
    public function bindUserPublicInfoBindKefu($user,$kefuId);

    /**
     * @param $studentId
     * @return mixed
     * @created by Jhu
     * 更新timeeinit
     */
    public function updateTimesInit($studentId);
    
    /**
     * @param $userId
     * @param $kefuId
     * @return mixed
     * create by wangke
     * 为新用户分配新签客服
     */
    public function distributeNewUser($userId,$kefuId);

    /**
     * @param $userId
     * @param $kefuId
     * @return mixed
     * create by wangke
     * 管理视角 未复购再分配 分配新签客服
     */
    public function distributeNotPurchase($userId,$kefuId);

    /**
     * @param $userId
     * @param $kefuId
     * @return mixed
     * create by wangke
     * 管理视角 未跟进再分配 修改客服
     */
    public function updateUserPublicInfoKefu($userId,$kefuId);

    /**
     * @param $userId
     * @param $kefuId
     * @return mixed
     * create by wangke
     * 管理视角 未跟进再分配 修改客服绑定记录表
     */
    public function updateCoursekefuBindHistoryKefu($userId,$kefuId);

    /**
     * @param $studentId
     * @param $kefuId
     * @return mixed
     * create by wangke
     * 修改用户的 复购客服
     */
    public  function  doBindKefuRe($studentId, $kefuId);

    /**
     * 退费用户
     * @param $user_id
     * @return mixed
     */
    public  function editRefundUser($user_id);

    /**
     * 删除用户初始化内容
     * @param $openid
     * @return mixed
     */
    public function deleteUserInit($openid);

    /**
     * 删除用户
     * @param $student
     */
    public function deleteUser($studentId);

    /**
     * 删除用户微信端的信息
     * @param $id
     */
    public  function deleteWechatInfo($id);

    /**
     * 将用户修改成高位用户
     * @param  $uid
     * @param  $tag
     * @return mixed
     */
    public  function markHighRiskUser($uid, $tag);

    /**
     * 修改备注信息
     * @param  $userId
     * @return bool
     */
    public  function  editStudentMark($userId,$request);


    /**
     * 更新微信isBind
     * @param  openID
     * @return bool
     */
    public  function updateUserInitInfoByOpenid($openID);

    /**
     * 更新销售系统详情
     * @param   $studentID  
     * @param   $birth
     * @param   $last_level
     * @param   $province
     * @param   $city
     * @return  bool
     */
    public  function  updateUserPublicInfoSale($studentID, $birth, $last_level, $province, $city);


    /**
     * 更新销售系统
     * @param   $studentID  
     * @param   $nick
     * @param   $mobile
     * @return  bool
     */
    public  function  updateUserPublicSales($studentID, $nick, $mobile);

    /**
     * 添加用户详情的内容
     * @param $uid
     * @param $openID
     * @param $birth
     * @param $level
     * @param $province
     * @param $city
     * @return bool
     */
    public  function addUserPublicInfo($uid, $openID, $birth, $level, $province, $city);


    /**
     * 插入销售系统
     * @param $uid
     * @param $nick
     * @param $mobile
     * @return bool
     */
    public  function updateUserPublicSale($uid, $nick, $mobile);



    /**
     * 新增销售交易表
     * @param   $sales_id      int  
     * @param   $uid           str
     * @param   $money         int
     * @return   bool
     */  
    public  function addSalesTrade($sales_id, $name, $uid, $money);

    /**
     * 修改统计渠道信息
     * @param   $channel_id      int  
     * @param   $today           str
     * @return   bool
     */
    public  function editStatisticsChannelInfo($channel_id, $today);

    /**
     * 新增统计渠道信息
     * @param   $channel_id      int  
     * @param   $today           str
     * @return   bool
     */
    public  function addStatisticsChannelInfo($channel_id, $today);


    /**
     * 更新渠道ID和自推广ID到学生表
     * @param   $uid             int  
     * @param   $channel_id      int
     * @param   $cid             int
     * @param   $sales_id        int
     * @return   bool
     */
    public  function  updateUserChannel($uid, $channel_id, $cid, $sales_id);


    /**
     * 添加换课渠道
     * @param   $uid    int
     * @param   $type   int
     * @return  bool
     */
    public  function insertUserChannelBean($mobile, $sid, $nick);



    /**
     * 添加体验课时
     * @param   $uid    int
     * @param   $type   int
     * @return  bool
     */
    public  function  insertClassLeftBean($uid, $type);

    /**
     * 更新用户关系表
     * @param   $uid     int
     * @param   $openid  int
     * @return  bool
     */
    public  function  updateWechatInfo($uid, $openID);


    /**
     * 更新小提琴等级
     */
    public  function  updateVoilinInstumentLevel($studentID, $level);


    /**
     *  创建用户的小提琴等级信息
     *  @param  $sid            int
     *  @param  $voilinLevel    int
     *  @return bool
     */
    public function  insertVoilinInstrumentLevel($sid, $voilinLevel);

    /**
     * 更新钢琴等级
     */
    public  function  updatePianoInstumentLevel($studentID, $level);


    /**
     *  创建用户的钢琴等级信息
     *  @param  $sid           int
     *  @param  $pianoLevel    int
     *  @return bool
     */
    public function  insertPianoInstrumentLevel($sid, $pianoLevel);

    /**
     *  编辑用户信息
     *  @param  $studentIDint
     *  @param  $phone    int
     *  @param  $name     str
     *  @param  $birth    str
     *  @param  $province int
     *  @param  $city     int
     *  @param  $remark   int
     *  @param  $age      int
     *  @param  $level    int
     *  @return mixed
     */
    public  function editUserInfo($studentID, $phone, $name, $birth, $province, $city, $remark, $age, $level);

    /**
     * 添加用户
     *  @param  $phone    int
     *  @param  $name     str
     *  @param  $birth    str
     *  @param  $province int
     *  @param  $city     int
     *  @param  $remark   int
     *  @param  $age      int
     *  @param  $level    int
     *  @return mixed
     */
    public  function addUserInfo($phone, $name, $birth, $province, $city, $remark, $age, $level);
    /*
    添加用户乐器
     *     create by sjy
     * 2017--2-21
     *  */
    public function insertInstrumentLevel($sid,$instrumentLevel,$instrumentid);
    /*
     * 更新体验课套餐id
     *     create by sjy
     * 2017--2-21
     *  */
    public function updateClassLeftIsex($sid, $instrument);
    
    /*
     * 更新课程信息中的乐器id
     *     create by sjy
     * 2017--2-21
     *  */
    public function updateIsexClassinfo($classid,$instrumentid);
    
    /*
     * 更新课程编辑历史记录中的乐器id
     *     create by sjy
     * 2017--2-21
     *  */
    
    public function updateIsexClasshistory($historyid,$instrumentid);
    
    /*
     * 删除用户乐器信息
     * create by sjy
     * 2017--2-21
     *  */
    public function deleteInsertInstrumentLevel($sid,$instrumentLevel,$instrumentid);

    /**
     * @param $kefu_id
     * @return mixed
     * create by wangke
     * 如果客服被删除测
     */
    public function unbindKefuByKefuId($kefu_id);

    /**
     * 记录最后一次添加的回访记录到user_public_info表中
     * @param  $id
     * @return  mixed
     * create by  wangkai
     * create time  2017/4/14
     */
    public function updateStudentEndVisitRecord($id);

    /**
     * @param $uid
     * @param $tag
     * @return mixed
     * @created by Jhu
     * 在userpublicinfo里更新高危用户标志
     */
    public function markHighRiskUserPublic($uid, $tag);

    /**
     * 根据渠道id去查询同一个推荐人的推荐数
     */
    public function getChannelCount($channel_id);
}