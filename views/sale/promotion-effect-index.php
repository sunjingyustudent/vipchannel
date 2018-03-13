<?php
/**
 * Created by PhpStorm.
 * User: wangkai
 * Date: 17/02/27
 * Time: 下午15:42
 */
?>
<div class="promotion-effect-list">
    <div class="head">
        <div class="form-group col-md-3">
            <label for="searchTime">时间筛选</label>
            <input id="time-range" class="form-control" readonly="" placeholder="时间筛选" style="width: 300px"
                   value="<?= date('Y/m/d', time()); ?> - <?= date('Y/m/d', time()); ?>">
        </div>
    </div>

    <div id="list-content" class="col-md-12"></div>
</div>

<script>
    $(".promotion-effect-list #time-range").daterangepicker(
        {format: 'YYYY/MM/DD'},
        function () {
             var start = $('.promotion-effect-list .head #time-range').val().split(' - ')[0],
                 end = $('.promotion-effect-list .head #time-range').val().split(' - ')[1];
             var url = "/sale/promotion-effect-page?start=" + start + "&end=" + end;
             $(".promotion-effect-list  #list-content").load(url);
        }
    );

</script>