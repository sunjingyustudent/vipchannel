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

interface IVisit
{
    /**
     * @param $userId
     * @return mixed
     * created by hujiyu
     * 根据学生ID获取用户可修改的信息
     */
    public function getStudentVisitInfoById($userId);

    /**
     * @param $student_id
     * @return mixed
     * 根据学生第获取回访总次数
     */
    public function countVisitByStudentId($studentId);

    /**
     * @param $student_id
     * @param $num
     * @return mixed
     * created by hujiyu
     * 获取用户回访纪录列表
     */
    public function getVisitHistoryList($studentId, $num);

    /**
     * @param $student_id
     * @return mixed
     * create by wangke
     * 查询某个学生的投诉列表条数
     */
    public function countComplainPage($studentId);

    /**
     * @param $request
     * @return mixed
     * create by wangke
     * 回访 修改学生信息
     */
    public function addUserArchive($request);

    /**
     * @param $student_id
     * @return mixed
     * create by wangke
     * 回访组件 添加回访的意向下拉框
     */
    public function getUserIntentionInAddVisit($studentId);

    /**
     * @param $request
     * @return mixed
     * create by wangke
     * 保存回访西信息操作
     */
    public function addUserHistory($request);
}
