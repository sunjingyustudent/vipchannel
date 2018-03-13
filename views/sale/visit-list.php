<?php
/**
 * Created by Sublime.
 * User: wanhkai
 * Date: 16/12/12
 * Time: 下午6:34
 */
?>
<?php foreach ($list as $v) :?>
    <div class="visit_div">
        <div class="visit_head">
            <p>客服： <?= $v['nickname'] ?>
                <a class="is_done" id="<?= $v['id'] ?>"  sid='<?= $v['is_done'] ?>' href="javascript:void(0);" >
                    <?php if ($v['is_done'] == 1) : ?>
                        <span class="label label-success fa fa-edit" style="padding: 3px 2px">已跟进</span>
                    <?php else : ?>
                        <span class="label label-danger fa fa-edit"  style="padding: 3px 2px">未跟进</span>
                    <?php endif; ?>
                </a>
            </p>
            <p>关联体验课： <?= $v['exClassInfo'] ?></p>
            <p>时间： <?= $v['time_visit'] ?></p>
            <p>内容： <?= $v['content'] ?></p>
        </div>
        <div class="visit_foot">
            <p style="margin-top: 10px">下次跟进时间： <?= $v['time_next'] ?> </p>
            <p>下次跟进备注： <?= $v['next_content'] ?> </p>
        </div>
    </div>
<?php endforeach; ?>

