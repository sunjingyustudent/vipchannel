<?php
/**
 * @User： wk
 * @Time： 2017-1-4  16:32
 */
?>
<?php if ($count > 0) : ?>
    <div id="reward_record_contont" class='table'></div>
    <ul id="reward_record_pagination" class="pagination"></ul>
<?php else : ?>
    <div class="noData">
        <span class="fa fa-times-circle">没有找到数据</span>
    </div>
<?php endif ?>


<script>
    $(function (){
//        var user_id = $('.reward-head .sale_channel_id').val(),
//            type = $('.reward-head  .active').attr('id').split('_')[2];

        var data = <?= json_encode(Yii::$app->request->get(), JSON_UNESCAPED_SLASHES) ?>;
        var pageSize = 4,
            pageNum = 0,
            total = <?=$count?>;
            pageNum = Math.ceil(total/pageSize)>0?
                        Math.ceil(total/pageSize):0;
        $('#reward_record_pagination').jqPaginator({
            totalPages: pageNum,
            visiblePages: 4,
            currentPage: 1,
            onPageChange: function (num) {
                var url = "/chat/other-reward-record-list?userId=" + data.userId + "&num=" + num;
                $(".reward-content #reward_record_contont").load(url);
            }
        });
    });
</script>