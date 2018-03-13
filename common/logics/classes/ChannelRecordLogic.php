<?php
/**
 * Created by phpStorm.
 * User: xl
 * Date: 2017/3/14
 * Time: 13:56
 */
namespace common\logics\classes;

use yii\base\Object;
use Yii;

class ChannelRecordLogic extends Object implements IChannelRecord
{

    /** @var  \common\sources\read\classes\ChannelRecordAccess  $RChannelRecordAccess */
    private $RChannelRecordAccess;

    public function init()
    {
        $this->RChannelRecordAccess = Yii::$container->get('RChannelRecordAccess');
        parent::init();
    }

    public function getClassChannelRecord($class_id)
    {
        $channel_ids = $this->RChannelRecordAccess->getChannelIds($class_id);

        $data = array();

        if (!empty($channel_ids))
        {
            foreach ($channel_ids as $key => $item)
            {
                $data[$key]['channel_id'] = $item;

                if( strpos($item,'justtalk/record') !== false )
                {
                    $data[$key]['count'] = 1;

                    $data[$key]['url_list'] = Yii::$app->params['vip_video_path'] .$item;

                }else{
                    $urls = $this->RChannelRecordAccess->getRecordUrlByChannelId($item);

                    $data[$key]['count'] = count($urls);

                    $data[$key]['url_list'] = $urls;
                }

            }
        }

        return array('error' => 0, 'data' => $data);
    }
}