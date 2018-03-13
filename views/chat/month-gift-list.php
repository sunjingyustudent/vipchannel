<table class="table table-striped" style="width:100%" id="detailPage">
    <thead>
        <tr>
            <th>奖励时间</th>
            <th>用户名</th>
            <th>手机号</th>
            <th>奖励类型</th>
            <th>奖励金额</th>
            <th>渠道经理</th>
            <th>聊天</th>
        </tr>
    </thead>
    <tbody >
        <?php foreach ($data as $v) : ?>
            <tr id="<?= $v['id'] ?>">
                <td><?= date('Y-m-d H:i:s', $v['time_created']) ?></td>

                <td><?= $v['nickname']?></td>
                <td><?= $v['username']?></td>
                <td><?= $v['status']?></td>
                <td><?= $v['money']?></td>
                <td><?= $v['kefuName']?></td>
                <td>
                    <a sid = "<?= $v['bind_openid'] ?>" class="talk_btn_gift label label-success fa fa-comments" > </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>