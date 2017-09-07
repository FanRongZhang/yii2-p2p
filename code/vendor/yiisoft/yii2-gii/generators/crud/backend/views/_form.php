<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

/* @var $model \yii\db\ActiveRecord */
$model = new $generator->modelClass();
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $model->attributes();
}

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="update-form">

    <?= "<?php " ?>$form = ActiveForm::begin([
        'id' => 'member-form',
        'options' => ['class' => 'form-horizontal bui-form-horizontal bui-form bui-form-field-container'], 
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"controls\">{input}<span class=\"valid-text\">{error}</span></div>",
            'labelOptions' => ['class' => 'lable-text control-label'],
            'errorOptions'=>['class'=>'valid-text']
        ],
    ]); ?>

    
    
    
<?php 
$fields=array();
foreach ($generator->getColumnNames() as $attribute) {
    if (in_array($attribute, $safeAttributes)) {
        $fields[]=$attribute;
    }
}
for($i=0;$i<count($fields);$i++)
{
    if($i==0)
    {
        echo '<div class="row">'."\n";
    }
    echo "    <?= " . $generator->generateActiveFieldWithOptons($fields[$i],"['options'=>['class'=>'control-group span8']]") . " ?>\n";
    if($i==count($fields)-1)
        echo "</div>\n";
    else
    if($i%4==3)
        echo '</div>'."\n".'<div class="row">'."\n";
}


?>


<div class="row-btn">
    <div class="btn-group">
        <?= "<?= " ?>Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    
    </div>
     <div class="btn-group">
    <?= "<?= " ?>Html::a(Yii::t('app', 'Goback list'), ['index'], ['class' => 'btn btn-success']) ?>
    </div>
    <?= "<?php " ?>ActiveForm::end(); ?>

</div>
