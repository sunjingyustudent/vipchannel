<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/5
 * Time: 下午2:33
 */
?>
<div class="todo-content">
    <div class="todo-head" style="display: none">
        <div class="form-group col-md-3">
            <label for="searchTime">时间筛选</label>
            <input class="form-control" id="todo-time" readonly placeholder="时间筛选" style="width: 300px" value="<?= date('Y/m/d', time()); ?> - <?= date('Y/m/d', time()); ?> ">
        </div>
    </div>

    <div class="todo-body col-md-12 " id="detailPage">

    </div>
</div>

<script>
    $(function () {
        $(".todo-content #todo-time").daterangepicker();
    });
</script>
