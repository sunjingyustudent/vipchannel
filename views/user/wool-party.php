<?php
/**
 * Created by PhpStorm.
 * User: wangke
 * Date: 17/05/03
 * Time: 下午15:42
 */

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
?>
<div class="wool-party">
    <div class="head">

        <ul style="float:left;padding-left: 0px;margin-left: 12px;list-style-type: none">
            <li>
                <?= Html::dropDownList(null, null, ArrayHelper::map($kefuList, 'id', 'nickname'), [
                    'class' => 'form-control',
                    'style' => 'width: 188px',
                    'id'=>'kefu-type'
                ]); ?>
            </li>

        </ul>
    </div>
    <div id="list-content" class="col-md-12"></div>
</div>