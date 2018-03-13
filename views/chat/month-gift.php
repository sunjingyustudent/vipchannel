<?php
/**
 * Created by PhpStorm.
 * User: wangke
 * Date: 17/06/13
 * Time: 下午15:42
 */
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
?>

<div class="month-gift">
    <div style="float: left;margin-left: 20px">
        <select id="user_type" class="form-control" style="width: 260px;position: static">
            <option value="1" selected >微课拉新奖</option>
            <option value="2" >体验达人奖</option>
            <option value="3" >首次体验奖</option>
        </select>
    </div>
    <div class="input-group" style="width: 260px;margin-left: 10px;position: static;float: left"" >
        <div class="input-group-addon">
            <i class="fa fa-calendar"></i>
        </div>
        <input type="text" class="form-control pull-right" id="date-range" placeholder="选择提取奖励时间(默认前14天)" />
    </div>
    <div class="input-group" style="width: 260px;margin-left: 10px;position: static;float: left"" >
        <?= Html::dropDownList(null, null, ArrayHelper::map($kefuInfo, 'id', 'nickname'), [
            'class' => 'form-control',
            'style' => 'width: 188px',
            'id'=>'kefu-type'
        ]); ?>
    </div>
    <div style="float: right;font-size: 18px;padding-top: 5px;padding-top: 3px;padding-right:50px" id="showCount">
        总数量：<span class="num">0</span>
    </div>
    <p></p>
    <div class="month-gift-content col-md-12" style="margin-top: 10px" >

    </div>
</div>

<script>

    $(function () {
        $(".month-gift #date-range").daterangepicker(
            {format: 'YYYY/MM/DD',opens:'right'},
            function(start, end, label) {
                gift_sdate = start.format('YYYY/MM/DD');
                gift_edate = end.format('YYYY/MM/DD');
                var user_type = $('.month-gift #user_type').val();
                var kefuId = $('.month-gift #kefu-type').val();
                $(".month-gift-content").load('/chat/month-gift-page?start=' + gift_sdate
                    + '&end=' + gift_edate
                    + '&usertype=' + user_type
                    + '&kefuId=' + kefuId);
            }
        );
    })




</script>