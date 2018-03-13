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

        <ul class="nav nav-pills" role="tablist" 　>
            <li class="active user_pill" id="new_user_0">
                <a href="#" style="border:1px solid #ddd">全部用户</a>
            </li>
            <li class="user_pill" id="new_user_1">
                <a href="#" style="border:1px solid #ddd">新用户</a>
            </li>
            <li id="channel_user_2" class="user_pill">
                <a href="#" style="border:1px solid #ddd">推广用户</a>
            </li>
            <li id="useless_user_3" class="user_pill">
                <a href="#" style="border:1px solid #ddd">无价值用户</a>
            </li>
        </ul>

        <table style="margin-top: 6px;">
            <tr>
                <td style="width: 60px;text-align: right;color: red">
                    分配顾问：
                </td>
                <td style="width: 140px">
                    <?= Html::dropDownList(null, null, ArrayHelper::map($list, 'id', 'nickname'), ['class' => 'form-control', 'id'=>'kefu']); ?>
                </td>
                <td style="width: 70px;text-align: right">
                    搜索：
                </td>
                <td style="width: 180px">
                    <input id="search_name" class="form-control" placeholder="填写姓名或手机号筛选">
                </td>
                <td style="width: 95px;text-align: right">
                    顾问筛选：
                </td>
                <td style="width: 140px">
                    <?= Html::dropDownList(null, null, ArrayHelper::map($list_2, 'id', 'nickname'), ['class' => 'form-control', 'id'=>'kefu-type']); ?>
                </td>

                <td class="show_date" style="width: 70px;text-align: right;display: none">
                    时间选择：
                </td>
                <td class="show_date" style="width: 140px;display: none">
                    <input id="search_date" class="form-control"   style="display: none" value="<?= date('Y/m/d', time()) ?>">
                </td>

                <td style="width: 95px;text-align: right">
                    绑定学生：
                </td>
                <td style="width: 180px">
                    <input id="student_phone" class="form-control" maxlength="11" placeholder="绑定学生手机号搜索">
                </td>

            </tr>

        </table>
        <p>
            &nbsp;
        </p>
    </div>

    <div id="list-content"></div>
</div>


<script>
    $(".all-user-list #search_date").datepicker({
        autoclose: true,
        format: "yyyy/mm/dd",
        language: "zh-CN"
    })
        .on('changeDate', function (ev) {
            var keyword = $('.all-user-list .head #search_name').val(),
                time = $('.all-user-list .head #search_date').val();


            var url = "/user/reward-user-page?time=" + time + "&keyword=" + encodeURI(keyword);
            $(".all-user-list #list-content").load(url);
        });

</script>