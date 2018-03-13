<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 16/7/24
 * Time: 下午10:10
 */
?>
<div class="table-responsive">
    <table class="table ">
        <?php foreach ($list as $answer): ?>
            <tr>
                <td>
                    <li><a class="content" href="javascript:void(0)"><?= $answer['content'] ?></a>
                        <!--                        <input type="text"  style="display: none;" class="edit_input" placeholder="按Enter进行保存" >-->
                        <span  id="delete_<?= $answer['id'] ?>" class="update trash_btn label label-danger fa fa-trash-o"> </span>
                        <span id="edit_<?= $answer['id'] ?>" class="update edit_btn label label-warning fa fa-pencil-square-o"> </span>
                    </li>
                </td>
            </tr>
            <tr id="textEdit"  style="display: none;" >
                <td>
                    <textarea class="input-quick form-control" rows="5" cols="119" type="text" id="quick_<?= $answer['id'] ?>"></textarea>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

</div>
