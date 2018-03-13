<?php
/**
 * Created by PhpStorm.
 * User: wangkai
 */
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
?>
<div class="user-info">
    <div class="wechat">
        <p><img class="image-cycle" src="<?=$data['head']?>"></p>
        <p><?=$data['wechat_name']?></p>
        <p><?=$data['private_code']  ?></p>
    </div>
    <table class="user table-re">
        <tr>
            <td style="width: 80px">关注时间</td>
            <td >
                <?= $data['follow_time'] ?>
            </td>
        </tr>
        <tr>
            <td style="width: 80px">用户姓名</td>
            <td>
                <input type="text" id="Name" class="name form-control" value="<?=$data['nickname']?>">
            </td>
        </tr>
        <tr>
            <td  style="width: 80px">用户手机</td>
            <td width="83%">
                <input type="text" id="Phone" class="name form-control" value="<?=$data['mobile']?>">
            </td>
        </tr>
        <tr>
            <td  style="width: 80px">省份</td>
            <td width="83%">
                <input type="text" id="Phone" class="name form-control" value="<?=$data['province']?>" disabled>
            </td>
        </tr>
        <tr>
            <td  style="width: 80px">渠道分类</td>
            <td width="83%">
                <input type="text" id="Phone" class="name form-control" value="<?=$data['from_nick']?>" disabled>
            </td>
        </tr>
        <tr>
            <td  style="width: 80px">用户分类</td>
            <td>
                <div class="status">
                    <?= Html::dropDownList(null, $data['user_type'], ArrayHelper::map($statusList, 'id', 'status'), ['id'=>'status_' . $data['user_type'],'class' => 'status-level form-control']); ?>
                    <div class="clear"></div>
                </div>
            </td>
        </tr>
        <tr>
            <td  style="width: 80px">用户价值</td>
            <td>
                <div class="worth">
                    <?= Html::dropDownList(null, $data['message_type'], ArrayHelper::map($worthList, 'id', 'worth'), ['id'=>'$worth_' . $data['message_type'],'class' => 'worth-level form-control']); ?>
                    <div class="clear"></div>
                </div>
            </td>
        </tr>
<!--        <tr>-->
<!--            <td>所属省市</td>-->
<!--            <td>-->
<!--                <div class="address">-->
<!--                    --><?//= Html::dropDownList(null, $data["province"], ArrayHelper::map($provinceList, 'id', 'name'), ['class' => 'province form-control']); ?>
<!---->
<!--                    --><?//= Html::dropDownList(null, $data["city"], ArrayHelper::map($cityList, 'id', 'name'), ['class' => 'city form-control']); ?>
<!---->
<!--                    <div class="clear"></div>-->
<!--                </div>-->
<!--            </td>-->
<!--        </tr>-->
        <tr>
            <td colspan="2">
                <hr />
            </td>
        </tr>
    </table>
    <p style="width: 100%;text-align: center;">
        <button type="button" id="Confirm" openId="<?= $data['bind_openid']?>" class="btn btn-success" width="80%">确 定</button>
    </p>
</div>
<input type="hidden" id="studentID" value="<?=$data['id']?>" />
