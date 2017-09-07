<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\enum\PermissionEnum;
use common\service\AdminService;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\feedback\models\FeedbackSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '反馈列表');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php //= Html::a(Yii::t('app', 'Create Qfb Feedback'), ['create'], ['class' => 'btn btn-success']) ?>
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

            ['attribute' => 'member_id','content' => function($data) {
                    return !empty($data->member) ? $data->member->mobile : '';
                }
            ],
            'content',
            ['attribute'=>'reply_content','content'=>function($data){
                return isset($data->r_content)?$data->r_content->content:'';
            }],
            ['attribute' => 'reply','content' => function($data) {
                    return $data->reply === 0 ? '未回复' : '已回复';
                }
            ],
            ['attribute' =>'create_time','content' => function($data) {
                    return date("Y-m-d H:i:s",$data->create_time);
                }
            ],

            common\service\AdminService::getGrideViewButtons($this,
                ([
                    [
                        'update',
                        PermissionEnum::REPLY,
                        function($url,$model,$key){
                            $options=[
                                'title'=>Yii::t('app','btn_update'),
                                'aria-label'=>Yii::t('app','btn_update'),
                                'data-pjax'=>0
                            ];
                            return Html::a('<span class="glyphicon glyphicon-pencil"></span>回复',$url,$options);
                        }
                    ],
                ]),'{update}'
            )
        ],
    ]); ?>

</div>
