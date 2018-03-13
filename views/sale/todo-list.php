<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/5
 * Time: 下午2:46
 */
?>
<?php if ($list) : ?>
<table class="table">
    <thead>
    <tr>
        <th>用户头像</th>
        <th style="width: 200px">用户昵称</th>
        <th style="width: 200px">关注时间</th>
        <th style="width: 200px">本次待跟进内容</th>
        <th>聊天</th>
    </tr>
    </thead>
    <tbody style="list-style: none;">
    <?php foreach ($list as $row) : ?>
    <tr>
        <td><span><img src="<?= $row['head'] ?>" alt=""></span></td>
        <td><?= $row['nickname'] ?></td>
        <td><?= $row['created_at'] ?></td>
        <td><div class="next_content"><?= $row['next_content'] ?></td>
        <td>
            <a  href="javascript:void(0);" class='chat-todo'>
                <span id="chat_<?=$row['uid']?>" class="talk_btn label label-success fa fa-comments"> 聊天</span>
            </a>
        </td>
        <td>
            <div class="hide" id="user_hidden_<?=$row['uid'] ?>">
                <input type="text" hidden id="open_id" value="<?=$row['bind_openid'] ?>" >
            </div>
        </td>
    </tr>

    <?php endforeach; ?>
    </tbody>
</table>
<?php else : ?>
        <div class="noData"><span class="fa fa-times-circle"> 没有找到数据!</span></div>
<?php endif; ?>
