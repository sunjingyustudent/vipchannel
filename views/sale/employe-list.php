<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/11/9
 * Time: 下午3:39
 */
?>
<table class="table" id="employe-table">
    <thead>
    <tr>
        <th>销售uid</th>
        <th>销售名称</th>
        <th>销售电话</th>
        <th>工作时间</th>
        <th>状态</th>
<!--        <th>日可分配人数</th>-->
<!--        <th>分配到新签转换率</th>-->
<!--        <th>体验到跟踪转换率</th>-->
<!--        <th>分配到联系转换率</th>-->
<!--        <th>人均客单价(元)</th>-->
<!--        <th>2次以上付费人均客单价(元)</th>-->
<!--        <th>未付费人数</th>-->
<!--        <th>总业绩</th>-->
        <th>操作</th>
    </tr>
    </thead>
    <tbody >
    <?php foreach ($list as $kefu): ?>
        <tr id=<?= 'kefu_' . $kefu['id'] ?>>
            <td><?= $kefu['id'] ?></td>
            <td><?= $kefu['nickname'] ?></td>
            <td><?= $kefu['telephone_system_name'] ?></td>
            <td>
                <a><span id="work_time" class="fa fa-2x fa-pencil-square-o"></span></a>
            </td>

            <td>

                <?php if($kefu['status'] == 10): ?>
                    禁用
                <?php else: ?>
                    可用
                <?php endif; ?>

            </td>
            <td>
                <a id=" <?= "kefu_".$kefu['id'] ?>" class="update-employe label label-primary">编辑</a>
                <?php if($kefu['status'] == 10): ?>
                    <a id=" <?= "kefu_".$kefu['id'] ?>" class="open label label-success">启用</a>
                <?php else: ?>
                    <a id=" <?= "kefu_".$kefu['id'] ?>" class="del label label-warning">禁用</a>
                <?php endif; ?>


           </td>
        </tr>
    <?php endforeach; ?>

    </tbody>
</table>