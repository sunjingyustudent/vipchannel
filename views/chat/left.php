<?php
/**
 * Created by PhpStorm.
 * User: wangkai
 */
use yii\helpers\Html;
?>

<?php foreach ($users as $user): ?>
    <div class="link-user" id=<?="link_" . $user['link_id'] ?> >
        <span>
            <img height="50px" src="<?= $user['head'] ?>"" onerror="this.src='/images/avatar.png';" class="img-circle">&nbsp;
        </span>
        <div class="channel-name"><?= mb_substr($user['name'],0,10,'utf-8') ?></div>
        <?php if ($user['message_type'] == 1): ?>
            <div class="channel-status" style="color: lawngreen;">新用户</div>
        <?php elseif ($user['message_type'] == 2): ?>
            <div class="channel-status" style="color: cornflowerblue;">有推广价值的用户</div>
        <?php elseif ($user['message_type'] == 3): ?>
            <div class="channel-status" style="color: red;">无推广价值的用户</div>
        <?php endif; ?>
    </div>

<?php endforeach; ?>
