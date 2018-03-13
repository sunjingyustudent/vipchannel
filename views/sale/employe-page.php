<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/11/9
 * Time: 下午2:52
 */
?>
<?php if($count > 0):?>
    <div id="detailPage" class="table"></div>
    <ul id="pagination" class="pagination"></ul>
    <script>
        $(function () {
            var data = <?= json_encode(Yii::$app->request->post(), JSON_UNESCAPED_SLASHES) ?>;

            $('.employe #pagination').jqPaginator({
                totalCounts: <?=$count?>,
                pageSize:10,
                visiblePages: 10,
                currentPage: 1,
                onPageChange: function (num, type) {
                    data.page_num = num;
                    var url = "/sale/employe-list";
                    $(".employe #detailPage").load(url, data, function (res) {

                    });
                }
            });
        });
    </script>
<?php else:?>
    <div class="noData"><span class="fa fa-times-circle"> 没有找到数据!</span></div>
<?php endif ?>


