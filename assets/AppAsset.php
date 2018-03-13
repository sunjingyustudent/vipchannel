<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/bootstrap.min.css',
        'css/font-awesome.min.css',
        'css/sweet-alert.css',
        'css/animate.css',
        'plugins/daterangepicker/daterangepicker-bs3.css',
        'plugins/datepicker/datepicker3.css',
        'plugins/webuploader/webuploader.css',
        'css/site.css?v=100000',
        'plugins/bootstrap-datetimepicker-master/css/bootstrap-datetimepicker.min.css',
        'css/liping.css',
        'css/wangkai.css?v=100000',
        'css/wangke.css'
    ];

    //public $jsOptions = ['position' => \yii\web\View::POS_HEAD];
    public $js = [
        'js/jquery.min.js',
        'js/bootstrap.min.js',
        'plugins/fastclick/fastclick.min.js',
        'js/jqPaginator.min.js',
        'js/sweet-alert.min.js',
        'plugins/daterangepicker/moment.min.js',
        'plugins/daterangepicker/daterangepicker.js',
        'plugins/datepicker/bootstrap-datepicker.js',
        'plugins/datepicker/locales/bootstrap-datepicker.zh-CN.js',
        'plugins/webuploader/webuploader.js',
        'plugins/bootstrap-datetimepicker-master/js/bootstrap-datetimepicker.js',
        'plugins/bootstrap-datetimepicker-master/js/locales/bootstrap-datetimepicker.zh-CN.js',
        'js/ajaxupload-min.js',
        ENV_EXIST && ENV_CONFIG === 'dev' ? 'js/define-local.js' : 'js/define.js',
        'js/json2.js',
        'js/page.js?v=100001',
        'js/chat.js',
        'js/liping.js',
        'js/wangkai.js?v=100000',
        'js/wangke.js',
    ];
    public $depends = [
        //'yii\web\YiiAsset',
    ];
}
