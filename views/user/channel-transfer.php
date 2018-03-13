
<?php
/**
 * Created by PhpStorm.
 * User: wangkai
 * Date: 17/02/27
 * Time: 下午15:42
 */

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
?>
<div class="all-user-list">
    <div class="head">
    	<form id="transfer_from">
        <table style="margin-top: 6px;">
            <tr>
                <td style="width:70px;text-align: right">
                    陪练用户：
                </td>
                <td style="width: 150px">
                    <input name="search_user" class="form-control" placeholder="用户名/手机号">
                </td>
                <td style="width:70px;text-align: right">
                    渠道搜索：
                </td>
                <td style="width: 150px">
                    <input name="search_channel" class="form-control" placeholder="微信/用户名/手机号">
                </td>
                <td style="width:70px;text-align: right">
                    渠道经理：
                </td>
                <td style="width: 135px">
				    <select name="search_account" class="form-control" placeholder="全部渠道经理">
				      <option value="">全部渠道经理</option>
				      <?php foreach ($channels as $key => $value):?>
				      	<option value="<?= $value['id']?>"><?= $value['nickname']?></option>
				      <?php endforeach;?>
				    </select>
                </td>
                <td class="show_date" style="width: 70px;text-align: right;">
                    选择日期：
                </td>
                <td class="show_date" style="width: 140px;">
                    <input name="search_date" id="search_date" class="form-control" value="" readonly placeholder="全部">
                </td>

            </tr>

        </table>
        </form>
    </div>
        <p>
            &nbsp;
        </p>

<div class="channel-transfer-list">
    <div class="list-transfer"></div>
</div>
</div>


<script>
    $(".all-user-list #search_date").datepicker({
        autoclose: true,
        format: "yyyy/mm/dd",
        language: "zh-CN"
    }).on('changeDate', function (ev) {
			var url = $('#transfer_from').serialize();
			url = "/user/transfer-page?"+url;

          	$(".channel-transfer-list .list-transfer").load(url);
        });
</script>