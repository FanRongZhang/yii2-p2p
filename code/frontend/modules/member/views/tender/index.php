<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

$this->title = '借款';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('@web/datepicker/laydate.js');

$columns = [
    [
        'header'  => '标的名称',
        'attribute' => 'product_name',
        'content' => function($model){
            return Html::a($model->product_name, 'javascript:void(0)', ['title' => '查看', 'onclick'=> 'product_detail('.$model->id.')']);
        }
    ],
    [
        'header'  => '放款状态',
        'attribute' => 'status',
        'content' => function($model){
            if(in_array($model->status, [0,1,2])){
                return '未放款';
            }elseif($model->status == 5){
                return '放款中';
            }else{
                return '已放款';
            }
        },
    ],
    [
        'header'  => '放款时间',
        'attribute' => 'time',
        'content' => function($model){
            if(in_array($model->status, [0,1,2])){
                return '';
            }
            return date('Y-m-d H:i:s', $model->credit_time);
        },
    ],
    [
        'header'  => '借款金额（元）',
        'attribute' => 'stock_money',
    ],
    [
        'header'  => '开始时间',
        'attribute' => 'start_time',
        'content'  => function($model){
            return date('Y-m-d H:i:s', $model->start_time);
        }
    ],
    [
        'header'  => '结束时间',
        'attribute' => 'finish_time',
        'content'  => function($model){
            return intval($model->finish_time) != 0 ? date('Y-m-d H:i:s', $model->finish_time) : '';
        }
    ],
    [
        'header'  => '还款状态',
        'attribute' => 'status',
        'content' => function($model){
            if($model->status == 7){
                $check =  '已还款,待确认';
            }elseif($model->status == 6){
                $check =  '还款中';
            }elseif($model->status == 8){
                $check =  '已还款';
            }else{
                $check =  '未还款';
            }

            return $check;
        },
    ]
];
?>
<div class="fr memberright">
    <div class="member-main-box1">
        <h2 class="member-total-tit">借款</h2>
        <div class="member-tment-main">
            <div class="member-stais-listmy ">
                <div class="con-prdpages member-con-tents">
                    <ul class="tabs clearfix">
                        <li class="pr-active"><a href="#tab1">全部</a> </li>
                        <li><a href="#tab2">参与中</a></li>
                        <li><a href="#tab3">已结束</a></li>
                    </ul>
                    <div class="ordertop orderright">
                        <?= $this->render('_search', ['model' => $model]); ?>
                    </div>
                </div>
                <div class="tab_container mar15">
                    <?php  Pjax::begin();?>
                    <div id="tab1" class="tab_content ">
                        <?= GridView::widget([
                            'dataProvider' => $dataProvider0,
                            'options' => ['class' => 'tab-total-mains'],
                            /* 表格配置 */
                            'tableOptions' => ['style'=>'width:100%'],
                            /* 重新排版 摘要、表格、分页 */
                            'layout' => '{items}<div class="page-box"><div class="tdb-page clearfix"><div class="b-count">{pager}</div>{summary}</div></div>',
                            /* 配置摘要 */
//                                        'summaryOptions' => ['class' => 'pagination'],
                            /* 配置分页样式 */
                            'pager' => [
                                'options' => ['class'=>'pagination','style'=>'visibility: visible;'],
                                'nextPageLabel' => '下一页',
                                'prevPageLabel' => '上一页',
                                'firstPageLabel' => '第一页',
                                'lastPageLabel' => '最后页'
                            ],
                            /* 定义列表格式 */
                            'columns' => $columns,
                        ]); ?>

                    </div>
                    <?php  Pjax::end();?>
                    <?php  Pjax::begin();?>
                    <div id="tab2" class="tab_content ">
                        <?= GridView::widget([
                            'dataProvider' => $dataProvider1,
                            'options' => ['class' => 'tab-total-mains'],
                            /* 表格配置 */
                            'tableOptions' => ['style'=>'width:100%'],
                            /* 重新排版 摘要、表格、分页 */
                            'layout' => '{items}<div class="page-box"><div class="tdb-page clearfix"><div class="b-count">{pager}</div>{summary}</div></div>',
                            /* 配置摘要 */
//                                        'summaryOptions' => ['class' => 'pagination'],
                            /* 配置分页样式 */
                            'pager' => [
                                'options' => ['class'=>'pagination','style'=>'visibility: visible;'],
                                'nextPageLabel' => '下一页',
                                'prevPageLabel' => '上一页',
                                'firstPageLabel' => '第一页',
                                'lastPageLabel' => '最后页'
                            ],
                            /* 定义列表格式 */
                            'columns' => $columns,
                        ]); ?>

                    </div>
                    <?php  Pjax::end();?>
                    <?php  Pjax::begin();?>
                    <div id="tab3" class="tab_content ">
                        <?= GridView::widget([
                            'dataProvider' => $dataProvider2,
                            'options' => ['class' => 'tab-total-mains'],
                            /* 表格配置 */
                            'tableOptions' => ['style'=>'width:100%'],
                            /* 重新排版 摘要、表格、分页 */
                            'layout' => '{items}<div class="page-box"><div class="tdb-page clearfix"><div class="b-count">{pager}</div>{summary}</div></div>',
                            /* 配置摘要 */
//                                        'summaryOptions' => ['class' => 'pagination'],
                            /* 配置分页样式 */
                            'pager' => [
                                'options' => ['class'=>'pagination','style'=>'visibility: visible;'],
                                'nextPageLabel' => '下一页',
                                'prevPageLabel' => '上一页',
                                'firstPageLabel' => '第一页',
                                'lastPageLabel' => '最后页'
                            ],
                            /* 定义列表格式 */
                            'columns' => $columns,
                        ]); ?>

                    </div>
                    <?php  Pjax::end();?>
                </div>
            </div>
        </div>

    </div>
</div>

<?php $this->beginBlock('test'); ?>

function product_detail(id){
window.location.href = '/index/index/detail?id='+id;
}


<?php $this->endBlock()  ?>
<!-- 将数据块 注入到视图中的某个位置 -->
<?php $this->registerJs($this->blocks['test'], \yii\web\View::POS_END); ?>
