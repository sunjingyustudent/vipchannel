<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/2/3
 * Time: 下午3:47
 */
namespace common\widgets;

use common\services\ErrorService;

class Message {
    
    public static function buildMessage($param, $openId = '')
    {

        if (!empty($param))
        {
            $data = self::getTemplateData($param);

            $message = array (
                'touser' => $openId,
                'template_id' => $param['template_id'],
                'url' => $param['url'],
                'data' => $data
            );

            return $message;
        }else
        {
            ErrorService::AddStudentTemplateError($msg, '错误的type类型');
        }
    }

    private static function getTemplateData($param)
    {
        switch ($param['keyword_num'])
        {
            case 1 :
                $data = array(
                    'first' => array('value' => $param['firstValue']),
                    'keyword1' => array('value' => $param['key1word']),
                    'remark' => array('value' => $param['remark'])
                );

                break;

            case 2 :
                $data = array(
                    'first' => array('value' => $param['firstValue']),
                    'keyword1' => array('value' => $param['key1word']),
                    'keyword2' => array('value' => $param['key2word']),
                    'remark' => array('value' => $param['remark'])
                );

                break;

            case 3 :
                $data = array(
                    'first' => array('value' => $param['firstValue']),
                    'keyword1' => array('value' => $param['key1word']),
                    'keyword2' => array('value' => $param['key2word']),
                    'keyword3' => array('value' => $param['key3word']),
                    'remark' => array('value' => $param['remark'])
                );

                break;

            case 4 :
                $data = array(
                    'first' => array('value' => $param['firstValue']),
                    'keyword1' => array('value' => $param['key1word']),
                    'keyword2' => array('value' => $param['key2word']),
                    'keyword3' => array('value' => $param['key3word']),
                    'keyword4' => array('value' => $param['key4word']),
                    'remark' => array('value' => $param['remark'])
                );

                break;
        }

        return $data;
    }
}