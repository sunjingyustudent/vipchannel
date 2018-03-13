<?php
/**
 * Created by PhpStorm.
 * User: wangkai
 */
use yii\helpers\Html;

?>

<div class="chat-center" sid=<?= $userId ?>>
    <div class="chat-head">
        <div class="left-content">
            正在接待: <span style="color: #0099ff;"><?= empty($user)?'':$user->wechat_name?> <?= $mobile ?></span>&nbsp;
            顾问名称: <span style="color: #0099ff;"><?= $kefu_name ?></span>&nbsp;
            授权状态: 
            <?php if (is_array_set($user, 'auth_time')) :?>
                <a href="javascript:;" title="<?= $user['auth_time']?>"><span style="color: #5cb85c;">已同意</span></a>
            <?php else :?>
                <span style="color: #f0ad4e;">未同意</span>
            <?php endif;?>
            <br>
            关注时间: <span style="color: #0099ff;"><?= empty($user)?'':date('Y-m-d H:i', $user->created_at) ?></span>&nbsp;
            地区: <span style="color: #0099ff;"><?= empty($user)?'':$user->province ?></span>&nbsp;
            乐器: <span style="color: #0099ff;" id="chat_instrument"><?= empty($user)?'':$user->instrument ?></span>&nbsp;
        </div>


        <?php if (!empty($user)) :?>
        <div class="center-content">
        </div>
        <div class="center-content">
            <a class="lighten-user" id="<?= $user->bind_openid ?>" href="javascript:void(0);">
                <?php if ($user->lighten_status == 1) : ?>
                    <span class="label label-success fa fa-star">已添加</span>
                <?php else : ?>
                    <span class="label label-warning fa fa-star">未添加</span>
                <?php endif; ?>
            </a>
        </div>
        <?php endif;?>
        <div class="right-content">
            <a class="edit-user" id="<?= empty($user)?'':$user->bind_openid ?>" href="javascript:void(0);">
                <span class="label label-blue fa fa-pencil-square-o"> 编辑用户</span>
            </a>
        </div>
    </div>
    <div class="chat-body">
        <div id="load-more">
            <a href="javascript:void(0);">
                <span class="fa fa-history"> 点击获得历史记录</span>
            </a>
        </div>
        <?php if (!empty($messages)) : ?>
            <?php foreach ($messages as $message) : ?>
                <?php if ($message['tag'] == 0) : ?>
                    <div class="left-message">
                        <div class="avatar">
                            <img src=<?= $message['head'] ?> >
                        </div>
                        <div class="content">
                            <?php if ($message['type'] == 1) : ?>
                                <p><?= $message['message'] ?></p>
                            <?php elseif ($message['type'] == 2) : ?>
                                <img class="wechat-img" src=<?= Yii::$app->params['vip_static_path'] . $message['message'] ?>>
                            <?php elseif ($message['type'] == 3) : ?>
                                <img name='<?= $message['id'] ?>' class="voice-img" src="/images/voice.png">
                                <audio id='<?= $message['id'] ?>' class="audio" src=<?= Yii::$app->params['vip_video_path'] . $message['message'] ?>></audio>
                            <?php endif; ?>
                            <p class="grey"><?= date('Y/m/d H:i:s', $message['time_created']) ?></p>
                        </div>
                        <div class="clearAll"></div>
                    </div>
                <?php else : ?>
                    <div class="right-message">
                        <div class="avatar">
                            <img src=<?= $message['kefu_head'] ?> >
                        </div>
                        <div class="content">
                            <?php if ($message['type'] == 1) : ?>
                                <p><?= $message['message'] ?></p>
                            <?php elseif ($message['type'] == 2) : ?>
                                <img class="wechat-img" src=<?= Yii::$app->params['vip_static_path'] . $message['message'] ?>>
                            <?php elseif ($message['type'] == 3) : ?>
                                <img name='<?= $message['id'] ?>' class="voice-img" src="/images/voice.png">
                                <audio id='<?= $message['id'] ?>' src=<?= Yii::$app->params['vip_video_path'] . $message['message'] ?>></audio>
                            <?php endif; ?>
                            <p class="grey"><?= $message['kefu_name'] . ' '. date('Y/m/d H:i:s', $message['time_created']) ?></p>
                        </div>
                        <?php if (!empty($message['is_fail'])) : ?>
                            <span style="color: red; margin-right: 5px;" class="fa fa-exclamation-circle pull-right"></span>
                        <?php endif; ?>
                        <div class="clearAll"></div>
                    </div>
                <?php endif; ?>
                <div class="clear"></div>

            <?php endforeach; ?>
        <?php endif; ?>

    </div>
    <div class="chat-footer">
        <div class="quick-bar">
            <a href="javascript:void(0)">
                <span id="emotion" class="label label-default fa fa-smile-o lineHeight"> 表情</span>
            </a>
            <a href="javascript:void(0)">
                <span id="image" class="label label-default fa fa-picture-o"> 图片</span>
            </a>
            <a href="javascript:void(0)" id="quick-answer">
                <span class="label label-default fa fa-fighter-jet lineHeight"> 快捷回复</span>
            </a>
            <a href="javascript:void(0)" id="pause-answer">
                <span class="label label-default fa fa-fighter-jet pause"> 回执</span>
            </a>
        </div>

        <textarea type="text" class="text-content" id="user-input" />
<!--        <div class="text-content" id="user-input" contenteditable="true"></div>-->
        <div class="send-btn" style="display: none">
            <span class="fa fa-3x fa-fighter-jet"> </span>
        </div>
        <div class="clear"></div>
    </div>
</div>

<div class="chat-right">
    <div class="top-gap"><span class="fa fa-bars"> Quick Menu</span></div>
    <ul>
<!--        <li>-->
<!--            <a id="change-channel" href="javascript:void(0);">-->
<!--                <span style="color: #7a43b6" class="fa fa-2x fa-group"></span>-->
<!--                <em>更换渠道</em>-->
<!--            </a>-->
<!--        </li>-->
<!--        <li>-->
<!--            <a id="user-detail2" href="javascript:void(0);">-->
<!--                <span style="color: #545096" class="fa fa-2x fa-user"></span>-->
<!--                <em>用户详情</em>-->
<!--            </a>-->
<!--        </li>-->
        <li>
            <a id="follow-info" href="javascript:void(0);">
                <span style="color: #0099FF" class="fa fa-2x  fa-sign-in"></span>
                <em>跟进信息</em>
            </a>
        </li>
        <li>
            <a id="listening-competence" href="javascript:void(0);">
                <span style="color: #d58512" class="fa fa-2x fa-lock"></span>
                <em>听课权限</em>
            </a>
        </li>
        <li>
            <a id="bind-haibao"  href="javascript:void(0);" data-openid="<?= empty($user)?'':$user->bind_openid ?>" data-userid="<?= $userId ?>">
                <span style="color: #00aa00" id="visit" labeltype="1"  class="new-link-edit-visit fa   fa-2x fa-file-image-o"></span>
                <em>发送素材</em>
            </a>
        </li>
        <li>
            <a id="send-class" href="javascript:void(0);">
                <span style="color: #00aa00" class="fa fa-2x fa-slideshare"></span>
                <em>发送课单</em>
            </a>
        </li>
        <li>
            <a id="send-reward" href="javascript:void(0);">
                <span style="color: red" class="fa fa-2x  fa-money"></span>
                <em>发送奖励</em>
            </a>
        </li>
        <li>
            <a id="channel-info" href="javascript:void(0);">
                <span style="color: #545096" class="fa fa-2x fa-user"></span>
                <em>推广详情</em>
            </a>
        </li>
    </ul>
    <div class="nav-ul">Others</div>
    <div class="transfer">
        <a id="transfer-server"  href="javascript:void(0);">
            <span class="fa fa-reply"> 转给其他客服</span>
        </a>
    </div>
<!--    <div class="transfer">-->
<!--        <a id="transfer-teacher" href="javascript:void(0);">-->
<!--            <span class="fa fa-reply-all"> 转给老师客服</span>-->
<!--        </a>-->
<!--    </div>-->
    <div class="transfer">
        <a id="off-chat" href="javascript:void(0);">
            <span class="fa fa-user-times"> 退 出 接 待</span>
        </a>
    </div>
</div>

<input type="hidden" id="open_id" value=<?= empty($user)?'':$user->bind_openid ?>>
<input type="hidden" id="openid" value=<?= empty($user)?'':$user->bind_openid ?>>
<input type="hidden" id="chat_studentId" value=<?= $userId ?>>
<input type="hidden" id="address_static" value=<?= Yii::$app->params['vip_static_path'] ?>>

<script>
    $(function () {
        var uploader = WebUploader.create({

            // 选完文件后，是否自动上传。
            auto: true,

            // swf文件路径
            swf: '../plugins' + '/webuploader/Uploader.swf',

            // 文件接收服务端。
            server: '/chat/do-send-image',

            // 选择文件的按钮。可选。
            // 内部根据当前运行是创建，可能是input元素，也可能是flash.
            pick: '.chat-footer #image',

            // 只允许选择图片文件。
            accept: {
                title: 'Images',
                extensions: 'gif,jpg,jpeg,bmp,png',
                mimeTypes: 'image/*'
            },

            formData: {open_id: $("#openid").val()}
        });

        // 文件上传过程中创建进度条实时显示。
        uploader.on('uploadProgress', function (file, percentage) {
            var $li = $('#' + file.id),
                $percent = $li.find('.progress span');

            // 避免重复创建
            if (!$percent.length) {
                $percent = $('<p class="progress"><span></span></p>')
                    .appendTo($li)
                    .find('span');
            }

            $percent.css('width', percentage * 100 + '%');
        });

// 文件上传成功，给item添加成功class, 用样式标记上传成功。
        uploader.on('uploadSuccess', function (file, res) {
            var list = '<div class="right-message" >';
            list = list + '<div class="avatar"><img src="<?= Yii::$app->user->identity->head ?>"></div>';
            list = list + '<div class="content"><img class="wechat-img" src="' + res.url + '">';
            list = list + '<p class="grey"><?= date('m/d H:i', time()) ?></p></div>';
            list = list + '<div class="clearAll"></div>';

            $(".chat-body").append(list);
            $('.chat-body').scrollTop(1000000);
            $('#' + file.id).addClass('upload-state-done');
        });

// 文件上传失败，显示上传出错。
        uploader.on('uploadError', function (file) {
            var $li = $('#' + file.id),
                $error = $li.find('div.error');

            // 避免重复创建
            if (!$error.length) {
                $error = $('<div class="error"></div>').appendTo($li);
            }

            $error.text('上传失败');
        });

// 完成上传完了，成功或者失败，先删除进度条。
        uploader.on('uploadComplete', function (file) {
            $('#' + file.id).find('.progress').remove();
        });
        
        
    });
</script>

