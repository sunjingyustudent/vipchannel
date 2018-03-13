<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/9
 * Time: 下午6:09
 */
namespace common\logics\sale;

use Yii;
use yii\base\Object;

interface IManage {
    /**
     * @return mixed
     * create by wangke
     * 显示客服列表
     */
    public function getKefuList();

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
     * 为复购组分配复购客服
     */
    public function distributeUserAccountOne($logid, $userId,$kefuId);

    /**
     * @return mixed
     * create by wangke
     * 获取新签客服列表
     */
    public function getNewSignKefuList();

    /**
     * @param $introduce
     * @param $start
     * @param $end
     * @return mixed
     * create by wangke
     * 管理视角  新用户的条数
     */
    public function countAllotNewUser($introduce, $start, $end);

    /**
     * @param $introduce
     * @param $start
     * @param $end
     * @param $num
     * @return mixed
     * create by wangke
     * 管理视角  新用户列表
     */
    public function getAllotNewUserList($introduce, $start, $end, $num);

    /**
     * @param $logid
     * @param $userId
     * @param $kefuId
     * @return mixed
     * create by wangke
     * 管理视角 为新用户分配复购客服
     */
    public function distributeNewUser($logid,$userId,$kefuId);

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
     * @param $logid
     * @param $userId
     * @param $kefuId
     * @return mixed
     * create by wangke
     * 管理视角 未复购再分配 分配新签客服
     */
    public function distributeNotPurchase($logid,$userId,$kefuId);

    /**
     * @param $keyword
     * @param $btn
     * @param $start
     * @param $end
     * @return mixed
     * create by wangke
     * 管理视角 未跟进再分配的条数
     */
    public function countAgainAllotNotFollow($btn, $keyword, $start, $end, $kefu_id);

    /**
     * @param $keyword
     * @param $btn
     * @param $start
     * @param $end
     * @param $num
     * @return mixed
     * create by wangke
     * 管理视角 未跟进再分配的列表信息
     */
    public function getAgainAllotNotFollowList($btn, $keyword, $start, $end, $num, $kefu_id);

    /**
     * @return mixed
     * create by wangke
     * 管理视角 公盘用户 获取需要显示的客服信息
     */
    public function getPublicUserKefuInfo();

    /**
     * @return mixed
     * create by wangke
     * 得到所有的role=5微课客服
     */
    public function getAllUserKefuInfo();


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
    public function countPublicUserPage($type, $kefuId, $area , $keyword , $intention , $time_type, $start , $end);

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
     * 管理视角 公盘用户的列表信息
     */
    public function getPublicUserList($type, $kefuId, $area, $keyword, $intention, $time_type, $start, $end,$num);

    /**
     * @param $userId
     * @param $kefuId
     * @return mixed
     * create by wangke
     * 管理者视角 公盘用户分配客服
     */
    public function distributePublicUserKefu($logid,$userId,$kefuId);

    /**
     * @param $userId
     * @param $kefuId
     * @return mixed
     * create by wangke
     * VIP微课 全部用户 分配客服
     */
    public function distributeAllUserKefu($userId,$kefuId);

    /**
     * 体验课报表的微课客服
     * @return mixed
     * create by wangke
     */
    public function getExClassReportKefuInfo();


}