<?php
/**
 * @User： wk
 * @Time： 2017-1-4  16:32
 */
?>
<?php if($count > 0): ?>
    <div id="channel_info_list" class='table'></div>
    <ul id="reward_record_pagination" class="pagination"></ul>
<?php else: ?>
    <div class="noData">
        <span class="fa fa-times-circle">没有找到数据</span>
    </div>
<?php endif ?>

<script>
    $(function (){
        var user_id = $('.channel-info-head .sale_channel_id').val(),
            type = $('.channel-info-head  .active').attr('id').split('_')[2];

        $('#reward_record_pagination').jqPaginator({
            totalCounts: <?= $count ?>,
            pageSize:4,
            visiblePages: 4,
            currentPage: 1,
            first: '<li class="first"><a href="javascript:void(0);">首页</a></li>',
            prev:  '<li class="prev"><a href="javascript:void(0);">上一页</a></li>',
            next:  '<li class="next"><a href="javascript:void(0);">下一页</a></li>',
            last:  '<li class="last"><a href="javascript:void(0);">末页</a></li>',
            onPageChange: function (num) {
                var url = "/chat/channel-info-list?id=" + user_id + "&type=" + type + "&num=" + num;
                $(".channel-info-content #channel_info_list").load(url);
            }
        });
    });
</script>