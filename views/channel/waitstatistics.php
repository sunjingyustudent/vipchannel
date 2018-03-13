
<?php
/**
 * Created by PhpStorm.
 * User: sjy
 * Date: 17/06
 * Time: 下午15:42
 */
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
?>
<div class="waitstatistics-list">
    <div class="head">
        <table style="margin-top: 6px;">
            <tr>
                <td class="show_date" style="width: 70px;text-align: right;">
                    选择日期：
                </td>
                <td class="show_date" style="width: 140px;">
                    <input name="search_date" id="search_date" class="form-control" value="<?= date("Y-m-d",time())?>" readonly placeholder="">
                </td>

            </tr>

        </table>
    </div>
        <p>
            &nbsp;
        </p>

<div class="channel-waitstatistics-list">
    <div class="list-waitstatistics"></div>
</div>
</div>
<script>
    $(".waitstatistics-list #search_date").datepicker({
        autoclose: true,
        format: "yyyy/mm/dd",
        language: "zh-CN"
    }).on('changeDate', function (ev) {
                 var search_date = $('.waitstatistics-list #search_date').val();
          	$(".channel-waitstatistics-list .list-waitstatistics").load('/channel/wait-statistics-page?date='+search_date);
        });
</script>