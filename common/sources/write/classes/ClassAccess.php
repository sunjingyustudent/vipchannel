<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/13
 * Time: 上午11:21
 */
namespace common\sources\write\classes;

use common\models\music\ClassEditHistory;
use common\models\music\ClassLeft;
use common\models\music\ClassRecord;
use common\models\music\UserShare;
use Yii;
use yii\db\ActiveRecord;
use crm\models\student\ClassFail;
use common\models\music\ClassRoom;
use common\models\music\Refund;
use common\models\music\StudentUserShare;

Class ClassAccess implements IClassAccess {

    public  function  UpdateFailClass($classid)
    {
        $query = ClassFail::findOne($classid);
        $query->is_deleted = 1;
        $query->time_updated = time();
        return $query->save();
    }

    public  function delFailClassALL($classIds_str){
        ClassFail::updateAll(['is_deleted' => 1], 'class_id IN ('.$classIds_str.')');
    }
    public function addClassRecord($request,$time){
        $sql = "INSERT INTO class_record(class_id,performance,note_accuracy,rhythm_accuracy,coherence,content,process,time_created) VALUES('{$request['class_id']}','{$request['learn_show']}','{$request['note_correctly']}','{$request['abstinence_correctly']}','{$request['consistent']}','{$request['peilian_course']}','{$request['peilian_zongjie']}','{$time}')";

        Yii::$app->db->createCommand($sql)->execute();
    }

    public function  updateClassRoomContact($class,$status_bit,$time_updated){
        $class->status_bit = $status_bit ;
        $class->time_updated = $time_updated;

        return $class->save();
    }

    public function updateClassSyn($data,$chat_token,$accessToken){
        $data->chat_token = $chat_token;
        $data->accessToken = $accessToken;
        $data->save();
    }

    public function saveClassRecord($recordInfo,$time_send,$class_left,$class_used){
        $recordInfo->time_send = $time_send;
        $recordInfo->class_left = $class_left;
        $recordInfo->class_used = $class_used;

        return $recordInfo->save();
    }

    public function initRecordTimeSendById($recordId)
    {
        $record = ClassRecord::findOne($recordId);
        
        $record->time_send = 0;
        
        return $record->save();
    }

    public function addClassHistoryFromPurchase($data)
    {
        $history = new ClassEditHistory();

        $history->student_id = $data['uid'];
        $history->order_id = $data['order_id'];
        $history->goods_id = $data['pid'];
        $history->goods_amount = $data['p_amount'];
        $history->amount = $data['amount'];
        $history->price = $data['price'];
        $history->instrument_id = $data['instrument_id'];
        $history->type = $data['time_type'];
        $history->is_ex_class = 0;
        $history->is_add = 1;
        $history->is_lock = 0;
        $history->is_success = 1;
        $history->time_created = time();

        $history->save();
        
        return Yii::$app->db->getLastInsertID();
    }

    public function deleteClassFromChangeGoods($leftId)
    {
        $sql = 'UPDATE class_room SET is_deleted = 1'
            . ' WHERE left_id = :left_id AND status = 0 AND is_deleted = 0';

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':left_id', $leftId)
            ->execute();
    }

    public function deleteClassHistoryFromChageGoods($orderIdOld)
    {
        $sql = 'UPDATE class_edit_history SET is_success = 0'
            . ' WHERE order_id = :order_id AND is_add = 0'
            . ' AND is_lock = 1 AND is_success = 1 AND is_deleted = 0';

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':order_id', $orderIdOld)
            ->execute();
    }

    public function reduceClassHistoryFromChangeGoods($data)
    {
        $history = new ClassEditHistory();
      
        $history->student_id = $data['uid'];
        $history->order_id = $data['order_id_old'];
        $history->amount = $data['amount'];
        $history->instrument_id = $data['instrument'];
        $history->type = $data['time_type'];
        $history->is_ex_class = 0;
        $history->is_add = 1;
        $history->is_lock = 0;
        $history->is_success = 1;
        $history->time_created = time();
        
        return $history->save();
    }


    /**
     * 删除修改学生信息
     * @param  $studentId  int
     */
    public function deleteStudentFixTime($studentId)
    {
        $sql = "DELETE FROM student_fix_time WHERE student_id = :student_id";
        return Yii::$app->db->createCommand($sql)
            ->bindValue(':student_id', $studentId)
            ->execute();
    }

    /**
     * 新增修改学生信息
     * @param  $studentId  int
     * @param  $fixInfo    array
     */
    public function addStudentFixTime($studentId, $fixInfo) 
    {
        $i = 0;
        $sqlHeader = "INSERT INTO student_fix_time(student_id,teacher_id,week,time,class_type,instrument_id,time_bit)";
        foreach($fixInfo as $row) {
            if($i == 0) {
                $sqlFooter = " VALUES($studentId,{$row['teacher_id']},{$row['week']},'{$row['time']}',{$row['class_type']},{$row['instrument_type']},{$row['time_bit']})";
                $i ++;
                continue;
            }
            $sqlFooter .= ", ($studentId,{$row['teacher_id']},{$row['week']},'{$row['time']}',{$row['class_type']},{$row['instrument_type']},{$row['time_bit']})";
            $i ++;
        }
        $sql = $sqlHeader . $sqlFooter;
        return Yii::$app->db->createCommand($sql)->execute();
    }




    public function batchUpdateClassTimeDelete($classIds) {
        $sql = "UPDATE class_room SET is_deleted = 1 WHERE id IN (".implode(',', $classIds).")";

        return Yii::$app->db->createCommand($sql)->execute();
    }


    // 修改课程数量
    public function resetClassAmountByLeftId($leftIds)
    {
        $result = array();
        $sql = "UPDATE class_left SET amount = amount + :amount WHERE id = :id";
        $db = Yii::$app->db->createCommand($sql);
        foreach ($leftIds as $leftId => $amount) {
            $result[] = $db->bindValues([
                        ':amount' => $amount,
                        ':id' => $leftId
                    ])->execute();
        }
        
        if (!empty($result)) {
            return true;
        }

        return false;
    }

    // 修改成功
    public function updateSuccessById($historyIds){
        $sql = "UPDATE class_edit_history SET is_success = 0 WHERE id IN (".implode(',', $historyIds).")";

        return Yii::$app->db->createCommand($sql)
                        ->execute();
    }



    public function reduceClassTimes($leftId, $amount)
    {
        $sql = "UPDATE class_left SET amount = amount - :amount WHERE id = :id";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':amount' => $amount,
                ':id' => $leftId
            ])->execute();
    }


    public function addClassTime($leftId,$instrumentId,$isExClass,$timeStart,$timeEnd,$studentId,$teacherId,$marks,$status) 
    {
        $sql = "INSERT INTO class_room(student_id,teacher_id,left_id,time_class,time_end,time_created,is_ex_class,marks,instrument_id, status_bit, course_info) VALUES(:student_id,:teacher_id,:left_id,:time_class,:time_end,:time_created,:is_ex_class,:marks,:instrument_id, $status, 'a:0:{}')";

        Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':student_id' => $studentId,
                ':teacher_id' => $teacherId,
                ':left_id' => $leftId,
                ':time_class' => $timeStart,
                ':time_end' => $timeEnd,
                ':time_created' => time(),
                ':is_ex_class' => $isExClass,
                ':marks' => $marks,
                ':instrument_id' => $instrumentId
            ])->execute();
        return Yii::$app->db->getLastInsertID();
    }

    public function updateFirstExClass($classId)
    {
        $sql = "UPDATE class_room SET is_first_ex = 1 WHERE id = :id";
        return Yii::$app->db->createCommand($sql)->bindValue(':id', $classId)->execute();
    }

    public function addHistory($userId,$role,$studentId,$instrumentId,$peiCardAmount,$isExClass,$type,$isAdd,$isSuccess,$isLock,$comment = '',$detail = '',$price = 0,$giveType = 0)
    {
        $sql = "INSERT INTO class_edit_history(user_id_edit,user_role_edit,student_id,instrument_id,amount,is_ex_class,type,is_add,is_success,is_lock,comment,detail,price,give_type,time_created) VALUES(:user_id_edit,:user_role_edit,:student_id,:instrument_id,:amount,:is_ex_class,:type,:is_add,:is_success,:is_lock,:comment,:detail,:price,:give_type,:time_created)";

        Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':user_id_edit' => $userId,
                ':user_role_edit' => $role,
                ':student_id' => $studentId,
                ':instrument_id' => $instrumentId,
                ':amount' => $peiCardAmount,
                ':is_ex_class' => $isExClass,
                ':type' => $type,
                ':is_add' => $isAdd,
                ':is_success' => $isSuccess,
                ':is_lock' => $isLock,
                ':comment' => $comment,
                'detail' => $detail,
                ':price' => $price,
                ':give_type' => $giveType,
                ':time_created' => time()
            ])->execute();
        return Yii::$app->db->getLastInsertID();
    }    


    public function updateHistoryIdByClassId($classId,$historyId) {
        $sql = "UPDATE class_room SET history_id = :history_id, time_updated = :time WHERE id = :id";
        return Yii::$app->db->createCommand($sql)
            ->bindValues([':history_id' => $historyId, ':time' => time(), ':id' => $classId])
            ->execute();
    }

    public function addCounts($timeDay,$type,$counts,$userId = 0) 
    {
        if(!empty($userId)) {
            $sql = "INSERT INTO statistics_user_info(user_id,time_day,time_date,type,counts,time_created) VALUES(:user_id,:time_day,:time_date,:type,:counts,:time_created) ON DUPLICATE KEY UPDATE counts = counts + :counts_2, time_updated = :time_updated";
            return Yii::$app->db->createCommand($sql)
                ->bindValues([':user_id' => $userId, ':time_day' => $timeDay, ':time_date' => date('Y-m-d', $timeDay), ':type' => $type, ':counts' => $counts, ':time_created' => time(), ':counts_2' => $counts, ':time_updated' => time()])
                ->execute();
            $param = [$userId,$timeDay,date('Y-m-d',$timeDay),$type,$counts,time(),$counts,time()];
        }
        return 100;
    }


    public function updateClassTimeDelete($classId) 
    {
        $sql = "UPDATE class_room SET is_deleted = 1 WHERE id = :id";
        return Yii::$app->db->createCommand($sql)->bindValue(':id', $classId)->execute();
    }

    public function updateHistory($userId,$role,$historyId) 
    {
        $sql = "UPDATE class_edit_history SET user_id_edit = :user_id, user_role_edit = :role , is_success = 0 WHERE id = :id";
        return Yii::$app->db->createCommand($sql)
            ->bindValues([':user_id' => $userId, ':role' => $role, ':id' => $historyId])
            ->execute();
    }


    public function addClassTimesByLeftId($leftId)
    {
        $sql = "UPDATE class_left SET amount = amount + 1 WHERE id = :id";

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':id', $leftId)
            ->execute();
    }

    public function addClassFail($classId,$type) 
    {
        $sql = "INSERT INTO class_fail(class_id,type,time_created) VALUES(:class_id,:type,:time_created)";
        return Yii::$app->db->createCommand($sql)
            ->bindValues([':class_id' => $classId, ':type' => $type, ':time_created' => time()])
            ->execute();
    }

    
    public function addClassByPurchase($data)
    {
        $class = new ClassLeft();
        
        $class->user_id = $data['uid'];
        $class->type = $data['class_type'];
        $class->left_bit = 1;
        $class->order_id = $data['order_id'];
        $class->instrument_id = $data['instrument'];
        $class->time_type = $data['time_type'];
        $class->name = $data['pname'];
        $class->price = round($data['actual_fee'] / $data['class_num'], 2);
        $class->total_amount = $data['class_num'];
        $class->amount = $data['class_num'];
        $class->ac_amount = $data['class_num'];
        
        return $class->save();
    }

    public function updateClassByChangeProduct($data)
    {

        $sql = "UPDATE class_left SET left_bit = left_bit | 2, order_id = :order_id, instrument_id = :instrument_id, time_type = :time_type, name = :name, price = :price, total_amount = :total_amount, amount = :amount, ac_amount = :ac_amount WHERE order_id = :order_id_old";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':order_id' => $data['order_id'],
                ':instrument_id' => $data['instrument'],
                ':time_type' => $data['time_type'],
                ':name' => $data['pname'],
                ':price' => $data['price'],
                ':total_amount' => $data['class_num'],
                ':amount' => $data['class_num'],
                ':ac_amount' => $data['class_num'],
                ':order_id_old' => $data['order_id_old']
            ])->execute();
    }
    
    public function updateClassRoomByLeftId($leftId){
        ClassRoom::updateAll(
            ['is_deleted' => 1],
            [
                'left_id' => $leftId,
                'status' => 0,
                'is_deleted' => 0
            ]);
    }

    public function updateClassEditHistoryByOrderId($order_id)
    {
        ClassEditHistory::updateAll(
            ['is_success' => 1],
            [
                'order_id' => $order_id,
                'is_add' => 0,
                'is_lock' => 1,
                'is_success' => 1,
                'is_deleted' => 0
            ]);
    }

    public  function saveClassEditHistoryByLeftInfo($leftInfo)
    {
        $classEditHistory = new ClassEditHistory();

        $classEditHistory->user_id_edit = Yii::$app->user->identity->id;
        $classEditHistory->user_role_edit = 2;
        $classEditHistory->student_id = $leftInfo['user_id'];
        $classEditHistory->order_id = $leftInfo['order_id'];
        $classEditHistory->amount = - $leftInfo['ac_amount'];
        $classEditHistory->price = - ($leftInfo['ac_amount'] * $leftInfo['price']);
        $classEditHistory->instrument_id = $leftInfo['instrument_id'];
        $classEditHistory->type = $leftInfo['time_type'];
        $classEditHistory->is_add = 1;
        $classEditHistory->is_success = 1;
        $classEditHistory->comment = '退费';
        $classEditHistory->time_created = time();

        return $classEditHistory->save();
    }

    public  function saveRefundByLeftInfo($leftInfo)
    {

        $refund = new Refund();
        $refund->user_id = $leftInfo['user_id'];
        $refund->order_id = $leftInfo['order_id'];
        $refund->amount = $leftInfo['ac_amount'];
        $refund->price = $leftInfo['ac_amount'] * $leftInfo['price'];

        return $refund->save();
    }

    public  function updateClassLeftInfoByLeftInfo($leftInfo)
    {
        $bit = $leftInfo['left_bit'] | 4;

        $sql = 'UPDATE class_left SET left_bit = :bit WHERE id = :id';

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':bit' => $bit,
                ':id' => $leftInfo['id']
            ])->execute();
    }


    public  function updateClassTimeEdit($classId,$leftId,$isExClass,$timeStart,$timeEnd,$teacherId,$instrumentId,$marks,$isFail)
    {
        $sql = "UPDATE class_room SET left_id = :left_id, is_ex_class = :is_ex, time_class = :time_class, time_end = :time_end, teacher_id = :teacher_id, instrument_id = :instrument_id, marks = :marks, time_updated = :time_updated".($isFail == 1 ? ",is_deleted = 0" : "")." WHERE id = :id";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':left_id' => $leftId,
                ':is_ex' => $isExClass,
                ':time_class' => $timeStart,
                ':time_end' => $timeEnd,
                ':teacher_id' => $teacherId,
                ':instrument_id' => $instrumentId,
                ':marks' => $marks,
                ':time_updated' => time(),
                ':id' => $classId
            ])->execute();
    }

    public  function updateHistoryEdit($userId,$role,$orderId,$peiCardAmount,$isExClass,$type,$historyId,$isFail) 
    {
        $sql = "UPDATE class_edit_history SET user_id_edit = :user_id_edit, user_role_edit = :user_role_edit, order_id = :order_id, type = :type, amount = :amount, is_ex_class = :is_ex_class, time_updated = :time_updated".($isFail == 1 ? ",is_success = 1" : "")." WHERE id = :id";
        
        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':user_id_edit' => $userId,
                ':user_role_edit' => $role,
                ':order_id' => $orderId,
                ':type' => $type,
                ':amount' => $peiCardAmount,
                ':is_ex_class' => $isExClass,
                ':time_updated' => time(),
                ':id' => $historyId
            ])->execute();
    }

    public  function updateClassStatusBit($classId,$data)
    {
        $sql = "UPDATE class_room SET $data WHERE id = :id";
        return Yii::$app->db->createCommand($sql)->bindValue(':id', $classId)->execute();
    }

    public function deleteClassFail($classId)
    {
        $sql = "UPDATE class_fail SET is_deleted = 1 WHERE class_id = :class_id";
        return Yii::$app->db->createCommand($sql)->bindValue(':class_id', $classId)->execute();
    }


    public function addClassCancel($request)
    {
        $sql = "INSERT INTO class_record(class_id, undo_reason, time_created) VALUES(:class_id,:reason,:time)";
        
        Yii::$app->db->createCommand($sql)
            ->bindValues([':class_id' => $request['class_id'], ':reason' => $request['content'], ':time' => time()])
            ->execute();
        return Yii::$app->db->getLastInsertID();
    }

    public  function updateClassStatus($classId,$isTeacher,$status) 
    {
        $sql = "UPDATE class_room SET status = :status, is_teacher_cancel = :type WHERE id = :id";
        
        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':status' => $status,
                ':type' => $isTeacher,
                ':id' => $classId
            ])->execute();
    }

    public function updateClassHistoryStatus($status, $historyId)
    {
        if($status == 1) {
            $sql = "UPDATE class_edit_history SET is_lock = 0 WHERE id = :id";
            Yii::$app->db->createCommand($sql)->bindValue(':id', $historyId)->execute();
        }else {
            $sql = "UPDATE class_edit_history SET is_success = 0 WHERE id = :id";
            Yii::$app->db->createCommand($sql)->bindValue(':id', $historyId)->execute();
        }
        return true;
    }

    public function updateReduceClass($historyId)
    {
        $sql = "UPDATE class_edit_history SET use_type = 1, is_lock = 0 WHERE id = :id";
        
        return Yii::$app->db->createCommand($sql)
            ->bindValue(':id', $historyId)
            ->execute();
    }


    public function reduceAcClassTimes($leftId, $amount = 1)
    {
        $sql = "UPDATE class_left SET ac_amount = ac_amount - :amount WHERE id = :id";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':amount' => $amount,
                ':id' => $leftId
            ])->execute();
    }

    /**
     * 添加购买课程
     */
    public function addBuyClassGoods($userId,$role,$studentId,$instrumentId,$goodsId,$GoodsAmount,$times,$classType,$isAdd,$isSuccess,$isLock,$price,$giveType,$historyId=0,$comment='') 
    {
        $sql = "INSERT INTO class_edit_history(user_id_edit,user_role_edit,student_id,instrument_id,goods_id,goods_amount,amount,type,is_add,is_success,is_lock,price,give_type,history_id_give,comment,time_created) VALUES(:user_id_edit,:user_role_edit,:student_id,:instrument_id,:goods_id,:goods_amount,:amount,:type,:is_add,:is_success,:is_lock,:price,:give_type,:history_id_give,:comment,:time_created)";
        
        Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':user_id_edit' => $userId, 
                ':user_role_edit' => $role, 
                ':student_id' => $studentId, 
                ':instrument_id' => $instrumentId, 
                ':goods_id' => $goodsId, 
                ':goods_amount' => $GoodsAmount, 
                ':amount' => $times, 
                ':type' => $classType, 
                ':is_add' => $isAdd, 
                ':is_success' => $isSuccess, 
                ':is_lock' => $isLock, 
                ':price' => $price, 
                ':give_type' => $giveType, 
                ':history_id_give' => $historyId, 
                ':comment' => $comment, 
                ':time_created' => time()
            ])->execute();
        
        return Yii::$app->db->getLastInsertID();
    }    


    public function addGiveClassTimes($uid, $instrumentId, $timeType, $price, $amount, $count)
    {

        if(empty($count))
        {
            $sql = "INSERT INTO class_left(user_id, type, instrument_id, time_type, name, price, total_amount, amount, ac_amount) VALUES(:uid, :type, :instrument, :time_type, :name, :price, :total_amount, :amount, :ac_amount)";

             return Yii::$app->db->createCommand($sql)
                ->bindValues([
                    ':uid' => $uid,
                    ':type' => 2,
                    ':instrument' => $instrumentId,
                    ':time_type' => $timeType,
                    ':name' => '赠送课',
                    ':price' => $price,
                    ':total_amount' => $amount,
                    ':amount' => $amount,
                    ':ac_amount' => $amount
                ])->execute();

        }

        $sql = "UPDATE class_left SET total_amount = total_amount + :total_amount, amount = amount + :amount, ac_amount = ac_amount + :ac_amount WHERE user_id = :uid AND type = 2 AND instrument_id = :instrument AND time_type = :time_type";

         return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':total_amount' => $amount,
                ':amount' => $amount,
                ':ac_amount' => $amount,
                ':uid' => $uid,
                ':instrument' => $instrumentId,
                ':time_type' => $timeType,
            ])->execute();

    }


    public  function deleteUserRoom($leftId)
    {
        ClassRoom::updateAll(
                ['is_deleted' => 1],
                [
                    'left_id' => $leftId,
                    'status' => 0,
                    'is_deleted' => 0
                ]);
    }


    public  function deleteClassEditHistory($order_id)
    {
        ClassEditHistory::updateAll(
                ['is_success' => 1],
                [
                    'order_id' => $order_id,
                    'is_add' => 0,
                    'is_lock' => 1,
                    'is_success' => 1,
                    'is_deleted' => 0
                ]);
    }




    public function addRefundClassEditHistory($user_id,$order_id, $ac_amount, $price, $instrument_id, $time_type)
    {
        $classEditHistory = new ClassEditHistory();

        $classEditHistory->user_id_edit = Yii::$app->user->identity->id;
        $classEditHistory->user_role_edit = 2;
        $classEditHistory->student_id = $user_id;
        $classEditHistory->order_id = $order_id;
        $classEditHistory->amount = - $ac_amount;
        $classEditHistory->price = - ($ac_amount * $price);
        $classEditHistory->instrument_id = $instrument_id;
        $classEditHistory->type = $time_type;
        $classEditHistory->is_add = 1;
        $classEditHistory->is_success = 1;
        $classEditHistory->comment = '退费';
        $classEditHistory->time_created = time();

        return $classEditHistory->save();
    }

    //升级套餐更新is_deleted为1
    public function updateIsDeleted($ids)
    {
        $sql = "UPDATE class_room SET is_deleted = 1 WHERE id IN (".implode(',',$ids).")";

        return Yii::$app->db->createCommand($sql)->execute();
    }


    //升级套餐更新剩余课时
    public function updateClassTimes($id)
    {
        $sql = "UPDATE user_instrument SET class_times = 0 WHERE user_id = ".$id;
        return Yii::$app->db->createCommand($sql)->execute();
    }


    public function insertClassEditHistory($student_id,$amount,$instrument,$type,$detail)
    {
        $sql = "INSERT INTO class_edit_history(user_id_edit,user_role_edit,student_id,course_kefu_id,"
            . "goods_id,goods_amount,amount,price,instrument_id,ex_old_amount,buy_old_amount,type,give_type,"
            . "history_id_give,is_ex_class,is_add,is_lock,is_success,comment,detail,trade_sn,time_created,time_updated,is_deleted)"
            . "VALUES(:user_id_edit,2,:student_id,0,0,0,:amount,0,:instrument,0,0,:type,0,0,0,1,0,1,'',:detail,'',:time,0,0)";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                'user_id_edit'=> Yii::$app->user->identity->id,
                ':student_id'=> $student_id,
                ':amount'=> $amount,
                ':instrument'=> $instrument,
                ':type'=> $type,
                ':detail'=> $detail,
                ':time'=> time()
            ])->execute();
    }


    public function deleteClassImage($id)
    {
        $sql = "DELETE  FROM class_image WHERE id = :id";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                'id'=> $id
            ])->execute();
    }

    public function updateClassTimeSend($classIdList)
    {
        $sql = "UPDATE class_room SET is_send = 1 WHERE id IN (".implode(',',$classIdList).")";

        return Yii::$app->db->createCommand($sql)
            ->execute();
    }

    public function addGiftClassAmount($param)
    {
        $sql = "UPDATE class_left SET total_amount = total_amount + :num_1, amount = amount + :num_2, ac_amount = ac_amount + :num_3 WHERE user_id = :uid AND type = 2 AND instrument_id = :instrument AND time_type = :time_type";
        
        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':num_1' => $param['class_num'],
                ':num_2' => $param['class_num'],
                ':num_3' => $param['class_num'],
                ':uid' => $param['student_id'],
                ':instrument' => $param['instrument'],
                ':time_type' => $param['time_type']
            ])->execute();
    }

    public function addGiftClass($param)
    {
        $class = new ClassLeft();
        
        $class->user_id = $param['student_id'];
        $class->type = $param['type'];
        $class->instrument_id = $param['instrument'];
        $class->time_type = $param['time_type'];
        $class->name = $param['name'];
        $class->price = $param['price'];
        $class->total_amount = $param['class_num'];
        $class->amount = $param['class_num'];
        $class->ac_amount = $param['class_num'];
        
        return $class->save();
    }


    /**
     * 保存乐谱
     */
    public  function saveMusic($id, $course_info, $marks)
    {
        $sql = 'UPDATE class_room SET course_info = :course_info, marks = :marks WHERE id = :id';

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':id' => $id, ':course_info' => serialize($course_info), 'marks' => $marks])
            ->execute();
    }


    //导入图片时删除之前的图片
    public function deleteFile($class_id)
    {
        $sql = "DELETE FROM class_image WHERE class_id = :class_id";
        return Yii::$app->db->createCommand($sql)
                    ->bindValue(':class_id',$class_id)
                    ->execute();
    }

        //插入导入的图片
    public function intoFile($class_id,$file_path){
        $sql = "INSERT INTO class_image(class_id,name,file_path,sorts,time_created)"
            . " VALUES (:class_id,:name,:file_path,0,:time_created)";

        Yii::$app->db->createCommand($sql)
             ->bindValues([
                 ':class_id'=>$class_id,
                 ':name' => '自主上传',
                 ':file_path'=>$file_path,
                 ':time_created'=>time()])
             ->execute();
        return Yii::$app->db->getLastInsertID();
    }


    public function addClassImage($classId, $filePath) 
    {
        $sql = "INSERT INTO class_image(class_id,name,file_path,time_created) VALUES(:id,'自主上传',:path,:time)";
        return Yii::$app->db->createCommand($sql)
            ->bindValues([':id' => $classId, ':path' => $filePath, ':time' => time()])
            ->execute();
    }

    public function updateClassTeacherId($class_id)
    {
        $sql = "UPDATE class_room SET teacher_id = 0 WHERE id = :class_id";

        return Yii::$app->db->createCommand($sql)
                    ->bindValue(':class_id',$class_id)
                    ->execute();
    }

    public function intoFailLog($class_id, $student_id, $teacher_id, $user_id, $role, $type)
    {
        $sql = "INSERT INTO class_fail_log(class_id, student_id, teacher_id, user_id, role, type, time_created)"
            . " VALUES (:class_id, :student_id, :teacher_id, :user_id, :role, :type, :time_created)";

        Yii::$app->db->createCommand($sql)
                    ->bindValues([
                        ':class_id' => $class_id,
                        ':student_id' => $student_id,
                        ':teacher_id' => $teacher_id,
                        ':user_id' => $user_id,
                        ':role' => $role,
                        ':type' => $type,
                        ':time_created' => time()
                    ])->execute();

        return Yii::$app->db->getLastInsertID();
    }
    
    /*
     * 把被抢占的体验课老师设置为0
     * 
     */
    public function updateClassInfoTeacher($classIds){
     
        $sql = "UPDATE class_room SET teacher_id = 0 WHERE id = ".$classIds." and is_ex_class = 1 ";

        return Yii::$app->db->createCommand($sql)->execute();
    
    }

    /**
     * @param $classIds
     * @return int
     * @author xl
     * 把因固定课抢占的课teacher_id = 0
     */
    public function updateClassInfoTeacherByFix($classIds){

        $sql = "UPDATE class_room SET teacher_id = 0 WHERE id = ".$classIds." AND status = 0 AND is_deleted = 0";

        return Yii::$app->db->createCommand($sql)->execute();

    }

    public function doChangeClassTime($class_id,$ahead,$defer){

        if(!empty($ahead)){
            $sql = "UPDATE class_room SET time_class = time_class - :ahead,time_end = time_end - :ahead WHERE id = :class_id";

            return Yii::$app->db->createCommand($sql)
                ->bindValues([
                    ':class_id' =>$class_id,
                    ':ahead' =>$ahead
                    ])
                ->execute();
        }else{
            $sql = "UPDATE class_room SET time_class = time_class + :defer,time_end = time_end + :defer WHERE id = :class_id";

            return Yii::$app->db->createCommand($sql)
                ->bindValues([
                    ':class_id' =>$class_id,
                    ':defer' =>$defer
                ])
                ->execute();
        }

    }
    
    /*
     *  添加学生分享记录
     * create by sjy
     * 
     */
    public function addStudentUserShare($class_id,$openid,$is_back,$is_free){
         //添加预约记录
         $StudentUserShare = new StudentUserShare();
         $StudentUserShare->class_id = $class_id;
         $StudentUserShare->open_id = $openid;
         $StudentUserShare->is_purview = 1;
         $StudentUserShare->is_back_share = $is_back;
         $StudentUserShare->share_time = time();
         $StudentUserShare->is_free = $is_free;
         $StudentUserShare->save();
    }

    public function lockStudentFixTime()
    {
        $sql = "LOCK TABLES student_fix_time WRITE, statistics_teacher_rest WRITE, teacher_info WRITE, user_teacher WRITE";

        return Yii::$app->db->createCommand($sql)
            ->execute();
    }

    public function unLockStudentFixTime()
    {
        $sql = "UNLOCK TABLES";

        return Yii::$app->db->createCommand($sql)
            ->execute();
    }
}

