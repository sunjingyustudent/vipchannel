<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 17/1/3
 * Time: 10:26
 */
namespace common\sources\read\complain;


use common\models\music\Complain;
use Yii;
use yii\db\ActiveRecord;
use common\models\music\UserPublicInfo;

Class ComplainAccess implements IComplainAccess
{
    public function getComplainCount($timeStart,$timeEnd,$status)
    {
        if(empty($status))
        {
            $sql = "SELECT COUNT(*) AS count FROM complain WHERE (status = 0 OR status = 1) AND tag = 1 AND time_created >= :timeStart AND time_created < :timeEnd";
        }else{
            $sql = "SELECT COUNT(*) AS count FROM complain WHERE status = 2 AND tag = 1 AND time_created >= :timeStart AND time_created < :timeEnd";
        }

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':timeStart' => $timeStart, 'timeEnd' => $timeEnd])
            ->queryScalar();
    }

    public function getComplainInfo($timeStart, $timeEnd, $status, $page_num)
    {
        $complain = Complain::find()
            ->alias('c')
            ->select('c.*, uin.head, uin.name as wename, u.mobile, u.id AS student_id, u.nick, r.prefix, r.money, tr.name as reward_name')
            ->leftJoin('user_init as uin','uin.openid = c.open_id')
            ->leftJoin('wechat_acc as we','we.openid = c.open_id')
            ->leftJoin('user as u','u.id = we.uid')
            ->leftJoin('reward_record as r','r.id = c.reward_record_id')
            ->leftJoin('teacher_reward_rule as tr','tr.id = r.reward_id')
            ->andWhere('c.time_created >= :timeStart',[':timeStart' => $timeStart])
            ->andWhere('c.time_created < :timeEnd',[':timeEnd' => $timeEnd]);

        if(empty($status))
        {
            $complain ->andWhere('c.status != 2');
            $complain ->andWhere('c.tag = 1');
        }else{
            $complain ->andWhere('c.status = 2');
            $complain ->andWhere('c.tag = 1');
        }

        return $complain ->orderBy(empty($status) ? 'c.time_created desc' : 'c.time_update desc')
            ->offset(($page_num-1) * 10)
            ->limit(10)
            ->asArray()
            ->all();
    }

    public function getComplainById($id)
    {
        return Complain::find()
            ->where('id = :id',[':id'=>$id])
            ->one();
    }

    public function countComplainPage($student_id){
        $count = Complain::find()
            ->alias('cp')
            ->leftJoin('user_public_info AS i','cp.open_id = i.open_id')
            ->where('i.user_id = :userid', [':userid' => $student_id])
            ->count();

        return $count;
    }

    public function getComplainList($student_id , $num){
        $list = Complain::find()
            ->alias('cp')
            ->select('cp.time_created AS complain_time, cp.content, cp.teacher_context, cp.teacher_remark')
            ->leftJoin('user_public_info AS i','cp.open_id = i.open_id')
            ->where('i.user_id = :userid',
                [':userid' => $student_id])
            ->orderBy('cp.time_created DESC')
            ->offset(($num - 1)*8)
            ->limit(8)
            ->asArray()
            ->all();
        return $list;
    }

    public function getParentComplaintsPage($status)
    {
        if(empty($status))
        {
            $sql = "SELECT COUNT(*) AS count FROM complain WHERE status = 0 OR status = 2";
        }else{
            $sql = "SELECT COUNT(*) AS count FROM complain WHERE status = 3";
        }

        return Yii::$app->db->createCommand($sql)
                        ->queryScalar();
    }

    public function getParentComplaintsList($status,$page)
    {
        $complain =  Complain::find()
                            ->alias('c')
                            ->select('c.id, c.content, c.status, c.kefu_context, c.kefu_remark, c.open_id, c.class_id, c.teacher_context, c.teacher_remark, c.time_created, uin.head, uin.name as wename, u.mobile, u.nick')
                            ->leftJoin('user_init as uin','uin.openid = c.open_id')
                            ->leftJoin('wechat_acc as we','we.openid = c.open_id')
                            ->leftJoin('user as u','u.id = we.uid');
        if(empty($status))
        {
            $complain ->where('status != 1');
            $complain ->andWhere('status != 3');
        }else{
            $complain ->where('status = 3');
        }

        return $complain ->orderBy(empty($status) ? 'c.time_created desc' : 'c.time_update desc')
            ->offset(($page-1) * 5)
            ->limit(5)
            ->asArray()
            ->all();
    }

    public function dealComplaintsInfo($id)
    {
        return Complain::find()
            ->where('id = :id',[':id'=>$id])
            ->one();
    }

}