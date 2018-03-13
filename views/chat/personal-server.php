<?php
/**
 * Created by PhpStorm.
 * User: wangke
 * Date: 17/06/13
 * Time: 下午15:42
 */
?>

<div class="personal-server">

    <div class="personal-server-head head" >
        <input id="time-range" style="width: 20%" class="form-control"   placeholder="选择操作时间(默认今天)"  />
    </div>
    <p></p>
    <div class="personal-server-content">

    </div>
</div>

<script>

    $(".personal-server #time-range").daterangepicker(
        {format: 'YYYY/MM/DD'},
        function(start, end, label) {
            start = start.format('YYYY/MM/DD');
            end = end.format('YYYY/MM/DD');
            $(".personal-server-content").load('/chat/personal-server-page?start='+start+'&end='+end);
        }
    );
</script>