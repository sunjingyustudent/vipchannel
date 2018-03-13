<?php if ($count > 0) :?>
    <div id="detailPage" class="table"></div>
    <ul id="pagination" class="pagination"></ul>
<?php else :?>
    <div class="noData"><span class="fa fa-times-circle"> 没有找到数据!</span></div>
<?php endif ?>

<script>
    $(function () {
        var data = <?= json_encode(Yii::$app->request->get(), JSON_UNESCAPED_SLASHES) ?>;
        var num = '<?= $count ?>';
        $('.ex-class-report #showCount .num').text(num);

        $('#pagination').jqPaginator({
            totalCounts: <?=$count?>,
            pageSize:200,
            visiblePages: 8,
            currentPage: 1,
            onPageChange: function (num, type) {
                var url = '/report/ex-class-report-list?num=' + num
                    +'&type='+ data.type
                    +'&date='+ data.date
                    +'&status='+ data.status
                    +'&kefuid=' + data.kefuid;

                $(".ex-class-report #detailPage").load(url);
            }
        });
    });
</script>