<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\enum\PermissionEnum;
use common\service\AdminService;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\system\models\SettingSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '全局设置');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-index">

    <p></p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'pager'=>array(
            'firstPageLabel'=>'首页',
            'lastPageLabel'=>'尾页',
            'nextPageLabel'=>'下一页',
            'prevPageLabel'=>'前一页',
        ),
        'columns' => [
            'min_money',
            'money_fee',
            'fast_rate',
             'slow_rate',
             'per_money',
             'day_money',
             'operator',
             'open_start_time',
             'open_end_time',
             'close_content',

            'operator',

            common\service\AdminService::getGrideViewButtons($this,
                ([
                    [
                        'update',
                        PermissionEnum::UPDATE,
                        function($url,$model,$key){
                            $options=[
                                'title'=>Yii::t('app','btn_update'),
                                'aria-label'=>Yii::t('app','btn_update'),
                                'data-pjax'=>0
                            ];
                            return Html::a('<span class="glyphicon glyphicon-pencil"></span>编辑', $url, $options);
                        }
            
                    ],
                ]),'{update}'
            )
        ],
    ]); ?>

</div>
