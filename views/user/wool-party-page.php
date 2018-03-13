<?php if ($count > 0) :?>
    <div id="detailPage" class="table"></div>
    <ul id="pagination" class="pagination"></ul>
<?php else :?>
    <div class="noData"><span class="fa fa-times-circle"> 没有找到数据!</span></div>
<?php endif ?>

<script>
    $(function () {
        var data = <?= json_encode(Yii::$app->request->get(), JSON_UNESCAPED_SLASHES) ?>;

        $('#pagination').jqPaginator({
            totalCounts: <?=$count?>,
            pageSize:10,
            visiblePages: 8,
            currentPage: 1,
            onPageChange: function (num, type) {
                var url = '/user/wool-party-list?num=' + num +'&kefuId=' + data.kefuId;

                $(".wool-party #detailPage").load(url);
            }
        });
    });
</script>