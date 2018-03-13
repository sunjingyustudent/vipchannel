<?php if($count > 0):?>
    <div id="detailPage" class="table"></div>
    <ul id="pagination" class="pagination"></ul>
<?php else:?>
    <div class="noData"><span class="fa fa-times-circle"> 没有找到数据!</span></div>
<?php endif ?>

<script>
    $(function () {

        $('.promotion-effect-list #pagination').jqPaginator({
            totalCounts: <?=$count?>,
            pageSize:50,
            visiblePages: 8,
            currentPage: 1,
            onPageChange: function (num) {
                var start = $('.promotion-effect-list .head #time-range').val().split(' - ')[0],
                    end = $('.promotion-effect-list .head #time-range').val().split(' - ')[1];


                var url = "/sale/promotion-effect-list?start=" + start + "&end=" + end + "&num=" + num;

                $(".promotion-effect-list #detailPage").load(url);
            }
        });
    });
</script>