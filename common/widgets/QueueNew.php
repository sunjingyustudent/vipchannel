<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2017/3/29
 * Time: 15:40
 */
namespace common\widgets ;
use Yii ;

class QueueNew {
    /*
     * 数据进行入队
     */
    public static function produce($message, $exchange ="logstash", $routing = "app_logs_routing", $isPersistent = 1){
        $conn = new \AMQPConnection(Yii::$app->params['queue']);

        if (!$conn->connect())
        {
            return false;
        }

        $message = json_encode($message);

        $channel = new \AMQPChannel($conn);
        $ex = new \AMQPExchange($channel);
        $ex->setName($exchange);
        $ex->setType(AMQP_EX_TYPE_DIRECT);
        $ex->setFlags(AMQP_DURABLE);

        $ex->declareExchange();

        if ($isPersistent == 1)
        {
            if (!$ex->publish($message,$routing,1,['delivery_mode' => 2]))
            {
                return false;
            }
        } else {
            if (!$ex->publish($message, $routing, 1))
            {
                return false;
            }
        }

        return true;
    }
    /*
     * 批量数据进行入队
     */
    public static  function batchProduce($list, $exchange ="logstash", $routing = "app_logs_routing", $isPersistent = 1){
        $conn = new \AMQPConnection(Yii::$app->params['queue']);

        if (!$conn->connect())
        {
            return false;
        }


        $channel = new \AMQPChannel($conn);
        $ex = new \AMQPExchange($channel);
        $ex->setName($exchange);
        $ex->setType(AMQP_EX_TYPE_DIRECT);
        $ex->setFlags(AMQP_DURABLE);

        $ex->declareExchange();
        foreach ($list as $v){
            $message = json_encode($v) ;

            if ($isPersistent == 1)
            {
                if (!$ex->publish($message,$routing,1,['delivery_mode' => 2]))
                {
                    return false;
                }
            } else {
                if (!$ex->publish($message, $routing, 1))
                {
                    return false;
                }
            }

        }
        return true;
    }
}