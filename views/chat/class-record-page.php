<?php
/**
 * Created by Sublime.
 * User: sjy
 * Date: 16/12/12
 * Time: 下午6:34
 */
?>
<table class="table table-striped">
    <?php if ($type == 1) : ?>
        <?php foreach ($list as $v) : ?>
            <tr>
                <td>
                    <span class="class_name">学生姓名: <?= $v['nick'] ?></span>
                </td>
                <td>
                    <span class="class_time">课程完成时间： <?= date('Y-m-d H:i:s', $v['time_end']) ?></span>
                </td>
                <td>
                    <span id="send_<?= $v['class_id'] ?>" class="send label label-success fa fa-paper-plane">发送</span>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php elseif ($type == 2) : ?>    
        <?php foreach ($list as $v) : ?>
            <tr>
                <td>
                    <span class="class_name">学生姓名: <?= $v['nick'] ?></span>
                </td>
                <td>
                    <span class="class_time">取消原因： <?= $v['undo_reason'] ?></span>
                </td>
                <td>
                    <span id="send_<?= $v['class_id'] ?>_word" class="sendword label label-success fa fa-paper-plane">发送客服消息</span>
                </td>
                <td>
                    <span id="send_<?= $v['class_id'] ?>_moban" class="sendmoban label label-danger fa fa-paper-plane">发送模板消息</span>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else : ?>     
            <div style="width: 50%;height: 50px;">
                <span id="send_no_ex_student" class="label label-danger fa fa-paper-plane" rel='<?= $keyword ?>'>发送消息</span> 
            </div>
        <?php foreach ($list as $v) : ?>
            <tr>
                <td>
                    <span class="class_name">昵称/微信名 :(<?= $v['nick'] ?>/<?= $v['name'] ?>)</span>
                </td>
                <td>
                    <span class="class_time">手机号： <?= $v['mobile'] ?></span>
                </td>
                <td>
                    <span class="class_time">关注时间：<?= date('Y-m-d H:i:s', $v['subscribe_time']) ?></span>
                </td>
            </tr>
        <?php endforeach; ?>
            
    <?php endif; ?>
</table> 




