<?php
return [

    /************************************ getui_config ****************************************/

//    'teacher_getui_appid' => 'R9hx1IsxI5Ala4zgm31vs3',
//    'teacher_getui_appkey' => 'oig8J8JG3HALCx7TMgacl6',
//    'teacher_getui_mastersecret' => 'C1bBTBuYpq7qKLtfHvGPl5',
//    'student_getui_appid' => '7EOhq07R5y5eiS1p4BdC0A',
//    'student_getui_appkey' => 'ccljACYGMc7ZLQapZTr6G4',
//    'student_getui_mastersecret' => 'MuWD7F1h6q8RCCtxl74Yu6',
//    //老师稳定版本的个推的配置
//    'teacher_getui_stable_appid' => 'OdAIbvJM258MY0MqdL13gA',
//    'teacher_getui_stable_appkey' => 'DdjrdeDJyK9DIIXDg6rQ94',
//    'teacher_getui_stable_mastersecret' => '7ki8ZsLMlC987IcvSfJfz',
//    //学生端稳定版本的个推的配置
//    'student_getui_stable_appid' => 'yMuGjGyFLI6bOtN8r0nrf3',
//    'student_getui_stable_appkey' => 'CvWUCae7tr7BX3qzAYybR6',
//    'student_getui_stable_mastersecret' => '52SVTpCH2D8ioKyNvbuNR',



    'teacher_getui_appid_dev' => 'MRSVW7bFsU9jgHv8gqGX42',
    'teacher_getui_appkey_dev' => '4ngFvZ1lVj73CVxn9BGJy9',
    'teacher_getui_mastersecret_dev' => 'AVdP83C74T9UNE6W9eljB5',

    'student_getui_appid_dev' => 'YkrFhAzlZa81gE4SWy8pW1',
    'student_getui_appkey_dev' => 'lFfBq6ibLW7kTFI0iClh05',
    'student_getui_mastersecret_dev' => 'ItLIJPkrbC6DptrcXbAZa1',
    //老师的iphone的个推设置
    'teacher_getui_iPhone_appid_dev' => 'mUBu5s5Im36OSnCQKyvt67',
    'teacher_getui_iPhone_appkey_dev' => 'hs5nC72W169AOJJN94cbN1',
    'teacher_getui_iPhone_mastersecret_dev' => 'k9vhjhSQCw8KxeVU5Eoq55',
    //老师稳定版本的个推设置
    'teacher_getui_stable_appid_dev' => 'OdAIbvJM258MY0MqdL13gA',
    'teacher_getui_stable_appkey_dev' => 'DdjrdeDJyK9DIIXDg6rQ94',
    'teacher_getui_stable_mastersecret_dev' => '7ki8ZsLMlC987IcvSfJfz',
    //学生稳定版本的个推设置
    'student_getui_stable_appid_dev' => 'yMuGjGyFLI6bOtN8r0nrf3',
    'student_getui_stable_appkey_dev' => 'CvWUCae7tr7BX3qzAYybR6',
    'student_getui_stable_mastersecret_dev' => '52SVTpCH2D8ioKyNvbuNR',



    /************************************ mq_config ******************************************/

    'queue' => array (
        'host'     => '192.168.40.213',//224
        'port'     => '5672',
        'login'    => 'mqadmin',
        'password' => 'mqadmin'
    ),

    /************************************ qiniu_config ****************************************/

    'qiniuAccessKey' => 'tM193uNBWVubyf1od06tTI50euAd31tOOg3GXsA4',
    'qiniuSecretKey' => 'U7pSQxlKAq8sMDXFC2wFH53mdvPR9mG9gzgYRVjq',

    'pnl_audio_bucket' => 'test001',
    'pnl_book_bucket' => 'pnl-book',
    'pnl_book_audio_bucket' => 'pnl-book-audio',
    'pnl_doc_bucket' => 'test001',
    'pnl_logs_bucket' => 'test001',
    'pnl_static_bucket' => 'test001',
    'pnl_video_bucket' => 'test001',
    'vip_static_bucket' => 'test001',
    'vip_video_bucket' => 'test001',

    'pnl_audio_path' => 'http://test001.pnlyy.com/',
    'pnl_book_path' => 'http://book.static.pnlyy.com/',
    'pnl_book_audio_path' => 'http://book.audio.pnlyy.com/',
    'pnl_doc_path' => 'http://test001.pnlyy.com/',
    'pnl_static_path' => 'http://test001.pnlyy.com/',
    'pnl_video_path' => 'http://test001.pnlyy.com/',
    'vip_static_path' => 'http://test001.pnlyy.com/',
    'vip_audio_path' => 'http://test001.pnlyy.com/',

    'vip_video_patah' => 'http://test001.pnlyy.com/',
    'vip_video_path' => 'http://test001.pnlyy.com/',

    'pic_size_1000' => '!1000',
    'pic_size_1500' => '!1500',
    'pic_size_200' => '!200',

    //七牛上传乐谱压缩策略
    'image_slim_fop' => 'imageView2/0/w/1200/format/jpg/q/60|imageslim',


    /************************************ VIP陪练 ********************************************/

                                        /* VIP陪练 */
    'wechat_mch_id' => '1231569802',
    'wechat_mch_secret' => 'B944BB5C489B45620CF106D2F23EC788',
    'wechat_app_id' => 'wxcdef6dd053995bc7',
    'pem_root' => '/var/www/web/pnl_projects/api/web/cert_api',

    //我的专属客服事件
    'student_key_personal_sales' => 'PERSONAL_SALES',
    //我的推荐事件
    'student_key_promotion' => 'STUDENT_PROMOTION',

    //问题处理结果反馈
    'student_template_feedback' => 'tt7KXbgUvygmaf3WuL6Obc3bLv9WLdPrc-on4LbjKpo',
    //课时到账通知
    'student_template_class_income' => 'fyRMpnpbn6NWXUP0nfibohtK5XLf1lz8eP7e4mCe4wA',
    //个人消息通知
    'student_template_personal' => 'p1z_1OWXR_1S_6GvffvXTUifXkr5T0UnsjS_HBWKJ24',
    //服务评价通知
    'student_template_service_comment' => 'zTMplbkuVRjylnXMP7uHvi36dSWNUoe4oEE07zFYQB4',
    //完成任务提醒
    'student_template_task_complete' => 'eIXCX-ynji-WjTgfzWycAPlCLq8KaWbhpRZe2-2GOjQ',
    //课程通知
    'student_template_class_alarm' => 'vK5I2V6BiShuCsceDbJjb38ssKjf6y9a6AblZCbnCeM',
    //客服通知提醒
    'student_template_kefu_message' => 'hiARFeaRchUaBkHZmanJeCqfjGrcIDk90_sbMcvVBDQ',
    //上课提醒
    'student_template_class_alert' => 'rLrHU-RTxsQe4NAaOWRDjsULmT8oPHmln948o-SOLRg',
    //预约成功通知
    'student_template_class_order' => 't7jhwMLFZ40hOipWUUuVcigWxd0bDQRTIuM9eFpoILk',


                                        /* VIP微课 */

    'sales_mch_id' => '1254775201',
    'sales_app_id' => 'wx4384ef5fb33ba448',
    'sales_mch_secret' => 'B944BB5C489B45620CF106D2F23EC788',
    'sales_pem_root' => '/var/www/web/pnl_projects/api/web/cert',

    'channel_key_recommend' => 'K003_MY_RECOMMEND',
    'channel_key_personal' => 'K003_PERSONAL_SERVICE',

    //预约成功通知
    'channel_template_class_success' => 'K8kbosV2n8tNT0Oq7KB-ULD4XKiaQ3ufXkuoCDqkDAs',
    //待办事项提醒
    'channel_template_todo' => 'DFxaiR2c1Jw5gxknA6Hyb1Xh0RN_X64F-5fAKU6BPok',
    //收入提醒
    'channel_template_income' => 'aBTi-LoQ2B8QFr7v00ogfvOeyPnW6zLlXeQ4JoxgMps',
    //问题处理结果反馈
    'channel_template_feedback' => 'qw7F0It9xPLxQMQwzYeGaw5GwAGbpUcT4N35C92ad0U',
    //购买成功通知
    'channel_template_purchase' => 'sf4xw9QtlHgQjyW-E9cOlyUQQGRNZC50DHc1xYV1q1k',
    //学习完成通知
    'channel_template_study_complete' => 'AMNaEEIr6eZ8YmXzefpXLPoRdQJxL40B679Z_SbvrCw',
    

                                        /* 妙克艺术人才基地 */
    
    //排课通知
    'teacher_template_class_arrange' => '-Hd1jLBesqpsoe1VrNMhHiSSx-oqcsVcnIVDiOHLGn4',
    //调课成功通知
    'teacher_template_class_edit' => 'v1YcfqV1VQOz1RaBDYVp9kXfPqxOpr9CKFgEwRJB5GQ',
    //课程取消通知
    'teacher_template_class_cancel' => 'Ey4T-AMyMb_hpgpl0sDeOBYh5WPMyozU4FWjFbniJFw',
    //课程提醒通知
    'teacher_template_class_alert' => '8hsWFvIJPSYOeKO_lzygCqzGNZACZuaO743XfN3c7vw',
    //薪资确认提醒
    'teacher_template_salary_confirm' => '1tSh3aYNVVHdNWvpQhrFtm2o0FaMiOQrXoPzxfx47EE',
    //负责人7天通知（临时）
    'teacher_template_class_arrange_tmp' => '5ykwT6eTXt9s0bOAsRYebH7aCX0voLFuOxPmjXhGIkw',


    /************************************ base_url ********************************************/
    //渠道公众号项目前端域名base_url
    'channel_frontend_url' => 'http://webchannel.dev.pnlyy.com/',
    'student_zh_domain' => 'http://yii.pnlyy.com/',
    'channel_zh_domain' => 'http://channelwx-test.pnlyy.com/',
    'teacher_zh_domain' => 'http://tiantuan-test.pnlyy.com/',

    'crm_domain' => 'http://crm-test.pnlyy.com/',
    'opt_domain' => 'http://opt-test.pnlyy.com/',
    'api_domain' => 'http://api-dev1.pnlyy.com/',
    'teacher_domain' => 'http://teacher-test.pnlyy.com/',

    //渠道公众号项目base_url
    'channel_base_url' => 'http://channelwx-test.pnlyy.com/',
    //下载课后端
    'down_class_url' => 'http://yii.pnlyy.com/student/down-app-page',
    //支付链接
    'pay_url' => 'http://yii.pnlyy.com/product/pay?orderID=',
    //本次奖励明细
    'reward_info' => 'http://channelwx-test.pnlyy.com/live/history-detail?historyid=',
    //我的课程页面
    'myclass_url' => 'http://yii.pnlyy.com/class/self-class-index',
    //我的专属服务页面
    'sales_url' => 'http://yii.pnlyy.com/student/show-adviser',
    //免费体验
    'free_class_url' => 'http://yii.pnlyy.com/student/intro',
    //课后单页面
    'record_path' => 'http://yii.pnlyy.com/class/record-detail?is_student=0&record_id=',
    'imageUrlPrefix' => 'http://opt.demo.com',
    'class_url' => 'http://yii.pnlyy.com/',
    'base_url' => 'http://yii.pnlyy.com/',
    'api_url' => 'http://api.pnlyy.com',

    'student_base_url' => 'http://yii.pnlyy.com/',



    /************************************ swoole_config ****************************************/

    'swoole_port' => 'http://127.0.0.1:9502',


    /*************************************** others ********************************************/

    'pem_root' => '',
    'department_teacher_id' => '7',
    'corp_id' => 'wxf39085431e55e51f',
    'corp_secret' => 'ckZ-vtPNCmNygvPfnqcddM9G3ClFWKZjxs0sYP8_HU5gKgrti_xFRZgPDr5mzGAa',
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'user.passwordResetTokenExpire' => 3600,
    //拉新奖励人数
    'reward_pull_num' =>3,
];
