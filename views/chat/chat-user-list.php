<table class="table table-striped">
    <thead>
    <tr>
        <th>头像</th>
        <th>微信昵称</th>
        <th>关注时间</th>
        <th>绑定顾问</th>
        <th>地域</th>
        <th>接入聊天</th>
    </tr>
    </thead>
    <tbody >
    <?php foreach ($list as $user): ?>
        <tr>
            <td><img width="50px" src=<?= "{$user['head']}" ?>></td>
            <td><?= $user['nickname'] ?></td>
            <td><?= $user['created_at'] ?></td>
            <td><?= empty($user['kefu_nickname']) ? '暂无客服' : $user['kefu_nickname'] ?></td>
            <td><?= $user['province'] ?></td>
            <td>
                <span id='<?= "wait_".$user['id'] ?>' class="access_btn label label-success fa fa-comments"> </span>
            </td>
        </tr>
    <?php endforeach; ?>
    <input type="text" id="page" hidden value="<?= $type ?>">
    </tbody>
</table>