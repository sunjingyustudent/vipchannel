<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2016 yuangaokun
 * @date 2016-11-25
 */

namespace common\widgets;

/**
 * Alert widget renders a message from session flash. All flash messages are displayed
 * in the sequence they were assigned using setFlash. You can set message as following:
 *
 * ```php
 * \Yii::$app->session->setFlash('error', 'This is the message');
 * \Yii::$app->session->setFlash('success', 'This is the message');
 * \Yii::$app->session->setFlash('info', 'This is the message');
 * ```
 *
 * Multiple messages could be set as follows:
 *
 * ```php
 * \Yii::$app->session->setFlash('error', ['Error 1', 'Error 2']);
 * ```
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @author Alexander Makarov <sam@rmcreative.ru>
 */
class Tool extends \yii\bootstrap\Widget
{
    /**
     * @var array the alert types configuration for the flash messages.
     * This array is setup as $key => $value, where:
     * - $key is the name of the session flash variable
     * - $value is the bootstrap alert type (i.e. danger, success, info, warning)
     */

    /**
     * @var array the options for rendering the close button tag.
     */

    /**
     * @param $arr
     * 打印Array数组
     */
    public static function p($arr){
        echo '<pre>';
        print_r($arr);
        echo '</pre>';
    }

    /**
     * @param $obj
     * @param int $flag
     * 打印最后一条SQL语句
     */
    public static function g($obj,$flag=1){
        if($flag){
            $com = clone $obj;
            echo $com->createCommand()->getRawSql();
        }else{
            $com = clone $obj;
            echo $com->createCommand()->getRawSql();
            die;
        }
    }

    /**
     * 表单数据过滤
     * @param $string
     * @param bool|False $simple
     * @return bool
     */
    public static function  xssFilter(&$string, $simple = False)
    {
        if (!is_array ( $string )){
            $string = trim ( $string );
            $string = htmlspecialchars ( $string );
            $string = addslashes( $string );
            if ($simple){
                return True;
            }
            $string = strip_tags ( $string );
            $string = str_replace ( array (
                '"',
                "\\",
                "'",
                "/",
                "..",
                "../",
                "./",
                "//"
            ), '', $string );
            $no = '/%0[0-8bcef]/';
            $string = preg_replace ( $no, '', $string );
            $no = '/%1[0-9a-f]/';
            $string = preg_replace ( $no, '', $string );
            $no = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';
            $string = preg_replace ( $no, '', $string );
            return True;
        }
        $keys = array_keys ( $string );
        foreach ( $keys as $key ){
            self::xssFilter ( $string [$key], $simple );
        }
    }
}
