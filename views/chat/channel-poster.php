<?php
/**
 * Created by PhpStorm.
 * User: wangkai
 */

?>

<p style="text-align: center;width: 100%;">
    <img id="imgHaibao" style="width: 320px" src="<?=$data?>" />
</p>
<p>
    <a href="javascript:void(0);">
        <span id="send-haibao" class="label label-blue label-block fa fa-file-image-o"> 立即发送海报</span>
    </a>
</p>
<input type="text" hidden value = "<?= $open_id ?>" id="haibao_openId">

