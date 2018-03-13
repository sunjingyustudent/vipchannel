<?php
/**
 * Created by Sublime.
 * User: wanhkai
 * Date: 16/12/12
 * Time: 下午6:34
 */
?>

<table class="table table-striped ">
    <tr>
        <th>日期</th>
        <th>应发金额</th>
        <th>奖励金额</th>
        <th>累计金额</th>
        <th>操作</th>
    </tr>
    <?php foreach ($data as $v): ?>
        <tr>
            <td>
                <?= $v['create_time']?>
            </td>
            <td>
                <?= $v['payable_amount'] ?>元
            </td>
            <td>
                <?= $v['reward_amount'] ?>元
            </td>
            <td>
                <?= $v['total_amount'] ?>元
            </td>
            <td>
                <span id="send_<?= $v['id'] . '_' . $v['uid'] ?>" class="send_history_reward label label-success fa fa-paper-plane">发送</span>
            </td>
        </tr>
    <?php  endforeach; ?>
</table>


