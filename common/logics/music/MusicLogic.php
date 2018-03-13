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
use yii\db\Exception;
use common\services\LogService;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class MusicLogic extends Object implements IMusic
{
    /** @var  \common\sources\read\music\MusicAccess $RMusicAccess */
    private $RMusicAccess;

    public function init()
    {
        $this->RMusicAccess = Yii::$container->get('RMusicAccess');
        parent::init();
    }

    public function getMusicLibrary($class_id)
    {
        $courses = $this->RMusicAccess->getMusicLibrary($class_id);

        return [$class_id, $courses['course_info'], $courses['marks']];
    }

    public function getBookList($search='')
    {
        $url = Yii::$app->params['api_url'] . "/book/book-list?"
            . (empty($search) ? "" : "s=$search")
            . "&from=0"
            . "&limit=50";

        return $this->httpGet($url);
    }

    public  function getChapterList($bookid=0,$keyword='')
    {
        $from = 0;
        $limit = 200;
        $keyword = urldecode(addslashes(Yii::$app->request->get('keyword', '')));

        $url = Yii::$app->params['api_url'] . "/book/book-detail?"
            . (empty($bookid) ? "bid=0" : "bid=$bookid")
            . (empty($keyword) ? "" : "&keyword=$keyword")
            . (empty($from) ? "&from=0" : "&from=$from")
            . (empty($limit) ? "&limit=10" : "&limit=$limit");
            
        return $this->httpGet($url);
    }

    public function httpGet($url, $headers = [])
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, @$_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_HTTPGET, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            return false;
        }
        curl_close($curl);
        return $tmpInfo; // 返回数据
    }
}
