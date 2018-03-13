<?php
/**
 * Created by Sublime.
 * User: wanhkai
 * Date: 16/12/12
 * Time: 下午6:34
 */
?>
<div class="class_record_list_head" style="margin-bottom: 30px;">
    <select id="class_record_list_select" class="form-control" style="width: 200px;display: inline;" >
        <option value="1" selected>完成课单</option>
        <option value="2">取消体验</option>
        <option value="3">关注未体验</option>
    </select>
    <input id="search_name" class="form-control"  placeholder="姓名和手机号" style="width: 300px; display: inline;margin-left: 10px;">    
    <input type="text" class="form-control pull-right" placeholder="(默认前30天到今天)" style="display: inline;width: 30%;" id="date-month" value="<?= date('Y/m/d', time() - 30 * 86400)?> - <?= date('Y/m/d', time())?>"/>
</div>
<div class="class_record_list_salesid" rel="<?= $saleId ?>"></div>
<?php if ($count > 0) : ?>
    <div class="class_record_list_body" id="class_record_list_body">

    </div>
    <ul id="class_record_list_pagination" class="pagination"></ul>
<?php else : ?>
    <div class="noData">
        <span class="fa fa-times-circle">没有找到数据</span>
    </div>
<?php endif ?>
<script>
    $(function () {
        var type = $('.class_record_list_head #class_record_list_select').val();
        var keyword = $('.class_record_list_head #search_name').val();
        var sale_id = $('.class_record_list_salesid').attr('rel');
        var date = $('.class_record_list_head #date-month').val();
        var start = 0;
        var end = 0;
        if (date != '')
        {
            start = $.trim(date.split('-')[0]);
            end = $.trim(date.split('-')[1]);
        }
        $('#class_record_list_pagination').jqPaginator({
            totalCounts: <?= $count ?>,
            pageSize: 10,
            visiblePages: 10,
            currentPage: 1,
            onPageChange: function (num) {
                $('.class_record_list_head #class_record_list_select').val(type);
                $('.class_record_list_head #search_name').val(keyword);
                $('.class_record_list_head #date-month').val(date);
                $("#class_record_list_body").load('/chat/class-record-page?saleId=' + sale_id + '&keyword=' + keyword + '&type=' + type + '&num=' + num + '&start=' + start + '&end=' + end , function () {
                });
            }
        });
    });
    //发送课单时间筛选
        $(".class_record_list_head #date-month").daterangepicker(
                {format: 'YYYY/MM/DD', opens: 'right'},
                function (start, end, label) {
                    var start = start.format('YYYY/MM/DD');
                    var end = end.format('YYYY/MM/DD');
                    var type = $('.class_record_list_head #class_record_list_select').val();
                    var keyword = $('.class_record_list_head #search_name').val();
                    var sale_id = $('.class_record_list_salesid').attr('rel');
                    var date = $('.class_record_list_head #date-month').val();
                    $("#showModal .modal-body").load('/chat/class-record-list?saleId=' + sale_id + '&keyword=' + keyword + '&type=' + type + '&start=' + start + '&end=' + end , function () {
                        $('.class_record_list_head #class_record_list_select').val(type);
                        $('.class_record_list_head #search_name').val(keyword);
                        $('.class_record_list_head #date-month').val(date);
                    });
                }
        );
</script>




