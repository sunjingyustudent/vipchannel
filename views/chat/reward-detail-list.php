<?php
/**
 * Created by Sublime.
 * User: wanhkai
 * Date: 16/12/12
 * Time: 下午6:34
 */
?>
<table class="table table-striped reward-detail">
    <tr>
        <th>用户信息</th>
        <th>备注</th>
        <th>收支明细(元)</th>
        <th>时间</th>
    </tr>
    <?php foreach ($data as $v) : ?>
        <tr>
            <td>
                <li>微信昵称：<?= $v['wechat_name'] ?></li>
                <li>用户姓名：<?= $v['nickname'] ?></li>
            </td>
            <td><span><?= $v['comment'] ?></span></td>
            <td><span><b><?= $v['money'] ?></b></span></td>
            <td><span><?= date('Y-m-d H:i:s', $v['time_created']) ?></span></td>
        </tr>
    <?php endforeach; ?>
</table>


