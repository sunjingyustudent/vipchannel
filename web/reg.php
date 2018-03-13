<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/12
 * Time: 下午6:27
 */


/***********************************  Logic组件  ***********************************************/

//销售新签组件
Yii::$container->set('common\\logics\\sale\\ISale', 'common\\logics\\sale\\SaleLogic');
Yii::$container->set('saleService', 'common\\logics\\sale\\ISale');

//销售复购组件
Yii::$container->set('common\\logics\\sale\\IPurchase', 'common\\logics\\sale\\PurchaseLogic');
Yii::$container->set('purchaseService', 'common\\logics\\sale\\IPurchase');

//销售管理组件
Yii::$container->set('common\\logics\\sale\\IManage', 'common\\logics\\sale\\ManageLogic');
Yii::$container->set('manageService', 'common\\logics\\sale\\IManage');

//学生组件
Yii::$container->set('common\\logics\\student\\IStudent', 'common\\logics\\student\\StudentLogic');
Yii::$container->set('studentService', 'common\\logics\\student\\IStudent');

//课程组件
Yii::$container->set('common\\logics\\classes\\IClasses', 'common\\logics\\classes\\ClassesLogic');
Yii::$container->set('classesService', 'common\\logics\\classes\\IClasses');

//回访组件
Yii::$container->set('common\\logics\\visit\\IVisit', 'common\\logics\\visit\\VisitLogic');
Yii::$container->set('visitService', 'common\\logics\\visit\\IVisit');

//投诉组件
Yii::$container->set('common\\logics\\complain\\IComplain', 'common\\logics\\complain\\ComplainLogic');
Yii::$container->set('complainService', 'common\\logics\\complain\\IComplain');

//订单组件
Yii::$container->set('common\\logics\\order\\IOrder', 'common\\logics\\order\\OrderLogic');
Yii::$container->set('orderService', 'common\\logics\\order\\IOrder');

//乐谱组件
Yii::$container->set('common\\logics\\music\\IMusic', 'common\\logics\\music\\MusicLogic');
Yii::$container->set('musicService', 'common\\logics\\music\\IMusic');

//课单组件
Yii::$container->set('common\\logics\\classes\\IRecord', 'common\\logics\\classes\\RecordLogic');
Yii::$container->set('recordService', 'common\\logics\\classes\\IRecord');

//操作课程组件
Yii::$container->set('common\\logics\\classes\\IOperation', 'common\\logics\\classes\\OperationLogic');
Yii::$container->set('classOperationService', 'common\\logics\\classes\\IOperation');

//课程监控组件
Yii::$container->set('common\\logics\\classes\\IMonitor', 'common\\logics\\classes\\MonitorLogic');
Yii::$container->set('monitorService', 'common\\logics\\classes\\IMonitor');

//员工账号组件
Yii::$container->set('common\\logics\\account\\IAccount', 'common\\logics\\account\\AccountLogic');
Yii::$container->set('accountService', 'common\\logics\\account\\IAccount');

//聊天组件
Yii::$container->set('common\\logics\\chat\\IChat', 'common\\logics\\chat\\ChatLogic');
Yii::$container->set('chatService', 'common\\logics\\chat\\IChat');

//老师静态属性组件
Yii::$container->set('common\\logics\\teacher\\ITeacher', 'common\\logics\\teacher\\TeacherLogic');
Yii::$container->set('teacherService', 'common\\logics\\teacher\\ITeacher');

//老师课时费计算组件
Yii::$container->set('common\\logics\\salary\\IWorkhour', 'common\\logics\\salary\\WorkhourLogic');
Yii::$container->set('workhourService', 'common\\logics\\salary\\IWorkhour');

//公众号模版消息推送组件
Yii::$container->set('common\\logics\\push\\ITemplate', 'common\\logics\\push\\TemplateLogic');
Yii::$container->set('templateService', 'common\\logics\\push\\ITemplate');

//课程乐谱组件
Yii::$container->set('common\\logics\\classes\\ICourse', 'common\\logics\\classes\\CourseLogic');
Yii::$container->set('courseService', 'common\\logics\\classes\\ICourse');

//学生固定课组件
Yii::$container->set('common\\logics\\student\\IFixtime', 'common\\logics\\student\\FixtimeLogic');
Yii::$container->set('fixtimeService', 'common\\logics\\student\\IFixtime');

//老师根据奖惩规则计算组件
Yii::$container->set('common\\compute\\ISalaryCompute', 'common\\compute\\SalaryCompute');
Yii::$container->set('salaryCompute', 'common\\compute\\ISalaryCompute');

//商品组件
Yii::$container->set('common\\logics\\product\\IProduct', 'common\\logics\\product\\ProductLogic');
Yii::$container->set('productService', 'common\\logics\\product\\IProduct');

//自动分配销售组件
Yii::$container->set('common\\logics\\sale\\IDistribution', 'common\\logics\\sale\\DistributionLogic');
Yii::$container->set('distributionService', 'common\\logics\\sale\\IDistribution');

//客服消息组件
Yii::$container->set('common\\logics\\push\\IMessage', 'common\\logics\\push\\MessageLogic');
Yii::$container->set('messageService', 'common\\logics\\push\\IMessage');

//渠道组件
Yii::$container->set('common\\logics\\channel\\IChannel', 'common\\logics\\channel\\ChannelLogic');
Yii::$container->set('channelService', 'common\\logics\\channel\\IChannel');

//老师奖励组件
Yii::$container->set('common\\logics\\salary\\IReward', 'common\\logics\\salary\\RewardLogic');
Yii::$container->set('rewardService', 'common\\logics\\salary\\IReward');

//渠道聊天组件
Yii::$container->set('common\\logics\\chat\\IChannelChat', 'common\\logics\\chat\\ChannelChatLogic');
Yii::$container->set('channelChatService', 'common\\logics\\chat\\IChannelChat');

//api组件
Yii::$container->set('common\\logics\\api\\IApi', 'common\\logics\\api\\ApiLogic');
Yii::$container->set('apiService', 'common\\logics\\api\\IApi');

/***********************************  DataAccess组件  *****************************************/

//用户组件
Yii::$container->set('common\\sources\\read\\student\\IStudentAccess', 'common\\sources\\read\\student\\StudentAccess');
Yii::$container->set('RStudentAccess', 'common\\sources\\read\\student\\IStudentAccess');

Yii::$container->set('common\\sources\\write\\student\\IStudentAccess', 'common\\sources\\write\\student\\StudentAccess');
Yii::$container->set('WStudentAccess', 'common\\sources\\write\\student\\IStudentAccess');

//老师组件
Yii::$container->set('common\\sources\\read\\teacher\\ITeacherAccess', 'common\\sources\\read\\teacher\\TeacherAccess');
Yii::$container->set('RTeacherAccess', 'common\\sources\\read\\teacher\\ITeacherAccess');

Yii::$container->set('common\\sources\\write\\teacher\\ITeacherAccess', 'common\\sources\\read\\teacher\\TeacherAccess');
Yii::$container->set('WTeacherAccess', 'common\\sources\\write\\teacher\\ITeacherAccess');

//渠道组件
Yii::$container->set('common\\sources\\read\\channel\\IChannelAccess', 'common\\sources\\read\\channel\\ChannelAccess');
Yii::$container->set('RChannelAccess', 'common\\sources\\read\\channel\\IChannelAccess');

Yii::$container->set('common\\sources\\write\\channel\\IChannelAccess', 'common\\sources\\write\\channel\\ChannelAccess');
Yii::$container->set('WChannelAccess', 'common\\sources\\write\\channel\\IChannelAccess');

//课程组件
Yii::$container->set('common\\sources\\read\\classes\\IClassAccess', 'common\\sources\\read\\classes\\ClassAccess');
Yii::$container->set('RClassAccess', 'common\\sources\\read\\classes\\IClassAccess');

Yii::$container->set('common\\sources\\write\\classes\\IClassAccess', 'common\\sources\\write\\classes\\ClassAccess');
Yii::$container->set('WClassAccess', 'common\\sources\\write\\classes\\IClassAccess');

//聊天组件
Yii::$container->set('common\\sources\\read\\chat\\IChatAccess', 'common\\sources\\read\\chat\\ChatAccess');
Yii::$container->set('RChatAccess', 'common\\sources\\read\\chat\\IChatAccess');

Yii::$container->set('common\\sources\\write\\chat\\IChatAccess', 'common\\sources\\write\\chat\\ChatAccess');
Yii::$container->set('WChatAccess', 'common\\sources\\write\\chat\\IChatAccess');

//渠道聊天组件
Yii::$container->set('common\\sources\\read\\chat\\IChannelChatAccess', 'common\\sources\\read\\chat\\ChannelChatAccess');
Yii::$container->set('RChannelChatAccess', 'common\\sources\\read\\chat\\IChannelChatAccess');

Yii::$container->set('common\\sources\\write\\chat\\IChannelChatAccess', 'common\\sources\\write\\chat\\ChannelChatAccess');
Yii::$container->set('WChannelChatAccess', 'common\\sources\\write\\chat\\IChannelChatAccess');

//订单组件
Yii::$container->set('common\\sources\\read\\order\\IOrderAccess', 'common\\sources\\read\\order\\OrderAccess');
Yii::$container->set('ROrderAccess', 'common\\sources\\read\\order\\IOrderAccess');

Yii::$container->set('common\\sources\\write\\order\\IOrderAccess', 'common\\sources\\write\\order\\OrderAccess');
Yii::$container->set('WOrderAccess', 'common\\sources\\write\\order\\IOrderAccess');

//商品组件
Yii::$container->set('common\\sources\\read\\product\\IProductAccess', 'common\\sources\\read\\product\\ProductAccess');
Yii::$container->set('RProductAccess', 'common\\sources\\read\\product\\IProductAccess');

Yii::$container->set('common\\sources\\write\\product\\IProductAccess', 'common\\sources\\read\\product\\ProductAccess');
Yii::$container->set('WProductAccess', 'common\\sources\\write\\product\\IProductAccess');

//投诉组件
Yii::$container->set('common\\sources\\read\\complain\\IComplainAccess', 'common\\sources\\read\\complain\\ComplainAccess');
Yii::$container->set('RComplainAccess', 'common\\sources\\read\\complain\\IComplainAccess');

Yii::$container->set('common\\sources\\write\\complain\\IComplainAccess', 'common\\sources\\write\\complain\\ComplainAccess');
Yii::$container->set('WComplainAccess', 'common\\sources\\write\\complain\\IComplainAccess');

//回访组件
Yii::$container->set('common\\sources\\read\\visit\\IVisitAccess', 'common\\sources\\read\\visit\\VisitAccess');
Yii::$container->set('RVisitAccess', 'common\\sources\\read\\visit\\IVisitAccess');

Yii::$container->set('common\\sources\\write\\visit\\IVisitAccess', 'common\\sources\\write\\visit\\VisitAccess');
Yii::$container->set('WVisitAccess', 'common\\sources\\write\\visit\\IVisitAccess');

//乐谱组件
Yii::$container->set('common\\sources\\read\\music\\IMusicAccess', 'common\\sources\\read\\music\\MusicAccess');
Yii::$container->set('RMusicAccess', 'common\\sources\\read\\music\\IMusicAccess');

Yii::$container->set('common\\sources\\write\\music\\IMusicAccess', 'common\\sources\\write\\music\\MusicAccess');
Yii::$container->set('WMusicAccess', 'common\\sources\\write\\music\\IMusicAccess');

//课单组件
Yii::$container->set('common\\sources\\read\\classes\\IRecordAccess', 'common\\sources\\read\\classes\\RecordAccess');
Yii::$container->set('RRecordAccess', 'common\\sources\\read\\classes\\IRecordAccess');

Yii::$container->set('common\\sources\\write\\classes\\IRecordAccess', 'common\\sources\\write\\classes\\RecordAccess');
Yii::$container->set('WRecordAccess', 'common\\sources\\write\\classes\\IRecordAccess');

//员工账号组件
Yii::$container->set('common\\sources\\read\\account\\IAccountAccess', 'common\\sources\\read\\account\\AccountAccess');
Yii::$container->set('RAccountAccess', 'common\\sources\\read\\account\\IAccountAccess');

Yii::$container->set('common\\sources\\write\\account\\IAccountAccess', 'common\\sources\\write\\account\\AccountAccess');
Yii::$container->set('WAccountAccess', 'common\\sources\\write\\account\\IAccountAccess');

//老师动态配置组件
Yii::$container->set('common\\sources\\read\\teacher\\IRuleAccess', 'common\\sources\\read\\teacher\\RuleAccess');
Yii::$container->set('RRuleAccess', 'common\\sources\\read\\teacher\\IRuleAccess');

//老师课时费组件
Yii::$container->set('common\\sources\\read\\salary\\IWorkhourAccess', 'common\\sources\\read\\salary\\WorkhourAccess');
Yii::$container->set('RWorkhourAccess', 'common\\sources\\read\\salary\\IWorkhourAccess');

Yii::$container->set('common\\sources\\write\\salary\\IWorkhourAccess', 'common\\sources\\write\\salary\\WorkhourAccess');
Yii::$container->set('WWorkhourAccess', 'common\\sources\\write\\salary\\IWorkhourAccess');

//老师底薪组件
Yii::$container->set('common\\sources\\read\\salary\\IBasepayAccess', 'common\\sources\\read\\salary\\BasepayAccess');
Yii::$container->set('RBasepayAccess', 'common\\sources\\read\\salary\\IBasepayAccess');

Yii::$container->set('common\\sources\\write\\salary\\IBasepayAccess', 'common\\sources\\write\\salary\\BasepayAccess');
Yii::$container->set('WBasepayAccess', 'common\\sources\\write\\salary\\IBasepayAccess');


// 推送消息组件
Yii::$container->set('common\\sources\\write\\push\\IPushAccess', 'common\\sources\\write\\push\\PushAccess');
Yii::$container->set('WPushAccess', 'common\\sources\\write\\push\\IPushAccess');

//奖励组件
Yii::$container->set('common\\sources\\read\\salary\\IRewardAccess', 'common\\sources\\read\\salary\\RewardAccess');
Yii::$container->set('RRewardAccess', 'common\\sources\\read\\salary\\IRewardAccess');

Yii::$container->set('common\\sources\\write\\salary\\IRewardAccess', 'common\\sources\\write\\salary\\RewardAccess');
Yii::$container->set('WRewardAccess', 'common\\sources\\write\\salary\\IRewardAccess');

//工作组件
Yii::$container->set('common\\sources\\read\\teacher\\IWorktimeAccess', 'common\\sources\\read\\teacher\\WorktimeAccess');
Yii::$container->set('RWorktimeAccess', 'common\\sources\\read\\teacher\\IWorktimeAccess');

Yii::$container->set('common\\sources\\write\\teacher\\IWorktimeAccess', 'common\\sources\\write\\teacher\\WorktimeAccess');
Yii::$container->set('WWorktimeAccess', 'common\\sources\\write\\teacher\\IWorktimeAccess');

//api组件
Yii::$container->set('common\\logics\\api\\IApi', 'common\\logics\\api\\ApiLogic');
Yii::$container->set('apiService', 'common\\logics\\api\\IApi');
