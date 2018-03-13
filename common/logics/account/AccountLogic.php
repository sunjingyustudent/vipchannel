<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/10
 * Time: 下午7:49
 */
namespace common\logics\account;

use common\models\music\KefuTimetableWeike;
use Yii;
use yii\base\Object;
use common\services\LogService;
use common\models\LoginForm;
use common\services\ErrorService;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;
use yii\db\Exception;

class AccountLogic extends Object implements IAccount
{

    /** @var  \common\sources\read\account\AccountAccess $RAccountAccess */
    private $RAccountAccess;
    /** @var  \common\sources\write\account\AccountAccess $RAccountAccess */
    private $WAccountAccess;
    /** @var  \common\sources\read\student\StudentAccess $RStudentAccess */
    private $RStudentAccess;
    /** @var  \common\sources\write\student\StudentAccess $WStudentAccess */
    private $WStudentAccess;


    public function init()
    {
        $this->RAccountAccess = Yii::$container->get('RAccountAccess');
        $this->WAccountAccess = Yii::$container->get('WAccountAccess');
        $this->RStudentAccess = Yii::$container->get('RStudentAccess');
        $this->WStudentAccess = Yii::$container->get('WStudentAccess');
        parent::init();
    }

    public function getKefuList()
    {
        return $this->RAccountAccess->getKefuList();
    }

    public function getNewSignKefuList()
    {
        return $this->RAccountAccess->getNewSignKefuList();
    }

    public function getPublicUserKefuInfo()
    {
        $data['kufu_newsign'] = $this->RAccountAccess->getNewSignKefuList();
        $kefuList_2 = $this->RAccountAccess->getReKefuList();
        array_unshift($kefuList_2, ['id' => -1, 'nickname' => '无顾问']);
        array_unshift($kefuList_2, ['id' => -2, 'nickname' => '有顾问']);
        array_unshift($kefuList_2, ['id' => 0, 'nickname' => '所有']);
        $data['kufu_re'] = $kefuList_2;
        return $data;
    }


    public function getAllUserKefuInfo()
    {
        $kefuList2 = $this->RAccountAccess->getAllUserKefuInfo();
        $data['kufu_binding'] = $kefuList2;
        array_unshift($kefuList2, ['id' => -1, 'nickname' => '未绑定']);
        array_unshift($kefuList2, ['id' => 0, 'nickname' => '所有']);

        $data['kufu_select'] = $kefuList2;

        return $data;
    }


    /**
     * @param $userName
     * @param $nick
     * @param $email
     * @param $type
     * @return mixed
     * create by wangke
     * 销售管理中 添加一个客服
     */
    public function addCourseKefuInKefuManagement($logid, $userName, $nick, $email, $type, $telephonename, $telephonpwd)
    {
        $account_flag = $this->WAccountAccess->addCourseKefuInKefuManagement($userName, $nick, $email, $type, $telephonename, $telephonpwd);

        if ($account_flag) {
            //LogService::OutputLog($logid, 'Add','','添加销售');
            return json_encode(array('error' => ''));
        }

        return json_encode(array('error' => '操作失败,请联系管理员'));
    }

    public function validateUniqueUsername($userName)
    {
        $data = $this->RAccountAccess->validateUniqueUsername($userName);

        if (empty($data)) {
            return 0;
        }

        return 1;
    }

    public function validateUniqueEmail($email)
    {
        $data = $this->RAccountAccess->validateUniqueEmail($email);
        if (empty($data)) {
            return 0;
        }

        return 1;
    }


    public function addEmployeManagement($request)
    {
        $request['card'] = strstr($request['card'], 'vipemployemanage');
        $request['poster'] = strstr($request['poster'], 'vipemployemanage');
        $request['qrcode'] = strstr($request['qrcode'], 'vipemployemanage');
        $request['banner'] = strstr($request['banner'], 'vipemployemanage');

        $data_username = $this->RAccountAccess->validateUniqueUsername($request['username']);
        if (!empty($data_username)) {
            return json_encode(array('error' => '账号已经存在！'));
        }

        $data_email = $this->RAccountAccess->validateUniqueEmail($request['email']);
        if (!empty($data_email)) {
            return json_encode(array('error' => '邮箱已经存在！'));
        }

        $account_flag = $this->WAccountAccess->addEmployeManagement($request);
        if ($account_flag) {
            return json_encode(array('error' => ''));
        }
        return json_encode(array('error' => '操作失败,请联系管理员'));
    }

    public function updateCourseKefuInKefuManagement($request)
    {
        $request['card'] = strstr($request['card'], 'vipemployemanage');
        $request['poster'] = strstr($request['poster'], 'vipemployemanage');
        $request['qrcode'] = strstr($request['qrcode'], 'vipemployemanage');
        $request['banner'] = strstr($request['banner'], 'vipemployemanage');

        $data_email = $this->RAccountAccess->validateUpdataUniqueEmail($request['kefu_id'], $request['email']);
        if (!empty($data_email)) {
            return json_encode(array('error' => '邮箱已经存在！'));
        }

        $account_update_flag = $this->WAccountAccess->updateCourseKefuInKefuManagement($request);
        if ($account_update_flag >= 0) {
            return json_encode(array('error' => ''));
        }
        return json_encode(array('error' => '操作失败,请联系管理员'));
    }

    public function countSalesKefu($keyword, $timestart, $timeend)
    {
        return $this->RAccountAccess->countSalesKefu($keyword, $timestart, $timeend);
    }


    public function countEmploye($keyword, $status)
    {
        return $this->RAccountAccess->countEmploye($keyword, $status);
    }

    public function getSalesKefuList($keyword, $timestart, $timeend, $num)
    {
        return $this->RAccountAccess->getSalesKefuList($keyword, $timestart, $timeend, $num);
    }

    public function getEmployeList($keyword, $status, $num)
    {
        return $this->RAccountAccess->getEmployeList($keyword, $status, $num);
    }

    public function deleteKefu($logid, $kefuid, $deltype)
    {

        $tr = Yii::$app->db->beginTransaction();

        try {
            $this->WAccountAccess->deleteKefu($kefuid);
            if ($deltype == 1) {
                $this->WStudentAccess->unbindKefuByKefuId($kefuid);
            }

            $tr->commit();
            return json_encode(array('error' => '', 'data' => array('kefu_id' => $kefuid)));
        } catch (Exception $e) {
            $tr->rollBack();
            return json_encode(array('error' => '绑定失败,请联系管理员', 'data' => array('kefu_id' => $kefuid)));
        }
    }

    public function openEmploye($kefuid)
    {

        $flog = $this->WAccountAccess->openEmploye($kefuid);

        if ($flog) {
            return json_encode(array('error' => '', 'data' => []));
        }
        return json_encode(array('error' => '绑定失败,请联系管理员', 'data' => []));
    }

    /**
     * 登陆post
     */
    public function doLogon($req)
    {
        $model = new LoginForm();
        $model->username = $req->post("username");
        $model->password = $req->post("passwd");
        $model->type = "crm";

        if ($model->login()) {
            return json_encode(['recode' => 1,
                'telephone_system_name' => Yii::$app->user->identity->telephone_system_name,
                'telephone_system_pwd' => Yii::$app->user->identity->telephone_system_pwd]);
        }

        return json_encode(['recode' => 0]);
    }

    public function doChannelLogon($req)
    {
        $model = new LoginForm();
        $model->username = $req->post("username");
        $model->password = $req->post("passwd");
        $model->type = "channel";

        if ($model->login()) {
            return json_encode(['recode' => 1,
                'telephone_system_name' => Yii::$app->user->identity->telephone_system_name,
                'telephone_system_pwd' => Yii::$app->user->identity->telephone_system_pwd]);
        }
        return json_encode(['recode' => 0]);
    }



    /**
     * 更换头像
     */
    public function doChangeHead($logid)
    {
        $file = $_FILES;

        $userId = Yii::$app->user->identity->id;
        $accessKey = Yii::$app->params['qiniuAccessKey'];
        $secretKey = Yii::$app->params['qiniuSecretKey'];

        if ($file['file']["error"] > 0) {
            return 0;
        } else {
            $fileKey = md5($userId . '_' . microtime() . '_' . rand(10, 99));

            // 构建鉴权对象
            $auth = new Auth($accessKey, $secretKey);

            // 要上传的空间
            $bucket = Yii::$app->params['pnl_static_bucket'];

            // 生成上传 Token
            $token = $auth->uploadToken($bucket);

            //$filePath = $file["tmp_name"];
            $filePath = $file['file']['tmp_name'];

            // 上传到七牛后保存的文件名
            $key = 'user/head/' . $fileKey;

            // 构建 UploadManager 对象
            $uploadMgr = new UploadManager();

            // 调用 UploadManager 的 putFile 方法进行文件的上传
            list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);

            if (1 == 2) {
                return $ret;
            }
            if ($err !== null) {
                return 0;
            } else {
                //$user = $this->RStudentAccess->getKefuInfo($userId);

                $head = Yii::$app->params['pnl_static_path'] . $key;
                if ($this->WStudentAccess->updateUserAccountHead($userId, $head)) {
                    LogService::OutputLog($logid, 'update', '', '更换头像');
                    return json_encode(array('src' => Yii::$app->params['pnl_static_path'] . $key), JSON_UNESCAPED_SLASHES);
                }
            }
        }
    }

    public function getAccountInfoByKefuId($kefuid)
    {
        return $this->RAccountAccess->getAccountInfoByKefuId($kefuid);
    }

    public function editKefuReceptionGrowth($kefuId)
    {
        $workday = strtotime('00:00:00');

        $maxnumber = $this->RAccountAccess->getMaxNumber($workday);
        if (empty($maxnumber)) {
            $maxnumber = 1;
        }

        $this->WAccountAccess->doAddKefuReceptionGrowth($kefuId, $workday, $maxnumber);
    }

    public function getEmployeWorkTime($kefuid, $time, $type)
    {
        if ($time == '') {
            $timeDay = strtotime(date('Y-m-d', time()));
        } else {
            $timeDay = strtotime($time);
        }

        $week = date('w', $timeDay);
        $week = $week == 0 ? 7 : $week;

        //查询日课表的基数
        $dayTimeBit = $this->RAccountAccess->getEmployeDayTime($kefuid, $timeDay);

        //查询周课表的  time_bit就是2进制排课
        $fixedTimeRow = $this->RAccountAccess->getEmployeWeekTime($kefuid, $week);

        if (!empty($fixedTimeRow) && $fixedTimeRow['time_execute'] <= $timeDay) {
            $dayTimeBit = empty($dayTimeBit) ? $fixedTimeRow['time_bit'] : $dayTimeBit;
        } else {
            $dayTimeBit = empty($dayTimeBit) ? 281474976710656 : $dayTimeBit;
        }

        $flag = false;
        $each = array();
        $timeList = array();
        $num = 1;

        for ($i = 1; $i <= 49; $i++) {
            if (($dayTimeBit & $num) == 0 && !$flag) {
                $flag = true;
                $tmp = $i / 2;
                $tmp = is_int($tmp) ? ($tmp - 1) . ":30" : floor($tmp) . ":00";
                $each['start'] = $tmp;
            } elseif (($dayTimeBit & $num) == $num && $flag) {
                $flag = false;
                $tmp = $i / 2;
                $tmp = is_int($tmp) ? ($tmp - 1) . ":30" : floor($tmp) . ":00";
                $each['end'] = $tmp;
                $timeList[] = $each;
                $each = [];
            }
            $num = $num << 1;
        }

        return $timeList;
    }


    public function getEmployeWeekTable($kefuid)
    {
        $fixedTimeBitList = $this->RAccountAccess->getFixedTimeBitList($kefuid);
        $executeTime = $this->RAccountAccess->getExecuteTime($kefuid);

        $weekModel = [1, 2, 3, 4, 5, 6, 7];
        $data = [];
        $data['time_execute'] = empty($executeTime) ? '' : $executeTime;

        foreach ($weekModel as $value) {
            $each = [];
            $flag = false;
            if (!empty($fixedTimeBitList)) {
                foreach ($fixedTimeBitList as $row) {
                    if ($row['week'] == $value) {
                        $each['week'] = $row['week'];
                        $each['time_bit'] = $row['time_bit'];
                        $data['bit_info'][] = $each;
                        $flag = true;
                    }
                }
            }
            if (!$flag) {
                $each['week'] = $value;
                $each['time_bit'] = 281474976710656;
                $data['bit_info'][] = $each;
            }
        }

        foreach ($data['bit_info'] as $key => $item) {
            $flag = false;
            $each = array();
            $num = 1;
            for ($i = 1; $i <= 49; $i++) {
                if (($item['time_bit'] & $num) == 0 && !$flag) {
                    $flag = true;
                    $tmp = $i / 2;
                    $tmp = is_int($tmp) ? ($tmp - 1) . ":30" : floor($tmp) . ":00";
                    $each['start'] = $tmp;
                } elseif (($item['time_bit'] & $num) == $num && $flag) {
                    $flag = false;
                    $tmp = $i / 2;
                    $tmp = is_int($tmp) ? ($tmp - 1) . ":30" : floor($tmp) . ":00";
                    $each['end'] = $tmp;
                    $data['bit_info'][$key]['time_list'][] = $each;
                    $each = [];
                }
                $num = $num << 1;
            }
        }

        return $data;
    }

    public function addEmployeWorkTime($request)
    {

        $kefu_id = $request['kefu_id'];
        $time = $request['time'];
        $timeDay = strtotime($time);

        if (isset($request['fix_info'])) {
            $timeList = $request['fix_info'];
        } else {
            $timeList = array();
        }
        $timeBit = 562949953421311;

        foreach ($timeList as $row) {
//            print_r($row);exit;
            $startArr = explode(':', $row['time_start']);
            $endArr = explode(':', $row['time_end']);

            $check_time = $this->checkTime($startArr, $endArr);

            if ($check_time == true) {
                return 0;
            }

            $startPos = $startArr[1] == '30'
                ? $startArr[0] * 2 + 2
                : $startArr[0] * 2 + 1;

            $endPos = $endArr[1] == '30'
                ? $endArr[0] * 2 + 1
                : $endArr[0] * 2;

            for ($i = $endPos; $i >= $startPos; $i--) {
                $endBit = pow(2, $i - 1);

                $timeBit = $timeBit & (~$endBit);
            }
        }

//        $timeModel = new KefuTimetableWeike();
//        $timeModel->addKefuTimetable($kefu_id,$timeDay,$timeBit);

        $this->WAccountAccess->addKefuTimetable($kefu_id, $timeDay, $timeBit);

        return 1;
    }

    private function checkTime($startArr, $endArr)
    {
        if (preg_match('/^[0-9]{1,2}$/', $startArr[0]) && preg_match('/^[0-9]{1,2}$/', $startArr[1]) && preg_match('/^[0-9]{1,2}$/', $endArr[0]) && preg_match('/^[0-9]{1,2}$/', $endArr[1])) {
            if ($startArr[1] != "00" && $startArr[1] != "30") {
                return true;
            }
            if ($startArr[0] < 0 || $startArr[0] > 24) {
                return true;
            }
            if ($endArr[1] != '00' && $endArr[1] != '30') {
                return true;
            }
            if ($endArr[0] < 0 || $endArr[0] > 24) {
                return true;
            }
            if ($startArr[0] > $endArr[0]) {
                return true;
            }
            if (($startArr[0] == $endArr[0]) && ($startArr[1] >= $endArr[1])) {
                return true;
            }
        } else {
            return true;
        }
    }

    public function addEmployeWeekTable($request)
    {

        $kefuId = $request['kefu_id'];
        $timeexecute = $request['time'];
        $timeExecute = strtotime($timeexecute);
        $timeList = $request['fix_info'];

        foreach ($timeList as $row) {
            $timeBit = 562949953421311;

            foreach ($row as $item) {
                $week = explode("_", $item['week'])[1];
                $startArr = explode(':', $item['time_start']);
                $endArr = explode(':', $item['time_end']);
                if (preg_match('/^[0-9]{1,2}$/', $startArr[0]) && preg_match('/^[0-9]{1,2}$/', $startArr[1]) && preg_match('/^[0-9]{1,2}$/', $endArr[0]) && preg_match('/^[0-9]{1,2}$/', $endArr[1])) {
                    if ($startArr[1] != "00" && $startArr[1] != "30") {
                        return 0;
                    }
                    if ($startArr[0] < 0 || $startArr[0] > 24) {
                        return 0;
                    }
                    if ($endArr[1] != '00' && $endArr[1] != '30') {
                        return 0;
                    }
                    if ($endArr[0] < 0 || $endArr[0] > 24) {
                        return 0;
                    }
                    if ($startArr[0] > $endArr[0]) {
                        return 0;
                    }
                    if (($startArr[0] == $endArr[0]) && ($startArr[1] > $endArr[1])) {
                        return 0;
                    }
                    if (($startArr[0] == $endArr[0]) && ($startArr[1] == $endArr[1]) && (($startArr[1] != '00') || ($startArr[0] != '00'))) {
                        return 0;
                    }
                } else {
                    return 0;
                }
                $startPos = $startArr[1] == '30'
                    ? $startArr[0] * 2 + 2
                    : $startArr[0] * 2 + 1;

                $endPos = $endArr[1] == '30'
                    ? $endArr[0] * 2 + 1
                    : $endArr[0] * 2;

                for ($i = $endPos; $i >= $startPos; $i--) {
                    $endBit = pow(2, $i - 1);
                    $timeBit = $timeBit & (~$endBit);
                }
            }

            $this->WAccountAccess->addKfuFixedTime($kefuId, $week, $timeBit, $timeExecute);
        }
        return 1;
    }

    public function getUserAccountDetailById($kefuId)
    {
        $result = $this->RAccountAccess->getUserAccountDetailById($kefuId);
        if ($result) {
            $result['card'] = $result['card'] ? Yii::$app->params['vip_static_path'] . $result['card'] : '';
            $result['poster'] = $result['poster'] ? Yii::$app->params['vip_static_path'] . $result['poster'] : '';
            $result['qrcode'] = $result['qrcode'] ? Yii::$app->params['vip_static_path'] . $result['qrcode'] : '';
            $result['banner'] = $result['banner'] ? Yii::$app->params['vip_static_path'] . $result['banner'] : '';
        }
        return $result;
    }

    public function getExClassReportKefuInfo()
    {
        $data = $this->RAccountAccess->getExClassReportKefuInfo();

        array_unshift($data, ['id' => 0, 'nickname' => '全部渠道经理']);
        return $data;
    }
}
