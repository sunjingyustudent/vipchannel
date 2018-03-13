<?php
namespace app\controllers;

use common\models\music\SalesChannel;

use common\models\music\Template;
use common\models\music\TemplatePushStatistic;
use common\widgets\Queue;
use common\widgets\TemplateBuilder;

use common\models\music\SalesTrade;
use console\models\ChannelActive;

use Yii;
use yii\base\Exception;
use yii\web\Controller;
use common\widgets\Request;
use common\services\QiniuService;
use common\models\music\WechatClass;
use common\models\music\UserShare;

/**
 * Site controller
 */
class TaskController extends Controller
{
    public function init()
    {
        parent::init();
    }

    /*
     * 同步 用户信息
     * create by sjy
     * 2017-06-30
     */
    public function actionUpdateUnion($id = 0)
    {
        die();
        $id = intval($id);
        $success = 0;
        $false = 0;
        $total = 0;
        $last_id = 0;
        $wechat = Yii::$app->wechat_new;

        $sql = "SELECT id,bind_openid,union_id
                FROM sales_channel
                WHERE bind_openid!='' AND status = 1 AND id > $id
                ORDER BY id ASC LIMIT 500";
        $result = Yii::$app->db->createCommand($sql)->queryAll();
        if ($result) {
            $total = count($result);
            foreach ($result as $value) {
                $userinfo = $wechat->getUserInfo($value["bind_openid"]);

                if ($value['union_id'] == '') {
                    $sql = "UPDATE sales_channel SET union_id = :union_id,subscribe =:subscribe WHERE id = :id";
                    $result = Yii::$app->db->createCommand($sql)
                        ->bindValues([
                            ':union_id' => $userinfo['unionid'],
                            ':subscribe' => (string)$userinfo['subscribe'],
                            ':id' => $value['id']
                        ])->execute();
                } else {
                    $sql = "UPDATE sales_channel SET subscribe =:subscribe WHERE id = :id";
                    $result = Yii::$app->db->createCommand($sql)
                        ->bindValues([
                            ':subscribe' => (string)$userinfo['subscribe'],
                            ':id' => $value['id']
                        ])->execute();
                }
                if ($result) {
                    $success++;
                } else {
                    $false++;
                }
                $last_id = $value['id'];
            }
        }
        echo 'total:' . $total;
        echo '<br>success:' . $success;
        echo '<br>false:' . $false;
        echo '<br>last_id:' . $last_id;
    }

    /**
     * 根据表名user_share 、redactive_record  更新user_id
     * @param string $tableName
     * create by wangke
     */
    public function actionUpdateUserid($tableName = '')
    {
        die();
        if (in_array($tableName, ['user_share', 'user_share_copy_wk' , 'redactive_record' , 'redactive_record_copy_wk'])) {
            $sql = "UPDATE " . $tableName . " a, sales_channel b SET  a.user_id = b.id 
                WHERE a.open_id = b.bind_openid  AND a.user_id = 0 and b.status =1";
            $count = Yii::$app->db->createCommand($sql)->execute();

            echo $tableName. '修改条数：' . $count;
        } else {
            echo "此表不能修改";
        }
    }
    
     /**
     * 修复二维码
     * @author Yrxin
     * @DateTime 2017-07-26T09:16:26+0800
     * @return   [type]                   [description]
     */
    public function actionQrcode($id = 0)
    {
        die();
        $id = intval($id);
        $sql = "SELECT id,weicode_path
                FROM sales_channel 
                WHERE bind_openid!='' AND status=1 AND id = $id";
        $result = Yii::$app->db->createCommand($sql)->queryOne();
        if ($result) {
            if (empty($result['weicode_path'])) {
                     //生成二维码
                $wechat = Yii::$app->wechat;

                $num = 2500000000;
                $senseid = $num + $result['id'];
                $qrcode = [
                    'action_name'=>'QR_SCENE',
                    'expire_seconds' => 2592000,
                    'action_info' => ['scene' => ['scene_id' => $senseid]]
                ];
                $tickect = $wechat->createQrCode($qrcode);
                $imgUrl = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $tickect['ticket'];
                // $img = Request::httpGet($imgUrl);

                //存储到七牛
                $filename = $this->uploadWeicode(Request::httpGet($imgUrl), 'sales_channel/qr_code/');

                $this->updateSalesChannelWithWpathById($filename, $result['id']);
            }
        }
    }

    private function uploadWeicode($file, $path)
    {
        $filename = md5(uniqid());
        // 要上传文件的本地路径
        $filePath = "/tmp/" . $filename;

        //保存到本地
        file_put_contents($filePath, $file);

        // 要上传的空间
        $bucket = Yii::$app->params['pnl_static_bucket'];

        // 上传到七牛后保存的文件名
        $key = $path . $filename;

        if (QiniuService::uploadToQiniu($bucket, $key, $filePath)) {
            unlink($filePath);
            return $key;
        }
        unlink($filePath);
    }

    private function updateSalesChannelWithWpathById($path, $insertId)
    {
        $sql = 'UPDATE sales_channel SET weicode_path = :path,reqrcode_time = :rtime WHERE id = :id';

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':path' => $path,
                ':rtime' =>time(),
                ':id' => $insertId
            ])
            ->execute();
    }


    /**
     * 新用户群发模板消息
     * @author wangke
     * @DateTime 2017/11/21  15:03
     * @return: [type]  [description]
     */
    public function actionSendTemplateGroup($page = 0, $size = 0, $uid = 0)
    {
        die('废弃');
        //需要发送的渠道
        $wechat_user = SalesChannel::find()
            ->select('id,bind_openid');
        if ($uid) {
            $wechat_user->where('id = :id', [
                ':id' => $uid
            ]);
        } else {
            $wechat_user->where('message_type = 1 AND status = 1 AND auth_time > 0 AND subscribe = "1"');
        }

        $wechat_user = $wechat_user->orderBy('id')
            ->offset(($page - 1) * $size)
            ->limit($size)
            ->asArray()
            ->all();

        echo '最后的id是' . end($wechat_user)['id'];
        //die();

        echo '<pre>';
        echo '1-----------------------------------------------';
        var_dump($wechat_user);

        if (empty($wechat_user)) {
            echo '本次查询：渠道表sales_channel已经没有数据';
            die();
        }

        //模板
        $param = [
            'template_id' => 'DFxaiR2c1Jw5gxknA6Hyb1Xh0RN_X64F-5fAKU6BPok',
            'firstValue' => ['value' => "瓜分10w奖金，赢三重好礼！" . "\n", 'color' => '#ff0000'],
            'key1word' => ['value' => '感恩节活动', 'color' => '#000000'],
            'key2word' => ['value' => '回复文字【火鸡】', 'color' => '#0000ff'],
            'key3word' => ['value' => '11月23日', 'color' => '#000000'],
            'remark' => ['value' => "\n" . "回复文字【火鸡】，领取福利！如不接受开课及红包活动推送提醒请回复【TD】", 'color' => '#0000ff'],
            'url' => '',
            'keyword_num' => 3
        ];

        //已经发送的渠道  注意 等到上一次发完才可以查，因为阻塞数据还没插入数据表template_push_statistics

        if (!empty($uid)) {
            $template_statis = [];
        } else {
            $template_statis = TemplatePushStatistic::find()
                ->select('open_id')
                ->where('template_id = :template_id', [
                    ':template_id' => 7
                ])
                ->column();
        }


        echo '<br>';
        echo '2-----------------------------------------------';
        var_dump($template_statis);

        //组装模板
        $messageList = [];
        foreach ($wechat_user as $key => $value) {
            if (in_array($value['bind_openid'], $template_statis)) {
                unset($wechat_user[$key]);
            } else {
                $build_tmp = TemplateBuilder::buildTemplate($param, $value['bind_openid']);
                if (!empty($build_tmp)) {
                    $messageList[] = [
                        'event' =>'TEMPLATE_ALL',
                        'id' => $uid ? 0 : 7,
                        'temp_send_info' => $build_tmp
                    ];
                }
            }
        }
//        echo '<br>';
//        echo '3-----------------------------------------------';
//        var_dump($messageList);
        die();

        //将要发送的内容发送到队列中
        if (!empty($messageList)) {
            Queue::batchProduce($messageList, 'template', 'channel_template_group');
            echo '第' . $page . '次发送人数：' . count($messageList);
        } else {
            echo '无渠道老师需要发送';
        }
    }

     /**
     * 感恩节活动发送奖励
     * @author wangke
     * @DateTime 2017/11/17  18:18
     * @return: [type]  [description]
     */
    public function actionSendThanksgivingReward()
    {
//        echo '现在还不能发送奖励';
        die();
        $november_start = strtotime('2017-11-01');
        $november_end = strtotime('2017-12-01') -1;
        //全部ids
        $all_ids = SalesTrade::find()
            ->select('uid')
            ->where('is_deleted = 0 AND status = 8 AND time_created BETWEEN :start AND :end', [
                ':start' => $november_start,
                ':end' => $november_end
            ])
            ->groupBy('uid')
            ->column();
        //已领取的id
        $ids_thanks = SalesTrade::find()
            ->select('uid')
            ->where('is_deleted = 0 AND status = 14')
            ->groupBy('uid')
            ->column();

        //可领取的ids

        if (empty($all_ids)) {
            echo 'data is null';
            die();
        }

        if ($ids_thanks) {
            $ids = array_diff($all_ids, $ids_thanks);
        } else {
            $ids = $all_ids;
        }

        if (empty($ids)) {
            echo 'data is null';
            die();
        }

        $data = [];
        $date_time = strtotime("20171205");
        foreach ($ids as $uid) {
            $data[] =['uid' => $uid,
                'studentID' => 0,
                'studentName' => '',
                'classID' => 0,
                'classType' => 0,
                'price' => 0,
                'recordID' => 0,
                'money' => 38,
                'descp' => '感恩节活动',
                'comment' => '感恩节活动',
                'status' => 14,
                'fromUid' => 0,
                'time_created' => $date_time];
        }

        try {
            $res = Yii::$app->db->createCommand()->batchInsert('sales_trade', [
                'uid',
                'studentID',
                'studentName',
                'classID',
                'classType',
                'price',
                'recordID',
                'money',
                'descp',
                'comment',
                'status',
                'fromUid',
                'time_created',
            ], $data)->execute();

            echo '给' . count($ids) . '人发送了感恩节奖励,插入数据库成功' . $res . '条' ;
            die();
        } catch (Exception $e) {
            Yii::error('thanksgiving send reward is fail:' . $e->getMessage());
            echo '奖励发送失败！';
        }
    }

    /**
     * 5000群发
     * @author wangke
     * @DateTime 2017/11/21  15:03
     * @return: [type]  [description]
     */
    public function actionSendTemplateGroupWuqian($id = '', $uid = 0)
    {
        die();
        //需要发送的渠道
        $wechat_user = SalesChannel::find()
            ->select('id,bind_openid');
        if ($uid) {
            $wechat_user->where('id = :id', [
                ':id' => $uid
            ]);
        } else {
            $wechat_user->where('message_type = 1 AND status = 1 AND auth_time > 0 AND subscribe = "1"')
            ->andFilterWhere(['<', 'id', $id]);
        }

        $wechat_user = $wechat_user->orderBy('id DESC')
            ->limit(5000)
            ->asArray()
            ->all();

        echo '最大id是：' . current($wechat_user)['id'];
        echo "<br>";
        echo '最小id是：' . end($wechat_user)['id'];
        echo "<br>";

        if (empty($wechat_user)) {
            echo '本次查询：渠道表sales_channel已经没有数据';
            die();
        }

        //模板
        $param = [
            'template_id' => 'KdgB9LPCikLMIA3WCsVphJBwIjzuPww5EZO0yiHCGD8',
            'firstValue' => ['value' => "瓜分10w奖金，赢三重好礼！" . "\n", 'color' => '#ff0000'],
            'key1word' => ['value' => '感恩节活动', 'color' => '#000000'],
            'key2word' => ['value' => '回复文字【火鸡】', 'color' => '#0000ff'],
            'key3word' => ['value' => '11月26日', 'color' => '#000000'],
            'remark' => ['value' => "\n" . "回复文字【火鸡】，领取福利！如不接受开课及红包活动推送提醒请回复【TD】", 'color' => '#0000ff'],
            'url' => '',
            'keyword_num' => 3
        ];

        //已经发送的渠道  注意 等到上一次发完才可以查，因为阻塞数据还没插入数据表template_push_statistics

        if (!empty($uid)) {
            $template_statis = [];
        } else {
            $template_statis = TemplatePushStatistic::find()
                ->select('open_id')
                ->where('template_id = :template_id', [
                    ':template_id' => 7
                ])
                ->column();
        }

        //组装模板
        $messageList = [];
        foreach ($wechat_user as $key => $value) {
            if (in_array($value['bind_openid'], $template_statis)) {
                unset($wechat_user[$key]);
            } else {
                $build_tmp = TemplateBuilder::buildTemplate($param, $value['bind_openid']);
                if (!empty($build_tmp)) {
                    $messageList[] = [
                        'event' =>'TEMPLATE_ALL',
                        'id' => $uid ? 0 : 7,
                        'temp_send_info' => $build_tmp
                    ];
                }
            }
        }

        //将要发送的内容发送到队列中
        if (!empty($messageList)) {
            Queue::batchProduce($messageList, 'template', 'channel_template_group');
            echo '本次次发送人数：' . count($messageList);
        } else {
            echo '无渠道老师需要发送';
        }
    }
}
