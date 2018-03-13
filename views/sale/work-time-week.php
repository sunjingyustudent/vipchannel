<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/11/9
 * Time: 下午6:51
 */
?>

<div id="week">
    <h4>执行时间</h4>
    <input type="text" class="form-control" id="work-week-time" value="<?= empty($data['time_execute']) ? date('Y/m/d','26496000') : date('Y/m/d',$data['time_execute'])?>" >
    <div style="margin-top:10px;">
        <?php foreach($data['bit_info'] as $bit):?>
            <div class="weekDayCell" id="<?='week_'.$bit['week']?>">
                <h4 >周
                    <?php if($bit['week'] == 1):?>
                        一
                    <?php elseif($bit['week'] == 2):?>
                        二
                    <?php elseif($bit['week'] == 3):?>
                        三
                    <?php elseif($bit['week'] == 4):?>
                        四
                    <?php elseif($bit['week'] == 5):?>
                        五
                    <?php elseif($bit['week'] == 6):?>
                        六
                    <?php else:?>
                        日
                    <?php endif?>
                    <button id="do-add" type="button" class="btn btn-xs">
                        <span class="fa fa-plus" aria-hidden="true"></span>
                    </button>
                </h4>
                <div>
                    <?php if(!array_key_exists('time_list',$bit)):?>
                    <?php else:?>
                        <?php foreach($bit['time_list'] as $key=>$time):?>
                            <div class="workTimeCell" id="<?='day_'.$key?>">
                                <div class="timeLabel">
                                    <input type="text" class="form-control timeInputField" placeholder="时间" value="<?=$time['start']?>">
                                    <span class="workTimeQuote">-</span>
                                    <input type="text" class="form-control timeInputField" placeholder="时间" value="<?=$time['end']?>">
                                    <button id="do-delete" type="button" class="btn btn-xs">
                                        <span class="fa fa-minus" aria-hidden="true"></span>
                                    </button></div>
                            </div>
                        <?php endforeach ?>
                    <?php endif?>
                </div>
                <button id="reset" type="button" class="btn btn-xs" aria-label="Left Align" data-toggle="modal" ng-click="weekDayReleax($index)">
                    全天休息
                </button>
            </div>
        <?php endforeach ?>
    </div>
</div>
<script>
    $(function(){
        $("#work-week-time").datepicker({
            autoclose:true,
            format: "yyyy/mm/dd",
            language: "zh-CN" })
            .on('changeDate', function(ev){

            });
    });
</script>
