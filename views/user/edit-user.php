<?php
/**
 * Created by PhpStorm.
 * User: wangkai
 */
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
?>

<form class="form-horizontal" role="form" id="edit_user_save">
<div class="form-group user-info-base">
    <div class="user-info-head">
        <img class="img-circle" src="<?=$data['head']?>" width=60 height=60>
    </div>
    <div class="user-info-name">
        <div><?=$data['wechat_name']?> <?=$data['subscribe']?></div>
        <div><?=$data['private_code']?></div>
        <div>渠道分类：<?= $data['from_nick'] ?></div>
    </div>
</div>
<hr style="margin-top: 0px;">
<fieldset disabled>
<div class="form-group">
    <label class="col-sm-2 control-label">授权时间</label>
    <div class="col-sm-10">
        <input class="form-control" type="text" value="<?= $data['auth_time'] ?>" disable>
    </div>
</div>

</fieldset>
<div class="form-group">
    <label class="col-sm-2 control-label">二维码</label>
    <div class="col-sm-10">
        <input readonly style="width: 54px; margin-right: 20px;float: left;" class="form-control" type="text" value="<?= $data['reqrcode_time'] ?>" disable>
        <?php if (2 == Yii::$app->user->identity->role) :?>
            <?php if ($data['weicode_path']) :?>
                <?php if ('永久' == $data['reqrcode_time']) :?>
                    <button type="button" class="label label-primary edit-user-update-code" data-type="temp">更新临时码</button>
                <?php else :?>
                    <button type="button" class="label label-primary edit-user-update-code" data-type="temp">更新临时码</button>
                    <button type="button" class="label label-info edit-user-update-code" data-type="perm">分配永久码</button>
                <?php endif;?>
            <?php else :?>
                    <button type="button" class="label label-primary edit-user-update-code" data-type="temp">更新临时码</button>
                    <button type="button" class="label label-info edit-user-update-code" data-type="perm">分配永久码</button>
            <?php endif;?>
        <?php endif;?>
    </div>
</div>
<div class="form-group">
    <label class="col-sm-2 control-label">用户姓名</label>
    <div class="col-sm-10">
        <input class="form-control" name="name" type="text" value="<?= $data['nickname'] ?>" placeholder="用户姓名">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-2 control-label">用户手机</label>
    <div class="col-sm-10">
        <input class="form-control" name="phone" type="text" value="<?= $data['mobile'] ?>" placeholder="用户手机" >
    </div>
</div>

<div class="form-group">
    <label class="col-sm-2 control-label">用户价值</label>
    <div class="col-sm-10">
        <select class="form-control" id="worth" name="worth" style="width: 380px;">
            <?php foreach ($worthList as $key => $value) :?>
            <option <?= $data['message_type'] == $key?'selected':''?> value="<?=$key?>"><?= $value?></option>
            <?php endforeach;?>
        </select>
    </div>
</div>

<div class="form-group">
<label class="col-sm-2 control-label">乐器类型</label>
<?php if (is_array($instrument)) :?>
    <?php foreach ($instrument as $value) :?>
    <label class="checkbox-inline" style=" margin-left: 12px;">
        <input <?= in_array($value['id'], $instrument_ids)?'checked':'';?> type="checkbox" name="instrument[]" value="<?=$value['id']?>" data-name="<?= $value['name']?>" style="width: 20px;"><?= $value['name']?>
    </label>
    <?php endforeach;?>
<?php endif;?>
</div>

<div class="form-group">
    <label class="col-sm-2 control-label">备注</label>
    <div class="col-sm-10">
        <textarea rows=4 cols=60 id="remark" name="remark" maxlength="500" placeholder="备注点什么...字数限制500"><?=$data['remark']?></textarea>
    </div>
</div>
    <input type="hidden" id="studentID" value="<?=$data['id']?>" />
    <input type="hidden" name="open_id" value="<?=$data['bind_openid']?>" />
    <p style="width: 100%;text-align: center;">
        <button type="button" id="Confirm"  class="btn btn-success" width="80%">确 定</button>
    </p>
</form>
