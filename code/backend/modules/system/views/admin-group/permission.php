<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\enum\PermissionEnum;
use common\service\AdminService;

$this->title=Yii::t('app', 'Update Permisson') . ':  ' . $model->name;
?>
<div class="create-form">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="update-form">
        <?php $form = ActiveForm::begin([
            'id' => 'member-form',
            'options' => ['class' => 'form-horizontal bui-form-horizontal bui-form bui-form-field-container'], 
            'fieldConfig' => [
                'template' => "<div class=\"controls\">{input}{label}</div>",
                'labelOptions' => ['class' => 'checkbox-label']
            ],
        ]); ?>
    
    <?php 
        foreach($menu as $k=>$v){
            echo '<h3 class="groups">'.$v['name'].'</h3>';
    ?>
    <div class="row">
    
    <?php 
            foreach($v['children'] as $a=>$b)
            {
                if($b['permision_value']==0)
                    continue;
    ?>
    
<div class="checkbox-group">
	<div class="title"><?=$b['name']?></div>
	<div class="checkbox-container">
		<div class=" field-admingroup-permission" value="1">
			<div class="controls">
				<div class="admingroup-permission">
				<?php 
				    $pers = PermissionEnum::getPermissionArray($b['permision_value']);
				    if(count($pers)>0)
				    {
				        foreach($pers as $p=>$pv)
				        {
				            $checked=AdminService::isChecked($b['id'],$p,$model->permission);
				            echo '<label class="checkbox-label">
                            <input name="AdminGroup[permission]['.$b['id'].'][]" 
                            value="'.$p.'" type="checkbox" '.($checked?"checked":"").'>'.$pv.'</label>';
				        }
				    }
				?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php 
            }
?>
    </div>
<?php }?>
    <div class="row-btn">
        <div class="btn-group">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        
        </div>
         <div class="btn-group">
        <?= Html::a(Yii::t('app', 'Goback list'), ['index'], ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    
    </div>

</div>