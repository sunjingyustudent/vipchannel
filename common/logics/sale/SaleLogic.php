<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/12
 * Time: 下午4:02
 */
namespace common\logics\sale;

use Yii;
use yii\base\Object;
use yii\db\Exception;
use yii\db\Query;

class SaleLogic extends Object implements ISale
{

    /** @var  \common\sources\read\channel\ChannelAccess $RChannelAccess */
    private $RChannelAccess;
    /** @var  \common\sources\read\classes\ClassAccess $RClassAccess */
    private $RClassAccess;
    /** @var  \common\logics\account\AccountLogic $accountService */
    private $accountService;
    /** @var  \common\sources\read\account\AccountAccess $RAccountAccess */
    private $RAccountAccess;
    /** @var  \common\sources\write\student\StudentAccess $WStudentAccess */
    private $WStudentAccess;
    /** @var  \common\sources\read\student\StudentAccess $RStudentAccess */
    private $RStudentAccess;

    public function init()
    {
        $this->RStudentAccess = Yii::$container->get('RStudentAccess');
        $this->WStudentAccess = Yii::$container->get('WStudentAccess');
        $this->RChannelAccess = Yii::$container->get('RChannelAccess');
        $this->RClassAccess = Yii::$container->get('RClassAccess');
        $this->RAccountAccess = Yii::$container->get('RAccountAccess');
        $this->accountService = Yii::$container->get('accountService');
        parent::init();
    }

    public function getAllSales()
    {
        $list = $this->RStudentAccess->getAllSales();
        return $list ? $list : [];
    }


    /**
     * 修饰已付款的用户列表，并且传递参数(配合分页+搜索)
     * @User：王锴
     * @Time: 16/12/13 21:38
     */
    public function getAllPayUserList($keyword, $num, $datanum, $maxnum)
    {
        // 用户信息
        $type = 0;
        $bodySql = $this->getSelfPayUserSql($keyword, $type, $datanum, $maxnum);
        $list = $this->RStudentAccess->getAllPayUserList($bodySql, $num);

        //将数据变成易看懂
        foreach ($list as $k => $v) {
            $list[$k]['class_consume'] = empty($v['class_consume']) ? 0 : $v['class_consume'];
            $list[$k]['introduce'] = empty($v['introduce']) ? 0 : $v['introduce'];


            $sum = $list[$k]['ac_amount'];
            if ($sum == 0) {
                $list[$k]['endweek'] = '没有剩余课程了.';
            } elseif ($list[$k]['class_consume'] == 0) {
                $list[$k]['endweek'] = '上周无安排课记录.';
            } elseif ($list[$k]['class_consume'] == 1) {
                $list[$k]['endweek'] = '预计要' . $sum . '周';
                $number = $sum * 7;
                $list[$k]['day'] = '还有' . $number . '天';
            } else {
                $numup = ceil($sum / $list[$k]['class_consume']);
                $numdown = floor($sum / $list[$k]['class_consume']);
                $list[$k]['endweek'] = '预计要' . $numdown . '~' . $numup . '周';
                $number = $numup * 7;
                $list[$k]['day'] = '还有' . $number . '天';
            }
        }


        return $list ? $list : [];
    }


    /**
     * 统计用户数量(搜索，查询都将统计)
     * @User：王锴
     * @Time: 16/12/13 21:38
     */
    public function selfPayUserPage($keyword = ' ', $type = 0, $datanum = '', $maxnum = '')
    {
        $bodySql = $this->getSelfPayUserSql($keyword, $type, $datanum, $maxnum);
        $count = $this->RStudentAccess->getPayUserInfo($bodySql);
        return $count;
    }


    /**
     * 计算新签用户
     * @User：王锴
     * @Time: 16/12/13 21:38
     */
    public function getAllNewPayUserList($keyword, $num, $datanum)
    {
        // 用户信息
        $type = 1;


        $bodySql = $this->getSelfPayUserSql($keyword, $type, $datanum);

        $list = $this->RStudentAccess->getAllNewPayUserList($bodySql, $num);
        // echo '<pre>';
        // var_dump($list);

        //将数据变成易看懂
        foreach ($list as $k => $v) {
            $list[$k]['time_created'] = date('Y-m-d H:i:s', $v['time_created']);
            $list[$k]['area'] = $v['area'] . '级地区';
            $list[$k]['birth'] = date('m月d日', $v['birth']);
            $list[$k]['total_amount'] = $v['total_amount'] . '课时';
            $list[$k]['ac_amount'] = empty($v['ac_amount']) ? '课时!没啦' : $v['ac_amount'] . '课时';

            if (!empty($v['newsale'])) {
                $list[$k]['channel_type'] = '主课';
                $list[$k]['qudao'] = $v['newsale'];
            } elseif (!empty($v['oldsale'])) {
                $list[$k]['qudao'] = $v['oldsale'];
                if ($v['channel_type'] == 2) {
                    $list[$k]['channel_type'] = '家长';
                } elseif ($v['channel_type'] == 5) {
                    $list[$k]['channel_type'] = '活动';
                } else {
                    $list[$k]['channel_type'] = '其他';
                }
            } else {
                $list[$k]['channel_type'] = '无';
                $list[$k]['qudao'] = '无';
            }


            if (!empty($v['marks'])) {
                $list[$k]['marks'] = $v['marks'];
            } else {
                $list[$k]['marks'] = '无备注';
            }
            if ($v['is_problem'] == 1) {
                $list[$k]['is_problem'] = '有问题';
            } else {
                $list[$k]['is_problem'] = '无问题';
            }

            if (!empty($v['teacher_nick'])) {
                $list[$k]['teacher_level'] = '等级为' . $v['teacher_level'] . '级';
            } else {
                $list[$k]['teacher_level'] = '暂无相关数据';
                $list[$k]['teacher_nick'] = "<span style='color:red'>无相关任课老师</span>";
            }
            if (!empty($v['max_time'])) {
                if ($v['max_time'] > time()) {
                    $list[$k]['max_time'] = date('m-d H:i', $v['max_time']);
                } else {
                    $list[$k]['max_time'] = '没有安排课程';
                }
            } else {
                $list[$k]['max_time'] = '没有安排课程';
            }
        }
        return $list ? $list : [];
    }

    public function countDistributeUser($keyword)
    {
        if ($keyword == '全部') {
            $keyword = '';
        }
        $count = $this->RStudentAccess->countDistributeUser($keyword);
        return empty($count) ? 0 : $count;
    }

    public function getDistributeUserList($num, $keyword)
    {
        $list = $this->RStudentAccess->getDistributeUserList($num, $keyword);

        if (!empty($list)) {
            foreach ($list as &$row) {
                if ($row['course_info'] == 'a:0:{}' && empty($row['if_class_id'])) {
                    $row['course_info'] = '0';
                } else {
                    $row['course_info'] = '1';
                }

                $row['birth'] = empty($row['birth']) ? '未设置'
                    : date('Y', time()) - date('Y', $row['birth']);

                $row['time_created'] = empty($row['time_created']) ? '未设置'
                    : date('Y-m-d H:i', $row['time_created']);

                $row['area'] = empty($row['area']) ? '无'
                    : $row['area'] . '类地区';

                $row['level'] = empty($row['level']) ? '未设置'
                    : $row['level'];


                $channelIds = $this->RStudentAccess->getChannelIdsByStudentId($row['id']);
                if (!empty($channelIds['sales_id'])) {
                    $name = '[主课]';
                } elseif (!empty($channelIds['channel_id'])) {
                    $channelInfo = $this->RChannelAccess->getUserChannelInfoById($channelIds['channel_id']);
                    if ($channelInfo['type'] == 5) {
                        $name = '[活动] ';
                    } elseif ($channelInfo['type'] == 2) {
                        $name = '[家长] ';
                    } else {
                        $name = '[其他] ';
                    }
                } else {
                    $name = '无';
                }
                $row['channel'] = $name;
            }
        }
        return empty($list) ? [] : $list;
    }

    public function getChannelNameByStudentId($studentid)
    {
        $channelIds = $this->RStudentAccess->getChannelIdsByStudentId($studentid);

        if (!empty($channelIds['sales_id'])) {
            $name = $this->RChannelAccess->getSalesChannelNameById($channelIds['sales_id']);
        } elseif (!empty($channelIds['channel_id'])) {
            $channelInfo = $this->RChannelAccess->getUserChannelInfoById($channelIds['channel_id']);
            if ($channelInfo['type'] == 5) {
                $name = '[活动] ' . $channelInfo['name'];
            } elseif ($channelInfo['type'] == 2) {
                $userId = $this->RStudentAccess->getUserIdByChannelIdSelf($channelInfo['id']);
                $salesName = $this->RStudentAccess->getSalesByStudntId($userId);

                $name = '[家长] ' . $channelInfo['name'] . ' (顾问:' . $salesName . ')';
            } else {
                $name = '[其他] ' . $channelInfo['name'];
            }
        } else {
            $name = '无';
        }

        return $name;
    }


    public function addUserHistory($request)
    {
        if ($this->WStudentAccess->addUserVisitHistory($request)) {
            if (isset($request['intention'])) {
                if ($request['intention'] == 1 || $request['intention'] == 4) {
                    $kefuId = 0;
                } else {
                    $kefuId = Yii::$app->user->identity->id;
                }
                $this->WStudentAccess->updateIntentionInfo($request['student_id'], $request['intention'], $kefuId);
            }
            return true;
        } else {
            return false;
        }
    }

    public function addUserArchive($request)
    {
        $count = $this->RStudentAccess->countUserArchiveInfo($request['student_id']);

        if ($request['i_keep'] == "是") {
            $iskeep = 1;
        } else {
            $iskeep = 0;
        }

        if (!empty($count)) {
            if ($this->WStudentAccess->UpdateUserArchiveInfo($request, $iskeep)) {
                $data = [
                    'msg' => 'Add Success!',
                    'status' => '1'
                ];
            } else {
                $data = [
                    'msg' => 'Add Success!',
                    'status' => '1'
                ];
            }
        } else {
            if ($this->WStudentAccess->addUserArchiveInfo($request, $iskeep)) {
                $data = [
                    'msg' => 'Add Success!',
                    'status' => '1'
                ];
            } else {
                $data = [
                    'msg' => 'Add Success!',
                    'status' => '1'
                ];
            }
        }

        return json_encode($data);
    }

    public function countExUser($keyword, $time)
    {
        $time = empty($time) ? strtotime(date('Y-m-d', time())) : strtotime($time);
        $count = $this->RStudentAccess->countExUser($keyword, $time);
        return empty($count) ? 0 : $count;
    }

    public function getExUserList($num, $keyword, $time)
    {
        $time = empty($time) ? strtotime(date('Y-m-d', time())) : strtotime($time);
        $list = $this->RClassAccess->getExUserList($num, $keyword, $time);

        foreach ($list as &$item) {
            $item['have_image'] = $item['course_info'] != 'a:0:{}' || !empty($item['class_id']) ? 1 : 0;
            $item["student_net"] = $this->RClassAccess->getStudentClassNet($item['id'], $item['student_id']);

            $item["teacher_net"] = $this->RClassAccess->getTeacherClassNet($item['id'], $item['teacher_id']);

            $stuStatus = $this->RClassAccess->getStudentClassStatus($item['id'], $item['student_id']);

            $tecStatus = $this->RClassAccess->getTeacherClassStatus($item['id'], $item['student_id']);

            $item["student_status"] = $stuStatus["name"];
            $item["teacher_status"] = $tecStatus["name"];

            $item["student_class"] = "";

            if ($stuStatus["id"] == 1) {
                $item["student_class"] = "yellow";
            } else if ($stuStatus["id"] == 3) {
                $item["student_class"] = "green";
            } else {
                $item["student_class"] = "red";
            }

            $item["teacher_class"] = "";

            if ($tecStatus["id"] == 1) {
                $item["teacher_class"] = "yellow";
            } else if ($tecStatus["id"] == 3) {
                $item["teacher_class"] = "green";
            } else {
                $item["teacher_class"] = "red";
            }

            $classType = $item["time_end"] - $item["time_class"];

            if ($classType == 1500) {
                $item["classType"] = "[25min]";
            } else if ($classType == 2700) {
                $item["classType"] = "[45min]";
            } else {
                $item["classType"] = "[50min]";
            }

            if ($stuStatus["id"] == 3 && $tecStatus["id"] == 3) {
                $item["bg"] = "greenbg";
            } elseif ($stuStatus["id"] == 1 || $tecStatus["id"] == 1) {
                $item["bg"] = "yellowbg";
            } else {
                $item["bg"] = "";
            }
        }
        return $list;
    }

    public function getTodoList($time)
    {
        $timeDay = empty($time) ? strtotime(date('Y-m-d', time())) : strtotime($time);
        $list = $this->RStudentAccess->getTodoList($timeDay);

        foreach ($list as &$v) {
            if ($v['purchase'] > 0) {
                $v['info'] = "<span style='color:#7DC67D'>已付费</span>";
            } elseif (!empty($v['ex_info'])) {
                $v['info'] = "<span style='color:#F0AD4E'>已体验未付费</span>";
            } else {
                $v['info'] = "<span style='color:#E17572'>未付费未体验</span>";
            }
        }
        return empty($list) ? [] : $list;
    }


    /**
     * 搜索，剩余数量充当条件
     * @User：王锴
     * @Time: 16/12/13 21:38
     */
    public function getSelfPayUserSql($keyword, $type, $datanum, $maxnum = '')
    {
        $bodySql = ' ';
        if (!empty($keyword)) {
            $bodySql .= " AND (u.nick like '%{$keyword}%' OR u.mobile LIKE '%$keyword%') ";
        }

        if (!empty($datanum)) {
            $bodySql .= " AND cl.ac_amount >= '{$datanum}' AND cl.ac_amount <= '{$maxnum}'  ";
        }
        return $bodySql;
    }


    /**
     *获得搜有的未付费用户
     * @author  王可
     */
    public function getNotPayAllUsers($kefuid, $num, $indention, $area, $exclass, $beforekeyword, $keyword)
    {
        $indention = $indention == '' ? 99 : $indention;
        $area = $area == '' ? 99 : $area;
        $ex_class = $ex_class == '' ? 0 : $ex_class;
        $beforekeyword = $beforekeyword == '' ? 0 : $beforekeyword;

        $list = $this->RStudentAccess->getNotPayAllUsers($kefuid, $num, $indention, $area, $exclass, $beforekeyword, $keyword);

        foreach ($list as &$row) {
            $row['age'] = empty($row['age']) ? '未设置'
                : $row['age'];

            $row['area'] = empty($row['area']) ? '无'
                : $row['area'] . '类地区';

            $row['level'] = empty($row['level']) ? '未设置'
                : $row['level'];

            $row['accessToken'] = empty($row['accessToken']) ? '没激活' : '激活';

            if (!empty($row['channelname'])) {
                $row['channel_type'] = '主课';
                $row['qudao'] = $row['channelname'];
            } elseif (!empty($row['channelname_2'])) {
                $row['qudao'] = $row['channelname_2'];
                if ($row['channel_type'] == 2) {
                    $row['channel_type'] = '家长';
                } elseif ($row['channel_type'] == 5) {
                    $row['channel_type'] = '活动';
                } else {
                    $row['channel_type'] = '其他';
                }
            } else {
                $row['channel_type'] = '无';
                $row['qudao'] = '无';
            }

            switch ($row['intention']) {
                case 0:
                    $row['intention'] = '未联系';
                    break;
                case 1:
                    $row['intention'] = '无意向';
                    break;
                case 2:
                    $row['intention'] = '有意向';
                    break;
                case 3:
                    $row['intention'] = '高意向';
                    break;
            }

            //最后一次回访记录
            $row['end_visit_time'] = empty($row['end_visit_time']) ? '无回访记录' : date('Y-m-d H:i:s', $row['end_visit_time']);
        }

        return $list ? $list : [];
    }

    /**
     * 未付费用户的条数
     * @author  王可
     * */
    public function getNotPayALlUsersCount($kefuid, $indention, $area, $exclass, $beforekeyword, $keyword)
    {
        $indention = $indention == '' ? 99 : $indention;
        $area = $area == '' ? 99 : $area;
        $exclass = $exclass == '' ? 0 : $exclass;
        $beforekeyword = $beforekeyword == '' ? 0 : $beforekeyword;

        $count = $this->RStudentAccess->getNotPayAllUsersCount($kefuid, $indention, $area, $exclass, $beforekeyword, $keyword);

        return $count;
    }

    /**
     * 一条未付费用户的详细信息
     * @author 王可
     * */
    public function getNotPayUserDetailInfo($userId)
    {
        return $this->RStudentAccess->getNotPayUserDetailInfo($userId);
    }

    /**
     * 回访记录条数处理
     * @author 王可
     * */
    public function getNotPayUserVisitListCount($studentid)
    {
        return $this->RStudentAccess->getNotPayUserVisitListCount($studentid);
    }

    public function countPayToClassUser($keyword)
    {
        $count = $this->RStudentAccess->countPayToClassUser($keyword);

        return $count;
    }

    public function getPayToClassUserList($num, $keyword)
    {
        $list = $this->RStudentAccess->getPayToClassUserList($num, $keyword);

        $week = ['日', '一', '二', '三', '四', '五', '六'];

        if (!empty($list)) {
            foreach ($list as &$row) {
                if (!empty($row['channelname'])) {
                    $row['channel_type'] = '主课';
                    $row['qudao'] = $row['channelname'];
                } elseif (!empty($row['channelname_2'])) {
                    $row['qudao'] = $row['channelname_2'];
                    if ($row['channel_type'] == 2) {
                        $row['channel_type'] = '家长';
                    } elseif ($row['channel_type'] == 5) {
                        $row['channel_type'] = '活动';
                    } else {
                        $row['channel_type'] = '其他';
                    }
                } else {
                    $row['channel_type'] = '无';
                    $row['qudao'] = '无';
                }

                $timepay = $row['time_pay'];
                $row['time_pay'] = "周" . $week[date('w', $timepay)] . " " . date('Y-m-d H:i', $timepay);

                $beforepay = ceil((($timepay - time()) / (3600 * 24)) * -1);
                if ($beforepay == '-0') {
                    $beforepay = 0;
                }
                $row['before_pay'] = "距今：" . $beforepay . "天";

                $timeclass = $row['time_class'];
                $row['time_class'] = empty($timeclass) ? '无' : "周" . $week[date('w', $timeclass)] . " " . date('Y-m-d H:i', $timeclass);

                $row['have_image'] = !empty($row['course_info']) || !empty($row['ci_class_id']) ? 1 : 0;
            }
        }
        return empty($list) ? [] : $list;
    }

    public function getUserIntentionInAddVisit($studentid)
    {
        $intenttion = $this->RStudentAccess->getUserIntentionInAddVisit($studentid);
        return $intenttion['intention'];
    }

    public function getBuyInfoNew($studentid)
    {
        $ex = $this->RClassAccess->getBuyClassRoomInfo($studentid);
        $bill = $this->RClassAccess->getBuyClassEditHistoryInfo($studentid);
        $leftInfo = $this->RClassAccess->getBuyClassLeftInfo($studentid);

        return ['ex' => $ex,
            'bill' => $bill,
            'leftInfo' => $leftInfo
        ];
    }

    /**
     * 客服搜索
     * @param $keyword
     * @return $array
     */
    public function getKefuSearch($keyword)
    {
        return $this->RStudentAccess->getUserAccountByKeyword($keyword);
    }


    //备注页面
    public function getStudentMarkPage($studentid)
    {
        $list['student'] = $this->RStudentAccess->getStudentRemark($studentid);

        $list['teacherList'] = $this->RStudentAccess->getTeacherName();

        array_unshift($list['teacherList'], array('id' => 0, 'nick' => ''));

        return $list;
    }


    //修改备注信息
    public function editStudentMark($userId, $timeDay, $request)
    {
        return $this->WStudentAccess->editStudentMark($userId, $request);
    }


    /**
     * 获取权限是新签和复购的客服列表
     */
    public function getKefuWithPower()
    {
        $kefuList = $this->RStudentAccess->getKefuWithPower();

        array_unshift($kefuList, ['id' => 0, 'nickname' => '所有']);

        return json_encode(array('error' => '', 'data' => $kefuList));
    }

    public function addCourseKefuInKefuManagement($logid, $userName, $nick, $email, $type, $telephonename, $telephonepwd)
    {

        return $this->accountService->addCourseKefuInKefuManagement($logid, $userName, $nick, $email, $type, $telephonename, $telephonepwd);
    }


    public function addEmployeManagement($request)
    {
        return $this->accountService->addEmployeManagement($request);
    }

    public function getChannelTodoList($start, $end)
    {
        $dayEnd = strtotime(date('Y/m/d', time())) + 3600 * 24;
        $list = $this->RAccountAccess->getChannelTodoList(0, $dayEnd);

        foreach ($list as &$v) {
            $v['created_at'] = date('Y-m-d H:i:s', $v['created_at']);
        }

        return ['error' => 0, 'data' => ['list' => $list]];
    }


    public function getShowTodolistCount()
    {
        $dayStart = strtotime(date('Y/m/d', time()));
        $dayEnd = $dayStart + 3600 * 24;
        $count = $this->RAccountAccess->getShowTodolistCount($dayEnd);
        return json_encode(['error' => '', 'data' => ['count' => $count]]);
    }

    public function updateCourseKefuInKefuManagement($request)
    {
        return $this->accountService->updateCourseKefuInKefuManagement($request);
    }

    public function countSalesKefu($keyword, $timestart, $timeend)
    {
        return $this->accountService->countSalesKefu($keyword, $timestart, $timeend);
    }

    public function countEmploye($keyword = '', $status = 0)
    {
        return $this->accountService->countEmploye($keyword, $status);
    }

    public function getSalesKefuList($keyword, $timestart, $timeend, $num)
    {
        return $this->accountService->getSalesKefuList($keyword, $timestart, $timeend, $num);
    }

    public function getEmployeList($keyword = '', $status = 0, $num)
    {
        return $this->accountService->getEmployeList($keyword, $status, $num);
    }

    public function deleteKefu($logid, $kefuid, $deltype)
    {
        return $this->accountService->deleteKefu($logid, $kefuid, $deltype);
    }


    public function openEmploye($kefuid)
    {
        return $this->accountService->openEmploye($kefuid);
    }

    public function getAllUsersByKefuId($uid, $keyword, $offset, $limit)
    {
        $returnData = array(
            'error' => 0,
            'data' => []
        );
        $data = $this->RStudentAccess->getAllUsersByKefuId($uid, $keyword, $offset, $limit);
        foreach ($data as &$row) {
            $row['channel_name'] = $this->getChannelNameById($row['sales_id'], $row['channel_id']);
            $row['user_type'] = $this->getUserType($row['open_id']);
        }
        $returnData['data'] = $data;
        return $returnData;
    }


    private function getChannelNameById($salesId, $channelId)
    {
        if (!empty($salesId)) {
            $name = $this->RChannelAccess->getSalesChannelNameById($salesId);
        } elseif (!empty($channelId)) {
            $channelInfo = $this->RChannelAccess->getUserChannelInfoById($channelId);
            if ($channelInfo['type'] == 5) {
                $name = '[活动] ' . $channelInfo['name'];
            } elseif ($channelInfo['type'] == 2) {
                //$userId = $this->RStudentAccess->getUserIdByChannelIdSelf($channelInfo['id']);
                $name = '[家长] ' . $channelInfo['name'];
            } else {
                $name = '[其他] ' . $channelInfo['name'];
            }
        } else {
            $name = '无';
        }
        return $name;
    }

    private function getUserType($openId)
    {
        $userId = $this->RStudentAccess->getUidByOpenid($openId);
        if (!empty($userId)) {
            $isDanger = $this->RStudentAccess->getStudentIsDanger($userId);
            if (empty($isDanger)) {
                $isBuy = $this->RClassAccess->getStudentIsBuy($userId);
                return empty($isBuy) ? 2 : 3;
            } else {
                return 4;
            }
        } else {
            return 1;
        }
    }

    public function getUseridByTelephone($telephone)
    {
        return $this->RStudentAccess->getUseridByTelephone($telephone);
    }

    public function getAccountInfoByKefuId($kefuid)
    {
        return $this->accountService->getAccountInfoByKefuId($kefuid);
    }


    public function countNewUserAgainAllotNotFollow($btn, $keyword, $start, $end)
    {
        $kefuid = Yii::$app->user->identity->id();
        $start = strtotime($start);
        $end = strtotime($end);

        //默认昨天和前天
        if (empty($start) && empty($end)) {
            $today = date('Y-m-d', time());
            $start = strtotime("$today -2 day ");
            $end = strtotime($today);
        }

        if ($btn == 0) {
            $count = $this->RStudentAccess->countAgainAllotNotFollow($keyword, $start, $end, $kefuid);
        } elseif ($btn == 1) {
            $count = $this->RStudentAccess->countAgainAllotNotFollowExperienceClassBefore($keyword, $start, $end, $kefuid);
        } elseif ($btn == 2) {
            $count = $this->RStudentAccess->countAgainAllotNotFollowExperienceClassLater($keyword, $start, $end, $kefuid);
        }

        return $count;
    }


    public function getPurChaseUserInfo($keyword)
    {
        $data = $this->RAccountAccess->getPurChaseUserInfo($keyword);
        if (empty($data)) {
            return ['error' => '该昵称下复购顾问暂无!', 'data' => ''];
        }
        $data = array(
            'sales' => $data
        );

        return ['error' => 0, 'data' => $data];
    }

    public function getNewAccountInfo()
    {
        $data = $this->RAccountAccess->getNewAccountList();

        if (empty($data)) {
            return ['error' => '该昵称下新签顾问暂无!', 'data' => ''];
        }

        $data = array(
            'sales' => $data
        );

        return ['error' => 0, 'data' => $data];
    }

    public function getPromotionEffectPage($start, $end, $uid)
    {
        $start = strtotime($start);
        $end = strtotime($end) + 86400;

        $count = $this->RChannelAccess->getPromotionEffectPage($start, $end, $uid);

        $data = array(
            'count' => $count
        );

        return ['error' => 0, 'data' => $data];
    }

    public function getPromotionEffectList($start, $end, $num, $uid)
    {
        $start = strtotime($start);
        $end = strtotime($end) + 86400;
        $list = $this->RChannelAccess->getPromotionEffectList($start, $end, $num, $uid);
        $sum = array(
            'chat_amount' => 0,
            'worth_chat_amount' => 0,
            'reg_amount' => 0,
            'ex_amount' => 0,
            'buy_amount' => 0,
            'buy_money' => 0,
            'distribution_buy_money' => 0
        );
        foreach ($list as &$v) {
            $v['time_created'] = date('Y-m-d', $v['time_created']);
            $sum['chat_amount'] = $sum['chat_amount'] + $v['chat_amount'];
            $sum['worth_chat_amount'] = $sum['worth_chat_amount'] + $v['worth_chat_amount'];
            $sum['reg_amount'] = $sum['reg_amount'] + $v['reg_amount'];
            $sum['ex_amount'] = $sum['ex_amount'] + $v['ex_amount'];
            $sum['buy_amount'] = $sum['buy_amount'] + $v['buy_amount'];
            $sum['buy_money'] = $sum['buy_money'] + $v['buy_money'];
            $sum['distribution_buy_money'] = $sum['distribution_buy_money'] + $v['distribution_buy_money'];
        }

        if ($start == $end - 86400) {
            $sum = '';
        }
        $data = array(
            'list' => $list,
            'sum' => $sum
        );
        return ['error' => 0, 'data' => $data];
    }

    public function getUserIntroduceCount($type, $keyword, $kefuId, $start, $end, $kefu, $isCheck)
    {
        $accountid = $this->isAdminUser($kefuId);
        $type = $this->getIntroduceType($type);
        if (!empty($keyword)) {
            $channel_id = $this->RChannelAccess->getUserChannelId($keyword);
            if (!empty($channel_id)) {
                $keyword = implode(',', $channel_id);
            } else {
                $keyword = -1;
            }
        }
        if (!empty($start) && !empty($end)) {
            $start = strtotime($start);
            $end = strtotime($end);
        }
        $count = $this->RStudentAccess->getUserIntroduceCount($type, $keyword, $accountid, $start, $end, $kefu, $isCheck);
        $data = array(
            'count' => $count,
        );

        return ['error' => 0, 'data' => $data];
    }

    public function getUserIntroduceList($type, $num, $keyword, $kefuId, $start, $end, $kefu, $isCheck)
    {
        $accountid = $this->isAdminUser($kefuId);
        $type = $this->getIntroduceType($type);

        if (!empty($keyword)) {
            $channel_id = $this->RChannelAccess->getUserChannelId($keyword);
            if (!empty($channel_id)) {
                $keyword = implode(',', $channel_id);
            } else {
                $keyword = -1;
            }
        }
        if (!empty($start) && !empty($end)) {
            $start = strtotime($start);
            $end = strtotime($end);
        }

        $list = $this->RStudentAccess->getUserIntroduceList($type, $keyword, $accountid, $num, $start, $end, $kefu, $isCheck);
        foreach ($list as &$item) {
            $item['kefu_name'] = $this->RAccountAccess->getNewSignKefuNick($item['kefu_id']);
            $item['time_class'] = empty($item['time_class']) ? '暂无课程' : date('Y-m-d H:i', $item['time_class']);
            $item['age'] = empty($item['age']) ? '未知' : $item['age'] . '岁';
            $item['time_end'] = empty($item['time_end']) ? '' : date('H:i', $item['time_end']);
            $item['time_created'] = empty($item['time_created']) ? '暂无' : date('Y-m-d H:i:s', $item['time_created']);
            //最后一次回访记录
            $item['end_visit_time'] = empty($item['end_visit_time']) ? '无回访记录' : date('Y-m-d H:i:s', $item['end_visit_time']);
            $imageCount = $this->RClassAccess->getClassImageCount($item['class_id']);
            $item['image'] = empty($imageCount) ? 0 : 1;
            $item['purchase'] = empty($item['purchase']) ? '无买单记录' : '有买单记录';
        }

        $data = array(
            'list' => $list,
        );

        return ['error' => 0, 'data' => $data];
    }

    private function isAdminUser($kefuId)
    {
        $role = $this->RAccountAccess->getKefuRoleByKefuid($kefuId);

        return $role == 2 ? 0 : $kefuId;
    }


    private function getIntroduceType($type)
    {
        if (!is_numeric($type)) {
            return ['error' => '非法字符', 'data' => ''];
        }

        switch ($type) {
            case 1:
                $type = ' ';
                break;
            case 2:
                $type = ' AND  time_class IS NULL';
                break;
            case 3:
                $type = ' AND c.status = 0';
                break;
            case 4:
                $type = ' AND c.status = 1';
                break;
        }
        return $type;
    }
}
