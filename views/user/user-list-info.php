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
        <th>联系信息</th>
        <th>渠道信息</th>
        <th>用户状态</th>
        <th>用户标记类型</th>
        <th>上次沟通时间</th>
        <th>聊天</th>
    </tr>
    </thead>
    <tbody >
        <?php foreach ($user as $v) : ?>
        <tr>
            <td>
                <li>用户姓名：<?= $v['nickname'] ?><span style="color:<?= $v['money_color'] ?>"><?= $v['status'] ?></span></li>
                <li>微信名称：<?= $v['wechat_name']?></li>
                <li>用户地区：<?= $v['province']?></li>
            </td>
            <td>
                <li>用户手机：<?= $v['mobile'] ?></li>
                <li>顾问名称：<?= $v['kefu_name'] ?></li>
            </td>
            <td>
                <li>关注时间：<?= $v['follow_time'] ?></li>
                <li>渠道：    <?= $v['code'] ?></li>
            </td>
            <td>
                <li>
                    <?php if ($v['subscribe'] == '1') : ?>
                        关注
                    <?php else : ?>
                        <span style="color:#EB3F2F"> 取消关注 </span>
                    <?php endif; ?>
                </li>
            </td>
            <td>
                <li><?= $v['worth'] ?></li>
            </td>
            <td>
                <li><?= $v['day'] ?></li>
            </td>
            <td>
                <li><a id = "chat_<?= $v['id'] ?>" class="talk_btn label label-success fa fa-comments" > </a></li>
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
