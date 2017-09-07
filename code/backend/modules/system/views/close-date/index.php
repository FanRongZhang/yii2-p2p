<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\enum\PermissionEnum;
use common\service\AdminService;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '关闭提现日期');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-index">

    <p>
        <?php
        //判断是否拥有创建按钮权限
        if(AdminService::hasPermision($this,PermissionEnum::ADD))
            echo Html::a(Yii::t('app', '添加日期'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'pager'=>array(
            'firstPageLabel'=>'首页',
            'lastPageLabel'=>'尾页',
            'nextPageLabel'=>'下一页',
            'prevPageLabel'=>'前一页',
        ),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            ['attribute' => 'time','content' => function($data){
                    return date("Y-m-d",$data->time);
                }
            ],
            'operator',

            common\service\AdminService::getGrideViewButtons($this,
                ([
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
                                'data-confirm' => '确定删除该日期？',
                                'data-method' => 'post',
                                'data-pjax' => '0',
                            ]);
                            return $html;
                        }
            
                    ],
                ]),'{delete}'
            )
        ],
    ]); ?>

</div>
