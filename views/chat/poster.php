<?php
/**
 * Created by PhpStorm.
 * User: wangkai
 */
?>
<div class="poster_content">
	<ul id="select_send" data-openid='<?= $open_id;?>' data-userid='<?= $user_id;?>' data-kefuid=<?= $kefu_id?>>
		<li class="active">海报</li>
		<li>福利卡</li>
		<li>介绍页</li>
		<li>拉老师海报</li>
		<li>名片</li>
	</ul>
	<!--海报-->
	<div class="main_content" >
		<div class="banner banner-left">
			<select id="select_send_poster" class="select">
			<?php if(!empty($posters)):?>
				<?php foreach ($posters as $k => $v):?>
					<option value="<?= $v['id']?>" data-path="<?= Yii::$app->params['vip_static_path'].$v['path']?>"> <?php if($v['is_default']==1):?>[默认]<?php endif;?><?= $v['name']?></option>
				<?php endforeach;?>
			<?php else:?>
				<option value=""  data-path="">--暂无海报--</option>
			<?php endif;?>
			</select>
			<div class="preview_l" id="change_poster_show">
			</div>
			<button id="create_send_poster" type="button" class="btn btn-default">生成海报</button>
		</div>
		<div class="banner" id="banner">
		</div>
		<div class="banner send_button">
			<button type="button" id="send-haibao" class="btn btn-success">立即发送</button>
		</div>
	</div>
	<!--福利卡-->
	<div class="main_content welfare" style="display: none;">
		<div class="banner">
			<img id="show_welfare_qrcode" src="" height="300" width="500" />
		</div>
		<div class="banner send_button">
			<button type="button" id="send_welfare_qrcode" class="btn btn-success">立即发送</button>
		</div>
	</div>
	<!--介绍页-->
	<div class="main_content" style="display: none;">
		<div class="banner banner-left block">
			<select class="select fl" id="select-introduce-page">
			<option value=""  data-content="">--请选择介绍页--</option>
			<?php if(!empty($introduce_page)):?>
				<?php foreach ($introduce_page as $k => $v):?>
					<option value="<?= $v['id']?>" data-content="<?= $v['content']?>" data-url="<?= $v['url']?>"><?= $v['name']?></option>
				<?php endforeach;?>
			<?php endif;?>
			</select>
			<label class="checkbox">
				<input type="checkbox" name="checkbox" id="bottom-qrcode"/>带底部二维码
			</label>
		</div>
		<div class="preview">
			<p>预览</p>
			<div id="introduce-page" contentEditable="true"></div>
		</div>
		<div class="banner send_button">
			<button id="send_introduce" class="btn btn-success btn-block">立即发送</button>
		</div>
	</div>
	<!--拉老师海报-->
	<div class="main_content welfare" style="display: none;">
		<div class="banner">
			<img id="teacher_temporary_poster" src="" height="400" width="320" />
		</div><br>
		<div class="banner send_button tp">
			<button type="button" id="send_teacher_temporary_poster" class="btn btn-success">立即发送</button>
		</div>
	</div>

	<!--名片-->
	<div class="main_content" style="display: none;">
		<div class="banner">
			<div class="select_send_type" id="select_send_type">
				<label>
					<input type="radio" name="radio" class="label_radio" data-picurl="<?=$account['card']?>" <?php echo empty($account['card'])?'disabled':'';?>/>名片
				</label>
				<br><br>
				<label>
					<input type="radio" name="radio" class="label_radio" data-picurl="<?=$account['poster']?>" <?php echo empty($account['poster'])?'disabled':'';?>/>海报
				</label>
				<br><br>
				<label>
					<input type="radio" name="radio" class="label_radio" data-picurl="<?=$account['qrcode']?>" <?php echo empty($account['qrcode'])?'disabled':'';?>/>二维码
				</label>
				<br><br>
				<label>
					<input type="radio" name="radio" data-picurl="<?=$account['banner']?>" class="label_radio" <?php echo empty($account['banner'])?'disabled':'';?>/>底部banner
				</label>
			</div>
			<div class="select_send_type per">
			</div>
		</div><br/>
		<div class="banner send_button">
			<button type="button" id="send-channel-pic" class="btn btn-success">立即发送</button>
		</div>
	</div>
<!--     <div class="poster bind_info">
        <button type="button" class="btn btn-primary btn-lg btn-block">销售渠道海报</button>
    </div>

    <div class="edit_word bind_info">
        <button type="button" class="btn btn-default btn-lg " disabled>可编辑话术(暂无)</button>
    </div> -->

    <div style="clear:both">

    </div>
</div>

