<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 17/1/3
 * Time: 10:32
 */
namespace common\sources\write\complain;


interface IComplainAccess {

    /**
     * @param $complain_id
     * @param $class_id
     * @return mixed
     * @author xl
     * 关联课程和投诉
     */
    public function relateClass($complain_id,$class_id);

    /**
     * @param $request
     * @param $context
     * @return mixed
     * created by xl
     * 处理家长投诉（修改投诉记录状态）
     */
    public function updateComplainStatus($request);

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 添加投诉
     */
    public function doAddComplain($open_id,$request);

    /**
     * @param $complain_id
     * @param $tag
     * @return mixed
     * @author xl
     * 更新投诉字段tag
     */
    public function updateComplainTag($complain_id,$tag);

    /**
     * @param $complain_id
     * @param $reward_record_id
     * @return mixed
     * @author xl
     * 更新家长投诉处罚ID
     */
    public function updateComplainRewardRecordId($complain_id, $reward_record_id);
}
