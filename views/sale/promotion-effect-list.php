<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/5
 * Time: 下午2:46
 */
?>
<table class="table">
    <thead>
    <tr>
        <th>日期</th>
        <th>接待数量</th>
        <th>聊通数量</th>
        <th>注册学生</th>
        <th>体验课完成</th>
        <th>买单次数</th>
        <th>买单金额</th>
        <th>二级买单金额</th>
    </tr>
    </thead>
    <tbody style="list-style: none;">
    <?php foreach ($list as $row): ?>
    <tr>
        <td><?= $row['time_created'] ?></td>
        <td><?= $row['chat_amount'] ?></td>
        <td><?= $row['worth_chat_amount'] ?></td>
        <td><?= $row['reg_amount'] ?></td>
        <td><?= $row['ex_amount'] ?></td>
        <td><?= $row['buy_amount'] ?></td>
        <td><?= $row['buy_money'] ?></td>
        <td><?= $row['distribution_buy_money'] ?></td>
    </tr>
    </tbody>
    <?php endforeach; ?>
    <?php if (!empty($sum)) : ?>
        <tr>
            <td>总计:</td>
            <td><?= $sum['chat_amount'] ?></td>
            <td><?= $sum['worth_chat_amount'] ?></td>
            <td><?= $sum['reg_amount'] ?></td>
            <td><?= $sum['ex_amount'] ?></td>
            <td><?= $sum['buy_amount'] ?></td>
            <td><?= $sum['buy_money'] ?></td>
            <td><?= $sum['distribution_buy_money'] ?></td>
        </tr>
    <?php endif; ?>
</table>
