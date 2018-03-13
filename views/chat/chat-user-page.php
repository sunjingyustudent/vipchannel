<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/7/8
 * Time: 上午9:47
 */
?>
<?php if($count > 0):?>
    <div id="ChatWaitPage" class="table"></div>
    <ul id="ChatWaitCount" class="pagination"></ul>
    <input type="text" hidden id = 'chat-wait-type' value = "<?= $type ?>">
<?php else:?>
    <div class="noData"><span class="fa fa-times-circle"> 没有找到数据!</span></div>
<?php endif ?>

<script>
    $(function () {
        var type = "<?=  Yii::$app->request->get('type') ?>";
        var pageSize = 4,
            pageNum = 0,
            total = <?=$count?>;
            pageNum = Math.ceil(total/pageSize)>0?
                        Math.ceil(total/pageSize):0;
        $('#ChatWaitCount').jqPaginator({
            totalPages: pageNum,
            visiblePages: 10,
            currentPage: 1,
            onPageChange: function (num) {
                var url = "/chat/chat-user-list?type=" +type+ "&num="+num;
                $("#ChatWaitPage").load(url,'', function (res) {

                });
            }
        });
    });
</script>