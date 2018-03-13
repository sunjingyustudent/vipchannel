<?php
/**
 * Created by PhpStorm.
 * User: wangkai
 * Date: 17/02/27
 * Time: 下午15:42
 */
?>
<div class="user-list">
    <div class="head">

        <ul class="nav nav-pills" role="tablist" 　>
            <li class="active user_pill" id="new_user_1">
                <a href="#" style="border:1px solid #ddd">新用户</a>
            </li>
            <li id="channel_user_2" class="user_pill">
                <a href="#" style="border:1px solid #ddd">推广用户</a>
            </li>
            <li id="useless_user_3" class="user_pill">
                <a href="#" style="border:1px solid #ddd">无价值用户</a>
            </li>
            <li id="useless_user_4" class="user_pill">
                <a href="#" style="border:1px solid #ddd">我的名单</a>
            </li>
            <li id="useless_user_5" class="user_pill">
                <a href="#" style="border:1px solid #ddd">奖励名单提醒</a>
            </li>
            <li id="useless_user_6" class="user_pill">
                <a href="#" style="border:1px solid #ddd">每日聊通</a>
            </li>
        </ul>

        <ul style="float:left">
            <li>
                <input id="search_name" class="form-control" placeholder="填写姓名或手机号筛选">
            </li>

            <li>
                <input id="student_phone" class="form-control" maxlength="11" placeholder="绑定学生手机号搜索">
            </li>

            <li>
                <input id="search_date_1" class="form-control"  style="display: none" value="<?= date('Y/m/d', strtotime('-1 day')) ?>">
                <input id="search_date_2" class="form-control"  style="display: none" value="<?= date('Y/m/d', time()) ?>">
            </li>

            <li>
                <select id="reward_type" class="form-control" style="display: none">
                        <option value="0" selected>选择奖励类型</option>
                        <option value="8" >体验课</option>
                        <option value="-3">付费</option>
                        <option value="-2">二级买单</option>
                        <option value="1">软文</option>
                        <option value="11">微课拉新奖</option>
                        <option value="13">体验达人奖</option>
                        <option value="12">转渠道奖励</option>
                </select>
            </li>
        </ul>
        <p>
            &nbsp;
        </p>
        <p>
            &nbsp;
        </p>
    </div>

    <div id="list-content"></div>
</div>


<script>
    $(".user-list #search_date_1, #search_date_2").datepicker({
        autoclose: true,
        format: "yyyy/mm/dd",
        language: "zh-CN"
    })
        .on('changeDate', function (ev) {
            var keyword = $.trim($('.user-list .head #search_name').val());

            var type = $('.active').attr('id').split('_')[2];
                keyword = encodeURI(keyword);

            if (type == 5)
            {
                var rewardtype = $('.user-list .head #reward_type option:selected').val();
                time = $('.user-list .head #search_date_1').val();
                $(".user-list #list-content").load( "/user/reward-user-page?time=" + time
                    + "&keyword=" + keyword
                    +'&rewardtype='+rewardtype, function (res) {});

            }  else if (type == 6) {
                time = $('.user-list .head #search_date_2').val();
                $(".user-list #list-content").load( "/user/user-list-page?type=" + type
                    + "&keyword=" + keyword + '&time=' + time, function () {});
            }

        });
</script>