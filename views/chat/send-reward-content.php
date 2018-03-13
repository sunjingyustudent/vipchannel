<div class="alert alert-info reward-prompt" role="alert">
    <div style="width: 150px;">
        <button type="button" class="btn btn-warning" id="premission">开启权限</button>
    </div>
    <div class="reward-money">当前可提取金额：<?= round($data['money'], 2) ?>元</div>
</div>

<!--<div class="alert alert-success reward" role="alert" >-->
<!--    <span>本次买单奖励：--><?//= $data['pay_number'].' '.$data['pay_money'] ?><!--</span>-->
<!--    <span style="">本次体验奖励：--><?//= $data['ex_number'].' '.$data['ex_money'] ?><!--</span>-->
<!--    <!--    <span style="">本次注册奖励：--><?////= $data['register_number'].' '.$data['register_money'] ?><!--<!--</span>-->
<!--</div>-->
<!---->
<!--<div class="alert alert-info reward" role="alert">-->
<!--    <span>买单二级奖励：--><?//= $data['extra_number'].' '.$data['extra_money'] ?><!--</span>-->
<!--    <span>红包退回金钱：--><?//= $data['red_number'].' '.$data['red_money'] ?><!--</span>-->
<!---->
<!--</div>-->
<!---->
<!--<div class="alert alert-warning reward" role="alert">-->
<!--    <span>其他金钱奖励：--><?//= $data['other_number'].' '.$data['other_money'] ?><!--</span>-->
<!--    <span></span>-->
<!--</div>-->

<input type="text" class="form-control" placeholder="发送话术" name="message">
<div class=" suggest "  >
    <li>亲爱的用户，本次奖励金额为<?= $data['money'] ?>元，感谢您一如既往的支持VIP陪练。</li>
</div>

<div  class="red_content" >
    <div>
    <input type="text" class="form-control" placeholder="红包title" name="title" >
    <div class="suggest ">
        <li>提成红包</li>
        <li>恭喜发财</li>
    </div>
    </div>
    <div >
    <input type="text" class="form-control" placeholder="发送金额" name ='money'>
    <div class="suggest" >
        <li><?= $data['money'] ?></li>
    </div>
    </div>
    <div>
    <button type="button" class="do_send_reward btn btn-info"  style="width: 200px; margin-left: 50px;">发送奖励</button>
    </div>

    <input type="text" hidden name="user_id" value="<?= $user_id ?>">
</div>
<div style="clear:both"></div>

<script>
    $(document).on('click', ".modal-body  input", function () {
        $('.suggest').removeClass('show');
        $('.suggest').addClass('hide');
        $(this).next().remove('hide');
        $(this).next().addClass('show');
    });

    $(document).on('click', ".modal-body  li", function () {
        $(this).parent().prev().val($(this).text());
        $('.suggest').removeClass('show');
        $('.suggest').addClass('hide');
        $(this).next().remove('hide');
        $(this).next().addClass('show');
    });

    $(document).on('input', ".modal-body input", function () {
        $(this).next().removeClass('show');
        $(this).next().addClass('hide');
    });
</script>






