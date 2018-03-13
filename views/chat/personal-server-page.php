<?php
/**
 * @User： wk
 * @Time： 2017-1-4  16:32
 */
?>
<?php if($count > 0): ?>
    <div id="personal-server-list" class='table'></div>
    <ul id="personal-server-pagination" class="pagination"></ul>
<?php else: ?>
    <div class="noData">
        <span class="fa fa-times-circle">没有找到数据</span>
    </div>
<?php endif ?>

<script>
    $(function (){
        var data = <?= json_encode(Yii::$app->request->get(), JSON_UNESCAPED_SLASHES) ?>;

        //console.info(data);
        $('#personal-server-pagination').jqPaginator({
            totalCounts: <?= $count ?>,
            pageSize:10,
            visiblePages: 4,
            currentPage: 1,
            first: '<li class="first"><a href="javascript:void(0);">首页</a></li>',
            prev:  '<li class="prev"><a href="javascript:void(0);">上一页</a></li>',
            next:  '<li class="next"><a href="javascript:void(0);">下一页</a></li>',
            last:  '<li class="last"><a href="javascript:void(0);">末页</a></li>',
            onPageChange: function (num) {
                var url = "/chat/personal-server-list?num=" + num + '&start=' + data.start + '&end=' + data.end;
                $(".personal-server-content #personal-server-list").load(url);
            }
        });
    });
</script>