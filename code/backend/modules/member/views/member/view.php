<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\enum\LevelEnum;
use common\service\MemberService;
?>
<div class="detail-view">

    <p>
        <?= Html::a(Yii::t('app','Update'), ['update', 'id' => $model->vid], ['class' => 'btn btn-primary']) ?>
        <?php
        /*= Html::a(Yii::t('app','Delete'), ['delete', 'id' => $model->vid], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) */
        ?>
        <?= Html::a(Yii::t('app','Goback list'), ['index'], ['class' => 'btn btn-primary']) ?>
        
    </p>
    <div class='tabs'>
    <ul>
        <li class="active"><a href="#home" data-toggle="tab"><?=Yii::t('app','基本信息')?></a></li>
        <li class=""><a href="/member/member/wealth?id=<?=$model->vid?>" data-toggle="tab"><?=Yii::t('app','会员资产') ?></a></li>
        <li class=""><a href="/member/member/product?id=<?=$model->vid?>" data-toggle="tab"><?=Yii::t('app','在投项目') ?></a></li>
        <li class=""><a href="/member/meminfo/one-contacts?id=<?=$model->vid?>" data-toggle="tab"><?=Yii::t('app','一度人脉') ?></a></li>
        <li class=""><a href="/member/meminfo/two-contacts?id=<?=$model->vid?>" data-toggle="tab"><?=Yii::t('app','二度人脉') ?></a></li>
        <li class=""><a href="/member/meminfo/voucher?id=<?=$model->vid?>" data-toggle="tab"><?=Yii::t('app','代金券明细') ?></a></li>

    </ul>
        <div class="tab-pane fade active in" id="home">
    <?= DetailView::widget([
        'model' => $model,
        'template' => '<tr><th>{label}</th><td>{value}</td></tr>',
        'attributes' => [
            'vmobile' ,
            'vrealname',
            'vaccount',
            [
                'attribute'=>'vstatus',
                'value'=> $model->vstatus==1 ?"正常": "冻结",

            ],
            [
                'attribute'=>'vlevel',
                'value'=> LevelEnum::getName($model->vlevel),

            ],
            [
                'attribute'=>'vrmember_mobile',
                'value'=>  !empty(MemberService::findModelById($model->vr_member_id)) ? MemberService::findModelById($model->vr_member_id)->mobile : "",
            ],
            [
                'attribute'=>'vrmember_realname',
                'value'=>  !empty(MemberService::findModelById($model->vr_member_id)) ? MemberService::findModelById($model->vr_member_id)->memberInfo->realname : "",
            ],
            //来源1钱富宝 2分享注册 3PC官网 4手机官网 5中盾商城
            [
                'attribute'=>'vsource',
                'value'=> !empty($model->vsource) ? \common\enum\MemberEnum::getName($model->vsource) : '',

            ],
            [
                'attribute'=>'vchannel_id',
                'value'=> $model->vchannel_id==1 ?"安卓": "苹果",

            ],

            [
                'attribute'=>'vlive_money',
                'value'=> $model->vlive_money+$model->vpre_live_money,

            ],
            'vfix_money',
            [
                'attribute'=>'证件类型',
                'value'=> $model->vcard_type==1 ?"身份证": "港澳通行证",

            ],
            [
                'attribute'=>'证件号',
                'value'=> $model->vcard_no,

            ],
            [
                'attribute'=>'vcreate_time',
                'value'=> date("Y-m-d H:i:s",$model->vcreate_time),

            ]
        ],
    ]) ?>
        </div>

</div>
<script>

    $('.tabs').tabs({
        load: function(event, ui) {
            $(ui.panel).on( 'click','a', function(event) {
                $(ui.panel).load(this.href);
                event.preventDefault();
            });
        },
        beforeLoad: function( event, ui ) {
            ui.jqXHR.error(function() {
                ui.panel.html(
                    "不能加载该标签页。尽快修复这个问题。" );
            });
        }
    });
</script>