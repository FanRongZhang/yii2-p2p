<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = '消息内容';
$this->params['breadcrumbs'][] = $this->title;


?>

<div class="fr memberright">
    <div class="member-main-box1">
        <div class="member-message-detail">
            <div class="member-mesage-tites">
                <h2><?php echo $data['title']; ?></h2>
                <p><?php echo date('Y-m-d H:i:s', $data['send_time']); ?></p>
            </div>
            <div class="member-cont-mesage">
                <p><?php echo $data['content'];?></p>
            </div>
        </div>

    </div>
</div>
