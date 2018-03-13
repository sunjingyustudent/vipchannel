 
<div class="poster_content" style="min-height: 50px;">
    <ul id="transfer_block">
        <li class="active">奖励操作</li>
        <li data-sid="<?= is_array_set($item,'student_id')?>">用户买单记录</li>
    </ul>
</div>
<div class="transfer-content">
        <form id="transfer_reward_form">
    <table class="table">
        <tbody>
            <tr>
                <td>渠道老师</td>
                <td><input  type="text" class="form-control" value="<?= is_array_set($item,'uname')?>" readonly="true"/></td>
            </tr>
            <tr>
                <td>手机号</td>
                <td><input type="text" class="form-control" value="<?= is_array_set($item,'new_moblie')?>" readonly="true"/></td>
            </tr>
            <tr>
                <td>奖励类型</td>
                <td>
                    <select name="type" class="form-control" >
                    <?php foreach ($reward_type as $key => $value):?>
                        <option value="<?= $key;?>" ><?= $value;?></option>
                    <?php endforeach;?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>奖励金额</td>
                <td><input <?php if(is_array_set($item,'status'))echo 'readonly="true"'?> type="number" name="money" maxlength="10" class="form-control" value="<?= is_array_set($item,'money',0)?>"/></td>
            </tr>
            <tr>
                <td>内部备注</td>
                <td>
                <textarea <?php if(is_array_set($item,'status'))echo 'readonly="true"'?> rows=4 cols=110 id='descp' name="descp" maxlength="500" placeholder="内部备注"><?= is_array_set($item,'descp')?></textarea>
                </td>
            </tr>
            <?php if(is_array_set($item,'status',0)==0):?>
            <tr>
                <td colspan=2>
                    <div>    
                        <p style="width: 100%;text-align: center;">
                            <button style="width: 200px;" type="button" class="btn btn-success" id="transfer_reward_form_submit">确 定</button>
                        </p>
                    </div>
                </td>
            </tr>
            <?php endif;?>
            <input type="hidden" id="transfer_id" name="transfer_id" value="<?= is_array_set($item,'transfer_id')?>"/>
            <input type="hidden" name="channel_id" value="<?= is_array_set($item,'channel_id')?>"/>
        </tbody>
    </table>
    </form>
</div>
<div class="transfer-content">
</div>