<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/7/7
 * Time: 下午2:59
 */

?>
<table class="table">
    <thead>
    <tr>
        <th>用户名</th>
        <th>用户类型</th>
        <th>红包总金额</th>
        <th>微课拉新</th>
        <th>陪练拉新</th>
        <th>渠道经理</th>
        <th>关注时间</th>
        <th>聊天</th>
        <th>操作</th>
    </tr>
    </thead>
    <tbody >
        <?php foreach ($data as $v) : ?>
        <tr>
            <td>
                <?= $v['username'] ?>
            </td>
            <td>
                <?= $v['type'] ?>
            </td>
            <td>
                <?= $v['money'] ?>
            </td>
            <td>
                <?= $v['channel_num'] ?>
            </td>
            <td>
                <?= $v['student_num'] ?>
            </td>
            <td>
                <?= $v['nickname'] ?>
            </td>
            <td>
                <?= $v['created_at'] ?>
            </td>
            <td>
                <li><a id = "chat_<?= $v['id'] ?>" class="talk_btn label label-success fa fa-comments" > </a></li>
            </td>
            <td>
                <a id = "<?= $v['id'] ?>" class="set_type label label-danger fa fa-edit" >设为无价值</a>
            </td>

            <tr id=<?= "user_hidden_".$v['id'] ?> class="hide">
                <td>
                    <input id="open_id" value=<?= $v['bind_openid']?>>
                </td>
            </tr>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
