<table class="table table-striped">
    <thead>
    <?php if ($type == 1) : ?>
    <tr>
        <th>关注时间</th>
        <th>微信昵称</th>
    </tr>
    <?php elseif ($type == 2) : ?>
        <tr>
            <th>注册时间</th>
            <th>学生名称</th>
            <th>手机号码</th>
        </tr>
    <?php elseif ($type == 3) : ?>
        <tr>
            <th>体验时间</th>
            <th>学生名称</th>
            <th>手机号码</th>
        </tr>
    <?php else : ?>
        <tr>
            <th>买单时间</th>
            <th>学生名称</th>
            <th>手机号码</th>
            <th>金额</th>
        </tr>
    <?php endif; ?>
    </thead>
    <tbody >
    <?php if ($type == 1) : ?>
        <?php foreach ($data as $user): ?>
            <tr>
                <td><?= $user['time_created'] ?></td>
                <td><?= $user['wechat_name'] ?></td>
            </tr>
        <?php endforeach; ?>
    <?php elseif ($type == 2) : ?>
        <?php foreach ($data as $user): ?>
            <tr>
                <td><?= $user['time_created'] ?></td>
                <td><?= $user['nick'] ?></td>
                <td><?= $user['mobile'] ?></td>
            </tr>
        <?php endforeach; ?>
    <?php elseif ($type == 3) : ?>
        <?php foreach ($data as $user): ?>
            <tr>
                <td><?= $user['time_created'] ?></td>
                <td><?= $user['nick'] ?></td>
                <td><?= $user['mobile'] ?></td>
            </tr>
        <?php endforeach; ?>
    <?php elseif ($type == 4) :?>
        <?php foreach ($data as $user): ?>
            <tr>
                <td><?= $user['time_created'] ?></td>
                <td><?= $user['nick'] ?></td>
                <td><?= $user['mobile'] ?></td>
                <td><?= $user['money'] / 0.08 ?></td>
            </tr>
        <?php endforeach; ?>
    <?php elseif ($type == 5) :?>
        <?php foreach ($data as $user): ?>
            <tr>
                <td><?= $user['time_created'] ?></td>
                <td><?= $user['nick'] ?></td>
                <td><?= $user['mobile'] ?></td>
                <td><?= $user['money'] / 0.08 * 2 ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>