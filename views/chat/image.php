<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/7/28
 * Time: 下午12:44
 */

?>

<div class="img-uploader-content">
    <div id="uploader" class="wu-example">
        <div id="thelist" class="uploader-list"></div>
        <div class="btns">
            <div id="picker">选择图片</div>
            <button id="ctlBtn" class="btn btn-default">发送</button>
        </div>
    </div>
</div>

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
            pick: '#picker',

            // 只允许选择图片文件。
            accept: {
                title: 'Images',
                extensions: 'gif,jpg,jpeg,bmp,png',
                mimeTypes: 'image/*'
            },

            formData: {open_id:$("#openid").val()}
        });

        // 文件上传过程中创建进度条实时显示。
        uploader.on( 'uploadProgress', function( file, percentage ) {
            var $li = $( '#'+file.id ),
                $percent = $li.find('.progress span');

            // 避免重复创建
            if ( !$percent.length ) {
                $percent = $('<p class="progress"><span></span></p>')
                    .appendTo( $li )
                    .find('span');
            }

            $percent.css( 'width', percentage * 100 + '%' );
        });

        // 文件上传成功，给item添加成功class, 用样式标记上传成功。
        uploader.on( 'uploadSuccess', function( file, res ) {
            var list = '<div class="right-message" >';
            list = list + '<div class="avatar"><img src="<?= Yii::$app->user->identity->head ?>"></div>';
            list = list + '<div class="content"><img class="wechat-img" src="' + res.url + '">';
            list = list + '<p class="grey"><?= date('m/d H:i', time()) ?></p></div>';
            list = list + '<div class="clearAll"></div>';

            $(".chat-body").append(list);
            $('.chat-body').scrollTop(1000000);
            $( '#'+file.id ).addClass('upload-state-done');
        });

        // 文件上传失败，显示上传出错。
        uploader.on( 'uploadError', function( file ) {
            var $li = $( '#'+file.id ),
                $error = $li.find('div.error');

            // 避免重复创建
            if ( !$error.length ) {
                $error = $('<div class="error"></div>').appendTo( $li );
            }

            $error.text('上传失败');
        });

        // 完成上传完了，成功或者失败，先删除进度条。
        uploader.on( 'uploadComplete', function( file ) {
            $( '#'+file.id ).find('.progress').remove();
        });

    });
</script>
