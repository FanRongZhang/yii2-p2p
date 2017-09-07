<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use trntv\filekit\widget\Upload;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $model common\models\QfbBanner */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="fr memberright">
    <div class="member-main-box1">
        <?php $form = ActiveForm::begin([
            'id' => 'member-form',
            'options' => ['class' => 'form-horizontal bui-form-horizontal bui-form bui-form-field-container'],
            'fieldConfig' => [
                'template' => "{label}\n<div class=\"controls\">{input}<span class=\"valid-fontes\">{error}</span></div>",
                'labelOptions' => ['class' => 'lable-text control-label'],

            ],
        ]); ?>
        <div class="member-photo-mains">
            <?php
            echo $form->field($model, 'thumbnail')->widget(
                Upload::className(),
                [
                    'url' => ['upload'],
                    'acceptFileTypes' => new JsExpression('/(\.|\/)(gif|jpe?g|png)$/i'),
                    'maxFileSize' => 5000000, // 5 MiB
                ]);
            ?>
            <div><?= Html::submitButton(Yii::t('app', '保存头像'), ['class' => 'save-avatar']) ?></div>
            <div class="mar15 save-shuoite">
                <p class="shuo-tites">说明：</p>
                <ul>
                    <li>图片格式支持jpg/png/bmp</li>
                    <li>图片大小不超过1M</li>
                </ul>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<?php $this->beginBlock('test'); ?>










<?php $this->endBlock()  ?>
<?php $this->registerJs($this->blocks['test'], \yii\web\View::POS_END); ?>
