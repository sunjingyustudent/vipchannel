<?php
use yii\helpers\Html;
use yii\widgets\LinkPager;

$this->title = "CHANNEL Portal";
$layout=2;   //2 代表  列表组（客户档案信息，客户回访信息 ，排课信息）  1 显示一个标题
?>


<div class="body-menu">
    <div class="logoInfo">
        <img src="/images/icon.png"/>
        <span class="title"> CHANNEL Portal </span>
        <a id="hideMenu" href="javascript:void(0);">
            <span class="fa fa-dedent"></span>
        </a>
    </div>
    <div class="menu-list">
        <p class="pages">Menu</p>
        <ul class="list">
            <?php if (Yii::$app->user->identity->role == 2) : ?>
                <li data-toggle="tooltip" title="全部用户" >
                    <a href="javascript:void(0);" class="all-user">
                        <i class="fa fa-user p1"></i>
                        <span>全部用户</span>
                        <i class="fa fa-chevron-right p2"></i>
                    </a>
                </li>
                <li data-toggle="tooltip" title="员工管理" >
                    <a href="javascript:void(0);" class="employe">
                        <i class="fa fa-briefcase p1"></i>
                        <span>员工管理</span>
                        <i class="fa fa-chevron-right p2"></i>
                    </a>
                </li>

                <li data-toggle="tooltip" title="转渠道列表" >
                    <a href="javascript:void(0);" class="channel_transfer_list">
                        <i class="fa fa-th-list p1"></i>
                        <span>转渠道列表</span>
                        <i class="fa fa-chevron-right p2"></i>
                    </a>
                </li>
                <li data-toggle="tooltip" title="未读消息统计" >
                    <a href="javascript:void(0);" class="wait_statistics">
                        <i class="fa fa-edit p1"></i>
                        <span>未读消息统计</span>
                        <i class="fa fa-chevron-right p2"></i>
                    </a>
                </li>
                <li data-toggle="tooltip" title="羊毛党列表" >
                    <a href="javascript:void(0);" class="wool_party">
                        <i class="fa fa-scissors p1"></i>
                        <span>羊毛党列表</span>
                        <i class="fa fa-chevron-right p2"></i>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (Yii::$app->user->identity->role == 5) : ?>
                <li data-toggle="tooltip" title="用户名单" >
                    <a href="javascript:void(0);" class="user-list">
                        <i class="fa fa-user p1"></i>
                        <span>用户名单</span>
                        <i class="fa fa-chevron-right p2"></i>
                    </a>
                </li>
            <?php endif; ?>

            <li data-toggle="tooltip" title="推广效果" >
                <a href="javascript:void(0);" class="promotion-effect">
                    <i class="fa fa-trophy p1"></i>
                    <span>推广效果</span>
                    <i class="fa fa-chevron-right p2"></i>
                </a>
            </li>
            <li data-toggle="tooltip" title="体验课报表" >
                <a href="javascript:void(0);" class="ex-class-report">
                    <i class="fa fa-list-alt p1"></i>
                    <span>体验报表</span>
                    <i class="fa fa-chevron-right p2"></i>
                </a>
            </li>

            <li data-toggle="tooltip" title="专属服务" >
                <a href="javascript:void(0);" class="personal-server">
                    <i class="fa fa-bell p1"></i>
                    <span>专属服务</span>
                    <i class="fa fa-chevron-right p2"></i>
                </a>
            </li>

            <li data-toggle="tooltip" title="月月活动奖励明细" >
                <a href="javascript:void(0);" class="month-gift">
                    <i class="fa fa-gift p1"></i>
                    <span>月月活动奖励明细</span>
                    <i class="fa fa-chevron-right p2"></i>
                </a>
            </li>
        </ul>
    </div>
</div>

<div class="body-container">
    <div class="body-header">
        <div class="quickTool">
            <a href="javascript:void(0);">
                <span class="fa fa-arrows-alt"></span>
            </a>
            <a href="javascript:void(0);">
                <span style="font-size: 20px;" class="fa fa-comments"></span>
            </a>
            <a href="javascript:void(0);" style="display: none;">
                <span class="fa fa-bell"></span>
            </a>
        </div>

        <div class="navInfo">
            <a href="javascript:void(0);" id="all-user">
                <span class="label label-default" id="all-wait"> 全部消息</span>
            </a>

            <a href="javascript:void(0);" id="user-of-the-day">
                <span class="label label-success" >当日关注用户</span>
            </a>

            <a href="javascript:void(0);" id="new-wait">
                <span class="label label-success" > 新用户消息</span>
            </a>

            <a href="javascript:void(0);" id="worth-wait">
                <span class="label label-primary" >推广用户消息</span>
            </a>

            <a href="javascript:void(0);" id="withdrawals-wait">
                <span class="label label-primary" >提现申请消息</span>
            </a>

            <a href="javascript:void(0);" id="no-worth-wait">
                <span class="label label-primary" >无价值用户消息</span>
            </a>

            <?php if (Yii::$app->user->identity->role == 5) : ?>
                <a href="#"  id="todo-stuff">
                    <span class="label label-primary">待跟进名单</span>
                </a>
            <?php endif; ?>
        </div>

        <div class="userInfo" style="top:0px;">
            <span class="fa fa-circle userPrompt"></span>
            <img src="<?=empty(Yii::$app->user->identity->head) ? '/images/avatar.png' : Yii::$app->user->identity->head ?>"/>
            <?= Yii::$app->user->identity->nickname ?>
            <span class="fa fa-chevron-down grey"></span>
        </div>

        <div class="configMenu" style="top:0px;">
            <span class="fa fa-list"></span>
        </div>

        <ul class="userConfig">
            <li><a href="#" id="online"><span class="fa fa-circle green"></span> &nbsp;在线</a></li>
            <li><a href="#" id="offline"><span class="fa fa-circle gray"></span> &nbsp;离线</a></li>
            <li class="line"></li>
            <li><a href="#" id="head"><span class="fa fa-header"></span> &nbsp;更换头像</a></li>
            <li class="line"></li>
            <?php if (empty(Yii::$app->user->identity->channel_code)) : ?>
                <li class="channel_qr"><a href="#" id="channel_code"><span class="fa fa-circle gray"></span> &nbsp;生成专属拉新二维码</a></li>
            <?php else : ?>
                <li class="channel_qr"><a href="#" id="channel_code_show"><span class="fa fa-circle green"></span> &nbsp;专属拉新二维码</a></li>
            <?php endif; ?>
            <li class="line"></li>
            <li><a id="passwd" href="javascript:void(0);"><span class="fa fa-unlock-alt"></span> &nbsp;修改密码</a></li>
            <li><a href="/site/logout"><span class="fa fa-power-off"></span> &nbsp;退出登录</a></li>
        </ul>
    </div>

    <div class="body-content">
        <div class="header-bg"></div>
        <div class="body-chat-home animated zoomIn">
            <div class="leftPanel">
                <div class="tool-bar">
                    <img src="<?=empty(Yii::$app->user->identity->head) ? '/images/avatar.png' : Yii::$app->user->identity->head ?>"/>
                    <a  href="javascript:void(0);" class="view-history">
                        <span id="link-history" class="link-list fa fa-history"> 历史接待</span>
                    </a>
                </div>
                <div class="search-bar">
                    <div class="search-input">
                        <span class="fa fa-search"></span>
                        <input type="text" id="user-search" placeholder="快速搜索用户" />
                    </div>
                </div>
                <div class="chat-bar">
                </div>
            </div>
            <div class="rightPanel">
                <div class="initChat" style="height: 100%;">
                    <img src="/images/network.png" />
                </div>
            </div>
        </div>

        <div class="body-modal animated">
            <div class="body-modal-header">
                <a href="javascript:void(0);" class="title">标 题</a>
                <a href="javascript:void(0);" class="back_it hide"><span class="fa fa-arrow-left"> 返回</span></a>
                <a href="javascript:void(0);" class="refresh_it"><span class="fa fa-refresh"> 刷新</span></a>
                <a href="javascript:void(0);" class="close_it"><span class="fa fa-close"> 关闭</span></a>
            </div>
            <div class="body-modal-content">

            </div>
        </div>

        <div class="body-modal-2 animated">
            <div class="body-modal-header">
                <?php if (!isset($layout)) : ?>
                    <a href="javascript:void(0);" class="title">标 题</a>
                <?php else : ?>
                    <div class="tab-list">
                        <ul>
                            <li class="active bind-type " id="editstudent-0">客户档案信息</li>
                            <li class="bind-type "        id="editstudent-1">客户回访信息</li>
                            <li class="bind-type "        id="editstudent-2">排课信息</li>
                            <li class="bind-type "        id="editstudent-3">购买信息</li>
                            <li class="bind-type "        id="editstudent-4">投诉信息</li>

                        </ul>
                        <input id="new-visit-type" type="hidden" value="">
                        <input id="new-student-id" type="hidden" value="" />
                    </div>
                <?php endif ?>

                <a href="javascript:void(0);" class="close_it" style="top: 2px;">
                    <span class="fa fa-reply"> 返回</span>
                </a>
            </div>
            <div class="body-modal-content"></div>
        </div>
    </div>

    <div class="body-edit-user animated">
        <div class="edit-user-header">
            <span class="label label-warning fa fa-close" style="float: right;margin-right: 10px;margin-top: 10px"> 关闭</span>
            <span class="title">编辑用户信息</span>
        </div>
        <div class="edit-user-content"></div>
    </div>

    <div class="body-alert-message animated">
        <div class="alert-header">
            <span class="title">新消息提醒</span>
            <span class="label label-warning fa fa-close"> 关闭</span>
        </div>
        <div class="alert-content"></div>
    </div>

</div>


<div class="modal fade" id="commonModal" tabindex="-1"
     role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button data-dismiss="modal" type="button" class="close" aria-hidden="true"
                >&times;</button>
                <h4 class="modal-title" id="myModalLabel">编辑内容</h4>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">关 闭</button>
                <button type="button" class="confirm_btn btn btn-success pull-right" id="confirm_post">确 定</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="showModal" tabindex="-1"
     role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="width: 800px;">
        <div class="modal-content">
            <div class="modal-header">
                <button data-dismiss="modal" type="button" class="close" aria-hidden="true"
                >&times;</button>
                <h4 class="modal-title" id="myModalLabel">信息内容</h4>
            </div>
            <div class="modal-body">

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="qrcodeModal" tabindex="-1"
     role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="width: 800px;">
        <div class="modal-content">
            <div class="modal-header">
                <button data-dismiss="modal" type="button" class="close" aria-hidden="true"
                >&times;</button>
                <h4 class="modal-title" id="myModalLabel">信息内容</h4>
            </div>
            <div class="modal-body">

            </div>
        </div>
    </div>
</div>


<div class="chat-hidden">
    <input type="hidden" id="user-id" value=<?= Yii::$app->user->identity->id ?>>
    <input type="hidden" id="user-head" value=<?= Yii::$app->user->identity->head ?>>
    <input type="hidden" id="page_id" value="">
</div>
<div class="norepay">
    <span>
        0
    </span>
</div>

<?php if ($identity) :?>
<script type="text/javascript">
    <?php $this->beginBlock('js_end') ?>
        $(function() {
            //修改密码
            $("#commonModal").modal({ backdrop: "static" });
            $("#commonModal .modal-title").text("修改密码");
            $("#commonModal .modal-footer .confirm_btn").attr('id','change_pwd');
            $("#commonModal .modal-body").load('/home/password');
            $("#commonModal .modal-header [data-dismiss=modal]").hide();
            $("#commonModal .modal-footer [data-dismiss=modal]").hide();
        });
    <?php $this->endBlock() ?>
    </script>
    <?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_END); ?>
<?php endif;?>