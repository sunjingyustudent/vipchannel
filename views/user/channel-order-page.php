<?php if($count > 0):?>
    <div id="student_order_list" class="table"></div>
    <ul id="pagination_student" class="pagination"></ul>
<?php else:?>
    <div class="noData"><span class="fa fa-times-circle"> 没有找到数据!</span></div>
<?php endif ?>
<script>
    $(function () {
        var pageSize = 5,
            pageNum = 0,
            total = <?=$count?>;
            pageNum = Math.ceil(total/pageSize)>0?
            			Math.ceil(total/pageSize):0;
        $('#pagination_student').jqPaginator({
            totalPages: pageNum,
            visiblePages: 10,
            currentPage: 1,
            onPageChange: function (num,type) {
                var url = "/user/get-order-list?num=" + num+'&sid=' + <?= $sid?>;
                $("#student_order_list").load(url);
            }
        });
    });
</script>