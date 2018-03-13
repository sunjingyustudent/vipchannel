<?php
/**
 * @User： wk
 * @Time： 2017-1-4  16:32
 */
?>
<?php if ($count > 0) : ?>
    <div id="listen-content" class='table'></div>
    <ul id="reward_record_pagination" class="pagination"></ul>
<?php else : ?>
    <div class="noData">
        <span class="fa fa-times-circle">没有找到数据</span>
    </div>
<?php endif ?>

<script>
    $(function (){
        var open_id = $('.rightPanel #open_id').val(),
            keyword = $('.listen-class-power #search_class').val();

        $('#reward_record_pagination').jqPaginator({
            totalCounts: <?= $count ?>,
            pageSize:10,
            visiblePages: 10,
            currentPage: 1,
            first: '<li class="first"><a href="javascript:void(0);">首页</a></li>',
            prev:  '<li class="prev"><a href="javascript:void(0);">上一页</a></li>',
            next:  '<li class="next"><a href="javascript:void(0);">下一页</a></li>',
            last:  '<li class="last"><a href="javascript:void(0);">末页</a></li>',
            onPageChange: function (num) {

                var url = "/chat/listen-class-power-list?openId=" + open_id + "&keyword=" + keyword + "&num=" + num;
                $(".listen-class-content #listen-content").load(url);
            }
        });
    });
</script>