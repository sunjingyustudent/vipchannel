<table class="table table-striped" style="width:100%">
    <thead>
        <tr>
            <th>用户昵称</th>
            <th>关注时间</th>
            <th>操作时间</th>
            <th>聊天</th>
        </tr>
    </thead>
    <tbody >
        <?php foreach ($data as $user): ?>
            <tr>
                <td><?= $user['nickname'] ?></td>
                <td><?= date('Y-m-d H:i:s',$user['created_at']) ?></td>
                <td><?= date('Y-m-d H:i:s',$user['create_time']) ?></td>
                <td>
                    <a sid = "<?= $user['open_id'] ?>" class="talk_btn_personal label label-success fa fa-comments" > </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>