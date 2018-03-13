<?php if($count > 0):?>
    <div id="detailPage" class="table"></div>
    <ul id="pagination" class="pagination"></ul>
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
        $('#pagination').jqPaginator({
            totalPages: pageNum,
            visiblePages: 10,
            currentPage: 1,
            onPageChange: function (num,type) {
                var params = $('#transfer_from').serialize();
                var url = "/user/transfer-list?num=" + num+'&'+params;
                $("#detailPage").load(url);
            }
        });
    });
</script>