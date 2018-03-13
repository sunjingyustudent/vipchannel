<?php
/**
 * 仅供数据多类型配置
 */
return [
    //用户类型sales_channel.message_type
    'message_type' => [
        1 => '新用户',
        2 => '有推广价值的用户',
        3 => '无推广价值的用户',
    ],

    //渠道类型
    'channel_type' => [
        1 => '无渠道',
        2 => '老师渠道',
        3 => '转介绍渠道',
        4 => '活动渠道',
    ],
    //用户身份sales_channel.user_type
    'user_type' => [
        1 => '任课老师',
        2 => '家长',
        3 => '其他'
    ],
];
