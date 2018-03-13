<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/7/12
 * Time: 上午11:05
 */
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
?>
<?php if ($count > 0) :?>
    <div id="detailPage-new" class="table-responsive">
        <div class="histort_visit_view" style="float: left;border-right: 2px solid #CCCCCC">
            <div class="visit_done_count" style="margin-left: 10px;margin-top: 35px">
                <h3> 今日需要跟进条数：<span style="color: red"><?= $nowNeedDoneCount ?></span></h3>
            </div>
            <div class="histort_visit" style="margin-left: 10px;margin-right: 27px">

            </div>
        </div>
        <div class="add_visit" style="margin-top: -20px;margin-right: 40px;margin-left: 10px;">
            <h3>填写本次跟进记录</h3>
            <p>是否关联体验课：
                <?= Html::dropDownList(null, null, ArrayHelper::map($exClassInfo, 'id', 'exClassInfo'), [
                    'class' => 'form-control',
                    'style' => 'width: 324px',
                    'id'=>'classId'
                ]); ?>
            </p>
            <p>编辑是否有意向：
                <select  id="worth" class="form-control">
                    <option value="2">有推广价值用户</option>
                    <option value="3">无推广价值用户</option>
                </select>
            </p>
            <p>本次跟进内容：<textarea  class="form-control content" ></textarea></p>
            <p>下次跟进时间：
                <input class="form-control fix-class-input time-next" id="time" value="" readonly>
            </p>
            <p>下次跟进内容：<textarea  class="form-control next-content" ></textarea></p>
            <div style="text-align: center">
                <button type="button" class="btn btn-info visit_btn" >提交</button>
            </div>
            <input type="text" hidden value="<?= $channel_id ?>" class="channel_id">
        </div>
    </div>

    <ul id="pagination" class="pagination"style="margin-left:8px;margin-top: -45px;"></ul>
<?php else :?>
    <div id="detailPage-new" class="table-responsive">
        <div class="histort_visit">
            <div class="no_data"><span class="fa fa-times-circle"> 没有找到数据!</span></div>
        </div>
        <div class="add_visit">
            <h3>填写本次跟进记录</h3>
            <p>是否关联体验课：
                <?= Html::dropDownList(null, null, ArrayHelper::map($exClassInfo, 'id', 'exClassInfo'), [
                    'class' => 'form-control',
                    'style' => 'width: 324px',
                    'id'=>'classId'
                ]); ?>
            </p>
            <p>编辑是否有意向：
                <select  id="worth" class="form-control">
                    <option value="2">有推广价值用户</option>
                    <option value="3">无推广价值用户</option>
                </select>
            </p>
            <p>本次跟进内容：<textarea  class="form-control content" ></textarea></p>
            <p>下次跟进时间：
                <input class="form-control fix-class-input time-next" id="time" value="" readonly>
            <p>下次跟进内容：<textarea  class="form-control next-content" ></textarea></p>
            <div style="text-align: center">
                <button type="button" class="btn btn-info visit_btn" >提交</button>
            </div>
            <input type="text" hidden value="<?= $channel_id ?>" class="channel_id">
        </div>
    </div>
<?php endif; ?>
<script>
    var channel_id = <?= Yii::$app->request->get('channelId') ?>;
    $(function () {
        $('#showModal .modal-body #pagination').jqPaginator({
            totalCounts: <?=$count?>,
            pageSize:1,
            visiblePages: 0,
            prev: '<li class="prev"><a href="javascript:void(0);">上一条记录</a></li>',
            next: '<li class="next"><a href="javascript:void(0);">下一条记录</a></li>',
            onPageChange: function (num) {
                var url = "/sale/get-visit-list?channelId=" + channel_id + '&num=' + num;
                $("#showModal .modal-body #detailPage-new .histort_visit ").load(url);
            }
        });
    });

    $('.add_visit #time').datetimepicker({
        language:  'zh-CN',
        weekStart: 1,
        todayBtn:  1,
        autoclose: 1,
        todayHighlight: 1,
        startView: 2,
        forceParse: 0,
        showMeridian: 1,
        pickerPosition:'top-right'
    });
</script>
