<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\enum\LevelEnum;
use common\enum\PermissionEnum;
use common\service\AdminService;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\member\models\Membersearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '会员管理');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>


    
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'pager'=>array(
            'firstPageLabel'=>'首页',
            'lastPageLabel'=>'尾页',
            'nextPageLabel'=>'下一页',
            'prevPageLabel'=>'前一页',
        ),
        'columns' => [
            'vmobile' ,
            'vrealname',
            'vaccount',
            [
                'attribute'=>'vstatus',
                'content'=>function($model){
                    if($model->vstatus==1)
                        return "正常";
                    else
                        return "冻结";
                }
            ],
            [
                'attribute'=>'vmember_type',
                'content'=>function($model){
                    if($model->vmember_type==1)
                        return "投资人";
                    else
                        return "借款人";
                }
            ],
            [
                'attribute'=>'vlevel',
                'content'=>function($model){
                    return LevelEnum::getName($model->vlevel);
                }
            ],
            [
                'attribute'=>'推荐人手机号',
                'content'=>function($model){
                    $memServ = \common\service\MemberService::findModelById($model->vr_member_id);
                    return isset($memServ->mobile) ?$memServ->mobile:"";
                }
            ],
            [
                'attribute'=>'推荐人姓名',
                'content'=>function($model){
                    $memServ = \common\service\MemberService::findModelById($model->vr_member_id,["memberInfo"]);
                    return isset($memServ->memberInfo->realname)?$memServ->memberInfo->realname:"";
                }
            ],
            //来源1钱富宝 2分享注册 3PC官网 4手机官网 5中盾商城
            [
                'attribute'=>'vsource',
                'content'=>function($model){
                    return !empty($model->vsource) ? \common\enum\MemberEnum::getName($model->vsource) : '';
                }
            ],
            [
                'attribute'=>'vchannel_id',
                'content'=>function($model){
                    if($model->vchannel_id==1)
                        return "安卓";
                    elseif($model->vchannel_id==2)
                        return "苹果";
                    else
                        return 'PC';
                }
            ],

            [
                'attribute'=>'vlive_money',
                'content'=>function($model) {
                    return $model->vlive_money + $model->vpre_live_money;
                }

            ],
            'vfix_money',
            [
                'attribute'=>'vcreate_time',
                'content'=>function($model){
                    return date("Y-m-d H:i:s",$model->vcreate_time);
                }
            ],
            [
                'attribute'=>'vis_verify',
                'content'=>function($model) {
                    if($model->vis_dredge == 1)
                        return "已认证";
                    if($model->vis_dredge == 9)
                        return "认证中";
                    else
                        return "未认证";
                }

            ],
            common\service\AdminService::getGrideViewButtons($this,
                ([
            
                    [
                        'view',
                        PermissionEnum::VIEW,
                        function($url,$model,$key){
                            $options=[
                                'title'=>Yii::t('app','btn_view'),
                                'aria-label'=>Yii::t('app','btn_view'),
                                'data-pjax'=>0
                            ];
                            return Html::a('<span class="glyphicon glyphicon-eye-open"></span>查看详情', '/member/member/view?id='.$model->vid, $options);
                        }
                    ],
                    [
                        'update',
                        PermissionEnum::UPDATE,
                        function($url,$model,$key){
                            $options=[
                                'title'=>Yii::t('app','btn_update'),
                                'aria-label'=>Yii::t('app','btn_update'),
                                'data-pjax'=>0
                            ];
                            return Html::a('<span class="glyphicon glyphicon-pencil"></span>编辑', '/member/member/update?id='.$model->vid );
                        }
            
                    ],
                    [
                        'money-info',
                        PermissionEnum::DETAILS,
                        function($url,$model,$key){
                            $options=[
                                'title'=>Yii::t('app','btn_delete'),
                                'aria-label'=>Yii::t('app','btn_delete'),
                                'data-pjax'=>0
                            ];
                            return Html::a('<span class="glyphicon glyphicon-eye-open"></span>收支明细', '/member/member/money-info?id='.$model->vid );
                        }
            
                    ],
                ]),'{view} {update} {money-info}'
            )

        ],
    ]); ?>

</div>
