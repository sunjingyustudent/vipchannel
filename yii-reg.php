<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/26
 * Time: 上午10:33
 */

/***********************************  Logic组件  ***********************************************/
//操作课程组件
Yii::$container->set('common\\logics\\classes\\IOperation', 'common\\logics\\classes\\OperationLogic');
Yii::$container->set('classOperationService','common\\logics\\classes\\IOperation');

//渠道组件
Yii::$container->set('common\\logics\\channel\\IChannel', 'common\\logics\\channel\\ChannelLogic');
Yii::$container->set('channelService','common\\logics\\channel\\IChannel');

/***********************************  DataAccess组件  *****************************************/


//用户组件
Yii::$container->set('common\\sources\\read\\student\\IStudentAccess', 'common\\sources\\read\\student\\StudentAccess');
Yii::$container->set('RStudentAccess','common\\sources\\read\\student\\IStudentAccess');

Yii::$container->set('common\\sources\\write\\student\\IStudentAccess', 'common\\sources\\write\\student\\StudentAccess');
Yii::$container->set('WStudentAccess','common\\sources\\write\\student\\IStudentAccess');

//老师组件
Yii::$container->set('common\\sources\\read\\teacher\\ITeacherAccess', 'common\\sources\\read\\teacher\\TeacherAccess');
Yii::$container->set('RTeacherAccess','common\\sources\\read\\teacher\\ITeacherAccess');

Yii::$container->set('common\\sources\\write\\teacher\\ITeacherAccess', 'common\\sources\\read\\teacher\\TeacherAccess');
Yii::$container->set('WTeacherAccess','common\\sources\\write\\teacher\\ITeacherAccess');

//渠道组件
Yii::$container->set('common\\sources\\read\\channel\\IChannelAccess', 'common\\sources\\read\\channel\\ChannelAccess');
Yii::$container->set('RChannelAccess','common\\sources\\read\\channel\\IChannelAccess');

Yii::$container->set('common\\sources\\write\\channel\\IChannelAccess', 'common\\sources\\write\\channel\\ChannelAccess');
Yii::$container->set('WChannelAccess','common\\sources\\write\\channel\\IChannelAccess');

//课程组件
Yii::$container->set('common\\sources\\read\\classes\\IClassAccess', 'common\\sources\\read\\classes\\ClassAccess');
Yii::$container->set('RClassAccess','common\\sources\\read\\classes\\IClassAccess');

Yii::$container->set('common\\sources\\write\\classes\\IClassAccess', 'common\\sources\\write\\classes\\ClassAccess');
Yii::$container->set('WClassAccess','common\\sources\\write\\classes\\IClassAccess');

//聊天组件
Yii::$container->set('common\\sources\\read\\chat\\IChatAccess', 'common\\sources\\read\\chat\\ChatAccess');
Yii::$container->set('RChatAccess','common\\sources\\read\\chat\\IChatAccess');

Yii::$container->set('common\\sources\\write\\chat\\IChatAccess', 'common\\sources\\write\\chat\\ChatAccess');
Yii::$container->set('WChatAccess','common\\sources\\write\\chat\\IChatAccess');

//订单组件
Yii::$container->set('common\\sources\\read\\order\\IOrderAccess', 'common\\sources\\read\\order\\OrderAccess');
Yii::$container->set('ROrderAccess','common\\sources\\read\\order\\IOrderAccess');

Yii::$container->set('common\\sources\\write\\order\\IOrderAccess', 'common\\sources\\write\\order\\OrderAccess');
Yii::$container->set('WOrderAccess','common\\sources\\write\\order\\IOrderAccess');

//商品组件
Yii::$container->set('common\\sources\\read\\product\\IProductAccess', 'common\\sources\\read\\product\\ProductAccess');
Yii::$container->set('RProductAccess','common\\sources\\read\\product\\IProductAccess');

Yii::$container->set('common\\sources\\write\\product\\IProductAccess', 'common\\sources\\read\\product\\ProductAccess');
Yii::$container->set('WProductAccess','common\\sources\\write\\product\\IProductAccess');

//投诉组件
Yii::$container->set('common\\sources\\read\\complain\\IComplainAccess', 'common\\sources\\read\\complain\\ComplainAccess');
Yii::$container->set('RComplainAccess','common\\sources\\read\\complain\\IComplainAccess');

Yii::$container->set('common\\sources\\write\\complain\\IComplainAccess', 'common\\sources\\write\\complain\\ComplainAccess');
Yii::$container->set('WComplainAccess','common\\sources\\write\\complain\\IComplainAccess');

//回访组件
Yii::$container->set('common\\sources\\read\\visit\\IVisitAccess', 'common\\sources\\read\\visit\\VisitAccess');
Yii::$container->set('RVisitAccess','common\\sources\\read\\visit\\IVisitAccess');

Yii::$container->set('common\\sources\\write\\visit\\IVisitAccess', 'common\\sources\\write\\visit\\VisitAccess');
Yii::$container->set('WVisitAccess','common\\sources\\write\\visit\\IVisitAccess');

//乐谱组件
Yii::$container->set('common\\sources\\read\\music\\IMusicAccess', 'common\\sources\\read\\music\\MusicAccess');
Yii::$container->set('RMusicAccess','common\\sources\\read\\music\\IMusicAccess');

Yii::$container->set('common\\sources\\write\\music\\IMusicAccess', 'common\\sources\\write\\music\\MusicAccess');
Yii::$container->set('WMusicAccess','common\\sources\\write\\music\\IMusicAccess');

//课单组件
Yii::$container->set('common\\sources\\read\\classes\\IRecordAccess', 'common\\sources\\read\\classes\\RecordAccess');
Yii::$container->set('RRecordAccess','common\\sources\\read\\classes\\IRecordAccess');

Yii::$container->set('common\\sources\\write\\classes\\IRecordAccess', 'common\\sources\\write\\classes\\RecordAccess');
Yii::$container->set('WRecordAccess','common\\sources\\write\\classes\\IRecordAccess');

//员工账号组件
Yii::$container->set('common\\sources\\read\\account\\IAccountAccess', 'common\\sources\\read\\account\\AccountAccess');
Yii::$container->set('RAccountAccess','common\\sources\\read\\account\\IAccountAccess');

Yii::$container->set('common\\sources\\write\\account\\IAccountAccess', 'common\\sources\\write\\account\\AccountAccess');
Yii::$container->set('WAccountAccess','common\\sources\\write\\account\\IAccountAccess');

//老师动态配置组件
Yii::$container->set('common\\sources\\read\\teacher\\IRuleAccess', 'common\\sources\\read\\teacher\\RuleAccess');
Yii::$container->set('RRuleAccess','common\\sources\\read\\teacher\\IRuleAccess');

//老师课时费组件
Yii::$container->set('common\\sources\\read\\salary\\IWorkhourAccess', 'common\\sources\\read\\salary\\WorkhourAccess');
Yii::$container->set('RWorkhourAccess','common\\sources\\read\\salary\\IWorkhourAccess');

Yii::$container->set('common\\sources\\write\\salary\\IWorkhourAccess', 'common\\sources\\write\\salary\\WorkhourAccess');
Yii::$container->set('WWorkhourAccess','common\\sources\\write\\salary\\IWorkhourAccess');

//老师底薪组件
Yii::$container->set('common\\sources\\read\\salary\\IBasepayAccess', 'common\\sources\\read\\salary\\BasepayAccess');
Yii::$container->set('RBasepayAccess','common\\sources\\read\\salary\\IBasepayAccess');

Yii::$container->set('common\\sources\\write\\salary\\IBasepayAccess', 'common\\sources\\write\\salary\\BasepayAccess');
Yii::$container->set('WBasepayAccess','common\\sources\\write\\salary\\IBasepayAccess');

//老师上课时间组件
Yii::$container->set('common\\sources\\read\\teacher\\IWorktimeAccess', 'common\\sources\\read\\teacher\\WorktimeAccess');
Yii::$container->set('RWorktimeAccess','common\\sources\\read\\teacher\\IWorktimeAccess');

Yii::$container->set('common\\sources\\write\\teacher\\IWorktimeAccess', 'common\\sources\\write\\teacher\\WorktimeAccess');
Yii::$container->set('WWorktimeAccess','common\\sources\\write\\teacher\\IWorktimeAccess');

//老师请假组件
Yii::$container->set('common\\sources\\read\\teacher\\IRestAccess', 'common\\sources\\read\\teacher\\RestAccess');
Yii::$container->set('RRestAccess','common\\sources\\read\\teacher\\IRestAccess');

Yii::$container->set('common\\sources\\write\\teacher\\IRestAccess', 'common\\sources\\write\\teacher\\RestAccess');
Yii::$container->set('WRestAccess','common\\sources\\write\\teacher\\IRestAccess');