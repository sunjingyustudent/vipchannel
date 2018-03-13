<?php
/**
 * Created by PhpStorm.
 * User: wangke
 * Date: 17/05/03
 * Time: 下午15:42
 */

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
?>
<div class="ex-class-report">
    <div class="head">

        <ul class="nav nav-pills" role="tablist" 　>
            <li class="active user_type" id="type_0">
                <a href="#" style="border:1px solid #ddd">每日关注</a>
            </li>
            <li  class="user_type"       id="type_1">
                <a href="#" style="border:1px solid #ddd">每日体验</a>
            </li>
        </ul>

        <ul style="float:left;padding-left: 0px;margin-top: 12px;">

            <li>
                <input id="date" class="form-control"  value="" placeholder = '默认今天'>
            </li>

            <li>
                <select id="register-status" class="form-control"
                        style="width: 200px;">
                    <option value="0" selected>关注未预约</option>
                    <option value="1" >关注已预约</option>
                </select>

                <select id="ex-status" class="form-control"
                        style="width: 200px;display:none; ">
                    <option value="0" selected>体验课待上课</option>
                    <option value="2" >体验课取消</option>
                    <option value="1" >体验课完成</option>
                </select>
            </li>

            <li>
                <?= Html::dropDownList(null, null, ArrayHelper::map($kefu_info, 'id', 'nickname'), [
                    'class' => 'form-control',
                    'style' => 'width: 188px',
                    'id'=>'kefu-type'
                ]); ?>
            </li>

        </ul>
    </div>
    <div style="float: right;font-size: 18px;padding-top: 5px;padding-top: 15px;padding-right:50px" id="showCount">总数量：<span class="num">0</span></div>

    <div id="list-content" class="col-md-12"></div>
</div>


<script>
    $(".ex-class-report #date").datepicker({
        autoclose: true,
        format: "yyyy/mm/dd",
        language: "zh-CN"
    }).on('changeDate', function (ev) {
        var type = $('.ex-class-report .active').attr('id').split('_')[1];
        var status = 0;
        var date =  $('.ex-class-report #date').val();
        var kefuid = $('.ex-class-report #kefu-type option:selected').val();

        if(type == 0)
        {
            $(".ex-class-report .head #type_0").addClass('active');
            $(".ex-class-report .head #type_1").removeClass('active');
            $(".ex-class-report .head #ex-status").hide();
            $(".ex-class-report .head #register-status").show();
            status = $('.ex-class-report #register-status option:selected').val();
        }
        else
        {
            $(".ex-class-report .head #type_1").addClass('active');
            $(".ex-class-report .head #type_0").removeClass('active');
            $(".ex-class-report .head #ex-status").show();
            $(".ex-class-report .head #register-status").hide();
            status = $('.ex-class-report #ex-status').val();
        }
        console.log(status)

        $(".ex-class-report #list-content").load('/report/ex-class-report-page?type='+ type
            +'&date='+ date
            +'&status='+ status
            +'&kefuid=' + kefuid);
    });
</script>