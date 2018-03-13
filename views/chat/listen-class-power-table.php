<?php if($list): ?>
    <table class="table listen-table " style="margin-top: 20px;">
        <?php foreach ($list as $v): ?>
            <tr>
                <td>
                    <li>
                        <span class="class_name <?= $v['color']?>">课程名称：<?=$v['title'] ?> <?=$v['info']?> <?= $v['back'] ?></span>
                    </li>
                    <li>
                        老师名称：<?= $v['teacher_name'] ?>
                    </li>
                    <li class="class_power_<?= $v['id'] ?>">
                        <?= $v['purview'] ?>
                    </li>
                </td>
                <td>
                    <li>
                        <?=$v['class_time'] ?>
                    </li>
                    <li>
                        <!--                --><?//=$v['share_time'] ?>
                    </li>
                </td>
                <td>
                    <span class="search_power_btn label label-success fa fa-sign-in" style="cursor: pointer; ">添加权限</span>
                    <input type="text" hidden value="<?= $v['id'] ?>" class="wechat_class_id" >
                    <input type="text" hidden value="<?= $v['is_back'] ?>" class="back_id" >
                    <span class="send-class-link label label-blue fa fa-paper-plane"  style="cursor: pointer; ">发送链接</span>
                    <input type="text" hidden value="<?= $v['url'] ?>" >
                    <input type="text" hidden value="<?= $v['title'] ?>" >
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <div class="noData"><span class="fa fa-times-circle"> 没有任何现在可以被上的课程!</span></div>
<?php endif; ?>