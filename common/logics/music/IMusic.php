<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/9
 * Time: 下午4:18
 */
namespace common\logics\music;

use Yii;
use yii\base\Object;

interface IMusic {

    /**
     * 音乐库页面
     * @user wk
     * @param  $class_id
     * @return array
     */
    public function getMusicLibrary($class_id);

    /**
     * 获取符合类型的乐谱书籍
     * @user wk
     * @param   search str
     * @return  array
     */
    public function getBookList($search='');

    /**
     * 获取书籍中的乐谱列表
     * @user wk
     * @param   search str
     * @return  array
     */
    public function httpGet($url, $headers = []);
}