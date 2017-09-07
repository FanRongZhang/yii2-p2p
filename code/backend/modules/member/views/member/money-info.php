<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */
/* @var $searchModel backend\modules\member\models\Membersearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '收支明细');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="search-form">

    <?php $form = ActiveForm::begin([
        'action' => ['money-info'],
        'method' => 'get',
    ]); ?>
    <input type="hidden" name="id" value="<?=$_GET['id']?>">
    <?=$form->field($searchModel, 'type')->dropDownList(['' => '全部','1' => '收入','2' => '支出']) ?>
    <?=$form->field($searchModel, 'create_time')->widget(common\widgets\datepicker\DatePicker::className(),[
        'options'=>[
            'istime'=>true,
            'readonly'=>true,
            'format'=>'YYYY-MM-DD'
        ]
    ])?>
    <?=$form->field($searchModel, 'create_time_end')->widget(common\widgets\datepicker\DatePicker::className(),[
        'options'=>[
            'istime'=>true,
            'readonly'=>true,
            'format'=>'YYYY-MM-DD'
        ]
    ])?>
    <div class="form-group search-button">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<div class="list-index">



    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'label'=>"收支类型",
                'attribute'=>'type',
                'content'=>function($model){
                    if($model->type == 1) {
                        return "收入";
                    }else{
                        return "支出";
                    }
                }
            ],
            'money',
            [
                'label'=>"交易类型",
                'attribute'=>'action',
                'content'=>function($model){
                    return \common\enum\MoneyLogActionEnum::getName($model->action);
                }
            ],
            'remark',
            [
                'attribute'=>'create_time',
                'content'=>function($model){
                    return date("Y-m-d H:i:s",$model->create_time);
                }
            ],

        ],
    ]); ?>

</div>
