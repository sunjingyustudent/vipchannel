<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午3:17
 */

namespace common\logics\salary;

interface IWorkhour {

    public function getClassMoney($long, $time_class, $teacher_id);

}