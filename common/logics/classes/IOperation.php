<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/9
 * Time: 下午5:16
 */
namespace common\logics\classes;

use Yii;
use yii\base\Object;

interface IOperation {

    /**
     * 执行批量删除学生课程操作
     * @param    $classIds   int
     * @param    $logid       str
     * @return   
     */
    public function batchDeleteClass($classIds, $logid);

    /**
     * 修改时间格式
     * @param   $time
     * @return  int
     */
    public function timeFormat($time);

    /**
     * 添加课程
     * @param $request 
     * @param $logid 
     * @return array  
     */
    public function doAddClass($request, $logid);

    /**
     * 查看空闲老师
     * @param  $request array
     */
    public function getTeacherAvailable($request);
    
    /**
     * 查看空闲老师list
     * @param  $request array
     */
    public function getTeacherAvailableTable($request);

    /**
     * 编辑该课程
     * @param  $request  array
     * @param  $logid    str
     * @return mixed
     */
    public function doEditClass($request, $logid);

    /**
     * 删除错误课程
     * @param $classId
     * @param $logid
     * @return mixed
     */
    public  function  deleteClassFail($classId, $logid);

	/**
     * 删除课程
     * @param $classId
     * @param $logid
     * @return mixed
     */
     public function deleteClass($classId, $logid);


    /**
     * 添加赠送课程
     * @param  $classId
     * @param  $logid
     * @return array
     */
    public function doGiveClass($request, $logid);

    /**
     * @param string $fixInfo
     * @return mixed
     * @author xl
     * 编辑学生固定时间
     */
    public function getDoStudentFixTime($openId, $fixInfo='', $logid);

    /**
     * @param $openId
     * @param string $fixInfo
     * @param $logid
     * @return mixed
     * @author xl
     * 编辑学生固定时间并排课
     */
    public function getDoStudentFixTimeClass($openId, $fixInfo='', $logid);

}