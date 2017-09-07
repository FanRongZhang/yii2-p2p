<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\enum\PermissionEnum;
use common\service\AdminService;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\system\models\MenuSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '菜单管理';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php echo $this->render('_search', ['model' => $searchModel]); ?>

<div class="qfb-menu-index">
    
    <p>
        <?php
        //判断是否拥有创建按钮权限
        if(AdminService::hasPermision($this,PermissionEnum::ADD))
            echo Html::a('添加菜单', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            // 'display',
            ['attribute' => 'display','content' => function($data) {
                    return $data->display == 0 ? '不显示' : '显示';
                }
            ],
            'parent_id',
            'level',
            'url:url',
            'permision_value',
            'sorts',

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
                            return Html::a('<span class="glyphicon glyphicon-eye-open"></span>查看详情', '/system/menu/index?parent_id='.$model->id, $options);
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
                            return Html::a('<span class="glyphicon glyphicon-pencil"></span>编辑', '/system/menu/update?id='.$model->id, $options);
                        }
            
                    ],
                    [
                        'delete',
                        PermissionEnum::DELETE,
                        function($url,$model,$key){
                            $options=[
                                'title'=>Yii::t('app','btn_delete'),
                                'aria-label'=>Yii::t('app','btn_delete'),
                                'data-pjax'=>0
                            ];
                            $html = '';
                            $html = Html::a('<span class="glyphicon glyphicon-trash"></span>删除', $url, [
                                'title' => '删除',
                                'data-confirm' => '确定删除该银行？',
                                'data-method' => 'post',
                                'data-pjax' => '0',
                            ]);
                            return $html;
                        }
            
                    ],
                ]),'{view} {update} {delete}'
            )
        ],
    ]); ?>

</div>
