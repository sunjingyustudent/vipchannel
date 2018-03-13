<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/9
 * Time: 下午3:29
 */
namespace common\logics\visit;

use Yii;
use yii\base\Object;
use yii\db\Exception;

class VisitLogic extends Object implements IVisit
{
    /** @var  \common\sources\read\channel\ChannelAccess  $RChannelAccess */
    private $RChannelAccess;
    /** @var  \common\sources\write\chat\ChatAccess  $WChatAccess */
    private $WChatAccess;
    /** @var  \common\sources\read\complain\ComplainAccess  $RComplainAccess */
    private $RComplainAccess;
    /** @var  \common\sources\read\chat\ChatAccess  $RChatAccess */
    private $RChatAccess;
    /** @var  \common\sources\write\student\StudentAccess  $WStudentAccess */
    private $WStudentAccess;
    /** @var  \common\sources\write\channel\ChannelAccess  $WChannelAccess */
    private $WChannelAccess;
    /** @var  \common\sources\read\classes\ClassAccess  $RClassAccess */
    private $RClassAccess;
    /** @var  \common\sources\write\classes\ClassAccess  $WClassAccess */
    private $WClassAccess;
    /** @var  \common\sources\read\student\StudentAccess  $RStudentAccess */
    private $RStudentAccess;
    /** @var  \common\sources\read\visit\VisitAccess  $RVisitAccess */
    private $RVisitAccess;
    /** @var  \common\sources\write\visit\VisitAccess  $WVisitAccess */
    private $WVisitAccess;
    /** @var  \common\sources\read\account\AccountAccess  $RAccountAccess */
    private $RAccountAccess;
    /** @var  \common\logics\sale\SaleLogic  $saleService */
    private $saleService;




    public function init()
    {
        $this->WStudentAccess = Yii::$container->get('WStudentAccess');
        $this->RStudentAccess = Yii::$container->get('RStudentAccess');
        $this->RChannelAccess = Yii::$container->get('RChannelAccess');
        $this->WChannelAccess = Yii::$container->get('WChannelAccess');
        $this->RChatAccess = Yii::$container->get('RChatAccess');
        $this->WChatAccess = Yii::$container->get('WChatAccess');
        $this->RClassAccess = Yii::$container->get('RClassAccess');
        $this->WClassAccess = Yii::$container->get('WClassAccess');
        $this->RComplainAccess = Yii::$container->get('RComplainAccess');
        $this->saleService = Yii::$container->get('saleService');
        $this->RVisitAccess = Yii::$container->get('RVisitAccess');
        $this->RAccountAccess = Yii::$container->get('RAccountAccess');
        $this->WVisitAccess = Yii::$container->get('WVisitAccess');

        parent::init();
    }


    /**
     * 添加回访记录
     */
    public function addVisitRecPage($studentId)
    {
        return $this->RStudentAccess->getUserPublicArchiverInfo($studentId);
    }

    public function getStudentVisitInfoById($userId)
    {
        $list = $this->RStudentAccess->getStudentVisitInfoById($userId);

        return $list;
    }

    public function countVisitByStudentId($studentId)
    {
        $count = $this->RStudentAccess->countVisitByStudentId($studentId);

        return $count;
    }

    public function getVisitHistoryList($studentId, $num)
    {
        $list = $this->RStudentAccess->getVisitHistoryList($studentId, $num);

        foreach ($list as &$row) {
            $row['is_back'] = (intval($row['status_bit']) & 1) == 1 ? 1 : 0;
            $row['is_never_visit'] = (intval($row['status_bit']) & 4) == 4 ? 1 : 0;
            $row['is_arrange_class'] = (intval($row['status_bit']) & 2) == 2 ? 1 : 0;
        }

        return $list;
    }

    public function countComplainPage($studentId)
    {
        return $this->RComplainAccess->countComplainPage($studentId);
    }

    public function addUserArchive($request)
    {
        $count = $this->RStudentAccess->countUserArchiveInfo($request['student_id']);

        if ($request['i_keep'] == "是") {
            $is_keep = 1;
        } else {
            $is_keep = 0;
        }

        if (!empty($count)) {
            if ($this->WStudentAccess->UpdateUserArchiveInfo($request, $is_keep)) {
                $data = [
                    'msg'=>'Add Success!',
                    'status'=>'1'
                ];
            } else {
                $data = [
                    'msg'=>'Add Success!',
                    'status'=>'1'
                ];
            }
        } else {
            if ($this->WStudentAccess->addUserArchiveInfo($request, $is_keep)) {
                $data = [
                    'msg'=>'Add Success!',
                    'status'=>'1'
                ];
            } else {
                $data = [
                    'msg'=>'Add Success!',
                    'status'=>'1'
                ];
            }
        }

        return json_encode($data);
    }

    public function getUserIntentionInAddVisit($studentId)
    {
        $intenttion = $this->RStudentAccess->getUserIntentionInAddVisit($studentId);
        return $intenttion['intention'];
    }

    public function addUserHistory($request)
    {
        if ($this->WStudentAccess->addUserVisitHistory($request)) {
            if (isset($request['intention'])) {
                if ($request['intention'] == 1 || $request['intention'] == 4) {
                    $kefuId = 0;
                } else {
                    $kefuId = '';
                }
                $this->WStudentAccess->updateIntentionInfo($request['student_id'], $request['intention'], $kefuId);
                $this->WStudentAccess->updateStudentEndVisitRecord($request['student_id']);
            }

            return true;
        } else {
            return false;
        }
    }


    public function getSaleChannelVisitCount($saleChannelId)
    {
        $now = strtotime(date('Y/m/d', time())) + 3600 * 24;
        $count = $this->RVisitAccess->getSaleChannelVisitCount($saleChannelId, 0);//得到总条数
        $nowNeedDoneCount = $this->RVisitAccess->getSaleChannelVisitCount($saleChannelId, $now);//得到今天之前未跟进条数
        $data = array(
            'count' => $count,
            'nowNeedDoneCount' => $nowNeedDoneCount
        );

        return ['error' => 0, 'data' => $data];
    }

    public function getSaleChannelVisitList($saleChannelId, $num)
    {
        $list = $this->RVisitAccess->getSaleChannelVisitInfo($saleChannelId, $num);

        foreach ($list as &$v) {
            $v['time_visit'] = date('Y-m-d H:i:s', $v['time_visit']);
            $v['time_next'] = empty($v['time_next']) ? '无' : date('Y-m-d H:i:s', $v['time_next']);
            $v['nickname'] = $this->RAccountAccess->getNewSignKefuNick($v['user_id_visit']);
            if (!empty($v['class_id'])) {
                $v['exClassInfo'] = date('Y-m-d H:i', $v['time_class']) . ' | ' . (empty($v['nick']) ? '无名称' : $v['nick']);
            } else {
                $v['exClassInfo'] = '无';
            }
        }

        $data = array(
            'list' => $list
        );

        return ['error' => 0, 'data' => $data];
    }

    public function doneVisit($request)
    {
        $visitId = $request->post('visitId');
        $dayStart = strtotime(date('Y/m/d', time()));
        $dayEnd = $dayStart + 3600 * 24;

        $data = $this->RChannelAccess->getVisitInfoById($visitId);
        if (Yii::$app->user->identity->id != $data['user_id_visit']) {
            return json_encode(['error' => '本条跟进信息不是您添加，不能修改!', 'data' => []]);
        }
        //下次跟进时间必须是今天才能修改状态
        if ($data['time_next'] > 0 && $data['time_next'] < $dayEnd) {
            if ($this->WChannelAccess->doneVisitById($visitId)) {
                $nowNeedDoneCount = $this->RVisitAccess->getSaleChannelVisitCount($data['sale_channel_id'], $dayEnd);//得到今天之前未跟进条数
                return json_encode(['error' => '','data' => ['nowNeedDoneCount' => $nowNeedDoneCount]]);
            }
            return json_encode(['error' => '修改失败!', 'data' => []]);
        } else {
            return json_encode(['error' => '未到跟进时间!', 'data' => []]);
        }
    }

    public function addChannelVisit($request)
    {
        $this->WChannelAccess->doUpdateSaleChannelWorth($request['channel_id'], $request['worth']);
        if (empty($request['next_content'])) {
            $isDone = 1;
        } else {
            $isDone = 0;
        }
        if ($this->WVisitAccess->addChannelVisit($request['channel_id'],
                $request['content'],
                $request['time_next'],
                $request['next_content'],
                $request['classId'],
                $isDone)) {
            return ['error' => 0, 'data' => ''];
        } else {
            return ['error' => '添加失败','data' => ''];
        }
    }
}
