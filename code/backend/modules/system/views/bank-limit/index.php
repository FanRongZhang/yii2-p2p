<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\enum\ChannelEnum;
use common\models\QfbAdmin;
/* @var $this yii\web\View */
/* @var $searchModel backend\modules\system\models\BankLimitSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '支持银行列表');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-index">

    <?php //echo $this->render('_search', ['model' => $searchModel]);?>

    <p>
        <?= Html::a(Yii::t('app', '+ 添加银行'), ['create','pt_type' => Yii::$app->request->get('pt_type')], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app','返回通道列表'), ['/system/qfb-channel'], ['class' => 'btn btn-primary']) ?>
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

            'name',

            ['attribute' => 'trade_num', 'content' => function($data) {
                    return $data->trade_num === 0 ? '无限制' : $data->trade_num;
                }
            ],
            'one_trade',

            'day_trade',

            'month_trade',

            ['attribute' => 'create_user', 'content' => function($data) {
                    return QfbAdmin::findOne($data->create_user)->account;
                }
            ],

            'iss_users',

            ['attribute' => 'is_support', 'content' => function($data) {
                    return $data->is_support === 0 ? '否' : '是';      
                }
            ],

            ['attribute' => 'pt_type', 'content' => function($data) {
                    return ChannelEnum::getChannelList($data->pt_type);
                }
            ],

            'bank_abbr',

            [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{update} {delete}',
                'buttons' => [  

                    'update' => function ($url,$model) {
                        $options = [
                            'title' => Yii::t('yii', '编辑'),
                            'aria-label' => Yii::t('yii', 'update'),
                            'data-pjax' => '0',
                        ];
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>编辑', '/system/bank-limit/update?id='.$model->id.'&pt_type='.Yii::$app->request->get('pt_type'), $options);
                    }, 

                    'delete' => function($url,$model){
                        $html = '';
                        $html = Html::a('<span class="glyphicon glyphicon-trash"></span>删除', $url, [
                            'title' => '删除',
                            'data-confirm' => '确定删除该银行？',
                            'data-method' => 'post',
                            'data-pjax' => '0',
                        ]);
                        return $html;
                    },
                ],
                'header' => '操作'
            ],
        ],
    ]); ?>

</div>
