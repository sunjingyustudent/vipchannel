<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/2/7
 * Time: 下午4:33
 */
namespace common\logics\sale;

use Yii;
use yii\base\Object;
use yii\db\Exception;
use yii\helpers\VarDumper;

class DistributionLogic extends Object implements IDistribution {

    /** @var  \common\sources\read\account\AccountAccess $RAccountAccess */
    private $RAccountAccess;
    /** @var  \common\sources\write\student\StudentAccess $WStudentAccess */
    private $WStudentAccess;
    /** @var  \common\sources\read\student\StudentAccess $RStudentAccess */
    private $RStudentAccess;
    /** @var  \common\sources\write\account\AccountAccess $WAccountAccess */
    private $WAccountAccess;
    /** @var  \common\sources\write\channel\ChannelAccess $WChannelAccess */
    private $WChannelAccess;

    public function init()
    {
        $this->RAccountAccess = Yii::$container->get('RAccountAccess');
        $this->WStudentAccess = Yii::$container->get('WStudentAccess');
        $this->RStudentAccess = Yii::$container->get('RStudentAccess');
        $this->WAccountAccess = Yii::$container->get('WAccountAccess');
        $this->WChannelAccess = Yii::$container->get('WChannelAccess');
    }

    public function autoAssignUser($uid, $status ='')
    {
        $channel_info = $this->RStudentAccess->getChannelIdsByStudentId($uid);

        if (empty($channel_info['sales_id']) && !empty($channel_info['channel_id']))
        {

            $channel_id = $this->RStudentAccess->getReferralChannel($channel_info['channel_id']);

            if (!empty($channel_id))
            {
                $kefu_info = $this->RStudentAccess->getUserPublicByUserId($channel_id);

                $this->WStudentAccess->doBindKefu($uid, $kefu_info['kefu_id']);
                $data = array(
                    'kefu_id' => $kefu_info['kefu_id']
                );

                return  ['error'=> 0, 'data'=> $data];
            }
        }

        // 获取所有今天上班的销售成员
        $data = $this->getTimeFormat();

        $time_start = strtotime('9:00');
        $time_end = strtotime('22:00');
        $on_time = time();


        for($i = 0; $i <= 20; $i++) {

            // 查找当天是否全天休息
            $all_list = $this->RAccountAccess
                ->getAllAtWorkKefuId($data['work_day']);

            $no_work = array();
            $all_id = array();
            foreach ($all_list as $v) {
                if ($v['time_bit'] == 562949953421311) {
                    $no_work[] = $v['id'];
                }
                $all_id[] = $v['id'];
            }



            $exclude_id = empty($all_id) ? 0 : implode(',',$all_id);
            $week_list = $this->RAccountAccess
                ->getAtWeekWorkKefuId($data['week'], '', $exclude_id);

            if ($no_work == $all_id && empty($week_list)) {
                $data['work_day'] = $data['work_day'] + 86400;
                $data['tomorrow_day'] = $data['tomorrow_day'] + 86400;
                $time_start = $time_start + 86400;
                $time_end = $time_end + 86400;
                $on_time = $on_time + 86400;

                if ($data['week'] == 7)
                {
                    $data['week'] = 0;
                    ++$data['week'];

                } else {
                    ++$data['week'];
                }

            } else {
                break;
            }
        }

        // 不上班的第一种情况 0:00到9:00
        if($on_time < $time_start) {
            // 求出当前分配的最大的值
            $max_number = $this->RAccountAccess
                ->getMaxNumber($data['work_day']);
            if(empty($max_number)) {
                $max_number = 1;
            }

            // 得到当天上班成员ID
            $all_id = array();
            $all_list = $this->RAccountAccess
                ->getAllAtWorkKefuId($data['work_day']);

            foreach ($all_list as $v) {
                $all_id[] = $v['id'];
            }

            $exclude_id = empty($all_id) ? 0 : implode(',',$all_id);
            $week_list = $this->RAccountAccess
                ->getAtWeekWorkKefuId($data['week'], '', $exclude_id);

            $week_list = empty($week_list) ? 0 :$week_list;
            if(!empty($week_list) || !empty($all_id) ) {
                $work_info = array_merge($week_list,$all_id);
            } else {
                $work_info = array();
            }

            // 进行轮询分配
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // 进行插入客服接待信息
                foreach ($work_info as $v) {
                    $this->WAccountAccess->doAddKefuReceptionInfo($v, $data['work_day'], $max_number);
                }

                // 获得需要增长的ID
                $kefu_id = $this->RAccountAccess->getAtWorkKefuInfo($data['work_day'], $work_info);

                if (empty($kefu_id)) {
                    $info['data'] = '该日客服已经分配完成';
                    $info['error'] = 0;
                } else {
                    $info = $this->bindKefu($uid, $kefu_id, $data['work_day']);
                }

                $transaction->commit();
            } catch (\yii\base\Exception $e) {
                $transaction->rollBack();
                return ['error' => '自动分配客服失败请检查相关配置.', 'data' => ''];
            }
        }

        // 不上班的第二种情况 22:00 - 24:00
        if($on_time > $time_end) {

            // 求出当前分配的最大的值
            $max_number = $this->RAccountAccess->getMaxNumber($data['tomorrow_day']);
            if(empty($max_number)) {
                $max_number = 1;
            }

            // 得到当天上班成员ID
            $all_id = array();
            $all_list = $this->RAccountAccess->getAllAtWorkKefuId($data['tomorrow_day']);
            foreach ($all_list as $v) {
                $all_id[] = $v['id'];
            }

            $exclude_id = empty($all_id) ? 0 : implode(',',$all_id);

            if($data['week'] == 7) {
                $data['week'] = 0;
                ++$data['week'];
            } else {
                ++$data['week'];
            }

            $week_list = $this->RAccountAccess
                ->getAtWeekWorkKefuId($data['week'], '', $exclude_id);

            $week_list = empty($week_list) ? 0 :$week_list;
            if(!empty($week_list) || !empty($all_id) ) {
                $work_info = array_merge($week_list,$all_id);
            } else {
                $work_info = array();
            }

            // 进行轮询分配
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // 进行插入客服接待信息
                foreach ($work_info as $v) {
                    $this->WAccountAccess->doAddKefuReceptionInfo($v, $data['tomorrow_day'], $max_number);
                }

                // 获得需要增长的ID
                $kefu_id = $this->RAccountAccess->getAtWorkKefuInfo($data['tomorrow_day'], $work_info);

                if (empty($kefu_id)) {
                    $info['data'] = '明日客服自动分配完成。';
                    $info['error'] = '0';
                } else {
                    $info = $this->bindKefu($uid, $kefu_id, $data['tomorrow_day']);
                }

                $transaction->commit();
            } catch (\yii\base\Exception $e) {
                $transaction->rollBack();
                return ['error' => '自动分配客服失败请检查相关配置.', 'data' => ''];
            }
        }

        if($on_time >= $time_start && $on_time <= $time_end) {

            // 求出当前分配的最大的值
            $max_number = $this->RAccountAccess
                            ->getMaxNumber($data['work_day']);
            if(empty($max_number)) {
                $max_number = 1;
            }

            $all_list = $this->RAccountAccess
                            ->getAllAtWorkKefuId($data['work_day']);

            $work = array();
            $all = array();
            foreach ($all_list as $v) {
                if(($v['time_bit'] & $data['bit']) == 0) {
                    $work[] = $v['id'];
                }
                $all[] = $v['id'];
            }

            $exclude_id = empty($all) ? 0 : implode(',',$all);

            $week_list = $this->RAccountAccess
                            ->getAtWeekWorkKefuId($data['week'], $data['bit'], $exclude_id);

            if($week_list || $work ) {
                $work_info = array_merge($week_list,$work);
            } else {
                $work_info = array();
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($work_info as $v) {
                    $this->WAccountAccess
                        ->doAddKefuReceptionInfo($v, $data['work_day'], $max_number);
                }

                $kefu_id = $this->RAccountAccess
                            ->getAtWorkKefuInfo($data['work_day'], $work_info);

                if (empty($kefu_id)) {
                    $info['data'] = '该时间段客服自动分配完成。';
                    $info['error'] = 0;
                } else {
                    $info = $this->bindKefu($uid, $kefu_id, $data['work_day']);
                }

                $transaction->commit();
            } catch (\yii\base\Exception $e) {
                $transaction->rollBack();
                return ['error' => '自动分配客服失败请检查相关配置.', 'data' => ''];
            }
        }

        return ['error'=> $info['error'], 'data'=>$info['data']];
    }

    private function getTimeFormat()
    {
        $time = date('H:i',time());
        $timeFormat = explode(':', $time);

        $week = date('N',time());

        $index = 2*$timeFormat[0] + ($timeFormat[1] < 30 ? 0 : 1);
        $bit = pow(2,$index);

        $work_day = strtotime(date('Y-m-d',time()));

        $data = [
            'bit'   => $bit,
            'work_day'  => $work_day,
            'tomorrow_day' => $work_day + 86400,
            'week' => $week,
        ];

        return $data;
    }

    private function bindKefu($uid, $kefu_id, $time)
    {
        $this->WAccountAccess->doEditKefuReceptionInfo($kefu_id, $time);
        $this->WStudentAccess->doBindKefu($uid, $kefu_id);

        $data['kefu_id'] = $kefu_id;
        return ['error' => 0, 'data' => $data];

    }

//    private function getTimeFormat()
//    {
//        $time = date('H:i',time());
//        $timeFormat = explode(':', $time);
//
//        $week = date('N',time());
//
//        $index = 2*$timeFormat[0] + ($timeFormat[1] < 30 ? 0 : 1);
//        $bit = pow(2,$index);
//
//        $work_day = strtotime(date('Y-m-d',time()));
//
//        $data = [
//                'bit'   => $bit,
//                'work_day'  => $work_day,
//                'tomorrow_day' => $work_day + 86400,
//                'week' => $week
//                ];
//
//        return $data;
//    }

    public function autoAssignChannelUser($uid)
    {
        // 获取所有今天上班的销售成员
        $data = $this->getTimeFormat();

        $time_start = strtotime('9:00');
        $time_end = strtotime('22:00');
        $on_time = time();

        for($i = 0; $i <= 20; $i++) {
            // 查找当天是否全天休息
            $all_list = $this->RAccountAccess
                ->getAllAtWorkChannelKefuId($data['work_day']);

            $no_work = array();
            $all_id = array();
            foreach ($all_list as $v) {
                if ($v['time_bit'] == 562949953421311) {
                    $no_work[] = $v['id'];
                }
                $all_id[] = $v['id'];
            }

            $exclude_id = empty($all_id) ? 0 : implode(',',$all_id);
            $week_list = $this->RAccountAccess
                ->getAtWeekWorkChannelKefuId($data['week'], '', $exclude_id);

            if($no_work == $all_id && empty($week_list)) {
                $data['work_day'] = $data['work_day'] + 86400;
                $data['tomorrow_day'] = $data['tomorrow_day'] + 86400;
                $time_start = $time_start + 86400;
                $time_end = $time_end + 86400;
                $on_time = $on_time + 86400;

                if($data['week'] == 7) {
                    $data['week'] = 0;
                    ++$data['week'];
                } else {
                    ++$data['week'];
                }
            } else {
                break;
            }
        }

        // 不上班的第一种情况 0:00到9:00
        if($on_time < $time_start) {
            // 求出当前分配的最大的值
            $max_number = $this->RAccountAccess
                ->getChannelMaxNumber($data['work_day']);
            if(empty($max_number)) {
                $max_number = 1;
            }

            // 得到当天上班成员ID
            $all_id = array();
            $all_list = $this->RAccountAccess
                ->getAllAtWorkChannelKefuId($data['work_day']);

            foreach ($all_list as $v) {
                $all_id[] = $v['id'];
            }

            $exclude_id = empty($all_id) ? 0 : implode(',',$all_id);
            $week_list = $this->RAccountAccess
                ->getAtWeekWorkChannelKefuId($data['week'], '', $exclude_id);

            $week_list = empty($week_list) ? 0 :$week_list;
            if(!empty($week_list) || !empty($all_id) ) {
                $work_info = array_merge($week_list,$all_id);
            } else {
                $work_info = array();
            }

            // 进行轮询分配
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // 进行插入客服接待信息
                foreach ($work_info as $v) {
                    $this->WAccountAccess->doAddChannelKefuReceptionInfo($v, $data['work_day'], $max_number);
                }

                // 获得需要增长的ID
                $kefu_id = $this->RAccountAccess->getAtWorChannelKefuInfo($data['work_day'], $work_info);

                if (empty($kefu_id)) {
                    $info['data'] = '该日客服已经分配完成';
                    $info['error'] = 0;
                } else {
                    $info = $this->bindChannelKefu($uid, $kefu_id, $data['work_day']);
                }

                $transaction->commit();
            } catch (\yii\base\Exception $e) {
                $transaction->rollBack();
                return ['error' => '自动分配客服失败请检查相关配置.', 'data' => ''];
            }
        }

        // 不上班的第二种情况 22:00 - 24:00
        if($on_time > $time_end) {

            // 求出当前分配的最大的值
            $max_number = $this->RAccountAccess->getChannelMaxNumber($data['tomorrow_day']);
            if(empty($max_number)) {
                $max_number = 1;
            }

            // 得到当天上班成员ID
            $all_id = array();
            $all_list = $this->RAccountAccess->getAllAtWorkChannelKefuId($data['tomorrow_day']);
            foreach ($all_list as $v) {
                $all_id[] = $v['id'];
            }

            $exclude_id = empty($all_id) ? 0 : implode(',',$all_id);

            if($data['week'] == 7) {
                $data['week'] = 0;
                ++$data['week'];
            } else {
                ++$data['week'];
            }

            $week_list = $this->RAccountAccess
                ->getAtWeekWorkChannelKefuId($data['week'], '', $exclude_id);

            $week_list = empty($week_list) ? 0 :$week_list;
            if(!empty($week_list) || !empty($all_id) ) {
                $work_info = array_merge($week_list,$all_id);
            } else {
                $work_info = array();
            }

            // 进行轮询分配
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // 进行插入客服接待信息
                foreach ($work_info as $v) {
                    $this->WAccountAccess->doAddChannelKefuReceptionInfo($v, $data['tomorrow_day'], $max_number);
                }

                // 获得需要增长的ID
                $kefu_id = $this->RAccountAccess->getAtWorChannelKefuInfo($data['tomorrow_day'], $work_info);

                if (empty($kefu_id)) {
                    $info['data'] = '明日客服自动分配完成。';
                    $info['error'] = '0';
                } else {
                    $info = $this->bindChannelKefu($uid, $kefu_id, $data['tomorrow_day']);
                }

                $transaction->commit();
            } catch (\yii\base\Exception $e) {
                $transaction->rollBack();
                return ['error' => '自动分配客服失败请检查相关配置.', 'data' => ''];
            }
        }

        if($on_time >= $time_start && $on_time <= $time_end) {

            // 求出当前分配的最大的值
            $max_number = $this->RAccountAccess
                ->getChannelMaxNumber($data['work_day']);
            if(empty($max_number)) {
                $max_number = 1;
            }

            $all_list = $this->RAccountAccess
                ->getAllAtWorkChannelKefuId($data['work_day']);
            $work = array();
            $all = array();
            foreach ($all_list as $v) {
                if(($v['time_bit'] & $data['bit']) == 0) {
                    $work[] = $v['id'];
                }
                $all[] = $v['id'];
            }

            $exclude_id = empty($all) ? 0 : implode(',',$all);

            $week_list = $this->RAccountAccess
                ->getAtWeekWorkChannelKefuId($data['week'], $data['bit'], $exclude_id);
            if($week_list || $work ) {
                $work_info = array_merge($week_list,$work);
            } else {
                $work_info = array();
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($work_info as $v) {
                    $this->WAccountAccess
                        ->doAddChannelKefuReceptionInfo($v, $data['work_day'], $max_number);
                }
                $kefu_id = $this->RAccountAccess
                    ->getAtWorChannelKefuInfo($data['work_day'], $work_info);

                if (empty($kefu_id)) {
                    $info['data'] = '该时间段客服自动分配完成。';
                    $info['error'] = 0;
                } else {
                    $info = $this->bindChannelKefu($uid, $kefu_id, $data['work_day']);
                }

                $transaction->commit();
            } catch (\yii\base\Exception $e) {
                $transaction->rollBack();
                return ['error' => '自动分配客服失败请检查相关配置.', 'data' => ''];
            }
        }

        return ['error'=> $info['error'], 'data'=>$info['data']];
    }


    /**
     * 给渠道绑定客服
     * create by  wangkai
     */
    private function bindChannelKefu($uid, $kefu_id, $time)
    {
        $this->WAccountAccess->doEditChannelKefuReceptionInfo($kefu_id, $time);
        $this->WChannelAccess->bindChannelKefu($uid, $kefu_id);

        $data['kefu_id'] = $kefu_id;
        return ['error' => 0, 'data' => $data];

    }
}