<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/6/24
 * Time: 下午4:07
 */

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;

\channel\assets\AppAsset::register($this);
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body>
    <?php $this->beginBody() ?>

    <div class="wrap">
        <div class="portal-header">
            <div class="logoInfo">
                <img src="/images/icon.jpg" />
            </div>
            <div class="navInfo">
                <a href="#" id="new-user" >新用户1</a>
                <a href="#" id="unbuy-user" >注册未付费用户</a>
                <a href="#" id="buy-user" >注册已付费用户</a>
                <a href="#" id="danger_user" >高危用户</a>
            </div>

            <div class="userInfo">
                <?=Yii::$app->user->identity->username?>
            </div>
            <div class="clearAll"></div>
        </div>
        <div class="portal-container">
            <?= $content ?>
        </div>
    </div>

    <?php $this->endBody() ?>
    <script>
        $(function () {
            $(".body-content").height(document.body.clientHeight-100);
            $(".rightPanel").width(document.body.clientWidth-202);
        });
    </script>
    </body>
    </html>
<?php $this->endPage() ?>