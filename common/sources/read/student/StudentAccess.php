<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/12
 * Time: 下午3:53
 */
namespace common\sources\read\student;

use common\models\music\ChannelVisitHistory;
use common\models\music\SalesChannel;
use common\models\music\StudentUserShare;
use common\models\music\User;
use common\models\music\UserAccount;
use common\models\music\UserChannel;
use common\models\music\UserInit;
use common\models\music\UserPublic;
use common\models\music\UserPublicArchiver;
use common\models\music\UserPublicInfo;
use common\models\music\VisitHistory;
use common\models\music\WechatAcc;
use Yii;
use yii\db\ActiveRecord;
use common\models\music\ClassRoom;
use common\models\music\ClassEditHistory;
use common\models\music\ClassLeft;
use common\models\music\UserEventWeixin;
use common\models\music\Provinces;
use common\models\music\Cities;
use common\models\music\Instrument;
use common\models\music\UserInstrument;
use common\models\music\StatisticsChannelInfo;
use common\models\music\UserTeacher;
use common\models\music\userPre;
use common\models\music\StudentFixTime;
use common\models\music\ProductOrder;

class StudentAccess implements IStudentAccess
{

    public function getAllSales()
    {
        return UserAccount::find()->where(['role' => 1])->all();
    }


    public function countDistributeUser($keyword)
    {
        return UserPublic::find()
            ->alias('u')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            ->leftJoin('(SELECT student_id AS info 
                FROM visit_history AS vh 
                LEFT JOIN user_public_info AS ui on ui.user_id = vh.student_id 
                WHERE vh.time_created > ui.time_operated 
                GROUP BY info )AS v ', 'v.info = u.user_id')
            ->where(
                'i.kefu_id = :id AND u.is_deleted = 0 AND v.info IS NULL ' . (empty($keyword) ? '' : " AND (u.nick LIKE '%$keyword%' OR u.mobile LIKE '%$keyword%')"),
                [':id' => Yii::$app->user->identity->id])
            ->count();
    }

    public function getDistributeUserList($num, $keyword)
    {
        return UserPublic::find()
            ->alias('u')
            ->select('u.user_id as id, u.nick, u.mobile, i.birth, i.level, i.city, i.call_count, i.area, i.open_id, c.ex_class_times,c.class_id, ci.if_class_id ,c.course_info, u.time_created')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            ->leftJoin('(SELECT student_id, time_class AS ex_class_times,id AS class_id,course_info FROM class_room WHERE is_ex_class = 1 AND (status = 0 OR status = 1) AND is_deleted = 0 GROUP BY student_id) AS c', 'c.student_id = u.user_id')
            ->leftJoin('(SELECT student_id AS info
            FROM visit_history AS vh
            LEFT JOIN user_public_info AS ui on ui.user_id = vh.student_id
            WHERE vh.time_created > ui.time_operated
            GROUP BY info )AS v ', 'v.info =u.user_id')
            ->leftJoin('(select class_id AS if_class_id from class_image group by class_id) as ci', 'ci.if_class_id = c.class_id')
            ->where(
                'i.kefu_id = :id AND u.is_deleted = 0 AND v.info IS NULL ' . (empty($keyword) ? '' : " AND (u.nick LIKE '%$keyword%' OR u.mobile LIKE '%$keyword%')"),
                [':id' => Yii::$app->user->identity->id]
            )
            ->orderBy('u.time_created DESC')
            ->offset(($num - 1)*8)
            ->limit(8)
            ->asArray()
            ->all();
    }


    /**
     * 查询是否填写乐谱
     * @param  $student_id    学生id
     * @param  $time_class    上上时间
     * @return array
     */
    public function getCounrseInfo($studentId, $timeClass)
    {
        return ClassRoom::find()
                    ->select('id,course_info')
                    ->where('time_class=:time_class AND student_id=:student_id AND is_ex_class = 1 AND status = 1 AND is_deleted = 0', [':time_class' => $timeClass, ':student_id' => $studentId])
                    ->asArray()
                    ->one();
    }


    public function getChannelIdsByStudentId($studentId)
    {
        return User::find()
            ->select('channel_id, sales_id')
            ->where(['id' => $studentId])
            ->asArray()
            ->one();
    }

    public function getUserIdByChannelIdSelf($channelId)
    {
        return User::find()
            ->select('id')
            ->where(['channel_id_self' => $channelId])
            ->scalar();
    }

    public function getSalesByStudntId($userId)
    {
        return UserPublicInfo::find()
            ->alias('p')
            ->select('u.nickname')
            ->leftJoin('user_account AS u', 'u.id = p.kefu_id')
            ->where(['p.user_id' => $userId])
            ->scalar();
    }

    public function getStudentVisitInfoById($userId)
    {
        return UserPublicArchiver::find()
            ->where(['user_id' => $userId])
            ->asArray()
            ->all();
    }
    
    public function countVisitByStudentId($studentId)
    {
        return VisitHistory::find()
            ->where('student_id = :student_id', [':student_id' => $studentId])
            ->count();
    }

    public function getVisitHistoryList($studentId, $num)
    {
        return VisitHistory::find()
            ->alias('v')
            ->select('uk.id as kefu_id, uk.nickname as kefu_name, us.status_bit,v.time_next,v.next_content, v.content, v.is_ex, v.time_created, v.time_visit')
            ->leftJoin('user_account AS uk', 'uk.id = v.user_id_visit')
            ->leftJoin('user AS us', 'us.id = v.student_id')
            ->where(['v.student_id' => $studentId])
            ->orderBy('v.time_created DESC')
            ->offset(($num-1)*8)
            ->limit(8)
            ->asArray()
            ->all();
    }

    public function countUserArchiveInfo($studentId)
    {
        return UserPublicArchiver::find()
            ->where(['user_id' => $studentId])
            ->count();
    }

    public function countExUser($keyword, $time)
    {
        return UserPublic::find()
            ->alias('u')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            ->leftJoin('(SELECT student_id, teacher_id, time_class, time_end FROM class_room WHERE is_ex_class = 1 AND status != 2 AND status != 3 AND is_deleted = 0 GROUP BY student_id) AS c', 'c.student_id = u.user_id')
            ->where(
                'i.kefu_id = :id AND u.is_deleted = 0 AND c.student_id > 0 AND c.time_class >= :start AND c.time_class < :end' . (empty($keyword) ? '' : " AND (u.nick LIKE '%$keyword%' OR u.mobile LIKE '%$keyword%')"),
                [
                    ':id' => Yii::$app->user->identity->id,
                    ':start' => $time,
                    ':end' => $time + 86400
                ]
            )->count();
    }
    /**
     * 修改 wang
     */
    public function getTodoList($timeDay)
    {
        return VisitHistory::find()
            ->alias('v')
            ->select('v.student_id, v.id, v.next_content, u.nick as userName,u.mobile as userMobile,u.last_level,u.age, cr.ex_info,p.purchase,p.open_id')
            ->leftJoin('user AS u', 'u.id = v.student_id')
            ->leftJoin('user_public_info AS p', 'p.user_id = v.student_id')
            ->leftJoin('(SELECT student_id,COUNT(id) AS ex_info FROM class_room WHERE is_ex_class = 1 AND is_deleted = 0 AND status = 1)AS cr', 'cr.student_id = v.student_id')
            ->where(['v.user_id_visit' => Yii::$app->user->identity->id])
            ->andWhere('v.is_done = 0 AND v.time_next >= :start AND v.time_next < :end ', [
                ':start' => $timeDay, ':end' => $timeDay + 86400
            ])
            ->asArray()
            ->all();
    }


    
    /**
     * 未付费用户全部信息  按照 关注时间排序
     *@author 王可
     * */

    public function getNotPayAllUsers($kefuId, $num, $indention, $area, $exClass, $beforekeyword, $keyword)
    {
        $select = 'i.end_visit_time, uu.age,sc.nickname as channelname,uc.name as channelname_2, uc.type as channel_type,u.user_id as id, u.nick, u.mobile, i.open_id, i.birth, i.level, i.city, i.call_count,i.intention, i.purchase, i.area ,ui.subscribe_time, ui.openid,uu.accessToken';

        $obj=UserPublic::find()
            ->alias('u')
            ->select($select)
            ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            ->leftJoin('(SELECT user_id, COUNT(*) as cou  from class_left where type  =3 GROUP BY user_id ) as cl ', 'cl.user_id= u.user_id')
            ->leftJoin('user_init AS ui', 'ui.openid =i.open_id')
            ->leftJoin('user AS uu', 'uu.id =u.user_id')
            ->leftJoin('(SELECT user_id, SUM(ac_amount) as counts FROM class_left WHERE type = 3 GROUP BY user_id) AS l', 'u.user_id = l.user_id')
            ->leftJoin('sales_channel AS sc', 'sc.id =uu.sales_id')
            ->leftJoin('user_channel AS uc', 'uc.id =uu.channel_id');


        $sql_where =" cl.cou is null   AND i.kefu_id =".$kefuId;

        if ($indention != 99) {
            $sql_where .=" AND i.intention =".$indention;
        }

        if ($area != 99) {
            $sql_where .=" AND i.area =".$area;
        }

        switch ($beforekeyword) {
            case 0://全部
                break;
            case 1://用户姓名
                $sql_where .= " AND u.nick like '%$keyword%'";
                break;
            case 2://电话号码
                $sql_where .= " AND u.mobile like '%$keyword%'";
                break;
            case 3://渠道名称
                    $sql_where .= " AND ( sc.nickname like '%$keyword%' OR uc.name like '%$keyword%' )";
        }
        
        switch ($exClass) {
            case 0:
                $sql_where .= '';
                break;
            case 1:
                $sql_where .= ' AND c.counts > 0';
                break;
            case 2:
                $sql_where .= ' AND c.counts > 0';
                break;
            case 3:
                $sql_where .= ' AND c.student_id IS NULL';
                break;
            case 4:
                $sql_where .= ' AND cma.max_time = cmi.max_time';
                break;
        }

        switch ($exClass) {
            case 1:
                $obj = $obj
                    ->leftJoin('(SELECT student_id, COUNT(id) as counts FROM class_room WHERE is_ex_class = 1 AND status = 0 AND is_deleted = 0 GROUP BY student_id) AS c', 'c.student_id = u.user_id')
                    ->leftJoin('(SELECT student_id, teacher_id, time_class, time_end FROM class_room WHERE is_ex_class = 1 AND status = 0 AND is_deleted = 0 GROUP BY student_id) AS cc', 'cc.student_id = u.user_id')
                    ->leftJoin('user_teacher AS ut', 'ut.id = cc.teacher_id');
                break;
            case 2:
                $obj = $obj
                    ->leftJoin('(SELECT student_id, COUNT(id) as counts FROM class_room WHERE is_ex_class = 1 AND status = 1 AND is_deleted = 0 GROUP BY student_id) AS c', 'c.student_id = u.user_id');
                break;
            case 3:
                $obj = $obj
                    ->leftJoin('(SELECT student_id, COUNT(id) as counts FROM class_room GROUP BY student_id) AS c', 'c.student_id = u.user_id');
                break;
            case 4:
                $obj= $obj
                    ->leftJoin('(SELECT student_id, MAX(time_class) as max_time FROM class_room WHERE is_ex_class = 1  AND (status = 2 OR status = 3) AND is_deleted = 0 GROUP BY student_id) AS cma', 'cma.student_id = u.user_id')
                    ->leftJoin('(SELECT student_id, MAX(time_class) as max_time FROM class_room WHERE is_deleted = 0 GROUP BY student_id) AS cmi', 'cmi.student_id = u.user_id');
                break;
        }

        $res= $obj->where( $sql_where)
            ->orderBy('i.end_visit_time ASC')
            ->distinct()
            ->offset(($num - 1)*8)
            ->limit(8)
            ->asArray()
            ->all();

        return $res;
    }

    public function getNotPayAllUsersCount($kefuId, $indention, $area, $exClass, $beforekeyword, $keyword)
    {
        $obj=UserPublic::find()
            ->alias('u')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            ->leftJoin('(SELECT user_id, COUNT(*) as cou from class_left where type = 3 GROUP BY user_id ) as cl ', 'cl.user_id= u.user_id');

        $sql_where =" cl.cou is null AND i.kefu_id =".$kefuId;
        
        if ($indention != 99) {
            $sql_where .=" AND i.intention =".$indention;
        }

        if ($area != 99) {
            $sql_where .=" AND i.area =".$area;
        }

        switch ($beforekeyword) {
            case 0://全部
                break;
            case 1://用户姓名
                $sql_where .= " AND u.nick like '%$keyword%'";
                break;
            case 2://电话号码
                $sql_where .= " AND u.mobile like '%$keyword%'";
                break;
            case 3://渠道名称
                $sql_where .= " AND ( sc.nickname like '%$keyword%' OR uc.name like '%$keyword%' )";
                $obj = $obj->leftJoin('user AS uu', 'uu.id =u.user_id')
                    ->leftJoin('sales_channel AS sc', 'sc.id =uu.channel_id')
                    ->leftJoin('user_channel AS uc', 'uc.id =uu.channel_id');
        }

        switch ($exClass) {
            case 0:
                $sql_where .= '';
                break;
            case 1:
                $sql_where .= ' AND c.counts > 0';
                
                $obj = $obj
                    ->leftJoin('(SELECT student_id, COUNT(id) as counts FROM class_room WHERE is_ex_class = 1 AND status = 0 AND is_deleted = 0 GROUP BY student_id) AS c', 'c.student_id = u.user_id');
                break;
            case 2:
                $sql_where .= ' AND c.counts > 0';
                
                $obj = $obj
                    ->leftJoin('(SELECT student_id, COUNT(id) as counts FROM class_room WHERE is_ex_class = 1 AND status = 1 AND is_deleted = 0 GROUP BY student_id) AS c', 'c.student_id = u.user_id');
                break;
            case 3:
                $sql_where .= ' AND c.student_id IS NULL';
                
                $obj = $obj
                    ->leftJoin('(SELECT student_id, COUNT(id) as counts FROM class_room GROUP BY student_id) AS c', 'c.student_id = u.user_id');
                break;
            case 4:
                $sql_where .= ' AND cma.max_time = cmi.max_time';
                
                $obj= $obj
                    ->leftJoin('(SELECT student_id, MAX(time_class) as max_time FROM class_room WHERE is_ex_class = 1  AND (status = 2 OR status = 3) AND is_deleted = 0 GROUP BY student_id) AS cma', 'cma.student_id = u.user_id')
                    ->leftJoin('(SELECT student_id, MAX(time_class) as max_time FROM class_room WHERE is_deleted = 0 GROUP BY student_id) AS cmi', 'cmi.student_id = u.user_id');
                break;
        }

        $count= $obj->where($sql_where)
            ->distinct()
            ->count();
        return $count;
    }



    /**
     * 一条未付费用户的详细信息
     *@author 王可
     * */
    public function getNotPayUserDetailInfo($userId)
    {
        return UserPublicArchiver::find()
            ->where(['user_id'=>$userId])
            ->asArray()
            ->all();
    }


    /**
     * 获取已付款用户信息,并进行搜索
     * @User：王锴
     * @Time: 16/12/13 21:38
     * @param bodySql    string  查询条件
     * @param num        num     开始位置
     * @param limit      num     长度
     */
    public function getAllPayUserList($bodySql = null, $num = 1, $limit = 8)
    {
        $start = strtotime('monday last week');
        $end   = $start + 86400*7;
        // 用户id 用户昵称 用户手机号 创建时间 等级 地区 生日 总课程  剩余课程  实际剩余课程 姓名 被回访的次数 新渠道 老渠道 openid  最近上课时间  7日消耗数量

     
        $list = UserPublic::find()
                ->alias('u')
                ->select('u.user_id, u.nick, u.mobile, stu.class_consume,i.open_id,inf.introduce,cl.ac_amount,a.nickname AS kefu_name')
                ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
                ->leftJoin('user AS us', 'us.id = u.user_id')
                ->leftJoin('(SELECT channel_id,count(channel_id) AS introduce FROM user WHERE is_disabled = 0 AND channel_id != 0 AND sales_id = 0 GROUP BY channel_id)AS inf', 'inf.channel_id = us.channel_id_self')
                ->leftJoin('(SELECT student_id,count(id) as class_consume FROM class_room WHERE  (status = 1 OR status = 0) AND is_deleted = 0 AND time_class >= :start AND time_class < :end  GROUP BY student_id) AS stu', 'stu.student_id = u.user_id')
                ->leftJoin('(SELECT user_id, COUNT(CASE WHEN type = 3 AND left_bit & 4 = 0 THEN 1 END ) AS buy_info , SUM(ac_amount) AS ac_amount FROM class_left  GROUP BY user_id) AS cl', 'cl.user_id = u.user_id')
                ->leftJoin('user_account AS a', 'a.id = i.kefu_id_re')
                ->where('u.is_deleted = 0 AND i.kefu_id = :kefu AND cl.buy_info > 0'.(empty($bodySql) ? '' :$bodySql), [ ':start' => $start, ':end' => $end , ':kefu' => Yii::$app->user->identity->id])
                ->orderBy('inf.introduce DESC')
                ->offset(($num - 1)*8)
                ->limit($limit)
                ->asArray()
                ->all();
        return $list;
    }

    /**
     * 统计用户数量
     * @User：王锴
     * @Time: 16/12/13 21:38
     */
    public function getPayUserInfo($bodySql = null, $bodyParams = null)
    {
        $count = UserPublic::find()
            ->alias('u')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            ->leftJoin('(SELECT user_id ,COUNT(CASE WHEN type = 3 AND left_bit & 4 = 0 THEN 1 END ) AS buy_info , SUM(ac_amount) AS ac_amount FROM class_left GROUP BY user_id) AS cl', 'cl.user_id = u.user_id')
            ->where('u.is_deleted = 0 AND i.kefu_id = :kefu AND cl.buy_info > 0 '.(empty($bodySql) ? '' :$bodySql), [':kefu' => Yii::$app->user->identity->id])
            ->count();

        return $count;
    }


    /**
     * 获取新签用户的信息
     * @User：王锴
     * @Time: 16/12/13 21:38
     * @param bodySql    string  查询条件
     * @param num        num     开始位置
     * @param limit      num     长度
     */
    public function getAllNewPayUserList($bodySql = null, $num = 1, $limit = 8)
    {
        $list = UserPublic::find()
            ->alias('u')
            ->select('u.user_id ,us.nick,us.mobile,u.time_created,i.level,i.area,i.birth,l.total_amount,l.amount,l.amount,l.ac_amount,us.username,i.call_count,ns.nickname as newsale,os.name as oldsale,i.open_id,stu.max_time,t.nick as teacher_nick,t.teacher_level,stu.marks,stu.is_problem,os.type as channel_type')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            ->leftJoin('user AS us', 'us.id = u.user_id')
            ->leftJoin('(SELECT user_id as count_user_id,count(user_id) as count_id FROM class_left WHERE type = 3 GROUP BY user_id) AS cla', 'cla.count_user_id = u.user_id')
            ->leftJoin('(SELECT user_id,type,sum(ac_amount) as ac_amount,sum(total_amount) as total_amount,sum(amount) as amount FROM class_left WHERE type = 3 GROUP BY user_id  )AS l', 'l.user_id = u.user_id')
            ->leftJoin('(SELECT student_id,MAX(time_class) as max_time,teacher_id,marks,is_problem FROM class_room WHERE  status = 0  AND is_deleted = 0   GROUP BY student_id) AS stu', 'stu.student_id = u.user_id')
            ->leftJoin('user_teacher AS t', 't.id = stu.teacher_id')
            ->leftJoin('sales_channel AS ns', 'ns.id = us.sales_id')
            ->leftJoin('user_channel AS os', 'os.id = us.channel_id')
            ->where('l.type = 3  '.$bodySql)
            ->offset(($num - 1)*8)
            ->limit($limit)
            ->asArray()
            ->all();
        return $list;
    }

    /**
     * 查询未付费回访记录条数
     *@author 王可
     * */
    public function getNotPayUserVisitListCount($studentId)
    {
        return VisitHistory::find()
            ->where('student_id = :student_id', [':student_id' => $studentId])
            ->count();
    }

    public function getUserInfoByClassId($classId)
    {
        return User::find()
                ->alias('u')
                ->select('u.mobile, u.nick, uin.head, uin.name as wename, t.nick as teacher_name, c.time_class, c.time_end, c.is_ex_class')
                ->leftJoin('class_room as c', 'c.student_id = u.id')
                ->leftJoin('wechat_acc as we', 'we.uid = u.id')
                ->leftJoin('user_init as uin', 'uin.openid = we.openid')
                ->leftJoin('user_teacher as t', 't.id = c.teacher_id')
                ->where('c.id = :class_id', [':class_id'=>$classId])
                ->asArray()
                ->one();
    }

    public function getOpenIdByClassId($classId)
    {
        return WechatAcc::find()
                    ->alias('w')
                    ->select('w.openid')
                    ->leftJoin('class_room as c', 'c.student_id = w.uid')
                    ->where('c.id = :class_id', [':class_id'=>$classId])
                    ->scalar();
    }

    public function getUserById($id)
    {
        $data = User::findOne($id);

        return $data;
    }




    // 依昵称作为条件搜索渠道表中的id
    public function getSalesId($keyword)
    {
        $sql = "SELECT id FROM sales_channel WHERE status = 1 AND nickname LIKE '%$keyword%'";
        $channelIds = Yii::$app->db->createCommand($sql)->queryColumn();

        $oldsql = "SELECT id FROM  user_channel WHERE name LIKE '%$keyword%'";
        $oldchannelIds = Yii::$app->db->createCommand($oldsql)->queryColumn();

        return [$channelIds,$oldchannelIds];
    }

    // 获取新渠道ID
    public function getNewSalesId($channelIds)
    {
        $sql = "SELECT id FROM user WHERE sales_id IN(".implode(',', $channelIds).")";
        $studentIds = Yii::$app->db->createCommand($sql)->queryColumn();
        return empty($studentIds) ? array(-100) : $studentIds;
    }

    // 获取老渠道ID
    public function getOldSalesId($oldchannelIds)
    {
        $sql = "SELECT id FROM user WHERE channel_id IN(".implode(',', $oldchannelIds).")";
        $studentIds = Yii::$app->db->createCommand($sql)->queryColumn();
        return empty($studentIds) ? array(-100) : $studentIds;
    }


    //添加回访记录
    public function visitRec($studentId = '')
    {
        $list = UserPublicArchiver::find()
            ->where(
                ['user_id'=>$studentId]
            )->asArray()->all();
        return $list;
    }



    //获取历史记录
    public function buyHistory($request)
    {
        return ClassRoom::find()->select('time_class')
            ->where('student_id = :student_id', [':student_id' => $request['student_id']])
            ->andWhere(['is_ex_class' => 1])
            ->andWhere('status != 2 AND status != 3')
            ->andWhere(['is_deleted' => 0])
            ->orderBy('time_class ASC')
            ->column();
    }

    // 获取修改的历史记录
    public function buyEditHistory($request)
    {
        return ClassEditHistory::find()->select('price, amount, type, time_created')
                ->where('student_id = :student_id', [':student_id' => $request['student_id']])
                ->andWhere('price > 0')
                ->andWhere(['is_add' => 1, 'is_success' => 1, 'is_deleted' => 0])
                ->orderBy('time_created ASC')
                ->all();
    }

    // 获取到课程相关购买信息
    public function buyAmountInfo($request)
    {
        return  ClassLeft::find()
                    ->select('id, type, left_bit, name, total_amount, amount, ac_amount')
                    ->where(['user_id' => $request['student_id']])
                    ->orderBy('type ASC')
                    ->asArray()
                    ->all();
    }

    public function classHistoryPage($studentId)
    {
        return ClassEditHistory::find()
                ->where('student_id = :student_id', [':student_id' => $studentId])
                ->andWhere(['is_deleted' => 0, 'is_success' => 1])
                ->count();
    }

    public function classHistoryList($studentId, $num)
    {
        return ClassEditHistory::find()->select('id, student_id, amount, ex_old_amount, buy_old_amount, type, give_type, is_add,is_ex_class, comment, time_created, price')
                ->where('student_id = :student_id', [':student_id' => $studentId])
                ->andWhere(['is_deleted' => 0, 'is_success' => 1])
                ->andWhere('amount > 0')
                ->orderBy('time_created DESC')
                ->offset(($num - 1) * 8)
                ->limit(8)
                ->asArray()
                ->all();
    }
    
    public function getWechatRowByOpenId($openId)
    {
        return WechatAcc::find()
            ->where(['openid' => $openId])
            ->asArray()
            ->one();
    }
    
    public function countUserInitByOpenid($openId)
    {
        return UserInit::find()
            ->where(['openid' => $openId])
            ->count();
    }

    public function countPayToClassUser($keyword)
    {
        $res=UserPublic::find()
            ->alias('u')
             ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            ->where(
                ' i.kefu_id = :id AND i.purchase >= 1  AND u.is_deleted = 0 ' . (empty($keyword) ? '' : " AND (u.nick LIKE '%$keyword%' OR u.mobile LIKE '%$keyword%')"),
                [':id' => Yii::$app->user->identity->id]
            )
            ->count();

        return $res;
    }

    public function getPayToClassUserList($num, $keyword)
    {
        $time = time();
        $res=UserPublic::find()
            ->alias('u')
            ->select('ci.ci_class_id,c2.course_info,c2.id as class_id,sc.nickname as channelname,uc.name as channelname_2,
            uc.type as channel_type,u.user_id as id, u.nick, u.mobile,c.time_class,p.time_pay, i.open_id')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            ->leftJoin('(SELECT student_id, MIN(time_class) as time_class from class_room  where  time_class > :time and status=0 and is_deleted=0 GROUP BY student_id) AS c', 'c.student_id = u.user_id')

            ->leftJoin('class_room as c2', 'c2.time_class = c.time_class and c.student_id = c2.student_id')

            ->leftJoin('(select class_id AS ci_class_id from class_image group by class_id) as ci', 'ci.ci_class_id = c2.id')
            ->leftJoin('(select uid, MIN(time_pay) as time_pay from product_order  GROUP BY uid )  AS p', 'p.uid =u.user_id')
            ->leftJoin('user AS uu', 'uu.id =u.user_id')
            ->leftJoin('sales_channel AS sc', 'sc.id =uu.sales_id')
            ->leftJoin('user_channel AS uc', 'uc.id =uu.channel_id')
            ->where(
                ' i.kefu_id = :id AND i.purchase >= 1  AND u.is_deleted = 0  ' . (empty($keyword) ? '' : " AND (u.nick LIKE '%$keyword%' OR u.mobile LIKE '%$keyword%')"),
                [':id' => Yii::$app->user->identity->id, ':time' => $time]
            )
            ->orderBy('p.time_pay DESC')
            ->offset(($num - 1)*8)
            ->limit(8)
            ->asArray()
            ->all();

        return $res;
    }

    public function getUserInitInfoByOpenid($openID)
    {
        return UserInit::findOne(['openid' => $openID]);
    }

    public function getSalesChannelInfo($openID)
    {
//        return SalesChannel::findOne(['bind_openid' => $openID]);

        return SalesChannel::find()
                        ->where('bind_openid = :openID AND status = 1', [':openID' => $openID])
                        ->one();
    }
    
    public function getUserRowById($userId)
    {
        return User::find()
            ->where(['id' => $userId])
            ->asArray()
            ->one();
    }

    public function getUserIntentionInAddVisit($studentId)
    {
        return UserPublicInfo::find()
            ->select('intention')
            ->where('user_id='.$studentId)
            ->one();
    }

    public function countAllPurchasePage($keyword, $isFixTime)
    {

        $count = UserPublic::find()
            ->alias('u')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            ->leftJoin('(SELECT student_id, COUNT(id) AS is_fix_time FROM student_fix_time GROUP BY student_id) AS t ', 't.student_id = u.user_id')
            ->where(" i.kefu_id_re = :id " . (empty($isFixTime) ? " AND is_fix_time IS NULL" : " AND is_fix_time <> 0 ")  . (empty($keyword) ? "" : " AND (u.nick LIKE '%$keyword%' OR u.mobile LIKE '%$keyword%')"),
                [':id' => Yii::$app->user->identity->id])
            ->count();

        return $count;
    }



    /**
     * 复购视角  待跟进名单
     * @param  $keyword  str
     * @param  $start    str
     * @param  $end      str
     * @return array
     */
    public function getTodoPurchaseList($keyword, $start, $end)
    {
        return VisitHistory::find()
            ->alias('v')
            ->select('v.student_id, v.id, v.next_content, u.nick as userName, u.mobile as userMobile,u.last_level, u.age, p.purchase, p.open_id, l.ac_amount')
            ->leftJoin('user AS u', 'u.id = v.student_id')
            ->leftJoin('user_public_info AS p', 'p.user_id = v.student_id')
            ->leftJoin('(SELECT user_id,SUM(ac_amount) AS ac_amount FROM class_left WHERE type != 1 AND left_bit & 4 = 0  GROUP BY user_id) AS l', 'l.user_id = v.student_id')
            ->where(['v.user_id_visit' => Yii::$app->user->identity->id])
            ->andWhere('v.is_done = 0 AND v.time_next >= :start AND v.time_next < :end ' .(empty($keyword) ? "" : " AND (u.nick LIKE '%$keyword%' OR u.mobile LIKE '%$keyword%')"), [
                ':start' => $start, ':end' => $end
            ])
            ->asArray()
            ->all();
    }
    

    /**
     * 复购视角  没有排课
     * @param  $keyword  string
     * @param  $keyword  string
     * @return array
     */
    public function getNoClassPurchaseList($num, $key, $value)
    {

        return User::find()
                ->alias('u')
                ->select('u.nick, u.mobile, u.id, p.open_id, inf.introduce, max_class, no_week' )
                ->leftJoin('user_public_info AS p', 'p.user_id = u.id')
                ->leftJoin('(SELECT student_id, COUNT(CASE WHEN time_class >:time THEN 1 END ) AS is_noclass, MAX(time_class) AS max_class, MAX(CASE WHEN time_class >= :start THEN 1 END) AS no_week FROM class_room WHERE  is_deleted = 0 GROUP BY student_id) AS r', 'r.student_id = u.id')
                ->leftJoin('(SELECT open_id, count(*) AS introduce FROM complain GROUP BY open_id ) AS inf', 'inf.open_id = p.open_id')
                ->where('kefu_id_re = :id AND r.is_noclass = 0 AND u.is_disabled = 0 '. (empty($key)?'':$key), [':id' => Yii::$app->user->identity->id, ':time' => time(),':start' => time()- 86400*7])
                ->offset(($num - 1)*8)
                ->limit(8)
                ->asArray()
                ->all();
    }


    public function getNoClassPurchasePage($key, $value)
    {
        $count=  User::find()
                ->alias('u')
                ->leftJoin('user_public_info AS p', 'p.user_id = u.id')
                ->leftJoin('(SELECT student_id,  COUNT(CASE WHEN time_class >:time THEN 1 END ) AS is_noclass, MAX(time_class) AS max_class, MAX(CASE WHEN time_class >= :start THEN 1 END) AS no_week FROM class_room WHERE is_deleted = 0 GROUP BY student_id) AS r', 'r.student_id = u.id')
                ->where('kefu_id_re = :id AND r.is_noclass = 0 AND u.is_disabled = 0 '. (empty($key)?'':$key), [':id' => Yii::$app->user->identity->id, ':time' => time(), ':start' => time()- 86400*7])
                ->count();
        return $count;
    }


    /**
     * 复购视角  待复购名单
     * @param  $keyword  string
     * @param  $keyword  string
     * @return array
     */
    public function getRebuyPurchaseList($num, $amount)
    {
        $start = strtotime('monday last week');
        $end   = $start + 86400*7;

        return User::find()
                ->alias('u')
                ->select('u.nick, u.mobile, u.id, p.open_id, inf.introduce, max_class, class_consume, l.amount' )
                ->leftJoin('user_public_info AS p', 'p.user_id = u.id')
                ->leftJoin('(SELECT student_id, MAX(CASE WHEN time_class <:time THEN time_class END) AS max_class, count(CASE WHEN time_class >= :start AND time_class <:end THEN id END) as class_consume  FROM class_room WHERE is_deleted = 0   GROUP BY student_id) AS m', 'm.student_id = u.id')
                ->leftJoin('(SELECT user_id, SUM(ac_amount) AS amount FROM class_left GROUP BY user_id) AS l', 'l.user_id = u.id')
                ->leftJoin('(SELECT open_id, count(*) AS introduce FROM complain GROUP BY open_id ) AS inf', 'inf.open_id = p.open_id')
                ->where('kefu_id_re = :id '. (empty($amount)?' AND l.amount <= 5 ':$amount), [':id' => Yii::$app->user->identity->id, 'time' => time(), 'start' => $start, 'end' => $end])
                ->offset(($num - 1)*8)
                ->limit(8)
                ->asArray()
                ->all();
    }

    /**
     * 获取待复购页面
     * @param  $amount
     * @param  $value
     * @return int
     */
    public function getRebuyPurchasePage($amount, $value)
    {
        return User::find()
                ->alias('u')
                ->leftJoin('user_public_info AS p', 'p.user_id = u.id')
                ->leftJoin('(SELECT user_id, SUM(ac_amount) AS amount FROM class_left GROUP BY user_id) AS l', 'l.user_id = u.id')
                ->where('kefu_id_re = :id '. (empty($amount)?' AND l.amount <= 5':$amount), [':id' => Yii::$app->user->identity->id])
                ->count();
    }

    /**
     * 获取最近回访内容
     * @param  $student_id
     * @return str
     */
    public function getMaxVisitHistory($studentId)
    {
        return  VisitHistory::find()
                        ->select('content')
                        ->where('student_id = :student_id ', [':student_id' => $studentId])
                        ->orderBy('id DESC')
                        ->limit('1')
                        ->asArray()
                        ->scalar();
    }


    public function queryAllPurchaseList($keyword, $isFixTime, $num)
    {
        $list = UserPublic::find()
            ->alias('u')
            ->select('u.user_id AS id, u.nick, u.mobile ,i.open_id,i.purchase,p.time_pay,a.nickname AS kefu_name')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            ->leftJoin('user_account AS a', 'a.id = i.kefu_id')
            ->leftJoin('(SELECT uid, MAX(time_pay) AS time_pay FROM product_order WHERE pay_status = 1 GROUP BY uid ) AS p', 'p.uid = u.user_id')
            ->leftJoin('(SELECT student_id, COUNT(id) AS is_fix_time FROM student_fix_time GROUP BY student_id) AS t ', 't.student_id = u.user_id')
            ->where(" i.kefu_id_re = :id " . (empty($isFixTime) ? " AND is_fix_time IS NULL" : " AND is_fix_time <> 0 ")  . (empty($keyword) ? "" : " AND (u.nick LIKE '%$keyword%' OR u.mobile LIKE '%$keyword%')"),
                [':id' => Yii::$app->user->identity->id])
            ->orderBy('p.time_pay DESC')
            ->offset(($num - 1)*8)
            ->limit(8)
            ->asArray()
            ->all();
        return $list;
    }

    public function countPurchaseComplain($keyword)
    {
        $time = time();
        $start_time=$time-24*60*60*7;
        $count = UserPublic::find()
            ->alias('u')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            ->leftJoin('(SELECT open_id ,COUNT(id) AS cp_count FROM complain WHERE time_created BETWEEN :complain_starttime AND :complain_endtime GROUP BY open_id ) AS cp', 'cp.open_id = i.open_id')
            ->where("i.kefu_id_re = :id AND cp.cp_count IS NOT NULL " . (empty($keyword) ? "" : " AND (u.nick LIKE '%$keyword%' OR u.mobile LIKE '%$keyword%')"),
                [
                ':id' => Yii::$app->user->identity->id,
                ':complain_starttime' => $start_time,
                ':complain_endtime' => $time
                ])
            ->count();
        //var_dump($count);
        return $count;
    }

    public function getPurchaseComplainList($keyword, $num)
    {
        $time = time();
        $start_time = $time - 24 * 60 * 60 * 7;
        $list = UserPublic::find()
            ->alias('u')
            ->select('cp2.content AS complain_content,c2.marks,c2.time_class,c2.course_info,c2.id as class_id,
            i.purchase,u.user_id AS id, u.nick, u.mobile ,i.open_id, ci.ci_class_id')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            //投诉sql
            ->leftJoin('(SELECT open_id ,count(*) AS cp_count,MAX(time_created) AS time_created FROM complain WHERE time_created BETWEEN :complain_starttime AND :complain_endtime GROUP BY open_id ) AS cp', 'cp.open_id = i.open_id')
            ->leftJoin('complain AS cp2', 'cp2.open_id = cp.open_id  AND  cp2.time_created = cp.time_created')
            //课程sql
            ->leftJoin('(SELECT student_id, MIN(time_class) AS time_class FROM class_room  WHERE  time_class > :time AND status = 0 AND is_deleted = 0 GROUP BY student_id) AS c', 'c.student_id = u.user_id')
            ->leftJoin('class_room AS c2', 'c2.time_class = c.time_class AND c.student_id = c2.student_id')
            ->leftJoin('(select class_id AS ci_class_id FROM class_image GROUP BY class_id) AS ci', 'ci.ci_class_id = c2.id')
            ->where("i.kefu_id_re = :id AND cp.cp_count IS NOT NULL" . (empty($keyword) ? "" : " AND (u.nick LIKE '%$keyword%' OR u.mobile LIKE '%$keyword%')"),
                [
                    ':id' => Yii::$app->user->identity->id,
                    ':time' => $time,
                    ':complain_starttime' => $start_time,
                    ':complain_endtime' => $time
                ])
            ->orderBy('c2.time_class ASC')
            ->offset(($num - 1) * 8)
            ->limit(8)
            ->asArray()
            ->all();

        return $list;
    }

    /**
     * 学生详细信息
     */
    public function kefuCountStudent($request, $type)
    {
        $sqlOrder = $this->getOrderSql($type, $request);
        $studentIds = $this->getStudentIdListByChannel($request['keyword']);
        $sql = "SELECT COUNT(us.id) AS student_count ,SUM(sft.all_fix_times) AS all_fix_num FROM user AS us "
            . " LEFT JOIN (SELECT student_id, (CASE WHEN COUNT(id) >= 1 THEN 1 END) AS all_fix_times FROM student_fix_time GROUP BY student_id ) AS sft ON sft.student_id = us.id "
            . " WHERE us.role = 0 AND us.is_disabled = 0 "
            . (!empty($request['time_start']) && $request['time_type'] == 0 ? " AND us.time_created >= {$request['time_start']} AND us.time_created < {$request['time_end']}" : "")
            . (empty($request['keyword']) ? '' : " AND (us.nick LIKE '%{$request['keyword']}%' OR us.mobile LIKE '%{$request['keyword']}%' OR us.id IN(".implode(',', $studentIds)."))")
            . ($type != 1 ? " AND us.is_auth = 1".$sqlOrder : $sqlOrder);

        if (Yii::$app->user->identity->id == 27 || (Yii::$app->user->identity->id >= 345 && Yii::$app->user->identity->id <= 373)) {
            $sql .= " AND us.is_fix = 0";
        }

        return Yii::$app->db->createCommand($sql)->queryOne();
    }

    public function kefuGetStudentInfoList($request, $type)
    {
        $sqlOrder = $this->getOrderSql($type, $request);
        $studentIds = $this->getStudentIdListByChannel($request['keyword']);

        $sql = "SELECT sft.fix_times, us.id as user_id, us.kefu_id, us.nick, us.remark, us.city as city_id, IF(bh.is_buy>0,1,0) as is_buy, us.province as province_id, c.name as city_name, p.name as province_name, IFNULL(wu.openid,'') as openid, IFNULL(wu.head,'') as wechat_head, IFNULL(wu.name,'') as wechat_name, us.sales_id, uc.nickname as channel_name,olduc.name as oldchannel_name,FROM_UNIXTIME(us.birth, '%Y-%m-%d') as birth_date, us.birth, us.time_created, us.student_level, us.mobile, us.teacher_recommend as teacher_nick_recommend, us.ex_class_times, us.time_created, us.label, us.is_refund, cl.last_time, IFNULL(le.total_amount,0) as total_class, IFNULL(le.left_times,0) AS left_class FROM user AS us"
            . " LEFT JOIN sales_channel AS uc ON uc.id = us.sales_id"
            . " LEFT JOIN user_channel AS olduc ON olduc.id = us.channel_id"
            . " LEFT JOIN cities AS c ON c.id = us.city"
            . " LEFT JOIN provinces AS p ON p.id = us.province"
            . " LEFT JOIN (SELECT IFNULL(COUNT(id),0) as is_buy, student_id FROM class_edit_history WHERE price > 0 AND is_success = 1 AND is_deleted = 0 AND is_add = 1 GROUP BY student_id) AS bh ON bh.student_id = us.id"
            . " LEFT JOIN (SELECT SUM(ac_amount) AS left_times, SUM(total_amount) AS total_amount, user_id FROM `class_left` WHERE type IN(2,3) AND (left_bit & 4) = 0 GROUP BY user_id ORDER BY NULL) AS le ON le.user_id = us.id"
            . " LEFT JOIN (SELECT FROM_UNIXTIME(max(time_class),'%Y-%m-%d %H:%i:%S') AS last_time, student_id FROM class_room WHERE is_deleted = 0 AND status =1 GROUP BY student_id) AS cl ON cl.student_id = us.id"
            . " LEFT JOIN (SELECT w.uid, ui.openid, ui.name, ui.head FROM wechat_acc AS w LEFT JOIN user_init AS ui ON ui.openid = w.openid) AS wu ON wu.uid = us.id"

            . " LEFT JOIN (SELECT student_id, COUNT(id) AS fix_times FROM student_fix_time GROUP BY student_id ) AS sft ON sft.student_id = us.id"

            . " WHERE us.role = 0 AND us.is_disabled = 0"
            . (!empty($request['time_start']) && $request['time_type'] == 0 ? " AND us.time_created >= {$request['time_start']} AND us.time_created < {$request['time_end']}" : "")
            . (empty($request['keyword']) ? '' : " AND (us.nick LIKE '%{$request['keyword']}%' OR us.mobile LIKE '%{$request['keyword']}%'  OR us.id IN(".implode(',', $studentIds)."))")
            . ($type != 1 ? " AND us.is_auth = 1".$sqlOrder : $sqlOrder)
            . (Yii::$app->user->identity->id == 27 || (Yii::$app->user->identity->id >= 345 && Yii::$app->user->identity->id <= 373) ? " AND us.is_fix = 0" : "")
            . " ORDER BY us.time_created DESC"
            . " LIMIT :offset, :limit";
    
        if ($request['is_export'] == 0) {
            return Yii::$app->db->createCommand($sql)
                ->bindValues([':offset' => ($request['page_num'] -1)*8, ':limit' => 8])
                ->queryAll();
        } else {
            return Yii::$app->db->createCommand($sql)
                ->bindValues([':offset' => 0, ':limit' => 2000])
                ->queryAll();
        }
    }


    public function getOrderSql($type, $request)
    {
        $studentBuyList = Yii::$app->db->createCommand(
            'SELECT DISTINCT(user_id) FROM class_left WHERE type = 3'
        )->queryColumn();

        switch ($type) {
            case 0:
                return '';
            case 1:
                return ' AND us.is_auth = 0';
            case 2:
                return " AND us.id IN (".implode(',', $studentBuyList).")";
            case 3:
                return $this->get3OrderSql($studentBuyList);
            case 4:
                return $this->get4OrderSql($studentBuyList);
            case 5:
                return $this->get5OrderSql($studentBuyList, $request['time_type'], $request['time_start'], $request['time_end']);
            case 6:
                return " AND us.id NOT IN (".implode(',', $studentBuyList).")";
            case 7:
                return $this->get7OrderSql($studentBuyList);
            case 8:
                return $this->get8OrderSql($studentBuyList);
            case 9:
                return $this->get9OrderSql($studentBuyList, $request['time_type'], $request['time_start'], $request['time_end']);
            case 10:
                return $this->get10OrderSql($studentBuyList);
            case 11:
                return $this->getNoSecondExStudentByType($studentBuyList, 0, $request['time_type'], $request['time_start'], $request['time_end']);
            case 12:
                return $this->getNoSecondExStudentByType($studentBuyList, 1, $request['time_type'], $request['time_start'], $request['time_end']);
            case 13:
                return $this->get13OrderSql($studentBuyList);
            case 14:
                return $this->get14OrderSql($studentBuyList, $request['time_type'], $request['time_start'], $request['time_end']);
            case 15:
                return $this->get15OrderSql($studentBuyList, $request['time_type'], $request['time_start'], $request['time_end']);
            case 16:
                return $this->get16OrderSql($studentBuyList, $request['time_type'], $request['time_start'], $request['time_end']);
            case 17:
                return $this->get17OrderSql($studentBuyList);
            case 18:
                return $this->get18OrderSql($studentBuyList);
            case 19:
                return $this->get19OrderSql($studentBuyList, $request['visit_time']);
            case 20:
                return $this->get20OrderSql($studentBuyList, $request['visit_time']);
            case 21:
                return $this->getUserTimesNotEnough();
        }
    }

    public function get3OrderSql($studentBuyList)
    {
        $timeDay = strtotime(date('Y-m-d', time()));
        $sql = "SELECT student_id FROM class_room WHERE time_class >= :time_class AND time_class < :time_end AND is_deleted = 0 AND status != 2 AND status != 3 GROUP BY student_id";
        $studentIdList = Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':time_class' => $timeDay-86400*7,
                ':time_end' => $timeDay+86400*7,
            ])
            ->queryColumn();
        
        $s1ql = "SELECT lef.user_id FROM (SELECT SUM(ac_amount) AS left_times, user_id FROM `class_left` WHERE type IN(2,3) AND (type & 4) = 0 GROUP BY user_id) AS lef WHERE lef.left_times <= 0";
        $userList = Yii::$app->db->createCommand($s1ql)
            ->queryColumn();

        $studentIdList = array_merge($studentIdList, $userList);

        $sqlOrder = empty($studentIdList) ? " AND us.id IN (".implode(',', $studentBuyList).") AND us.is_refund = 0" : " AND us.id NOT IN (".implode(',', $studentIdList).") AND us.id IN (".implode(',', $studentBuyList).") AND us.is_refund = 0";
        return $sqlOrder;
    }

    public function get4OrderSql($studentBuyList)
    {
        $sql = "SELECT c.student_id FROM class_room AS c LEFT JOIN (SELECT student_id,max(time_class) as mtime_class FROM class_room WHERE time_class < :time_class GROUP BY student_id) AS cl ON c.student_id = cl.student_id and c.time_class = cl.mtime_class WHERE c.status = 2";
        $studentIdList = Yii::$app->db->createCommand($sql)
            ->bindValues([':time_class' => time()])
            ->queryColumn();
        $sqlOrder = empty($studentIdList) ? " AND us.id = -100": " AND us.id IN (".implode(',', $studentBuyList).") AND us.id IN (".implode(',', $studentIdList).")";
        return $sqlOrder;
    }

    public function get5OrderSql($studentBuyList, $timeType, $timeStart, $timeEnd)
    {
        $sql = "SELECT student_id FROM class_edit_history WHERE price > 0 AND is_add = 1 AND is_success = 1 AND is_deleted = 0".($timeType == 1 && $timeStart > 0 ? " AND time_created >= $timeStart AND time_created < $timeEnd" : "");
        $studentIdList = Yii::$app->db->createCommand($sql)->queryColumn();
        $sqlOrder = empty($studentIdList) ? " AND us.id = -100" : " AND us.id IN (".implode(',', $studentBuyList).") AND us.id IN (".implode(',', $studentIdList).")";
        return $sqlOrder;
    }

    public function get7OrderSql($studentBuyList)
    {
        $studentIdList = $this->getNoFirstExStudent();
        $sqlOrder = empty($studentIdList) ? " AND us.id = -100" : " AND us.id NOT IN (".implode(',', $studentBuyList).") AND us.id IN (".implode(',', $studentIdList).")";
        return $sqlOrder;
    }

    private function get8OrderSql($studentBuyList)
    {
        $sql = "SELECT u.id FROM user AS u LEFT JOIN class_room AS c ON u.id = c.student_id WHERE u.role = 0 AND u.is_disabled = 0 AND u.is_auth = 1 AND c.id IS NULL";
        $studentIdList = Yii::$app->db->createCommand($sql)->queryColumn();
        $sqlOrder = empty($studentIdList) ? " AND us.id = -100" : " AND us.id NOT IN (".implode(',', $studentBuyList).") AND us.id IN (".implode(',', $studentIdList).")";
        return $sqlOrder;
    }

    private function get9OrderSql($studentBuyList, $timeType, $timeStart, $timeEnd)
    {
        $NoFirstExStudents = $this->getNoFirstExStudent();
        $sql = "SELECT student_id FROM class_room WHERE status = 2 AND is_deleted = 0 AND is_ex_class = 1 AND student_id IN (".implode(',', $NoFirstExStudents).")".($timeType == 1 && $timeStart > 0 ? " AND time_class >= $timeStart AND time_end < $timeEnd" : "");
        $studentIdList = Yii::$app->db->createCommand($sql)->queryColumn();
        $sqlOrder = empty($studentIdList) ? " AND us.id = -100" : " AND us.id NOT IN (".implode(',', $studentBuyList).") AND us.id IN (".implode(',', $studentIdList).")";
        return $sqlOrder;
    }

    private function get10OrderSql($studentBuyList)
    {
        $studentIdList = $this->getNoSecondExStudent();
        $sqlOrder = empty($studentIdList) ? " AND us.id = -100" : " AND us.id NOT IN (".implode(',', $studentBuyList).") AND us.id IN (".implode(',', $studentIdList).")";
        return $sqlOrder;
    }

    private function get13OrderSql($studentBuyList)
    {
        $sql = "SELECT c.student_id FROM (SELECT student_id, COUNT(id) as counts FROM class_room WHERE is_ex_class = 1 AND status = 1 AND is_deleted = 0 GROUP BY student_id) AS c WHERE c.counts >= 2 AND c.student_id NOT IN (".implode(',', $studentBuyList).")";
        $studentIdList = Yii::$app->db->createCommand($sql)->queryColumn();
        $sqlOrder = empty($studentIdList) ? " AND us.id = -100" : " AND us.id IN (".implode(',', $studentIdList).")";
        return $sqlOrder;
    }

    private function get14OrderSql($studentBuyList, $timeType, $timeStart, $timeEnd)
    {
        $studentIdList = $this->getOrderedStudent($studentBuyList, $timeType, $timeStart, $timeEnd);
        $sqlOrder = empty($studentIdList) ? " AND us.id = -100" : " AND us.id IN (".implode(',', $studentIdList).")";
        return $sqlOrder;
    }

    private function get15OrderSql($studentBuyList, $timeType, $timeStart, $timeEnd)
    {
        $OrderedStudent = $this->getOrderedStudent($studentBuyList, $timeType, $timeStart, $timeEnd);
        if (!empty($OrderedStudent)) {
            $sql = "SELECT student_id FROM class_room WHERE status = 1 AND student_id IN (".implode(',', $OrderedStudent).")";
            $student2IdList = Yii::$app->db->createCommand($sql)->queryColumn();
            $studentIdList = array_diff($OrderedStudent, $student2IdList);
            $sqlOrder = empty($studentIdList) ? " us.id = -100" : " AND us.id IN (".implode(',', $studentIdList).")";
        } else {
            $sqlOrder = " AND us.id = -100";
        }
        return $sqlOrder;
    }

    private function get16OrderSql($studentBuyList, $timeType, $timeStart, $timeEnd)
    {
        $OrderedStudent = $this->getOrderedStudent($studentBuyList, $timeType, $timeStart, $timeEnd);
        if (!empty($OrderedStudent)) {
            $sql = "SELECT student_id FROM class_room WHERE status = 1 AND student_id IN (".implode(',', $OrderedStudent).")";
            $studentIdList = Yii::$app->db->createCommand($sql)->queryColumn();
            $sqlOrder = empty($studentIdList) ? " AND us.id = -100" : " AND us.id IN (".implode(',', $studentIdList).")";
        } else {
            $sqlOrder = " AND us.id = -100";
        }
        return $sqlOrder;
    }

    private function get17OrderSql($studentBuyList)
    {
        $sql = "SELECT u.id FROM user AS u LEFT JOIN wechat_acc AS w ON u.id = w.uid WHERE u.role = 0 AND u.is_auth = 1 AND u.is_disabled = 0 AND w.id IS NULL AND u.id NOT IN (".implode(',', $studentBuyList).")";
        $studentIdList = Yii::$app->db->createCommand($sql)->queryColumn();
        $sqlOrder = empty($studentIdList) ? " AND us.id = -100" : " AND us.id IN (".implode(',', $studentIdList).")";
        return $sqlOrder;
    }

    private function get18OrderSql($studentBuyList)
    {
        $sql = "SELECT u.id FROM user AS u LEFT JOIN wechat_acc AS w ON u.id = w.uid WHERE u.role = 0 AND u.is_auth = 1 AND u.is_disabled = 0 AND w.id IS NULL AND u.id IN (".implode(',', $studentBuyList).")";
        $studentIdList = Yii::$app->db->createCommand($sql)->queryColumn();
        $sqlOrder = empty($studentIdList) ? " AND us.id = -100" : " AND us.id IN (".implode(',', $studentIdList).")";
        return $sqlOrder;
    }

    private function get19OrderSql($studentBuyList, $visitTime)
    {
        $sqlWhere = empty($visitTime) ? '' : " WHERE time_visit >= $visitTime AND time_visit < ".($visitTime+86400);
        $sql = " SELECT student_id FROM visit_history".$sqlWhere;
        $studentIdList = Yii::$app->db->createCommand($sql)->queryColumn();
        $sqlOrder = empty($studentIdList) ? " AND us.id = -100": " AND us.id IN (".implode(',', $studentIdList).") AND us.id NOT IN (".implode(',', $studentBuyList).")";
        return $sqlOrder;
    }

    private function get20OrderSql($studentBuyList, $visitTime)
    {
        $sqlWhere = empty($visitTime) ? '' : " WHERE time_visit >= $visitTime AND time_visit < ".($visitTime+86400);
        $sql = " SELECT student_id FROM visit_history".$sqlWhere;
        $studentIdList = Yii::$app->db->createCommand($sql)->queryColumn();
        $sqlOrder = empty($studentIdList) ? " AND us.id = -100": " AND us.id IN (".implode(',', $studentIdList).") AND us.id IN (".implode(',', $studentBuyList).")";
        return $sqlOrder;
    }

    private function getUserTimesNotEnough()
    {
        $sql = "SELECT u.id FROM user AS u"
            . " LEFT JOIN (SELECT SUM(ac_amount) as left_times, user_id FROM class_left  GROUP BY  user_id) AS cl ON cl.user_id = u.id "
            . " LEFT JOIN class_edit_history AS h ON h.student_id = u.id"
            . " WHERE u.is_disabled = 0 AND h.is_add = 1 AND h.price > 0 AND h.is_success = 1 AND cl.left_times <= 5"
            . " AND cl.left_times > 0"
            . " GROUP BY u.id";
        $studentIdList = Yii::$app->db->createCommand($sql)->queryColumn();

        $sqlOrder = empty($studentIdList)  ? " AND us.id = -100 AND us.is_refund = 0"
            : " AND us.id IN (".implode(',', $studentIdList).") AND  us.is_refund = 0";
        return $sqlOrder;
    }

    private function getNoFirstExStudent()
    {
        $sql = "SELECT student_id FROM class_room WHERE is_ex_class = 1 AND status != 2 AND is_deleted = 0 GROUP BY student_id";
        $studentIdList = Yii::$app->db->createCommand($sql)->queryColumn();
        $sql = "SELECT id FROM user WHERE is_auth = 1 AND is_disabled = 0 AND role = 0 AND id NOT IN (".implode(',', $studentIdList).")";
        return Yii::$app->db->createCommand($sql)->queryColumn();
    }

    private function getNoSecondExStudent()
    {
        $sql = "SELECT c.student_id FROM (SELECT student_id, COUNT(id) as counts FROM class_room WHERE is_ex_class = 1 AND is_deleted = 0 AND status = 1 GROUP BY student_id) AS c WHERE c.counts = 1";
        $student2IdList = Yii::$app->db->createCommand($sql)->queryColumn();
        $sql = "SELECT c.student_id FROM (SELECT student_id, IFNULL(COUNT(id),0) as counts FROM class_room WHERE is_ex_class = 1 AND is_deleted = 0 AND status != 2 GROUP BY student_id) AS c WHERE c.counts = 1".(empty($student2IdList) ? " AND c.student_id = -100" : " AND c.student_id IN(".implode(',', $student2IdList).")");
        return Yii::$app->db->createCommand($sql)->queryColumn();
    }

    private function getOrderedStudent($studentBuyList, $timeType, $timeStart, $timeEnd)
    {
        $sql = "SELECT student_id FROM class_room WHERE is_ex_class = 1 AND status = 0 AND is_deleted = 0 AND student_id NOT IN (".implode(',', $studentBuyList).")"
            . ($timeType == 1 && $timeStart > 0 ? " AND time_class >= $timeStart AND time_class < $timeEnd" : "")
            . " GROUP BY student_id";
        return Yii::$app->db->createCommand($sql)->queryColumn();
    }

    private function getStudentIdListByChannel($keyword)
    {
        if (!empty($keyword)) {
            $sql = "SELECT id FROM sales_channel WHERE status = 1 AND nickname LIKE '%$keyword%'";
            $channelIds = Yii::$app->db->createCommand($sql)->queryColumn();

            $oldsql = "SELECT id FROM  user_channel WHERE name LIKE '%$keyword%'";
            $oldchannelIds = Yii::$app->db->createCommand($oldsql)->queryColumn();
            $studentIdsSales = array();
            $studentIdsChannel = array();
            if (!empty($channelIds)) {
                $sql = "SELECT id FROM user WHERE sales_id IN(".implode(',', $channelIds).")";
                $studentIds = Yii::$app->db->createCommand($sql)->queryColumn();
                $studentIdsSales = empty($studentIds) ? array(-100) : $studentIds;
            }
            if (!empty($oldchannelIds)) {
                $sql = "SELECT id FROM user WHERE channel_id IN(".implode(',', $oldchannelIds).")";
                $studentIds = Yii::$app->db->createCommand($sql)->queryColumn();
                $studentIdsChannel = empty($studentIds) ? array(-100) : $studentIds;
            }

            $res = array_merge($studentIdsChannel, $studentIdsSales);

            if (empty($res)) {
                $res = array(-100);
            }
            return $res;
        }
        return array(-100);
    }

    /**
     * 获取用户跟乐器的关联
     * @param $userId
     * @return array
     */
    public function getInstrumentLevelByStudentId($userId)
    {
        $sql = "SELECT ui.instrument_id, ui.level, i.name FROM user_instrument AS ui LEFT JOIN instrument AS i ON i.id = ui.instrument_id WHERE ui.user_id = :user_id AND ui.type = 1 GROUP BY ui.instrument_id";
        return Yii::$app->db->createCommand($sql)->bindValue(':user_id', $userId)->queryAll();
    }

    /**
     * 获取用户绑定客服id
     * @param  $student_id  int
     * @array
     */
    public function getUserBindInfo($studentId)
    {
        return UserPublicInfo::find()
                    ->alias('u')
                    ->select('u.user_id as id, u.kefu_id, k.nickname, u.kefu_id_re,k2.nickname AS nickname_re')
                    ->leftJoin('user_account AS k', 'u.kefu_id = k.id')
                    ->leftJoin('user_account AS k2', 'u.kefu_id_re = k2.id')
                    ->where(['u.user_id' => $studentId])
                    ->asArray()
                    ->one();
    }

    /**
     * 获取用户绑定客服id
     * @param  $student_id  int
     * @array
     */
    public function getWechatIdByOpenId($openId)
    {
            return WechatAcc::find()
                ->select('uid')
                ->where(['openid' => $openId])
                ->scalar();
    }

    /**
     * 获取客服列表
     * @param  $student_id  int
     * @array
     */
    public function getKefuList()
    {
        return UserAccount::find()
            ->select('id, nickname')
            ->where(['role' => 1, 'status' => 1])
            ->asArray()
            ->all();
    }

    public function getStudentList($filter)
    {
        return User::find()
            ->select('id, nick')
            ->where("nick LIKE '%$filter%'")
            ->asArray()
            ->all();
    }

    public function getStudentOpenId($studentId)
    {
        return WechatAcc::find()
            ->select('openid')
            ->where('uid = :student_id', [':student_id' => $studentId])
            ->scalar();
    }

    /**
     * 获取学生初始化信息
     * @param   $openID   str
     * @return  array
     */
    public function getUserInitInfo($openID)
    {
        return  UserInit::find()
                        ->alias('w')
                        ->select('w.name,w.head,w.subscribe_time,w.channel_id,w.sales_id,user.*')
                        ->leftJoin('wechat_acc as a', 'w.openid = a.openid')
                        ->leftJoin('user', 'user.id = a.uid')
                        ->where('w.openid=:openid', [':openid' => $openID])
                        ->asArray()
                        ->one();
    }

    /**
     * 获取学生活动渠道信息
     * @param   $openID   str
     * @return  array
     */
    public function getUserEventWeixinInfo($openID)
    {
        return UserEventWeixin::findOne(['openid'=>$openID]);
    }

    /**
     * 获取省份列表
     * @return array
     */
    public function getProvincesList()
    {
        return Provinces::find()
                        ->asArray()
                        ->all();
    }

    /**
     * 获取城市列表
     * @param  $pid  int  省份ID
     * @return array
     */
    public function getCityList($pid)
    {
        return  Cities::find()
                     ->where('pid=:pid', [':pid' => $pid])
                     ->asArray()
                     ->all();
    }

    /**
     * 获取乐器列表
     * @return array
     */
    public function getInstrumentList()
    {
        return  Instrument::find()
                     ->select('*')
                     ->asArray()
                     ->all();
    }

    /**
     * 获取用户乐器等级信息
     * @param   $user_id  int
     * @return  str
     */
    public function getUserInstrumentLevel($userId, $instrumentId)
    {
        return  UserInstrument::find()
                    ->select("level")
                    ->where('user_id=:uid', [':uid' => $userId])
                    ->andWhere('instrument_id=:id', [':id' => $instrumentId])
                    ->andWhere('type=1')
                    ->asArray()
                    ->one();
    }

    /**
     * 查询该手机号是否存在
     * @param   $phone   int
     * @return  array
     */
    public function getUserPhoneExist($phone)
    {
        return  User::find()
                    ->select('id')
                    ->Where('mobile=:phone', [':phone' => $phone])
                    ->andWhere('is_disabled = 0')
                    ->asArray()
                    ->one();
    }

    /**
     * 添加渠道费用
     * @param   $channel_id   int
     * @param   $today        str
     * @return  array
     */
    public function getStatisticsChannelInfo($channelId, $today)
    {
        return StatisticsChannelInfo::findOne([
            'channel_id' => $channelId,
            'time_day' => $today
        ]);
    }



    /**
     * 查找新销售渠道ID
     * @param  $sales_id
     * @return array
     */
    public function getSalesChannelId($salesId)
    {
        return SalesChannel::findOne(["id"=>$salesId,"status"=>1]);
    }

    /**
     * 获取修改用户的手机是否存在
     * @param   $studentID     int
     * @param   $phone   int
     * @return  array
     */
    public function getEditUserInfo($studentID, $phone)
    {
        return User::find()
                    ->select('id')
                    ->where('id != :sid', [':sid' => $studentID])
                    ->andWhere('mobile=:phone', [':phone' =>$phone])
                    ->andWhere('is_disabled = 0')
                    ->asArray()->one();
    }
    /**
     * 查询用户的钢琴信息
     * @param   $studentID     int
     * @return  array
     */
    public function getUserPianoCheck($studentID)
    {
        return UserInstrument::find()
                            ->select('*')
                            ->where('user_id=:uid', [':uid' => $studentID])
                            ->andWhere('instrument_id=1 and type=1')
                            ->ASarray()
                            ->one();
    }

    /**
     * 查询用户的小提琴信息
     * @param   $studentID     int
     * @return  array
     */
    public function getUserVoilinCheck($studentID)
    {
        return  UserInstrument::find()
                            ->select('*')
                            ->where('user_id=:uid', [':uid' => $studentID])
                            ->andWhere('instrument_id=2 and type=1')
                            ->one();
    }

    /**
     * 获取备注信息
     */
    public function getStudentRemark($studentId)
    {
        $sql = "SELECT u.remark_out, u.remark, u.self_class_time, u.teacher_prefer, u.teacher_2_prefer, u.teacher_3_prefer, uf.nick as teacher_name, us.nick as teacher_2_name, ut.nick as teacher_3_name FROM user AS u"
            . " LEFT JOIN user_teacher AS uf ON uf.id = u.teacher_prefer"
            . " LEFT JOIN user_teacher AS us ON us.id = u.teacher_2_prefer"
            . " LEFT JOIN user_teacher AS ut ON ut.id = u.teacher_3_prefer"
            . " WHERE u.id = :student_id";
        return Yii::$app->db->createCommand($sql)->bindValue(':student_id', $studentId)->queryOne();
    }

    /**
     * 获取老师昵称基本信息
     */
    public function getTeacherName()
    {
        return  UserTeacher::find()
                    ->select('id, nick')
                    ->asArray()
                    ->all();
    }


    /**
     * 展示回访信息页面
     * @param  $student_id  int
     * @return array
     */
    public function getUserPublicArchiverInfo($studentId)
    {
        return  UserPublicArchiver::find()
                        ->where(['user_id'=>$studentId])
                        ->asArray()
                        ->all();
    }


    /**
     * 查询微信渠道注册的用户是否存在
     */
    public function getUserExist($studentId)
    {
        return  WechatAcc::find()
                    ->select('id, openid')
                    ->where(['uid' => $studentId])
                    ->one();
    }

    public function getStudentFixTimeById($studentId)
    {
        $sql = "SELECT t.week, t.time, t.class_type, t.teacher_id, u.nick FROM student_fix_time AS t LEFT JOIN user_teacher AS u ON u.id = t.teacher_id WHERE t.student_id = :student_id ORDER BY t.week ASC";
        return Yii::$app->db->createCommand($sql)->bindValue(':student_id', $studentId)->queryAll();
    }

    public function getUserByLeftId($userId)
    {
        return User::findOne($userId)->toArray();
    }

    public function countAllotPurchase($keyword, $start, $end)
    {
        $obj = UserPublic::find()
            ->alias('u')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            //为了区别 未复购再分配 修改后的信息
            ->leftJoin('(SELECT user_id,COUNT(user_id) AS class_num FROM class_left WHERE type = 3 AND ac_amount > 0 GROUP BY user_id) AS cl', 'cl.user_id = u.user_id');
        if (!empty($start) && !empty($end)) {
            $obj = $obj->leftJoin('(select uid, MAX(time_pay) as time_pay FROM product_order WHERE pay_status = 1 GROUP BY uid )  AS p', 'p.uid =u.user_id');
        }

        $obj = $obj->where("i.purchase >= 1 AND u.is_deleted = 0 AND i.kefu_id_re = 0 AND cl.class_num IS NOT NULL"
                . (empty($keyword) ? "" : " AND (u.nick LIKE '%$keyword%' OR u.mobile LIKE '%$keyword%')"));

        if (!empty($start) && !empty($end)) {
             $obj = $obj->andWhere('p.time_pay BETWEEN :start AND :end', [
                ':start' => $start,
                ':end' => $end
             ]);
        }
        return $obj->count();
    }

    public function getAllotPurchaseList($keyword, $start, $end, $num)
    {
        $obj = UserPublic::find()
            ->alias('u')
            ->select('u.user_id AS id, u.nick AS username, u.mobile ,i.open_id, p.time_pay, a.nickname AS kefu_nick')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            ->leftJoin('(select uid, MAX(time_pay) as time_pay FROM product_order WHERE pay_status = 1 GROUP BY uid )  AS p', 'p.uid =u.user_id')
            ->leftJoin('user_account AS a', 'a.id = i.kefu_id')
            //为了区别 未付购再分配 修改后的信息
            ->leftJoin('(SELECT user_id,COUNT(user_id) AS class_num FROM class_left WHERE type = 3 AND ac_amount > 0 GROUP BY user_id) AS cl', 'cl.user_id = u.user_id')
            ->where("i.purchase >= 1 AND u.is_deleted = 0 AND i.kefu_id_re = 0 AND cl.class_num IS NOT NULL "
                . (empty($keyword) ? "" : " AND (u.nick LIKE '%$keyword%' OR u.mobile LIKE '%$keyword%')"));

        if (!empty($start) && !empty($end)) {
            $obj = $obj->andWhere('p.time_pay BETWEEN :start AND :end', [
                ':start' => $start,
                ':end' => $end
            ]);
        }

        $list = $obj->orderBy('u.time_created DESC')
            ->offset(($num - 1) * 8)
            ->limit(8)
            ->asArray()
            ->all();

        return $list;
    }

    public function countUserAccountDistribute($kefuId)
    {
        return UserPublicInfo::find()
            ->where(['kefu_id_re' => $kefuId])//当前接待顾问ID
            ->count();
    }

    public function getUserPublicInfoKefuid($userId)
    {
        return UserPublicInfo::findOne(['user_id' => $userId]);
    }

    public function getOpenidByUid($uid)
    {
        return WechatAcc::find()
            ->select('openid')
            ->where(['uid' => $uid])
            ->scalar();
    }

    public function getUidByOpenid($openId)
    {
        return WechatAcc::find()
            ->select('uid')
            ->where(['openid' => $openId])
            ->scalar();
    }

    public function getStudentIsDanger($userId)
    {
        return User::find()
            ->where('id = :id AND is_high = 1', [
                ':id' => $userId
            ])->count();
    }

    public function getUserName($openId)
    {
        return UserInit::find()
            ->select('name')
            ->where(['openid' => $openId])
            ->scalar();
    }
    
    public function countAllotNewUser($introduce, $start, $end)
    {
        $obj = UserPublic::find()
            ->alias('u')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            ->where('u.is_deleted = 0 AND i.kefu_id = 0 ');

        if (!empty($start) && !empty($end)) {
            $obj = $obj->andWhere('u.time_created BETWEEN :start AND :end', [
                ':start' => $start,
                ':end' => $end
            ]);
        }
        if (!empty($introduce)) {
            $obj = $obj->leftJoin('user AS uu', 'uu.id = u.user_id')
                ->leftJoin('(SELECT id ,nick,channel_id_self FROM user where channel_id_self > 0) AS uu2', 'uu2.channel_id_self = uu.channel_id')
                ->leftJoin('user_public_info AS i2', 'i2.user_id = uu2.id')
                ->andWhere('i2.kefu_id = :kefu_id', [
                    ':kefu_id' => $introduce
                ]);
        }
        return $obj->count();
    }

    public function getAllotNewUserList($introduce, $start, $end, $num)
    {
        //要对数据去重
        $obj = UserPublic::find()
            ->alias('u')
            ->select('u.user_id,u.nick,u.mobile,u.time_created,sc.nickname as channelname,uc.name as channelname_2,
            uc.type as channel_type,a.nickname AS kefu_nick,uu2.nick AS jiazhang')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            //查询渠道
            ->leftJoin('user AS uu', 'uu.id =u.user_id')
            ->leftJoin('sales_channel AS sc', 'sc.id =uu.sales_id')
            ->leftJoin('user_channel AS uc', 'uc.id =uu.channel_id')
            //查询转介绍(家长)的客服姓名
            ->leftJoin('(SELECT id ,nick,channel_id_self FROM user where channel_id_self > 0) AS uu2', 'uu2.channel_id_self = uu.channel_id')
            ->leftJoin('user_public_info AS i2', 'i2.user_id = uu2.id')
            ->leftJoin('user_account AS a', 'a.id = i2.kefu_id')
            ->where('u.is_deleted = 0 AND i.kefu_id = 0 ');

        if (!empty($start) && !empty($end)) {
            $obj = $obj->andWhere('u.time_created BETWEEN :start AND :end', [
                ':start' => $start,
                ':end' => $end
            ]);
        }
        if (!empty($introduce)) {
            $obj = $obj->andWhere('i2.kefu_id = :kefu_id', [
                    ':kefu_id' => $introduce
                ]);
        }

        $list = $obj->orderBy('uu.time_created DESC')
            ->offset(($num - 1) * 8)
            ->limit(8)
            ->asArray()
            ->all();
        return $list;
    }

    public function countAgainAllotNotPay($keyword)
    {
        $time = time() - 14*24*60*60;//这个时间应是从当前算起的之前14天    和未跟进的不同
        $obj = UserPublic::find()
            ->alias('u')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            ->leftJoin('(SELECT student_id, MAX(time_end) AS latest_ex_time FROM class_room WHERE is_ex_class = 1 AND status = 1 GROUP BY student_id) AS cl', 'cl.student_id = u.user_id')
            ->where('u.is_deleted = 0 AND i.purchase = 0 AND cl.latest_ex_time < :time '
                . (empty($keyword) ? "" : " AND (u.nick LIKE '%$keyword%' OR u.mobile LIKE '%$keyword%')"), [
                ':time' => $time]);
        return $obj->count();
    }

    public function getAgainAllotNotPayList($keyword, $num)
    {
        $time = time() - 14*24*60*60;
        $list = UserPublic::find()
            ->alias('u')
            ->select('u.user_id AS id, u.nick AS username, u.mobile ,i.open_id,cl.latest_ex_time,i.kefu_id,cp.complain_time,a.nickname')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            ->leftJoin('(SELECT student_id, MAX(time_class) AS latest_ex_time FROM class_room WHERE is_ex_class = 1 AND status = 1 GROUP BY student_id) AS cl', 'cl.student_id = u.user_id')
            ->leftJoin('(SELECT open_id, COUNT(*) AS complain_time FROM complain GROUP BY open_id) AS cp', 'cp.open_id = i.open_id ')
            ->leftJoin('user_account AS a', 'a.id = i.kefu_id')
            ->where('u.is_deleted = 0 AND i.purchase = 0 AND cl.latest_ex_time < :time '
                . (empty($keyword) ? "" : " AND (u.nick LIKE '%$keyword%' OR u.mobile LIKE '%$keyword%')"), [
                ':time' => $time])
            ->orderBy('i.time_operated ASC')
            ->offset(($num - 1) * 8)
            ->limit(8)
            ->asArray()
            ->all();
        return $list;
    }

    public function countAgainAllotNotPurchase($keyword)
    {
        $time = time() - 30*24*60*60; //time() - 4*60*60;//time() - 30*24*60*60;
        $obj = UserPublic::find()
            ->alias('u')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            ->leftJoin('(SELECT user_id,COUNT(user_id) AS ac_amount_count  FROM class_left WHERE type = 3 AND ac_amount > 0 GROUP BY user_id) AS cl', 'cl.user_id = u.user_id')
            ->leftJoin('(SELECT student_id, MAX(time_end) AS latest_actual_time FROM class_room WHERE is_ex_class = 0 AND status = 1 GROUP BY student_id) AS cr', 'cr.student_id = u.user_id')
            ->where('u.is_deleted = 0 AND i.purchase > 0 AND cr.latest_actual_time < :time AND cl.ac_amount_count IS NULL '
                . (empty($keyword) ? "" : " AND (u.nick LIKE '%$keyword%' OR u.mobile LIKE '%$keyword%')"), [
                    ':time' => $time,
                ]);

        if (!empty($start) && !empty($end)) {
            $obj = $obj->andWhere('u.time_created BETWEEN :start AND :end', [
                ':start' => $start,
                ':end' => $end]);
        }
        return $obj->count();
    }

    public function getAgainAllotNotPurchaseList($keyword, $num)
    {
        $time = time() - 30*24*60*60; //time() - 4*60*60;//time() - 30*24*60*60;
        $list = UserPublic::find()
            ->alias('u')
            ->select('u.user_id AS id, u.nick AS username, u.mobile ,i.open_id,cr.latest_actual_time,cp.complain_time,a.nickname AS newsign_kefu, a2.nickname AS re_kefu')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            ->leftJoin('(SELECT user_id,COUNT(user_id) AS ac_amount_count  FROM class_left WHERE type = 3 AND ac_amount > 0 GROUP BY user_id) AS cl', 'cl.user_id = u.user_id')
            ->leftJoin('(SELECT student_id, MAX(time_end) AS latest_actual_time FROM class_room WHERE is_ex_class = 0 AND status = 1 GROUP BY student_id) AS cr', 'cr.student_id = u.user_id')
            ->leftJoin('(SELECT open_id, COUNT(open_id) AS complain_time FROM complain GROUP BY open_id) AS cp', 'cp.open_id = i.open_id ')
            ->leftJoin('user_account AS a', 'a.id = i.kefu_id')
            ->leftJoin('user_account AS a2', 'a2.id = i.kefu_id_re')
            ->where('u.is_deleted = 0 AND i.purchase > 0 AND cr.latest_actual_time < :time AND cl.ac_amount_count IS NULL '
                . (empty($keyword) ? "" : " AND (u.nick LIKE '%$keyword%' OR u.mobile LIKE '%$keyword%')"), [
                    ':time' => $time,
                ])
            ->orderBy('i.time_operated ASC')
            ->offset(($num - 1) * 8)
            ->limit(8)
            ->asArray()
            ->all();

        return $list;
    }

    public function countAgainAllotNotFollow($keyword, $start, $end, $kefuId)
    {
        $time = time() - 24*60*60;

        $obj = UserPublic::find()
            ->alias('u')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            ->leftJoin('user_account AS a', 'a.id = i.kefu_id')
            ->leftJoin('(SELECT v.student_id,COUNT(v.student_id) AS visit_count 
            FROM visit_history AS v 
            LEFT JOIN user_public_info AS vi on vi.user_id = v.student_id 
            WHERE v.time_created BETWEEN vi.time_operated AND vi.time_operated + 24*60*60 AND vi.time_operated < :time
            GROUP BY student_id) AS vh', 'vh.student_id = u.user_id')
            ->where('u.is_deleted = 0 AND i.kefu_id > 0 AND i.kefu_id_re = 0 AND i.intention <> 1 AND i.time_operated BETWEEN :start AND :end AND i.time_operated < :time AND vh.visit_count IS NULL'
                . (empty($keyword) ? "" : " AND (u.nick LIKE '%$keyword%' OR u.mobile LIKE '%$keyword%')")
                .(empty($kefuId) ? "" : " AND i.kefu_id = $kefuId"), [
                    ':time' => $time,
                    ':start' => $start,
                    ':end' => $end
                ]);
        return $obj->count();
    }

    public function getAgainAllotNotFollowList($keyword, $start, $end, $num, $kefuId)
    {
        $time = time() - 24*60*60;//确定范围

        $list = UserPublic::find()
            ->alias('u')
            ->select('u.user_id,u.nick,u.mobile,i.open_id,i.time_operated,a.nickname AS kefu_nick,vv.first_visit_time,ar2.time_class AS ex_class_time,ar2.time_end AS ex_clastime_time_end')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            ->leftJoin('user_account AS a', 'a.id = i.kefu_id')
            ->leftJoin('(SELECT student_id,MIN(time_created) AS first_visit_time 
             FROM visit_history AS v 
             LEFT JOIN user_public_info AS vi on vi.user_id = v.student_id  
             WHERE v.time_created > vi.time_operated 
             GROUP BY student_id) AS vv', 'vv.student_id = u.user_id')
            //体验课 不包括删除 不包括取消
            ->leftJoin('(SELECT student_id,MAX(time_class) AS ex_class_time_1 FROM class_room WHERE is_ex_class = 1 AND is_deleted = 0 AND status <> 2 AND status <> 3 GROUP BY student_id) AS ar', 'ar.student_id = u.user_id')
            ->leftJoin('class_room  AS ar2', 'ar2.student_id = ar.student_id AND ar2.time_class = ar.ex_class_time_1 ')
            ->leftJoin('(SELECT v.student_id,COUNT(student_id) AS visit_count 
            FROM visit_history AS v 
            LEFT JOIN user_public_info AS vi on vi.user_id = v.student_id 
            WHERE v.time_created BETWEEN vi.time_operated AND vi.time_operated + 24*60*60 AND vi.time_operated < :time
            GROUP BY student_id) AS vh', 'vh.student_id = u.user_id')
            ->where('u.is_deleted = 0 AND i.kefu_id > 0 AND i.kefu_id_re = 0 AND i.intention <> 1 AND i.time_operated BETWEEN :start AND :end AND i.time_operated < :time AND vh.visit_count IS NULL'
                . (empty($keyword) ? "" : " AND (u.nick LIKE '%$keyword%' OR u.mobile LIKE '%$keyword%')")
                .(empty($kefuId) ? "" : " AND i.kefu_id = $kefuId"), [
                    ':time' => $time,
                    ':start' => $start,
                    ':end' => $end
                ])
            ->orderBy('u.time_created DESC')
            ->offset(($num - 1) * 8)
            ->limit(8)
            ->asArray()
            ->all();
        return $list;
    }

    public function countAgainAllotNotFollowExperienceClassBefore($keyword, $start, $end, $kefuId)
    {
        $time = time();//主管去看已经上完课的课程

        $obj = UserPublic::find()
            ->alias('u')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            //体验课前的24小时跟进  is null =>未跟进
            ->leftJoin('(SELECT student_id,COUNT(student_id) AS visit_count 
            FROM visit_history AS v 
            LEFT JOIN (SELECT student_id AS cl_student_id,MAX(time_class) AS ex_class_time FROM class_room WHERE is_ex_class = 1 AND is_deleted = 0 GROUP BY student_id) AS cm ON cm.cl_student_id = v.student_id
            WHERE v.time_created BETWEEN cm.ex_class_time - 24*60*60 AND cm.ex_class_time AND cm.ex_class_time < :time
            GROUP BY student_id) AS vh', 'vh.student_id = u.user_id')
            ->leftJoin('(SELECT student_id,MAX(time_class) AS ex_class_time FROM class_room WHERE is_ex_class = 1 AND is_deleted = 0  GROUP BY student_id) AS cr', 'cr.student_id = u.user_id')
            ->where('u.is_deleted = 0 AND i.kefu_id > 0 AND i.kefu_id_re = 0 AND i.intention <> 1 AND cr.ex_class_time BETWEEN :start AND :end  AND cr.ex_class_time < :time AND vh.visit_count IS NULL'
                . (empty($keyword) ? "" : " AND (u.nick LIKE '%$keyword%' OR u.mobile LIKE '%$keyword%')")
                .(empty($kefuId) ? "" : " AND i.kefu_id = $kefuId"), [
                    ':time' => $time,
                    ':start' => $start,
                    ':end' => $end
                ]);

        return $obj->count();
    }

    public function getAgainAllotNotFollowExperienceClassBeforeList($keyword, $start, $end, $num, $kefuId)
    {
        $time = time();//主管去看已经上完课的课程

        $list = UserPublic::find()
            ->alias('u')
            ->select('u.user_id,u.nick,u.mobile,i.open_id,a.nickname AS kefu_nick,vv.before_visit_time,cr2.status,cr2.time_class AS ex_class_time,cr2.time_end AS ex_clastime_time_end')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            ->leftJoin('user_account AS a', 'a.id = i.kefu_id')
            //体验课 不包括删除 包括取消
            ->leftJoin('(SELECT student_id,MAX(time_class) AS ex_class_time FROM class_room WHERE is_ex_class = 1 AND is_deleted = 0 GROUP BY student_id) AS cr', 'cr.student_id = u.user_id')
            ->leftJoin('class_room AS cr2', 'cr2.student_id = cr.student_id AND cr2.time_class = cr.ex_class_time')
            //体验课前的24小时跟进  is null =>未跟进
            ->leftJoin('(SELECT student_id,COUNT(student_id) AS visit_count 
            FROM visit_history AS v 
            LEFT JOIN (SELECT student_id AS cl_student_id,MAX(time_class) AS ex_class_time FROM class_room WHERE is_ex_class = 1 AND is_deleted = 0 GROUP BY student_id) AS cm ON cm.cl_student_id = v.student_id
            WHERE v.time_created BETWEEN cm.ex_class_time - 24*60*60 AND cm.ex_class_time AND cm.ex_class_time < :time
            GROUP BY student_id) AS vh', 'vh.student_id = u.user_id')
            //体验课前的跟进时间
            ->leftJoin('(SELECT student_id,MAX(time_created) AS before_visit_time 
            FROM visit_history AS v
            LEFT JOIN (SELECT student_id AS cl_student_id,MAX(time_class) AS ex_class_time FROM class_room WHERE is_ex_class = 1 AND is_deleted = 0 GROUP BY student_id) AS cm2 ON cm2.cl_student_id = v.student_id 
            WHERE v.time_created < cm2.ex_class_time
            GROUP BY student_id) AS vv', 'vv.student_id = u.user_id')
            ->where('u.is_deleted = 0 AND i.kefu_id > 0 AND i.kefu_id_re = 0 AND i.intention <> 1 AND cr.ex_class_time BETWEEN :start AND :end AND cr.ex_class_time < :time AND vh.visit_count IS NULL'
                . (empty($keyword) ? "" : " AND (u.nick LIKE '%$keyword%' OR u.mobile LIKE '%$keyword%')")
                .(empty($kefuId) ? "" : " AND i.kefu_id = $kefuId"), [
                    ':time' => $time,
                    ':start' => $start,
                    ':end' => $end
                ])
            ->orderBy('cr.ex_class_time DESC')
            ->offset(($num - 1) * 8)
            ->limit(8)
            ->asArray()
            ->all();
        return $list;
    }

    public function countAgainAllotNotFollowExperienceClassLater($keyword, $start, $end, $kefuId)
    {
        $time = time() - 12*60*60;

        $obj = UserPublic::find()
            ->alias('u')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            //体验课后的12小时跟进 is null =>未跟进
            ->leftJoin('(SELECT student_id,COUNT(student_id) AS visit_count 
            FROM visit_history AS v 
            LEFT JOIN (SELECT student_id AS cl_student_id,MAX(time_end) AS ex_class_time_end FROM class_room WHERE is_ex_class = 1 AND is_deleted = 0 AND status <> 2 AND status <> 3 GROUP BY student_id) AS cm ON cm.cl_student_id = v.student_id
            WHERE v.time_created BETWEEN cm.ex_class_time_end AND cm.ex_class_time_end + 12*60*60 AND cm.ex_class_time_end < :time
            GROUP BY student_id) AS vh', 'vh.student_id = u.user_id')
            ->leftJoin('(SELECT student_id,MAX(time_end) AS ex_class_time_end FROM class_room WHERE is_ex_class = 1 AND is_deleted = 0 AND status <> 2 AND status <> 3  GROUP BY student_id) AS cr', 'cr.student_id = u.user_id')

            ->where('u.is_deleted = 0 AND i.kefu_id > 0 AND i.kefu_id_re = 0 AND i.intention <> 1 AND cr.ex_class_time_end BETWEEN :start AND :end AND cr.ex_class_time_end < :time AND vh.visit_count IS NULL'
                . (empty($keyword) ? "" : " AND (u.nick LIKE '%$keyword%' OR u.mobile LIKE '%$keyword%')")
                .(empty($kefuId) ? "" : " AND i.kefu_id = $kefuId"), [
                    ':time' => $time,
                    ':start' => $start,
                    ':end' => $end
                ]);

        //var_dump($obj->count());
        return $obj->count();
    }

    public function getAgainAllotNotFollowExperienceClassLaterList($keyword, $start, $end, $num, $kefuId)
    {
        $time = time() - 12*60*60;

        $list = UserPublic::find()
            ->distinct()
            ->alias('u')
            ->select('u.user_id,u.nick,u.mobile,i.open_id,a.nickname AS kefu_nick,vv.later_visit_time,cr2.status,cr2.time_class AS ex_class_time,cr2.time_end AS ex_clastime_time_end')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.user_id')
            ->leftJoin('user_account AS a', 'a.id = i.kefu_id')
            //体验课 不包括删除 不包括取消
            ->leftJoin('(SELECT student_id,MAX(time_end) AS ex_class_time_end FROM class_room WHERE is_ex_class = 1 AND is_deleted = 0 AND status <> 2 AND status <> 3 GROUP BY student_id) AS cr', 'cr.student_id = u.user_id')
            ->leftJoin('class_room AS cr2', 'cr2.student_id = cr.student_id AND cr2.time_end = cr.ex_class_time_end')
            //体验课后的3小时跟进 is null =>未跟进
            ->leftJoin('(SELECT student_id,COUNT(student_id) AS visit_count 
            FROM visit_history AS v 
            LEFT JOIN (SELECT student_id AS cl_student_id,MAX(time_end) AS ex_class_time_end FROM class_room WHERE is_ex_class = 1 AND is_deleted = 0 AND status <> 2 AND status <> 3 GROUP BY student_id) AS cm ON cm.cl_student_id = v.student_id
            WHERE v.time_created BETWEEN cm.ex_class_time_end AND cm.ex_class_time_end + 12*60*60 AND cm.ex_class_time_end < :time
            GROUP BY student_id) AS vh', 'vh.student_id = u.user_id')
            //体验课后的跟进时间
            ->leftJoin('(SELECT student_id,MIN(time_created) AS later_visit_time 
            FROM visit_history AS v
            LEFT JOIN (SELECT student_id AS cl_student_id,MAX(time_end) AS ex_class_time_end FROM class_room WHERE is_ex_class = 1 AND is_deleted = 0 AND status <> 2 AND status <> 3 GROUP BY student_id) AS cm2 ON cm2.cl_student_id = v.student_id 
            WHERE v.time_created > cm2.ex_class_time_end
            GROUP BY student_id) AS vv', 'vv.student_id = u.user_id')
            ->where('u.is_deleted = 0 AND i.kefu_id > 0 AND i.kefu_id_re = 0 AND i.intention <> 1  AND cr.ex_class_time_end BETWEEN :start AND :end  AND cr.ex_class_time_end < :time AND vh.visit_count IS NULL'
                . (empty($keyword) ? "" : " AND (u.nick LIKE '%$keyword%' OR u.mobile LIKE '%$keyword%')")
                .(empty($kefuId) ? "" : " AND i.kefu_id = $kefuId"), [
                    ':time' => $time,
                    ':start' => $start,
                    ':end' => $end
                ])
            ->orderBy('cr.ex_class_time_end DESC')
            ->offset(($num - 1) * 8)
            ->limit(8)
            ->asArray()
            ->all();
        return $list;
    }

    public function countPublicUserPage($type, $kefuId, $area, $keyword, $intention, $timeType, $start, $end)
    {
        list($bodySql, $bodyParams) = $this->getPubilcUserSql($type, $kefuId, $area, $keyword, $intention, $timeType, $start, $end);

        $obj = User::find()
            ->alias('u')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.id');

        if ($intention == 6) {
            $obj = $obj
                ->leftJoin('(SELECT student_id FROM class_room WHERE (status = 0 OR status = 1) AND is_deleted = 0 GROUP BY student_id) AS r', 'r.student_id = u.id');
        }

        if ($timeType == 1 || ($timeType == 2 && empty($start))) {
             $count = $obj
                ->where($bodySql, $bodyParams)
                ->count();
        } else {
             $count = ClassRoom::find()
                ->alias('c')
                ->leftJoin('user AS u', 'u.id = c.student_id')
                ->leftJoin('user_public_info AS i', 'i.user_id = u.id')
                ->where($bodySql, $bodyParams)
                ->andWhere('c.is_ex_class = 1 AND c.status != 2 AND c.status != 3 AND c.is_deleted = 0')
                ->count();
        }
        return $count;
    }

    public function getPublicUserList($type, $kefuId, $area, $keyword, $intention, $timeType, $start, $end, $num)
    {
        list($bodySql, $bodyParams) = $this->getPubilcUserSql($type, $kefuId, $area, $keyword, $intention, $timeType, $start, $end);

        $obj = User::find()
            ->alias('u')
            ->select('a1.nickname AS kefu_new,a2.nickname AS kefu_purchase,u.id as user_id,u.age, u.nick, u.mobile, u.time_created, u.sales_id, u.channel_id, uc.type, i.birth, i.level, i.city, i.kefu_id, i.kefu_id_tmp, i.call_count, i.intention, i.purchase, i.area,i.kefu_id_re')
            ->leftJoin('user_public_info AS i', 'i.user_id = u.id')
            ->leftJoin('user_channel AS uc', 'uc.id = u.channel_id')
            ->leftJoin('user_account AS a1', 'a1.id = i.kefu_id')
            ->leftJoin('user_account AS a2', 'a2.id = i.kefu_id_re');


        if ($intention == 6) {
            $obj = $obj
                ->leftJoin('(SELECT student_id FROM class_room WHERE (status = 0 OR status = 1) AND is_deleted = 0 GROUP BY student_id) AS r', 'r.student_id = u.id');
        }

        if ($timeType == 1 || ($timeType == 2 && empty($start))) {
            $list = $obj
                ->where($bodySql, $bodyParams)
                ->orderBy('u.time_created DESC')
                ->offset(($num - 1) * 8)
                ->limit(8)
                ->asArray()
                ->all();
        } else {
            $list = ClassRoom::find()
                ->alias('c')
                ->select('a1.nickname AS kefu_new,a2.nickname AS kefu_purchase,u.id as user_id, u.nick, u.mobile, u.time_created, i.birth, i.level, i.city, i.kefu_id, i.kefu_id_tmp, i.call_count, i.intention, i.purchase, i.area, c.student_id, ut.nick as teacher_name, c.time_class, c.time_end,i.kefu_id_re, uc.type, u.channel_id, u.sales_id')
                ->leftJoin('user AS u', 'u.id = c.student_id')
                ->leftJoin('user_public_info AS i', 'i.user_id = u.id')
                ->leftJoin('user_channel AS uc', 'uc.id = u.channel_id')
                ->leftJoin('user_teacher AS ut', 'ut.id = c.teacher_id')
                ->leftJoin('user_account AS a1', 'a1.id = i.kefu_id')
                ->leftJoin('user_account AS a2', 'a2.id = i.kefu_id_re')
                ->where($bodySql, $bodyParams)
                ->andWhere('c.is_ex_class = 1 AND c.status != 2 AND c.status != 3 AND c.is_deleted = 0')
                ->orderBy('c.time_class ASC')
                ->offset(($num - 1) * 8)
                ->limit(8)
                ->asArray()
                ->all();
        }
        return $list;
    }

    private function getPubilcUserSql($type, $kefuId, $area, $keyword, $intention, $timeType, $start, $end)
    {
        $bodySql = 'u.is_disabled = 0';
        $bodyParams = [];

        if ($type == 1 && Yii::$app->user->identity->role == 1) {
            $type = 2;
        }

        if ($type == 1) {
            $bodySql .= ' AND i.intention = 0';
        } elseif ($type == 2) {
            $bodySql .= ' AND i.intention in (1,2,3)';
        } elseif ($type == 3) {
            $bodySql .= ' AND i.intention = 4';
        }

        if ($intention == 1) {
            $bodySql .= ' AND i.intention = 1';
        } elseif ($intention == 2) {
            $bodySql .= ' AND i.intention = 2';
        } elseif ($intention == 3) {
            $bodySql .= ' AND i.intention = 3';
        } elseif ($intention == 4) {
            $bodySql .= ' AND i.purchase > 0';
        } elseif ($intention == 5) {
            $bodySql .= ' AND i.purchase = 0';
        } elseif ($intention == 6) {
            $bodySql .= ' AND r.student_id IS NULL';
        }

        if ($kefuId == -1) {
            $bodySql .= ' AND i.kefu_id = 0';
        } elseif ($kefuId == -2) {
            $bodySql .= ' AND i.kefu_id > 0';
        } elseif ($kefuId > 0) {
            //新签客服名称
            $role=  UserAccount::find()
                ->select('role')
                ->where('id = :id', [
                    ':id' => $kefuId
                ])
                ->scalar();

            if ($role == 4) {
                $bodySql .= ' AND i.kefu_id_re = :kefu_id ';
            } else {
                $bodySql .= ' AND i.kefu_id = :kefu_id ';
            }

            $bodyParams[':kefu_id'] = $kefuId;
        }

        if ($area > 0) {
            $bodySql .= ' AND i.area = :area';
            $bodyParams[':area'] = $area;
        } elseif ($area == -1) {
            $bodySql .= ' AND i.area = 0';
        }

        if (!empty($keyword)) {
            $bodySql .= " AND u.nick LIKE '%$keyword%'";
        }

        if ($timeType == 1) {
            if (!empty($start)) {
                $bodySql .= " AND u.time_created > :start AND u.time_created < :end";
                $bodyParams[':start'] = $start;
                $bodyParams[':end'] = $end;
            }
        } else {
            if (!empty($start)) {
                $bodySql .= " AND c.time_class > :start AND c.time_end < :end";
                $bodyParams[':start'] = $start;
                $bodyParams[':end'] = $end;
            }
        }
        return [$bodySql, $bodyParams];
    }

    public function countUserPublicInfoKefutmp($kefuId)
    {
        return UserPublicInfo::find()
            ->where(['kefu_id_tmp' => $kefuId])
            ->count();
    }

    public function getUserAccountByKeyword($keyword)
    {
        return UserAccount::find()
            ->select('id, nickname')
            ->where("(role = 1 OR role =4) AND status = 1 ". (empty($keyword) ? '' : " AND nickname LIKE '%$keyword%'"))
            ->asArray()
            ->all();
    }


    public function getKefuWithPower()
    {
        return  UserAccount::find()
                    ->select('id, nickname')
                    ->where('status = 1 AND (role = 1 OR role = 4)')
                    ->asArray()
                    ->all();
    }

    public function getKefuInfo($kefuId)
    {
        return UserAccount::find()
                        ->where(['id' => $kefuId])
                        ->asArray()
                        ->one();
    }

    public function getStundentInfoByClassId($classId)
    {
        return  User::find()
                    ->alias('u')
                    ->select('u.mobile,u.nick,uin.head,uin.name as wename')
                    ->leftJoin('class_room as c', 'c.student_id = u.id')
                    ->leftJoin('wechat_acc as we', 'we.uid = u.id')
                    ->leftJoin('user_init as uin', 'uin.openid = we.openid')
                    ->where('c.id = :class_id', [':class_id'=>$classId])
                    ->asArray()
                    ->one();
    }


    public function getApplysCount($isCalled, $search)
    {
        $query = UserPre::find()
            ->select('user_pre.*,user_channel.name as channelName')
            ->leftJoin('user_channel', '`user_pre`.`channel_id` =`user_channel`.`id`')
            ->where('user_pre.is_called=:is_called', [':is_called' => $isCalled])
            ->andWhere('user_pre.is_deleted=0');

        if (count($search)>0) {
            $query = $query->andWhere(("user_pre.name LIKE '%".$search."%' or user_pre.mobile LIKE '%".$search."%' or user_channel.name LIKE '%".$search."%' "));
        }
        return   $query->count();
    }

    public function getApplysList($isCalled, $search, $page)
    {
        $query = UserPre::find()
            ->select('user_pre.*,user_channel.name as channelName')
            ->leftJoin('user_channel', '`user_pre`.`channel_id` =`user_channel`.`id`')
            ->where('user_pre.is_called=:is_called', [':is_called' => $isCalled])
            ->andWhere('user_pre.is_deleted=0');

        if (count($search)>0) {
            $query = $query->andWhere(("user_pre.name LIKE '%".$search."%' or user_pre.mobile LIKE '%".$search."%' or user_channel.name LIKE '%".$search."%' "));
        }

        return  $query->orderBy('id desc')
                        ->offset(($page-1)*8)
                        ->limit(8)
                        ->asArray()
                        ->all();
    }

    public function getUserPreInfo($applyId)
    {
        return UserPre::findOne($applyId);
    }

    public function getAppsCount($status, $search)
    {
        $query = UserEventWeixin::find()
                    ->where(["status"=>$status, "openid"=>"app"]);

        if (count($search)>0) {
            $query = $query->andWhere("username like '%" . $search . "%' or cellphone like '%" . $search . "%'");
        }

        return  $query->count();
    }

    public function getAppsList($status, $search, $page)
    {
        $query = UserEventWeixin::find()->where(["status"=>$status, "openid"=>"app"]);
        if (count($search)>0) {
            $query = $query->andWhere("username like '%" . $search . "%' or cellphone like '%" . $search . "%'");
        }

        return $query->orderBy('id desc')
                    ->offset(($page-1)*8)
                    ->limit(8)
                    ->asArray()
                    ->all();
    }

    public function getUserEventWeixinById($id)
    {
        return  UserEventWeixin::findOne($id);
    }

    public function getLikeCrmKefu()
    {
        return  UserAccount::find()
            ->select('id,nickname')
            ->where("type like '%crm%'")
            ->asArray()
            ->all();
    }

    public function getLikeCrmKefuByName($name)
    {
        return UserAccount::find()
            ->select('id,nickname')
            ->andWhere("nickname like '%".$name."%'")
            ->andWhere("type like '%crm%'")
            ->asArray()
            ->all();
    }

    public function getHighUserCount($uid)
    {
        return User::find()
            ->where(['is_high' => 1, 'id' => $uid])
            ->count();
    }

    public function getWeChatAccInfo($openId)
    {
        return WechatAcc::find()
            ->select('u.nick, wechat_acc.uid, c.name as channel_name')
            ->leftJoin('user AS u', 'u.id = wechat_acc.uid')
            ->leftJoin('user_channel AS c', 'c.id = u.channel_id')
            ->where(['openid' => $openId])
            ->asArray()
            ->one();
    }

    public function getUserAccountById($id)
    {
        return  UserAccount::find()
                    ->select('id, nickname')
                    ->where(['id' => $id])
                    ->asArray()
                    ->one();
    }

    public function getUserInitByOpenId($openId)
    {
        return  UserInit::find()
                        ->select('id,name')
                        ->where(['openid' => $openId])
                        ->asArray()
                        ->one();
    }

    public function getAllUserInitCount()
    {
        return UserInit::find()->count();
    }

    public function getWechatUserCount($keyword, $type)
    {
        if ($type == 0) {
            $sql = "SELECT COUNT(id) FROM user_init"
                . (empty($keyword) ? "" : " WHERE name LIKE '%$keyword%'");
        } elseif ($type == 1) {
            $sql = "SELECT COUNT(ui.id) FROM user_init AS ui"
                . " LEFT JOIN wechat_acc AS w ON w.openid = ui.openid"
                . " LEFT JOIN (SELECT student_id, COUNT(id) FROM class_edit_history WHERE is_success = 1 AND is_deleted = 0 GROUP BY student_id) AS c ON c.student_id = w.uid"
                . " WHERE c.student_id IS NULL"
                . (empty($keyword) ? "" : " AND ui.name LIKE '%$keyword%'");
        } elseif ($type == 2) {
            $sql = "SELECT COUNT(ui.id) FROM user_init AS ui"
                . " LEFT JOIN wechat_acc AS w ON w.openid = ui.openid"
                . " LEFT JOIN (SELECT student_id, COUNT(id) as counts FROM class_room WHERE is_ex_class = 1 AND is_deleted = 0 AND status = 1 GROUP BY student_id) AS c ON c.student_id = w.uid"
                . " LEFT JOIN (SELECT student_id, COUNT(id) as counts FROM class_edit_history WHERE price > 0 AND is_success = 1 AND is_deleted = 0 AND is_add = 1 GROUP BY student_id) AS ce ON ce.student_id = w.uid"
                . " WHERE c.counts > 0 AND ce.counts IS NULL"
                . (empty($keyword) ? "" : " AND ui.name LIKE '%$keyword%'");
        } elseif ($type == 3) {
            $sql = "SELECT COUNT(ui.id) FROM user_init AS ui"
                . " LEFT JOIN wechat_acc AS w ON w.openid = ui.openid"
                . " LEFT JOIN (SELECT student_id, COUNT(id) as counts FROM class_edit_history WHERE price > 0 AND is_success = 1 AND is_deleted = 0 AND is_add = 1 GROUP BY student_id) AS ce ON ce.student_id = w.uid"
                . " WHERE ce.counts > 0"
                . (empty($keyword) ? "" : " AND ui.name LIKE '%$keyword%'");
        } elseif ($type == 4) {
            $sql = "SELECT COUNT(ui.id) FROM user_init AS ui"
                . " LEFT JOIN wechat_acc AS w ON w.openid = ui.openid"
                . " LEFT JOIN user AS u ON u.id = w.uid"
                . " WHERE u.is_high = 1"
                . (empty($keyword) ? "" : " AND ui.name LIKE '%$keyword%'");
        }
        return Yii::$app->db->createCommand($sql)->queryScalar();
    }

    public function getWechatUserList($request)
    {
        if ($request['type'] == 0) {
            $sql = "SELECT openid, name, head, subscribe_time, province FROM user_init"
                . (empty($request['keyword']) ? "" : " WHERE name LIKE '%{$request['keyword']}%'")
                . " ORDER BY subscribe_time DESC"
                . " LIMIT :offset, :limit";
        } elseif ($request['type'] == 1) {
            $sql = "SELECT ui.openid, ui.name, ui.head, ui.subscribe_time, ui.province FROM user_init AS ui"
                . " LEFT JOIN wechat_acc AS w ON w.openid = ui.openid"
                . " LEFT JOIN (SELECT student_id, COUNT(id) FROM class_edit_history WHERE is_success = 1 AND is_deleted = 0 GROUP BY student_id) AS c ON c.student_id = w.uid"
                . " WHERE c.student_id IS NULL"
                . (empty($request['keyword']) ? "" : " AND ui.name LIKE '%{$request['keyword']}%'")
                . " ORDER BY ui.subscribe_time DESC"
                . " LIMIT :offset, :limit";
        } elseif ($request['type'] == 2) {
            $sql = "SELECT ui.openid, ui.name, ui.head, ui.subscribe_time, ui.province FROM user_init AS ui"
                . " LEFT JOIN wechat_acc AS w ON w.openid = ui.openid"
                . " LEFT JOIN (SELECT student_id, COUNT(id) as counts FROM class_room WHERE is_ex_class = 1 AND is_deleted = 0 AND status = 1 GROUP BY student_id) AS c ON c.student_id = w.uid"
                . " LEFT JOIN (SELECT student_id, COUNT(id) as counts FROM class_edit_history WHERE price > 0 AND is_success = 1 AND is_deleted = 0 AND is_add = 1 GROUP BY student_id) AS ce ON ce.student_id = w.uid"
                . " WHERE c.counts > 0 AND ce.counts IS NULL"
                . (empty($request['keyword']) ? "" : " AND ui.name LIKE '%{$request['keyword']}%'")
                . " ORDER BY ui.subscribe_time DESC"
                . " LIMIT :offset, :limit";
        } elseif ($request['type'] == 3) {
            $sql = "SELECT ui.openid, ui.name, ui.head, ui.subscribe_time, ui.province FROM user_init AS ui"
                . " LEFT JOIN wechat_acc AS w ON w.openid = ui.openid"
                . " LEFT JOIN (SELECT student_id, COUNT(id) as counts FROM class_edit_history WHERE price > 0 AND is_success = 1 AND is_deleted = 0 AND is_add = 1 GROUP BY student_id) AS ce ON ce.student_id = w.uid"
                . " WHERE ce.counts > 0"
                . (empty($request['keyword']) ? "" : " AND ui.name LIKE '%{$request['keyword']}%'")
                . " ORDER BY ui.subscribe_time DESC"
                . " LIMIT :offset, :limit";
        } elseif ($request['type'] == 4) {
            $sql = "SELECT ui.openid, ui.name, ui.head, ui.subscribe_time, ui.province FROM user_init AS ui"
                . " LEFT JOIN wechat_acc AS w ON w.openid = ui.openid"
                . " LEFT JOIN user AS u ON u.id = w.uid"
                . " WHERE u.is_high = 1"
                . (empty($request['keyword']) ? "" : " AND ui.name LIKE '%{$request['keyword']}%'")
                . " ORDER BY ui.subscribe_time DESC"
                . " LIMIT :offset, :limit";
        }

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':offset' => ($request['page_num'] - 1)*8, ':limit' => 8])
            ->queryAll();
    }

    public function getStudentFixTimeInfo($studentId)
    {
        return  StudentFixTime::find()
                    ->alias('s')
                    ->select('s.id, s.week, s.time, s.class_type, s.teacher_id, s.instrument_id, t.nick as teacher_name, t.gender')
                    ->leftJoin('user_teacher as t', 't.id = s.teacher_id')
                    ->where(['s.student_id' => $studentId])
                    ->asArray()
                    ->all();
    }

    public function getInitAndWechatByOpenId($openid)
    {
        return UserInit::find()
            ->alias('w')
            ->select('w.name,w.head,w.subscribe_time,user.*')
            ->leftJoin('wechat_acc as a', 'w.openid = a.openid')
            ->leftJoin('user', 'user.id = a.uid')
            ->where('w.openid=:openid', [':openid' => $openid])
            ->asArray()
            ->one();
    }

    public function getWechatChannelId($openid)
    {
        return WechatAcc::find()
                    ->alias('w')
                    ->select('u.channel_id_self')
                    ->innerJoin('user as u', 'u.id=w.uid')
                    ->where('u.is_disabled=0 and w.openid=:openid', [':openid'=>$openid])
                    ->scalar();
    }

    public function getNoZeroUserAccount()
    {
        return UserAccount::find()
                            ->select('id,nickname')
                            ->where('role > 0')
                            ->asArray()
                            ->all();
    }

    public function getRemainClass()
    {
        $sql = "SELECT u.nick, u.mobile, IFNULL(uc.name,'') AS channel_name, IFNULL(wu.name,'') as wechat_name, IFNULL(le.left_times,0) AS left_times, FROM_UNIXTIME(max(time_class),'%Y-%m-%d %H:%i:%S') AS time_class, u.kefu_id FROM user AS u"
            . " LEFT JOIN (SELECT w.uid, ui.openid, ui.name, ui.head FROM wechat_acc AS w LEFT JOIN user_init AS ui ON ui.openid = w.openid) AS wu ON wu.uid = u.id"
            . " LEFT JOIN user_channel AS uc ON uc.id = u.channel_id"
            . " LEFT JOIN (SELECT SUM(ac_amount) AS left_times, user_id FROM `class_left` WHERE type IN(2,3) AND (left_bit & 4) = 0 GROUP BY user_id) AS le ON le.user_id = u.id"
            . " LEFT JOIN class_edit_history AS h ON h.student_id = u.id"
            . " LEFT JOIN class_room AS cl ON u.id = cl.student_id"
            . " WHERE u.is_disabled = 0 AND h.is_add = 1 AND h.price > 0 AND h.is_success = 1 AND le.left_times <= 5 AND u.is_refund = 0 AND cl.is_deleted = 0 AND cl.status = 1"
            . " GROUP BY u.id";

        return Yii::$app->db->createCommand($sql)->queryAll();
    }

    public function getUserInstrumentClassTime($classInfo)
    {
        return UserInstrument::find()
                                    ->select('user_id,class_times')
                                    ->where('user_id = :student_id', [':student_id'=>$classInfo['student_id']])
                                    ->andWhere('instrument_id = :instrument_id', [':instrument_id'=>$classInfo['instrument_id']])
                                    ->andWhere('type = :type', [':type'=>$classInfo['type']])
                                    ->asArray()
                                    ->one();
    }

    public function getUserOpenId($studentId)
    {
        $sql = "SELECT openid FROM wechat_acc WHERE uid = :uid";
        return Yii::$app->db->createCommand($sql)->bindValue(':uid', $studentId)->queryScalar();
    }

    public function isNullPublicUserKefu($userId)
    {
        return UserPublicInfo::find()
            ->where('usre_id = :userid', [
                ':userid' =>$userId,
            ]);
    }


    public function getUserPublicByUserId($studentId)
    {
        return UserPublicInfo::find()
            ->alias('u')
            ->select('u.user_id as id , u.kefu_id, u.kefu_id_re ,k.nickname')
            ->leftJoin('user_account AS k', 'u.kefu_id = k.id')
            ->leftJoin('user AS us', 'us.id = u.user_id')
            ->where(['us.channel_id_self' => $studentId])
            ->asArray()
            ->one();
    }

    public function getAllUsersByKefuId($uid, $keyword, $offset, $limit)
    {
        return UserPublicInfo::find()
            ->alias('p')
            ->select('u.nick, u.mobile, w.name as wechat_name, u.sales_id, u.channel_id, w.head, w.subscribe_time, w.province, p.open_id')
            ->leftJoin('user AS u', 'u.id = p.user_id')
            ->leftJoin('user_init AS w', 'w.openid = p.open_id')
            ->where('p.kefu_id = :kefu_id AND u.is_disabled = 0' . (empty($keyword) ? '' : ' AND (w.name LIKE "%'. $keyword .'%" OR u.nick LIKE "%'. $keyword .'%" OR u.mobile LIKE "%'. $keyword .'%")'), [':kefu_id' => $uid])
            ->orderBy('w.subscribe_time DESC')
            ->offset($offset)
            ->limit($limit)
            ->asArray()
            ->all();
    }

    public function getAllUsersByKefuIdRe($uid, $keyword, $offset, $limit)
    {
        return UserPublicInfo::find()
            ->alias('p')
            ->select('u.nick, u.mobile, w.name as wechat_name, u.sales_id, u.channel_id, w.head, w.subscribe_time, w.province, p.open_id')
            ->leftJoin('user AS u', 'u.id = p.user_id')
            ->leftJoin('user_init AS w', 'w.openid = p.open_id')
            ->where('p.kefu_id = :kefu_id AND u.is_disabled = 0' . (empty($keyword) ? '' : ' AND (u.nick LIKE "%' . $keyword . '%" OR w.name LIKE "%' . $keyword . '%" OR u.mobile LIKE "%' . $keyword . '%")'), [':kefu_id_re' => $uid])
            ->orderBy('w.subscribe_time DESC')
            ->offset($offset)
            ->limit($limit)
            ->asArray()
            ->all();
    }

    public function getNickByOpenId($openId)
    {
        return UserPublicInfo::find()
            ->alias('i')
            ->select('nick')
            ->leftJoin('user_public AS p', 'p.user_id = i.user_id')
            ->where('open_id = :open_id ', [':open_id' => $openId])
            ->scalar();
    }



    public function getOpenIdByStudentId($id)
    {
        return UserPublicInfo::find()
            ->select('open_id')
            ->where('user_id = :id ', [':id' => $id])
            ->scalar();
    }

    public function getUseridByTelephone($telephone)
    {
        return User::find()
            ->select('id')
            ->where('mobile = :mobile', [
                ':mobile' =>$telephone
            ])
            ->one();
    }

    /**
     * @return mixed
     * create by sjy
     * 查询用户的体验课信息
     */
    public function selectClassleftIsex($sid)
    {
        $sql="select ac_amount,id,instrument_id from class_left where user_id = :user_id and type = 1 ";
        return Yii::$app->db->createCommand($sql)
                ->bindValue(':user_id', $sid)
                ->queryOne();
    }
    
    /**
     * @return mixed
     * create by sjy
     * 查询未上课的体验课信息
     */
    public function selectIsexClassinfo($sid, $classleftid)
    {
        $sql="select status,id,history_id from class_room where student_id = :student_id and is_ex_class = 1 and left_id = :left_id";
        return Yii::$app->db->createCommand($sql)
            ->bindValues([':student_id' => $sid, ':left_id' => $classleftid])
            ->queryOne();
    }

    public function getReferralChannel($id)
    {
        return UserChannel::find()
                        ->alias('u')
                        ->select('id')
                        ->where('id = :id AND type = 2', ['id' => $id])
                        ->scalar();
    }

    public function getUnionIdByOpenId($openId)
    {
        return UserInit::find()
            ->select('union_id')
            ->where(['openid' => $openId])
            ->scalar();
    }


    public function getChannelIdByWechatClassId($sshareId)
    {
        return StudentUserShare::find()
            ->alias('s')
            ->select('user.channel_id_self, s.class_id')
            ->leftJoin('wechat_acc AS wa', 'wa.openid = s.open_id')
            ->leftJoin('user', 'user.id = wa.uid')
            ->where('s.id = :id', [
                ':id' => $sshareId
            ])
            ->asArray()
            ->one();
    }

    public function getPurchaseUserAgainAllotNotFollowPage($data)
    {
        if ($data['type'] == 1) {
            return UserPublicInfo::find()
                        ->alias('u')
                        ->leftJoin('user_public AS p', 'p.user_id = u.user_id')
                        ->leftJoin('(SELECT student_id, user_id_visit, COUNT(id) AS is_again FROM visit_history GROUP BY student_id, user_id_visit) AS v', 'v.student_id = u.user_id AND v.user_id_visit = u.kefu_id_re')
                        ->where('v.is_again IS NULL AND u.time_operated_re >= :start AND u.time_operated_re <= :end'
                            . (empty($data['saleId']) ? ' AND u.kefu_id_re <> :saleId' : ' AND u.kefu_id_re = :saleId')
                            . (empty($data['studentName']) ? ' ' : " AND (p.nick LIKE '%{$data['studentName']}%' OR p.mobile LIKE '%{$data['studentName']}%')"), [
                                    ':start' => $data['start'],
                                    ':end' => $data['end'],
                                    ':saleId' => $data['saleId']
                                ])
                        ->count();
        } elseif ($data['type'] == 2) {
            return UserPublicInfo::find()
                ->alias('u')
                ->leftJoin('user_public AS p', 'p.user_id = u.user_id')
                ->leftJoin('(SELECT v.student_id, user_id_visit, COUNT(id) AS is_again FROM visit_history v LEFT JOIN 
                (SELECT student_id,IFNULL(MIN(time_class), 0) AS class_time FROM class_room AS c
                LEFT JOIN (SELECT uid, IFNULL(MAX(time_pay), 0) AS pay_time FROM product_order WHERE pay_status = 1  GROUP BY uid) 
                AS o ON o.uid = c.student_id WHERE time_class > pay_time GROUP BY student_id) AS c On c.student_id = v.student_id
                WHERE  time_visit > c.class_time AND class_time <> 0 GROUP BY student_id) AS v', 'v.student_id = u.user_id AND v.user_id_visit = u.kefu_id_re')
                ->leftJoin('(SELECT student_id,IFNULL(MIN(time_class), 0) AS class_time FROM class_room AS c 
			LEFT JOIN (SELECT uid, IFNULL(MAX(time_pay), 0) AS pay_time FROM product_order WHERE pay_status = 1 GROUP BY uid) AS o ON o.uid = c.student_id WHERE time_class > pay_time GROUP BY student_id) AS t', 't.student_id = u.user_id')
                ->where('v.is_again IS NULL AND u.time_operated_re >= :start AND u.time_operated_re <= :end AND t.class_time <> 0 '
                    . (empty($data['saleId']) ? ' AND u.kefu_id_re <> :saleId' : ' AND u.kefu_id_re = :saleId')
                    . (empty($data['studentName']) ? ' ' : " AND (p.nick LIKE '%{$data['studentName']}%' OR p.mobile LIKE '%{$data['studentName']}%')"), [
                        ':start' => $data['start'],
                        ':end' => $data['end'],
                        ':saleId' => $data['saleId']
                    ])
                ->count();
        } else {
            $time = strtotime('-1 month');

            return UserPublicInfo::find()
                ->alias('u')
                ->leftJoin('user_public AS p', 'p.user_id = u.user_id')
                ->leftJoin('(SELECT student_id, user_id_visit, MAX(time_created) AS max_visit FROM visit_history  GROUP BY student_id, user_id_visit) AS v', 'v.student_id = u.user_id AND v.user_id_visit = u.kefu_id_re')
                ->where('v.max_visit < :time AND u.time_operated_re >= :start AND u.time_operated_re <= :end'
                    . (empty($data['saleId']) ? ' AND u.kefu_id_re <> :saleId' : ' AND u.kefu_id_re = :saleId')
                    . (empty($data['studentName']) ? ' ' : " AND (p.nick LIKE '%{$data['studentName']}%' OR p.mobile LIKE '%{$data['studentName']}%')"), [
                        ':start' => $data['start'],
                        ':end' => $data['end'],
                        ':saleId' => $data['saleId'],
                        ':time' => $time
                    ])
                ->count();
        }
    }

    public function getPurchaseUserAgainAllotNotFollowList($data)
    {
        if ($data['type'] == 1) {
            return UserPublicInfo::find()
                ->alias('u')
                ->select('p.nick, p.mobile, u.open_id, u.time_operated_re, u.kefu_id_re, u.user_id')
                ->leftJoin('user_public AS p', 'p.user_id = u.user_id')
                ->leftJoin('(SELECT student_id, user_id_visit, COUNT(id) AS is_again FROM visit_history GROUP BY student_id, user_id_visit) AS v', 'v.student_id = u.user_id AND v.user_id_visit = u.kefu_id_re')
                ->where('v.is_again IS NULL AND u.time_operated_re >= :start AND u.time_operated_re <= :end'
                    . (empty($data['saleId']) ? ' AND u.kefu_id_re <> :saleId' : ' AND u.kefu_id_re = :saleId')
                    . (empty($data['studentName']) ? ' ' : " AND (p.nick LIKE '%{$data['studentName']}%' OR p.mobile LIKE '%{$data['studentName']}%')"), [
                        ':start' => $data['start'],
                        ':end' => $data['end'],
                        ':saleId' => $data['saleId']
                    ])
                ->offset(($data['num'] - 1) * 8)
                ->limit(8)
                ->asArray()
                ->all();
        } elseif ($data['type'] == 2) {
            return UserPublicInfo::find()
                ->alias('u')
                ->select('p.nick, p.mobile, u.open_id, u.time_operated_re, u.kefu_id_re, u.user_id')
                ->leftJoin('user_public AS p', 'p.user_id = u.user_id')
                ->leftJoin('(SELECT v.student_id, user_id_visit, COUNT(id) AS is_again FROM visit_history v LEFT JOIN 
                (SELECT student_id,IFNULL(MIN(time_class), 0) AS class_time FROM class_room AS c
                LEFT JOIN (SELECT uid, IFNULL(MAX(time_pay), 0) AS pay_time FROM product_order WHERE pay_status = 1 GROUP BY uid) 
                AS o ON o.uid = c.student_id WHERE time_class > pay_time GROUP BY student_id) AS c ON c.student_id = v.student_id
                WHERE v.time_created > c.class_time AND class_time <> 0 GROUP BY student_id) AS v', 'v.student_id = u.user_id AND v.user_id_visit = u.kefu_id_re')
                ->leftJoin('(SELECT student_id,IFNULL(MIN(time_class), 0) AS class_time FROM class_room AS c 
			LEFT JOIN (SELECT uid, IFNULL(MAX(time_pay), 0) AS pay_time FROM product_order WHERE pay_status = 1 GROUP BY uid) AS o ON o.uid = c.student_id WHERE time_class > pay_time GROUP BY student_id) AS t', 't.student_id = u.user_id')
                ->where('v.is_again IS NULL AND u.time_operated_re >= :start AND u.time_operated_re <= :end AND t.class_time <> 0 '
                    . (empty($data['saleId']) ? ' AND u.kefu_id_re <> :saleId' : ' AND u.kefu_id_re = :saleId')
                    . (empty($data['studentName']) ? ' ' : " AND (p.nick LIKE '%{$data['studentName']}%' OR p.mobile LIKE '%{$data['studentName']}%')"), [
                        ':start' => $data['start'],
                        ':end' => $data['end'],
                        ':saleId' => $data['saleId']
                    ])
                ->offset(($data['num'] - 1) * 8)
                ->limit(8)
                ->asArray()
                ->all();
        } else {
            $time = strtotime('-1 month');
            return UserPublicInfo::find()
                ->alias('u')
                ->select('p.nick, p.mobile, u.open_id, u.time_operated_re, u.kefu_id_re, u.user_id')
                ->leftJoin('user_public AS p', 'p.user_id = u.user_id')
                ->leftJoin('(SELECT student_id, user_id_visit, MAX(time_created) AS max_visit FROM visit_history  GROUP BY student_id, user_id_visit) AS v',
                    'v.student_id = u.user_id AND v.user_id_visit = u.kefu_id_re')
                ->where('v.max_visit < :time AND u.time_operated_re >= :start AND u.time_operated_re <= :end'
                    . (empty($data['saleId']) ? ' AND u.kefu_id_re <> :saleId' : ' AND u.kefu_id_re = :saleId')
                    . (empty($data['studentName']) ? ' ' : " AND (p.nick LIKE '%{$data['studentName']}%' OR p.mobile LIKE '%{$data['studentName']}%')"), [
                        ':start' => $data['start'],
                        ':end' => $data['end'],
                        ':saleId' => $data['saleId'],
                        ':time' => $time
                    ])
                ->offset(($data['num'] - 1) * 8)
                ->limit(8)
                ->asArray()
                ->all();
        }
    }

    public function getUserSaleidAndNick($uid)
    {
        return User::find()
                    ->select('nick, sales_id')
                    ->where('id = :id AND is_disabled = 0', [':id' => $uid])
                    ->asArray()
                    ->one();
    }

    public function getUserSaleidAndNameByOpenid($openId)
    {
        return UserInit::find()
            ->select('name, sales_id')
            ->where(['openid' => $openId])
            ->asArray()
            ->one();
    }

    //根据渠道id去查询同一个推荐人的推荐数
    public function getChannelCount($channelId)
    {
        return User::find()
            ->alias('u')
            ->leftJoin('product_order AS o', 'u.id=o.uid')
            ->where('channel_id='.$channelId.' and pay_status=1')
            ->count('DISTINCT u.id');
    }

    //查询推荐人的信息
    public function getChannelChannelIdSelf($channelId)
    {
        return User::find()
            ->where(['channel_id_self' => $channelId])
            ->asArray()
            ->one();
    }

    public function getUserIntroduceCount($type, $keyword, $accountId, $start, $end, $kefu, $isCheck)
    {
        if ($type == ' AND c.status = 1') {
            $type = ' AND  c.status = 1';
            $info = ' AND status = 1';
        } elseif ($type == ' AND c.status = 0') {
            $type = '  AND  c.status = 0';
            $info = ' AND status = 0';
        } else {
            $info = ' ';
        }

        $query =  UserPublicInfo::find()
            ->alias('p')
            ->leftJoin('user AS u', 'u.id = p.user_id')
            ->leftJoin('user_channel AS uc', 'uc.id = u.channel_id')
            ->leftJoin('(SELECT id, course_info, student_id, status, time_class, time_end FROM class_room 
            WHERE is_deleted = 0 AND status != 2 AND status != 3 AND is_ex_class = 1 ' .$info. ' GROUP BY student_id) AS c', 'c.student_id = p.user_id')
            ->where((empty($accountId) ? '' : " p.kefu_id = {$accountId} AND ")
                . ' uc.type = 2 AND channel_id != 0 AND sales_id = 0 '
                . $type  . (empty($keyword) ? '' : " AND  u.channel_id IN ($keyword)"));

        if (!empty($isCheck) && $isCheck == 1) {
            $query = $query->andWhere('purchase > 0 ');
        } elseif (!empty($isCheck) && $isCheck == 2) {
            $query = $query->andWhere('purchase = 0');
        }

        // 判断时间是否输入时间
        if (!empty($start) && !empty($end)) {
            $query = $query->andWhere('u.time_created >= :start AND u.time_created <= :end', [
                ':start' => $start,
                ':end' => $end
            ]);
        }

        // 判断有无选择客服
        if (!empty($kefu) && $kefu > 0) {
            $query = $query->andWhere('p.kefu_id = :kefu', [':kefu' => $kefu]);
        } elseif ($kefu == '-1') {
            $query = $query->andWhere('p.kefu_id = 0');
        }

        $query = $query->count();

        return $query;
    }

    public function getUserIntroduceList($type, $keyword, $accountId, $num, $start, $end, $kefu, $isCheck)
    {
        if ($type == ' AND c.status = 1') {
            $type = ' AND  c.status = 1';
            $info = ' AND status = 1';
        } elseif ($type == ' AND c.status = 0') {
            $type = '  AND  c.status = 0';
            $info = ' AND status = 0';
        } else {
            $info = ' ';
        }

        $query =  UserPublicInfo::find()
            ->alias('p')
            ->select('uc.name AS name, p.purchase, c.id AS class_id, p.kefu_id , u.id, c.course_info, p.user_id, p.open_id, u.nick, u.mobile, u.channel_id, u.age, u.last_level, c.time_class, c.time_end, u.time_created, end_visit_time')
            ->leftJoin('user AS u', 'u.id = p.user_id')
            ->leftJoin('user_channel AS uc', 'uc.id = u.channel_id')
            ->leftJoin('(SELECT id, course_info, student_id, status, time_class, time_end FROM class_room 
            WHERE is_deleted = 0 AND status != 2 AND status != 3 AND is_ex_class = 1 ' .$info. ' GROUP BY student_id) AS c', 'c.student_id = p.user_id')
            ->where((empty($accountId) ? '' : " p.kefu_id = {$accountId} AND ")
            . ' uc.type = 2 AND channel_id != 0 AND sales_id = 0 '
            . $type  . (empty($keyword) ? '' : " AND  u.channel_id IN ($keyword)"));

        if (!empty($isCheck) && $isCheck == 1) {
            $query = $query->andWhere('purchase > 0 ');
        } elseif (!empty($isCheck) && $isCheck == 2) {
            $query = $query->andWhere('purchase = 0');
        }

        if (!empty($start) && !empty($end)) {
            $query = $query->andWhere('u.time_created >= :start AND u.time_created <= :end', [
                ':start' => $start,
                ':end' => $end
            ]);
        }


        if (!empty($kefu) && $kefu > 0) {
            $query = $query->andWhere('p.kefu_id = :kefu', [':kefu' => $kefu]);
        } elseif ($kefu == '-1') {
            $query = $query->andWhere('p.kefu_id = 0');
        }

        $query = $query->orderBy('end_visit_time ASC')->offset( ($num - 1) * 8)->limit(8)->asArray()->all();
        return $query;
    }

    public function getUserIdbyKeyword($keyword = '')
    {
        return User::find()
            ->select('id')
            ->where("mobile LIKE '%$keyword%' OR nick LIKE '%$keyword%'")
            ->column();
    }

    public function getProductOrderData($id)
    {
        return ProductOrder::find()
            ->where('pay_status = 1 AND uid = :uid', [':uid' => $id])
            ->count();
    }

    public function getBuyOrderList($id, $num)
    {
        return ProductOrder::find()
            ->select('time_created,actual_fee')
            ->where('pay_status = 1 AND uid = :uid', [':uid' => $id])
            ->orderBy('id DESC')
            ->offset(($num - 1)*5)
            ->limit(5)
            ->asArray()
            ->all();
    }

    public function getStudentNumBySalesId($array)
    {
        return User::find()
            ->select('count(sales_id) student_num, sales_id')
            ->where(['in', 'sales_id', $array])
            ->groupBy('sales_id')
            ->asArray()
            ->all();
    }
}
