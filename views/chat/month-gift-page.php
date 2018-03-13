<?php
/**
 * @User： wk
 * @Time： 2017-1-4  16:32
 */
?>
<?php if ($count > 0) : ?>
    <div id="month-gift-list" class='table'></div>
    <ul id="month-gift-pagination" class="pagination"></ul>
<?php else : ?>
    <div class="noData">
        <span class="fa fa-times-circle">没有找到数据</span>
    </div>
<?php endif ?>

<script>
    $(function (){
        var data = <?= json_encode(Yii::$app->request->get(), JSON_UNESCAPED_SLASHES) ?>;
        var num = '<?= $count ?>';
        $('.month-gift #showCount .num').text(num);
        //console.info(data);
        $('#month-gift-pagination').jqPaginator({
            totalCounts: <?= $count ?>,
            pageSize:200,
            visiblePages: 4,
            currentPage: 1,
            onPageChange: function (num) {
                var url = "/chat/month-gift-list?num=" + num
                    + '&start=' + data.start
                    + '&end=' + data.end
                    + '&usertype=' + data.usertype
                    + '&kefuId=' + data.kefuId;
                $(".month-gift-content #month-gift-list").load(url);
            }
        });
    });
</script>