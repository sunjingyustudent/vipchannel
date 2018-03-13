<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/10/21
 * Time: 下午3:09
 */
namespace common\widgets;

use Yii;

class Queue {

    /**
     * @param $message
     * @param $exchange
     * @param $routing
     * @return bool
     * @created by Jhu
     * 通用进队方法,消息持久化
     */
    public static function produce($message, $exchange, $routing, $isPersistent = 1)
    {
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

    /**
     * @param $messageList
     * @param $exchange
     * @param $routing
     * @return bool
     * @created by Jhu
     * 通用批量进队方法,消息持久化
     */
    public static function batchProduce($messageList, $exchange, $routing, $isPersistent = 1)
    {
        try{
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

            foreach ($messageList as $message)
            {
                $messageJson = json_encode($message);

                if ($isPersistent == 1)
                {
                    if (!$ex->publish($messageJson,$routing,1,['delivery_mode' => 2]))
                    {
                        return false;
                    }
                } else {
                    if (!$ex->publish($messageJson, $routing, 1))
                    {
                        return false;
                    }
                }
            }

            return true;
        }catch (\Throwable $ex){
            return true ;
        }

    }

    /**
     * @param $message
     * @param $exchange
     * @param $routing
     * @param $ttl    消息生存时间(1000 = 1s)
     * @return bool
     * @created by Jhu
     * 通用延迟消费进队方法,消息持久化
     */
    public static function produceTtl($message, $exchange, $routing, $ttl)
    {
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

        $argument = array (
            'delivery_mode' => 2,
            'expiration' => $ttl
        );

        if (!$ex->publish($message,$routing,1,$argument))
        {
            return false;
        }

        return true;
    }

    /**
     * @param $message
     * @param $exchange
     * @param $routing
     * @return bool
     * @created by Jhu
     * 日志进队
     */
    public static function produceLogs($message, $exchange, $routing)
    {
        try {
            $conn = new \AMQPConnection(Yii::$app->params['queue']);

            if (!$conn->connect()) {
                return false;
            }

            $message = json_encode($message);

            $channel = new \AMQPChannel($conn);
            $ex = new \AMQPExchange($channel);
            $ex->setName($exchange);
            $ex->setType(AMQP_EX_TYPE_DIRECT);
            $ex->setFlags(AMQP_DURABLE);

            $ex->declareExchange();

            if (!$ex->publish($message, $routing, 1)) {
                return false;
            }

            return true;

        } catch (\Throwable $ex) {
            return true;
        }
    }
}