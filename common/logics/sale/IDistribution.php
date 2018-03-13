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

interface IDistribution {

    /** 自动分配用户给客服
     * @param   $uid    用户ID
     * @return  array
     * create by  wangkai
     */
    public function autoAssignUser($uid);

    /** 自动分配给推广大使渠道的用户
     * @param   $uid    用户ID
     * @return  array
     * create by  wangkai
     */
    public function autoAssignChannelUser($uid);
}