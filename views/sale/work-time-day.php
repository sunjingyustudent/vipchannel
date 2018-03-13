<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/11/9
 * Time: 下午5:35
 */
?>
<div id="day">
    <div>
        <input type="text" class="form-control" id="work-day-time" value="<?=date('Y/m/d',time())?>" style="margin-top: 5px">
        <div style="margin-top:10px;">
            <h4>工作时间
                <button id="do-add" type="button" class="btn btn-xs">
                    <span class="fa fa-plus" aria-hidden="true"></span>
                </button>
            </h4>
            <div id="day-time">
                <?php foreach($time_list as $key=>$item):?>
                    <div class="workTimeCell" id="<?='day_'.$key?>">
                        <div class="timeLabel">
                            <input type="text" class="form-control timeInputField" placeholder="时间" value="<?=$item['start']?>">
                            <span class="workTimeQuote">-</span>
                            <input type="text" class="form-control timeInputField" placeholder="时间" value="<?=$item['end']?>">
                            <button id="do-delete" type="button" class="btn btn-xs">
                                <span class="fa fa-minus" aria-hidden="true"></span>
                            </button></div>
                    </div>
                <?php endforeach?>
            </div>
            <button id="reset" type="button" class="btn btn-xs" >
                全天休息
            </button>
        </div>
    </div>
</div>
<script>
    $(function(){
        $("#work-day-time").datepicker({
            autoclose: true,
            format: "yyyy/mm/dd",
            language: "zh-CN"
        }).on('changeDate', function (ev) {
            var kefu_id = $("#commonModal #kid").val();
            var time = $('#work-day-time').val();
            $.getJSON('/sales/get-work-time?kefu_id=' + kefu_id + '&time=' + time + '&type=2', function (re) {

                var string = "";
                $('#work_time #day-time').empty();
                $.each(re,function (index,item) {

                    string += '<div class="workTimeCell" id="day_' + index + '" >'
                        + '<div class="timeLabel">'
                        + '<input type="text" class="form-control timeInputField" placeholder="时间" value="' + item.start + '" >'
                        + '<span class="workTimeQuote"> - </span>'
                        + '<input type="text" class="form-control timeInputField" placeholder="时间" value="' + item.end + '" >'
                        + '<button id="do-delete" type="button" class="btn btn-xs" >'
                        + '<span class="fa fa-minus" aria-hidden="true" ></span>'
                        + '</button></div ></div >';
                });
                $('#work_time #day-time').append(string);
            });
        });
    });
</script>
