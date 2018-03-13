<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/12
 * Time: 下午3:53
 */
namespace common\sources\write\student;

use common\models\music\SalesChannelScan;
use common\models\music\User;
use common\models\music\UserAccount;
use common\models\music\UserInit;
use common\models\music\UserPublicArchiver;
use common\models\music\UserPublicInfo;
use common\models\music\VisitHistory;
use Yii;
use yii\db\ActiveRecord;
use common\models\music\WechatAcc;
use common\models\music\UserInstrument;
use common\models\music\UserChannel;
use common\models\music\ClassLeft;
use common\models\music\SalesTrade;
use common\models\music\UserPublic;
use common\models\music\CoursekefuBindHistory;
use common\models\music\UserPre;
use common\models\music\UserEventWeixin;
use common\models\music\StatisticsChannelInfo;

Class StudentAccess implements IStudentAccess {

    public function addUserVisitHistory($request)
    {
        $model = new VisitHistory();
        
        $model->user_id_visit = Yii::$app->user->identity->id;
        $model->student_id = $request['student_id'];
        $model->content = $request['content'];
        $model->is_ex = $request['is_ex'];
        $model->time_visit = strtotime($request['time_visit']);
        $model->time_created = time();
        $model->time_next = empty($request['next_visit_time']) ? 0 
            : strtotime($request['next_visit_time']);
        $model->next_content = $request['next_visit_mark'];
        
        return $model->save();
    }

    public function updateOrUserStatus($studentId, $bit)
    {
        $model = User::findOne($studentId);
        $model->status_bit = $model->status_bit | $bit;
        return $model->save();
    }
    
    public function updateOppositesUserStatus($studentId, $bit)
    {
        $model = User::findOne($studentId);
        $model->status_bit = $model->status_bit &(~$bit);
        return $model->save();
    }

    public function updateIntentionInfo($studentId, $intention, $kefuId)
    {
        $model = UserPublicInfo::findOne(['user_id' => $studentId]);

        $model->intention = $intention;
        $model->call_count = $model->call_count + 1;
        if($kefuId !== ''){
            $model->kefu_id = $kefuId;
        }

        
        return $model->save();
    }

    public function UpdateUserArchiveInfo($request, $is_keep)
    {
        $archive = UserPublicArchiver::findOne(['user_id' => $request['student_id']]);

        $archive->user_id = $request['student_id'];
        $archive->main_level = $request['m_level'];
        $archive->plan_day = strtotime($request['p_day']);
        $archive->plan_level = $request['p_level'];
        $archive->class_rate = $request['c_rate'];
        $archive->class_price = $request['c_price'];
        $archive->test_rate = $request['t_rate'];
        $archive->test_long_time = $request['t_long_time'];
        $archive->test_manager = $request['t_manager'];
        $archive->learn_begin_day = strtotime($request['l_beginday']);
        $archive->learn_time = $request['l_time'];
        $archive->teacher_preference = $request['t_preference'];
        $archive->is_keep = $is_keep;
        $archive->parent_analysed = $request['p_analysed'];
        $archive->teacher_wish = $request['t_wish'];
        $archive->suggest = $request['suggest'];
        
        return $archive->save();
    }

    public function addUserArchiveInfo($request, $is_keep)
    {
        $archive = new UserPublicArchiver();

        $archive->user_id = $request['student_id'];
        $archive->main_level = $request['m_level'];
        $archive->plan_day = strtotime($request['p_day']);
        $archive->plan_level = $request['p_level'];
        $archive->class_rate = $request['c_rate'];
        $archive->class_price = $request['c_price'];
        $archive->test_rate = $request['t_rate'];
        $archive->test_long_time = $request['t_long_time'];
        $archive->test_manager = $request['t_manager'];
        $archive->learn_begin_day = strtotime($request['l_beginday']);
        $archive->learn_time = $request['l_time'];
        $archive->teacher_preference = $request['t_preference'];
        $archive->is_keep = $is_keep;
        $archive->parent_analysed = $request['p_analysed'];
        $archive->teacher_wish = $request['t_wish'];
        $archive->suggest = $request['suggest'];
        
        return $archive->save();
    }

    public function updateStudentSalesId($saleId, $uid)
    {
        $student = User::findOne($uid);
        
        $student->sales_id = $saleId;
        
        return $student->save();
    }
    
    public function updateWechatSalesId($openId, $saleId)
    {
        $init = UserInit::findOne(['openid' => $openId]);
        
        $init->sales_id = $saleId;
        
        return $init->save();
    }

    public function updateStudentChannelId($channelId, $uid)
    {
        $student = User::findOne($uid);

        $student->channel_id = $channelId;
        $student->sales_id = 0;
        
        return $student->save();
    }


    public function addUserInitFromChannel($userInfo, $channelId)
    {
        $userInit = new UserInit();
        
        $userInit->openid = $userInfo['openid'];
        $userInit->name = $userInfo['nickname'];
        $userInit->province = $userInfo['province'];
        $userInit->city = $userInfo['city'];
        $userInit->head = $userInfo['headimgurl'];
        $userInit->subscribe_time = $userInfo['subscribe_time'];
        $userInit->remark = $userInfo['remark'];
        $userInit->channel_id = $channelId;
        $userInit->union_id = $userInfo['unionid'];

        
        return $userInit->save();
    }

    public function addUserInitFromChannelClass($userInfo, $channelId, $classId)
    {
        $userInit = new UserInit();

        $userInit->openid = $userInfo['openid'];
        $userInit->name = $userInfo['nickname'];
        $userInit->province = $userInfo['province'];
        $userInit->city = $userInfo['city'];
        $userInit->head = $userInfo['headimgurl'];
        $userInit->subscribe_time = $userInfo['subscribe_time'];
        $userInit->remark = $userInfo['remark'];
        $userInit->channel_id = $channelId;
        $userInit->class_id = $classId;
        $userInit->union_id = $userInfo['unionid'];


        return $userInit->save();
    }

    public function addUserInitFromSale($userInfo, $salesId)
    {
        $userInit = new UserInit();

        $userInit->openid = $userInfo['openid'];
        $userInit->name = $userInfo['nickname'];
        $userInit->province = $userInfo['province'];
        $userInit->city = $userInfo['city'];
        $userInit->head = $userInfo['headimgurl'];
        $userInit->subscribe_time = $userInfo['subscribe_time'];
        $userInit->remark = $userInfo['remark'];
        $userInit->sales_id = $salesId;
        $userInit->union_id = $userInfo['unionid'];

        return $userInit->save();
    }
    
    public function addUserInitFromSelf($userInfo)
    {
        $userInit = new UserInit();

        $userInit->openid = $userInfo['openid'];
        $userInit->name = $userInfo['nickname'];
        $userInit->province = $userInfo['province'];
        $userInit->city = $userInfo['city'];
        $userInit->head = $userInfo['headimgurl'];
        $userInit->subscribe_time = $userInfo['subscribe_time'];
        $userInit->remark = $userInfo['remark'];
        $userInit->union_id = $userInfo['unionid'];

        return $userInit->save();
    }

    public function updateUserInitSaleId($id, $saleId)
    {
        return Yii::$app->db->createCommand(
            "UPDATE user_init SET sales_id = :sales_id WHERE id = :id"
        )
            ->bindValues([
                ':sales_id' => $saleId,
                ':id' => $id
            ])->execute();
    }

    public function updateUserBuyTimes($uid)
    {
        $sql = "UPDATE user_public_info SET purchase = purchase + 1 WHERE user_id = :uid";
        
        return Yii::$app->db->createCommand($sql)
            ->bindValue(':uid', $uid)
            ->execute();
    }

    public function addUserInfo($phone, $name, $birth, $province, $city, $remark, $age, $level)
    {
        $student = new User();
        $student->user_id_add = Yii::$app->user->identity->id;
        $student->username = '';
        $student->mobile = $phone;
        $student->password = md5('123456');
        $student->role = 0;
        $student->sex = 0;
        $student->nick = $name;
        $student->birth = strtotime($birth);
        $student->province = $province;
        $student->city = $city;
        $student->teacher_prefer = 0;
        $student->teacher_2_prefer = 0;
        $student->teacher_3_prefer = 0;
        $student->teacher_recommend = 0;
        $student->self_class_time = 0;
        $student->student_level = 1;
        $student->ex_class_times = 1;
        $student->buy_class_times = 0;
        $student->channel_id = 0;
        $student->channel_id_self = 0;

        $student->head_icon = '';
        $student->link_person_name = '';
        $student->link_person_mobile = 0;

        $student->status_bit = 0;
        $student->label_allocate = 0;
        $student->label = '';
        $student->remark = $remark;
        $student->logout_time = 0;
        $student->time_created = time();
        $student->time_updated = 0;

        $student->is_auth = 1;
        $student->is_refund = 0;
        $student->is_disabled = 0;
        $student->is_high = 0;

        $student->age = $age;
        $student->last_level = $level;

        return [$student->save(), $student->attributes['id']];

    }

    public  function editUserInfo($studentID, $phone, $name, $birth, $province, $city, $remark, $age, $level)
    {
        $student = User::findOne($studentID);
        $student->nick = $name;
        $student->mobile = $phone;
        $student->age = $age;
        $student->last_level = $level;
        $student->birth = strtotime($birth);
        $student->province = $province;
        $student->city = $city;
        $student->remark = $remark;
        $student->time_updated = time();

        return $student->save();
        
    }


    public function  insertPianoInstrumentLevel($sid, $pianoLevel)
    {
        $piano = new UserInstrument();
        $piano->user_id = $sid;
        $piano->instrument_id = 1;
        $piano->type = 1;
        $piano->level = $pianoLevel;
        $piano->class_times = 0;

        return  $piano->save();
                        
    } 
    public function insertInstrumentLevel($sid,$instrumentLevel,$instrumentid){
        $instr = UserInstrument::findOne(['user_id'=>$sid,'instrument_id' => $instrumentid]);
        if(empty($instr))
        {
        $sql="insert into user_instrument(user_id,instrument_id,type,level,class_times) value(:user_id,:instrument_id,1,:level,0)";
        return Yii::$app->db->createCommand($sql)
                        ->bindValues([':user_id' => $sid, ':instrument_id' => $instrumentid, ':level' => $instrumentLevel])
                        ->execute();
        }else{
            $instr->level = $instrumentLevel;
            return $instr->save();
        }
        
        
    }

        public  function  updatePianoInstumentLevel($studentID, $level)
    {
        $piano = UserInstrument::findOne(['user_id'=>$studentID,'instrument_id' => 1]);
        $piano->level = $level;

        return $piano->save();
    }


    public function  insertVoilinInstrumentLevel($sid, $voilinLevel)
    {
        $voilin = new UserInstrument();
        $voilin->user_id = $sid;
        $voilin->instrument_id = 2;
        $voilin->type = 1;
        $voilin->level = $voilinLevel;
        $voilin->class_times = 0;

        return  $voilin->save();
                        
    } 


    public  function  updateVoilinInstumentLevel($studentID, $level)
    {
        $voilin = UserInstrument::findOne(['user_id'=>$studentID,'instrument_id' => 2]);
        $voilin->level = $level;
        
        return $voilin->save();
    }


    public  function  updateWechatInfo($uid, $openID)
    {
        $userAcc = new WechatAcc();
        $userAcc->uid = $uid;
        $userAcc->openid = $openID;

        return $userAcc->save();
    }


    public function insertClassLeftBean($uid, $type)
    {
        $classLeft = new ClassLeft();
        $classLeft->user_id = $uid;
        $classLeft->type = 1;
        $classLeft->instrument_id = $type;
        $classLeft->time_type = 2;
        $classLeft->name = "体验课";
        $classLeft->price = 8;
        $classLeft->total_amount = 1;
        $classLeft->amount = 1;
        $classLeft->ac_amount = 1;      


        return $classLeft->save();
    }

    public  function insertUserChannelBean($mobile, $sid, $nick)
    {
        $channel = new UserChannel();
        $channel->mobile = $mobile . $sid;
        $channel->password = md5('123456');
        $channel->name = $nick;
        $channel->role = 3;
        $channel->scene_id = '0';
        $channel->wechat_account = '';
        $channel->type = 2;
        $channel->qr_code = "";
        $channel->cash = 0;
        $channel->first_rate = 0;
        $channel->second_rate = 0;
        $channel->gift_class = 2;
        $channel->gift_first_exclass = 1;
        $channel->first_exclass_price = 0;
        $channel->time_created = time();
        $channel->time_updated = 0;
        $channel->logout_time = 0;
        $channel->is_first_login = 1;
        $channel->is_deleted = 0;

        return [$channel->save(), $channel->attributes['id']];
      
    }

    public  function  updateUserChannel($uid, $channel_id, $cid, $sales_id)
    {
//        $stu = User::findOne($uid);
//
//        $stu->channel_id = $channel_id;
//        $stu->channel_id_self = $cid;
//        $stu->sales_id = $sales_id;


        $sql = 'UPDATE user SET channel_id = :channel_id , channel_id_self = :cid , sales_id = :sales_id WHERE id = :uid ';

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':channel_id' => $channel_id,
                ':cid' => $cid,
                ':sales_id' => $sales_id,
                ':uid' => $uid
            ])
            ->execute();

    }


    public  function addStatisticsChannelInfo($channel_id, $today)
    {
        $info = new StatisticsChannelInfo();
        $info->channel_id = $channel_id;
        $info->time_day = $today;
        $info->time_date = date('Y-m-d', time());
        $info->new = 1;
        $info->first_exclass = 0;
        $info->second_exclass = 0;
        $info->user_buy = 0;
        $info->buy_price = 0;
        $info->user_rebuy = 0;
        $info->class_change = 0;
        $info->time_created = time();
        $info->time_updated = 0;

        return $info->save();
    }

    public  function editStatisticsChannelInfo($channel_id, $today)
    {
        $info = StatisticsChannelInfo::findOne([
                'channel_id' => $channel_id,
                'time_day' => $today
            ]);

        $info->new = $info->new + 1;
        $info->time_updated = time();

        return $info->save();
    }  

    public  function addSalesTrade($sales_id, $name, $uid, $money)
    {
        $trade = new SalesTrade();
        $trade->uid = $sales_id;
        $trade->fromUid = 0;
        $trade->studentID = $uid;
        $trade->studentName = $name;
        $trade->classID = 0;
        $trade->classType = 0;
        $trade->price = 0.00;
        $trade->recordID = 0;
        $trade->money = $money;
        $trade->descp = "通过您的渠道注册了新学生";
        $trade->comment = "通过您的渠道注册了新学生";
        $trade->status = 7;
        $trade->transaction_id = "0";
        $trade->time_created = time();
        $trade->is_deleted = 0;

        return $trade->save();
    }

    public  function updateUserPublicSale($uid, $nick, $mobile)
    {
        $public = new UserPublic();
        $public->user_id = $uid;
        $public->nick = $nick;
        $public->mobile = $mobile;
        $public->time_created = time();

        return $public->save();
    }

    public  function addUserPublicInfo($uid, $openID, $birth, $level, $province, $city)
    {
        $pinfo = new UserPublicInfo();
        $pinfo->user_id = $uid;
        $pinfo->open_id = $openID;
        $pinfo->birth = $birth;
        $pinfo->level = $level;
        $pinfo->province = $province;
        $pinfo->city = $city;
        if(in_array($city,[41,49,496,495])){
            $pinfo->area = 1;
        }else{
            $pinfo->area = 2;
        }

        return $pinfo->save();

    }


    public  function  updateUserPublicSales($studentID, $nick, $mobile)
    {
        $public = UserPublic::findOne(['user_id'=>$studentID]);
        $public->nick = $nick;
        $public->mobile = $mobile;
        $public->time_created = time();

        return $public->save(); 
    }


    public  function  updateUserPublicInfoSale($studentID, $birth, $last_level, $province, $city)
    {
        $pinfo = UserPublicInfo::findOne(['user_id'=>$studentID]);
        $pinfo->birth = $birth;
        $pinfo->level = $last_level;
        $pinfo->province = $province;
        $pinfo->city = $city;

        if(in_array($city,[41,49,496,495])){
            $pinfo->area = 1;
        }else{
            $pinfo->area = 2;
        }

        return $pinfo->save(); 
    }

    public  function updateUserInitInfoByOpenid($openID)
    {
        $userInit = UserInit::findOne(['openid' => $openID]);
        $userInit->is_bind = 1;

        return $userInit->save();
    }


    public  function  editStudentMark($userId,$request)
    {
        $student = User::find()->where(['id' => $userId])->one();

        foreach ($request as $key => $info) {
            if ($info !== null) {
                $student->$key = $info;
            }
        }

        return $student->save();
    } 

    public  function  doBindKefu($student_id, $kefu_id)
    {
        $sql = 'UPDATE user_public_info SET kefu_id = :kefuId, time_operated = :time_op'
            . ' WHERE user_id = :userid ';

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':kefuId' => $kefu_id,
                ':userid' => $student_id,
                ':time_op' => time()
            ])
            ->execute();
    }

    public  function  doBindKefuRe($studentId, $kefuId)
    {
        //一个修改
        $sql = 'UPDATE user_public_info SET kefu_id_re = :kefuId, time_operated_re = :time_op'
            . ' WHERE user_id = :userid ';

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':kefuId' => $kefuId,
                ':userid' => $studentId,
                ':time_op' => time()
            ])
            ->execute();
    }

    public  function markHighRiskUser($uid, $tag)
    {
        $query = User::findOne($uid);
        $query->is_high = $tag;

        return $query->save();
    }

    public function markHighRiskUserPublic($uid, $tag)
    {
        $query = UserPublicInfo::findOne(['user_id' => $uid]);
        $query->is_high = $tag;

        return $query->save();
    }

    public function deleteUser($studentId)
    {
        $student = User::find()->where(['id' => $studentId])->one();

        $student->is_disabled = 1;
        $student->save();
    }

    public  function deleteWechatInfo($id)
    {
        $sql = 'DELETE FROM wechat_acc WHERE id =:id';

        return  Yii::$app->db->createCommand($sql)
                    ->bindValue(':id',$id)
                    ->execute();
    } 


    public function deleteUserInit($openid)
    {
        $init = UserInit::find()->where(['openid' => $openid])->one();

        $init->is_bind = 0;
        $init->save();

    }


    public function bindUserPublicInfoBindKefu($user,$kefuId){
        $user->kefu_id_re = $kefuId;
        $user->time_operated_re = time();
        return $user->save();
    }
    
    public function distributeNewUser($userId,$kefuId)
    {
        $sql = 'UPDATE user_public_info SET kefu_id = :kefuId , time_operated = :time_op'
            . ' WHERE user_id = :userid ';
        
        return Yii::$app->db->createCommand($sql)
            ->bindValues([':kefuId' => $kefuId,
                ':userid' => $userId,
                ':time_op' => time()
            ])
            ->execute();
    }

    public function updateTimesInit($studentId)
    {
        $sql = "UPDATE user SET times_init = times_init - 1 WHERE id = :student_id AND times_init > 0";
        return Yii::$app->db->createCommand($sql)
            ->bindValue(':student_id', $studentId)
            ->execute();
    }
    
    public function distributeNotPurchase($userId,$kefuId){
        $sql = 'UPDATE user_public_info SET kefu_id = :kefuId ,kefu_id_re = 0, time_operated = :time_op'
            . ' WHERE user_id = :userid ';

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':kefuId' => $kefuId,
                ':userid' => $userId,
                ':time_op' => time()
            ])
            ->execute();
    }

    public function updateUserPublicInfoKefu($userId,$kefuId){
        //一个修改
        $sql = 'UPDATE user_public_info SET kefu_id = :kefuId ,kefu_id_tmp = :kefuId, time_operated = :time_op'
            . ' WHERE user_id = :userid ';

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':kefuId' => $kefuId,
                ':userid' => $userId,
                ':time_op' => time()
            ])
            ->execute();
    }



    public function updateAllUserInfoKefu($userId,$kefuId){
        //一个修改
        $sql = 'UPDATE sales_channel SET kefu_id = :kefuId'
            . ' WHERE id = :userid ';

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':kefuId' => $kefuId,
                ':userid' => $userId
            ])
            ->execute();
    }

    public function updateCoursekefuBindHistoryKefu($userId,$kefuId)
    {
        //一个添加
        $bind = new CoursekefuBindHistory();
        $bind->user_id = $userId;
        $bind->kefu_id = $kefuId;
        $bind->time_created = time();
        return $bind->save();
    }

    public function experienceMark($applyId)
    {
        $userApply = UserPre::findOne($applyId);
        $userApply->is_called = 1;

        return $userApply->save();
    }

    public function deleteExperience($applyId)
    {
        $userApply = UserPre::findOne($applyId);
        $userApply->is_deleted = 1;

        return $userApply->save();
    }

    public function editWeixinStatus($id,$result)
    {
        $query =  UserEventWeixin::findOne($id);

        $query->status = 1;
        $query->result = $result;

        return $query->save();
    }


    public  function editRefundUser($user_id)
    {
        $sql = "UPDATE user SET is_refund = 1 WHERE id = :user_id";
        
        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':user_id' => $user_id
            ])->execute();
    }



    public function deleteUserPulicInfo($user_id)
    {
        $sql = "UPDATE user_public SET is_deleted = 1 WHERE user_id = :user_id";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':user_id' => $user_id
            ])->execute();
    }

    public  function updateUserAccountHead($user_id, $head)
    {
        $sql = "UPDATE user_account  SET head = :head WHERE id=:id";

        return Yii::$app->db->createCommand($sql)
                            ->bindValues([
                                ':id' => $user_id,
                                ':head' => $head
                            ])->execute();   
    }


    public function addUserRegistration($user_id, $open_id)
    {
        $sql = "INSERT INTO user_registration (user_id, open_id, registration_time) VALUE(:user_id, :open_id, :registration)";

        return  Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':user_id' => $user_id,
                ':open_id' => $open_id,
                ':registration' => time()
            ])->execute();
    }



    public function addUserAttention($open_id)
    {
        $sql = "INSERT INTO user_attention (open_id, attention_time) VALUE(:open_id, :attention_time)";

        return  Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':open_id' => $open_id,
                ':attention_time' => time()
            ])->execute();
    }
    
    /*
     * 更新体验课套餐id
     *     create by sjy
     * 2017--2-21
     *  */
    public function updateClassLeftIsex($sid, $instrument){
        $sql = "UPDATE class_left  SET instrument_id = :instrument_id WHERE user_id=:user_id";
        return Yii::$app->db->createCommand($sql)
                            ->bindValues([
                                ':instrument_id' => $instrument,
                                ':user_id' => $sid
                            ])->execute();   
    }
    
    /*
     * 更新课程信息中的乐器id
     *     create by sjy
     * 2017--2-21
     *  */
    public function updateIsexClassinfo($classid,$instrumentid){
        $sql = "UPDATE class_room  SET instrument_id = :instrument_id WHERE id=:id";
        return Yii::$app->db->createCommand($sql)
                            ->bindValues([
                                ':instrument_id' => $instrumentid,
                                ':id' => $classid
                            ])->execute();   
    }
    
    /*
     * 更新课程编辑历史记录中的乐器id
     *     create by sjy
     * 2017--2-21
     *  */
    public function updateIsexClasshistory($historyid,$instrumentid){
        $sql = "UPDATE class_edit_history  SET instrument_id = :instrument_id WHERE id=:id";
        return Yii::$app->db->createCommand($sql)
                            ->bindValues([
                                ':instrument_id' => $instrumentid,
                                ':id' => $historyid
                            ])->execute();   
    }
    
     /*
     * 删除用户乐器信息
     * create by sjy
     * 2017--2-21
     *  */
    public function deleteInsertInstrumentLevel($sid,$instrumentLevel,$instrumentid){
       
        $instr = UserInstrument::findOne(['user_id'=>$sid,'instrument_id' => $instrumentid]);
        if(!empty($instr))
        {
            $sql = 'DELETE FROM user_instrument WHERE user_id = :user_id and instrument_id = :instrument_id ';

            return Yii::$app->db->createCommand($sql)
                                ->bindValues([
                                    ':user_id' => $sid,
                                    ':instrument_id' => $instrumentid
                                ])->execute();   
        } else {
            return true;
        }
    }

    public function unbindKefuByKefuId($kefu_id){

        $sql = "UPDATE user_public_info  SET kefu_id = 0, kefu_id_re = 0  WHERE kefu_id = :id OR kefu_id_re = :id";
        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':id' => $kefu_id
            ])->execute();
    }

    public function updateStudentEndVisitRecord($id)
    {
        $sql = 'UPDATE user_public_info SET end_visit_time = :time WHERE user_id = :id';
        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':id' => $id,
                ':time' => time(),
            ])->execute();
    }
    //根据渠道id去查询同一个推荐人的推荐数
    public function getChannelCount($channel_id)
    {
        return User::find()
            ->alias('u')
            ->select('u.nickname')
            ->leftJoin('product_order AS o', 'u.id = o.uid')
            ->where('u.channel_id=:channel_id', [':channel_id' => $channel_id])
            ->andWhere('o.channel_id=:pay_status', [':pay_status' => 1])
            ->count();
    }
}