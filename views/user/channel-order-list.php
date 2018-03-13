<table class="table">
    <thead>
    <tr>
        <th>买单时间</th>
        <th>买单金额</th>
        <th>8%佣金换算</th>
    </tr>
    </thead>
    <tbody >
        <?php foreach ($item as $v) : ?>
        <tr>
            <td><?= date("Y-m-d H:i:s",$v['time_created']) ?></td>
            <td><?= $v['actual_fee'] ?></td>
            <td><?= $v['actual_fee']*0.08?><td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>


