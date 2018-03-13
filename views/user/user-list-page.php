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
                if(typeof(data.type) == "undefined") {
                    var url = "/user/reward-user-list?num=" + num + "&time=" + data.time + "&keyword=" +encodeURI(data.keyword) +'&rewardtype='+ data.rewardtype;
                } else if (data.type == 6) {
                    var url = "/user/user-list-info?type=" + data.type + "&num=" + num + "&keyword=" + encodeURI(data.keyword) + '&time=' + data.time;
                } else {
                    var url = "/user/user-list-info?type=" + data.type + "&num=" + num + "&keyword=" + encodeURI(data.keyword) + '&studentPhone=' + data.studentPhone;
                }
                $("#detailPage").load(url);
            }
        });
    });
</script>