<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/16
 * Time: 上午11:52
 */
namespace common\logics\classes;

use Yii;
use yii\base\Object;

interface IMonitor {

    /**
     * 上课过程监控的信息条数 
     * @param   $type  		状态  (待开始, 进行中, 已结束)
     * @param   $date 		时间
     * @param   $keyword    搜索内容 (时间或者手机)
     * @param   $kefu_id	当前客服ID  	
     * @return  int 
     */
    public  function getMonitorCount($type, $date, $keyword, $kefu_id, $monitor_courseType);

    /**
     * 上课过程监控的列表 
     * @param   $page       当前页面  
     * @param   $type  		状态  (待开始, 进行中, 已结束)
     * @param   $date 		时间
     * @param   $keyword    搜索内容 (时间或者手机)
     * @param   $kefu_id	当前客服ID  	
     * @return  int 
     */
    public function MlistList($page, $type,$date, $keyword, $kefu_id,$monitor_courseType);

    /**
     * 同步按钮,当学生老师的chat_token不存在时调用
     * @param   $req
     * @return  str
     */
    public function synAccount($req);
}