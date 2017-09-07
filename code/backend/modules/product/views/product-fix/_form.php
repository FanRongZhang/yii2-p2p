<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\enum\ProductEnum;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $model common\models\QfbProduct */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="update-form">

    <?php $form = ActiveForm::begin([
        'id' => 'member-form',
        'options' => ['class' => 'form-horizontal bui-form-horizontal bui-form bui-form-field-container'], 
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"controls\">{input}<span class=\"valid-text\">{error}</span></div>",
            'labelOptions' => ['class' => 'lable-text control-label'],
            'errorOptions'=>['class'=>'valid-text']
        ],
    ]); ?>

    
<div class="row">
    <?= $form->field($model, 'product_name',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
</div>
<div class="row">  <!-- [''=>'请选择产品类型','1'=>'活期','2'=>'定期'] -->
    <?= $form->field($model, 'product_type',['options'=>['class'=>'control-group span8']])->dropDownList([''=>'请选择产品类型','2'=>'定期']) ?>
</div>
<div class="row"> <!-- [''=>'请选择产品分类','1'=>'新手专享理财','2'=>'活期理财','3'=>'定期抵押贷'] -->
    <?= $form->field($model, 'category_id',['options'=>['class'=>'control-gorup span8']])->dropDownList([''=>'请选择产品分类','1'=>'新手专享理财','3'=>'定期抵押贷']) ?>
</div>
<div class="row">
    <?= $form->field($model, 'profit_type',['options'=>['class'=>'control-group span8']])->dropDownList(ProductEnum::getProfitType()) ?>
</div>
<div class="row">
    <?= $form->field($model, 'stock_money',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
</div>
<div class="row">
    <?= $form->field($model, 'platform_income',['options'=>['class'=>'control-group span8', 'style'=>'display:none;']])->textInput(['maxlength' => true]) ?>
</div> 
<div class="row">
    <?= $form->field($model, 'platform_income_rate',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
</div>
<div class="row">
    <?= $form->field($model, 'year_rate',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
</div>
<div class="row">
    <?= $form->field($model, 'has_money',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true, 'value'=>0, 'readonly'=>'readonly']) ?>
</div>
<div class="row">
    <?= $form->field($profitmodel, 'recommond_rate',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true, 'value'=>0, 'readonly'=>'readonly']) ?>
</div>
<div class="row">
    <?= $form->field($profitmodel, 'manage_rate',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true, 'value'=>0, 'readonly'=>'readonly']) ?>
</div>
<div class="row">
    <?= $form->field($profitmodel, 'agent_rate',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true, 'value'=>0, 'readonly'=>'readonly']) ?>
</div>
<div class="row">
    <?= $form->field($model, 'lock_day',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true, 'value'=>0, 'readonly'=>'readonly']) ?>
</div>
<div class="row">
    <?= $form->field($model, 'invest_day',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
</div>
<div class="row">
    <?= $form->field($model, 'profit_day',['options'=>['class'=>'control-group span8']])->dropDownList(ProductEnum::getProfitDay()) ?>
</div>
<div class="row">
    <?= $form->field($model, 'min_money',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
</div>
<div class="row">
    <?= $form->field($model, 'step_money',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
</div>
<div class="row">
    <?= $form->field($model, 'max_money',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
</div>
<div class="row" style="margin-left:4px;margin-top:10px;">
    <?=$form->field($model, 'end_time')->widget(
        'trntv\yii\datetime\DateTimeWidget',
        [
            'phpDatetimeFormat' => 'dd.MM.yyyy, HH:mm',
            'inputAddonOptions' => [
                'style' => 'width:0%;',
            ],
            'clientOptions' => [
                'minDate' => new \yii\web\JsExpression('new Date("2015-01-01")'),
                'allowInputToggle' => false,
                'sideBySide' => true,
                'locale' => 'zh-cn',
                'widgetPositioning' => [
                   'horizontal' => 'auto',
                   'vertical' => 'auto'
                ],
            ],
        ]
    ); ?>
</div>

<div class="row member" >
    <div class="control-group span8 field-qfbproduct-member_id required has-success">
        <label class="lable-text control-label" for="qfbproduct-member_id">收款人账号</label>
        <div class="controls">
            <input type="text" id="account" class="form-control" value="" >
            <span class="valid-text"></span>
        </div>
    </div>
    <button type="button" class="btn btn-success" onClick="chakan()">查询</button>
</div>

<div class="row" style="display: none;">
    <div class="control-group span8 field-qfbproduct-member_id required">
        <label class="lable-text control-label" for="qfbproduct-member_id">收款人ID</label>
        <div class="controls">
            <input id="qfbproduct-member_id" class="form-control" name="QfbProduct[member_id]" type="text" readonly="readonly">
            <span class="valid-text">
                <div class="valid-text"></div>
            </span>
        </div>
    </div>
</div>

<div class="row member" style="display:none;">
    <div class="control-group span8 field-qfbproduct-member_id required has-success">
        <label class="lable-text control-label" for="qfbproduct-member_id">收款人姓名</label>
        <div class="controls">
            <input type="text" id="member_name" class="form-control" value="" readonly="readonly">
            <span class="valid-text"></span>
        </div>
    </div>
</div>

<div class="row member" style="display:none;">
    <div class="control-group span8 field-qfbproduct-member_id required has-success">
        <label class="lable-text control-label" for="qfbproduct-member_id">收款人身份证号</label>
        <div class="controls">
            <input type="text" id="member_card" class="form-control" value="" readonly="readonly">
            <span class="valid-text"></span>
        </div>
    </div>
</div>

<div class="row member" style="display:none;">
    <div class="control-group span8 field-qfbproduct-member_id required has-success">
        <label class="lable-text control-label" for="qfbproduct-member_id">收款人银行卡号</label>
        <div class="controls">
            <input type="text" id="member_bankno" class="form-control" value="" readonly="readonly">
            <span class="valid-text"></span>
        </div>
    </div>
</div>

<div class="row member" style="display:none;">
    <div class="control-group span8 field-qfbproduct-member_id required has-success">
        <label class="lable-text control-label" for="qfbproduct-member_id">收款人所属银行</label>
        <div class="controls">
            <input type="text" id="member_bankcard" class="form-control" value="" readonly="readonly">
            <span class="valid-text"></span>
        </div>
    </div>
</div>

<div class="row">
    <?= $form->field($model, 'is_newer',['options'=>['class'=>'control-group span8']])->radioList(['0'=>'否','1'=>'是']) ?>
</div>
<div class="row">
    <?= $form->field($model, 'can_rate_ticket',['options'=>['class'=>'control-group span8']])->radioList(['0'=>'否','1'=>'是']) ?>
</div>
<div class="row">
    <?= $form->field($model, 'can_money_ticket',['options'=>['class'=>'control-group span8']])->radioList(['0'=>'否','1'=>'是']) ?>
</div>
<div class="row">
    <?= $form->field($model, 'is_index',['options'=>['class'=>'control-group span8']])->radioList(['0'=>'否','1'=>'是']) ?>
</div>
<div class="row">
    <?= $form->field($model, 'is_hidden',['options'=>['class'=>'control-group span8']])->radioList(['0'=>'否','1'=>'是']) ?>
</div>

<div class="row">
    <?= $form->field($model, 'address',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
</div>



<div class="row">
    <?= $form->field($model, 'warranty_type',['options'=>['class'=>'control-group span8 baozheng']])->dropDownList(['0'=>'请选择','1'=>'质押保证','2'=>'抵押担保','3'=>'保证担保']) ?>
</div>



<div style="display:none;" class="baozheng1">
    <div class="row">
        <?= $form->field($warranty, 'plate_number',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    </div>

    <div class="row">
        <?= $form->field($warranty, 'model',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    </div>

    <div class="row">
        <?= $form->field($warranty, 'engine_number',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    </div>

    <div class="row">
        <?= $form->field($warranty, 'vin',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    </div>
</div>


<div class="row">
    <?= $form->field($warranty, 'contract_number',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
</div>


<div style="display:none;" class="baozheng2">
    <div class="row">
        <?= $form->field($warranty, 'warrantor',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    </div>

    <div class="row">
        <?= $form->field($warranty, 'id_card',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    </div>

    <div class="row">
        <?= $form->field($warranty, 'mobile',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    </div>

    <div class="row">
        <?= $form->field($warranty, 'guarantee_way',['options'=>['class'=>'control-group span8']])->dropDownList(['1'=>'一般保证','2'=>'连带责任保']) ?>
    </div>
</div>

<div class="row">
    <?php  if($model->isNewRecord){?>
    <?=$form->field($agreemodel, 'agreement_id')->checkboxList(ArrayHelper::map($agreements,'id', 'title')) ?>
    <?php }else{?>
        <div class="form-group field-qfbproductagreement-agreement_id has-success">
            <label class="lable-text control-label" for="qfbproductagreement-agreement_id">产品协议</label>
            <div class="controls">
                <?php foreach($agreements as $key=>$v){ ?>
                    <label><input type="checkbox" <?php if(isset($v['product_agreement']['agreement_id'])) echo "checked";?>  name="QfbProductAgreement[agreement_id][]" value=<?=isset($v['product_agreement']['agreement_id'])?$v['product_agreement']['agreement_id']:$v['id'] ?> ><?=$v['title']?></label>
                <?php }?>
        </div>
    <?php }?>
</div>
<div class="row">
    <?= $form->field($detailmodel, 'content',['options'=>['class'=>'control-group span8']])->textarea(['rows' => 5,'cols' => 20]) ?>
</div>
<div class="row">
    <?php
        echo $form->field($detailmodel, 'detail')->widget(
            \yii\imperavi\Widget::className(),
            [
                'plugins' => ['fullscreen', 'fontcolor', 'video','image'],
                'options' => [
                    'minHeight' => 400,
                    'maxHeight' => 400,
                    'buttonSource' => true,
                    'convertDivs' => false,
                    'removeEmptyTags' => false,
                    'imageUpload' => Url::to(['upload-imperavi'])
                ]
            ]
        );
    ?>
</div>

<div class="row-btn">
    <div class="btn-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '创建') : Yii::t('app', '编辑'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
     <div class="btn-group">
    </div>
    <?php ActiveForm::end(); ?>
</div>
<script>
    jQuery(document).ready(function() {
        $('.baozheng select').change(function(){
            var item = $(this).val();
            if(item == 2){
                $('.baozheng1').css('display', 'none');
                $('.baozheng2').css('display', 'none');
            }else if(item == 3){
                $('.baozheng1').css('display', 'none');
                $('.baozheng2').css('display', 'block');
            }else if(item == 1){
                $('.baozheng1').css('display', 'block');
                $('.baozheng2').css('display', 'none');
            }
        });

        
    });


    function chakan(){

        var account = $('#account').val();

        if(account == ''){
            alert('收款人账号不能为空');
            return false;
        }

        posturl = '<?php echo Url::toRoute("/product/product-fix/memberinfo");?>';
        $.post(posturl, {account:account}, function(result){

            if (result.status == "success") {
                $(".member").css('display','block');
                $('#qfbproduct-member_id').val(result.res.member_id);
                $('#member_name').val(result.res.username);
                $('#member_card').val(result.res.card_no);
                $('#member_bankno').val(result.res.bankno);
                $('#member_bankcard').val(result.res.bankname);
            } else {
                alert(result.message);
            }
        }, 'json');
    }
    
</script>