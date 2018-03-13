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
        <th>用户信息</th>
        <th>客服</th>
        <th>联系信息</th>
        <th>渠道信息</th>

        <th>用户身份</th>
        <th>用户标记类型</th>
        <th>上次沟通时间</th>
        <th>分配</th>
        <th>聊天</th>
        <th>操作</th>
    </tr>
    </thead>
    <tbody >
        <?php foreach ($user as $v) : ?>
        <tr id = '<?= $v['id']?>'>
            <td>
                <li>用户姓名：<?= $v['nickname'] ?><span style="color:<?= $v['money_color'] ?>"><?= $v['status'] ?></span></li>
                <li>微信名称：<?= $v['wechat_name']?></li>
                <li>用户地区：<?= $v['province']?></li>
            </td>
            <td class="kefu-nick">
                <li> <?= $v['kefu_nickname'] ?></li>
            </td>
            <td>
                <li>用户手机：<?= $v['mobile'] ?></li>
            </td>
            <td>
                <li>关注时间：<?= $v['follow_time'] ?></li>
                <li>渠道：    <?= $v['code'] ?></li>
            </td>


            <td>
                <li> <?= $v['user_type'] ?></li>
            </td>
            <td>
                <li><?= $v['worth'] ?></li>
            </td>
            <td>
                <li><?= $v['day'] ?></li>
            </td>
            <td>
                <span sid="<?= $v['kefu_id'] ?>" id="distrbute_<?= $v['id'] ?>" class="distrbute_btn label label-primary ">重新分配 </span>
            </td>
            <td>
                <li><a id = "chat_<?= $v['id'] ?>" class="talk_btn label label-success fa fa-comments" > </a></li>
            </td>
            <td>
                <span id="delete_<?= $v['id'] ?>" class="delete_btn label label-danger fa fa-trash-o"> </span>
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


