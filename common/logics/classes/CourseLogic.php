<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/22
 * Time: 上午9:50
 */
namespace common\logics\classes;

use common\services\QiniuService;
use Yii;
use yii\base\Object;
use yii\db\Exception;
use common\services\LogService;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;


class CourseLogic  extends Object implements ICourse 
{
    /** @var  \common\sources\read\classes\ClassAccess  $RClassAccess */
    private $RClassAccess;
    /** @var  \common\sources\write\classes\ClassAccess  $WClassAccess */
    private $WClassAccess;

    public function init()
    {
        $this->RClassAccess = Yii::$container->get('RClassAccess');
        $this->WClassAccess = Yii::$container->get('WClassAccess');
        parent::init();
    }

    public function doAddMusic($request, $logid)
    {
        $courseInfo = array();

        if (!empty($request['course_ids'])) {
            foreach ($request['course_ids'] as $courseId) {
                $courseInfo[] = $this->coursesDetail($courseId);
            }
        }
        $request['marks'] = empty($request['marks']) ? '' : $request['marks'];
        $this->WClassAccess->saveMusic($request['class_id'], $courseInfo, $request['marks']);

        LogService::OutputLog($logid, 'update', '', '乐谱库选择乐谱');

        return json_encode(array('error' => ''));
    }

    public function getLastMusic($class_id)
    {
        $course_info = $this->RClassAccess->getLastCourse($class_id);

        $course_info_new = unserialize($course_info);

        if(!empty($course_info_new)){
            $data = array(
                'error' => '',
                'data' => $course_info_new
            );
        }else {
            $data = array('error' => '当前没有乐谱!');
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    private function coursesDetail($coursesId)
    {
        $url = Yii::$app->params['api_url'] . "/book/courses-detail?"
            . "id=$coursesId"
            . "&from=0"
            . "&limit=50";
        $data = json_decode($this->httpGet($url), true);
        return $data['data'];
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

    public function uploadMusic($class_id)
    {
        $images = $this->RClassAccess->getClassImageInfoByClassid($class_id);
        
        return $images;
    } 

    public function exportFile($class_id)
    {
        $last_class_id = $this->RClassAccess->getClassId($class_id);

        $files = $this->RClassAccess->getClassImage($last_class_id);

        if(empty($files)){
            $data = array(
                'error' => '最后一次课没有图片',
            );
        }else {
            $this->WClassAccess->deleteFile($class_id);

            foreach($files as $k=>$v){

                $img = file_get_contents(Yii::$app->params['pnl_static_path'].$v['file_path']);

                $fileKey = md5($class_id . '_' . microtime() . '_' . rand(10, 99));

                $file_path = '/tmp/'.$fileKey;

                file_put_contents($file_path,$img);

                $accessKey = Yii::$app->params['qiniuAccessKey'];
                $secretKey = Yii::$app->params['qiniuSecretKey'];

                $auth = new Auth($accessKey, $secretKey);

                // 要上传的空间
                $bucket = Yii::$app->params['pnl_static_bucket'];

                // 生成上传 Token
                $token = $auth->uploadToken($bucket);

                $filePath = $file_path;

                // 上传到七牛后保存的文件名
                $key = 'class/image/' . $fileKey;

                // 构建 UploadManager 对象
                $uploadMgr = new UploadManager();

                // 调用 UploadManager 的 putFile 方法进行文件的上传
                list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);

                if ($err !== null) {
                    $data = array('error'=>'导入失败!');
                    break;
                } else {

                    $id = $this->WClassAccess->intoFile($class_id,$key);
                    $images[] = array('id'=>$id,'file_path'=>Yii::$app->params['pnl_static_path'].$key,);
                    $data = array('error' => '','images'=>$images);
                    unlink($file_path);
                }
            }
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public function doUpload($classId, $logid)
    {
        $file = $_FILES;

        if ($file['file']["error"] > 0)
        {
            return 0;
        } else {
            $fileKey = md5($classId . '_' . microtime() . '_' . rand(10, 99));

            $bucket = Yii::$app->params['pnl_static_bucket'];

            $filePath = $file['file']['tmp_name'];

            // 上传到七牛后保存的文件名
            $key = 'class/image/' . $fileKey;

            if (QiniuService::uploadToQiniu($bucket, $key, $filePath))
            //if (QiniuService::uploadToQiniu($bucket, $key, $filePath))
            {
                $this->WClassAccess->addClassImage($classId, $key);
                LogService::OutputLog($logid,'add',serialize($classId),'上传乐谱');
                
                $fop = Yii::$app->params['image_slim_fop'];
                $image = Yii::$app->params['pnl_static_path'] . $key;
                QiniuService::fopImageSaveAs($image, $bucket, $key, $fop);
                
                return true;
            } else {
                return 0;
            }
        }
    }

    public function doDeleteClassImage($imageId, $logid)
    {
        $imageInfo = $this->RClassAccess->getClassImageInfo($imageId);

        $accessKey = Yii::$app->params['qiniuAccessKey'];
        $secretKey = Yii::$app->params['qiniuSecretKey'];

        //初始化Auth状态
        $auth = new Auth($accessKey, $secretKey);

        //初始化BucketManager
        $bucketMgr = new BucketManager($auth);

        //你要测试的空间， 并且这个key在你空间中存在
        $bucket = Yii::$app->params['pnl_static_bucket'];
        $key = $imageInfo['file_path'];

        //删除$bucket 中的文件 $key
        $err = $bucketMgr->delete($bucket, $key);
/*
        if ($err !== null) {
            return json_encode(array('error' => $err));
        }
*/
        if ($this->WClassAccess->deleteClassImage($imageId)) {
            LogService::OutputLog($logid,'delete',serialize($imageId),'删除乐谱');
            return json_encode(array('error' => ''));
        }
    }

    public function queryImagepage($id = 0)
    {

        $courseData = $this->RClassAccess->queryCourseData($id);
        $courseData = unserialize($courseData->course_info);

        $imageList = $this->RClassAccess->queryImageList($id);
        if(!empty($imageList)) {
            foreach($imageList as &$image) {
                $image['file_path'] = Yii::$app->params['pnl_static_path'] . $image['file_path'];
            }
        }

        $data['courseData'] =$courseData;
        $data['imageList'] =$imageList;

        return $data;
    }

}