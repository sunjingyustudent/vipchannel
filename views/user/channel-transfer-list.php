<table class="table">
    <thead>
    <tr>
        <th>更换时间</th>
        <th>学生姓名</th>
        <th>学生联系号</th>
        <th>学生来源</th>
        <th>原渠道名称</th>
        <th>原渠道手机</th>
        <th>新渠道名称</th>
        <th>新渠道手机</th>
        <th>备注</th>
        <th>操作</th>
    </tr>
    </thead>
    <tbody id="transfer_list" >
        <?php foreach ($item as $v) : ?>
        <tr>
            <td><?= $v['created_time'] ?></td>
            <td><?= $v['nick'] ?></td>
            <td><?= $v['mobile'] ?></td>
            <td><?= $v['type'] ?></td>
            <td><?= $v['old_name'] ?></td>
            <td><?= $v['old_mobile'] ?></td>
            <td><?= $v['new_name'] ?></td>
            <td><?= $v['new_mobile'] ?></td>
            <td><?= $v['remark'] ?></td>
            <td>
                
                <?php if($v['status']==0):?>
                    <a data-id="<?= $v['id']?>" data-sid="<?= $v['student_id'] ?>" class="transfer_channel_click label label-warning fa">
                    操作</a>
                <?php else:?>
                    <a data-id="<?= $v['id']?>" data-sid="<?= $v['student_id'] ?>" class="transfer_channel_click label label-success fa">
                    详情
                    </a>
                <?php endif;?>                    
                
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>


