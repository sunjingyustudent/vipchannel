<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/22
 * Time: 上午9:49
 */
namespace common\logics\classes;

use Yii;
use yii\base\Object;

interface ICourse {
     /**
     * 上传乐谱
     * @param   $request
     * @param   $logid
     * @return  array
     */
    public function doAddMusic($request, $logid);  

    /**
     * 查询学生最近一节课的乐谱
     * @param   $class_id
     * @return  array
     */
    public function getLastMusic($class_id);

    /**
     * 学生自主上传乐谱页面
     * @param   $classId 
     * @param   $logid
     * @return　bool
     */
    public function uploadMusic($class_id);

    /**
     * 学生自主上传乐谱
     * @param   $classId 
     * @param   $logid
     * @return　bool
     */
    public function doUpload($classId, $logid);

    /**
     * 删除乐谱
     * @param  $imageId
     * @param  $logid
     * @return array
     */
    public function doDeleteClassImage($imageId, $logid);

    /**
     * 导出学生自主上传的上次乐谱
     * @param   $class_id
     * @return  array
     */
    public function exportFile($class_id);

    /**
     * 查看乐谱
     * @param    $id
     * @return   array
     */
    public function queryImagepage($id = 0);
}