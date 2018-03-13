<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/3/9
 * Time: 下午2:46
 */
namespace common\widgets;

use Yii;

class RedPack
{

    /**
     * @param $req
     * @created by Jhu
     *
     * $req = array (
     *'open_id' => $open_id,
     *'mch_id' => Yii::$app->params['channel_mch_id'],
     *'wxappid' => Yii::$app->params['channel_app_id'],
     *'wechat_mch_secret' => Yii::$app->params['wechat_mch_secret'],
     *'send_name' => '微课',
     *'total_amount' => intval($total_money),
     *'total_num' => 1,
     *'wishing' => $title,
     *'act_name' => '推广奖励',
     *'remark' => '妙克信息科技',
     *'scene_id' => 'PRODUCT_5'
     *'pem_root' => Yii::$app->params['pem_root']
     *);
     */
    public static function send($req)
    {
        $result = array (
            'error' => 0,
            'data' => []
        );
        $data = array (
            'nonce_str' => md5(uniqid() . mt_rand(1000, 9999) . $req['open_id']),
            'mch_billno' => $req['mch_id'] . date('YmdHis') . mt_rand(1000, 9999),
            'mch_id' => $req['mch_id'],
            'wxappid' => $req['wxappid'],
            'send_name' => $req['send_name'],
            're_openid' => $req['open_id'],
            'total_amount' => intval($req['total_amount']),
            'total_num' => $req['total_num'],
            'wishing' => $req['wishing'],
            'client_ip' => isset($req['client_ip']) ? $req['client_ip'] : $_SERVER['SERVER_ADDR'],
            'act_name' => $req['act_name'],
            'remark' => $req['remark']
        );

        // 超过200元需要有签名

        if ($data['total_amount'] > 20000) {
            $data['scene_id'] = $req['scene_id'];
        }

        $data['sign'] = self::getSign($data, $req['wechat_mch_secret']);

        $url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack";
        $postData = self::toXml($data);
        $returnData = self::postSsl($url, $postData, $req['pem_root']);
        $returnData = self::getXml($returnData);

        switch ($returnData['result_code']) {
            case 'SUCCESS':
                $result = ['error' => 0, 'data' => array('mch_billno' => $data['mch_billno'])];
                break;

            case 'FAIL':
                if ($returnData['err_code_des'] === 'NOTENOUGH') {
                    self::sendPhoneMessage($data['mch_id']);
                }

                $result['error'] = $returnData['err_code_des'];
                break;

            default:
                $result['error'] = '未知错误';
        }

        return $result;
    }

    // 当余额不足触发
    private function sendPhoneMessage($mchId = '')
    {
        $args = array(
            "apikey" => "8f5fae8054bdfb3808f310526acea74a",
            "tpl_id" => 1240355,
            "tpl_value" => "#content#=微信商户号" .$mchId. "余额不足，请及时充值。如充值后短期内再次出现此短信，请告知技术",
            "mobile" => '18616300092'
        );

        $this->tplSendSms($args);
    }

    private function tplSendSms($args = [])
    {
        $url = "http://yunpian.com/v1/sms/tpl_send.json";
        $encoded_tpl_value = urlencode("{$args['tpl_value']}");
        $post_string = "apikey={$args['apikey']}&tpl_id={$args['tpl_id']}&tpl_value=$encoded_tpl_value&mobile={$args['mobile']}";
        return $this->sockPost($url, $post_string);
    }

    private function sockPost($url = '', $query = '')
    {
        $data = "";
        $errno = "";
        $errstr = "";
        $write = "";
        $info = parse_url($url);
        $fp = fsockopen($info["host"], 80, $errno, $errstr, 30);
        if (!$fp) {
            return $data;
        }
        $head="POST ".$info['path']." HTTP/1.0\r\n";
        $head.="Host: ".$info['host']."\r\n";
        $head.="Referer: http://".$info['host'].$info['path']."\r\n";
        $head.="Content-type: application/x-www-form-urlencoded\r\n";
        $head.="Content-Length: ".strlen(trim($query))."\r\n";
        $head.="\r\n";
        $head.=trim($query);
        $write=fputs($fp, $head);
        $header = "";
        while ($str = trim(fgets($fp, 4096))) {
            $header.=$str;
        }
        while (!feof($fp)) {
            $data .= fgets($fp, 4096);
        }
        return $data;
    }


    // 获取签名
    private function getSign($input, $key)
    {
        unset($input['sign']);
        ksort($input);
        $buff = "";

        foreach ($input as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }

        $string = trim($buff, "&");
        $string = $string . "&key=" . $key;
        $string = md5($string);
        return strtoupper($string);
    }

    // 转换成XML 格式
    private function toXml($array, $useCdata = true, $xml = "<xml>", $xmlKey = "</xml>")
    {
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $xml.="<".$key.">";
                $xmlKey ="</".$key.">".$xmlKey;
                return $this->toXml($val, $useCdata, $xml, $xmlKey);
            }
            if (is_numeric($val) || !$useCdata) {
                $xml.="<".$key.">".$val."</".$key.">";
            } else {
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }

        $xml.= $xmlKey == "</xml>" ?  $xmlKey : $xmlKey;
        return $xml;
    }

    private function postSsl($url, $params, $pemRoot, $seconds = 30, $headerMap = [])
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_TIMEOUT, $seconds);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($curl, CURLOPT_SSLCERT, $pemRoot . '/apiclient_cert.pem');
        curl_setopt($curl, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($curl, CURLOPT_SSLKEY, $pemRoot . '/apiclient_key.pem');
        curl_setopt($curl, CURLOPT_CAINFO, $pemRoot . '/rootca.pem');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_USERPWD, "miaoke:music111");
        $tmpInfo = curl_exec($curl);
        $errno = curl_errno($curl);
        if ($errno) {
            $info  = curl_getinfo( $curl );
            $info['errno'] = $errno;
            $log = json_encode( $info );
            Yii::error('redPack api error:'.$log);
            return false;
        }
        curl_close($curl);
        return $tmpInfo;
    }

    public function getXml($xml = '')
    {
        $xml = $xml == '' ? file_get_contents("php://input") : $xml;
        libxml_disable_entity_loader(true);

        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }
}
