<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use \common\service\OrderFixService;

$this->title = '我的投资';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('@web/datepicker/laydate.js');

$columns = [

    [
        'label'=>'标的名称',
        'format'=>'raw',
        'value' => function($model){
            $url = "/index/index/detail?id=".$model->product_id;
            return Html::a($model->product_name, $url, ['title' => '查看']); 
        }
    ],
    [
        'header'  => '预期年化利率',
        'attribute' => 'year_rate',
        'content' => function($model){
            return $model->year_rate.'%';
        },
    ],
    [
        'header'  => '期限（天）',
        'attribute' => 'order_invest_day',
    ],
    [
        'header'  => '投资金额（元）',
        'attribute' => 'money',
    ],
    [
        'header'  => '预期收益（元）',
        'content' => function($model){
            $profitDay = $model->order_invest_day;
            $money = $model->day_interest*$profitDay;
            return \common\toolbox\Tool::moneyCalculate($money);
        },
    ],
    [
        'header'  => '还款方式',
        'attribute' => 'profit_type',
        'content' => function($model){
            $profit = '';
            if($model->profit_type == 1){
                $profit = '到期还本付息';
            }elseif($model->profit_type == 2){
                $profit = '按月等额付息，到期还本';
            }elseif($model->profit_type == 3){
                $profit = '按日等额付息，到期还本';
            }elseif($model->profit_type == 4){
                $profit = '按月等额本息';
            }elseif($model->profit_type == 5){
                $profit = '按日等额本息';
            }elseif($model->profit_type == 6){
                $profit = '按月等额还本，到期付息';
            }elseif($model->profit_type == 7){
                $profit = '按日等额还本，到期付息';
            }

            return $profit;
        },
    ],
    [
        'header'  => '加入时间',
        'attribute' => 'order_create_time',
        'content'  => function($model){
            return date('Y-m-d', $model->order_create_time);
        }
    ],
    [
        'header'  => '结束时间',
        'content'  => function($model){
            if($model->profit_day == 10){
                $profitDay = $model->finish_time+($model->order_invest_day)*(24*3600);
            }elseif($model->profit_day == 11){
                $profitDay = $model->finish_time+($model->order_invest_day+1)*(24*3600);;
            }elseif($model->profit_day == 20){
                $profitDay = $model->finish_time+($model->order_invest_day)*(24*3600);
            }else{
                $profitDay = $model->finish_time+($model->order_invest_day+1)*(24*3600);
            }
            return $model->finish_time ? date('Y-m-d', $profitDay) : '';
        }
    ],
    [
        'header'  => '状态',
        'attribute' => 'status',
        'content' => function($model){
            $check = '';
            if($model->status == 1){
                $check =  '投资中';
            }elseif($model->status == 2){
                $check =  '收益中';
            }elseif($model->status == 3){
                $check =  '已结束';
            }elseif($model->status == 4){
                $check =  '支付失败';
            }

            return $check;
        },
    ]
];
?>

<!-- content product begin-->
<div class="fr memberright">
    <div class="member-main-box1">
        <h2 class="member-total-tit">我的投资</h2>
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
                            'layout' => '{items}<div class="page-box con-pagets paterts"><div class="tdb-page clearfix">{summary}<div id="b-count" class="fl">{pager}</div></div></div>',
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
                    <?php Pjax::end();?>
                    <?php  Pjax::begin();?>
                    <div id="tab2" class="tab_content ">
                        <?= GridView::widget([
                            'dataProvider' => $dataProvider1,
                            'options' => ['class' => 'tab-total-mains'],
                            /* 表格配置 */
                            'tableOptions' => ['style'=>'width:100%'],
                            /* 重新排版 摘要、表格、分页 */
                            'layout' => '{items}<div class="page-box con-pagets paterts"><div class="tdb-page clearfix">{summary}<div id="b-count" class="fl">{pager}</div></div></div>',
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
                    <?php Pjax::end();?>
                    <?php  Pjax::begin();?>
                    <div id="tab3" class="tab_content ">
                        <?= GridView::widget([
                            'dataProvider' => $dataProvider2,
                            'options' => ['class' => 'tab-total-mains'],
                            /* 表格配置 */
                            'tableOptions' => ['style'=>'width:100%'],
                            /* 重新排版 摘要、表格、分页 */
                            'layout' => '{items}<div class="page-box con-pagets paterts"><div class="tdb-page clearfix">{summary}<div id="b-count" class="fl">{pager}</div></div></div>',
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
                    <?php Pjax::end();?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->beginBlock('test'); ?>

$('body').on('click','a',function(){document.location.href=this.href;});

<?php $this->endBlock()  ?>
    <!-- 将数据块 注入到视图中的某个位置 -->
<?php $this->registerJs($this->blocks['test'], \yii\web\View::POS_END); ?>