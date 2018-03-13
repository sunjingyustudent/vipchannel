<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/7/21
 * Time: 下午7:29
 */
?>

<div class="transfer-server table-responsive">
    <table class="table">
        <thead>
        <tr>
            <th>头像</th>
            <th>姓名</th>
            <th>操作</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($list as $kefu): ?>
            <tr>
                <td><img class="img-circle" src=<?= $kefu['head'] ?>></td>
                <td><?= $kefu['nickname'] ?></td>
                <td>
                    <a href="javascript:void(0);" id='<?= $kefu['kefu_id'] ?>' class="transfer-btn">
                        <span id="" class="label label-success fa fa-exchange"> 转接</span>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
