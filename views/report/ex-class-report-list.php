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
        <th>学生姓名</th>
        <th>手机号</th>
        <th>渠道老师姓名</th>
        <th>渠道老师手机号</th>
        <th>体验课时间</th>
        <th>渠道经理姓名</th>
        <th>备注</th>
        <?php if ($type == 1 && $status == 1) : ?>
            <th>是否跟进</th>
        <?php endif; ?>
        <th>操作</th>


    </tr>
    </thead>
    <tbody >
<!--    u.remark, u.username, u.mobile, sc.id AS teacher_id, sc.wechat_name AS teacher_name,-->
<!--    sc.username AS teacher_mobile, c.time_class, ua.nickname AS kefu_name-->


        <?php foreach ($data as $v) : ?>
        <tr>
            <td>
                <li><?= $v['nick'] ?></li>
            </td>
            <td>
                <li><?= $v['mobile'] ?></li>
            </td>
            <td>
                <li><?= $v['nickname'] ?></li>
            </td>
            <td>
                <li><?= $v['username'] ?></li>
            </td>
            <td>
                <?php if (empty($v['ex_class_time'])) : ?>
                    暂无
                <?php else : ?>
                    <li> <?=  date('Y-m-d H:i', $v['ex_class_time']) ?></li>
                <?php endif; ?>


            </td>
            <td>
                <li><?= $v['kefu_name'] ?></li>
            </td>
            <td>
                <li><?= $v['remark'] ?></li>
            </td>

            <?php if ($type == 1 && $status == 1) : ?>
                <td>
                    <li>
                        <?php if (empty($v['visit'])) : ?>
                            <span class="label label-danger " >未跟进</span>
                        <?php else : ?>
                            <span class="label label-success " >已跟进</span>
                        <?php endif; ?>
                    </li>
                </td>
            <?php endif; ?>

            <td>
                <li><a id = "chat_<?= $v['sales_id'] ?>" class="talk_btn label label-success fa fa-comments" > </a></li>
            </td>

            <tr id=<?= "user_hidden_".$v['sales_id'] ?> class="hide">
                <td>
                    <input id="open_id" value=<?= $v['bind_openid']?>>
                </td>
            </tr>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
