<?php
return [
    /*********************************** getui_config ****************************************/

    'teacher_getui_appid' => 'R9hx1IsxI5Ala4zgm31vs3',
    'teacher_getui_appkey' => 'oig8J8JG3HALCx7TMgacl6',
    'teacher_getui_mastersecret' => 'C1bBTBuYpq7qKLtfHvGPl5',
    'student_getui_appid' => '7EOhq07R5y5eiS1p4BdC0A',
    'student_getui_appkey' => 'ccljACYGMc7ZLQapZTr6G4',
    'student_getui_mastersecret' => 'MuWD7F1h6q8RCCtxl74Yu6',
    //老师稳定版本的个推的配置
    'teacher_getui_stable_appid' => 'OdAIbvJM258MY0MqdL13gA',
    'teacher_getui_stable_appkey' => 'DdjrdeDJyK9DIIXDg6rQ94',
    'teacher_getui_stable_mastersecret' => '7ki8ZsLMlC987IcvSfJfz',
    //学生端稳定版本的个推的配置
    'student_getui_stable_appid' => 'yMuGjGyFLI6bOtN8r0nrf3',
    'student_getui_stable_appkey' => 'CvWUCae7tr7BX3qzAYybR6',
    'student_getui_stable_mastersecret' => '52SVTpCH2D8ioKyNvbuNR',
    //老师的iphone的个推设置
    'teacher_getui_iPhone_appid' => 'mUBu5s5Im36OSnCQKyvt67',
    'teacher_getui_iPhone_appkey' => 'hs5nC72W169AOJJN94cbN1',
    'teacher_getui_iPhone_mastersecret' => 'k9vhjhSQCw8KxeVU5Eoq55',
    //老师的iphone的个推设置dev
    'teacher_getui_iPhone_appid_dev' => 'mUBu5s5Im36OSnCQKyvt67',
    'teacher_getui_iPhone_appkey_dev' => 'hs5nC72W169AOJJN94cbN1',
    'teacher_getui_iPhone_mastersecret_dev' => 'k9vhjhSQCw8KxeVU5Eoq55',

    'teacher_getui_appid_dev' => 'MRSVW7bFsU9jgHv8gqGX42',
    'teacher_getui_appkey_dev' => '4ngFvZ1lVj73CVxn9BGJy9',
    'teacher_getui_mastersecret_dev' => 'AVdP83C74T9UNE6W9eljB5',
    'student_getui_appid_dev' => 'YkrFhAzlZa81gE4SWy8pW1',
    'student_getui_appkey_dev' => 'lFfBq6ibLW7kTFI0iClh05',
    'student_getui_mastersecret_dev' => 'ItLIJPkrbC6DptrcXbAZa1',
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
        'host' => '172.16.3.207',
        'port' => '5672',
        'login' => 'mqadmin',
        'password' => 'mqadmin_2017'
    ),

    /************************************ qiniu_config ****************************************/

    'qiniuAccessKey' => 'tM193uNBWVubyf1od06tTI50euAd31tOOg3GXsA4',
    'qiniuSecretKey' => 'U7pSQxlKAq8sMDXFC2wFH53mdvPR9mG9gzgYRVjq',

    'pnl_audio_bucket' => 'pnl-audio',
    'pnl_book_bucket' => 'pnl-book',
    'pnl_book_audio_bucket' => 'pnl-book-audio',
    'pnl_doc_bucket' => 'pnl-doc',
    'pnl_logs_bucket' => 'pnl-logs',
    'pnl_static_bucket' => 'pnl-static',
    'pnl_video_bucket' => 'pnl-video',
    'vip_static_bucket' => 'vip-static',
    'vip_video_bucket' => 'vip-video',

    'pnl_audio_path' => 'http://audio.pnlyy.com/',
    'pnl_book_path' => 'http://book.static.pnlyy.com/',
    'pnl_book_audio_path' => 'http://book.audio.pnlyy.com/',
    'pnl_doc_path' => 'http://doc.pnlyy.com/',
    'pnl_static_path' => 'http://static.pnlyy.com/',
    'pnl_video_path' => 'http://video.pnlyy.com/',
    'vip_static_path' => 'http://vip-static.pnlyy.com/',
    'vip_video_path' => 'http://vip-video.pnlyy.com/',

    'pic_size_1000' => '!1000',
    'pic_size_1500' => '!1500',
    'pic_size_200' => '!200',
    
    //七牛上传乐谱压缩策略
    'image_slim_fop' => 'imageView2/0/w/1200/format/jpg/q/60|imageslim',

    /************************************ 公众号 ********************************************/

                                        /* VIP陪练 */

    'wechat_mch_id' => '1231569802',
    'wechat_mch_secret' => 'B944BB5C489B45620CF106D2F23EC788',
    'wechat_app_id' => 'wxcdef6dd053995bc7',
    'pem_root' =>  dirname(dirname(__DIR__)) . '/web/cert_api',

    //我的专属客服事件
    'student_key_personal_sales' => 'PERSONAL_SALES',
    //我的推荐事件
    'student_key_promotion' => 'STUDENT_PROMOTION',

    //问题处理结果反馈
    'student_template_feedback' => '4Rmzj_vNA8HReWbYzL1HxqAizA0BpYPFnJgB3Zqwiko',
    //课时到账通知
    'student_template_class_income' => 'EHsUv53GZ2t8jj1g1zXuh2z1JodmRoSXakMaetro5ps',
    //个人消息通知
    'student_template_personal' => 'Q4kpcnrrWt7i7vTtt9SD-6xTnf4EPkShkNg4gMJStkA',
    //服务评价通知
    'student_template_service_comment' => 'RzFMSSINSNK-rqNST1Wv1ThmNKCdwEfVcBSJPF3dyew',
    //完成任务提醒
    'student_template_task_complete' => 'XJBY9q8n-zr13cHxN1FP1u_SI7NX2B0uZ088nnQj0Ms',
    //课程通知
    'student_template_class_alarm' => 'XnMawZhYrM5gjqfIKq7OTZP5jspfA5jeuzy9RJmZWCI',
    //客服通知提醒
    'student_template_kefu_message' => 'c-dGAYihOoqTChjN8qr_GcDPmUaswa_oa_4MdnMOuzQ',
    //上课提醒
    'student_template_class_alert' => 'ei4ouznxLpOLrDtcUd-l781qv2ldBi3Cu6rF6SsSjbI',
    //预约成功通知
    'student_template_class_order' => 't7jhwMLFZ40hOipWUUuVcigWxd0bDQRTIuM9eFpoILk',


                                        /* VIP微课 */

    'sales_mch_id' => '1254775201',
    'sales_app_id' => 'wx4384ef5fb33ba448',
    'sales_mch_secret' => 'B944BB5C489B45620CF106D2F23EC788',
    'sales_pem_root' => dirname(dirname(__DIR__)).'/web/cert',

    'channel_key_recommend' => 'K003_MY_RECOMMEND',
    'channel_key_personal' => 'K003_PERSONAL_SERVICE',

    //预约成功通知
    'channel_template_class_success' => '05oUiFi9f3gv0avnjWfAnR5oZl1FOSrnExICKzSp6So',
    //待办事项提醒
    'channel_template_todo' => 'KdgB9LPCikLMIA3WCsVphJBwIjzuPww5EZO0yiHCGD8',
    //收入提醒
    'channel_template_income' => 'fgQE6M-zA-yxjP0c8mo3sM_i23WLHCys7DX7CSUYcAk',
    //问题处理结果反馈
    'channel_template_feedback' => 'hB_cnpDH7weIdjS2Y3Igghu24C7ZC7qqp1YaCXXsU9M',
    //购买成功通知
    'channel_template_purchase' => 'qEv7BQgG0e5sPKFG4OpfdwMlicHpOn1cIv9VYshcp6I',
    //学习完成通知
    'channel_template_study_complete' => 'z9KBCRtTKe-ftI0GCkWOKADp3BojIWEP27h2RaU34vU',


                                        /* 妙克艺术人才基地 */
    
    //排课通知
    'teacher_template_class_arrange' => 'htBAAUCKEbMJc2Cuk-HHLxXK2YNNjVXZkYL1LaSOZwg',
    //调课成功通知
    'teacher_template_class_edit' => 'V-_Omxuw8RX2ZxnypTdVEHkiwLUQK3BGOySrLcWp0Zc',
    //课程取消通知
    'teacher_template_class_cancel' => 'D7I9cY9rDbd1WBAxJT24Qf03HxK_X4PhYlQFg9ITYHU',
    //课程提醒通知
    'teacher_template_class_alert' => 'eg0C9ZF3IOkV8XKqHhGqKhdYb9ZDOWOxDupom_w8Aec',
    //薪资确认提醒
    'teacher_template_salary_confirm' => 'iaS1cZblopNNtkfUUv9DaKPSIjYUWTFXStPWhv6JYwM',


    /************************************ base_url ********************************************/
    //渠道公众号项目前端域名base_url
    'channel_frontend_url' => 'http://webchannel.pnlyy.com/',
    'student_zh_domain' => 'http://wx.pnlyy.com/',
    'channel_zh_domain' => 'http://channel.pnlyy.cn/',
    'teacher_zh_domain' => 'http://tiantuan.pnlyy.com/',

    'crm_domain' => 'http://crm.pnlyy.com/',
    'opt_domain' => 'http://opt.pnlyy.com/',
    'api_domain' => 'http://api.pnlyy.com/',
    'teacher_domain' => 'http://teacher.pnlyy.com/',

    /*准备弃用*/
    'api_url' => 'http://api.pnlyy.com',
    'base_url' => 'http://wx.pnlyy.com/',
    'class_url' => 'http://crm.pnlyy.com/class_record.html?id=',
    'imageUrlPrefix' => 'http://opt.pnlyy.com',
    'myclass_url' => 'http://wx.pnlyy.com/class/self-class-index',
    'sales_url' => 'http://wx.pnlyy.com/student/show-adviser',
    'record_path' => 'http://wx.pnlyy.com/class/record-detail?is_student=0&record_id=',
    'channel_base_url' => 'http://channel.pnlyy.cn/',
    'free_class_url' => 'http://wx.pnlyy.com/student/intro',


    /************************************ swoole_config ****************************************/

    'swoole_port' => 'http://10.173.226.163:9502',


    /*************************************** others ********************************************/

    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'user.passwordResetTokenExpire' => 3600,

    'corp_id' => 'wxf39085431e55e51f',
    'corp_secret' => 'ckZ-vtPNCmNygvPfnqcddM9G3ClFWKZjxs0sYP8_HU5gKgrti_xFRZgPDr5mzGAa',
    'department_teacher_id' => '9',
    //拉新奖励人数
    'reward_pull_num' =>18,
];
